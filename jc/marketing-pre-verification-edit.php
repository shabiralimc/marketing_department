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


if (isset($_GET['jc_number'])) {
    $jc_number = $_GET['jc_number'];
    // Use prepared statements to fetch data based on jc_number for jobcard_main
    $sql_main = "SELECT jc_number, quotation_number, bill_on, jc_date, quotation_date, peyment_terms, client,branch_name, instructed_by, proposed_rate, s_location, completion_before,now_remark,jc_billable,involvements FROM jobcard_main WHERE jc_number = ?";
    $stmt_main = mysqli_prepare($conn, $sql_main);

    if ($stmt_main) {
        mysqli_stmt_bind_param($stmt_main, "s", $jc_number);
        mysqli_stmt_execute($stmt_main);
        $result_main = mysqli_stmt_get_result($stmt_main);
        $row_main = mysqli_fetch_assoc($result_main);
        mysqli_stmt_close($stmt_main);

    }


    // / Use prepared statements to fetch data based on jc_number for jobcard_items
    $sql_items = "SELECT pre_details FROM jobcard_items_pv WHERE jc_number = ?";
       
    $stmt_items = mysqli_prepare($conn, $sql_items);
    
    if ($stmt_items) {
        mysqli_stmt_bind_param($stmt_items, "s", $jc_number);
        mysqli_stmt_execute($stmt_items);
        $result_items = mysqli_stmt_get_result($stmt_items);
        $row_items = mysqli_fetch_assoc($result_items);
        mysqli_stmt_close($stmt_items);
    }
    
 }

$involvementsArray = [];  // Initialize an empty array

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['ok'])) {
        $quotation_number = $_POST['quotation_number'];
        $bill = $_POST['bill_on'];
        $jc_date = $_POST['jc_date'];
        $quotation_date = $_POST['quotation_date'];
        $payment = $_POST['peyment_terms'];
        $client = $_POST['client'];
        $branch_name = $_POST['branch_name'];
        $instruct = $_POST['instruct'];
        $proposed = $_POST['proposed'];
        $location = $_POST['location'];
        $completion = $_POST['completion'];
        $now_remark = $_POST['now_remark'];
        $jc_billable = $_POST['jc_billable'];
        $selectedItems = isset($_POST['selected_items']) ? $_POST['selected_items'] : [];
// If selectedItems is empty, set $involvements to an empty string
$involvements = $selectedItems ? implode(', ', $selectedItems) : '';

// Update jobcard_main data
$sql_update_main = "UPDATE jobcard_main SET quotation_number=?, bill_on=?, jc_date=?, quotation_date=?, peyment_terms=?, client=?, branch_name=?, instructed_by=?, proposed_rate=?, s_location=?, completion_before=?, now_remark=?, jc_billable=?, involvements=? WHERE jc_number=?";
$stmt_update_main = $conn->prepare($sql_update_main);

if ($stmt_update_main) {
    $stmt_update_main->bind_param("ssssssssissssss", $quotation_number, $bill, $jc_date, $quotation_date, $payment, $client, $branch_name, $instruct, $proposed, $location, $completion, $now_remark, $jc_billable, $involvements, $jc_number);

    $main_result = $stmt_update_main->execute();

    if (!$main_result) {
        echo "Error updating jobcard main: " . $stmt_update_main->error;
    }

    $stmt_update_main->close();
} else {
    echo "Error preparing UPDATE query: " . $conn->error;
}

if (isset($_POST['pre_details'])) {
    $pre_details = $_POST['pre_details'];

    // Use prepared statement for the UPDATE query
    $sql = "UPDATE jobcard_items_pv SET pre_details=? WHERE jc_number=?";
    $stmt2 = $conn->prepare($sql);

    if ($stmt2) {
        // Assuming $jc_number is set appropriately before this point
        $stmt2->bind_param("ss", $pre_details, $jc_number);
        $main_stmt2 = $stmt2->execute();

        if (!$main_stmt2) {
            echo "Error updating jobcard_items_pv: " . $stmt2->error;
        } else {
            echo "<script>alert('JC Pre-Verification Edits Saved Successfully'); window.location = 'marketing-jc.php';</script>";
        }

        $stmt2->close();
    }
}
    }
}

// Initialize $buttonStatus
$buttonStatus = '';

    // Check if s_status is present in creative_main
    $check_creative_sql = "SELECT * FROM creative_main WHERE jc_number = ?";
    $check_creative_stmt = $conn->prepare($check_creative_sql);

    if ($check_creative_stmt) {
        $check_creative_stmt->bind_param("s", $jc_number);
        $check_creative_stmt->execute();
        $check_creative_stmt->store_result();

        // Check if s_status is present in production_jc_po_main
        $check_production_sql = "SELECT * FROM production_main WHERE jc_number = ?";
        $check_production_stmt = $conn->prepare($check_production_sql);

        if ($check_production_stmt) {
            $check_production_stmt->bind_param("s", $jc_number);
            $check_production_stmt->execute();
            $check_production_stmt->store_result();

            // If s_status is present in both tables, disable the button
            if ($check_creative_stmt->num_rows > 0 || $check_production_stmt->num_rows > 0) {
                $buttonStatus = 'disabled';
            } else {
                $buttonStatus = ''; // Button is enabled
            }

            $check_production_stmt->close();
        } else {
            echo "Error preparing production SELECT query: " . $conn->error;
        }

        $check_creative_stmt->close();
    } else {
        echo "Error preparing creative SELECT query: " . $conn->error;
    }

// Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['jc_cancel'])) {
            // Update jc_status to 'cancelled' in jobcard_main
            $update_status_sql = "UPDATE jobcard_main SET jc_status = 'cancelled' WHERE jc_number = ?";
            $update_status_stmt = $conn->prepare($update_status_sql);

            if ($update_status_stmt) {
                $update_status_stmt->bind_param("s", $jc_number);
                $update_status_stmt->execute();

                if ($update_status_stmt->affected_rows > 0) {
                   echo "<script>alert('JC Cancelled Successfully'); window.location = 'marketing-jc.php';</script>";

                } else {
                    echo "Error updating JC Status: " . $conn->error;
                }

                $update_status_stmt->close();
            } else {
                echo "Error preparing UPDATE query: " . $conn->error;
            }
        }
    }
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
          <h1 class="m-0">EDIT PRE-VARIFICATION</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <form action=""method="POST">
              <input type="submit" class="btn btn-danger" name="jc_cancel" value="CANCEL JC" <?php echo $buttonStatus; ?>>
            </form>
            </ol>
        </div>
      </div><!-- /.row -->
    </div>
    <!-- /.container-fluid -->
  </div>
  <!-- /.content-header -->

  <!-- Main content -->
  <section class="content">
    <div class="container-fluid">
      <div class="card card-info card-outline">

        <form action=""method="POST">
          <div class="card-body">
            <div class="row">
              <div class="col-md-12">
                <div class="row">
                  <div class="col-md-4">

                    <div class="form-group">
                      <label for="jc_number"class="form-label">JC Number</label>
                      <input type="text" name="jc_number" class="form-control" value="<?php echo $row_main['jc_number']; ?>" readonly>
                    </div>

                    <div class="form-group">
                      <label for="jc_date"class="form-label">JC Date</label>
                      <input type="date"name="jc_date"class="form-control" value="<?php echo $row_main['jc_date']; ?>" readonly>
                    </div>

                    <div class="form-group">
                      <label for="client" class="form-label">Client</label><span class="text-danger">*</span><br>
                      <input name="client" id="cl1" class="form-control" value="<?php echo $row_main['client']; ?>" readonly>
                    </div>

                    <div class="form-group">
                      <label for="branch_name" class="form-label">Branch</label><br>
                      <select name="branch_name" class="form-control">
                        <?php
                        // Fetch branch name from jobcard_main based on jc_number
                        $branch_query = "SELECT branch_name FROM jobcard_main WHERE jc_number = ?";
                        $stmt_branch = $conn->prepare($branch_query);

                        if ($stmt_branch) {
                            $stmt_branch->bind_param("s", $jc_number);
                            $stmt_branch->execute();
                            $result_branch = $stmt_branch->get_result();

                            // Display the branch name as a default option
                            while ($row_branch = $result_branch->fetch_assoc()) {
                                $branch_option = htmlspecialchars($row_branch['branch_name']);
                                echo '<option value="' . $branch_option . '">' . $branch_option . '</option>';
                            }

                            // Fetch branch options from client_billing_masters based on the selected client name
                            $selected_client = $row_main['client'];
                            $billing_branch_query = "SELECT DISTINCT branch_name FROM client_billing_masters WHERE client_name = ?";
                            $stmt_billing_branch = $conn->prepare($billing_branch_query);

                            if ($stmt_billing_branch) {
                                $stmt_billing_branch->bind_param("s", $selected_client);
                                $stmt_billing_branch->execute();
                                $result_billing_branch = $stmt_billing_branch->get_result();

                                // Loop through branch options and populate the dropdown, excluding the selected branch
                                while ($row_billing_branch = $result_billing_branch->fetch_assoc()) {
                                    $billing_branch_option = htmlspecialchars($row_billing_branch['branch_name']);
                                    if ($billing_branch_option !== $branch_option) {
                                        echo '<option value="' . $billing_branch_option . '">' . $billing_branch_option . '</option>';
                                    }
                                }

                                $stmt_billing_branch->close();
                            } else {
                                // Handle the error if prepare fails
                                echo "Error in preparing statement: " . $conn->error;
                            }

                            $stmt_branch->close();
                        } else {
                            // Handle the error if prepare fails
                            echo "Error in preparing statement: " . $conn->error;
                        }
                        ?>
                      </select>
                    </div>

                    <div class="form-group">
                      <label for="location"class="form-label">Location</label>
                      <input type="text"name="location"class="form-control" value="<?php echo $row_main['s_location']; ?>">
                    </div>

                  </div>
                  <div class="col-md-4">

                    <div class="form-group">
                      <label for="quotation_number"class="form-label">Quotation Number</label>
                      <input type="text"name="quotation_number"class="form-control" value="<?php echo $row_main['quotation_number']; ?>">
                    </div>

                    <div class="form-group">
                      <label for="quotation_date"class="form-label">Quotation Date</label>
                      <input type="date"name="quotation_date"class="form-control" value="<?php echo $row_main['quotation_date']; ?>">
                    </div>

                    <div class="form-group">
                      <label for="insurted"class="form-label">Instructed By</label>
                      <input type="text"name="instruct"class="form-control" value="<?php echo $row_main['instructed_by']; ?>">
                    </div>

                    <div class="form-group">
                      <label for="completion"class="form-label">Completion Before</label>
                      <input type="date"name="completion"class="form-control" value="<?php echo $row_main['completion_before']; ?>">
                    </div>

                    <div class="form-group">
                      <label for="jc_billable"class="form-label">Is Billable</label>
                      <select class="form-control" id="jc_billable" name="jc_billable">
                        <option value="Yes"<?php echo ($row_main['jc_billable'] === 'Yes') ? 'selected' : ''; ?>>Yes</option>
                        <option value="No"<?php echo ($row_main['jc_billable'] === 'No') ? 'selected' : ''; ?>>No</option>
                      </select>
                    </div>

                  </div>
                  <div class="col-md-4">

                    <div class="form-group">
                      <label for="bill on"class="form-label">Bill on</label>
                      <input type="text"name="bill_on"class="form-control" value="<?php echo $row_main['bill_on']; ?>">
                    </div>

                    <div class="form-group">
                      <label for="peyment"class="form-label">Payment Terms</label>
                      <input type="text"name="peyment_terms"class="form-control" value="<?php echo $row_main['peyment_terms']; ?>">
                    </div>

                    <div class="form-group">
                      <label for="proposed"class="form-label">Proposed Rate</label>
                      <input type="number"name="proposed"class="form-control" value="<?php echo $row_main['proposed_rate']; ?>">
                    </div>

                    <div class="form-group">
                      <label for="now_remark"class="form-label">Nature Of Work/Remarks</label><br>
                      <textarea type="text"name="now_remark"id="now_remark"class="form-control"cols="10"rows="5" style="width: 500px;"><?php echo $row_main['now_remark']; ?></textarea>
                    </div>
                  </div>
                </div>
              </div>

              <div class="col-md-12">
                <div class="card card-info card-outline">
                  <div class="card-header">
                    <h3 class="card-title">Involvements</h3>
                  </div>
                  <div class="card-body">
                    <?php
// Assume you have retrieved user's involvements from the database and stored in $involvementsArray variable
$involvementsArray = explode(', ', $row_main['involvements']); // Assuming involvements are stored as comma-separated values in the database

// Checkbox values
$checkboxValues = array("fab_tvm", "fab_ekm");

// Check if each checkbox value exists in user's involvements
$checked = array();
foreach ($checkboxValues as $value) {
    $checked[$value] = in_array($value, $involvementsArray) ? 'checked' : '';
}
?>

<!-- Involvements checkboxes -->

<label style="margin-right: 20px;"><input style="margin-right: 5px;" type="checkbox" name="selected_items[]" value="fab_tvm" <?php echo $checked['fab_tvm']; ?>>Fabrication TVM</label>
<label style="margin-right: 20px;"><input style="margin-right: 5px;" type="checkbox" name="selected_items[]" value="fab_ekm" <?php echo $checked['fab_ekm']; ?>>Fabrication EKM</label>
                  </div>
                </div>
              </div>

              <div class="col-md-12">
                <div class="card card-info card-outline">
                  <div class="card-header">
                    <h3 class="card-title">Preverification Details</h3>
                  </div>
                  <div class="card-body">
                    <textarea class="form-control" style="width:100%;" name="pre_details" id="pre_details" cols="10" rows="5"><?php echo $row_items['pre_details']; ?></textarea>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="card-footer">
            <button class="btn btn-success" name="ok" id="checkBtn">SAVE PRE-VERIFICATION</button>
            <a href="marketing-jc.php" class="btn btn-primary" >CLOSE (without save)</a>
          </div>

        </form>
      </div>
    </div>
  </section>
</div>



<!-- Include Footer File -->
<?php include_once ('../../../include/php/footer.php') ?>


 <script>
    document.addEventListener("DOMContentLoaded", function () {

 // Add a change event listener to each involvement checkbox
 var checkboxes = document.querySelectorAll("input[name='selected_items[]']");
    checkboxes.forEach(function (checkbox) {
        checkbox.addEventListener("change", function () {
            updateInvolvementsField(checkboxes);
        });
    });

    
});

</script>

<script type="text/javascript">
$(document).ready(function () {
    $('#checkBtn').click(function() {
      checked = $("input[type=checkbox]:checked").length;

      if(!checked) {
        alert("Please fillout all the * marked fields and atleast one involvement");
        return false;
      }

    });
});

</script>
