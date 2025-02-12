<?php
include_once('../../../include/php/connect.php');


// Get start and end dates from the AJAX request
$startDate = $_POST['startDate'];
$endDate = $_POST['endDate'];

// Prepare SQL query
$query = "SELECT
    ci.jc_number,
    ci.desinger,
    ci.work,
    ci.activity,
    ci.start_date_time,
    ci.end_date_time,
    TIMEDIFF(ci.end_date_time, ci.start_date_time) AS `time_difference`,
    ci.item_amount,
    jm.client
FROM
    creative_items ci
LEFT JOIN jobcard_main jm ON jm.jc_number = ci.jc_number
WHERE
    start_date_time BETWEEN '$startDate' AND '$endDate';";

// Execute the query
$result = $conn->query($query);

// Fetch the result and convert it to an associative array
$data = array();
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

// Close the database connection
$conn->close();

// Return the result as JSON
header('Content-Type: application/json');
echo json_encode($data);
?>
