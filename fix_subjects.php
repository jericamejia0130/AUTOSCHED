<?php
include 'db_connect.php';

// Check if we're fixing the database
if (isset($_GET['fix']) && $_GET['fix'] == 'true') {
    echo "<h1>Fixing Subject-Department Relationships</h1>";
    
    // Check if subject_departments table has the correct structure
    $check = $conn->query("SHOW TABLES LIKE 'subject_departments'");
    if ($check->num_rows == 0) {
        echo "<p>Creating subject_departments table...</p>";
        $conn->query("CREATE TABLE IF NOT EXISTS `subject_departments` (
            `id` int(30) NOT NULL AUTO_INCREMENT,
            `subject_id` int(30) NOT NULL,
            `department_id` int(30) NOT NULL,
            PRIMARY KEY (`id`),
            KEY `subject_id` (`subject_id`),
            KEY `department_id` (`department_id`),
            CONSTRAINT `fk_subject_dept_department` FOREIGN KEY (`department_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_subject_dept_subject` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");
        
        if ($conn->error) {
            echo "<p>Error creating table: " . $conn->error . "</p>";
        } else {
            echo "<p>Table created successfully!</p>";
        }
    } else {
        echo "<p>Table already exists.</p>";
    }
    
    // Check existing relationships
    $check = $conn->query("SELECT COUNT(*) as count FROM subject_departments");
    $row = $check->fetch_assoc();
    echo "<p>Current subject_departments entries: " . $row['count'] . "</p>";
    
    // Check for subjects without relationships
    $subjects = $conn->query("SELECT s.* FROM subjects s 
                              LEFT JOIN subject_departments sd ON s.id = sd.subject_id 
                              WHERE sd.id IS NULL");
    
    if ($subjects->num_rows > 0) {
        echo "<p>Found " . $subjects->num_rows . " subjects without department relationships. Adding default relationships...</p>";
        
        // Get first department ID for defaults
        $dept_query = $conn->query("SELECT id FROM courses ORDER BY id ASC LIMIT 1");
        if ($dept_query->num_rows > 0) {
            $dept = $dept_query->fetch_assoc();
            $default_dept_id = $dept['id'];
            
            // Add default relationships
            $success = 0;
            $errors = 0;
            
            while ($subject = $subjects->fetch_assoc()) {
                $result = $conn->query("INSERT INTO subject_departments (subject_id, department_id) 
                                      VALUES ({$subject['id']}, $default_dept_id)");
                if ($result) {
                    $success++;
                } else {
                    $errors++;
                    echo "<p>Error adding relationship for subject ID {$subject['id']}: " . $conn->error . "</p>";
                }
            }
            
            echo "<p>Added $success relationships successfully. Encountered $errors errors.</p>";
        } else {
            echo "<p>No departments found in the database!</p>";
        }
    } else {
        echo "<p>All subjects have department relationships.</p>";
    }
    
    echo "<p><a href='fix_subjects.php'>Back to Summary</a></p>";
    echo "<p><a href='subjects.php'>Go to Subjects Page</a></p>";
    
} else {
    // Display summary of database state
    echo "<h1>Database Summary</h1>";
    
    // Check subjects
    $subjects = $conn->query("SELECT COUNT(*) as count FROM subjects");
    $row = $subjects->fetch_assoc();
    echo "<p>Total subjects: " . $row['count'] . "</p>";
    
    // Check departments (courses)
    $depts = $conn->query("SELECT COUNT(*) as count FROM courses");
    $row = $depts->fetch_assoc();
    echo "<p>Total departments: " . $row['count'] . "</p>";
    
    // Check subject_departments
    $rel = $conn->query("SELECT COUNT(*) as count FROM subject_departments");
    $row = $rel->fetch_assoc();
    echo "<p>Total subject-department relationships: " . $row['count'] . "</p>";
    
    // Check subjects without relationships
    $orphans = $conn->query("SELECT COUNT(*) as count FROM subjects s 
                            LEFT JOIN subject_departments sd ON s.id = sd.subject_id 
                            WHERE sd.id IS NULL");
    $row = $orphans->fetch_assoc();
    echo "<p>Subjects without department relationships: " . $row['count'] . "</p>";
    
    // Show action buttons
    echo "<p><a href='fix_subjects.php?fix=true' style='color:red;'>Fix Database Issues</a></p>";
    echo "<p><a href='subjects.php'>Go to Subjects Page</a></p>";
    
    // Show sample of subjects with their departments
    echo "<h2>Sample Subjects with Departments</h2>";
    $sample = $conn->query("SELECT s.*, GROUP_CONCAT(c.course SEPARATOR ', ') as departments
                           FROM subjects s
                           LEFT JOIN subject_departments sd ON s.id = sd.subject_id
                           LEFT JOIN courses c ON sd.department_id = c.id
                           GROUP BY s.id
                           LIMIT 10");
    
    if ($sample->num_rows > 0) {
        echo "<table border='1'>";
        echo "<tr><th>ID</th><th>Subject</th><th>Units</th><th>Type</th><th>Departments</th></tr>";
        
        while ($row = $sample->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['id'] . "</td>";
            echo "<td>" . $row['subject'] . "</td>";
            echo "<td>" . $row['units'] . "</td>";
            echo "<td>" . $row['type'] . "</td>";
            echo "<td>" . ($row['departments'] ? $row['departments'] : 'None') . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        echo "<p>No subjects found!</p>";
    }
}
?> 