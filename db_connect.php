<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Enable MySQL error reporting
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$conn = new mysqli('localhost','root','','scheduling_old') or die("Could not connect to mysql".mysqli_error($conn));

// Log database connection status
if($conn->connect_error){
    error_log("Database connection failed: " . $conn->connect_error);
} else {
    error_log("Database connected successfully");
}
?>
