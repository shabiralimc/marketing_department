<?php
include_once('connect.php');
session_start(); // Start the session (make sure you have this at the beginning of your PHP script)
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

if (isset($_GET['jc_number'])) {
    $jc_number = $_GET['jc_number'];
// Query to retrieve user information from jobcard_main
$sql_main = "SELECT jc_number,quotation_number,bill_on,jc_date,quotation_date,peyment_terms,client,instructed_by,proposed_rate,s_location,completion_before,involvements,user FROM jobcard_main WHERE jc_number = ?";

// Prepare the statement
$stmt_main = mysqli_prepare($conn, $sql_main);

// Bind the jc_number parameter
mysqli_stmt_bind_param($stmt_main, "s", $jc_number);

// Execute the statement
mysqli_stmt_execute($stmt_main);

// Get the result
$result_main = mysqli_stmt_get_result($stmt_main);

// Fetch the user's information

// Query to retrieve related records from jobcard_items
$sql_items = "SELECT s_description,width,height,unit,qty,amount FROM jobcard_items WHERE jc_number = ?";

// Prepare the statement
$stmt_items = mysqli_prepare($conn, $sql_items);

// Bind the jc_number parameter
mysqli_stmt_bind_param($stmt_items, "s", $jc_number);

// Execute the statement
mysqli_stmt_execute($stmt_items);

// Get the result
$result_items = mysqli_stmt_get_result($stmt_items);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet"type="text/css" href="print.css"media="print">
    
</head>
<body>
        
<?php 

if ($result_main) { ?>

<table border="1">
    <thead>
        <tr>
            <th>JC Number</th>
            <th>Quotation Number</th>
            <th>Bill On</th>
            <th>JC Date</th>
            <th>Quotation Date</th>
            <th>Payment Terms</th>
            <th>Client</th>
            <th>Instructed By</th>
            <th>Proposed Rate</th>
            <th>Location</th>
            <th>Completion Before</th>
            <th>Involvements</th>
        </tr>
    </thead>
    <tbody>
    <?php
    // Fetch the username inside the loop
    while ($user_info = mysqli_fetch_assoc($result_main)) {
    
?>
            <tr>
            <td><?php echo $user_info['jc_number']; ?></td>
    <td><?php echo $user_info['quotation_number']; ?></td>
    <td><?php echo $user_info['bill_on']; ?></td>
    <td><?php echo $user_info['jc_date']; ?></td>
    <td><?php echo $user_info['quotation_date']; ?></td>
    <td><?php echo $user_info['peyment_terms']; ?></td>
    <td><?php echo $user_info['client']; ?></td>
    <td><?php echo $user_info['instructed_by']; ?></td>
    <td><?php echo $user_info['proposed_rate']; ?></td>
    <td><?php echo $user_info['s_location']; ?></td>
    <td><?php echo $user_info['completion_before']; ?></td>
    <td><?php echo $user_info['involvements']; ?></td>
                
            </tr>
            <?php } ?>

         </tbody>
</table>
<?php } else {
        echo "User not found for JC Number: " . $jc_number;
    } ?>
        <?php if ($result_items) { ?>
            <br>
            <table border="1">
        <thead>
            <tr>
            <th>Description</th>
            <th>Width</th>
            <th>Height</th>
            <th>Unit</th>
            <th>Quantity</th>
            <th>Amount</th>
            </tr>
            </thead>
        <tbody>
            <?php while ($row = mysqli_fetch_assoc($result_items)) { ?>
                <tr>
                <td><?php echo $row['s_description']; ?></td>
                <td><?php echo $row['width']; ?></td>
                <td><?php echo $row['height']; ?></td>
                <td><?php echo $row['unit']; ?></td>
                <td><?php echo $row['qty']; ?></td>
                <td><?php echo $row['amount']; ?></td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
    <?php } else {
        echo "No related records found for JC Number: " . $jc_number;
    } 
    ?>
    <center>
        <button  class="btn btn-primary"type="button"name="print"id="print"id="print-btn"onclick="window.print();">Print</a></button>
        <button class="btn btn-danger"><a href="jobcard.php">Close</button>
    </center>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
</body>
</html>

    