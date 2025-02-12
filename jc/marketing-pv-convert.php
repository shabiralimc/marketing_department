<?php

// Include the database connection configuration
include_once("../../../include/php/connect.php");

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
    $sql_main = "SELECT jc_number, quotation_number, bill_on, jc_date, quotation_date, peyment_terms, client,branch_name, instructed_by, proposed_rate, s_location, completion_before,now_remark,jc_billable,jc_status, involvements FROM jobcard_main WHERE jc_number = ?";
    $stmt_main = mysqli_prepare($conn, $sql_main);

    if ($stmt_main) {
        mysqli_stmt_bind_param($stmt_main, "s", $jc_number);
        mysqli_stmt_execute($stmt_main);
        $result_main = mysqli_stmt_get_result($stmt_main);
        $row_main = mysqli_fetch_assoc($result_main);
        mysqli_stmt_close($stmt_main);
    }
    $sql_item = mysqli_query($conn, "SELECT * FROM jobcard_items_pv WHERE jc_number='$jc_number'");

    $existingItems = [];

     // Use prepared statements to fetch data based on jc_number for jobcard_items
     $check_items_sql = "SELECT jc_number FROM jobcard_items WHERE jc_number = ?";

     $stmt_items = mysqli_prepare($conn, $check_items_sql);
 
     if ($stmt_items) {
         mysqli_stmt_bind_param($stmt_items, "s", $jc_number);
         mysqli_stmt_execute($stmt_items);
         $result_items = mysqli_stmt_get_result($stmt_items);
 
   
         if (mysqli_num_rows($result_items) > 0) {
            $isUpdateItems = true;
            $sql_items = "SELECT id,s_description, width, height, unit, qty, amount FROM jobcard_items WHERE jc_number = ?";
            $stmt_items = mysqli_prepare($conn, $sql_items);
            
            if ($stmt_items) {
            mysqli_stmt_bind_param($stmt_items, "s", $jc_number);
            mysqli_stmt_execute($stmt_items);
            $result_items = mysqli_stmt_get_result($stmt_items);
            
            // // Loop through the results if needed
            while ($row_item = mysqli_fetch_assoc($result_items)) {
            
            // Store data for each row in the array
            $existingItems[] = $row_item; // Uncomment this line to store data
            
            }
            
            // Close the $stmt_items after the loop
            mysqli_stmt_close($stmt_items);
            }
            } else {
            $isUpdateItems = false;
            }
            
            } else {
            $isUpdateItems = false;
            }
         
}
$involvementsArray = [];  // Initialize an empty array

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['ok'])) {
        $quotation_number = $_POST['quotation_number'];
        $bill = $_POST['bill_on'];
        $jc_date = $_POST['jc_date'];
        if (empty($_POST['quotation_date'])) {
          $quotation_date = NULL;
        } else {
          $quotation_date = $_POST['quotation_date'];
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
        $selectedItems = isset($_POST['selected_items']) ? $_POST['selected_items'] : [];
// If selectedItems is empty, set $involvements to an empty string
$involvements = $selectedItems ? implode(', ', $selectedItems) : '';
$work_jc = $_POST['work_jc'];
$pv_to_jc = $_POST['pv_to_jc'];

// Update jobcard_main data
$sql_update_main = "UPDATE jobcard_main SET quotation_number=?, bill_on=?, jc_date=?, quotation_date=?, peyment_terms=?, client=?, branch_name=?, instructed_by=?, proposed_rate=?, s_location=?, completion_before=?, now_remark=?, jc_billable=?, jc_status=?, involvements=?,work_jc=?,pv_to_jc=? WHERE jc_number=?";
$stmt_update_main = $conn->prepare($sql_update_main);

if ($stmt_update_main) {
    $stmt_update_main->bind_param("ssssssssisssssssss", $quotation_number, $bill, $jc_date, $quotation_date, $payment, $client, $branch, $instruct, $proposed, $location, $completion, $now_remark, $jc_billable, $jc_status, $involvements,$work_jc, $pv_to_jc, $jc_number);

    $main_result = $stmt_update_main->execute();

    if (!$main_result) {
        echo "Error updating jobcard main: " . $stmt_update_main->error;
    }

    $stmt_update_main->close();
} else {
    echo "Error preparing UPDATE query: " . $conn->error;
}
    
   // Inside the if ($_SERVER['REQUEST_METHOD'] === 'POST') condition
if (isset($_POST['s_description'], $_POST['width'], $_POST['height'], $_POST['unit'], $_POST['qty'], $_POST['amount'])) {
    $descriptions = $_POST['s_description'];
    $widths = $_POST['width'];
    $heights = $_POST['height'];
    $units = $_POST['unit'];
    $qtys = $_POST['qty'];
    $amounts = $_POST['amount'];
    $itemIds = $_POST['id']; // Assuming you have this field in your form

    
    // Loop through the arrays and insert/update each row separately
    for ($i = 0; $i < count($descriptions); $i++) {
        $description = $descriptions[$i];
        $width = $widths[$i];
        $height = $heights[$i];
        $unit = $units[$i];
        $qty = $qtys[$i];
        $amount = $amounts[$i];
        $itemId = $itemIds[$i]; // Assuming you have this field in your form
        
        if (!empty($itemId)) {
            // Update the existing record in the jobcard_items table
            $update_items_sql = "UPDATE jobcard_items SET s_description=?, width=?, height=?, unit=?, qty=?, amount=? WHERE id=?";
            $stmt_items = $conn->prepare($update_items_sql);
            $stmt_items->bind_param("sddsddi", $description, $width, $height, $unit, $qty, $amount, $itemId);
        } else {
            // Insert a new record into the jobcard_items table
            $insert_items_sql = "INSERT INTO jobcard_items (jc_number, s_description, width, height, unit, qty, amount) VALUES (?,?,?,?,?,?,?)";
            $stmt_items = $conn->prepare($insert_items_sql);
            $stmt_items->bind_param("ssddsdd", $jc_number, $description, $width, $height, $unit, $qty, $amount);
        }
        // Execute the prepared statement
        if (!$stmt_items->execute()) {
            echo "Error executing SQL statement: " . $stmt_items->error;
        }

        // Close the statement
        $stmt_items->close();
        echo "<script>alert('Job Card Edits Saved Successfully'); window.location = 'marketing-jc.php';</script>";
    }
}

    }
    
}
// Initialize $buttonStatus
$buttonStatus = '';

    // Check if s_status is present in creative_main
    $check_creative_sql = "SELECT s_status FROM creative_main WHERE jc_number = ?";
    $check_creative_stmt = $conn->prepare($check_creative_sql);

    if ($check_creative_stmt) {
        $check_creative_stmt->bind_param("s", $jc_number);
        $check_creative_stmt->execute();
        $check_creative_stmt->store_result();

        // Check if s_status is present in production_jc_po_main
        $check_production_sql = "SELECT s_status FROM production_jc_po_main WHERE jc_number = ?";
        $check_production_stmt = $conn->prepare($check_production_sql);

        if ($check_production_stmt) {
            $check_production_stmt->bind_param("s", $jc_number);
            $check_production_stmt->execute();
            $check_production_stmt->store_result();

            // If s_status is present in both tables, disable the button
            if ($check_creative_stmt->num_rows > 0 && $check_production_stmt->num_rows > 0) {
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
                    echo "JC Status updated to 'cancelled' successfully.";

                      // Redirect to jobcard.php after successful update
                header("Location:jobcard.php");

                 // Set session variable to disable the EDIT button
                $_SESSION['disable_edit_button'] = true;
                $_SESSION['cancelled_jc_number'] = $jc_number;

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
          <h1 class="m-0">CONVERT PV TO JC</h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <input type="submit" class="btn btn-danger" name="jc_cancel" value="JC Cancel" <?php echo $buttonStatus; ?>>
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

      <form action="" method="POST" id="updateForm">

        <div class="card">
          <div class="card-body">

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
                      <label for="client" class="form-label">Client</label>
                      <input name="client" id="cl1" class="form-control" value="<?php echo $row_main['client']?>" readonly>
                    </div>
                    <div class="form-group">
                      <label for="branch" class="form-label">Branch</label>
                      <input name="branch_name"id="branch_name" class="form-control" value="<?php echo $row_main['branch_name'];?>" readonly>
                    </div>
                    <div class="form-group">
                      <label for="location"class="form-label">Location</label>
                      <input type="text"name="location"class="form-control"value="<?php echo $row_main['s_location']; ?>" readonly>
                    </div>

                  </div>

                  <div class="col-md-4">

                    <div class="form-group">
                      <label for="quotation_number"class="form-label">Quotation Number</label>
                      <input type="text"name="quotation_number"class="form-control" value="<?php echo $row_main['quotation_number']; ?>">
                    </div>
                    <div class="form-group">
                      <label for="quotation_date"class="form-label">PO Date</label>
                      <input type="date"name="quotation_date"class="form-control" value="<?php echo $row_main['quotation_date']; ?>">
                    </div>
                    <div class="form-group">
                      <label for="insurted"class="form-label">Insurted By</label>
                      <input type="text"name="instruct"class="form-control" value="<?php echo $row_main['instructed_by']; ?>">
                    </div>
                    <div class="form-group">
                      <label for="completion"class="form-label">Completion Before<span class="text-danger">*</span></label>
                      <input type="date"name="completion"class="form-control" value="<?php echo $row_main['completion_before']; ?>" required>
                    </div>
                    <div class="form-group">
                      <label for="jc_billable"class="form-label">Is Billable<span class="text-danger">*</span></label>
                      <select class="form-control" id="jc_billable" name="jc_billable" required>
                        <option value="yes" <?php echo $row_main['jc_billable'] === 'yes' ? 'selected' : ''; ?>>Yes</option>
                        <option value="no" <?php echo $row_main['jc_billable'] === 'no' ? 'selected' : ''; ?>>No</option>
                      </select>
                      <input hidden type="date"name="pv_to_jc"class="form-control" value="<?php echo date('Y-m-d'); ?>">
                    </div>

                  </div>

                  <div class="col-md-4">

                    <div class="form-group">
                      <label for="bill on"class="form-label">bill on<span class="text-danger">*</span></label>
                      <input type="text"name="bill_on"class="form-control" value="<?php echo $row_main['bill_on']; ?>" required>
                    </div>

                    <div class="form-group">
                      <label for="peyment"class="form-label">Peyment Terms<span class="text-danger">*</span></label>
                      <input type="text"name="peyment_terms"class="form-control" value="<?php echo $row_main['peyment_terms']; ?>" required>
                    </div>

                    <div class="form-group">
                      <label for="proposed"class="form-label">Proposed Rate</label>
                      <input type="number"name="proposed"class="form-control" value="<?php echo $row_main['proposed_rate']; ?>">
                    </div>

                    <div class="form-group">
                      <label for="now_remark"class="form-label">Nature Of Work/Remarks<span class="text-danger">*</span></label>
                      <textarea type="text"name="now_remark"id="now_remark"class="form-control"cols="10"rows="5" required><?php echo $row_main['now_remark']; ?></textarea>
                    </div>

                  </div>

                </div>
              </div>
            </div>

            <div class="card card-info card-outline">
              <div class="card-header">
                <div class="card-title">Involvements<span class="text-danger">*</span></div>
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
                <input type="hidden"name="work_jc"id="work_jc"value="work_jc">
              </div>
            </div>

            <div class="card card-info card-outline">
              <div class="card-header">
                <div class="card-title">Job Details</div>
              </div>
              <div class="card-body">

                <table class="table table-bordered table-striped" id="dataTable">
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
                  foreach ($existingItems as $index => $item) {
                  ?>

                  <tr id="row<?php echo $index; ?>">
                    <td><input type="checkbox" name="delete[]" id="delete<?php echo $index; ?>" class="delete-checkbox" style="width: 20px; height: 20px"></td>
                    <td><textarea class="form-control" name="s_description[]" id="s_description<?php echo $index; ?>"readonly><?php echo $item['s_description']; ?></textarea></td>
                    <td><input class="form-control" type="number" name="width[]" id="width<?php echo $index; ?>" step="0.01" placeholder="0.00" value="<?php echo $item['width']; ?>"readonly></td>
                    <td><input class="form-control" type="number" name="height[]" id="height<?php echo $index; ?>" step="0.01" placeholder="0.00" value="<?php echo $item['height']; ?>"readonly></td>
                    <td><input type="text" class="form-control" name="unit[]" id="unit<?php echo $index; ?>"value="<?php echo $item['unit']; ?>"readonly></td>
                    <td><input class="form-control" type="number" name="qty[]" id="qty<?php echo $index; ?>" placeholder="0" value="<?php echo $item['qty']; ?>"readonly></td>
                    <td><input class="form-control" type="number" name="amount[]" id="amount<?php echo $index; ?>" step="0.01" placeholder="0.00" value="<?php echo $item['amount']; ?>"readonly><input class="form-control" type="hidden" name="id[]" id="id<?php echo $index; ?>" value="<?php echo $item['id']; ?>"></td>
                  </tr>

                  <?php
                  }
                  ?>
                </table>

              </div>

              <div class="card-footer">
                <button class="btn btn-primary" type="button" name="addrow" id="addrow"><i class="fa fa-plus"></i> Add Row</button>
                <button class="btn btn-danger" type="button" name="delete" id="delete"><i class="fa fa-minus"></i> Delete Row</button>
              </div>

            </div>
          </div> 

          <div class="card-footer">
            <button class="btn btn-success" name="ok" id="checkBtn">SAVE JOB CARD</button>
            <button class="btn btn-primary" onclick="btncloseWsave()">Close (without save)</a>
          </div>

        </div>
      </form>
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

    // Function to update the involvements input field
    function updateInvolvementsField(checkboxes) {
        var involvementsField = document.getElementById("involvementsField");
        var selectedInvolvements = [];

        checkboxes.forEach(function (checkbox) {
            if (checkbox.checked) {
                selectedInvolvements.push(checkbox.value);
            }
        });

        // Add the existing involvements to the selected ones
        var existingInvolvements = involvementsField.value.split(',').map(function (item) {
            return item.trim();
        });

        selectedInvolvements = selectedInvolvements.concat(existingInvolvements);

        // Remove duplicates from the array
        selectedInvolvements = [...new Set(selectedInvolvements)];

        involvementsField.value = selectedInvolvements.join(', ');
    }
});

</script>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        var currentRow = <?php echo count($existingItems); ?>; // Initialize the row counter
// Add a new row when the "Add More" button is clicked
document.getElementById("addrow").addEventListener("click", function () {
        var table = document.getElementById("dataTable");
        var newRow = table.insertRow(-1); // Append a new row at the end
        var currentRow = table.rows.length - 1; // Update the current row counter

        // Insert cells for each column in the table
        var cell1 = newRow.insertCell(0);
        var cell2 = newRow.insertCell(1);
        var cell3 = newRow.insertCell(2);
        var cell4 = newRow.insertCell(3);
        var cell5 = newRow.insertCell(4);
        var cell6 = newRow.insertCell(5);
        var cell7 = newRow.insertCell(6);

        // Set the content of the cells
        cell1.innerHTML = '<input type="checkbox" name="delete[]" class="delete-checkbox" style="width: 20px; height: 20px;">';
        cell2.innerHTML = '<textarea name="s_description[]" id="s_description' + currentRow + '" class="form-control"></textarea>';
        cell3.innerHTML = '<input type="number" name="width[]" id="width' + currentRow + '" class="form-control">';
        cell4.innerHTML = '<input type="number" name="height[]" id="height' + currentRow + '" class="form-control">';
        cell5.innerHTML = '<select name="unit[]" class="form-control" id="unit' + currentRow + '"></select>';
        cell6.innerHTML = '<input type="number" name="qty[]" id="qty' + currentRow + '" class="form-control">';
        cell7.innerHTML = '<input type="number" name="amount[]" class="form-control" id="amount' + currentRow + '"><input type="hidden" name="id[]" id="id' + currentRow + '" value="">';

        var unitDropdown = cell5.querySelector("select");
        unitDropdown.innerHTML = '<option value="mm">mm</option><option value="cm">cm</option><option value="m">m</option><option value="inch">inch</option><option value="feet">feet</option><option value="numbers">numbers</option>';
    });


       
  document.getElementById("delete").addEventListener("click", function () {
var selectedIds = [];
var checkboxes = document.querySelectorAll(".delete-checkbox:checked");

checkboxes.forEach(function (checkbox) {
// Get the closest row (tr) and remove it from the table
var row = checkbox.closest("tr");
row.remove();

// Get the hidden input field with item_id and add its value to the selectedIds array
var itemIdInput = row.querySelector("input[name='id[]']");
if (itemIdInput) {
selectedIds.push(itemIdInput.value);
}
});

// Send the selected IDs to your server using AJAX
if (selectedIds.length > 0) {
var xhr = new XMLHttpRequest();
xhr.open("POST", "", true);
xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
xhr.onreadystatechange = function () {
if (xhr.readyState == 4 && xhr.status == 200) {
    // Handle the response from the server if needed
    console.log(xhr.responseText);
}
};
xhr.send("selectedIds=" + selectedIds.join(","));
}
});

});
</script>
<?php
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["selectedIds"])) {
$selectedIds = explode(",", $_POST["selectedIds"]);

// Delete records from the database
foreach ($selectedIds as $id) {
$delete_items_sql = "DELETE FROM jobcard_items WHERE id=?";
$stmt_delete_items = $conn->prepare($delete_items_sql);
$stmt_delete_items->bind_param("i", $id);
$stmt_delete_items->execute();

}
}
?>

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

<script>
function btncloseWsave() {
  let text = "Press OK - to Close without Save! Canel to stay on this page";
  if (confirm(text) == true) {
    window.location.href = 'marketing-jc.php';
  } 
}
</script>


</body>
</html>