<?php
include_once('../../../include/php/connect.php');
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['user']) || $_SESSION['role'] !== '3') {
    echo "<script>alert('You are not authorised to view the URL - Please login using your username and password before accessing URL...'); window.location = '$app_url';</script>";
    exit();
}

// Calculate the remaining time
$sessionStart = $_SESSION['session_start'];
$sessionLifetime = $_SESSION['session_lifetime'];
$currentTime = time();
$remainingTime = ($sessionStart + $sessionLifetime) - $currentTime;

// Check if branch_name is provided in the POST request
if (isset($_POST['branch_name'])) {
    $branchName = $_POST['branch_name'];

    // Prepare and execute the SQL query to delete the row
    $sql_delete = "DELETE FROM client_billing_masters WHERE branch_name = ?";
    $stmt_delete = $conn->prepare($sql_delete);
    $stmt_delete->bind_param("s", $branchName);

    if ($stmt_delete->execute()) {
        // Return a success message if deletion is successful
        echo "Row deleted successfully on the server.";
    } else {
        // Return an error message if deletion fails
        echo "Error deleting row on the server: " . $stmt_delete->error;
    }

    $stmt_delete->close();
} else {
    // Return an error message if branch_name is not provided
    echo "Error: branch_name not provided.";
}

// Close the database connection
$conn->close();
?>
