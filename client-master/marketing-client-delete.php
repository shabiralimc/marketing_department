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



if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Use a transaction to ensure atomicity
    mysqli_begin_transaction($conn);

    try {
        // Fetch client name from client_masters
        $query_fetch_client_name = "SELECT client_name FROM client_masters WHERE id = ?";
        $stmt_fetch_client_name = mysqli_prepare($conn, $query_fetch_client_name);

        if ($stmt_fetch_client_name) {
            mysqli_stmt_bind_param($stmt_fetch_client_name, "i", $id);
            mysqli_stmt_execute($stmt_fetch_client_name);
            $result_fetch_client_name = mysqli_stmt_get_result($stmt_fetch_client_name);
            $row_fetch_client_name = mysqli_fetch_assoc($result_fetch_client_name);

            $client_name = $row_fetch_client_name['client_name'];

            // Close the statement
            mysqli_stmt_close($stmt_fetch_client_name);
        } else {
            throw new Exception("Error preparing fetch client name statement");
        }

        // Delete from client_masters table
        $query_client = "DELETE FROM client_masters WHERE id = ?";
        $stmt_client = mysqli_prepare($conn, $query_client);

        if ($stmt_client) {
            mysqli_stmt_bind_param($stmt_client, "i", $id);
            $result_client = mysqli_stmt_execute($stmt_client);

            if (!$result_client) {
                throw new Exception("Failed to delete from client_masters");
            }

            // Close the client statement
            mysqli_stmt_close($stmt_client);
        } else {
            throw new Exception("Error preparing client statement");
        }

        // Delete from client_billing_masters table
        $query_billing = "DELETE FROM client_billing_masters WHERE client_name = ?";
        $stmt_billing = mysqli_prepare($conn, $query_billing);

        if ($stmt_billing) {
            mysqli_stmt_bind_param($stmt_billing, "s", $client_name);
            $result_billing = mysqli_stmt_execute($stmt_billing);

            if (!$result_billing) {
                throw new Exception("Failed to delete from client_billing_masters");
            }

            // Close the billing statement
            mysqli_stmt_close($stmt_billing);
        } else {
            throw new Exception("Error preparing billing statement");
        }

        // Commit the transaction
        mysqli_commit($conn);

        echo '<script>alert("Successfully Deleted");</script>';
        header("location:marketing-client-index.php");

    } catch (Exception $e) {
        // Rollback the transaction in case of any errors
        mysqli_rollback($conn);

        echo '<script>alert("' . $e->getMessage() . '");</script>';
        header("location:marketing-client-index.php");
    }
} else {
    echo '<script>alert("Invalid request");</script>';
    header("location:marketing-client-index.php");
}


?>
