<?php

class CategoryModel {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    // CREATE / UPDATE a category
    public function saveCategory($id, $name) {
        try {
            if ($id) {
                // Update (Note: Categories are global, not user-specific)
                $sql = "UPDATE categories SET name = ? WHERE id = ?";
                $stmt = $this->pdo->prepare($sql);
                return $stmt->execute([$name, $id]);
            } else {
                // Create
                $sql = "INSERT INTO categories (name) VALUES (?)";
                $stmt = $this->pdo->prepare($sql);
                return $stmt->execute([$name]);
            }
        } catch (PDOException $e) {
             error_log("DB Error in saveCategory: " . $e->getMessage());
             return false;
        }
    }

    // READ all categories
    public function fetchCategories() {
        try {
            $stmt = $this->pdo->prepare("SELECT id, name FROM categories ORDER BY name ASC");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching categories: " . $e->getMessage());
            throw new Exception("Could not retrieve categories at this time."); 
        }
    }

    // DELETE a category
    public function deleteCategory($id) {
        try {
            // Check if any products use this category before deleting (Integrity)
            $check_sql = "SELECT COUNT(*) FROM products WHERE category_id = ?";
            $check_stmt = $this->pdo->prepare($check_sql);
            $check_stmt->execute([$id]);
            if ($check_stmt->fetchColumn() > 0) {
                // Throw exception to be caught in categories.php
                throw new Exception("Cannot delete category. Products are linked to it.");
            }

            $delete_stmt = $this->pdo->prepare("DELETE FROM categories WHERE id = ?");
            return $delete_stmt->execute([$id]);
        } catch (PDOException $e) {
             error_log("DB Error in deleteCategory: " . $e->getMessage());
             return false;
        }
    }
}