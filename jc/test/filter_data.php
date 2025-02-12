<?php

include_once('../../../../include/php/connect.php');

// Get start and end date from form submission
$start_date = $_POST['start_date'];
$end_date = $_POST['end_date'];

// MySQL query to filter data
$sql = "SELECT ci.id, ci.jc_number, ci.desinger, ci.work, ci.activity, ci.start_date_time, ci.end_date_time, ci.item_amount, cm.client,
        TIMESTAMPDIFF(SECOND, ci.start_date_time, ci.end_date_time) AS time_difference
        FROM creative_items ci
        LEFT JOIN jobcard_main cm ON ci.jc_number = cm.jc_number
        WHERE ci.start_date_time BETWEEN '$start_date' AND '$end_date'";

$result = $conn->query($sql);

// Display the result in a DataTable
if ($result->num_rows > 0) {
    echo '<table id="data_table" border="1">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>JC Number</th>
                    <th>Designer</th>
                    <th>Work</th>
                    <th>Activity</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Item Amount</th>
                    <th>Client</th>
                    <th>Time Difference</th>
                </tr>
            </thead>
            <tbody>';

    while ($row = $result->fetch_assoc()) {
        echo '<tr>
                <td>' . $row['id'] . '</td>
                <td>' . $row['jc_number'] . '</td>
                <td>' . $row['designer'] . '</td>
                <td>' . $row['work'] . '</td>
                <td>' . $row['activity'] . '</td>
                <td>' . $row['start_date_time'] . '</td>
                <td>' . $row['end_date_time'] . '</td>
                <td>' . $row['item_amount'] . '</td>
                <td>' . $row['client'] . '</td>
                <td>' . $row['time_difference'] . ' seconds</td>
              </tr>';
    }

    echo '</tbody></table>';
} else {
    echo "No records found";
}

$conn->close();
?>
