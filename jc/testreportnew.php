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


$jc_number = $_GET['jc_number'];

// Query to retrieve user information from jobcard_main
$sql_main = "SELECT * FROM jobcard_main jcm 
LEFT JOIN creative_main cm ON jcm.jc_number = cm.jc_number
LEFT JOIN production_main pm ON jcm.jc_number = pm.jc_number
WHERE jcm.jc_number LIKE '%$jc_number%'";

$result_main = mysqli_query($conn, $sql_main);


?>


<!-- Include Header File -->
<?php include_once ('../../../include/php/header.php') ?>

<!-- Include Sidebar File -->
<?php include_once ('../../../include/php/sidebar-marketing.php') ?>


<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1 class="m-0">VIEW JOBCARD</h1>
        </div>
        <!-- /.col -->
      </div>
      <!-- /.row -->
    </div>
    <!-- /.container-fluid -->
  </div>
  <!-- /.content-header -->
  <!-- Main content -->
  <section class="content">
    <div class="container-fluid">
      <div class="card card-info card-outline">
        <div class="card-body">
          <div class="row">
            <div class="col-md-12">
              <!-- PRINT SECTION -->
              <div id="print">
              	<div class="row">
			        		<div class="col-md-6">
			        			<img style="width: 100px;" src="<?php echo $app_url; ?>/dist/img/logo-full.png" alt="Chakra Logo" class="brand-image">
			        		</div>
		        			<div class="col-md-6">
		        				<div class="card card-primary">
		        					<div class="card-header">
		        						<h3 style="text-align:center;">COMPLETION DETAILS FOR <?php echo $jc_number; ?></h3>
		        					</div>
		        				</div>
		        			</div>
		        		</div>
		        		<div class="row">
							    <div class="col-md-12">

								<?php 

								if (mysqli_num_rows($result_main) > 0) {
								    // Output data of each row
								    while($row = mysqli_fetch_assoc($result_main)) {
								        echo '
			        			<table class="table table-bordered table-striped">
		        					<tr>
		        						<td style="width:25%;">Client Name</td>
		        						<td style="width:25%;">'.$row['client'].'</td>
		        						<td style="width:25%;">Pre-Verification</td>
		        						<td style="width:25%;">'.$row['pre_varification'].'</td>							        						
		        					</tr>
		        					<tr>
		        						<td style="width:25%;">Billing Name</td>
		        						<td style="width:25%;">'.$row['branch_name'].'</td>
		        						<td style="width:25%;">Involvements</td>
		        						<td style="width:25%;">'.$row['involvements'].'</td>
		        					</tr>
		        					<tr>
		        						<td style="width:25%;">CSR</td>
		        						<td style="width:25%;">'.$row['csr'].'</td>
		        						<td style="width:25%;">Remarks</td>
		        						<td style="width:25%;">'.$row['now_remark'].'</td>
		        					</tr>
		        					<tr>
		        						<td style="width:25%;">Quotation No.</td>
		        						<td style="width:25%;">'.$row['quotation_number'].'</td>
		        						<td style="width:25%;">Is Billable</td>
		        						<td style="width:25%;">'.$row['jc_billable'].'</td>
		        					</tr>
		        					<tr>
		        						<td style="width:25%;">Quotation Date</td>
		        						<td style="width:25%;">'.$row['quotation_date'].'</td>
		        						<td style="width:25%;">Completion Before</td>
		        						<td style="width:25%;">'.$row['completion_before'].'</td>
		        					</tr>
		        				</table>
								        ';
								    }
								} else {
								    echo "No Jobcard Data Available";
								}

								?>
									</div>
								</div>

								<div class="row">
									<div class="col-md-12">
										<div class="card card-primary">
											<div class="card-header">
												<h3 class="card-title">CREATIVE INVOLVEMENT</h3>
											</div>
										</div>
									</div>
								</div>

								<div class="row">
									<div class="col-md-12">
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
									    echo '<table class="table table-bordered table-striped">';
									    echo '<thead style="text-align:center;">
									            <tr>
								                <th>Designer</th>
								                <th>Work</th>
								                <th>Activity</th>
								                <th>Start Date & Time</th>
								                <th>End Date & Time</th>
								                <th>Item Amount</th>
									            </tr>
									          </thead>';
									    echo '<tbody style="text-align:center;">';

									    // Display individual rows
									    while ($row = $result->fetch_assoc()) {
									        echo '<tr>';
									        echo '<td>' . $row['desinger'] . '</td>';
									        echo '<td>' . $row['work'] . '</td>';
									        echo '<td>' . $row['activity'] . '</td>';
									        echo '<td>' . $row['start_date_time'] . '</td>';
									        echo '<td>' . $row['end_date_time'] . '</td>';
									        echo '<td>₹ ' . $row['item_amount'] . '</td>';
									        echo '</tr>';
									    }

									    echo '</tbody>';
									    
									    // Display total row
									    echo '<tfoot style="text-align:center;">';
									    while ($row = $totalResult->fetch_assoc()) {
									        echo '<tr>';
									        echo '<td colspan="5" style="text-align:right; font-size:18px; font-weight:800;">TOTAL Proposed Design Charges</td>';
									        echo '<td style="font-size:18px; font-weight:800;">₹ ' . $row['total_item_amount'] . '</td>';
									        echo '</tr>';
									    }
									    echo '</tfoot>';

									    echo '</table>';
									?>
									</div>
								</div>

								<div class="row">
									<div class="col-md-12">
										<div class="card card-primary">
											<div class="card-header">
												<h3 class="card-title">PRODUCTION INVOLVEMENT</h3>
											</div>
										</div>
									</div>
								</div>

								<div class="row">
									<div class="col-md-12">
										<?php

									    // Fetch data
									    $result = $conn->query("SELECT po_number, invoice_number, invoice_date, amount, freight, addl_expences, total_expences FROM production_invoice_main WHERE jc_number = '$jc_number'");

									    // Fetch total data
									    $totalResult = $conn->query("SELECT SUM(amount) AS total_amount, SUM(freight) AS total_frieght, SUM(addl_expences) AS total_addl_expences, SUM(total_expences) AS total_total_expences FROM production_invoice_main WHERE jc_number = '$jc_number'");

									    // Display the result as a table
									    echo '<table class="table table-bordered table-striped">';
									    echo '<thead style="text-align:center;">
									            <tr>
								                <th>PO Number</th>
								                <th>Invoice Number</th>
								                <th>Invoice Date</th>
								                <th>Amount</th>
								                <th>Frieght Charges</th>
								                <th>Additional Expences</th>								                
								                <th>Total Expences</th>
									            </tr>
									          </thead>';
									    echo '<tbody style="text-align:center;">';

									    // Display individual rows
									    while ($row = $result->fetch_assoc()) {
									        echo '<tr>';
									        echo '<td>' . $row['po_number'] . '</td>';
									        echo '<td>' . $row['invoice_number'] . '</td>';
									        echo '<td>' . $row['invoice_date'] . '</td>';
									        echo '<td>₹ ' . $row['amount'] . '</td>';
									        echo '<td>₹ ' . $row['freight'] . '</td>';
									        echo '<td>₹ ' . $row['addl_expences'] . '</td>';
									        echo '<td>₹ ' . $row['total_expences'] . '</td>';
									        echo '</tr>';
									    }

									    echo '</tbody>';

									    // Display total row
									    echo '<tfoot style="text-align:center;">';
									    while ($row = $totalResult->fetch_assoc()) {
									        echo '<tr>';
									        echo '<td colspan="3" style="text-align:right; font-size:18px; font-weight:800;">TOTAL PRODUCTION EXPENCES</td>';
									        echo '<td style="font-size:18px; font-weight:800;">₹ ' . $row['total_amount'] . '</td>';
									        echo '<td style="font-size:18px; font-weight:800;">₹ ' . $row['total_frieght'] . '</td>';
									        echo '<td style="font-size:18px; font-weight:800;">₹ ' . $row['total_addl_expences'] . '</td>';
									        echo '<td style="font-size:18px; font-weight:800;">₹ ' . $row['total_total_expences'] . '</td>';
									        echo '</tr>';
									    }
									    echo '</tfoot>';

									    echo '</table>';
									?>
									</div>
								</div>
							</div>
							<!-- End of Print Section -->

							<center style="margin-top: 50px;">
                <div class="row">
                <div class="col-sm-3"></div>
                <div class="col-sm-3"><a class="btn btn-info btn-block" href="javascript:void(0);" onclick="printPageArea('print')"><i class="fa fa-print"></i> Print</a></div>
                <div class="col-sm-3"><a class="btn btn-danger btn-block" href="marketing-jc.php"><i class="fa fa-window-close"></i> Close</a></div>
                <div class="col-sm-3"></div>
                </div>
                
              </center>


            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</div>

<!-- Include Footer File -->
<?php include_once ('../../../include/php/footer.php') ?>

</div>
<!-- ./wrapper -->

<script type="text/javascript">
    function printPageArea(print){
    var printContent = document.getElementById(print).innerHTML;
    var originalContent = document.body.innerHTML;
    document.body.innerHTML = printContent;
    window.print();
    document.body.innerHTML = originalContent;
}
</script>

</body>
</html> 