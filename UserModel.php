<?php

class UserModel {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Attempts to log in a user using either username or email.
     * @return array|false The user row on success, false otherwise.
     */
    public function login($login_identifier, $password) {
        $sql = "SELECT id, username, password FROM users WHERE username = :login_id OR email = :login_id";
        
        if ($stmt = $this->pdo->prepare($sql)) {
            $stmt->bindParam(":login_id", $param_login_id, PDO::PARAM_STR);
            $param_login_id = $login_identifier;
            
            if ($stmt->execute() && $stmt->rowCount() == 1) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $hashed_password = $row['password'];
                
                if (password_verify($password, $hashed_password)) {
                    // Password is correct
                    return $row;
                }
            }
            unset($stmt);
        }
        return false;
    }

    /**
     * Registers a new user.
     * @return bool True on success, false otherwise.
     */
    public function register($username, $email, $password) {
        // Validation check for uniqueness (must be done before inserting)
        $sql_check = "SELECT id FROM users WHERE username = :username OR email = :email";
        $stmt_check = $this->pdo->prepare($sql_check);
        $stmt_check->execute([':username' => $username, ':email' => $email]);
        if ($stmt_check->rowCount() > 0) {
            // Error handling needs to be done in the calling script (register.php)
            return false;
        }

        $sql = "INSERT INTO users (username, email, password) VALUES (:username, :email, :password)";
        if ($stmt = $this->pdo->prepare($sql)) {
            $param_password = password_hash($password, PASSWORD_DEFAULT);
            
            if ($stmt->execute([
                ':username' => $username, 
                ':email' => $email, 
                ':password' => $param_password
            ])) {
                return true;
            }
            error_log("DB Error in register: " . $stmt->errorInfo()[2]);
            unset($stmt);
        }
        return false;
    }

    /**
     * Updates the user's password.
     * @return bool True on success, false otherwise.
     */
    public function updatePassword($user_id, $new_password) {
        $sql = "UPDATE users SET password = :password WHERE id = :id";
        if ($stmt = $this->pdo->prepare($sql)) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            
            if ($stmt->execute([':password' => $hashed_password, ':id' => $user_id])) {
                return true;
            }
            error_log("DB Error in updatePassword: " . $stmt->errorInfo()[2]);
            unset($stmt);
        }
        return false;
    }
}