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

// Query to retrieve the current maximum id
         function generateUserId($conn) {
            // Query the database to get the current maximum job card number
            $query = "SELECT MAX(jc_number) AS max_jc_number FROM jobcard_main";
            $result = mysqli_query($conn, $query);
        
            if ($result && mysqli_num_rows($result) > 0) {
                $row = mysqli_fetch_assoc($result);
                $maxId = $row['max_jc_number'];
        
                // Determine the starting point
                $startNumber = 3677;
        
                // If no job cards exist yet, start from the specified value
                if ($maxId === null) {
                    return 'JC' . $startNumber;
                }
        
                // Extract the numeric part and increment
                $numericPart = (int)substr($maxId, 2) + 1;
        
                // Generate the next job card number by using the incremented numeric part
                $nextId = 'JC' . $numericPart;
        
                return $nextId;
            } else {
                // Handle errors or initial case when no job cards exist
                return 'JC3677';
            }
        }




if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["ok"])) {
   // Get the branch from the session or any other source
//    $branch = $_SESSION['branch']; // Make sure you have the branch value in your session

   $newUserId = generateUserId($conn);
   $_SESSION['new'] = $newUserId;
    if (isset($_POST['quotation_number'], $_POST['bill_on'], $_POST['jc_date'], $_POST['quotation_date'], $_POST['peyment'], $_POST['client'],$_POST['branch'], $_POST['instruct'], $_POST['proposed'], $_POST['location'], $_POST['completion'],$_POST['now_remark'],$_POST['jc_billable'], $_POST['selected_items'])) {

        $quotation_number = $_POST['quotation_number'];
        $bill = $_POST['bill_on'];
        $jc_date = $_POST['jc_date'];
        if (empty($_POST['quotation_date'])) {
          $quotation_date = NULL;
        } else {
          $quotation_date = $_POST['quotation_date'];
        }
        $peyment = $_POST['peyment'];
        $client = $_POST['client'];
        $branch = $_POST['branch'];
        $instruct = $_POST['instruct'];
        $proposed = $_POST['proposed'];
        $location = $_POST['location'];
        $completion = $_POST['completion'];
        $now_remark = $_POST['now_remark'];
        $jc_billable = $_POST['jc_billable'];
        $selectedItems = $_POST['selected_items'];
        $selectedItemsString = implode(', ', $selectedItems);
        $work_jc = $_POST['work_jc'];
        $pname=$_SESSION['user'];
        $csr=$_SESSION['user'];
        // Insert a new record with the incremented id
        $insert_sql = "INSERT INTO jobcard_main (jc_number,quotation_number,bill_on,jc_date,quotation_date,peyment_terms,client,branch_name,instructed_by,proposed_rate,s_location,completion_before,now_remark,jc_billable,involvements,work_jc,user,csr) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
        $stmt1 = $conn->prepare($insert_sql);
        $stmt1->bind_param("sssssssssissssssss", $newUserId, $quotation_number, $bill, $jc_date, $quotation_date, $peyment, $client,$branch, $instruct, $proposed, $location, $completion,$now_remark,$jc_billable,$selectedItemsString,$work_jc,$pname,$csr);
        if ($stmt1->execute()) {
            echo "";
        }else{
            echo "Error: " . $stmt1->error;

        }
        $stmt1->close();
    }

if(isset($_POST['description'], $_POST['width'], $_POST['height'], $_POST['unit'], $_POST['qty'], $_POST['amount'])) {

        $descriptions = $_POST['description'];
        $widths = $_POST['width'];
        $heights = $_POST['height'];
        $units = $_POST['unit'];
        $qtys = $_POST['qty'];
        $amounts = $_POST['amount'];
        for ($i = 0; $i < count($descriptions); $i++) {
            // Extract values for the current row
            $description = $descriptions[$i];
            $width = $widths[$i];
            $height = $heights[$i];
            $unit = $units[$i];
            $qty = $qtys[$i];
            $amount = $amounts[$i];
       
        $sql = "INSERT INTO jobcard_items (jc_number,s_description, width, height, unit, qty, amount) VALUES (?,?,?,?,?,?,?)";
        $stmt2 = $conn->prepare($sql);
        $stmt2->bind_param("ssddsid", $newUserId,$description, $width, $height, $unit, $qty, $amount);
        if ($stmt2->execute()) {
            echo "<script>alert('JC Created Successfully'); window.location = 'marketing-jc.php';</script>";
        }else{
            echo "Error: " . $stmt2->error;
        }
        $stmt2->close();

    }

}


}
$newUserId = generateUserId($conn);

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
          <h1 class="m-0">CREATE JOB CARD</h1>
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
                      <label for="jc_number" class="form-label" id="id">JC Number</label>
                      <input type="text"name="jc_number"class="form-control" value="<?php echo $newUserId; ?>"readonly>
                    </div>
                    <div class="form-group">
                      <label for="jc_date"class="form-label">JC Date<span class="text-danger">*</span></label>
                      <input type="date"name="jc_date"id="jc1"class="form-control" value="" required readonly>
                    </div>
                    
<?php
// Fetch client names from the client_masters table
$sqls = "SELECT client_name FROM client_masters";
$results = $conn->query($sqls);

// Check if there are rows in the result set
if ($results->num_rows > 0) {
    // Start the dropdown
    echo '<div class="form-group">';
    echo '<label for="client Name">Client Name<span class="text-danger">*</span></label> <button style="width:25px; height:25px; border-radius:100px;" class="btn btn-primary btn-xs" title="Add New Client" onclick="navCreateClient()"><i class="fas fa-plus"></i></a></button>';
    echo '<select name="client" id="cl1" class="form-control select2bs4" required>';
    // Add a default option
    echo '<option value="" disabled selected>-- Select Client --</option>';

    
    // Loop through each row and create an option for each client_name
    while ($rows = $results->fetch_assoc()) {
        echo '<option value="' . $rows['client_name'] . '">' . $rows['client_name'] . '</option>';
    }

    // End the dropdown
    echo '</select>';
    echo '</div>';
} else {
    // If there are no clients in the database
    echo '<p>No clients found</p>';
}
     ?>
<div class="form-group" id="branchContainer" style="display: none;">
    <!-- Branch options will be dynamically added here -->
</div>




                    <div class="form-group">
                      <label for="location"class="form-label">Location<span class="text-danger">*</span></label>
                      <input type="text"name="location"id="lo1"class="form-control" required>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label for="quotation_number"class="form-label">Quotation Number</label>
                      <input type="text"name="quotation_number"id="quotation_number"class="form-control">
                    </div>
                    <div class="form-group">
                      <label for="quotation_date"class="form-label">Quotation Date</label>
                      <input type="date"name="quotation_date"id="quotation_date"class="form-control" value="">
                    </div>
                    <div class="form-group">
                      <label for="instruct"class="form-label">Instructed By</label>
                      <input type="text"name="instruct"id="in1"class="form-control">
                    </div>
                    <div class="form-group">
                      <label for="completion"class="form-label">Completion Before<span class="text-danger">*</span></label>
                      <input type="date"name="completion"id="co1"class="form-control" required value="">
                    </div>
                    <div class="form-group">
                      <label for="jc_billable"class="form-label">Is Billable<span class="text-danger">*</span></label>
                      <select class="form-control" id="jc_billable" name="jc_billable" required>
                        <option value="" disabled selected>--Selection Option--</option>
                        <option value="yes">Yes</option>
                        <option value="no">No</option>
                      </select>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label for="bill_on"class="form-label">Bill on<span class="text-danger">*</span></label>
                      <input type="text"name="bill_on"id="on1"class="form-control" required>
                    </div>
                    <div class="form-group">
                      <label for="peyment"class="form-label">Payment Terms<span class="text-danger">*</span></label>
                      <input type="text"name="peyment"id="pay1"class="form-control" required>
                    </div>
                    <div class="form-group">
                      <label for="proposed"class="form-label">Proposed Rate</label>
                      <input type="number"name="proposed"id="pr1"class="form-control" step="0.01" placeholder="0.00">
                    </div>

                    <div class="form-group">
                      <label for="now_remark"class="form-label">Nature Of Work/Remarks</label><span class="text-danger">*</span>
                      <textarea type="text"name="now_remark"id="now_remark"class="form-control"cols="10"rows="5" required></textarea>
                    </div>
                    <input type="text"name="work_jc"id="work_jc"class="form-control" value="work_jc" hidden>
                  </div>
     


                  <div class="col-md-12">
                    <div style="margin-top:25px;" class="card card-info card-outline">
                      <div class="card-header">
                        <h3 class="card-title">Involvements<span class="text-danger">*</span></h3>
                      </div>
                      <div class="card-body">
                        <label style="margin-right: 20px;"><input style="margin-right: 5px;" type="checkbox" name="selected_items[]" value="creative"> Creative</label>
                        <label style="margin-right: 20px;"><input style="margin-right: 5px;" type="checkbox" name="selected_items[]" value="production"> Production</label>
                        <label style="margin-right: 20px;"><input style="margin-right: 5px;" type="checkbox" name="selected_items[]" value="fab_tvm"> Fabrication TVM</label>
                        <label style="margin-right: 20px;"><input style="margin-right: 5px;" type="checkbox" name="selected_items[]" value="fab_ekm"> Fabrication EKM</label>
                        <label style="margin-right: 20px;"><input style="margin-right: 5px;" type="checkbox" name="selected_items[]" value="cps"> CPS</label>
                      </div>
                    </div>
                  </div>
                  
                  <div class="col-md-12">
                    <div style="margin-top:25px;" class="card card-info card-outline">
                      <div class="card-header">
                        <h3 class="card-title">Job Details</h3>
                      </div>
                      <div class="card-body">
                        <table class="table table-bordered table-striped" id="dataTable">
                          <tr>
                            <th style="width:3%; text-align: center;"></th>
                            <th style="width:37%; text-align: center;">Description<span class="text-danger">*</span></th>
                            <th style="width:10%; text-align: center;">Width</th>
                            <th style="width:10%; text-align: center;">Height</th>
                            <th style="width:10%; text-align: center;">Unit</th>
                            <th style="width:10%; text-align: center;">Quantity</th>
                            <th style="width:10%; text-align: center;">Amount</th>
                          </tr>
                          <tr id="row0">
                            <td style="vertical-align:middle;"><input type="checkbox" name="ch1[]" id="ch[]"class="delete-checkbox checkbox"></td>              
                            <td style="vertical-align:middle;"><textarea class="form-control" style="width:100%;" name="description[]" id="dis[]" required></textarea></td>
                            <td style="vertical-align:middle;"><input class="form-control" style="width:100%;" type="number"name="width[]"id="width[]" step="0.01" placeholder="0.00"></td>
                            <td style="vertical-align:middle;"><input class="form-control" style="width:100%;" type="number"name="height[]"id="height[]" step="0.01" placeholder="0.00"></td>
                            <td style="vertical-align:middle;"><select class="form-control" style="width:100%;"  name="unit[]" id="unit[]">
                              <option value="" selected>--Select Unit--</option>
                              <option value="mm">mm</option>
                              <option value="cm">cm</option>
                              <option value="m">m</option>
                              <option value="inch">Inch</option>
                              <option value="feet">Feet</option>
                              <option value="numbers">Numbers</option>
                            </select></td>
                            <td style="vertical-align:middle;"><input class="form-control" style="width:100%;" type="number"name="qty[]"id="qty[]" placeholder="0"></td>
                            <td style="vertical-align:middle;"><input class="form-control" style="width:100%;" type="number" name="amount[]" id="am1[]" step="0.01" placeholder="0.00"></td>
                          </tr>
                        </table>
                      </div>
                      <div class="card-footer">
                        <button type="button" class="btn btn-outline-info" name="addrow"id="addrow"><i class="fa fa-plus"></i> Add Item Row</button>
                        <button type="button" class="btn btn-outline-danger" name="delete" id="deleterow"><i class="fa fa-trash"></i> Delete</button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="card-footer">
            <button class="btn btn-success" name="ok" id="checkBtn">SAVE JOB CARD</button>
            <button class="btn btn-primary float-right" onclick="btncloseWsave()">Close (without save)</a>
          </div>
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
            var currentRow = 0; // Initialize the row counter

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

<script>
function navCreateClient() {
  let text = "Press OK - to Go to Client Creation Page! Or Press Cancel to stay on this page.";
  if (confirm(text) == true) {
    window.location.href = '../client-master/marketing-client-create.php';
  } 
}
</script>

<script type="text/javascript">
  document.getElementById('jc1').valueAsDate = new Date();
</script>

<script>
$(document).ready(function(){
    // Event listener for client dropdown change
    $('#cl1').on('change', function(){
        var selectedClient = $(this).val();

        // Clear existing branch options
        $('#branchContainer').empty();

        // Check if a client is selected
        if (selectedClient !== '') {
            // AJAX request to fetch branch names for the selected client
            $.ajax({
                url: 'fetch_branches.php', // Replace with the actual PHP file to handle AJAX request
                type: 'POST',
                data: {client: selectedClient},
                success: function(response){
                    // Display branch options or input based on the response
                    $('#branchContainer').html(response).show();
                }
            });
        } else {
            // Hide branch container if no client is selected
            $('#branchContainer').hide();
        }
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