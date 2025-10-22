<?php
require_once __DIR__ . '/../config/config.php';

class NutritionistModel {
    private $pdo;
    
    public function __construct() {
        $this->pdo = getDBConnection();
    }
    
    /**
     * Get all nutritionists with their user details - FIXED VERSION
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
                    $orderBySql = 'COALESCE(n.experience_years, n.experience, 0) ASC';
                    break;
                case 'experience_desc':
                    $orderBySql = 'COALESCE(n.experience_years, n.experience, 0) DESC';
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

            // Try different table name combinations
            $table_combinations = [
                ['nutritionist', 'user_table'],
                ['Nutritionist', 'User_Table'],
                ['nutritionist', 'User_Table'],
                ['Nutritionist', 'user_table']
            ];
            
            foreach ($table_combinations as $tables) {
                try {
                    $sql = "
                        SELECT 
                            n.*,
                            u.name,
                            u.email,
                            COALESCE(u.phone, u.contact_no) as phone,
                            COALESCE(n.experience_years, n.experience, 0) as experience
                        FROM {$tables[0]} n
                        INNER JOIN {$tables[1]} u ON n.nutritionist_id = u.user_id
                        $whereSql
                        ORDER BY $orderBySql
                    ";

                    $stmt = $this->pdo->prepare($sql);
                    $stmt->execute($params);
                    $result = $stmt->fetchAll();
                    
                    if ($result !== false) {
                        error_log("Successfully used tables: {$tables[0]} and {$tables[1]}");
                        return $result;
                    }
                } catch (PDOException $e) {
                    error_log("Failed with tables {$tables[0]} and {$tables[1]}: " . $e->getMessage());
                    continue;
                }
            }
            
            error_log('All table combinations failed for getNutritionists');
            return [];
            
        } catch (PDOException $e) {
            error_log('Error fetching nutritionists: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get a single nutritionist with user details - FIXED VERSION
     */
    public function getNutritionistById($nutritionist_id) {
        try {
            $table_combinations = [
                ['nutritionist', 'user_table'],
                ['Nutritionist', 'User_Table'],
                ['nutritionist', 'User_Table'],
                ['Nutritionist', 'user_table']
            ];
            
            foreach ($table_combinations as $tables) {
                try {
                    $sql = "
                        SELECT 
                            n.*,
                            u.name,
                            u.email,
                            COALESCE(u.phone, u.contact_no) as phone,
                            COALESCE(n.experience_years, n.experience, 0) as experience
                        FROM {$tables[0]} n
                        INNER JOIN {$tables[1]} u ON n.nutritionist_id = u.user_id
                        WHERE n.nutritionist_id = ?
                    ";
                    $stmt = $this->pdo->prepare($sql);
                    $stmt->execute([$nutritionist_id]);
                    $result = $stmt->fetch();
                    
                    if ($result !== false) {
                        return $result;
                    }
                } catch (PDOException $e) {
                    continue;
                }
            }
            
            return null;
        } catch (PDOException $e) {
            error_log("Error fetching nutritionist: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Update nutritionist status only - FIXED VERSION
     */
    public function updateStatus($nutritionist_id, $status) {
        try {
            error_log("Updating nutritionist $nutritionist_id to status: $status");
            
            $table_names = ['nutritionist', 'Nutritionist'];
            
            foreach ($table_names as $table) {
                try {
                    $stmt = $this->pdo->prepare("
                        UPDATE $table 
                        SET status = ?, updated_at = NOW()
                        WHERE nutritionist_id = ?
                    ");
                    $result = $stmt->execute([$status, $nutritionist_id]);
                    
                    if ($result && $stmt->rowCount() > 0) {
                        error_log("Status update successful with table: $table");
                        return true;
                    }
                } catch (PDOException $e) {
                    error_log("Failed with table $table: " . $e->getMessage());
                    continue;
                }
            }
            
            error_log("Status update failed - no rows affected");
            return false;
            
        } catch (PDOException $e) {
            error_log("Error updating nutritionist status: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete a nutritionist - FIXED VERSION
     */
    public function deleteNutritionist($nutritionist_id) {
        try {
            // First get nutritionist data
            $nutritionist = $this->getNutritionistById($nutritionist_id);
            
            if (!$nutritionist) {
                error_log("Nutritionist not found with ID: $nutritionist_id");
                return false;
            }
            
            // Start transaction to delete from both tables
            $this->pdo->beginTransaction();
            
            try {
                $table_combinations = [
                    ['nutritionist', 'user_table'],
                    ['Nutritionist', 'User_Table'],
                    ['nutritionist', 'User_Table'],
                    ['Nutritionist', 'user_table']
                ];
                
                $deleted = false;
                
                foreach ($table_combinations as $tables) {
                    try {
                        // Delete from nutritionist table first
                        $stmt = $this->pdo->prepare("DELETE FROM {$tables[0]} WHERE nutritionist_id = ?");
                        $result = $stmt->execute([$nutritionist_id]);
                        
                        if ($result && $stmt->rowCount() > 0) {
                            // Delete from user table
                            $stmt = $this->pdo->prepare("DELETE FROM {$tables[1]} WHERE user_id = ?");
                            $result = $stmt->execute([$nutritionist_id]);
                            
                            if ($result && $stmt->rowCount() > 0) {
                                $deleted = true;
                                error_log("Successfully deleted using tables: {$tables[0]} and {$tables[1]}");
                                break;
                            }
                        }
                    } catch (PDOException $e) {
                        error_log("Failed with tables {$tables[0]} and {$tables[1]}: " . $e->getMessage());
                        continue;
                    }
                }
                
                if (!$deleted) {
                    throw new Exception("Failed to delete nutritionist from database");
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
     * Add a new nutritionist manually by supervisor - FIXED VERSION
     */
    public function addNutritionist($name, $email, $phone, $password, $gender, $dob, $nic, $specialization, $experience_years, $certification = '', $qualifications = '', $nic_image_path = '') {
        try {
            // Start transaction
            $this->pdo->beginTransaction();
            
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $table_combinations = [
                ['user_table', 'nutritionist'],
                ['User_Table', 'Nutritionist'],
                ['user_table', 'Nutritionist'],
                ['User_Table', 'nutritionist']
            ];
            
            foreach ($table_combinations as $tables) {
                try {
                    // Check if email already exists
                    $stmt = $this->pdo->prepare("SELECT user_id FROM {$tables[0]} WHERE email = ?");
                    $stmt->execute([$email]);
                    if ($stmt->fetch()) {
                        throw new Exception("Email already exists");
                    }
                    
                    // Insert into user table
                    $user_sql = "INSERT INTO {$tables[0]} (name, email, phone, contact_no, password, role, gender, dob, status, created_at) 
                                VALUES (?, ?, ?, ?, ?, 'Nutritionist', ?, ?, 'Active', NOW())";
                    
                    $user_stmt = $this->pdo->prepare($user_sql);
                    $user_result = $user_stmt->execute([$name, $email, $phone, $phone, $hashed_password, $gender, $dob]);
                    
                    if (!$user_result) {
                        continue; // Try next combination
                    }
                    
                    $user_id = $this->pdo->lastInsertId();
                    
                    // Insert into nutritionist table
                    $nutr_sql = "INSERT INTO {$tables[1]} (
                        nutritionist_id, gender, dob, nic, nic_image_path,
                        specialization, experience_years, experience, certification, 
                        qualifications, status, created_at, updated_at
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', NOW(), NOW())";
                    
                    $nutr_stmt = $this->pdo->prepare($nutr_sql);
                    $nutr_result = $nutr_stmt->execute([
                        $user_id, $gender, $dob, $nic, $nic_image_path,
                        $specialization, $experience_years, $experience_years, $certification, $qualifications
                    ]);
                    
                    if ($nutr_result) {
                        // Commit transaction
                        $this->pdo->commit();
                        error_log("Successfully added nutritionist with user_id: $user_id using tables: {$tables[0]} and {$tables[1]}");
                        return true;
                    }
                    
                } catch (PDOException $e) {
                    error_log("Failed with tables {$tables[0]} and {$tables[1]}: " . $e->getMessage());
                    continue;
                }
            }
            
            // If we get here, all combinations failed
            throw new Exception("Failed to create nutritionist with any table combination");
            
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log("Add nutritionist error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update nutritionist details - FIXED VERSION
     */
    public function updateNutritionist($nutritionist_id, $name, $email, $phone, $specialization, $experience_years, $certification = '', $qualifications = '') {
        try {
            // Start transaction
            $this->pdo->beginTransaction();
            
            $table_combinations = [
                ['user_table', 'nutritionist'],
                ['User_Table', 'Nutritionist'],
                ['user_table', 'Nutritionist'],
                ['User_Table', 'nutritionist']
            ];
            
            foreach ($table_combinations as $tables) {
                try {
                    // Update user table
                    $user_sql = "UPDATE {$tables[0]} SET name = ?, email = ?, phone = ?, contact_no = ? WHERE user_id = ?";
                    $user_stmt = $this->pdo->prepare($user_sql);
                    $user_result = $user_stmt->execute([$name, $email, $phone, $phone, $nutritionist_id]);
                    
                    if ($user_result) {
                        // Update nutritionist table
                        $nutr_sql = "UPDATE {$tables[1]} 
                                    SET specialization = ?, experience_years = ?, experience = ?, certification = ?, qualifications = ?, updated_at = NOW()
                                    WHERE nutritionist_id = ?";
                        
                        $nutr_stmt = $this->pdo->prepare($nutr_sql);
                        $nutr_result = $nutr_stmt->execute([
                            $specialization, $experience_years, $experience_years, $certification, $qualifications, $nutritionist_id
                        ]);
                        
                        if ($nutr_result) {
                            // Commit transaction
                            $this->pdo->commit();
                            error_log("Successfully updated nutritionist using tables: {$tables[0]} and {$tables[1]}");
                            return true;
                        }
                    }
                } catch (PDOException $e) {
                    error_log("Failed with tables {$tables[0]} and {$tables[1]}: " . $e->getMessage());
                    continue;
                }
            }
            
            throw new Exception("Failed to update nutritionist with any table combination");
            
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log("Update nutritionist error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get nutritionist statistics - FIXED VERSION
     */
    public function getStatistics() {
        try {
            $table_names = ['nutritionist', 'Nutritionist'];
            
            foreach ($table_names as $table) {
                try {
                    $sql = "
                        SELECT 
                            COUNT(*) as total,
                            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                            SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
                            SUM(CASE WHEN status = 'inactive' THEN 1 ELSE 0 END) as inactive
                        FROM $table
                    ";
                    $stmt = $this->pdo->query($sql);
                    $result = $stmt->fetch();
                    
                    if ($result && $result['total'] >= 0) {
                        error_log("Statistics retrieved using table: $table");
                        return $result;
                    }
                } catch (PDOException $e) {
                    error_log("Failed with table $table: " . $e->getMessage());
                    continue;
                }
            }
            
            return ['total' => 0, 'pending' => 0, 'active' => 0, 'inactive' => 0];
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
            $table_names = ['nutritionist', 'Nutritionist'];
            
            foreach ($table_names as $table) {
                $stmt = $this->pdo->query("SHOW TABLES LIKE '$table'");
                if ($stmt->rowCount() > 0) {
                    return true;
                }
            }
            
            return false;
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