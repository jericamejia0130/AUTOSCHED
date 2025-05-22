<?php
include 'db_connect.php';

echo "<h2>Testing Section Validation</h2>";

// Get a list of sections for testing
$sections = $conn->query("SELECT id, name, section, course_id, strand_id, year_level, section_code FROM sections LIMIT 5");
if($sections && $sections->num_rows > 0) {
    echo "<h3>Available Sections for Testing</h3>";
    echo "<table border='1'><tr><th>ID</th><th>Name</th><th>Section</th><th>Course ID</th><th>Strand ID</th><th>Year Level</th><th>Section Code</th></tr>";
    
    while($row = $sections->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['name'] . "</td>";
        echo "<td>" . $row['section'] . "</td>";
        echo "<td>" . $row['course_id'] . "</td>";
        echo "<td>" . $row['strand_id'] . "</td>";
        echo "<td>" . $row['year_level'] . "</td>";
        echo "<td>" . $row['section_code'] . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
}

// Create a test form for verification
echo "<h3>Test Section Code Verification</h3>";
echo "<form method='post'>";
echo "Section ID: <input type='text' name='section_id'><br>";
echo "Section Code: <input type='text' name='section_code'><br>";
echo "Type: <select name='type'><option value='college'>College</option><option value='shs'>SHS</option></select><br>";
echo "<input type='submit' name='test' value='Test Verification'>";
echo "</form>";

// Handle form submission
if(isset($_POST['test'])) {
    $section_id = intval($_POST['section_id']);
    $section_code = $_POST['section_code'];
    $type = $_POST['type'];
    
    echo "<h3>Testing validation with:</h3>";
    echo "Section ID: " . $section_id . "<br>";
    echo "Section Code: " . $section_code . "<br>";
    echo "Type: " . $type . "<br>";
    
    // Replicate the verification logic
    try {
        // Check if section exists and has a code that matches
        $query = $conn->prepare("SELECT * FROM sections WHERE id = ?");
        $query->bind_param("i", $section_id);
        $query->execute();
        $result = $query->get_result();
        
        if($result->num_rows == 0) {
            echo "<div style='color:red'>Error: Section not found</div>";
        } else {
            $section = $result->fetch_assoc();
            
            // Actual section code validation
            if(!isset($section['section_code']) || $section['section_code'] == '') {
                // If section has no code yet (temporary case for initial setup)
                // Use section ID as temporary password for testing
                $valid = ($section_code === 'code' . $section_id);
                echo "Using fallback temporary code: 'code" . $section_id . "'<br>";
            } else {
                // Normal case: validate against stored code
                $valid = ($section_code === $section['section_code']);
                echo "Using stored section code: '" . $section['section_code'] . "'<br>";
            }
            
            if($valid) {
                echo "<div style='color:green'>Success: Section code is valid!</div>";
                
                // Debug session
                echo "<h4>Session would contain:</h4>";
                echo "student_section_id: " . $section_id . "<br>";
                echo "student_type: " . $type . "<br>";
                
                if($type == 'college') {
                    echo "student_department_id: " . $section['course_id'] . "<br>";
                    echo "student_year_level: " . $section['year_level'] . "<br>";
                } else { // shs
                    echo "student_strand_id: " . $section['strand_id'] . "<br>";
                    echo "student_grade_level: " . $section['year_level'] . "<br>";
                }
            } else {
                echo "<div style='color:red'>Error: Invalid section code</div>";
            }
        }
    } catch(Exception $e) {
        echo "<div style='color:red'>Error: " . $e->getMessage() . "</div>";
    }
}
?> 