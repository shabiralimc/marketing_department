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


$jc_number = $_GET['jc_number']; // Replace with the way you retrieve the jc_number

// Query to retrieve user information from jobcard_main
$sql_main = "SELECT * FROM jobcard_main jcm 
LEFT JOIN creative_main cm ON jcm.jc_number = cm.jc_number
LEFT JOIN production_main pm ON jcm.jc_number = pm.jc_number
WHERE jcm.jc_number LIKE '%$jc_number%'";

$result = mysqli_query($conn, $sql_main);




// Query to retrieve related records from jobcard_items
$sql_items = "SELECT desinger,work,activity,start_date_time,end_date_time,item_amount FROM creative_items WHERE jc_number = ?";

// Prepare the statement
$stmt_items = mysqli_prepare($conn, $sql_items);

// Bind the jc_number parameter
mysqli_stmt_bind_param($stmt_items, "s", $jc_number);

// Execute the statement
mysqli_stmt_execute($stmt_items);

// Get the result
$result_items = mysqli_stmt_get_result($stmt_items);




echo "JC NUMBER : ".$jc_number.'<br>';

while ($row = mysqli_fetch_assoc($result)) {
  echo "JC Date : ".$row['jc_date'].'<br>';
  echo "Client Name : ".$row['client'].'<br>';    
  echo "Billing Name : ".$row['branch_name'].'<br>';
  echo "CSR : ".$row['csr'].'<br>';
  echo "Quotation Number : ".$row['quotation_number'].'<br>';
  echo "Quotation Date : ".$row['quotation_date'].'<br>';
  echo "Bill On: ".$row['bill_on'].'<br>';  
  echo "Payment Terms : ".$row['peyment_terms'].'<br>';
  echo "Instructed By : ".$row['instructed_by'].'<br>';
  echo "Proposed Rate : ".$row['proposed_rate'].'<br>';
  echo "Location : ".$row['s_location'].'<br>';
  echo "Completion Before : ".$row['completion_before'].'<br>';
  echo "Remark : ".$row['now_remark'].'<br>';
  echo '------------CREATIVE---------- <br>';
  echo "Start Date : ".$row['s_start_date'].'<br>';
  echo "Ref No. : ".$row['ref_no'].'<br>';
  echo "End Date : ".$row['end_date'].'<br>';
  echo "Ref Date : ".$row['ref_date'].'<br>';
  echo "Status of Work : ".$row['s_status'].'<br>';
  echo "Number of Corrections : ".$row['corrections'].'<br>';
  echo "Medium : ".$row['medium'].'<br>';
  echo "Attached files : ".$row['files'].'<br>';
}



?>


<style>
        table {
            border-collapse: collapse;
            width: 100%;
        }
        th, td {
            border: 1px solid #dddddd;
            text-align: left;
            padding: 8px;
        }
        th {
            background-color: #f2f2f2;
        }
        tfoot {
            font-weight: bold;
        }
    </style>

<p>WORK INVOLVEMENT</p>

<?php
    

    // Fetch data
    $result = $conn->query("SELECT jc_number, desinger, work, activity, start_date_time, end_date_time, item_amount,
        SEC_TO_TIME(TIMESTAMPDIFF(SECOND, start_date_time, end_date_time)) AS total_time_consumed
        FROM creative_items
        WHERE jc_number = '$jc_number'");

    // Fetch total data
    $totalResult = $conn->query("SELECT jc_number, 'Total' AS desinger, '' AS work, '' AS activity, '' AS start_date_time, '' AS end_date_time,
        SUM(item_amount) AS total_item_amount,
        SEC_TO_TIME(SUM(TIMESTAMPDIFF(SECOND, start_date_time, end_date_time))) AS total_time_consumed
        FROM creative_items
        WHERE jc_number = '$jc_number'");

    // Display the result as a table
    echo '<table>';
    echo '<thead>
            <tr>
                <th>JC Number</th>
                <th>Designer</th>
                <th>Work</th>
                <th>Activity</th>
                <th>Start Date & Time</th>
                <th>End Date & Time</th>
                <th>Item Amount</th>
                <th>Total Time Consumed</th>
            </tr>
          </thead>';
    echo '<tbody>';

    // Display individual rows
    while ($row = $result->fetch_assoc()) {
        echo '<tr>';
        echo '<td>' . $row['jc_number'] . '</td>';
        echo '<td>' . $row['desinger'] . '</td>';
        echo '<td>' . $row['work'] . '</td>';
        echo '<td>' . $row['activity'] . '</td>';
        echo '<td>' . $row['start_date_time'] . '</td>';
        echo '<td>' . $row['end_date_time'] . '</td>';
        echo '<td>' . $row['item_amount'] . '</td>';
        echo '<td>' . $row['total_time_consumed'] . '</td>';
        echo '</tr>';
    }

    echo '</tbody>';
    
    // Display total row
    echo '<tfoot>';
    while ($row = $totalResult->fetch_assoc()) {
        echo '<tr>';
        echo '<td>' . $row['jc_number'] . '</td>';
        echo '<td>' . $row['desinger'] . '</td>';
        echo '<td>' . $row['work'] . '</td>';
        echo '<td>' . $row['activity'] . '</td>';
        echo '<td>' . $row['start_date_time'] . '</td>';
        echo '<td>' . $row['end_date_time'] . '</td>';
        echo '<td>' . $row['total_item_amount'] . '</td>';
        echo '<td>' . $row['total_time_consumed'] . '</td>';
        echo '</tr>';
    }
    echo '</tfoot>';

    echo '</table>';
?>