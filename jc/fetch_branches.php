<?php
// Include the database connection configuration
include_once("../../../include/php/connect.php");

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


// Check if the client parameter is set in the POST request
if (isset($_POST['client'])) {
    $selectedClient = $_POST['client'];

    // Fetch branch names for the selected client from client_billing_masters
    $sql = "SELECT branch_name FROM client_billing_masters WHERE client_name = '$selectedClient'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // If there are one or more branches, create a dropdown
        echo '<label for="branch" class="form-label">Billing/Branch Name<span class="text-danger">*</span></label>';
        echo '<select name="branch" id="branch" class="form-control" required>';

         // Add a default option
         echo '<option value="" disabled selected>-- Select Billing Name --</option>';
        while ($row = $result->fetch_assoc()) {
            echo '<option value="' . $row['branch_name'] . '">' . $row['branch_name'] . '</option>';
        }
        echo '</select>';
    } else {
        // If there is only one branch, create an input field
        echo '<label for="branch" class="form-label">Billing/Branch Name</label><br>';
        echo '<input type="text" name="branch" id="branch" class="form-control" value="'.$_POST['client'].'" required>';
    }
} else {
    // If the client parameter is not set, display an error message
    echo 'Error: Client not specified';
}

// Close the database connection
$conn->close();
?>
