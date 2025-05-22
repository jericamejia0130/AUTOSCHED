<?php
include 'db_connect.php';

// Set headers for clean output
header('Content-Type: text/plain');

echo "=== DATABASE STRUCTURE CHECK ===\n\n";

// Check subject_departments table
echo "1. Checking subject_departments table:\n";
$table_check = $conn->query("SHOW TABLES LIKE 'subject_departments'");
if ($table_check->num_rows > 0) {
    echo "✓ Table exists\n";
    
    // Check columns
    echo "\nColumns:\n";
    $columns = $conn->query("DESCRIBE subject_departments");
    while ($col = $columns->fetch_assoc()) {
        echo "- {$col['Field']} ({$col['Type']}) " . ($col['Key'] ? "[{$col['Key']}]" : "") . "\n";
    }
    
    // Check constraints
    echo "\nConstraints:\n";
    $create_table = $conn->query("SHOW CREATE TABLE subject_departments");
    $row = $create_table->fetch_assoc();
    $create_sql = $row['Create Table'];
    
    if (strpos($create_sql, 'FOREIGN KEY') !== false) {
        preg_match_all('/CONSTRAINT `([^`]+)` FOREIGN KEY \(`([^`]+)`\) REFERENCES `([^`]+)`\(`([^`]+)`\)(.*?)(?:,|\))/s', $create_sql, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            echo "- {$match[1]}: {$match[2]} -> {$match[3]}.{$match[4]}{$match[5]}\n";
        }
    } else {
        echo "- No constraints found\n";
    }
    
    // Count records
    $count = $conn->query("SELECT COUNT(*) as total FROM subject_departments");
    $row = $count->fetch_assoc();
    echo "\nTotal records: {$row['total']}\n";
    
    // Show sample data
    echo "\nSample data (up to 5 records):\n";
    $sample = $conn->query("SELECT * FROM subject_departments LIMIT 5");
    if ($sample->num_rows > 0) {
        while ($row = $sample->fetch_assoc()) {
            echo "- ID: {$row['id']}, Subject ID: {$row['subject_id']}, Department ID: {$row['department_id']}\n";
        }
    } else {
        echo "- No data found\n";
    }
} else {
    echo "✗ Table does not exist!\n";
}

// Check subjects table
echo "\n\n2. Checking subjects table:\n";
$subjects = $conn->query("SELECT * FROM subjects LIMIT 5");
if ($subjects->num_rows > 0) {
    echo "✓ Table exists with data\n\n";
    echo "Sample subjects:\n";
    while ($row = $subjects->fetch_assoc()) {
        echo "- ID: {$row['id']}, Subject: {$row['subject']}, Units: {$row['units']}, Type: {$row['type']}\n";
        
        // Check related departments
        $deps = $conn->query("SELECT c.course FROM subject_departments sd 
                              JOIN courses c ON sd.department_id = c.id 
                              WHERE sd.subject_id = {$row['id']}");
        
        if ($deps->num_rows > 0) {
            echo "  Departments: ";
            $depts = [];
            while ($dept = $deps->fetch_assoc()) {
                $depts[] = $dept['course'];
            }
            echo implode(", ", $depts) . "\n";
        } else {
            echo "  Departments: None\n";
        }
    }
} else {
    echo "✗ No subjects found or table does not exist!\n";
}

// Test inserting a subject
echo "\n\n3. Testing subject insert with department:\n";
// Enable error reporting for transaction
$conn->query("SET AUTOCOMMIT=0");

try {
    // Start transaction
    $conn->begin_transaction();
    
    // Insert a test subject
    $test_subject = "TEST_" . time();
    $conn->query("INSERT INTO subjects (subject, units, type) VALUES ('$test_subject', 3, 'Minor')");
    $new_id = $conn->insert_id;
    
    if ($new_id) {
        echo "✓ Inserted test subject (ID: $new_id)\n";
        
        // Insert department relationship
        $result = $conn->query("INSERT INTO subject_departments (subject_id, department_id) VALUES ($new_id, 43)");
        
        if ($result) {
            echo "✓ Added department relationship\n";
            
            // Verify
            $verify = $conn->query("SELECT sd.*, c.course FROM subject_departments sd 
                                  JOIN courses c ON sd.department_id = c.id 
                                  WHERE sd.subject_id = $new_id");
            
            if ($verify->num_rows > 0) {
                $dept = $verify->fetch_assoc();
                echo "✓ Verified: Found relationship with department {$dept['course']} (ID: {$dept['department_id']})\n";
            } else {
                echo "✗ Failed to verify relationship!\n";
            }
        } else {
            echo "✗ Failed to add department relationship: " . $conn->error . "\n";
        }
    } else {
        echo "✗ Failed to insert test subject: " . $conn->error . "\n";
    }
    
    // Rollback to clean up
    $conn->rollback();
    echo "\n✓ Test transaction rolled back\n";
} catch (Exception $e) {
    $conn->rollback();
    echo "✗ Error during test: " . $e->getMessage() . "\n";
}

// Check for any database errors
echo "\n\nDatabase error (if any): " . $conn->error . "\n";
?> 