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

$sql = mysqli_query($conn,"SELECT id,client_name, nature_of_client, gst_pan_number, name_of_ceo, aadhar_details,landphone_no,mob_no,email_id,contact_no,nature_of_work,monthly_volume,special_terms,terms_of_payment,bank_details FROM client_masters");
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
          <h1 class="m-0">CLIENT DETAILS</h1>
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
      <!-- Main row -->
      <div class="row">
        <div class="col-md-12">
          <div class="card card-info card-outline">
            <div class="card-header">
              <a class="btn btn-primary" href="marketing-client-create.php">CREATE NEW CLIENT</a>
            </div>
            <div class="card-body">
              <table id="clientTable"class="table table-bordered table-striped" style="width:100%">
                <thead>
                  <tr>
                    <th>Sl.No</th>
                    <th>Client Name</th>
                    <th>Nature Of Client</th>
                    <th>Name Of CEO</th>
                    <th>Landphone No</th>
                    <th>Mob No</th>
                    <th>Email ID</th>
                    <th>Contact</th>
                    <th>Edit</th>
                    <th>Delete</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
    $sino = 0;
    while ($row = mysqli_fetch_assoc($sql)) {
        $_SESSION['id']=$row['id'];
        $id=$_SESSION['id'];
        $_SESSION['client_names']=$row['client_name'];
        $sino++;
    ?>
                  <tr>
                    <td><?php echo $sino; ?></td>
                    <td><?php echo $row['client_name']; ?></td>
                    <td><?php echo $row['nature_of_client']; ?></td>
                    <td><?php echo $row['name_of_ceo']; ?></td>
                    <td><?php echo $row['landphone_no']; ?></td>
                    <td><?php echo $row['mob_no']; ?></td>
                    <td><?php echo $row['email_id']; ?></td>
                    <td><?php echo $row['contact_no']; ?></td>
                    <td><a href="marketing-client-edit.php?id=<?php echo $row['id'];?>" class="btn btn-primary">EDIT</a></td>
                    <td><a href="marketing-client-delete.php?id=<?php echo $row['id'];?>"class="btn btn-danger">DELETE</a></td>
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
$(document).ready(function() {
    $('#clientTable').DataTable({
      "responsive": true,
      "columnDefs": [ {
        "targets": [8, 9],
        "orderable": false
      } ],
    });
});
</script>

</body>
</html>