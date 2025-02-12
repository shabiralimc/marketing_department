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
$sql_main = "SELECT jc_number,quotation_number,bill_on,jc_date,quotation_date,peyment_terms,client,instructed_by,jc_status,proposed_rate,s_location,completion_before,jc_billable,now_remark,involvements, user FROM jobcard_main WHERE jc_number = ?";
  
  $stmt_main = mysqli_prepare($conn, $sql_main);
  mysqli_stmt_bind_param($stmt_main, "s", $jc_number);
  mysqli_stmt_execute($stmt_main);
  $result_main = mysqli_stmt_get_result($stmt_main);

  $user_info = mysqli_fetch_assoc($result_main);


$sql_fabmain = "SELECT labour_total, transport_total, other_total, material_total, fab_status, grand_total, fab_supervisor FROM fabrication_main WHERE jc_number = ?";

  $stmt_fabmain = mysqli_prepare($conn, $sql_fabmain);
  mysqli_stmt_bind_param($stmt_fabmain, "s", $jc_number);
  mysqli_stmt_execute($stmt_fabmain);
  $result_fabmain = mysqli_stmt_get_result($stmt_fabmain);

  $resultsing_fabmain = mysqli_fetch_assoc($result_fabmain);


$sql_fabmat = "SELECT * FROM fab_mat_expences WHERE jc_number = ?";

  $stmt_fabmat = mysqli_prepare($conn, $sql_fabmat);
  mysqli_stmt_bind_param($stmt_fabmat, "s", $jc_number);
  mysqli_stmt_execute($stmt_fabmat);
  $result_fabmat = mysqli_stmt_get_result($stmt_fabmat);

$sql_fablab = "SELECT * FROM fab_labour_expences WHERE jc_number = ?";

  $stmt_fablab = mysqli_prepare($conn, $sql_fablab);
  mysqli_stmt_bind_param($stmt_fablab, "s", $jc_number);
  mysqli_stmt_execute($stmt_fablab);
  $result_fablab = mysqli_stmt_get_result($stmt_fablab);


$sql_fabtran = "SELECT * FROM fab_transport_expences WHERE jc_number = ?";

  $stmt_fabtran = mysqli_prepare($conn, $sql_fabtran);
  mysqli_stmt_bind_param($stmt_fabtran, "s", $jc_number);
  mysqli_stmt_execute($stmt_fabtran);
  $result_fabtran = mysqli_stmt_get_result($stmt_fabtran);


$sql_fabother = "SELECT * FROM fab_other_expences WHERE jc_number = ?";

  $stmt_fabother = mysqli_prepare($conn, $sql_fabother);
  mysqli_stmt_bind_param($stmt_fabother, "s", $jc_number);
  mysqli_stmt_execute($stmt_fabother);
  $result_fabother = mysqli_stmt_get_result($stmt_fabother);


?>


<!-- Include Header File -->
<?php include_once ('../../../include/php/header.php') ?>

<style>
  th, td {
  padding: 2px;
}
</style>

<!-- Include Sidebar File -->
<?php include_once ('../../../include/php/sidebar-fab.php') ?>


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
                <div style="padding: 10px;">
                  <div style="padding: 20px;"><img style="width: 100px;" src="<?php echo $app_url; ?>/dist/img/logo-full.png" alt="Chakra Logo" class="brand-image"></div>
                    <div class="card card-primary card-outline">
                      <div class="card-body">
                        <table class="table table-bordered table-striped">
                          <thead>                           
                            <tr>
                              <th style="font-size:30px; font-weight: 900; text-align: center; background-color: lightgrey;" colspan="4">FAB INVOLVEMENT DETAILS</th>
                            </tr>
                          </thead>
                          <tbody>
                            <tr>
                              <td style="width:20%">JC Number</td>
                              <td style="width:30%"><?php echo $user_info['jc_number']; ?></td>
                              <td style="width:20%">Instructed By</td>
                              <td style="width:30%"><?php echo $user_info['instructed_by']; ?></td>
                            </tr>
                            <tr>
                              <td>JC Date</td>
                              <td><?php echo date("d-m-Y", strtotime($user_info['jc_date'])); ?></td>
                              <td>Completion Before</td>
                              <td><?php echo date("d-m-Y", strtotime($user_info['completion_before'])); ?></td>
                            </tr>
                            <tr>
                              <td>Client</td>
                              <td><?php echo strtoupper($user_info['client']); ?></td>
                              <td>Location</td>
                              <td><h5><?php echo $user_info['s_location']; ?></h5></td>                        
                            </tr>
                            <tr>
                              <td>Nature Of Work/Remarks</td>
                              <td colspan="3"><?php echo str_replace("\n", "<br/>", $user_info['now_remark']); ?></td>
                            </tr>
                            <tr>
                              <td>Fab Supervisor</td>
                              <td colspan="3">
                                <h5>
                                <?php if ($result_fabmain->num_rows > 0) {
                                  echo $resultsing_fabmain['fab_supervisor'];
                                } else {
                                  echo "Work Not Started";
                                } ?>
                            </h5>
                              </td>
                            </tr>
                          </tbody>
                        </table>
                      </div>
                    </div>

                    <div class="card card-primary card-outline">
                      <div class="card-header">
                        <h3>Material Involvement Details</h3>
                      </div>
                      <div class="card-body">
                        <?php 

                        if ($result_fabmat->num_rows > 0) {

                        // Display the results as a table
                        echo '<table class="table table-bordered table-striped" style="width:100%;">
                                <tr>
                                  <th>Sl No</th>
                                  <th>Product Date</th>
                                  <th>Activity</th>
                                  <th>Material Name</th>
                                  <th>Measuring Unit</th>
                                  <th>Quantity</th>
                                  <th>Total Cost</th>
                                </tr>';

                        $sum_totalcost = 0;
                        $sl_no = 1;

                        while ($row = $result_fabmat->fetch_assoc()) {
                            echo '<tr>';
                            echo '<td>' . $sl_no++ . '</td>';
                            echo '<td>' . $row['product_date'] . '</td>';
                            echo '<td>' . $row['activity'] . '</td>';
                            echo '<td>' . $row['material_name'] . '</td>';
                            echo '<td>' . $row['measuring_unit'] . '</td>';
                            echo '<td>' . $row['quantity'] . '</td>';
                            echo '<td>' . $row['total_cost'] . '</td>';
                            echo '</tr>';

                            $sum_totalcost += $row['total_cost'];
                        }

                        echo '<tr><td colspan="6" style="text-align:right;">Total Cost</td><td>Rs.' . $sum_totalcost . '</td></tr>';
                        echo '</table>';
                        } else {
                            // If no rows, display a message
                            echo '<p>No data to show.</p>';
                        }

                        ?>
                      </div>
                    </div>

                    <div class="card card-primary card-outline">
                      <div class="card-header">
                        <h3>Labour Involvement Details</h3>
                      </div>
                      <div class="card-body">
                        <?php 

                        if ($result_fablab->num_rows > 0) {

                        // Display the results as a table
                        echo '<table class="table table-bordered table-striped" style="width:100%;">
                                <tr>
                                  <th>Sl No</th>
                                  <th>Type of Expence</th>
                                  <th>Activity</th>
                                  <th>Staff Name</th>
                                  <th>Place</th>
                                  <th>Start Date/Time</th>
                                  <th>End Date/Time</th>
                                  <th>Hours</th>
                                  <th>Cost</th>
                                </tr>';

                        $sum_totallabourcost = 0;
                        $sl_no = 1;

                        while ($row = $result_fablab->fetch_assoc()) {
                            echo '<tr>';
                            echo '<td>' . $sl_no++ . '</td>';
                            echo '<td>' . $row['expences'] . '</td>';
                            echo '<td>' . $row['type'] . '</td>';
                            echo '<td>' . $row['name'] . '</td>';
                            echo '<td>' . $row['place'] . '</td>';
                            echo '<td>' . $row['date'] . '</td>';
                            echo '<td>' . $row['endtime'] . '</td>';
                            echo '<td>' . $row['total_ot'] . '</td>';
                            echo '<td>' . $row['labour_cost'] . '</td>';
                            echo '</tr>';

                            $sum_totallabourcost += $row['labour_cost'];
                        }

                        echo '<tr><td colspan="8" style="text-align:right;">Total Cost</td><td>Rs.' . $sum_totallabourcost . '</td></tr>';
                        echo '</table>';
                        } else {
                            // If no rows, display a message
                            echo '<p>No data to show.</p>';
                        }

                        ?>
                      </div>
                    </div>

                    <div class="card card-primary card-outline">
                      <div class="card-header">
                        <h3>Transport Involvement Details</h3>
                      </div>
                      <div class="card-body">
                        <?php 

                        if ($result_fabtran->num_rows > 0) {

                        // Display the results as a table
                        echo '<table class="table table-bordered table-striped" style="width:100%;">
                                <tr>
                                  <th>Sl No</th>
                                  <th>Date</th>
                                  <th>Staff Name</th>
                                  <th>Vehicle</th>
                                  <th>From</th>
                                  <th>To</th>
                                  <th>KM</th>
                                  <th>Cost</th>
                                </tr>';

                        $sum_totaltrancost = 0;
                        $sl_no = 1;

                        while ($row = $result_fabtran->fetch_assoc()) {
                            echo '<tr>';
                            echo '<td>' . $sl_no++ . '</td>';
                            echo '<td>' . $row['fab_tran_date'] . '</td>';
                            echo '<td>' . $row['staff_name'] . '</td>';
                            echo '<td>' . $row['vehicle'] . '</td>';
                            echo '<td>' . $row['from'] . '</td>';
                            echo '<td>' . $row['to'] . '</td>';
                            echo '<td>' . $row['km'] . '</td>';
                            echo '<td>' . $row['cost'] . '</td>';
                            echo '</tr>';

                            $sum_totaltrancost += $row['cost'];
                        }

                        echo '<tr><td colspan="7" style="text-align:right;">Total Cost</td><td>Rs.' . $sum_totaltrancost . '</td></tr>';
                        echo '</table>';
                        } else {
                            // If no rows, display a message
                            echo '<p>No data to show.</p>';
                        }

                        ?>
                      </div>
                    </div>


                    <div class="card card-primary card-outline">
                      <div class="card-header">
                        <h3>Other Involvement Details</h3>
                      </div>
                      <div class="card-body">
                        <?php 

                        if ($result_fabother->num_rows > 0) {

                        // Display the results as a table
                        echo '<table class="table table-bordered table-striped" style="width:100%;">
                                <tr>
                                  <th>Sl No</th>
                                  <th>Date</th>
                                  <th>Staff Name</th>
                                  <th>Expence Type</th>
                                  <th>Remark</th>
                                  <th>Cost</th>
                                </tr>';

                        $sum_totalothercost = 0;
                        $sl_no = 1;

                        while ($row = $result_fabother->fetch_assoc()) {
                            echo '<tr>';
                            echo '<td>' . $sl_no++ . '</td>';
                            echo '<td>' . $row['fab_other_date'] . '</td>';
                            echo '<td>' . $row['staff_names'] . '</td>';
                            echo '<td>' . $row['exp'] . '</td>';
                            echo '<td>' . $row['remark'] . '</td>';
                            echo '<td>' . $row['other_costs'] . '</td>';
                            echo '</tr>';

                            $sum_totalothercost += $row['other_costs'];
                        }

                        echo '<tr><td colspan="5" style="text-align:right;">Total Cost</td><td>Rs.' . $sum_totalothercost . '</td></tr>';
                        echo '</table>';
                        } else {
                            // If no rows, display a message
                            echo '<p>No data to show.</p>';
                        }

                        ?>
                      </div>
                    </div>

                    
                  <div class="card card-primary card-outline">
                    <div class="card-body">
                  <table style="width:50%; " class="table table-bordered">
                    <tr>
                      <td style="width:20%">Job Created By</td>
                      <td style="width:30%"><?php echo strtoupper($username); ?></td>
                    </tr>
                    <tr>
                      <td style="width:20%">JC Printed By</td>
                      <td style="width:30%"><?php echo strtoupper($_SESSION['user']); ?></td>
                    </tr>
                  </table>
                  </div>
                  </div>
                  <div class="row">
                    <div class="col-md-12">
                      <p>Job card printed from software on <?php
                        date_default_timezone_set('Asia/Kolkata');
                        $currentTime = date( 'd-m-Y h:i A', time () );
                        echo $currentTime;
                        ?></p>
                    </div>
                  </div>
                </div>
              </div>
              <!-- PRINT SECTION -->
              <center style="margin-top: 50px;">
                <div class="row">
                <div class="col-sm-3"></div>
                <div class="col-sm-3"><a class="btn btn-info btn-block" href="javascript:void(0);" onclick="printPageArea('print')"><i class="fa fa-print"></i> Print</a></div>
                <div class="col-sm-3"><a class="btn btn-danger btn-block" href="fab-work-jobcard.php"><i class="fa fa-window-close"></i> Close</a></div>
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

<!-- Page Specific Script -->
<script>
  $(function () {
    //Initialize Select2 Elements
    $('.select2').select2()

    //Initialize Select2 Elements
    $('.select2bs4').select2({
      theme: 'bootstrap4'
    })

})

</script>

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