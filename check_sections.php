<?php
// Include database connection
include 'db_connect.php';

echo "<h2>All Sections in Database</h2>";

// Query all sections with related info
$query = "SELECT s.*, 
          c.course as department_name, 
          st.code as strand_code, 
          st.name as strand_name 
          FROM sections s 
          LEFT JOIN courses c ON s.course_id = c.id 
          LEFT JOIN strands st ON s.strand_id = st.id 
          ORDER BY s.strand_id, s.course_id, s.year_level, s.name";

$result = $conn->query($query);

if (!$result) {
    die("Query failed: " . $conn->error);
}

if ($result->num_rows > 0) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr>
            <th>ID</th>
            <th>Section Name</th>
            <th>Year Level</th>
            <th>Department</th>
            <th>Strand</th>
          </tr>";
    
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['name'] . "</td>";
        echo "<td>" . $row['year_level'] . "</td>";
        echo "<td>" . ($row['department_name'] ?? 'N/A') . "</td>";
        echo "<td>" . ($row['strand_name'] ? $row['strand_code'] . ' - ' . $row['strand_name'] : 'N/A') . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
} else {
    echo "<p>No sections found in the database.</p>";
}

// Now let's check specifically for potential duplicates
echo "<h2>Checking for Potential Duplicates</h2>";

$check_query = "SELECT s1.id as id1, s1.name as name1, s1.year_level as year1, 
                s1.strand_id as strand_id1, s1.course_id as course_id1,
                s2.id as id2, s2.name as name2, s2.year_level as year2, 
                s2.strand_id as strand_id2, s2.course_id as course_id2,
                st1.name as strand_name1, st2.name as strand_name2,
                c1.course as course_name1, c2.course as course_name2
                FROM sections s1
                JOIN sections s2 ON s1.name = s2.name 
                    AND s1.year_level = s2.year_level 
                    AND s1.id < s2.id
                    AND ((s1.strand_id = s2.strand_id) OR (s1.course_id = s2.course_id))
                LEFT JOIN strands st1 ON s1.strand_id = st1.id
                LEFT JOIN strands st2 ON s2.strand_id = st2.id
                LEFT JOIN courses c1 ON s1.course_id = c1.id
                LEFT JOIN courses c2 ON s2.course_id = c2.id";

$dup_result = $conn->query($check_query);

if (!$dup_result) {
    die("Duplicate check query failed: " . $conn->error);
}

if ($dup_result->num_rows > 0) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr>
            <th colspan='4'>Section 1</th>
            <th colspan='4'>Section 2</th>
          </tr>";
    echo "<tr>
            <th>ID</th>
            <th>Name</th>
            <th>Year</th>
            <th>Course/Strand</th>
            <th>ID</th>
            <th>Name</th>
            <th>Year</th>
            <th>Course/Strand</th>
          </tr>";
    
    while ($row = $dup_result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id1'] . "</td>";
        echo "<td>" . $row['name1'] . "</td>";
        echo "<td>" . $row['year1'] . "</td>";
        echo "<td>" . ($row['course_name1'] ?? $row['strand_name1'] ?? 'N/A') . "</td>";
        
        echo "<td>" . $row['id2'] . "</td>";
        echo "<td>" . $row['name2'] . "</td>";
        echo "<td>" . $row['year2'] . "</td>";
        echo "<td>" . ($row['course_name2'] ?? $row['strand_name2'] ?? 'N/A') . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
} else {
    echo "<p>No duplicate sections found.</p>";
}

$conn->close();
?> 