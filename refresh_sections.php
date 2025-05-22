<?php
// This is a debugging page to check sections directly in the database
include 'db_connect.php';

// Enable all error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Get all sections
echo "<h3>All Sections in Database</h3>";
$all_sections = $conn->query("SELECT * FROM sections ORDER BY id DESC");

if (!$all_sections) {
    echo "<p>Error querying sections: " . $conn->error . "</p>";
} else {
    echo "<p>Total sections found: " . $all_sections->num_rows . "</p>";
    
    if ($all_sections->num_rows > 0) {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>Name</th><th>Year Level</th><th>Course ID</th><th>Strand ID</th></tr>";
        
        while ($row = $all_sections->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['id'] . "</td>";
            echo "<td>" . $row['name'] . "</td>";
            echo "<td>" . $row['year_level'] . "</td>";
            echo "<td>" . ($row['course_id'] ? $row['course_id'] : 'NULL') . "</td>";
            echo "<td>" . ($row['strand_id'] ? $row['strand_id'] : 'NULL') . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        echo "<p>No sections found in the database.</p>";
    }
}

// Get SHS sections with strand information
echo "<h3>SHS Sections Query</h3>";
$shs_query = "SELECT s.*, st.code as strand_code, st.name as strand_name 
             FROM sections s 
             LEFT JOIN strands st ON s.strand_id = st.id 
             WHERE s.year_level IN (11,12)
             ORDER BY st.name ASC, s.year_level ASC, s.name ASC";

echo "<p>SQL Query: " . $shs_query . "</p>";

$shs_sections = $conn->query($shs_query);

if (!$shs_sections) {
    echo "<p>Error querying SHS sections: " . $conn->error . "</p>";
} else {
    echo "<p>SHS sections found: " . $shs_sections->num_rows . "</p>";
    
    if ($shs_sections->num_rows > 0) {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>Name</th><th>Year Level</th><th>Strand ID</th><th>Strand Code</th><th>Strand Name</th></tr>";
        
        while ($row = $shs_sections->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['id'] . "</td>";
            echo "<td>" . $row['name'] . "</td>";
            echo "<td>" . $row['year_level'] . "</td>";
            echo "<td>" . ($row['strand_id'] ? $row['strand_id'] : 'NULL') . "</td>";
            echo "<td>" . ($row['strand_code'] ? $row['strand_code'] : 'NULL') . "</td>";
            echo "<td>" . ($row['strand_name'] ? $row['strand_name'] : 'NULL') . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        echo "<p>No SHS sections found with the JOIN query.</p>";
    }
}

// Check for orphaned sections (sections with non-existent strand IDs)
echo "<h3>Checking for Orphaned SHS Sections</h3>";
$orphaned_query = "SELECT s.* FROM sections s 
                  LEFT JOIN strands st ON s.strand_id = st.id 
                  WHERE s.year_level IN (11,12) AND st.id IS NULL AND s.strand_id IS NOT NULL";

$orphaned = $conn->query($orphaned_query);

if (!$orphaned) {
    echo "<p>Error checking orphaned sections: " . $conn->error . "</p>";
} else {
    echo "<p>Orphaned SHS sections found: " . $orphaned->num_rows . "</p>";
    
    if ($orphaned->num_rows > 0) {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>Name</th><th>Year Level</th><th>Invalid Strand ID</th></tr>";
        
        while ($row = $orphaned->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['id'] . "</td>";
            echo "<td>" . $row['name'] . "</td>";
            echo "<td>" . $row['year_level'] . "</td>";
            echo "<td>" . $row['strand_id'] . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
        
        echo "<p>These sections reference strand IDs that don't exist in the strands table.</p>";
    } else {
        echo "<p>No orphaned sections found.</p>";
    }
}

// Check strands table
echo "<h3>Available Strands</h3>";
$strands = $conn->query("SELECT * FROM strands");

if (!$strands) {
    echo "<p>Error querying strands: " . $conn->error . "</p>";
} else {
    echo "<p>Total strands found: " . $strands->num_rows . "</p>";
    
    if ($strands->num_rows > 0) {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>Code</th><th>Name</th></tr>";
        
        while ($row = $strands->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['id'] . "</td>";
            echo "<td>" . $row['code'] . "</td>";
            echo "<td>" . $row['name'] . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        echo "<p>No strands found in the database.</p>";
    }
}
?> 