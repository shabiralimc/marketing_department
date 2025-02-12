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

$sql = "SELECT pm.jc_number, pim.po_number, pjm.vandor_name, pim.invoice_number, pim.invoice_date, pim.amount, pim.freight, pim.addl_expences, pim.s_descriptions
        FROM production_main pm
        JOIN production_invoice_main pim ON pm.jc_number = pim.jc_number
        JOIN production_jc_po_main pjm ON pim.po_number = pjm.po_number
        WHERE pm.pro_main_status = 'Completed'";

$result = $conn->query($sql);



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
          <h3 class="m-0">PRODUCTION REPORT</h3>
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
              
              
<!-- DataTable -->
<table id="resultTable" class="display table table-bordered table-striped">
    <thead>
        <tr>
            <th>JC Number</th>
        <th>PO Number</th>
        <th>Vendor Name</th>
        <th>Invoice Number</th>
        <th>Invoice Date</th>
        <th>Amount</th>
        <th>Freight</th>
        <th>Additional Expenses</th>
        <th>Description</th>
        </tr>
    </thead>
    <tbody>
       <?php
    while ($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>" . $row["jc_number"] . "</td>
                <td>" . $row["po_number"] . "</td>
                <td>" . $row["vandor_name"] . "</td>
                <td>" . $row["invoice_number"] . "</td>
                <td>" . $row["invoice_date"] . "</td>
                <td>" . $row["amount"] . "</td>
                <td>" . $row["freight"] . "</td>
                <td>" . $row["addl_expences"] . "</td>
                <td>" . $row["s_descriptions"] . "</td>
              </tr>";
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


<!-- Page Specific Script -->

<script>

    // Initialize DataTable
    $(document).ready(function() {
      $('#resultTable').DataTable({
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