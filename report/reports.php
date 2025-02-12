<?php
include_once('../../../include/php/connect.php');

if (isset($_GET['jc_number'])) {
    $newUserId = $_GET['jc_number'];

    $jobmain = mysqli_query($conn, "SELECT * FROM jobcard_main WHERE jc_number = '$newUserId'");
    $jobmainrow = mysqli_fetch_assoc($jobmain);

    $involvements = $jobmainrow['involvements'];
    $involvements_array = explode(",", $involvements);

    // Remove spaces from each element in the array
    $involvements_array = array_map('trim', $involvements_array);

    $jobcard_items = mysqli_query($conn, "SELECT * FROM jobcard_items WHERE jc_number='$newUserId'");
    $pv_items = mysqli_query($conn, "SELECT * FROM jobcard_items_pv WHERE jc_number='$newUserId'");
    
    $creative_items = mysqli_query($conn, "SELECT * FROM creative_items WHERE jc_number='$newUserId'");
    $production_jc_po_items = mysqli_query($conn, "SELECT * FROM production_invoice_main WHERE jc_number='$newUserId'");

    $fab_main_items = mysqli_query($conn, "SELECT * FROM fabrication_main WHERE jc_number='$newUserId'");
    $pv_main_items = mysqli_query($conn, "SELECT * FROM pre_varification_total WHERE jc_number='$newUserId'");

    $fab_labour_expences = mysqli_query($conn, "SELECT * FROM fab_labour_expences WHERE jc_number='$newUserId'");
    $fab_transport_expences = mysqli_query($conn, "SELECT * FROM fab_transport_expences WHERE jc_number='$newUserId'");
    $fab_other_expences = mysqli_query($conn, "SELECT * FROM fab_other_expences WHERE jc_number='$newUserId'");
    $fab_mat_expences = mysqli_query($conn, "SELECT * FROM fab_mat_expences WHERE jc_number='$newUserId'");
    $pre_varifacation_labour = mysqli_query($conn, "SELECT * FROM pre_varifacation_labour WHERE jc_number='$newUserId'");
    $pre_varification_transport = mysqli_query($conn, "SELECT * FROM pre_varification_transport WHERE jc_number='$newUserId'");
    $pre_varification_other = mysqli_query($conn, "SELECT * FROM pre_varification_other WHERE jc_number='$newUserId'");

    // Initialize total for jobcard items
    $totalJobcard = 0;

    
    if ($jobmainrow['pre_varification'] === 'pre_varification' && $jobmainrow['work_jc'] === '') {
      echo '<div class="alert alert-warning alert-dismissible"><h5><i class="icon fas fa-exclamation-triangle"></i> Alert!</h5>This is a PRE-VERIFICATION Job Card</div>';
    } elseif ($jobmainrow['pre_varification'] === '' && $jobmainrow['work_jc'] === 'work_jc') {
      echo '<div class="alert alert-warning alert-dismissible"><h5><i class="icon fas fa-exclamation-triangle"></i> Alert!</h5>This is a WORK Job Card with involvements - '.$jobmainrow['involvements'].'</div>';
    } elseif ($jobmainrow['pre_varification'] === 'pre_varification' && $jobmainrow['work_jc'] === 'work_jc') {
      echo '<div class="alert alert-warning alert-dismissible"><h5><i class="icon fas fa-exclamation-triangle"></i> Alert!</h5>This is a PRE-VERIFICATION & WORK Job Card with involvements - '.$jobmainrow['involvements'].'</div>';
    }
    


    // Check for Preverification is available display preverification details

    if ($jobmainrow['pre_varification'] === 'pre_varification') {

        $serialNumber = 1;

        $html01 = '
        <div class="card card-info card-outline">
        <div class="card-header">
          <h3>Pre-Verification Work Details</h3>
        </div>
        <div class="card-body">
        <table class="table table-striped table-bordered" id="pv_item_table">
          <thead>
            <tr>
              <th style="width:10%; text-align:center;">Sl No.</th>
              <th style="width:90%; text-align:center;">PV Details</th>
            </tr>
          </thead>
          <tbody>';

        while ($pv_row = mysqli_fetch_assoc($pv_items)) {

        $html01 .= '
        <tr>
          <td style="vertical-align:middle; text-align:center;">' . $serialNumber++ . '</td>
          <td style="vertical-align:middle;">' . $pv_row['pre_details'] . '</td>          
        </tr>';

        }

        $html01 .= '</tbody>
            </table>
        </div></div>';

        echo $html01;
    } 


// Check for Preverification is available display preverification Involvements
    

    if ($jobmainrow['pre_varification'] === 'pre_varification') {

      $serialNumber = 1;

      $html25 = '
      <div class="card card-info card-outline">
        <div class="card-header">
          <h3>PV - FAB - Labour Involvement Details</h3>
        </div>
        <div class="card-body">
        <table class="table table-striped table-bordered" id="pv_labour_table">
          <thead>
            <tr>
              <th style="text-align:center;">Sl No.</th>
              <th style="text-align:center;">Type</th>
              <th style="text-align:center;">Activity</th>
              <th style="text-align:center;">Staff Name</th>
              <th style="text-align:center;">Place</th>
              <th style="text-align:center;">Start Date & Time</th>
              <th style="text-align:center;">End Date & Time</th>
              <th style="text-align:center;">OT Hrs</th>
              <th style="text-align:center;">OT Amount</th>
              <th style="text-align:center;">Regular Hrs</th>
              <th style="text-align:center;">Regular Amount</th>
              <th style="text-align:center;">Total Amount</th>
            </tr>
          </thead>
          <tbody>';

          $totalprelabour =0;

          while($prelabourexpences = mysqli_fetch_assoc($pre_varifacation_labour)){

            $html25 .= '
              <tr>
                <td style="vertical-align:middle; text-align:center;">' . $serialNumber++ . '</td>
                <td style="vertical-align:middle; text-align:center;">' . $prelabourexpences['expences'] . '</td>
                <td style="vertical-align:middle; text-align:center;">' . $prelabourexpences['type'] . '</td>
                <td style="vertical-align:middle; text-align:center;">' . $prelabourexpences['name'] . '</td>
                <td style="vertical-align:middle; text-align:center;">' . $prelabourexpences['place'] . '</td>
                <td style="vertical-align:middle; text-align:center;">' . date("d-m-Y h:i A", strtotime($prelabourexpences['date'])) . '</td>
                <td style="vertical-align:middle; text-align:center;">' . date("d-m-Y h:i A", strtotime($prelabourexpences['endtime'])) . '</td>
                <td style="vertical-align:middle; text-align:center;">' . $prelabourexpences['total_ot'] . '</td>
                <td style="vertical-align:middle; text-align:center;">' . number_format($prelabourexpences['labour_cost'], 2) . '</td>
                <td style="vertical-align:middle; text-align:center;">' . $prelabourexpences['regular_time'] . '</td>
                <td style="vertical-align:middle; text-align:center;">' . number_format($prelabourexpences['regular_expences'], 2) . '</td>
                <td style="vertical-align:middle; text-align:center;">' . number_format($prelabourexpences['total_lab_cost'], 2) . '</td>
              </tr>';

              $totalprelabour += $prelabourexpences['total_lab_cost'];
            }

          $html25 .= '
          </tbody>
          <tfoot>
            <tr>
              <th style="text-align:right;" colspan="11">Pre Verification Labour Total</th>
              <th style="text-align:center;">' . number_format($totalprelabour, 2) . '</th>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>';

        echo $html25;

    } 


// PREVERIFICATION TRANSPORT INVOLVEMENTS

    if ($jobmainrow['pre_varification'] === 'pre_varification') {

      $serialNumber = 1;

      $html8 = '
      <div class="card card-info card-outline">
        <div class="card-header">
          <h3>PV - FAB - Transport Involvement Details</h3>
        </div>
        <div class="card-body">
          <table class="table table-striped table-bordered" id="pv_transport_table" style="table-layout: fixed; width: 100%;">
            <thead>
              <tr>
                <th style="text-align:center;">Sl No.</th>
                <th style="text-align:center;">Date</th>
                <th style="text-align:center;">Staff Name</th>
                <th style="text-align:center;">Vehicle</th>
                <th style="text-align:center;">From</th>
                <th style="text-align:center;">To</th>
                <th style="text-align:center;">KM</th>
                <th style="text-align:center;">Total Transport Cost</th>
              </tr>
            </thead>
          <tbody>';

          $totalpretransport = 0;

          while($pretransportexpences = mysqli_fetch_assoc($pre_varification_transport)){

            $html8 .= '
            <tr>
              <td style="text-align:center;">' . $serialNumber++ . '</td>
              <td style="text-align:center;">' . date("d-m-Y", strtotime($pretransportexpences['pre_tran_date'])) . '</td>
              <td style="text-align:center;">' . $pretransportexpences['staff_name'] . '</td>
              <td style="text-align:center;">' . $pretransportexpences['vehicle'] . '</td>
              <td style="text-align:center;">' . $pretransportexpences['from'] . '</td>
              <td style="text-align:center;">' . $pretransportexpences['to'] . '</td>
              <td style="text-align:center;">' . $pretransportexpences['km'] . '</td>
              <td style="text-align:center;">' . number_format($pretransportexpences['cost'], 2) . '</td>
            </tr>';

            $totalpretransport += $pretransportexpences['cost'];
          }

          $html8 .= '
          </tbody>
          <tfoot>
            <tr>
              <th colspan="7" style="text-align:right;">Pre Verification Transport Total</th>
              <th style="text-align:center;">' . number_format($totalpretransport, 2) . '</th>
            </tr>
          </tfoot>
        </table>
        </div>
        </div>';

      echo $html8;

      } 


// PREVERIFICATION OTHER INVOLVEMENTS


    if ($jobmainrow['pre_varification'] === 'pre_varification') {

      $serialNumber = 1;

      $html9 = '
      <div class="card card-info card-outline">
        <div class="card-header">
          <h3>PV - FAB - Other Involvement Details</h3>
        </div>
        <div class="card-body">
          <table class="table table-striped table-bordered" id="pv_other_table" style="table-layout: fixed; width: 100%;">
            <thead>
              <tr>
                <th style="text-align:center;">Sl No.</th>
                <th style="text-align:center;">Date</th>
                <th style="text-align:center;">Staff Name</th>
                <th style="text-align:center;">Expences</th>
                <th style="text-align:center;">Remark</th>
                <th style="text-align:center;">Total Other Cost</th>
              </tr>
            </thead>
            <tbody>';

            $totalpreother = 0;

            while($preotherexpences = mysqli_fetch_assoc($pre_varification_other)){

              $html9 .= '
                <tr>
                  <td style="text-align:center;">' . $serialNumber++ . '</td>
                  <td style="text-align:center;">' . date("d-m-Y", strtotime($preotherexpences['pre_other_date'])) . '</td>
                  <td style="text-align:center;">' . $preotherexpences['staff_names'] . '</td>
                  <td style="text-align:center;">' . $preotherexpences['exp'] . '</td>
                  <td style="text-align:center;">' . $preotherexpences['remark'] . '</td>
                  <td style="text-align:center;">' . number_format($preotherexpences['other_costs'], 2) . '</td>
                </tr>';

                $totalpreother += $preotherexpences['other_costs'];
              }

      $html9 .= '
        </tbody>
          <tfoot>
            <tr>
              <th colspan="5" style="text-align:right;">Pre Verification other Total</th>
              <th style="text-align:center;">' . number_format($totalpreother, 2) . '</th>
            </tr>
          </tfoot>
        </table>
      </div>
      </div>';

      echo $html9;

      } 




// Check for Work JC available Display work jc details

    if ($jobmainrow['work_jc'] === 'work_jc') {

      $serialNumber = 1;

      $html02 = '
      <div class="card card-info card-outline">
        <div class="card-header">
          <h3>Work JC Items Detail</h3>
        </div>
        <div class="card-body">
        <table class="table table-striped table-bordered" id="jc_items_table">
          <thead>
            <tr>
              <th style="text-align:center; width:5%">Sl No.</th>
              <th style="text-align:center; width:40%">Description</th>
              <th style="text-align:center; width:10%">Width</th>
              <th style="text-align:center; width:10%">Height</th>
              <th style="text-align:center; width:10%">Unit</th>
              <th style="text-align:center; width:10%">Qty</th>
              <th style="text-align:center; width:15%">Amount</th>
            </tr>
          </thead>
          <tbody>';

          while ($jobcardrow = mysqli_fetch_assoc($jobcard_items)) {

            $html02 .= '
              <tr>
                <td style="text-align:center; vertical-align:middle;">' . $serialNumber++ . '</td>
                <td style="text-align:center; vertical-align:middle;">' . $jobcardrow['s_description'] . '</td>
                <td style="text-align:center; vertical-align:middle;">' . $jobcardrow['width'] . '</td>
                <td style="text-align:center; vertical-align:middle;">' . $jobcardrow['height'] . '</td>
                <td style="text-align:center; vertical-align:middle;">' . $jobcardrow['unit'] . '</td>
                <td style="text-align:center; vertical-align:middle;">' . $jobcardrow['qty'] . '</td>
                <td style="text-align:center; vertical-align:middle;">' . number_format($jobcardrow['amount'], 2) . '</td>
              </tr>';
            }

            $html02 .= '
                  </tbody>
                </table>
              </div>
            </div>';

    echo $html02;

    } 


    // Check for Work JC available and Involvements have creative Display creative details

    if ($jobmainrow['work_jc'] === 'work_jc' && in_array('creative', $involvements_array)) {

      $serialNumber = 1;

      $html03 = '
      <div class="card card-info card-outline">
        <div class="card-header">
          <h3>Creative Involvement Details</h3>
        </div>
        <div class="card-body">
        <table class="table table-striped table-bordered" id="creativeitems_table" style="table-layout: fixed; width: 100%;">
          <thead>
            <tr>
              <th style="text-align:center;">Sl No.</th>
              <th style="text-align:center;">Designer</th>
              <th style="text-align:center;">Activity</th>
              <th style="text-align:center;">Start Date & Time</th>
              <th style="text-align:center;">End Date & Time</th>
              <th style="text-align:center;">Amount</th>
            </tr>
          </thead>
          <tbody>';

          $totalcreative = 0;

          while($creativeRow = mysqli_fetch_assoc($creative_items)){

            $html03 .= '
              <tr>
                <td style="text-align:center;">' . $serialNumber++ . '</td>
                <td style="text-align:center;">' . $creativeRow['desinger'] . '</td>
                <td style="text-align:center;">' . $creativeRow['activity'] . '</td>
                <td style="text-align:center;">' . date("d-m-Y h:i A", strtotime($creativeRow['start_date_time'])) . '</td>
                <td style="text-align:center;">' . date("d-m-Y h:i A", strtotime($creativeRow['end_date_time'])) . '</td>
                <td style="text-align:center;">' . number_format($creativeRow['item_amount'], 2) . '</td>
              </tr>';

              $totalcreative += $creativeRow['item_amount'];
            }

          $html03 .= '
          </tbody>
          <tfoot>
            <tr>
              <th colspan="5" style="text-align:right;">Creative Items Total</th>
              <th style="text-align:center;">' . number_format($totalcreative, 2) . '</th>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>';

    echo $html03;

    } 


    // Check for Work JC available and Involvements have production Display details

    if ($jobmainrow['work_jc'] === 'work_jc' && in_array('production', $involvements_array)) {

      $serialNumber = 1;

      $html04 = '
      <div class="card card-info card-outline">
        <div class="card-header">
          <h3>Production Involvement Details</h3>
        </div>
        <div class="card-body">
        <table class="table table-striped table-bordered" id="production_table" style="table-layout: fixed; width: 100%;">
          <thead>
            <tr>
              <th style="text-align:center;">Sl No.</th>
              <th style="text-align:center;">PO Number</th>
              <th style="text-align:center;">Invoice Number</th>
              <th style="text-align:center;">Invoice Date</th>
              <th style="text-align:center;">Amount</th>
              <th style="text-align:center;">Freight Chrgs.</th>
              <th style="text-align:center;">Addl Expences</th>
              <th style="text-align:center;">Total Expences </th>
            </tr>
          </thead>
          <tbody>';

          $totalproduction = 0;

          while($productionRow = mysqli_fetch_assoc($production_jc_po_items)){

            $html04 .= '
              <tr>
                <td style="text-align:center;">' . $serialNumber++ . '</td>
                <td style="text-align:center;">' . $productionRow['po_number'] . '</td>
                <td style="text-align:center;">' . $productionRow['invoice_number'] . '</td>
                <td style="text-align:center;">' . date("d-m-Y", strtotime($productionRow['invoice_date'])) . '</td>
                <td style="text-align:center;">' . number_format($productionRow['amount'], 2) . '</td>
                <td style="text-align:center;">' . number_format($productionRow['freight'], 2) . '</td>
                <td style="text-align:center;">' . number_format($productionRow['addl_expences'], 2) . '</td>
                <td style="text-align:center;">' . number_format($productionRow['total_expences'], 2) . '</td>
              </tr>';

              $totalproduction +=$productionRow['total_expences'];
            }

          $html04 .= '
          </tbody>
          <tfoot>
            <tr>
              <th colspan="7" style="text-align:right;">Production Items Total</th>
              <th style="text-align:center;">' . number_format($totalproduction, 2) . '</th>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>';

    echo $html04;

    } 



    // Check for Work JC available and Involvements have FAB TVM or FAB EKM Display details

    if ($jobmainrow['work_jc'] === 'work_jc' && in_array('fab_tvm', $involvements_array) || in_array('fab_ekm', $involvements_array)) {

      $serialNumber = 1;
      
      $html3 = '
      <div class="card card-info card-outline">
        <div class="card-header">
          <h3>JC - FAB - Labour Involvement Details</h3>
        </div>
        <div class="card-body">
        <table class="table table-striped table-bordered" id="jc_labour_table">
          <thead>
            <tr>
              <th style="text-align:center;">Sl No.</th>
              <th style="text-align:center;">Type</th>
              <th style="text-align:center;">Activty</th>
              <th style="text-align:center;">Staff Name</th>
              <th style="text-align:center;">Place</th>
              <th style="text-align:center;">Start Date & Time</th>
              <th style="text-align:center;">End Date & Time</th>
              <th style="text-align:center;">OT Hrs.</th>
              <th style="text-align:center;">OT Amount</th>
              <th style="text-align:center;">Regular Hrs.</th>
              <th style="text-align:center;">Regular Amount</th>
              <th style="text-align:center;">Total Amount</th>
            </tr>
          </thead>
          <tbody>';

          $totalfablabour = 0;

          while($fablabourexpences = mysqli_fetch_assoc($fab_labour_expences)){

            $html3 .= '
              <tr>
                <td style="text-align:center;">' . $serialNumber++ . '</td>
                <td style="text-align:center;">' . $fablabourexpences['expences'] . '</td>
                <td style="text-align:center;">' . $fablabourexpences['type'] . '</td>
                <td style="text-align:center;">' . $fablabourexpences['name'] . '</td>
                <td style="text-align:center;">' . $fablabourexpences['place'] . '</td>
                <td style="text-align:center;">' . date("d-m-Y h:i A", strtotime($fablabourexpences['date'])) . '</td>
                <td style="text-align:center;">' . date("d-m-Y h:i A", strtotime($fablabourexpences['endtime'])) . '</td>
                <td style="text-align:center;">' . $fablabourexpences['total_ot'] . '</td>
                <td style="text-align:center;">' . number_format($fablabourexpences['labour_cost'], 2) . '</td>
                <td style="text-align:center;">' . $fablabourexpences['regular_time'] . '</td>
                <td style="text-align:center;">' . number_format($fablabourexpences['regular_expences'], 2) . '</td>
                <td style="text-align:center;">' . number_format($fablabourexpences['total_lab_cost'], 2) . '</td>
              </tr>';

              $totalfablabour += $fablabourexpences['total_lab_cost'];
            } 

            $html3 .= '
            </tbody>
              <tfoot>
                <tr>
                  <th colspan="11" style="text-align:right;">Fabrication Labour Total</th>
                  <th style="text-align:center;">' . number_format($totalfablabour, 2) . '</th>
                </tr>
              </tfoot>
            </table>
            </div>
            </div>';

    echo $html3;

    } 

    // 
    // 
    //

    if ($jobmainrow['work_jc'] === 'work_jc' && in_array('fab_tvm', $involvements_array) || in_array('fab_ekm', $involvements_array)) {

      $serialNumber = 1;
      
      $html4 = '
      <div class="card card-info card-outline">
        <div class="card-header">
          <h3>JC - FAB - Transport Involvement Details</h3>
        </div>
        <div class="card-body">
        <table class="table table-striped table-bordered" id="jc_transport_table" style="table-layout: fixed; width: 100%;">
          <thead>
            <tr>
              <th style="text-align:center;">Sl No.</th>
              <th style="text-align:center;">Date</th>
              <th style="text-align:center;">Staff Name</th>
              <th style="text-align:center;">Vehicle</th>
              <th style="text-align:center;">From</th>
              <th style="text-align:center;">To</th>
              <th style="text-align:center;">KM</th>
              <th style="text-align:center;">Total Transport Cost</th>
            </tr>
          </thead>
          <tbody>';

          $totalfabtransport = 0;

          while($fabtransportexpences = mysqli_fetch_assoc($fab_transport_expences)){

            $html4 .= '
              <tr>
                <td style="text-align:center;">' . $serialNumber++ . '</td>
                <td style="text-align:center;">' . date("d-m-Y", strtotime($fabtransportexpences['fab_tran_date'])) . '</td>
                <td style="text-align:center;">' . $fabtransportexpences['staff_name'] . '</td>
                <td style="text-align:center;">' . $fabtransportexpences['vehicle'] . '</td>
                <td style="text-align:center;">' . $fabtransportexpences['from'] . '</td>
                <td style="text-align:center;">' . $fabtransportexpences['to'] . '</td>
                <td style="text-align:center;">' . $fabtransportexpences['km'] . '</td>
                <td style="text-align:center;">' . number_format($fabtransportexpences['cost'], 2) . '</td>
              </tr>';

              $totalfabtransport += $fabtransportexpences['cost'];

            }

            $html4 .= '
              </tbody>
              <tfoot>
                <tr>
                  <th colspan="7" style="text-align:right;">Fabrication Transport Total</th>
                  <th style="text-align:center;">' . number_format($totalfabtransport, 2) . '</th>
                </tr>
              </tfoot>
            </table>
            </div>
            </div>';

    echo $html4;

    } 


// 
// 
// 
    if ($jobmainrow['work_jc'] === 'work_jc' && in_array('fab_tvm', $involvements_array) || in_array('fab_ekm', $involvements_array)) {

      $serialNumber = 1;
      
      $html5 = '
      <div class="card card-info card-outline">
        <div class="card-header">
          <h3>JC - FAB - Other Involvement Details</h3>
        </div>
        <div class="card-body">
        <table class="table table-striped table-bordered" id="jc_other_table" style="table-layout: fixed; width: 100%;">
          <thead>
            <tr>
              <th style="text-align:center;">Sl No.</th>
              <th style="text-align:center;">Date</th>
              <th style="text-align:center;">Staff Name</th>
              <th style="text-align:center;">Expences</th>
              <th style="text-align:center;">Remark</th>
              <th style="text-align:center;">Total Other Cost</th>
            </tr>
          </thead>
          <tbody>';

          $totalfabother = 0;

          while($fabotherexpences = mysqli_fetch_assoc($fab_other_expences)){

            $html5 .= '
              <tr>
                <td style="text-align:center;">' . $serialNumber++ . '</td>
                <td style="text-align:center;">' . date("d-m-Y", strtotime($fabotherexpences['fab_other_date'])) . '</td>
                <td style="text-align:center;">' . $fabotherexpences['staff_names'] . '</td>
                <td style="text-align:center;">' . $fabotherexpences['exp'] . '</td>
                <td style="text-align:center;">' . $fabotherexpences['remark'] . '</td>
                <td style="text-align:center;">' . number_format($fabotherexpences['other_costs'], 2) . '</td>
              </tr>';

              $totalfabother += $fabotherexpences['other_costs'];

            }

            $html5 .= '
              </tbody>
              <tfoot>
                <tr>
                  <th colspan="5" style="text-align:right;">Fabrication Transport Total</th>
                  <th style="text-align:center;">' . number_format($totalfabother, 2) . '</th>
                </tr>
              </tfoot>
            </table>
            </div>
            </div>';

    echo $html5;

    } 

// 
//
// 

    if ($jobmainrow['work_jc'] === 'work_jc' && in_array('fab_tvm', $involvements_array) || in_array('fab_ekm', $involvements_array)) {

      $serialNumber = 1;

      $html6 = '
      <div class="card card-info card-outline">
        <div class="card-header">
          <h3>JC - FAB - Material Involvement Details</h3>
        </div>
        <div class="card-body">
        <table class="table table-striped table-bordered" id="jc_material_table">
          <thead>
          <tr>
            <th style="text-align:center;">Sl No.</th>
            <th style="text-align:center;">Date</th>
            <th style="text-align:center;">Activity</th>
            <th style="text-align:center;">Material Name</th>
            <th style="text-align:center;">Measuring Unit</th>
            <th style="text-align:center;">Quantity</th>
            <th style="text-align:center;">Per Cost</th>
            <th style="text-align:center;">Total Material Cost</th>
          </tr>
        </thead>
        <tbody>';

        $totalfabmaterial = 0;

        while($fabmaterialexpences = mysqli_fetch_assoc($fab_mat_expences)){

          $html6 .= '
            <tr>
              <td style="text-align:center;">' . $serialNumber++ . '</td>
              <td style="text-align:center;">' . date("d-m-Y", strtotime($fabmaterialexpences['product_date'])) . '</td>
              <td style="text-align:center;">' . $fabmaterialexpences['activity'] . '</td>
              <td style="text-align:center;">' . $fabmaterialexpences['material_name'] . '</td>
              <td style="text-align:center;">' . $fabmaterialexpences['measuring_unit'] . '</td>
              <td style="text-align:center;">' . $fabmaterialexpences['quantity'] . '</td>
              <td style="text-align:center;">' . $fabmaterialexpences['per_cost'] . '</td>
              <td style="text-align:center;">' . number_format($fabmaterialexpences['total_cost'], 2) . '</td>
            </tr>';

            $totalfabmaterial += $fabmaterialexpences['total_cost'];

          }

          $html6.= '
          </tbody>
            <tfoot>
              <tr>
                <th colspan="7" style="text-align:right;">Fabrication Material Total</th>
                <th style="text-align:center;">' . number_format($totalfabmaterial, 2) . '</th>
              </tr>
            </tfoot>
          </table>
          </div>
          </div>';

    echo $html6;

    } 

// 
// 
// 

    $grandTotal = 0;

    if ($jobmainrow['pre_varification'] === 'pre_varification') {
      $grandTotal += $totalprelabour + $totalpretransport + $totalpreother;
    } 

    if (in_array('creative', $involvements_array)) {
      $grandTotal += $totalcreative;
    }

    if (in_array('production', $involvements_array)) {
      $grandTotal += $totalproduction;
    } 

    if (in_array('fab_tvm', $involvements_array) || in_array('fab_ekm', $involvements_array)) {
      $grandTotal += $totalfablabour + $totalfabtransport + $totalfabother + $totalfabmaterial;
    }


// Now, build the HTML for the grand total
$html07 = '

<div class="row">
<div class="col-md-6">
<blockquote>
<small>INVOLVEMENTS GRAND TOTAL</small>
<h2>' . $grandTotal . '</h2>
</blockquote>
</div>
<div class="col-md-6 .align-middle">
<a class="btn btn-info btn-block" href="javascript:void(0);" onclick="printPageArea(\'print\')"><i class="fa fa-print"></i> Print</a><br><br></center>
</div>
</div>';



echo $html07;
}
?>
