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
    $sql_main = "SELECT jc_number, quotation_number, bill_on, jc_date, quotation_date, peyment_terms, client, branch_name, instructed_by, proposed_rate, s_location, completion_before, involvements, now_remark, jc_billable, jc_status FROM jobcard_main WHERE jc_number = ?";
    $stmt_main = mysqli_prepare($conn, $sql_main);

    if ($stmt_main) {
        mysqli_stmt_bind_param($stmt_main, "s", $jc_number);
        mysqli_stmt_execute($stmt_main);
        $result_main = mysqli_stmt_get_result($stmt_main);
        $row_main = mysqli_fetch_assoc($result_main);
        mysqli_stmt_close($stmt_main);
    }


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['ok'])) {

    $po = $_POST['quotation_number'];
    $bill = $_POST['bill_on'];
    $jc_date = $_POST['jc_date'];

    if (empty($_POST["quotation_date"]))
        {
          $quotation_date = NULL;
        } else {
          $quotation_date = $_POST["quotation_date"];
        }

    $payment = $_POST['peyment_terms']; 
    $client = $_POST['client'];
    $branch_name = $_POST['branch_name'];
    $instruct = $_POST['instruct'];
    $proposed = $_POST['proposed'];
    $location = $_POST['location'];
    $completion = $_POST['completion'];
    $now_remark = $_POST['now_remark'];
    $jc_billable = $_POST['jc_billable'];
    $selectedItems = $_POST['selected_items'];
    $involvements = implode(', ', $selectedItems); // Combine selected items into a comma-separated string
    $user = $_SESSION['user'];
    date_default_timezone_set('Asia/Kolkata');
    $datetime = date("d-m-y h:i:s");


    // Update jobcard_main data
    $sql_update_main = "UPDATE jobcard_main SET quotation_number=?, bill_on=?, jc_date=?, quotation_date=?, peyment_terms=?, client=?, branch_name=?, instructed_by=?, proposed_rate=?, s_location=?, completion_before=?, now_remark=?, jc_billable=?, involvements=?, user=?, last_edit=? WHERE jc_number=?";
    $stmt_update_main = $conn->prepare($sql_update_main);

    if ($stmt_update_main) {
        $stmt_update_main->bind_param("ssssssssdssssssss", $po, $bill, $jc_date, $quotation_date, $payment, $client, $branch_name, $instruct, $proposed, $location, $completion, $now_remark, $jc_billable, $involvements, $user, $datetime, $jc_number);

        $main_result = $stmt_update_main->execute();

        if (!$main_result) {
            echo "Error updating jobcard main: " . $stmt_update_main->error;
        }

        $stmt_update_main->close();
   }

   
}
}
}

if (isset($_GET['jc_number'])) {
    $jc_number = $_GET['jc_number'];
    
    // Use prepared statements to fetch data based on jc_number for jobcard_items
    $sql_items = "SELECT s_description, width, height, unit, qty, amount FROM jobcard_items WHERE jc_number = ?";
   
    $stmt_items = mysqli_prepare($conn, $sql_items);

    if ($stmt_items) {
        mysqli_stmt_bind_param($stmt_items, "s", $jc_number);
        mysqli_stmt_execute($stmt_items);
        $result_items = mysqli_stmt_get_result($stmt_items);

        $existingItems = [];

        // Fetch existing data and store it in the $existingItems array
        while ($row_items = mysqli_fetch_assoc($result_items)) {
            $existingItems[] = $row_items;
        }
       
        mysqli_stmt_close($stmt_items);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['ok'])) {
        if (isset($_POST['description'], $_POST['width'], $_POST['height'], $_POST['unit'], $_POST['qty'], $_POST['amount'])) {
            $descriptions = $_POST['description'];
            $widths = $_POST['width'];
            $heights = $_POST['height'];
            $units = $_POST['unit'];
            $qtys = $_POST['qty'];
            $amounts = $_POST['amount'];

            // Delete existing rows before adding updated data
            $deleteQuery = "DELETE FROM jobcard_items WHERE jc_number = ?";
            if ($stmt = $conn->prepare($deleteQuery)) {
                $stmt->bind_param("s", $jc_number);
                if ($stmt->execute()) {
                    echo "<script>alert('Job Card Edits Saved Successfully'); window.location = 'marketing-jc.php';</script>";
                } else {
                    echo "Error executing DELETE query: " . $stmt->error . "<br>";
                }
                $stmt->close();
            } else {
                echo "Error preparing DELETE query: " . $conn->error . "<br>";
            }

            // Insert data from both existing and new rows
            foreach ($descriptions as $index => $description) {
                $width = $widths[$index];
                $height = $heights[$index];
                $unit = $units[$index];
                $qty = $qtys[$index];
                $amount = $amounts[$index];
                $user = $_SESSION['user'];
                date_default_timezone_set('Asia/Kolkata');
    $datetime = date("d-m-y h:i:s");


                $sql = "INSERT INTO jobcard_items (jc_number, s_description, width, height, unit, qty, amount, user, last_edit) VALUES (?,?,?,?,?,?,?,?,?)";
                $stmt2 = $conn->prepare($sql);
                $stmt2->bind_param("ssddsidss", $jc_number, $description, $width, $height, $unit, $qty, $amount, $user, $datetime);
                if ($stmt2->execute()) {
                    echo "<script>alert('Job Card Edits Saved Successfully'); window.location = 'marketing-jc.php';</script>";
                } else {
                    echo "Error: " . $stmt2->error;
                }
                $stmt2->close();
                echo "<script>alert('Job Card Edits Saved Successfully'); window.location = 'marketing-jc.php';</script>";

            }
        }
    }
}

$sql_client = "SELECT DISTINCT client_name FROM client_masters ORDER BY client_name ASC";
$client_result = mysqli_query($conn, $sql_client);

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


            // Check if s_status is present in Preverification main Table
            $check_preveri_sql = "SELECT * FROM pre_varification_total WHERE jc_number = ?";
            $check_preveri_stmt = $conn->prepare($check_preveri_sql);

            if ($check_preveri_stmt) {
                $check_preveri_stmt->bind_param("s", $jc_number);
                $check_preveri_stmt->execute();
                $check_preveri_stmt->store_result();


                // Check if s_status is present in Fabrication main Table
                $check_fab_sql = "SELECT * FROM fabrication_main WHERE jc_number = ?";
                $check_fab_stmt = $conn->prepare($check_fab_sql);

                if ($check_fab_stmt) {
                    $check_fab_stmt->bind_param("s", $jc_number);
                    $check_fab_stmt->execute();
                    $check_fab_stmt->store_result();


                    // If s_status is present in any one of the tables, disable the button
                    if ($check_creative_stmt->num_rows > 0 || $check_production_stmt->num_rows > 0 || $check_preveri_stmt->num_rows > 0 || $check_fab_stmt->num_rows > 0) {
                        $buttonStatus = 'disabled';
                    } else {
                        $buttonStatus = ''; // Button is enabled
                    }
                }
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
    <form action="" method="POST" id="updateForm">
  <!-- Content Header (Page header) -->
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1 class="m-0">EDIT JOBCARD</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <input type="submit" class="btn btn-danger" name="jc_cancel" value="CANCEL JC" <?php echo $buttonStatus; ?>>
            </ol>
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
                <label for="client" class="form-label">Client</label><br>
                <input type="text" name="client" id="cl1" class="form-control" value="<?php echo $row_main['client']; ?>" readonly>
              </div>

   <div class="form-group">
    <label for="branch" class="form-label">Branch</label><br>
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
                <label for="location"class="form-label">Location<span class="text-danger">*</span></label>
                <input type="text"name="location"class="form-control" value="<?php echo $row_main['s_location']; ?>" required>
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
                  <label for="completion"class="form-label">Completion Before<span class="text-danger">*</span></label>
                  <input type="date"name="completion"class="form-control" value="<?php echo $row_main['completion_before']; ?>" required>
                </div>
                <div class="form-group">
                  <label for="jc_billable"class="form-label">Is Billable<span class="text-danger">*</span></label>
                  <select class="form-control" id="jc_billable" name="jc_billable" required>
                    <option value="" <?php echo $row_main['jc_billable'] === '' ? 'selected' : ''; ?>>--Selection Option--</option>
                    <option value="yes" <?php echo $row_main['jc_billable'] === 'yes' ? 'selected' : ''; ?>>Yes</option>
                    <option value="no" <?php echo $row_main['jc_billable'] === 'no' ? 'selected' : ''; ?>>No</option>
                  </select>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label for="bill on"class="form-label">Bill on<span class="text-danger">*</span></label>
                  <input type="text"name="bill_on"class="form-control" value="<?php echo $row_main['bill_on']; ?>" required>
                </div>
                <div class="form-group">
                  <label for="peyment"class="form-label">Payment Terms<span class="text-danger">*</span></label>
                  <input type="text"name="peyment_terms"class="form-control" value="<?php echo $row_main['peyment_terms']; ?>" required>
                </div>
                <div class="form-group">
                  <label for="proposed"class="form-label">Proposed Rate</label>
                  <input type="number"name="proposed"class="form-control" value="<?php echo $row_main['proposed_rate']; ?>" step="0.01" placeholder="0.00">
                </div>
                <div class="form-group">
                  <label for="now_remark"class="form-label">Nature Of Work/Remarks</label><span class="text-danger">*</span>
                  <textarea type="text"name="now_remark"id="now_remark"class="form-control"cols="10"rows="5" required><?php echo $row_main['now_remark']; ?></textarea>
                </div>
              </div>
              <div class="col-md-12">
                <div style="margin-top:25px;" class="card card-info card-outline">
                  <div class="card-header">
                    <h3 class="card-title">Involvements</h3>
                  </div>
                  <div class="card-body">
                    <div class="form-group">
                      <?php
                        $involvementsArray = explode(', ', $row_main['involvements']);
                        $creativeChecked = in_array('creative', $involvementsArray) ? 'checked' : '';
                        $productionChecked = in_array('production', $involvementsArray) ? 'checked' : '';
                        $fab_tvmChecked = in_array('fab_tvm', $involvementsArray) ? 'checked' : '';
                        $fab_ekmChecked = in_array('fab_ekm', $involvementsArray) ? 'checked' : '';
                        $cpsChecked = in_array('cps', $involvementsArray) ? 'checked' : '';
                        ?>
                        <label style="margin-right: 20px;"><input style="margin-right: 5px;" type="checkbox" name="selected_items[]" value="creative" <?php echo $creativeChecked; ?>> Creative</label>
                        <label style="margin-right: 20px;"><input style="margin-right: 5px;" type="checkbox" name="selected_items[]" value="production" <?php echo $productionChecked; ?>> Production</label>
                        <label style="margin-right: 20px;"><input style="margin-right: 5px;" type="checkbox" name="selected_items[]" value="fab_tvm" <?php echo $fab_tvmChecked; ?>> Fabrication TVM</label>
                        <label style="margin-right: 20px;"><input style="margin-right: 5px;" type="checkbox" name="selected_items[]" value="fab_ekm" <?php echo $fab_ekmChecked; ?>> Fabrication EKM</label>
                        <label style="margin-right: 20px;"><input style="margin-right: 5px;" type="checkbox" name="selected_items[]" value="cps" <?php echo $cpsChecked; ?>> CPS</label>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-md-12">
              <div style="margin-top:25px;" class="card card-info card-outline">
                <div class="card-header">
                  <h3 class="card-title">Job Details</h3>
                </div>
                <div class="card-body">
                  <table class="table table-bordered table-striped" border="2" id="dataTable">
                    <tr>
                      <th style="width:3%; text-align: center;"></th>
                      <th style="width:37%; text-align: center;">Description</th>
                      <th style="width:10%; text-align: center;">Width</th>
                      <th style="width:10%; text-align: center;">Height</th>
                      <th style="width:10%; text-align: center;">Unit</th>
                      <th style="width:10%; text-align: center;">Quantity</th>
                      <th style="width:10%; text-align: center;">Amount</th>
                    </tr>
                    <?php
                      // Loop through existing items and pre-fill the fields
                      foreach ($existingItems as $index => $item) {
                          $description = $item['s_description'];
                          $width = $item['width'];
                          $height = $item['height'];
                          $unit = $item['unit'];
                          $qty = $item['qty'];
                          $amount = $item['amount'];
                      ?>
                    <tr id="row<?php echo $index; ?>">
                      <td style="vertical-align:middle;"><input type="checkbox" name="ch<?php echo $index; ?>[]" id="ch<?php echo $index; ?>" class="delete-checkbox" style="width: 20px; height: 20px;"></td>
                      <td style="vertical-align:middle;"><textarea class="form-control" style="width:100%" name="description[]" id="dis<?php echo $index; ?>" rows="3"><?php echo $description; ?></textarea></td>
                      <td style="vertical-align:middle;"><input class="form-control" style="width:100%" type="number" name="width[]" step="0.01" placeholder="0.00" id="width<?php echo $index; ?>" value="<?php echo $width; ?>"></td>
                      <td style="vertical-align:middle;"><input class="form-control" style="width:100%" type="number" name="height[]" step="0.01" placeholder="0.00" id="height<?php echo $index; ?>" value="<?php echo $height; ?>"></td>
                      <td style="vertical-align:middle;">
                        <select class="form-control" name="unit[]" id="unit<?php echo $unit; ?>" style="width: 100%;">
                          <option value="" <?php if ($unit === '') echo 'selected'; ?>>-- Select Unit --</option>
                          <option value="mm" <?php if ($unit === 'mm') echo 'selected'; ?>>mm</option>
                          <option value="cm" <?php if ($unit === 'cm') echo 'selected'; ?>>cm</option>
                          <option value="m" <?php if ($unit === 'm') echo 'selected'; ?>>m</option>
                          <option value="inch" <?php if ($unit === 'inch') echo 'selected'; ?>>Inch</option>
                          <option value="feet" <?php if ($unit === 'feet') echo 'selected'; ?>>Feet</option>
                          <option value="numbers" <?php if ($unit === 'numbers') echo 'selected'; ?>>Numbers</option>
                        </select>
                      </td>
                      <td style="vertical-align:middle;"><input class="form-control" style="width:100%" type="number" name="qty[]" placeholder="0" id="qty<?php echo $qty; ?>" value="<?php echo $qty; ?>"></td>
                      <td style="vertical-align:middle;"><input class="form-control" style="width:100%" type="number" step="0.01" name="amount[]" placeholder="0.00" id="am1<?php echo $amount; ?>" value="<?php echo $amount; ?>"></td>
                    </tr>
                    <?php } ?>
                  </table>
                </div>
                <div class="card-footer">
                  <button class="btn btn-primary" type="button" name="addrow" id="addrow"><i class="fa fa-plus"></i> Add More</button>
                  <button class="btn btn-danger" type="button" name="delete" id="deleterow"><i class="fa fa-minus"></i> Delete</button>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="card-footer">
          <button class="btn btn-success" type="submit" name="ok" id="checkBtn">Save Job Card</button>
          <a class="btn btn-info float-right" href="marketing-jc.php">Close (without save)</a>
        </div>

    </div>
  </div>
</section>
      </form>
</div>

<!-- Include Footer File -->
<?php include_once ('../../../include/php/footer.php') ?>


<script>
    document.addEventListener("DOMContentLoaded", function () {
        var currentRow = <?php echo count($existingItems); ?>; // Initialize the row counter

        // Add a new row when the "Add More" button is clicked
        document.getElementById("addrow").addEventListener("click", function () {
            currentRow++; // Increment the row counter

            // Clone the last row of the table
            var table = document.getElementById("dataTable");
            var lastRow = table.rows[table.rows.length - 1];
            var newRow = lastRow.cloneNode(true);
            newRow.id = "row" + currentRow;

            // Clear the input fields in the new row
            var inputs = newRow.querySelectorAll("input, select, textarea");
            inputs.forEach(function (input) {
                var oldId = input.id;
                var newId = oldId + currentRow;
                input.id = newId;
                input.value = "";

                // Update the name attribute as well to make it unique
                var oldName = input.name;
                var newName = oldName + currentRow;
                input.name = newName;

                input.value = "";
            });

            // Append the new row to the table
            table.appendChild(newRow);
        });

        // Add a click event listener to the "Delete" button
        document.getElementById("deleterow").addEventListener("click", function () {
            // Get all checkboxes with class "delete-checkbox"
            var checkboxes = document.querySelectorAll(".delete-checkbox");
            var firstRowCheckbox = checkboxes[0]; // Get the checkbox of the first row
            checkboxes.forEach(function (checkbox) {

                // Loop through the checkboxes and remove the corresponding row if checked
                for (var i = 1; i < checkboxes.length; i++) {
                    if (checkbox.checked) {
                        // Find the parent row and remove it
                        var row = checkbox.closest("tr");
                        row.parentNode.removeChild(row);
                    }
                }
                // Uncheck the checkbox of the first row to prevent deletion
                firstRowCheckbox.checked = false;
            });
        });
    });
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

</body>
</html>