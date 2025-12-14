<?php

class ProductModel {
    private $pdo;

    // The PDO connection is passed in (Dependency Injection)
    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    // REMOVED fetchCategories() method here

    // Read products for a specific user
    public function fetchUserProducts($user_id) {
        $sql = "
            SELECT p.id, p.name, p.price, c.name AS category_name, p.category_id
            FROM products p
            JOIN categories c ON p.category_id = c.id
            WHERE p.created_by = ? 
            ORDER BY p.id DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Create or Update a product
    public function saveProduct($id, $name, $price, $category_id, $user_id) {
        try {
            if ($id) {
                // Update
                $sql = "UPDATE products SET name = ?, price = ?, category_id = ? WHERE id = ? AND created_by = ?";
                $stmt = $this->pdo->prepare($sql);
                $success = $stmt->execute([$name, $price, $category_id, $id, $user_id]);
            } else {
                // Create
                $sql = "INSERT INTO products (created_by, name, price, category_id) VALUES (?, ?, ?, ?)";
                $stmt = $this->pdo->prepare($sql);
                $success = $stmt->execute([$user_id, $name, $price, $category_id]);
            }
            return $success;
        } catch (PDOException $e) {
             error_log("DB Error in saveProduct: " . $e->getMessage());
             return false;
        }
    }

    // Delete a product
    public function deleteProduct($id, $user_id) {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM products WHERE id = ? AND created_by = ?");
            return $stmt->execute([$id, $user_id]);
        } catch (PDOException $e) {
             error_log("DB Error in deleteProduct: " . $e->getMessage());
             return false;
        }
    }
}