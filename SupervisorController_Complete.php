<?php
require_once __DIR__ . '/../models/NutritionistModel.php';
require_once __DIR__ . '/../models/careerRegModel.php';

class SupervisorController {
    
    /**
     * Get all nutritionists with search and sorting - FIXED VERSION
     */
    public static function getNutritionists($search = '', $sort = 'created_at_desc') {
        try {
            $model = new NutritionistModel();
            return $model->getNutritionists($search, $sort);
        } catch (Exception $e) {
            error_log("Error getting nutritionists: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get nutritionist by ID
     */
    public static function getNutritionistById($nutritionist_id) {
        try {
            $model = new NutritionistModel();
            return $model->getNutritionistById($nutritionist_id);
        } catch (Exception $e) {
            error_log("Error getting nutritionist by ID: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Add new nutritionist - FIXED VERSION
     */
    public static function addNutritionist($data) {
        try {
            // Validate required fields
            $required_fields = ['name', 'email', 'phone', 'password', 'gender', 'dob', 'nic', 'specialization', 'experience'];
            
            foreach ($required_fields as $field) {
                if (empty($data[$field])) {
                    return [
                        'success' => false,
                        'message' => "Please fill in all required fields. Missing: " . ucfirst($field)
                    ];
                }
            }
            
            // Validate email format
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                return [
                    'success' => false,
                    'message' => 'Please enter a valid email address'
                ];
            }
            
            // Validate password confirmation if provided
            if (isset($data['confirm_password']) && $data['password'] !== $data['confirm_password']) {
                return [
                    'success' => false,
                    'message' => 'Passwords do not match'
                ];
            }
            
            // Check if email already exists
            if (CareerRegModel::emailExists($data['email'])) {
                return [
                    'success' => false,
                    'message' => 'Email address already exists'
                ];
            }
            
            // Check if NIC already exists
            if (CareerRegModel::nicExists($data['nic'])) {
                return [
                    'success' => false,
                    'message' => 'NIC number already registered'
                ];
            }
            
            // Handle file upload for NIC image
            $nic_image_path = '';
            if (isset($_FILES['nic_image']) && $_FILES['nic_image']['error'] === UPLOAD_ERR_OK) {
                $nic_image_path = self::handleNicImageUpload($_FILES['nic_image']);
                if (!$nic_image_path) {
                    return [
                        'success' => false,
                        'message' => 'Failed to upload NIC image'
                    ];
                }
            }
            
            $model = new NutritionistModel();
            
            // Map experience field correctly
            $experience_years = is_numeric($data['experience']) ? intval($data['experience']) : 0;
            
            $result = $model->addNutritionist(
                trim($data['name']),
                trim($data['email']),
                trim($data['phone']),
                $data['password'],
                $data['gender'],
                $data['dob'],
                trim($data['nic']),
                trim($data['specialization']),
                $experience_years,
                trim($data['certification'] ?? ''),
                trim($data['qualifications'] ?? ''),
                $nic_image_path
            );
            
            if ($result) {
                return [
                    'success' => true,
                    'message' => 'Nutritionist added successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to add nutritionist. Please check if email already exists.'
                ];
            }
            
        } catch (Exception $e) {
            error_log("Error adding nutritionist: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Edit nutritionist - FIXED VERSION
     */
    public static function editNutritionist($data) {
        try {
            // Validate required fields
            $required_fields = ['nutritionist_id', 'name', 'email', 'phone', 'specialization', 'experience'];
            
            foreach ($required_fields as $field) {
                if (empty($data[$field])) {
                    return [
                        'success' => false,
                        'message' => "Please fill in all required fields. Missing: " . ucfirst($field)
                    ];
                }
            }
            
            // Validate email format
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                return [
                    'success' => false,
                    'message' => 'Please enter a valid email address'
                ];
            }
            
            $model = new NutritionistModel();
            
            // Map experience field correctly
            $experience_years = is_numeric($data['experience']) ? intval($data['experience']) : 0;
            
            $result = $model->updateNutritionist(
                intval($data['nutritionist_id']),
                trim($data['name']),
                trim($data['email']),
                trim($data['phone']),
                trim($data['specialization']),
                $experience_years,
                trim($data['certification'] ?? ''),
                trim($data['qualifications'] ?? '')
            );
            
            if ($result) {
                return [
                    'success' => true,
                    'message' => 'Nutritionist updated successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to update nutritionist'
                ];
            }
            
        } catch (Exception $e) {
            error_log("Error editing nutritionist: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Update nutritionist status - FIXED VERSION
     */
    public static function updateNutritionistStatus($nutritionist_id, $status) {
        try {
            // Validate status
            $valid_statuses = ['pending', 'active', 'inactive'];
            if (!in_array($status, $valid_statuses)) {
                return [
                    'success' => false,
                    'message' => 'Invalid status value'
                ];
            }
            
            $model = new NutritionistModel();
            $result = $model->updateStatus(intval($nutritionist_id), $status);
            
            if ($result) {
                return [
                    'success' => true,
                    'message' => 'Status updated successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to update status'
                ];
            }
            
        } catch (Exception $e) {
            error_log("Error updating nutritionist status: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Delete nutritionist
     */
    public static function deleteNutritionist($nutritionist_id) {
        try {
            $model = new NutritionistModel();
            $result = $model->deleteNutritionist(intval($nutritionist_id));
            
            if ($result) {
                return [
                    'success' => true,
                    'message' => 'Nutritionist deleted successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to delete nutritionist'
                ];
            }
            
        } catch (Exception $e) {
            error_log("Error deleting nutritionist: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get nutritionist statistics - FIXED VERSION
     */
    public static function getNutritionistStatistics() {
        try {
            $model = new NutritionistModel();
            return $model->getStatistics();
        } catch (Exception $e) {
            error_log("Error getting nutritionist statistics: " . $e->getMessage());
            return [
                'total' => 0,
                'pending' => 0,
                'active' => 0,
                'inactive' => 0
            ];
        }
    }
    
    /**
     * Handle NIC image upload
     */
    private static function handleNicImageUpload($file) {
        try {
            // Validate file
            $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'];
            if (!in_array($file['type'], $allowed_types)) {
                error_log("Invalid file type: " . $file['type']);
                return false;
            }
            
            // Check file size (max 5MB)
            if ($file['size'] > 5 * 1024 * 1024) {
                error_log("File too large: " . $file['size']);
                return false;
            }
            
            // Create upload directory if it doesn't exist
            $upload_dir = __DIR__ . '/../uploads/professionals/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            // Generate unique filename
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'cert_' . uniqid() . '.' . $extension;
            $filepath = $upload_dir . $filename;
            
            // Move uploaded file
            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                return 'uploads/professionals/' . $filename;
            } else {
                error_log("Failed to move uploaded file");
                return false;
            }
            
        } catch (Exception $e) {
            error_log("Error handling NIC image upload: " . $e->getMessage());
            return false;
        }
    }
}
?>