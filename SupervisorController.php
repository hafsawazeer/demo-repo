<?php
require_once __DIR__ . '/../models/NutritionistModel.php';

class SupervisorController {
    
    /**
     * Get all nutritionists with search and sort functionality
     */
    public static function getNutritionists($search = '', $sort = 'created_at_desc') {
        $model = new NutritionistModel();
        return $model->getNutritionists($search, $sort);
    }
    
    /**
     * Get a single nutritionist by ID
     */
    public static function getNutritionistById($nutritionist_id) {
        $model = new NutritionistModel();
        return $model->getNutritionistById($nutritionist_id);
    }
    
    /**
     * Get nutritionist statistics
     */
    public static function getNutritionistStatistics() {
        $model = new NutritionistModel();
        return $model->getStatistics();
    }
    
    /**
     * Add a new nutritionist
     */
    public static function addNutritionist($data) {
        try {
            // Validate required fields
            $required_fields = ['name', 'email', 'phone', 'password', 'confirm_password', 'gender', 'dob', 'nic', 'specialization', 'experience_years'];
            
            foreach ($required_fields as $field) {
                if (empty($data[$field])) {
                    return ['success' => false, 'message' => "Field '$field' is required."];
                }
            }
            
            // Validate password confirmation
            if ($data['password'] !== $data['confirm_password']) {
                return ['success' => false, 'message' => 'Passwords do not match.'];
            }
            
            // Validate password strength
            if (strlen($data['password']) < 6) {
                return ['success' => false, 'message' => 'Password must be at least 6 characters long.'];
            }
            
            // Validate email format
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                return ['success' => false, 'message' => 'Please enter a valid email address.'];
            }
            
            // Validate NIC format
            if (!preg_match('/^[0-9]{9}[vVxX]$|^[0-9]{12}$/', $data['nic'])) {
                return ['success' => false, 'message' => 'Please enter a valid NIC number.'];
            }
            
            // Validate experience years
            if (!is_numeric($data['experience_years']) || $data['experience_years'] < 0) {
                return ['success' => false, 'message' => 'Experience years must be a valid number.'];
            }
            
            // Handle file upload for NIC image
            $nic_image_path = '';
            if (isset($_FILES['nic_image']) && $_FILES['nic_image']['error'] === UPLOAD_ERR_OK) {
                $upload_result = self::handleFileUpload($_FILES['nic_image'], 'nic_images');
                if ($upload_result['success']) {
                    $nic_image_path = $upload_result['path'];
                } else {
                    return ['success' => false, 'message' => $upload_result['message']];
                }
            }
            
            // Check if email already exists
            $model = new NutritionistModel();
            if (self::emailExists($data['email'])) {
                return ['success' => false, 'message' => 'Email address already exists.'];
            }
            
            // Add nutritionist
            $result = $model->addNutritionist(
                $data['name'],
                $data['email'],
                $data['phone'],
                $data['password'],
                $data['gender'],
                $data['dob'],
                $data['nic'],
                $data['specialization'],
                $data['experience_years'],
                $data['certification'] ?? '',
                $data['qualifications'] ?? '',
                $nic_image_path
            );
            
            if ($result) {
                return ['success' => true, 'message' => 'Nutritionist added successfully!'];
            } else {
                return ['success' => false, 'message' => 'Failed to add nutritionist. Please try again.'];
            }
            
        } catch (Exception $e) {
            error_log('Add nutritionist error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred while adding the nutritionist.'];
        }
    }
    
    /**
     * Edit an existing nutritionist
     */
    public static function editNutritionist($data) {
        try {
            // Validate required fields
            $required_fields = ['nutritionist_id', 'name', 'email', 'phone', 'specialization', 'experience_years'];
            
            foreach ($required_fields as $field) {
                if (empty($data[$field])) {
                    return ['success' => false, 'message' => "Field '$field' is required."];
                }
            }
            
            // Validate email format
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                return ['success' => false, 'message' => 'Please enter a valid email address.'];
            }
            
            // Validate experience years
            if (!is_numeric($data['experience_years']) || $data['experience_years'] < 0) {
                return ['success' => false, 'message' => 'Experience years must be a valid number.'];
            }
            
            // Check if email already exists for other nutritionists
            if (self::emailExistsForOther($data['email'], $data['nutritionist_id'])) {
                return ['success' => false, 'message' => 'Email address already exists for another nutritionist.'];
            }
            
            // Update nutritionist
            $model = new NutritionistModel();
            $result = $model->updateNutritionist(
                $data['nutritionist_id'],
                $data['name'],
                $data['email'],
                $data['phone'],
                $data['specialization'],
                $data['experience_years'],
                $data['certification'] ?? '',
                $data['qualifications'] ?? ''
            );
            
            if ($result) {
                return ['success' => true, 'message' => 'Nutritionist updated successfully!'];
            } else {
                return ['success' => false, 'message' => 'Failed to update nutritionist. Please try again.'];
            }
            
        } catch (Exception $e) {
            error_log('Edit nutritionist error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred while updating the nutritionist.'];
        }
    }
    
    /**
     * Delete a nutritionist
     */
    public static function deleteNutritionist($nutritionist_id) {
        try {
            if (empty($nutritionist_id)) {
                return ['success' => false, 'message' => 'Invalid nutritionist ID.'];
            }
            
            $model = new NutritionistModel();
            $result = $model->deleteNutritionist($nutritionist_id);
            
            if ($result) {
                return ['success' => true, 'message' => 'Nutritionist deleted successfully!'];
            } else {
                return ['success' => false, 'message' => 'Failed to delete nutritionist. Please try again.'];
            }
            
        } catch (Exception $e) {
            error_log('Delete nutritionist error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred while deleting the nutritionist.'];
        }
    }
    
    /**
     * Update nutritionist status
     */
    public static function updateNutritionistStatus($nutritionist_id, $status) {
        try {
            if (empty($nutritionist_id) || empty($status)) {
                return ['success' => false, 'message' => 'Invalid nutritionist ID or status.'];
            }
            
            // Validate status
            $valid_statuses = ['pending', 'active', 'inactive'];
            if (!in_array($status, $valid_statuses)) {
                return ['success' => false, 'message' => 'Invalid status value.'];
            }
            
            $model = new NutritionistModel();
            $result = $model->updateStatus($nutritionist_id, $status);
            
            if ($result) {
                return ['success' => true, 'message' => 'Status updated successfully!'];
            } else {
                return ['success' => false, 'message' => 'Failed to update status. Please try again.'];
            }
            
        } catch (Exception $e) {
            error_log('Update status error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred while updating the status.'];
        }
    }
    
    /**
     * Handle file upload
     */
    private static function handleFileUpload($file, $upload_dir) {
        try {
            // Validate file
            if ($file['error'] !== UPLOAD_ERR_OK) {
                return ['success' => false, 'message' => 'File upload error.'];
            }
            
            // Check file size (5MB max)
            if ($file['size'] > 5 * 1024 * 1024) {
                return ['success' => false, 'message' => 'File size must be less than 5MB.'];
            }
            
            // Check file type
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
            if (!in_array($file['type'], $allowed_types)) {
                return ['success' => false, 'message' => 'Only JPG, PNG, GIF, and PDF files are allowed.'];
            }
            
            // Create upload directory if it doesn't exist
            $upload_path = __DIR__ . '/../../uploads/' . $upload_dir;
            if (!is_dir($upload_path)) {
                mkdir($upload_path, 0755, true);
            }
            
            // Generate unique filename
            $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '_' . time() . '.' . $file_extension;
            $full_path = $upload_path . '/' . $filename;
            
            // Move uploaded file
            if (move_uploaded_file($file['tmp_name'], $full_path)) {
                return ['success' => true, 'path' => 'uploads/' . $upload_dir . '/' . $filename];
            } else {
                return ['success' => false, 'message' => 'Failed to save uploaded file.'];
            }
            
        } catch (Exception $e) {
            error_log('File upload error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred during file upload.'];
        }
    }
    
    /**
     * Check if email already exists
     */
    private static function emailExists($email) {
        try {
            require_once __DIR__ . '/../config/config.php';
            $pdo = getDBConnection();
            
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM user_table WHERE email = ?");
            $stmt->execute([$email]);
            
            return $stmt->fetchColumn() > 0;
            
        } catch (Exception $e) {
            error_log('Email check error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Check if email exists for other nutritionists (excluding current one)
     */
    private static function emailExistsForOther($email, $nutritionist_id) {
        try {
            require_once __DIR__ . '/../config/config.php';
            $pdo = getDBConnection();
            
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM user_table WHERE email = ? AND user_id != ?");
            $stmt->execute([$email, $nutritionist_id]);
            
            return $stmt->fetchColumn() > 0;
            
        } catch (Exception $e) {
            error_log('Email check error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get trainers (if needed for trainer management)
     */
    public static function getTrainers($search = '', $sort = 'created_at_desc') {
        // This would be similar to getNutritionists but for trainers
        // You can implement this if you need trainer management as well
        return [];
    }
}
?>