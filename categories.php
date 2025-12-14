<?php
// Includes the common header, handling session start and authentication
require_once "includes/header.php"; 

// Includes the sidebar, starting the main content area
require_once "includes/sidebar.php"; 

require_once "config.php"; // Database connection

$message = '';
$pdo = $GLOBALS['pdo']; // Access the PDO object from config.php

// ====================================================================
// A. CRUD LOGIC
// ====================================================================

// --- DELETE Operation ---
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    // Check if category is used by any products before deletion
    $check_sql = "SELECT COUNT(*) FROM products WHERE category_id = ?";
    $check_stmt = $pdo->prepare($check_sql);
    $check_stmt->execute([$id]);
    $product_count = $check_stmt->fetchColumn();

    if ($product_count > 0) {
        $message = '<div class="alert alert-danger">Cannot delete category ID ' . $id . '. It is linked to ' . $product_count . ' product(s).</div>';
    } else {
        $delete_sql = "DELETE FROM categories WHERE id = ?";
        $delete_stmt = $pdo->prepare($delete_sql);
        if ($delete_stmt->execute([$id])) {
            $message = '<div class="alert alert-success">Category deleted successfully.</div>';
        } else {
            $message = '<div class="alert alert-danger">Error deleting category.</div>';
        }
    }
}

// --- CREATE / UPDATE Operation ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['category_name'])) {
    $id = isset($_POST['category_id']) ? (int)$_POST['category_id'] : null;
    $name = trim($_POST['category_name']);

    if (empty($name)) {
        $message = '<div class="alert alert-warning">Please enter a category name.</div>';
    } else {
        try {
            if ($id) {
                // Update
                $sql = "UPDATE categories SET name = ? WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $success = $stmt->execute([$name, $id]);
                $action_word = 'updated';
            } else {
                // Create
                $sql = "INSERT INTO categories (name) VALUES (?)";
                $stmt = $pdo->prepare($sql);
                $success = $stmt->execute([$name]);
                $action_word = 'created';
            }
            
            if ($success) {
                $message = '<div class="alert alert-success">Category ' . $action_word . ' successfully.</div>';
            } else {
                 $message = '<div class="alert alert-danger">Error ' . $action_word . ' category.</div>';
            }
        } catch (PDOException $e) {
            // Error for duplicate name (UNIQUE constraint)
            if ($e->getCode() == 23000) { 
                $message = '<div class="alert alert-danger">Error: A category with this name already exists.</div>';
            } else {
                error_log("Category DB Error: " . $e->getMessage());
                $message = '<div class="alert alert-danger">Database error occurred.</div>';
            }
        }
    }
}

// ====================================================================
// B. DATA FETCHING
// ====================================================================

$categories = [];
try {
    $stmt = $pdo->prepare("SELECT id, name FROM categories ORDER BY name ASC");
    $stmt->execute();
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Handle error
}
?>

<div class="content-card">
    
    <div class="back-button-container">
        <a href="dashboard.php" class="btn back-btn-style">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Category Management <i class="fas fa-tags"></i></h2>
        <button type="button" class="btn btn-success" data-toggle="modal" data-target="#categoryModal" data-id="" data-name="">
            ➕ Add New Category
        </button>
    </div>
    
    <?php echo $message; // Display success/error messages ?>

    <div class="category-list mt-4">
        <h3>Current Categories</h3>
        
        <?php if (count($categories) > 0): ?>
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="thead-dark">
                        <tr>
                            <th style="width: 10%;">ID</th>
                            <th>Category Name</th>
                            <th style="width: 150px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $category): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($category['id']); ?></td>
                                <td><?php echo htmlspecialchars($category['name']); ?></td>
                                <td>
                                    <button class="btn btn-sm btn-info edit-btn"
                                        data-toggle="modal" data-target="#categoryModal"
                                        data-id="<?php echo htmlspecialchars($category['id']); ?>"
                                        data-name="<?php echo htmlspecialchars($category['name']); ?>"> 
                                        ✏️ Edit
                                    </button>
                                    <a href="categories.php?action=delete&id=<?php echo htmlspecialchars($category['id']); ?>" class="btn btn-sm btn-danger" onclick="return confirm('WARNING: Deleting category ID <?php echo htmlspecialchars($category['id']); ?>? This action is irreversible and requires no linked products.');">
                                        🗑️ Delete
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-warning">No categories found. Click "Add New Category" to start.</div>
        <?php endif; ?>
    </div>
</div>

<div class="modal fade" id="categoryModal" tabindex="-1" role="dialog" aria-labelledby="categoryModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="modal-header bg-primary text-white"> 
                    <h5 class="modal-title" id="categoryModalLabel">Add/Edit Category</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="category_id" id="category_id">
                    
                    <div class="form-group">
                        <label for="category_name">Category Name</label>
                        <input type="text" name="category_name" id="category_name" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success" id="modalSaveBtn">Save changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Script to dynamically update the modal content based on whether adding or editing
    $('#categoryModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var id = button.data('id');
        var name = button.data('name');
        
        var modal = $(this);
        
        if (id) {
            modal.find('.modal-title').text('Edit Category (ID: ' + id + ')');
            modal.find('#modalSaveBtn').text('Update Category').removeClass('btn-success').addClass('btn-primary'); 
            modal.find('#category_id').val(id);
            modal.find('#category_name').val(name);
        } else {
            modal.find('.modal-title').text('Add New Category');
            modal.find('#modalSaveBtn').text('Add Category').removeClass('btn-primary').addClass('btn-success'); 
            modal.find('#category_id').val('');
            modal.find('#category_name').val('');
        }
    });
</script>

<?php 
// Includes the footer (closes .content-wrapper and HTML)
require_once "includes/footer.php"; 
?>