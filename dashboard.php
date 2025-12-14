<?php
// Include header file, which handles session and starts the HTML layout
require_once "includes/header.php"; 

// NOTE: You must also ensure 'config.php' is available if you need database access here
// require_once "config.php"; 
?>

<div class="dashboard-hero">
    <h1>Product Inventory Hub</h1>
    <p>Welcome back, **<?php echo htmlspecialchars($_SESSION["username"]); ?>**! Track, manage, and optimize your stock effortlessly.</p>
    <i class="fas fa-warehouse hero-icon"></i>
</div>

<div class="content-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Your Products <i class="fas fa-box-open text-warning"></i></h2>
        <a href="products/create.php" class="btn btn-success back-btn-style">
            <i class="fas fa-plus-circle"></i> Add New Product
        </a>
    </div>

    <div class="table-responsive">
        <table class="table table-hover">
            <thead class="thead-light">
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Price (P)</th>
                    <th>Category</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>3</td>
                    <td>blush</td>
                    <td>₱150.00</td>
                    <td>Beauty Products</td>
                    <td>
                        <a href="products/edit.php?id=3" class="btn btn-sm btn-info"><i class="fas fa-pen"></i> Edit</a>
                        <a href="products/delete.php?id=3" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i> Delete</a>
                    </td>
                </tr>
                <tr>
                    <td>2</td>
                    <td>blush</td>
                    <td>₱150.00</td>
                    <td>Beauty Products</td>
                    <td>
                        <a href="products/edit.php?id=2" class="btn btn-sm btn-info"><i class="fas fa-pen"></i> Edit</a>
                        <a href="products/delete.php?id=2" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i> Delete</a>
                    </td>
                </tr>
                <tr>
                    <td>1</td>
                    <td>ricee</td>
                    <td>₱250.00</td>
                    <td>Food & Drinks</td>
                    <td>
                        <a href="products/edit.php?id=1" class="btn btn-sm btn-info"><i class="fas fa-pen"></i> Edit</a>
                        <a href="products/delete.php?id=1" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i> Delete</a>
                    </td>
                </tr>
                </tbody>
        </table>
    </div>

</div>

<?php 
require_once "includes/footer.php"; 
?>