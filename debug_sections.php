<?php
include 'db_connect.php';

// Enable all error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>SHS Sections Database Debug</h2>";

// Direct query to get all sections
$query = "SELECT * FROM sections ORDER BY id DESC";
$result = $conn->query($query);

if (!$result) {
    echo "<p>Error running query: " . $conn->error . "</p>";
} else {
    echo "<p>Total sections found: " . $result->num_rows . "</p>";
    
    if ($result->num_rows > 0) {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>Name</th><th>Year Level</th><th>Course ID</th><th>Strand ID</th></tr>";
        
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['id'] . "</td>";
            echo "<td>" . $row['name'] . "</td>";
            echo "<td>" . $row['year_level'] . "</td>";
            echo "<td>" . $row['course_id'] . "</td>";
            echo "<td>" . $row['strand_id'] . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        echo "<p>No sections found in the database.</p>";
    }
}

// Specific query to check SHS sections
echo "<h2>SHS Sections Only</h2>";
$shs_query = "SELECT s.*, st.code as strand_code, st.name as strand_name 
             FROM sections s 
             LEFT JOIN strands st ON s.strand_id = st.id 
             WHERE s.year_level IN (11,12)
             ORDER BY s.id DESC";
$shs_result = $conn->query($shs_query);

if (!$shs_result) {
    echo "<p>Error running SHS query: " . $conn->error . "</p>";
} else {
    echo "<p>SHS sections found: " . $shs_result->num_rows . "</p>";
    
    if ($shs_result->num_rows > 0) {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>Name</th><th>Year Level</th><th>Strand Code</th><th>Strand Name</th></tr>";
        
        while ($row = $shs_result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['id'] . "</td>";
            echo "<td>" . $row['name'] . "</td>";
            echo "<td>" . $row['year_level'] . "</td>";
            echo "<td>" . ($row['strand_code'] ? $row['strand_code'] : 'Missing') . "</td>";
            echo "<td>" . ($row['strand_name'] ? $row['strand_name'] : 'Missing') . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        echo "<p>No SHS sections found in the database.</p>";
    }
}
?> 