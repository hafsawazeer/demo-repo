<?php
// Include your config
require_once 'config.php'; // Adjust path as needed

try {
    $pdo = getDBConnection();
    echo "<h2>Database Diagnosis</h2>";
    
    // 1. Check what tables exist
    echo "<h3>Available Tables:</h3>";
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "<ul>";
    foreach ($tables as $table) {
        echo "<li>$table</li>";
    }
    echo "</ul>";
    
    // 2. Check nutritionist table structure
    echo "<h3>Nutritionist Table Structure:</h3>";
    foreach (['nutritionist', 'Nutritionist'] as $table) {
        if (in_array($table, $tables)) {
            echo "<h4>Table: $table</h4>";
            $stmt = $pdo->query("DESCRIBE $table");
            $columns = $stmt->fetchAll();
            echo "<table border='1'>";
            echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
            foreach ($columns as $col) {
                echo "<tr>";
                echo "<td>{$col['Field']}</td>";
                echo "<td>{$col['Type']}</td>";
                echo "<td>{$col['Null']}</td>";
                echo "<td>{$col['Key']}</td>";
                echo "<td>{$col['Default']}</td>";
                echo "<td>{$col['Extra']}</td>";
                echo "</tr>";
            }
            echo "</table>";
            
            // Check foreign key constraints
            echo "<h5>Foreign Key Constraints:</h5>";
            $stmt = $pdo->query("
                SELECT 
                    COLUMN_NAME,
                    REFERENCED_TABLE_NAME,
                    REFERENCED_COLUMN_NAME
                FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
                WHERE TABLE_NAME = '$table' 
                AND REFERENCED_TABLE_NAME IS NOT NULL
            ");
            $fks = $stmt->fetchAll();
            if ($fks) {
                foreach ($fks as $fk) {
                    echo "<p>{$fk['COLUMN_NAME']} -> {$fk['REFERENCED_TABLE_NAME']}.{$fk['REFERENCED_COLUMN_NAME']}</p>";
                }
            } else {
                echo "<p>No foreign key constraints found</p>";
            }
        }
    }
    
    // 3. Check user table structure
    echo "<h3>User Table Structure:</h3>";
    foreach (['user_table', 'User_Table'] as $table) {
        if (in_array($table, $tables)) {
            echo "<h4>Table: $table</h4>";
            $stmt = $pdo->query("DESCRIBE $table");
            $columns = $stmt->fetchAll();
            echo "<table border='1'>";
            echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
            foreach ($columns as $col) {
                echo "<tr>";
                echo "<td>{$col['Field']}</td>";
                echo "<td>{$col['Type']}</td>";
                echo "<td>{$col['Null']}</td>";
                echo "<td>{$col['Key']}</td>";
                echo "<td>{$col['Default']}</td>";
                echo "<td>{$col['Extra']}</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    }
    
    // 4. Test a simple nutritionist query
    echo "<h3>Test Queries:</h3>";
    
    foreach (['nutritionist', 'Nutritionist'] as $n_table) {
        foreach (['user_table', 'User_Table'] as $u_table) {
            if (in_array($n_table, $tables) && in_array($u_table, $tables)) {
                echo "<h4>Testing: $n_table + $u_table</h4>";
                try {
                    $stmt = $pdo->prepare("
                        SELECT COUNT(*) as count 
                        FROM $n_table n 
                        INNER JOIN $u_table u ON n.nutritionist_id = u.user_id
                    ");
                    $stmt->execute();
                    $result = $stmt->fetch();
                    echo "<p>✅ Query successful: {$result['count']} nutritionists found</p>";
                } catch (Exception $e) {
                    echo "<p>❌ Query failed: " . $e->getMessage() . "</p>";
                }
            }
        }
    }
    
} catch (Exception $e) {
    echo "<p>Database connection failed: " . $e->getMessage() . "</p>";
}
?>