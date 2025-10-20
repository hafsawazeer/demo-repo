<?php
/**
 * Test file to verify CRUD functionality
 * Run this file to test database connections and basic operations
 */

// Include required files
require_once 'config.php';
require_once 'SupervisorController.php';

// Set error reporting for testing
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>FitVerse Nutritionist Management - Functionality Test</h1>";

// Test 1: Database Connection
echo "<h2>Test 1: Database Connection</h2>";
try {
    $pdo = getDBConnection();
    echo "✅ Database connection successful<br>";
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "<br>";
    exit;
}

// Test 2: Check if tables exist
echo "<h2>Test 2: Database Tables</h2>";
try {
    $tables = ['user_table', 'nutritionist'];
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "✅ Table '$table' exists<br>";
        } else {
            echo "❌ Table '$table' does not exist<br>";
        }
    }
} catch (Exception $e) {
    echo "❌ Error checking tables: " . $e->getMessage() . "<br>";
}

// Test 3: Get Nutritionists (Read operation)
echo "<h2>Test 3: Read Operation - Get Nutritionists</h2>";
try {
    $nutritionists = SupervisorController::getNutritionists();
    echo "✅ Successfully retrieved " . count($nutritionists) . " nutritionists<br>";
    
    if (count($nutritionists) > 0) {
        echo "<strong>Sample nutritionist data:</strong><br>";
        $sample = $nutritionists[0];
        echo "- ID: " . ($sample['nutritionist_id'] ?? 'N/A') . "<br>";
        echo "- Name: " . ($sample['name'] ?? 'N/A') . "<br>";
        echo "- Email: " . ($sample['email'] ?? 'N/A') . "<br>";
        echo "- Status: " . ($sample['status'] ?? 'N/A') . "<br>";
    } else {
        echo "ℹ️ No nutritionists found in database<br>";
    }
} catch (Exception $e) {
    echo "❌ Error getting nutritionists: " . $e->getMessage() . "<br>";
}

// Test 4: Get Statistics
echo "<h2>Test 4: Statistics</h2>";
try {
    $stats = SupervisorController::getNutritionistStatistics();
    echo "✅ Statistics retrieved successfully:<br>";
    echo "- Total: " . ($stats['total'] ?? 0) . "<br>";
    echo "- Active: " . ($stats['active'] ?? 0) . "<br>";
    echo "- Pending: " . ($stats['pending'] ?? 0) . "<br>";
    echo "- Inactive: " . ($stats['inactive'] ?? 0) . "<br>";
} catch (Exception $e) {
    echo "❌ Error getting statistics: " . $e->getMessage() . "<br>";
}

// Test 5: Search functionality
echo "<h2>Test 5: Search Functionality</h2>";
try {
    $search_results = SupervisorController::getNutritionists('test', 'name_asc');
    echo "✅ Search functionality working - found " . count($search_results) . " results for 'test'<br>";
} catch (Exception $e) {
    echo "❌ Error testing search: " . $e->getMessage() . "<br>";
}

// Test 6: File upload directory check
echo "<h2>Test 6: File Upload Directories</h2>";
$upload_dirs = ['uploads', 'uploads/nic_images', 'uploads/certifications', 'uploads/profile_images'];
foreach ($upload_dirs as $dir) {
    if (is_dir($dir)) {
        if (is_writable($dir)) {
            echo "✅ Directory '$dir' exists and is writable<br>";
        } else {
            echo "⚠️ Directory '$dir' exists but is not writable<br>";
        }
    } else {
        echo "❌ Directory '$dir' does not exist<br>";
        // Try to create it
        if (mkdir($dir, 0755, true)) {
            echo "✅ Created directory '$dir'<br>";
        } else {
            echo "❌ Failed to create directory '$dir'<br>";
        }
    }
}

// Test 7: Sample data insertion (optional)
echo "<h2>Test 7: Sample Data Test</h2>";
echo "ℹ️ To test add functionality, use the web interface<br>";
echo "ℹ️ Sample supervisor login: supervisor@fitverse.com / supervisor123<br>";

// Test 8: Configuration check
echo "<h2>Test 8: Configuration Check</h2>";
echo "✅ Database Host: " . DB_HOST . "<br>";
echo "✅ Database Name: " . DB_NAME . "<br>";
echo "✅ Max File Size: " . (MAX_FILE_SIZE / 1024 / 1024) . "MB<br>";
echo "✅ Debug Mode: " . (DEBUG_MODE ? 'Enabled' : 'Disabled') . "<br>";

echo "<h2>Test Summary</h2>";
echo "🎉 Basic functionality tests completed!<br>";
echo "📝 Next steps:<br>";
echo "1. Access manageNutritionist.php in your browser<br>";
echo "2. Test adding a new nutritionist<br>";
echo "3. Test editing and deleting operations<br>";
echo "4. Verify file uploads work properly<br>";

?>

<style>
body {
    font-family: Arial, sans-serif;
    max-width: 800px;
    margin: 0 auto;
    padding: 20px;
    line-height: 1.6;
}
h1 {
    color: #E67E22;
    border-bottom: 2px solid #E67E22;
    padding-bottom: 10px;
}
h2 {
    color: #2c3e50;
    margin-top: 30px;
}
</style>