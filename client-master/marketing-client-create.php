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

        // Insert  Details of clients
        $insert_sql = "INSERT INTO client_masters (client_name,nature_of_client,gst_pan_number,name_of_ceo,aadhar_details,landphone_no,mob_no,email_id,contact_no,nature_of_work,monthly_volume,special_terms,terms_of_payment,bank_details,csr) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
        $stmt1 = $conn->prepare($insert_sql);
        $stmt1->bind_param("sssssissssdssss", $client_name, $nature_of_client, $gst_pan_number, $name_of_ceo, $aadhar_details, $landphone_no, $mob_no, $email_id, $contact_no,$nature_of_work,$monthly_volume,$special_terms,$terms_of_payment,$bank_details,$csr);
        if ($stmt1->execute()) {
            echo "";
        }else{
            echo "Error: " . $stmt1->error;

        }
        $stmt1->close();
    }
    if(isset($_POST['branch_name'], $_POST['billing_address'])) {
        $branch_name = $_POST['branch_name'];
        $billing_address = $_POST['billing_address'];
    
        // Check if these variables are arrays before using count()
        if (is_array($branch_name) && is_array($billing_address)) {
            for ($i = 0; $i < count($branch_name); $i++) {
                // Extract values for the current row
                $branch_names = $branch_name[$i];
                $billing_addresss = $billing_address[$i];
    
                $sql = "INSERT INTO client_billing_masters (client_name, branch_name, billing_address) VALUES (?, ?, ?)";
                $stmt2 = $conn->prepare($sql);
                $stmt2->bind_param("sss", $client_name, $branch_names, $billing_addresss); 
    
                if ($stmt2->execute()) {
                    echo "<script>alert('Client Created Successfully'); window.location = 'marketing-client-index.php'; </script>";
                } else {
                    echo "Error: " . $stmt2->error;
                }
                $stmt2->close();
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
                              <label for="client name"class="form-label">Client Name </label><span class="text-danger">*</span>
                              <input type="text"class="form-control"name="client_name"placeholder="client name"required>
                            </div>

                            <div class="form-group">
                              <label for="nature_of_client"class="form-label">Nature Of Client </label>
                              <input type="text"class="form-control"name="nature_of_client"placeholder="Nature Of Client">
                            </div>
                            
                            <div class="form-group">
                              <label for="gst_pan_number"class="form-label">GST/PAN Number </label>
                              <input type="text" class="form-control"name="gst_pan_number"placeholder="GST/PAN Number">
                            </div>
                            
                            <div class="form-group">
                              <label for="name_of_ceo"class="form-label">Name Of CEO/MD/Proprietor </label>
                              <input type="text"class="form-control"name="name_of_ceo"placeholder="Name Of CEO/MD/Proprietor">
                            </div>

                            <div class="form-group">
                              <label for="aadhar_details"class="form-label">Aadhaar Details</label>
                              <input type="text"class="form-control"name="aadhar_details"placeholder="Aadhaar Details">
                            </div>

                            <div class="form-group">
                              <label for="landphone_no"class="form-label">Land Phone No.</label>
                              <input type="number"class="form-control"name="landphone_no"placeholder="Land Phone No">
                            </div>

                            <div class="form-group">
                              <label for="mob_no"class="form-label">Mobile No.</label>
                              <input type="text"class="form-control"name="mob_no"placeholder="Mobile No">
                            </div>

                            <div class="form-group">
                              <label for="email_id"class="form-label">Email ID</label>
                              <input type="text"class="form-control"name="email_id"placeholder="Email ID">
                            </div>

                          </div>
                          <div class="col-md-6">

                            <div class="form-group">
                              <label for="contact_no"class="form-label">Second Person & Contact No.</label>
                              <input type="text"class="form-control"name="contact_no"placeholder="Second Contact Number">
                            </div>

                            <div class="form-group">
                              <label for="nature_of_work"class="form-label">Nature Of Work</label>
                              <input type="text"class="form-control"name="nature_of_work"placeholder="Nature Of Work">
                            </div>

                            <div class="form-group">
                              <label for="monthly_volume"class="form-label">Expected Monthly Volume</label>
                              <input type="text"class="form-control"name="monthly_volume"placeholder="Expected Monthly Volume" step="0.01">
                            </div>

                            <div class="form-group">
                              <label for="special_terms"class="form-label">Special T & C</label>
                              <input type="text"class="form-control"name="special_terms"placeholder="If Any Special Terms or Conditions">
                            </div>

                            <div class="form-group">
                              <label for="terms_of_payment"class="form-label">Terms Of Payment </label>
                              <input type="text"class="form-control"name="terms_of_payment"placeholder="Terms Of Payment">
                            </div>

                            <div class="form-group">
                              <label for="bank_details"class="form-label">Bank Details</label>
                              <textarea class="form-control"name="bank_details"placeholder="Bank Details " rows="4" cols="50"></textarea>
                            </div>

                            <div class="form-group">
                              <label for="csr"class="form-label">CSR</label>
                              <input type="text"class="form-control"name="csr"placeholder="user">
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
                              <th></th>
                              <th>Sl.No</th>
                              <th>Branch Name</th>
                              <th>Billing Address</th>
                            </tr>
                          </thead>
                          <tbody>
                            <?php
                            $siNo = 0; // Initialize Si. No
                            $index = 0;

                                $index++;
                            ?>
                            <tr id="row<?php echo $index; ?>">
                              <td><input type="checkbox" name="delete[]" id="delete<?php echo $index; ?>" class="delete-checkbox" style="width: 20px; height: 20px;"></td>
                              <td><?php echo $siNo++; ?></td>
                              <td><input type="text" name="branch_name[]" id="branch_name"placeholder="Branch Name" class="form-control" required></td>
                              <td><input type="text" name="billing_address[]" id="billing_address" placeholder="Branch Address"class="form-control"required></td>
                            </tr>
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
                <input type="submit"class="btn btn-success"name="ok"value="Create Client">
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
    var currentRow =0; // Initialize the row counter

  // Add a new row when the "Add More" button is clicked
  document.getElementById("addrow").addEventListener("click", function () {
        currentRow++; // Increment the row counter

        // Clone the last row of the table
        var table = document.getElementById("clientTable");
        var lastRow = table.rows[table.rows.length - 1];
        var newRow = lastRow.cloneNode(true);
        newRow.id = "row" + currentRow;

        // Clear the input fields in the new row
        var inputs = newRow.querySelectorAll("input, textarea");
        inputs.forEach(function (input) {
            var oldId = input.id;
            var newId = oldId.replace(/\d+/, currentRow); // Update the numeric part of the ID
            input.id = newId;
            input.value = "";

            // Update the name attribute as well to make it unique
            var oldName = input.name;
            var newName = oldName.replace(/\d+/, currentRow); // Update the numeric part of the name
            input.name = newName;

            input.value = "";
        });

        // Increment the Sl.No in the new row
        var slNoCell = newRow.querySelector("td:nth-child(2)");
        slNoCell.textContent = currentRow;

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

</body>
</html>