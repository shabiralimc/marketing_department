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
          <h3 class="m-0">CUSTOM REPORT DASBOARD</h3>
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
        <div class="card-body">
          <form method="POST" id="main-form">

            <div class="row">
              <div class="col-md-6">

                <div id="jcNumberDropdown">
                  <div class="form-group">
                    <label for="jcNumber" class="form-label">Select a JC Number</label>
                    <select name="jcNumber" id="jcNumber" class="form-control select2bs4">
                      <option value="" selected disabled>Choose a JC Number</option>
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
                  <div class="form-group">
                    <button type="button" id="ok" class="btn btn-primary"><i class="fa fa-search"></i> Get Report</button>
                  </div>
                </div>


                

                

              </div>
            </div>  
          </form>

          <div id="print">
            <div class="col-md-12" id="reportDiv" style="display:none;">
              <div class="card card-info">
                <div class="card-header">
                  <h3 id="reportText"></h3>
                </div>
                <div class="card-body">
                  <div id="tablesContainer"></div>
                </div>
              </div>
            </div>
          </div>

          <script>
            
            document.getElementById("ok").addEventListener("click", function() {
              var jcNumber = document.getElementById("jcNumber").value;

              if (jcNumber !== "") {
                // AJAX request to fetch jobcard and creative items based on selected JC number
                var xhttp = new XMLHttpRequest();

                xhttp.onreadystatechange = function() {
                  if (this.readyState === 4 && this.status === 200) {
                    document.getElementById("tablesContainer").innerHTML = this.responseText;
                  }
                };

                xhttp.open("GET", "reports.php?jc_number=" + jcNumber, true);
                xhttp.send();

                reportDiv.style.display = 'block';
                reportText.textContent = 'REPORT FOR JOB CARD NO. - '+ jcNumber;
              }
            });
          </script>

 

</div>
</div>
</div>
</section>
</div>

<!-- Include Footer File -->
<?php include_once ('../../../include/php/footer.php') ?>


<script type="text/javascript">

  function printPageArea(print){
    var printContent = document.getElementById(print).innerHTML;
    var originalContent = document.body.innerHTML;
    document.body.innerHTML = printContent;
    window.print();
    document.body.innerHTML = originalContent;
  }

</script>

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

<script>
        document.getElementById('ok').addEventListener('click', function(event) {
            event.preventDefault(); // Prevent the form from submitting
            var form = document.getElementById('main-form');
            form.style.display = 'none'; // Hide the form
        });
</script>


</body>
</html>