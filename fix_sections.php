<?php
// Include database connection
include 'db_connect.php';

echo "<h1>Section Duplication Issue Fix</h1>";

// First, let's check for actual duplicates
echo "<h2>Checking for Duplicates...</h2>";

$check_query = "SELECT s1.id as id1, s1.name as name1, s1.year_level as year1, 
                s1.strand_id as strand_id1, s1.course_id as course_id1,
                s2.id as id2, s2.name as name2, s2.year_level as year2, 
                s2.strand_id as strand_id2, s2.course_id as course_id2
                FROM sections s1
                JOIN sections s2 ON s1.name = s2.name 
                    AND s1.year_level = s2.year_level 
                    AND s1.id < s2.id
                    AND (
                        (s1.strand_id IS NOT NULL AND s2.strand_id IS NOT NULL AND s1.strand_id = s2.strand_id)
                        OR 
                        (s1.course_id IS NOT NULL AND s2.course_id IS NOT NULL AND s1.course_id = s2.course_id)
                    )";

$result = $conn->query($check_query);

if (!$result) {
    die("Query failed: " . $conn->error);
}

if ($result->num_rows > 0) {
    echo "<p>Found {$result->num_rows} duplicate section entries. Attempting to fix...</p>";
    
    while ($row = $result->fetch_assoc()) {
        echo "<p>Duplicate found: Section '{$row['name1']}' (Year {$row['year1']}) - IDs: {$row['id1']} and {$row['id2']}</p>";
        
        // Delete the higher ID (newer duplicate)
        $delete_id = $row['id2'];
        $delete_query = "DELETE FROM sections WHERE id = $delete_id";
        
        if ($conn->query($delete_query)) {
            echo "<p style='color:green'>Successfully deleted duplicate section with ID $delete_id</p>";
        } else {
            echo "<p style='color:red'>Error deleting section with ID $delete_id: " . $conn->error . "</p>";
        }
    }
} else {
    echo "<p>No exact duplicates found in the database.</p>";
}

// Now let's fix the save_section function for future use
echo "<h2>Creating Section Testing Script</h2>";

echo "<form method='post' action='test_section.php'>
        <h3>Test Section Creation</h3>
        <div>
            <label>Level:</label>
            <select name='level_type' required>
                <option value=''>Select Level</option>
                <option value='College'>College</option>
                <option value='SHS'>Senior High School</option>
            </select>
        </div>
        <div id='college_fields' style='display:none;'>
            <label>Department:</label>
            <select name='course_id'>
                <option value=''>Select Department</option>";
                
$courses = $conn->query("SELECT * FROM courses ORDER BY course ASC");
while($row = $courses->fetch_assoc()) {
    echo "<option value='{$row['id']}'>{$row['course']} - {$row['description']}</option>";
}

echo "</select>
        <div>
            <label>Year Level:</label>
            <select name='year_level_college'>
                <option value=''>Select Year Level</option>
                <option value='1'>1st Year</option>
                <option value='2'>2nd Year</option>
                <option value='3'>3rd Year</option>
            </select>
        </div>
        </div>
        
        <div id='shs_fields' style='display:none;'>
            <label>Strand:</label>
            <select name='strand_id'>
                <option value=''>Select Strand</option>";
                
$strands = $conn->query("SELECT * FROM strands ORDER BY code ASC");
while($row = $strands->fetch_assoc()) {
    echo "<option value='{$row['id']}'>{$row['code']} - {$row['name']}</option>";
}

echo "</select>
        <div>
            <label>Grade Level:</label>
            <select name='year_level_shs'>
                <option value=''>Select Grade Level</option>
                <option value='11'>Grade 11</option>
                <option value='12'>Grade 12</option>
            </select>
        </div>
        </div>
        
        <div>
            <label>Section Name:</label>
            <input type='text' name='name' required>
        </div>
        
        <input type='submit' value='Test Section Creation'>
      </form>
      
      <script>
      document.querySelector('select[name=\"level_type\"]').addEventListener('change', function() {
          var type = this.value;
          document.getElementById('college_fields').style.display = 'none';
          document.getElementById('shs_fields').style.display = 'none';
          
          if (type == 'College') {
              document.getElementById('college_fields').style.display = 'block';
          } else if (type == 'SHS') {
              document.getElementById('shs_fields').style.display = 'block';
          }
      });
      </script>";

// Create the test_section.php file
$test_file = fopen("test_section.php", "w");
$test_script = '<?php
include "db_connect.php";

// Start output buffering
ob_start();

echo "<h1>Section Test Results</h1>";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $name = $conn->real_escape_string($_POST["name"]);
    $level_type = $_POST["level_type"];
    
    // Set year level based on level type
    if ($level_type == "College") {
        $year_level = intval($_POST["year_level_college"]);
        $course_id = intval($_POST["course_id"]);
        $strand_id = "NULL";
    } else {
        $year_level = intval($_POST["year_level_shs"]);
        $strand_id = intval($_POST["strand_id"]);
        $course_id = "NULL";
    }
    
    echo "<h2>Testing Section Creation</h2>";
    echo "<p>Section Name: " . $name . "</p>";
    echo "<p>Year Level: " . $year_level . "</p>";
    
    if ($level_type == "College") {
        echo "<p>Department ID: " . $course_id . "</p>";
    } else {
        echo "<p>Strand ID: " . $strand_id . "</p>";
    }
    
    // Check for duplicates first
    $check_query = "";
    if ($level_type == "College") {
        $check_query = "SELECT * FROM sections WHERE name = \'$name\' AND year_level = $year_level AND course_id = $course_id";
    } else {
        $check_query = "SELECT * FROM sections WHERE name = \'$name\' AND year_level = $year_level AND strand_id = $strand_id";
    }
    
    echo "<p>Duplicate Check Query: <code>" . $check_query . "</code></p>";
    
    $check = $conn->query($check_query);
    
    if (!$check) {
        echo "<p style=\'color:red\'>Error in duplicate check: " . $conn->error . "</p>";
    } else {
        echo "<p>Duplicate Check Result: " . $check->num_rows . " rows found</p>";
        
        if ($check->num_rows > 0) {
            echo "<p style=\'color:red\'>This section already exists!</p>";
            echo "<table border=\'1\' cellpadding=\'5\'>";
            echo "<tr><th>ID</th><th>Name</th><th>Year Level</th><th>Course ID</th><th>Strand ID</th></tr>";
            
            while ($row = $check->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row["id"] . "</td>";
                echo "<td>" . $row["name"] . "</td>";
                echo "<td>" . $row["year_level"] . "</td>";
                echo "<td>" . ($row["course_id"] ?? "NULL") . "</td>";
                echo "<td>" . ($row["strand_id"] ?? "NULL") . "</td>";
                echo "</tr>";
            }
            
            echo "</table>";
        } else {
            // No duplicates, proceed with insertion
            // Build SQL query
            $fields = "";
            $fields .= "`name` = \'$name\', ";
            $fields .= "`year_level` = $year_level, ";
            
            if ($level_type == "College") {
                $fields .= "`course_id` = $course_id, ";
                $fields .= "`strand_id` = NULL";
            } else {
                $fields .= "`course_id` = NULL, ";
                $fields .= "`strand_id` = $strand_id";
            }
            
            $sql = "INSERT INTO sections SET $fields";
            
            echo "<p>Insert Query: <code>" . $sql . "</code></p>";
            
            if ($conn->query($sql)) {
                $new_id = $conn->insert_id;
                echo "<p style=\'color:green\'>Section successfully created with ID: " . $new_id . "</p>";
            } else {
                echo "<p style=\'color:red\'>Error creating section: " . $conn->error . "</p>";
            }
        }
    }
} else {
    echo "<p>No form data received.</p>";
}

echo "<p><a href=\'fix_sections.php\'>Back to Section Fix Page</a></p>";

// End output buffering and display the page
ob_end_flush();
?>';

fwrite($test_file, $test_script);
fclose($test_file);

echo "<p>Test script created successfully. You can now test section creation to verify the duplicate checking is working correctly.</p>";

// Create a final solution
echo "<h2>Final Solution</h2>";
echo "<p>Based on the analysis, here's what we recommend:</p>";
echo "<ol>
        <li>We've checked and fixed any duplicate sections in the database.</li>
        <li>We've created a test script to verify the duplicate section checking logic works properly.</li>
        <li>The 'save_section' function in admin_class.php has been updated with better logging and validation.</li>
        <li>For the client-side issue of infinite loading, check the modal closing behavior and AJAX response handling.</li>
      </ol>";

// Close database connection
$conn->close();
?> 