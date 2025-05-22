<?php
include 'db_connect.php';

// Display current subjects
echo "<h3>Current Subjects:</h3>";
$subjects = $conn->query("SELECT * FROM subjects ORDER BY id ASC");
echo "<table border='1'><tr><th>ID</th><th>Subject</th><th>Units</th><th>Type</th></tr>";
while($row = $subjects->fetch_assoc()) {
    echo "<tr><td>".$row['id']."</td><td>".$row['subject']."</td><td>".$row['units']."</td><td>".$row['type']."</td></tr>";
}
echo "</table>";

// Display current subject_departments relationships
echo "<h3>Current Subject-Department Relationships:</h3>";
$subject_deps = $conn->query("SELECT sd.*, s.subject, c.course 
                              FROM subject_departments sd
                              JOIN subjects s ON sd.subject_id = s.id
                              JOIN courses c ON sd.department_id = c.id
                              ORDER BY sd.subject_id ASC");

if($subject_deps) {
    echo "<table border='1'><tr><th>ID</th><th>Subject ID</th><th>Subject</th><th>Department ID</th><th>Department</th></tr>";
    while($row = $subject_deps->fetch_assoc()) {
        echo "<tr><td>".$row['id']."</td><td>".$row['subject_id']."</td><td>".$row['subject']."</td><td>".$row['department_id']."</td><td>".$row['course']."</td></tr>";
    }
    echo "</table>";
} else {
    echo "Error querying subject_departments: " . $conn->error;
}

// Test adding a new subject with department
echo "<h3>Test Adding New Subject with Department:</h3>";
echo "<form method='post'>";
echo "Subject: <input type='text' name='subject' value='Test Subject'><br>";
echo "Units: <input type='number' name='units' value='3'><br>";
echo "Type: <select name='type'><option value='Major'>Major</option><option value='Minor'>Minor</option></select><br>";
echo "Department: <select name='department_id'>";
$depts = $conn->query("SELECT * FROM courses ORDER BY course ASC");
while($row = $depts->fetch_assoc()) {
    echo "<option value='".$row['id']."'>".$row['course']."</option>";
}
echo "</select><br>";
echo "<input type='submit' name='add_subject' value='Add Subject'>";
echo "</form>";

// Process adding a new subject
if(isset($_POST['add_subject'])) {
    $subject = $_POST['subject'];
    $units = $_POST['units'];
    $type = $_POST['type'];
    $department_id = $_POST['department_id'];
    
    try {
        // Begin transaction
        $conn->begin_transaction();
        
        // Insert new subject
        $stmt = $conn->prepare("INSERT INTO subjects (subject, units, type) VALUES (?, ?, ?)");
        $stmt->bind_param("sis", $subject, $units, $type);
        $save = $stmt->execute();
        $new_id = $conn->insert_id;
        
        // Add department relationship
        $result = $conn->query("INSERT INTO subject_departments (subject_id, department_id) VALUES ('$new_id', '$department_id')");
        
        if($result) {
            // Commit if everything worked
            $conn->commit();
            echo "<p style='color:green'>Subject saved successfully</p>";
        } else {
            throw new Exception("Error adding department relationship: " . $conn->error);
        }
    } catch (Exception $e) {
        // Roll back if there was an error
        $conn->rollback();
        echo "<p style='color:red'>Error: " . $e->getMessage() . "</p>";
    }
    
    // Refresh the page to show updated data
    echo "<script>window.location.href = window.location.href;</script>";
}
?> 