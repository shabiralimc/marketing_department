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


if (isset($_GET['id'])) {
    $id = $_GET['id'];

    $sql_client = "SELECT id, client_name, nature_of_client, gst_pan_number, name_of_ceo, aadhar_details,landphone_no,mob_no,email_id,contact_no,nature_of_work,monthly_volume,special_terms,terms_of_payment,bank_details,csr FROM client_masters WHERE id = ?";
    $stmt_client = mysqli_prepare($conn, $sql_client);

    if ($stmt_client) {
        mysqli_stmt_bind_param($stmt_client, "i", $id);
        mysqli_stmt_execute($stmt_client);
        $result_client = mysqli_stmt_get_result($stmt_client);
        $row_client = mysqli_fetch_assoc($result_client);
        mysqli_stmt_close($stmt_client);
    }

    $sql_items = "SELECT branch_name, billing_address FROM client_billing_masters WHERE client_name = ?";
    $stmt_items = mysqli_prepare($conn, $sql_items);

    if ($stmt_items) {
        mysqli_stmt_bind_param($stmt_items, "s", $row_client['client_name']);
        mysqli_stmt_execute($stmt_items);
        $result_items = mysqli_stmt_get_result($stmt_items);

        $existingItems = [];

        while ($row_items = mysqli_fetch_assoc($result_items)) {
            $existingItems[] = $row_items;
        }

        mysqli_stmt_close($stmt_items);
    }
     // Store the existing branch names in an array
     $existingBranchNames = array_column($existingItems, 'branch_name');
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["ok"])) {
    if (isset($_POST['client_name'], $_POST['nature_of_client'], $_POST['gst_pan_number'], $_POST['name_of_ceo'], $_POST['aadhar_details'], $_POST['landphone_no'], $_POST['mob_no'], $_POST['email_id'], $_POST['contact_no'],$_POST['nature_of_work'],$_POST['monthly_volume'],$_POST['special_terms'],$_POST['terms_of_payment'],$_POST['bank_details'],$_POST['csr'])) {

        $client_name = $_POST['client_name'];
        $nature_of_client = $_POST['nature_of_client'];
        $gst_pan_number = $_POST['gst_pan_number'];
        $name_of_ceo = $_POST['name_of_ceo'];
        $aadhar_details = $_POST['aadhar_details'];
        $landphone_no = $_POST['landphone_no'];
        $mob_no = $_POST['mob_no'];
        $email_id = $_POST['email_id'];
        $contact_no = $_POST['contact_no'];
        $nature_of_work = $_POST['nature_of_work'];
        $monthly_volume = $_POST['monthly_volume'];
        $special_terms = $_POST['special_terms'];
        $terms_of_payment = $_POST['terms_of_payment'];
        $bank_details = $_POST['bank_details'];
        $csr = $_POST['csr'];
      
        $sql_update_client = "UPDATE client_masters SET client_name=?, nature_of_client=?, gst_pan_number=?, name_of_ceo=?, aadhar_details= ?, landphone_no=?,mob_no=?,email_id=?,contact_no=?,nature_of_work=?,monthly_volume=?,special_terms=?,terms_of_payment=?,bank_details=?,csr=? WHERE  id=?";
        $stmt_main= $conn->prepare($sql_update_client);
        $stmt_main->bind_param("sssssiisssdssssi", $client_name, $nature_of_client, $gst_pan_number, $name_of_ceo, $aadhar_details, $landphone_no, $mob_no, $email_id, $contact_no,$nature_of_work,$monthly_volume,$special_terms,$terms_of_payment,$bank_details,$csr,$id);
    
        if ($stmt_main->execute()) {
            echo "<script>alert('Client Edits Saved Successfully'); window.location = 'marketing-client-edit.php?id=$id'; </script>";
        } else {
            echo "Error: " . $stmt_main->error;
        }

        // Close the statement
        $stmt_main->close();

        if(isset($_POST['branch_name'], $_POST['billing_address'])) {
            $branch_names = $_POST['branch_name'];
            $billing_addresss = $_POST['billing_address'];

            foreach ($branch_names as $index => $branch_name) {
                $billing_address = $billing_addresss[$index];

                // Check if the row is newly added by checking if the branch_name is not in the existingBranchNames array
            if (!empty($branch_name) && !in_array($branch_name, $existingBranchNames)) {
                $sql_insert = "INSERT INTO client_billing_masters (client_name, branch_name, billing_address) VALUES (?,?,?)";
                $stmt_insert = $conn->prepare($sql_insert);
                $stmt_insert->bind_param("sss", $client_name, $branch_name, $billing_address);

                if ($stmt_insert->execute()) {
                    echo "<script>alert('Client Edits Saved Successfully'); window.location = 'marketing-client-edit.php?id=$id'; </script>";
                } else {
                    echo "Error: " . $stmt_insert->error;
                }

                // Close the statement
                $stmt_insert->close();
            }
        }
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
          <h1 class="m-0">CREATE NEW CLIENT</h1>
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
          <div class="card card-primary card-outline">
            <form action=""method="POST">
              <div class="card-body">
                <div class="row">
                  <div class="col-md-6">
                    <div class="card card-info card-outline">
                      <div class="card-header">
                        <h5>GENERAL DETAILS</h5>
                      </div>
                      <div class="card-body">
                        <div class="row">
                          <div class="col-md-6">

                            <div class="form-group">
                              <label for="client name"class="form-label">Client Name</label><span class="text-danger">*</span>
                              <input type="text"class="form-control"name="client_name"placeholder="client name"value="<?php echo $row_client['client_name']; ?>"readonly>
                            </div>

                            <div class="form-group">
                              <label for="nature_of_client"class="form-label">Nature Of Client</label>
                              <input type="text"class="form-control"name="nature_of_client"placeholder="Nature Of Client"value="<?php echo $row_client['nature_of_client']; ?>">
                            </div>

                            <div class="form-group">
                              <label for="gst_pan_number"class="form-label">GST & PAN Number</label>
                              <input type="text" class="form-control"name="gst_pan_number"placeholder="GST & PAN Number" value="<?php echo $row_client['gst_pan_number']; ?>">
                            </div>

                            <div class="form-group">
                              <label for="name_of_ceo"class="form-label">Name Of CEO/MD/Proprietor</label>
                              <input type="text"class="form-control"name="name_of_ceo"placeholder=" Name Of CEO/MD/Proprietor " value="<?php echo $row_client['name_of_ceo']; ?>">
                            </div>

                            <div class="form-group">
                              <label for="aadhar_details"class="form-label">Aadhaar Details</label>
                              <input type="text"class="form-control"name="aadhar_details"placeholder="Aadhaar Details" value="<?php echo $row_client['aadhar_details']; ?>">
                            </div>

                            <div class="form-group">
                              <label for="landphone_no"class="form-label">Land Phone No.</label>
                              <input type="number"class="form-control"name="landphone_no"placeholder="Land Phone No" value="<?php echo $row_client['landphone_no']; ?>"red>
                            </div>

                            <div class="form-group">
                              <label for="mob_no"class="form-label">Mobile No.</label>
                              <input type="number"class="form-control"name="mob_no"placeholder="Mobile No" value="<?php echo $row_client['mob_no']; ?>">
                            </div>

                            <div class="form-group">
                              <label for="email_id"class="form-label">Email ID</label>
                              <input type="text"class="form-control"name="email_id"placeholder="Email ID " value="<?php echo $row_client['email_id']; ?>">
                            </div>   

                          </div>
                          <div class="col-md-6">

                            <div class="form-group">
                              <label for="contact_no"class="form-label">Second Person to be Contacted With Contact No.</label>
                              <input type="text"class="form-control"name="contact_no"placeholder="Second Contact Number" value="<?php echo $row_client['contact_no']; ?>">
                            </div>

                            <div class="form-group">
                              <label for="nature_of_work"class="form-label">Nature Of Work</label>
                              <input type="text"class="form-control"name="nature_of_work"placeholder="Nature Of Work" value="<?php echo $row_client['nature_of_work']; ?>">
                            </div>

                            <div class="form-group">
                              <label for="monthly_volume"class="form-label">Expected Monthly Volume</label>
                              <input type="text"class="form-control"name="monthly_volume"placeholder="Expected Monthly Volume" value="<?php echo $row_client['monthly_volume']; ?>" step="0.01">
                            </div>

                            <div class="form-group">
                              <label for="special_terms"class="form-label">Special T & C</label>
                              <input type="text"class="form-control"name="special_terms"placeholder="If Any Special Terms or Conditions" value="<?php echo $row_client['special_terms']; ?>">
                            </div>

                            <div class="form-group">
                              <label for="terms_of_payment"class="form-label">Terms Of Payment</label>
                              <input type="text"class="form-control"name="terms_of_payment"placeholder="Terms Of Payment" value="<?php echo $row_client['terms_of_payment']; ?>">
                            </div>

                            <div class="form-group">
                              <label for="bank_details"class="form-label">Bank Details</label>
                              <textarea class="form-control"name="bank_details"placeholder="Bank Details " rows="4" cols="50"><?php echo $row_client['bank_details']; ?></textarea>
                            </div>

                            <div class="form-group">
                              <label for="csr"class="form-label">CSR</label>
                              <input type="text"class="form-control"name="csr"placeholder="user" value="<?php echo $row_client['csr']; ?>">
                            </div>

                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="card card-info card-outline">
                      <div class="card-header">
                        <h5>Billing Addresses<span class="text-danger">*</span></h5>
                      </div>
                      <div class="card-body">
                        <table class="table table-bordered table-striped" id="clientTable">
            <thead>
                <tr>
                    <th>Sl.No</th>
                    <th>Branch Name</th>
                    <th>Billing Address</th>
                </tr>
            </thead>
            <tbody>
            <?php
            // Loop through existing items and pre-fill the fields
            foreach ($existingItems as $index => $item) {
                $branch_name = $item['branch_name'];
                $billing_address = $item['billing_address'];
                ?>
                <tr id="row<?php echo $index; ?>">
                <td><input type="checkbox" name="checkbox<?php echo $index; ?>" id="checkbox<?php echo $index; ?>" class="delete-checkbox" style="width: 20px; height: 20px;"></td>
                <td><input type="text" class="form-control" name="branch_name[]" id="branch_name<?php echo $index; ?>" value="<?php echo $branch_name; ?>"></td>
                <td><input type="text" class="form-control" name="billing_address[]" id="billing_address<?php echo $index; ?>" value="<?php echo $billing_address; ?>"></td>
            </tr>
            <?php } ?>
            </tbody>
            </table>
                      </div>
                      <div class="card-footer">
                        <button type="button" class="btn btn-outline-info" name="addrow" id="addrow"><i class="fa fa-plus"></i> Add Item Row</button>
                        <button type="button" class="btn btn-outline-danger" name="deleterow" id="deleterow"><i class="fa fa-trash"></i> Delete</button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="card-footer">
                <input type="submit"class="btn btn-success"name="ok"value="Save Edits">
                <a href="marketing-client-index.php" class="btn btn-info">Close Without Save</a>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </section>
</div>

<!-- Include Footer File -->
<?php include_once ('../../../include/php/footer.php') ?>

<script>
document.addEventListener("DOMContentLoaded", function () {
    var currentRow = <?php echo count($existingItems); ?>;

    document.getElementById("addrow").addEventListener("click", function () {
        currentRow++;

        var table = document.getElementById("clientTable");
        var lastRow = table.rows[table.rows.length - 1];
        var newRow = lastRow.cloneNode(true);
        newRow.id = "row" + currentRow;

        var inputs = newRow.querySelectorAll("input");
        inputs.forEach(function (input) {
            var oldId = input.id;
            var newId = oldId + currentRow;
            input.id = newId;
            input.value = "";

            var oldName = input.name;
            var newName = oldName + currentRow;
            input.name = newName;
            input.value = "";

        });

        table.appendChild(newRow);
    });
    
    document.getElementById("deleterow").addEventListener("click", function () {
    // Get all checkboxes with class "delete-checkbox"
    var checkboxes = document.querySelectorAll(".delete-checkbox");

    // Create an array to store checkboxes that are checked
    var checkedCheckboxes = [];

    checkboxes.forEach(function (checkbox) {
        if (checkbox.checked) {
            // Store checked checkboxes in the array
            checkedCheckboxes.push(checkbox);
        }
    });

    // Check if there are any checkboxes checked before attempting to delete rows
    if (checkedCheckboxes.length > 0) {
        // Iterate over the checked checkboxes
        checkedCheckboxes.forEach(function (checkbox) {
            // Find the parent row and remove it from the DOM
            var row = checkbox.closest("tr");
            row.parentNode.removeChild(row);

            // Get the branch name associated with the row
            var branchName = row.querySelector("[name^='branch_name']").value;

            // Make an AJAX request to delete the row from the server
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "delete_row.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

            // Send the branch name as data to identify the row to delete on the server
            xhr.send("branch_name=" + encodeURIComponent(branchName));

            // You may want to handle the server response here if needed
            xhr.onload = function () {
                if (xhr.status === 200) {
                    console.log("Row deleted successfully on the server");
                } else {
                    console.error("Error deleting row on the server");
                }
            };
        });
    }
});

 });
</script>

</body>
</html>