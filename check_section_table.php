<?php
include 'db_connect.php';

// Get table structure
echo "<h2>Table Structure</h2>";
$result = $conn->query("DESCRIBE sections");
if ($result) {
    echo "<table border='1'><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "<td>" . $row['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "Error: " . $conn->error;
}

// Get sample data
echo "<h2>Sample Data</h2>";
$result = $conn->query("SELECT * FROM sections LIMIT 3");
if ($result && $result->num_rows > 0) {
    $first_row = $result->fetch_assoc();
    echo "<pre>";
    print_r($first_row);
    echo "</pre>";
    
    // Reset
    $result->data_seek(0);
    
    echo "<table border='1'><tr>";
    foreach (array_keys($first_row) as $key) {
        echo "<th>" . $key . "</th>";
    }
    echo "</tr>";
    
    $result->data_seek(0);
    while($row = $result->fetch_assoc()) {
        echo "<tr>";
        foreach ($row as $value) {
            echo "<td>" . htmlspecialchars($value ?? 'NULL') . "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "No sections found or error: " . $conn->error;
}
?> 