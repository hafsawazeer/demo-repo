<?php
require_once __DIR__ . '/../config/config.php';

class NutritionistModel {
    private $pdo;
    
    public function __construct() {
        $this->pdo = getDBConnection();
    }
    
    /**
     * Get all nutritionists with their user details
     */
    public function getNutritionists($search = '', $sort = 'created_at_desc') {
        try {
            $params = [];
            $whereSql = '';
            $hasSearch = ($search !== '');

            if ($hasSearch) {
                $contains = '%' . $search . '%';
                $whereSql = "
                    WHERE
                        COALESCE(u.name, '') LIKE :q_contains
                    OR  COALESCE(u.email, '') LIKE :q_contains
                    OR  COALESCE(n.specialization, '') LIKE :q_contains
                    OR  COALESCE(n.nic, '') LIKE :q_contains
                    OR  CONCAT('NT', LPAD(n.nutritionist_id, 5, '0')) LIKE :q_contains
                ";
                $params[':q_contains'] = $contains;
            }

            // Whitelisted ORDER BY
            $orderBySql = 'n.created_at DESC';
            switch ($sort) {
                case 'name_asc':
                    $orderBySql = 'u.name ASC';
                    break;
                case 'name_desc':
                    $orderBySql = 'u.name DESC';
                    break;
                case 'experience_asc':
                    $orderBySql = 'n.experience_years ASC';
                    break;
                case 'experience_desc':
                    $orderBySql = 'n.experience_years DESC';
                    break;
                case 'status':
                    $orderBySql = 'n.status ASC, n.created_at DESC';
                    break;
                case 'created_at_asc':
                    $orderBySql = 'n.created_at ASC';
                    break;
                case 'created_at_desc':
                default:
                    $orderBySql = 'n.created_at DESC';
                    break;
            }

            $sql = "
                SELECT 
                    n.*,
                    u.name,
                    u.email,
                    u.phone,
                    n.experience_years as experience
                FROM nutritionist n
                INNER JOIN user_table u ON n.nutritionist_id = u.user_id
                $whereSql
                ORDER BY $orderBySql
            ";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('Error fetching nutritionists: ' . $e->getMessage());
            
            // Try with capital table names as fallback
            try {
                $sql = "
                    SELECT 
                        n.*,
                        u.name,
                        u.email,
                        u.phone,
                        n.experience_years as experience
                    FROM Nutritionist n
                    INNER JOIN User_Table u ON n.nutritionist_id = u.user_id
                    $whereSql
                    ORDER BY $orderBySql
                ";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute($params);
                return $stmt->fetchAll();
            } catch (PDOException $e2) {
                error_log('Error with capital table names: ' . $e2->getMessage());
                return [];
            }
        }
    }
    
    /**
     * Get a single nutritionist with user details
     */
    public function getNutritionistById($nutritionist_id) {
        try {
            $sql = "
                SELECT 
                    n.*,
                    u.name,
                    u.email,
                    u.phone,
                    n.experience_years as experience
                FROM nutritionist n
                INNER JOIN user_table u ON n.nutritionist_id = u.user_id
                WHERE n.nutritionist_id = ?
            ";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$nutritionist_id]);
            $result = $stmt->fetch();
            
            if (!$result) {
                // Try with capital table names
                $sql = "
                    SELECT 
                        n.*,
                        u.name,
                        u.email,
                        u.phone,
                        n.experience_years as experience
                    FROM Nutritionist n
                    INNER JOIN User_Table u ON n.nutritionist_id = u.user_id
                    WHERE n.nutritionist_id = ?
                ";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([$nutritionist_id]);
                $result = $stmt->fetch();
            }
            
            return $result;
        } catch (PDOException $e) {
            error_log("Error fetching nutritionist: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Update nutritionist status only
     */
    public function updateStatus($nutritionist_id, $status) {
        try {
            error_log("Updating nutritionist $nutritionist_id to status: $status");
            
            // Try lowercase table name first
            $stmt = $this->pdo->prepare("
                UPDATE nutritionist 
                SET status = ?, updated_at = NOW()
                WHERE nutritionist_id = ?
            ");
            $result = $stmt->execute([$status, $nutritionist_id]);
            
            if ($result && $stmt->rowCount() > 0) {
                error_log("Status update successful with lowercase table name");
                return true;
            }
            
            // Try uppercase table name
            $stmt = $this->pdo->prepare("
                UPDATE Nutritionist 
                SET status = ?, updated_at = NOW()
                WHERE nutritionist_id = ?
            ");
            $result = $stmt->execute([$status, $nutritionist_id]);
            
            if ($result) {
                $rowCount = $stmt->rowCount();
                error_log("Status update affected $rowCount rows with uppercase table name");
                return $rowCount > 0;
            }
            
            error_log("Status update failed - no result");
            return false;
            
        } catch (PDOException $e) {
            error_log("Error updating nutritionist status: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete a nutritionist
     */
    public function deleteNutritionist($nutritionist_id) {
        try {
            // First get NIC image path to delete file
            $nutritionist = $this->getNutritionistById($nutritionist_id);
            
            if (!$nutritionist) {
                error_log("Nutritionist not found with ID: $nutritionist_id");
                return false;
            }
            
            // Start transaction to delete from both tables
            $this->pdo->beginTransaction();
            
            try {
                // Delete from nutritionist table first (try lowercase)
                $stmt = $this->pdo->prepare("DELETE FROM nutritionist WHERE nutritionist_id = ?");
                $result = $stmt->execute([$nutritionist_id]);
                
                if (!$result || $stmt->rowCount() === 0) {
                    // Try uppercase
                    $stmt = $this->pdo->prepare("DELETE FROM Nutritionist WHERE nutritionist_id = ?");
                    $result = $stmt->execute([$nutritionist_id]);
                }
                
                if (!$result || $stmt->rowCount() === 0) {
                    throw new Exception("Failed to delete from nutritionist table");
                }
                
                // Delete from user_table (try lowercase)
                $stmt = $this->pdo->prepare("DELETE FROM user_table WHERE user_id = ?");
                $result = $stmt->execute([$nutritionist_id]);
                
                if (!$result || $stmt->rowCount() === 0) {
                    // Try uppercase
                    $stmt = $this->pdo->prepare("DELETE FROM User_Table WHERE user_id = ?");
                    $result = $stmt->execute([$nutritionist_id]);
                }
                
                if (!$result || $stmt->rowCount() === 0) {
                    throw new Exception("Failed to delete from user table");
                }
                
                // Commit transaction
                $this->pdo->commit();
                
                // Delete NIC image file if exists
                if (!empty($nutritionist['nic_image_path'])) {
                    $filePath = __DIR__ . '/../../' . $nutritionist['nic_image_path'];
                    if (file_exists($filePath)) {
                        @unlink($filePath);
                        error_log("Deleted NIC image: $filePath");
                    }
                }
                
                error_log("Successfully deleted nutritionist ID: $nutritionist_id");
                return true;
                
            } catch (Exception $e) {
                $this->pdo->rollBack();
                throw $e;
            }
            
        } catch (Exception $e) {
            error_log("Error deleting nutritionist: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Add a new nutritionist manually by supervisor
     */
    public function addNutritionist($name, $email, $phone, $password, $gender, $dob, $nic, $specialization, $experience_years, $certification = '', $qualifications = '', $nic_image_path = '') {
        try {
            // Start transaction
            $this->pdo->beginTransaction();
            
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Check if email already exists
            $stmt = $this->pdo->prepare("SELECT user_id FROM user_table WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                // Try uppercase table name
                $stmt = $this->pdo->prepare("SELECT user_id FROM User_Table WHERE email = ?");
                $stmt->execute([$email]);
                if ($stmt->fetch()) {
                    throw new Exception("Email already exists");
                }
            }
            
            // Insert into user_table (try lowercase first)
            $user_sql = "INSERT INTO user_table (name, email, phone, password, role, gender, dob, status, created_at) 
                        VALUES (?, ?, ?, ?, 'Nutritionist', ?, ?, 'Active', NOW())";
            
            $user_stmt = $this->pdo->prepare($user_sql);
            $user_result = $user_stmt->execute([$name, $email, $phone, $hashed_password, $gender, $dob]);
            
            if (!$user_result) {
                // Try uppercase table name
                $user_sql = "INSERT INTO User_Table (name, email, phone, password, role, gender, dob, status, created_at) 
                            VALUES (?, ?, ?, ?, 'Nutritionist', ?, ?, 'Active', NOW())";
                
                $user_stmt = $this->pdo->prepare($user_sql);
                $user_result = $user_stmt->execute([$name, $email, $phone, $hashed_password, $gender, $dob]);
                
                if (!$user_result) {
                    throw new Exception("Failed to create user account");
                }
            }
            
            $user_id = $this->pdo->lastInsertId();
            
            // Insert into nutritionist table (try lowercase first)
            $nutr_sql = "INSERT INTO nutritionist (
                nutritionist_id, gender, dob, nic, nic_image_path,
                specialization, experience_years, certification, 
                qualifications, status, created_at, updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', NOW(), NOW())";
            
            $nutr_stmt = $this->pdo->prepare($nutr_sql);
            $nutr_result = $nutr_stmt->execute([
                $user_id, $gender, $dob, $nic, $nic_image_path,
                $specialization, $experience_years, $certification, $qualifications
            ]);
            
            if (!$nutr_result) {
                // Try uppercase table name
                $nutr_sql = "INSERT INTO Nutritionist (
                    nutritionist_id, gender, dob, nic, nic_image_path,
                    specialization, experience_years, certification, 
                    qualifications, status, created_at, updated_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', NOW(), NOW())";
                
                $nutr_stmt = $this->pdo->prepare($nutr_sql);
                $nutr_result = $nutr_stmt->execute([
                    $user_id, $gender, $dob, $nic, $nic_image_path,
                    $specialization, $experience_years, $certification, $qualifications
                ]);
                
                if (!$nutr_result) {
                    throw new Exception("Failed to create nutritionist profile");
                }
            }
            
            // Commit transaction
            $this->pdo->commit();
            error_log("Successfully added nutritionist with user_id: $user_id");
            return true;
            
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log("Add nutritionist error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update nutritionist details
     */
    public function updateNutritionist($nutritionist_id, $name, $email, $phone, $specialization, $experience_years, $certification = '', $qualifications = '') {
        try {
            // Start transaction
            $this->pdo->beginTransaction();
            
            // Update user_table (try lowercase first)
            $user_sql = "UPDATE user_table SET name = ?, email = ?, phone = ? WHERE user_id = ?";
            $user_stmt = $this->pdo->prepare($user_sql);
            $user_result = $user_stmt->execute([$name, $email, $phone, $nutritionist_id]);
            
            if (!$user_result || $user_stmt->rowCount() === 0) {
                // Try uppercase table name
                $user_sql = "UPDATE User_Table SET name = ?, email = ?, phone = ? WHERE user_id = ?";
                $user_stmt = $this->pdo->prepare($user_sql);
                $user_result = $user_stmt->execute([$name, $email, $phone, $nutritionist_id]);
                
                if (!$user_result) {
                    throw new Exception("Failed to update user details");
                }
            }
            
            // Update nutritionist table (try lowercase first)
            $nutr_sql = "UPDATE nutritionist 
                        SET specialization = ?, experience_years = ?, certification = ?, qualifications = ?, updated_at = NOW()
                        WHERE nutritionist_id = ?";
            
            $nutr_stmt = $this->pdo->prepare($nutr_sql);
            $nutr_result = $nutr_stmt->execute([
                $specialization, $experience_years, $certification, $qualifications, $nutritionist_id
            ]);
            
            if (!$nutr_result || $nutr_stmt->rowCount() === 0) {
                // Try uppercase table name
                $nutr_sql = "UPDATE Nutritionist 
                            SET specialization = ?, experience_years = ?, certification = ?, qualifications = ?, updated_at = NOW()
                            WHERE nutritionist_id = ?";
                
                $nutr_stmt = $this->pdo->prepare($nutr_sql);
                $nutr_result = $nutr_stmt->execute([
                    $specialization, $experience_years, $certification, $qualifications, $nutritionist_id
                ]);
                
                if (!$nutr_result) {
                    throw new Exception("Failed to update nutritionist profile");
                }
            }
            
            // Commit transaction
            $this->pdo->commit();
            return true;
            
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log("Update nutritionist error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get nutritionist statistics
     */
    public function getStatistics() {
        try {
            // Try lowercase table name first
            $sql = "
                SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
                    SUM(CASE WHEN status = 'inactive' THEN 1 ELSE 0 END) as inactive
                FROM nutritionist
            ";
            $stmt = $this->pdo->query($sql);
            $result = $stmt->fetch();
            
            if ($result && $result['total'] > 0) {
                return $result;
            }
            
            // Try uppercase table name
            $sql = "
                SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
                    SUM(CASE WHEN status = 'inactive' THEN 1 ELSE 0 END) as inactive
                FROM Nutritionist
            ";
            $stmt = $this->pdo->query($sql);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Error fetching statistics: " . $e->getMessage());
            return ['total' => 0, 'pending' => 0, 'active' => 0, 'inactive' => 0];
        }
    }
    
    /**
     * Check if Nutritionist table exists
     */
    public function tableExists() {
        try {
            // Check lowercase first
            $stmt = $this->pdo->query("SHOW TABLES LIKE 'nutritionist'");
            if ($stmt->rowCount() > 0) {
                return true;
            }
            
            // Check uppercase
            $stmt = $this->pdo->query("SHOW TABLES LIKE 'Nutritionist'");
            return $stmt->rowCount() > 0;
        } catch (Exception $e) {
            error_log("Error checking table existence: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Debug method to check table structure
     */
    public function debugTables() {
        try {
            $result = [];
            
            // Check what tables exist
            $stmt = $this->pdo->query("SHOW TABLES");
            $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
            $result['tables'] = $tables;
            
            // Check nutritionist table structure (try both cases)
            foreach (['nutritionist', 'Nutritionist'] as $table) {
                if (in_array($table, $tables)) {
                    $stmt = $this->pdo->query("DESCRIBE $table");
                    $result[$table . '_structure'] = $stmt->fetchAll();
                }
            }
            
            // Check user table structure (try both cases)
            foreach (['user_table', 'User_Table'] as $table) {
                if (in_array($table, $tables)) {
                    $stmt = $this->pdo->query("DESCRIBE $table");
                    $result[$table . '_structure'] = $stmt->fetchAll();
                }
            }
            
            return $result;
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
}
?>