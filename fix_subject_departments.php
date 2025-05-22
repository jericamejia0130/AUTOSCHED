<?php
include 'db_connect.php';

// Set up header
echo "<!DOCTYPE html>
<html>
<head>
    <title>Fix Subject Departments</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .success { color: green; }
        .error { color: red; }
        .warning { color: orange; }
        pre { background: #f5f5f5; padding: 10px; overflow: auto; }
        table { border-collapse: collapse; width: 100%; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .button {
            display: inline-block;
            padding: 10px 15px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin-right: 10px;
        }
        .button.danger {
            background-color: #f44336;
        }
    </style>
</head>
<body>
    <h1>Subject-Department Relationship Maintenance</h1>";

// Check if we're fixing the database
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    
    if ($action === 'rebuild_table') {
        echo "<h2>Rebuilding subject_departments Table</h2>";
        
        // Drop existing table if it exists
        $conn->query("DROP TABLE IF EXISTS subject_departments");
        
        // Create the table with proper constraints
        $create_table = "CREATE TABLE `subject_departments` (
            `id` int(30) NOT NULL AUTO_INCREMENT,
            `subject_id` int(30) NOT NULL,
            `department_id` int(30) NOT NULL,
            PRIMARY KEY (`id`),
            KEY `subject_id` (`subject_id`),
            KEY `department_id` (`department_id`),
            CONSTRAINT `fk_subject_dept_department` FOREIGN KEY (`department_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_subject_dept_subject` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
        
        if ($conn->query($create_table)) {
            echo "<p class='success'>Successfully created subject_departments table.</p>";
        } else {
            echo "<p class='error'>Error creating table: " . $conn->error . "</p>";
        }
    } 
    else if ($action === 'add_default_departments') {
        echo "<h2>Adding Default Department Relationships</h2>";
        
        // Get subjects without relationships
        $subjects = $conn->query("SELECT s.* FROM subjects s 
                                 LEFT JOIN subject_departments sd ON s.id = sd.subject_id 
                                 WHERE sd.id IS NULL");
        
        if ($subjects->num_rows > 0) {
            // Get first department for default
            $dept_query = $conn->query("SELECT id FROM courses ORDER BY id ASC LIMIT 1");
            if ($dept_query->num_rows > 0) {
                $dept = $dept_query->fetch_assoc();
                $default_dept_id = $dept['id'];
                
                // Add relationships
                $success = 0;
                $errors = 0;
                
                while ($subject = $subjects->fetch_assoc()) {
                    $result = $conn->query("INSERT INTO subject_departments (subject_id, department_id) 
                                           VALUES ({$subject['id']}, $default_dept_id)");
                    if ($result) {
                        $success++;
                    } else {
                        $errors++;
                        echo "<p class='error'>Error adding relationship for subject {$subject['subject']} (ID: {$subject['id']}): " . $conn->error . "</p>";
                    }
                }
                
                echo "<p class='success'>Added $success default department relationships. Encountered $errors errors.</p>";
            } else {
                echo "<p class='warning'>No departments found in database. Please add departments first.</p>";
            }
        } else {
            echo "<p class='success'>All subjects already have department relationships.</p>";
        }
    }
    
    echo "<p><a href='fix_subject_departments.php' class='button'>Back to Summary</a></p>";
} else {
    // Show database summary
    echo "<h2>Database Status</h2>";
    
    // Check if subject_departments table exists
    $check = $conn->query("SHOW TABLES LIKE 'subject_departments'");
    if ($check->num_rows > 0) {
        echo "<p class='success'>subject_departments table exists.</p>";
        
        // Check structure
        echo "<h3>Table Structure:</h3>";
        $cols = $conn->query("DESCRIBE subject_departments");
        echo "<table><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        while ($col = $cols->fetch_assoc()) {
            echo "<tr>";
            foreach ($col as $key => $value) {
                echo "<td>" . ($value === null ? 'NULL' : $value) . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
        
        // Check foreign keys
        echo "<h3>Foreign Keys:</h3>";
        $create = $conn->query("SHOW CREATE TABLE subject_departments");
        $row = $create->fetch_assoc();
        $create_sql = $row['Create Table'];
        
        if (strpos($create_sql, 'FOREIGN KEY') !== false) {
            preg_match_all('/CONSTRAINT `([^`]+)` FOREIGN KEY \(`([^`]+)`\) REFERENCES `([^`]+)`\(`([^`]+)`\)/s', $create_sql, $matches, PREG_SET_ORDER);
            
            if (count($matches) > 0) {
                echo "<ul>";
                foreach ($matches as $match) {
                    echo "<li class='success'>{$match[1]}: {$match[2]} -> {$match[3]}.{$match[4]}</li>";
                }
                echo "</ul>";
            } else {
                echo "<p class='warning'>No foreign key constraints found in query result.</p>";
                echo "<pre>" . htmlspecialchars($create_sql) . "</pre>";
            }
        } else {
            echo "<p class='error'>No foreign key constraints found!</p>";
            echo "<pre>" . htmlspecialchars($create_sql) . "</pre>";
        }
    } else {
        echo "<p class='error'>subject_departments table does not exist!</p>";
    }
    
    // Check counts
    echo "<h3>Record Counts:</h3>";
    $subjects = $conn->query("SELECT COUNT(*) as count FROM subjects");
    $subjects_count = $subjects->fetch_assoc()['count'];
    
    $deps = $conn->query("SELECT COUNT(*) as count FROM subject_departments");
    $deps_count = $deps->fetch_assoc()['count'];
    
    $orphans = $conn->query("SELECT COUNT(*) as count FROM subjects s 
                           LEFT JOIN subject_departments sd ON s.id = sd.subject_id 
                           WHERE sd.id IS NULL");
    $orphans_count = $orphans->fetch_assoc()['count'];
    
    echo "<ul>";
    echo "<li>Total subjects: $subjects_count</li>";
    echo "<li>Total subject-department relationships: $deps_count</li>";
    echo "<li>" . ($orphans_count > 0 ? "<span class='error'>Subjects without departments: $orphans_count</span>" : "<span class='success'>All subjects have departments</span>") . "</li>";
    echo "</ul>";
    
    // Fix options
    echo "<h2>Maintenance Options</h2>";
    echo "<p><a href='fix_subject_departments.php?action=rebuild_table' class='button danger' onclick='return confirm(\"WARNING: This will drop and recreate the subject_departments table. All existing relationships will be lost. Continue?\")'>Rebuild subject_departments Table</a></p>";
    echo "<p><a href='fix_subject_departments.php?action=add_default_departments' class='button'>Add Default Department for Orphaned Subjects</a></p>";
    
    // Show sample data
    echo "<h2>Sample Data</h2>";
    
    $sample = $conn->query("SELECT s.id, s.subject, s.units, s.type, 
                          GROUP_CONCAT(c.course SEPARATOR ', ') as departments
                          FROM subjects s
                          LEFT JOIN subject_departments sd ON s.id = sd.subject_id
                          LEFT JOIN courses c ON sd.department_id = c.id
                          GROUP BY s.id
                          LIMIT 10");
    
    if ($sample && $sample->num_rows > 0) {
        echo "<table>";
        echo "<tr><th>ID</th><th>Subject</th><th>Units</th><th>Type</th><th>Departments</th></tr>";
        
        while ($row = $sample->fetch_assoc()) {
            echo "<tr>";
            echo "<td>{$row['id']}</td>";
            echo "<td>{$row['subject']}</td>";
            echo "<td>{$row['units']}</td>";
            echo "<td>{$row['type']}</td>";
            echo "<td>" . (!empty($row['departments']) ? $row['departments'] : '<span class="error">None</span>') . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        if ($sample) {
            echo "<p>No subjects found in database.</p>";
        } else {
            echo "<p class='error'>Error querying subjects: " . $conn->error . "</p>";
        }
    }
}

echo "</body></html>";
?> 