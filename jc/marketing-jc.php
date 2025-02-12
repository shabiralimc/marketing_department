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


$user_query=$_SESSION['user'];

$query = "SELECT 
    jm.jc_number, 
    jm.quotation_number, 
    jm.bill_on, 
    jm.jc_date,
    jm.csr,
    jm.quotation_date, 
    jm.peyment_terms, 
    jm.client, 
    jm.instructed_by, 
    jm.jc_billable,
    jm.proposed_rate, 
    jm.s_location, 
    jm.completion_before, 
    jm.pre_varification,
    jm.work_jc,
    jm.involvements, 
    jm.now_remark,
    jm.user,
    jm.jc_status,
    cm.s_status,
    pm.pro_main_status AS production_s_status,
    pv.current_status AS preveri_status,
    fm.fab_status
FROM jobcard_main jm 
LEFT JOIN creative_main cm ON jm.jc_number = cm.jc_number
LEFT JOIN production_main pm ON jm.jc_number = pm.jc_number
LEFT JOIN pre_varification_total pv ON jm.jc_number = pv.jc_number
LEFT JOIN fabrication_main fm ON jm.jc_number = fm.jc_number
WHERE jm.csr LIKE '%$user_query%'";

$result = mysqli_query($conn, $query);

// Check if the session variable is set to disable the EDIT button
$disable_edit_button = isset($_SESSION['disable_edit_button']) && $_SESSION['disable_edit_button'];
$cancelled_jc_number = $_SESSION['cancelled_jc_number'] ?? null;

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
          <h3 class="m-0">MANAGE JOB CARD</h3>
        </div>
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
        <div class="card-header">
          <a href="marketing-jc-insert.php"><input type="submit" name="edit" class="btn btn-primary" value="CREATE NEW JC"></a>
          <a href="marketing-pre-verification.php"><input type="submit" name="edit" class="btn btn-info" value="CREATE PREVERIFICATION"></a>
        </div>
        <!-- /.card-header -->
        <div class="card-body">
          <div class="row">
            <div style="margin-top:25px;" class="col-md-12">
              <table id="jobcardTable" class="table table-bordered table-striped">
                <thead>
                  <tr>
                    <th>JC Number</th>
                    <th>JC Date</th>
                    <th>Client</th>
                    <th>Instructed By</th>
                    <th>Location</th>
                    <th>Completion Before</th>
                    <th>Is Billable</th>
                    <th>Involvements</th>
                    <th>Nature Of Work</th>
                    <th>View</th>
                    <th>Edit</th>
                  </tr>
                </thead>
                <tbody>
                 <?php
       while ($row = mysqli_fetch_assoc($result)) {

        $creativestatusexist = $row['s_status'] > 0;
    
        // Determine the creative status text
        $creativeStatusText = '';
        if ($creativestatusexist) {
            $sql_creative_status = "SELECT s_status FROM creative_main WHERE jc_number = ?";
            $stmt_creative_status = mysqli_prepare($conn, $sql_creative_status);
    
            if ($stmt_creative_status) {
                mysqli_stmt_bind_param($stmt_creative_status, "s", $jc_number);
                mysqli_stmt_execute($stmt_creative_status);
    
                $result_creative_status = mysqli_stmt_get_result($stmt_creative_status);
    
                // Check if there are any rows returned
                if ($result_creative_status) {
                    $row_creative_status = mysqli_fetch_assoc($result_creative_status);
    
                    if ($row_creative_status) {
                        $status = $row_creative_status['s_status'];
    
                        if ($status === "approved") {
                            $creativeStatusText = "creative approved";
                        } elseif ($status === "mailed") {
                            $creativeStatusText = "creative mailed";
                        }elseif ($status === "WIP"){
                            $creativeStatusText="creative work in progress";                        
                        }
                    }
                }
    
                mysqli_stmt_close($stmt_creative_status);
            }
        }

        $productionstatusexist = $row['s_status'] > 0;

        // Determine the production status text
        $productionStatusText = '';
        if ($productionstatusexist) {
            $sql_production_status = "SELECT s_status FROM production_jc_po_main WHERE jc_number = ?";
            $stmt_production_status = mysqli_prepare($conn, $sql_production_status);
    
            if ($stmt_production_status) {
                mysqli_stmt_bind_param($stmt_production_status, "s", $jc_number);
                mysqli_stmt_execute($stmt_production_status);
    
                $result_production_status = mysqli_stmt_get_result($stmt_production_status);
    
                // Check if there are any rows returned
                if ($result_production_status) {
                    $row_production_status = mysqli_fetch_assoc($result_production_status);
    
                    if ($row_production_status) {
                        $status_s = $row_production_status['production_s_status'];
    
                        if ($status_s === "Completed") {
                            $productionStatusText = "production completed";
                        } elseif ($status_s === "ongoing"){
                            $productionStatusText="production ongoing";                        
                        }
                    }
                }
    
                mysqli_stmt_close($stmt_production_status);
            }
        }

        $preverificationexist = $row['preveri_status'] > 0;

        // Determine the production status text
        $preverificationStatusText = '';
        if ($preverificationexist) {
            $sql_pv_status = "SELECT current_status FROM pre_varification_total WHERE jc_number = ?";
            $stmt_pv_status = mysqli_prepare($conn, $sql_pv_status);
    
            if ($stmt_pv_status) {
                mysqli_stmt_bind_param($stmt_pv_status, "s", $jc_number);
                mysqli_stmt_execute($stmt_pv_status);
    
                $result_pv_status = mysqli_stmt_get_result($stmt_pv_status);
    
                // Check if there are any rows returned
                if ($result_pv_status) {
                    $row_pv_status = mysqli_fetch_assoc($result_pv_status);
    
                    if ($row_pv_status) {
                        $status_pv = $row_pv_status['current_status'];
    
                        if ($status_pv === "wip") {
                            $preverificationStatusText = "WIP";
                        } elseif ($status_pv === "completed"){
                            $preverificationStatusText="Completed";                        
                        }
                    }
                }
    
                mysqli_stmt_close($stmt_pv_status);
            }
        }

        $fabricationexist = $row['fab_status'] > 0;

        // Determine the production status text
        $fabStatusText = '';
        if ($fabricationexist) {
            $sql_fab_status = "SELECT fab_status FROM fabrication_main WHERE jc_number = ?";
            $stmt_fab_status = mysqli_prepare($conn, $sql_fab_status);
    
            if ($stmt_fab_status) {
                mysqli_stmt_bind_param($stmt_fab_status, "s", $jc_number);
                mysqli_stmt_execute($stmt_fab_status);
    
                $result_fab_status = mysqli_stmt_get_result($stmt_fab_status);
    
                // Check if there are any rows returned
                if ($result_fab_status) {
                    $row_fab_status = mysqli_fetch_assoc($result_fab_status);
    
                    if ($row_fab_status) {
                        $status_fab = $row_fab_status['fab_status'];
    
                        if ($status_fab === "wip") {
                            $fabStatusText = "WIP";
                        } elseif ($status_fab === "completed"){
                            $fabStatusText = "Completed";                        
                        }
                    }
                }
    
                mysqli_stmt_close($stmt_fab_status);
            }
        }
        
        ?>
        <tr>
            <td><?php echo $row['jc_number'] ?></td>
            <td><?php echo date('d-m-Y', strtotime($row['jc_date'])) ?></td>
            <td><?php echo $row['client'] ?></td>
            <td><?php echo $row['instructed_by'] ?></td>
            <td><?php echo $row['s_location'] ?></td>
            <td><?php echo date ('d-m-Y', strtotime($row['completion_before'])) ?></td>
            <td><?php echo $row['jc_billable'] ?></td>
            <td><?php echo $row['involvements'] ?>

            <?php $creativeStatusText = $row['s_status'];
            if ($creativeStatusText == 'Work In Progress') {
              echo "<table class='width:100%'><tr><td style='padding:0px;'><div class='btn-group'><button type='button' class='btn btn-default' style='font-size:7pt;'>CREATIVE</button><button type='button' class='btn btn-info' style='font-size:7pt;'>WIP</button></div></td></tr></table>";
            } elseif ($creativeStatusText == 'Approved') {
              echo "<table class='width:100%'><tr><td style='padding:0px;'><div class='btn-group'><button type='button' class='btn btn-default' style='font-size:7pt;'>CREATIVE</button><button type='button' class='btn btn-success' style='font-size:7pt;'>COMPLETED</button></div></td></tr></table>";
            } ?>

            <?php $productionStatusText =$row['production_s_status'];
            if ($productionStatusText=='WIP') {
              echo "<table class='width:100%'><tr><td style='padding:0px;'><div class='btn-group'><button type='button' class='btn btn-default' style='font-size:7pt;'>PRODUCTION</button><button type='button' class='btn btn-info' style='font-size:7pt;'>WIP</button></div></td></tr></table>";
            } elseif ($productionStatusText=='Completed') {
              echo "<table class='width:100%'><tr><td style='padding:0px;'><div class='btn-group'><button type='button' class='btn btn-default' style='font-size:7pt;'>PRODUCTION</button><button type='button' class='btn btn-success' style='font-size:7pt;'>COMPLETED</button></div></td></tr></table>";
            } ?>

            <?php $preverificationStatusText =$row['preveri_status'];
            if ($preverificationStatusText=='wip') {
              echo "<table class='width:100%'><tr><td style='padding:0px;'><div class='btn-group'><button type='button' class='btn btn-default' style='font-size:7pt;'>PRE-VERIFICATION</button><button type='button' class='btn btn-info' style='font-size:7pt;'>WIP</button></div></td></tr></table>";
            } elseif ($preverificationStatusText=='completed') {
              echo "<table class='width:100%'><tr><td style='padding:0px;'><div class='btn-group'><button type='button' class='btn btn-default' style='font-size:7pt;'>PRE-VERIFICATION</button><button type='button' class='btn btn-success' style='font-size:7pt;'>COMPLETED</button></div></td></tr></table>";
            } ?>

            <?php $fabStatusText =$row['fab_status'];
            if ($fabStatusText=='wip') {
              echo "<table class='width:100%'><tr><td style='padding:0px;'><div class='btn-group'><button type='button' class='btn btn-default' style='font-size:7pt;'>FABRICATION</button><button type='button' class='btn btn-info' style='font-size:7pt;'>WIP</button></div></td></tr></table>";
            } elseif ($fabStatusText=='completed') {
              echo "<table class='width:100%'><tr><td style='padding:0px;'><div class='btn-group'><button type='button' class='btn btn-default' style='font-size:7pt;'>FABRICATION</button><button type='button' class='btn btn-success' style='font-size:7pt;'>COMPLETED</button></div></td></tr></table>";
            } ?>

          </td>
          <td><?php echo $row['now_remark'] ?></td>

             <?php

// Initialize $buttonStatus
$buttonStatus = '';
$row_jc_number = $row['jc_number'];

// Check if s_status is present in creative_main
$check_creative_sql = "SELECT * FROM creative_main WHERE jc_number = '$row_jc_number'";
$check_creative_result = $conn->query($check_creative_sql);

if ($check_creative_result) {
    // Check if s_status is present in production_main
    $check_production_sql = "SELECT * FROM production_main WHERE jc_number = '$row_jc_number'";
    $check_production_result = $conn->query($check_production_sql);

    if ($check_production_result) {
        // Check if s_status is present in Preverification main Table
        $check_preveri_sql = "SELECT * FROM pre_varification_total WHERE jc_number = '$row_jc_number'";
        $check_preveri_result = $conn->query($check_preveri_sql);

        if ($check_preveri_result) {
            // Check if s_status is present in Fabrication main Table
            $check_fab_sql = "SELECT * FROM fabrication_main WHERE jc_number = '$row_jc_number'";
            $check_fab_result = $conn->query($check_fab_sql);

            if ($check_fab_result) {
                // If s_status is present in any one of the tables, disable the button
                if (
                    $check_creative_result->num_rows > 0 || 
                    $check_production_result->num_rows > 0 || 
                    $check_preveri_result->num_rows > 0 || 
                    $check_fab_result->num_rows > 0
                ) {
                    $buttonStatus = 'disabled';
                } else {
                    $buttonStatus = ''; // Button is enabled
                }
            } else {
                echo "Error executing Fabrication SELECT query: " . $conn->error;
            }
        } else {
            echo "Error executing Preverification SELECT query: " . $conn->error;
        }
    } else {
        echo "Error executing Production SELECT query: " . $conn->error;
    }
} else {
    echo "Error executing Creative SELECT query: " . $conn->error;
}

                // Check if both "pre_varification" and "work_jc" have the respective values in the jobcard_main table
                $jcNumber = $row['jc_number'];
                $jobcardMainQuery = "SELECT pre_varification, work_jc FROM jobcard_main WHERE jc_number = ?";
                $stmt_jobcardMain = $conn->prepare($jobcardMainQuery);
                $stmt_jobcardMain->bind_param("s", $jcNumber);
                $stmt_jobcardMain->execute();
                $stmt_jobcardMain->bind_result($preVarification, $workJC);
                $stmt_jobcardMain->fetch();
                $stmt_jobcardMain->close();

                if ($row['jc_status']=="cancelled") {
                  echo '
                    <td><button class="btn btn-danger" onclick="location.href=\'marketing-jc-view.php?jc_number=' . $jcNumber . '\'">View JC</button></td>

                    <td><button class="btn btn-danger" disabled>Cancelled JC</button></td>';

                } else {
                    // Continue with the existing logic
                    if ($preVarification === 'pre_varification') {
                        // Check if the JC number is present in the pre_varification_total table
                        $preVerificationQuery = "SELECT * FROM pre_varification_total WHERE jc_number = ?";
                        $stmt_preVerification = $conn->prepare($preVerificationQuery);
                        $stmt_preVerification->bind_param("s", $jcNumber);
                        $stmt_preVerification->execute();
                        $stmt_preVerification->store_result();
                        $preVerificationRows = $stmt_preVerification->num_rows;
                        $stmt_preVerification->close();

                        if ($preVerificationRows > 0) {
                            // Check the current status of the JC number
                            $statusQuery = "SELECT current_status FROM pre_varification_total WHERE jc_number = ?";
                            $stmt = $conn->prepare($statusQuery);
                            $stmt->bind_param("s", $jcNumber);
                            $stmt->execute();
                            $stmt->bind_result($currentStatus);
                            $stmt->fetch();
                            $stmt->close();

                            // If current status is "completed", show the "Convert JC" and "Convert View" buttons
                            if ($currentStatus == "completed" && $row['work_jc']=="work_jc") {
                                echo '
                                  <td>
                            <button class="btn btn-primary" onclick="location.href=\'marketing-jc-view.php?jc_number=' . $jcNumber . '\'">View JC</button>
                          </td>
                          <td>
                            <button '.$buttonStatus.'  class="btn btn-primary" onclick="location.href=\'marketing-jc-edit.php?jc_number=' . $jcNumber . '\'">JC Edit</button>
                          </td>';
                            } elseif ($currentStatus == "completed" && $row['work_jc']=="") {
                                // If current status is not "completed", show the "Pre Edit" and "Pre View" buttons
                                echo '
                                  <td>
                                    <button class="btn btn-warning convert-view-btn" onclick="location.href=\'marketing-pre-verification-view.php?jc_number=' . $jcNumber . '\'">View PV</button>
                                  </td>
                                  <td>
                                    <button class="btn btn-warning"id="convert-jc-btn" onclick="location.href=\'marketing-pv-convert.php?jc_number=' . $jcNumber . '\'">Convert to JC</button>
                                  </td>';
                            } else {
                                // If current status is not "completed", show the "Pre Edit" and "Pre View" buttons
                                echo '
                                  <td>
                                    <button class="btn btn-info" onclick="location.href=\'marketing-pre-verification-view.php?jc_number=' . $jcNumber . '\'">View PV</button>
                                  </td>
                                  <td>
                                    <button class="btn btn-info" onclick="location.href=\'marketing-pre-verification-edit.php?jc_number=' . $jcNumber . '\'">Edit PV</button>
                                  </td>';
                            }
                        } else {
                            // If the JC number is not present in pre_varification_total, show the "Pre Edit" and "Pre View" buttons
                            echo '
                              <td>
                                <button class="btn btn-info convert-view-btn" onclick="location.href=\'marketing-pre-verification-view.php?jc_number=' . $jcNumber . '\'">View PV</button>
                              </td>
                              <td>
                                <button class="btn btn-info convert-edit-btn" onclick="location.href=\'marketing-pre-verification-edit.php?jc_number=' . $jcNumber . '\'">Edit PV</button>
                              </td>';
                        }
                    } else {
                        // If pre_varification is not present, show the "JC Edit" and "JC View" buttons
                        echo '
                          <td>
                            <button class="btn btn-primary" onclick="location.href=\'marketing-jc-view.php?jc_number=' . $jcNumber . '\'">View JC</button>
                          </td>
                          <td>
                            <button '.$buttonStatus.'  class="btn btn-primary" onclick="location.href=\'marketing-jc-edit.php?jc_number=' . $jcNumber . '\'">Edit JC</button>
                          </td>';
                    }
                }
                ?>
        </tr>
        <?php
        }
        ?>
                </tbody>

              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</div>


<!-- Include Footer File -->
<?php include_once ('../../../include/php/footer.php') ?>

<script>
    $(document).ready(function () {
  $(document).Toasts('create', {
    class: 'bg-warning',
    title: 'UPDATE',
    subtitle: 'Close',
    body: 'Once Job Involvement begins for a new JC, the editing option will be disabled. For any modifications to a created JC, please contact the ADMIN.'
  });
});
</script>


<!-- Page Specific Script -->
<script>
$(document).ready(function() {
    $('#jobcardTable').DataTable({
      "responsive": true,
      "columnDefs": [ {
        "targets": [0, 9, 10],
        "orderable": false
      } ],
      "order": [[ 0, "desc" ]]
    });
});
</script>

<script>
  let appUrl = "<?php echo $app_url; ?>";
  let lastActivityTime;

  function checkLastActivity() {
    if (lastActivityTime && (Date.now() - lastActivityTime > 1800000)) {
      // last activity was more than 15 minutes ago
      // Redirect to the logout page
      alert("You have been logged out due to inactivity, Please login again.");
      window.location.href = appUrl + "/include/php/logout.php";
    }
  }

  // Set initial last activity time
  lastActivityTime = Date.now();

  // Check last activity every 1 minute (you can adjust the interval)
  setInterval(checkLastActivity, 1800000);

  // Update last activity time on user interaction
  document.addEventListener("mousemove", function () {
    lastActivityTime = Date.now();
  });

  document.addEventListener("keypress", function () {
    lastActivityTime = Date.now();
  });
</script>

</body>
</html>
