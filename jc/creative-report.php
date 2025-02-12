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

?>

<!-- Include Header File -->
<?php include_once ('../../../include/php/header.php') ?>
    
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h3 class="m-0">CREATIVE REPORT</h3>
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
        <!-- /.card-header -->
        <div class="card-body">
          <div class="row">
            <div style="margin-top:25px;" class="col-md-12">
              
              <form id="searchForm">
    <label for="startDate">Start Date:</label>
    <input type="date" id="startDate" name="startDate" required>

    <label for="endDate">End Date:</label>
    <input type="date" id="endDate" name="endDate" required>

    <button type="button" onclick="searchData()">Search</button>
</form>

<!-- DataTable -->
<table id="resultTable" class="display table table-bordered table-striped">
    <thead>
        <tr>
            <th>jc_number</th>
            <th>desinger</th>
            <th>Client</th>
            <th>work</th>
            <th>activity</th>
            <th>start_date_time</th>
            <th>end_date_time</th>
            <th>time_difference</th>
            <th>item_amount</th>
        </tr>
    </thead>
    <tbody>
        <!-- Data will be dynamically loaded here -->
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


<!-- Page Specific Script -->
<script>

$(document).ready(function() {
    $('#jobcardTable').DataTable({
      "responsive": true,
      "lengthMenu": [[50, 100, 500, -1], [50, 100, 500, "All"]],
      dom: 'Bfrtip',
        buttons: [
            'pageLength',
            {
                extend: 'spacer',
                style: 'bar',
                text: 'Export files:'
            },
            'copyHtml5',
            'excelHtml5',
            'pdfHtml5',
        ]
    });
});
</script>

<script>
    function searchData() {
        var startDate = $('#startDate').val();
        var endDate = $('#endDate').val();

        $.ajax({
            url: 'creative-server.php',
            method: 'POST',
            data: { startDate: startDate, endDate: endDate },
            dataType: 'json',
            success: function(data) {
                // Clear existing rows in the DataTable
                $('#resultTable').DataTable().clear();

                // Add the new data to the DataTable
                $('#resultTable').DataTable().rows.add(data).draw();
            },
            error: function(xhr, status, error) {
                console.error('Error fetching data:', status, error);
            }
        });
    }

    // Initialize DataTable
    $(document).ready(function() {
        // Setup - add a text input to each footer cell
            $('#resultTable thead tr').clone(true).appendTo('#resultTable thead');
            $('#resultTable thead tr:eq(1) th').each(function (i) {
                if (!$(this).hasClass("noFilter")) {
                    var title = $(this).text();
                    $(this).html('<input type="text" class="form-control" placeholder="Search ' + title + '" />');

                    $('input', this).on('keyup change', function () {
                        if (table.column(i).search() !== this.value) {
                            table
                                .column(i)
                                .search(this.value)
                                .draw();
                        }
                    });
                }
                else {
                    $(this).html('<span></span>');
                }

            });
        $('#resultTable').DataTable({
            columns: [
                { data: 'jc_number' },
                { data: 'desinger' },
                { data: 'client' },
                { data: 'work' },
                { data: 'activity' },
                { data: 'start_date_time' },
                { data: 'end_date_time' },
                { data: 'time_difference' },
                { data: 'item_amount' }
            ],
            "responsive": true,
            "lengthMenu": [[50, 100, 500, -1], [50, 100, 500, "All"]],
      dom: 'Bfrtip',
        buttons: [
            'pageLength',
            {
                extend: 'spacer',
                style: 'bar',
                text: 'Export files:'
            },
            'copyHtml5',
            'excelHtml5',
            'pdfHtml5',
        ]
        });
    });
</script>



</body>
</html>
