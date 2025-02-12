<?php
session_start(); 
include_once('../../../include/php/connect.php');

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
          <h3 class="m-0">JC COMBINED REPORT DASBOARD</h3>
        </div>
        <div class="card-body">

          <div class="row">
            <div class="col-md-8 offset-md-2">
              <div class="input-group">

                <div class="col-md-10" data-select2-id="43">
                  <div class="form-group" data-select2-id="43">
                    <div class="select2-purple" data-select2-id="42">
                      <select class="form-control select2 select2-hidden-accessible" multiple="" data-placeholder="Select JC Number(s)" data-dropdown-css-class="select2-purple" style="width: 100%;" data-select2-id="15" tabindex="-1" aria-hidden="true"id="jcNumber">
                        <?php

                        // Query to fetch JC numbers meeting all conditions
                        $query = "SELECT DISTINCT jc.jc_number, jc.jc_date, jc.client, jc.csr, jc.proposed_rate, jc.quotation_number, jc.quotation_date  
                        FROM jobcard_main jc
                        LEFT JOIN creative_main cr ON jc.jc_number = cr.jc_number
                        LEFT JOIN production_main pr ON jc.jc_number = pr.jc_number
                        LEFT JOIN fabrication_main fb ON jc.jc_number = fb.jc_number
                        LEFT JOIN pre_varification_total pv ON jc.jc_number = pv.jc_number 
                        WHERE jc.jc_status != 'cancelled'
                          AND (
                                (jc.pre_varification = 'pre_varification' AND pv.current_status = 'completed')
                                  OR (jc.work_jc = 'work_jc' AND 
                                    (
                                      FIND_IN_SET('creative', jc.involvements) AND cr.s_status = 'Approved'
                                      OR FIND_IN_SET('production', jc.involvements) AND pr.pro_main_status = 'Completed'
                                      OR FIND_IN_SET('fab_tvm', jc.involvements) AND fb.fab_status = 'Completed'
                                      OR FIND_IN_SET('fab_ekm', jc.involvements) AND fb.fab_status = 'Completed'
                                    )
                                )
                              )";


                          $result = mysqli_query($conn, $query);

                          // Check if query executed successfully
                          if ($result) {
                            // Loop through the results and display each JC number as an option in the dropdown
                            while ($row = mysqli_fetch_assoc($result)) {
                              echo '<option value="' . $row['jc_number'] . '">' . $row['jc_number'] . '</option>';
                            }

                            // Free result set
                            mysqli_free_result($result);
                          } else {

                            // If query fails, display an error message
                            echo '<option value="">Error: Unable to fetch JC numbers</option>';
                          }
                          ?>
                      </select>
                    </div>
                  </div>
                </div>
                <div class="col-md-2">
                  <button type="button" id="ok" class="btn btn-primary">OK</button>
                </div>

              </div>
            </div>
          </div>

         
            <div id="printableArea">
              <div class="col-md-12" id="tablesContainer">
              </div>
            </div>
          </div>

<!-- Division Closing from Combined reports php -->

      </div>
    </div>
  </section>
</div>




<!-- Include Footer File -->
<?php include_once ('../../../include/php/footer.php') ?>

    <script>
       document.getElementById("ok").addEventListener("click", function() {
    var jcNumbers = $('#jcNumber').val(); // Get all selected JC numbers
    if (jcNumbers.length > 0) {
        // AJAX request to fetch jobcard and creative items based on selected JC numbers
        var xhttp = new XMLHttpRequest();
        xhttp.onreadystatechange = function() {
            if (this.readyState === 4 && this.status === 200) {
                document.getElementById("tablesContainer").innerHTML = this.responseText;
            }
        };
        // Send all selected JC numbers to the server
        xhttp.open("GET", "combined-reports-main.php?jc_numbers=" + jcNumbers.join(','), true);
        xhttp.send();
    }
});

    </script>
    <script>
  $(function () {
    //Initialize Select2 Elements
    $('.select2').select2()

    //Initialize Select2 Elements
    $('.select2bs4').select2({
      theme: 'bootstrap4'
    })  })

</script>

<script type="text/javascript">
  function printDiv(divName) {
     var printContents = document.getElementById(divName).innerHTML;
     var originalContents = document.body.innerHTML;

     document.body.innerHTML = printContents;

     window.print();

     document.body.innerHTML = originalContents;
}

</script>

</body>
</html>