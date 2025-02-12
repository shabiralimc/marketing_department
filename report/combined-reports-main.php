<?php
session_start(); 
include_once('../../../include/php/connect.php');


// Calculate the remaining time
$sessionStart = $_SESSION['session_start'];
$sessionLifetime = $_SESSION['session_lifetime'];
$currentTime = time();
$remainingTime = ($sessionStart + $sessionLifetime) - $currentTime;


if (isset($_GET['jc_numbers'])) {
    $selectedJCNumbers = $_GET['jc_numbers'];

    // Convert selected JC numbers to an array
    $selectedJCNumbersArray = explode(',', $selectedJCNumbers);
    // Escape each JC number for use in SQL query
    $escapedJCNumbers = array_map(function($jcNumber) use ($conn) {
        return "'" . mysqli_real_escape_string($conn, $jcNumber) . "'";
    }, $selectedJCNumbersArray);
    // Create a comma-separated list of escaped JC numbers
    $escapedJCNumbersString = implode(',', $escapedJCNumbers);

    $jobcard_mains = mysqli_query($conn, "SELECT * FROM jobcard_main WHERE jc_number IN ($escapedJCNumbersString)");
    if (!$jobcard_mains) {
        // Query failed, output error message
        echo "Error: " . mysqli_error($conn);
    } else {
        // Initialize HTML table for jobcard main
        $html = '
        <div class="row">
          <div class="col-md-4">
            <div class="callout callout-info">
              <h5><i class="fas fa-info"></i> '.$selectedJCNumbers.'</h5>
              Searched JC Numbers:
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-md-12">
            <div class="card card-info card-outline">
              <div class="card-header">
                <h5>Job-Card Details</h5>
              </div>
              <div class="card-body">
                <table id="JobcardmainTable" class="table table-bordered table-striped">
                  <thead>
                    <tr>
                      <th style="text-align:center; vertical-align:middle;">JC Number</th>
                      <th style="text-align:center; vertical-align:middle;">JC Date</th>
                      <th style="text-align:center; vertical-align:middle;">Client</th>
                      <th style="text-align:center; vertical-align:middle;">Instructed By</th>
                      <th style="text-align:center; vertical-align:middle;">Proposed Rate</th>
                      <th style="text-align:center; vertical-align:middle;">Location</th>
                      <th style="text-align:center; vertical-align:middle;">Completion Before</th>
                      <th style="text-align:center; vertical-align:middle;">Remark</th>
                      <th style="text-align:center; vertical-align:middle;">Involvements</th>
                      <th style="text-align:center; vertical-align:middle;">Pre Varification</th>
                      <th style="text-align:center; vertical-align:middle;">Work JC</th>
                      <th style="text-align:center; vertical-align:middle;">CSR</th>
                    </tr>
                  </thead>
                  <tbody>';
                  // Loop through each selected JC number and append its data to the HTML table
                  while ($jobcardrow = mysqli_fetch_assoc($jobcard_mains)) {
                    $html .= '
                    <tr>
                      <td style="text-align:center; vertical-align:middle;">' . $jobcardrow['jc_number'] . '</td>
                      <td style="text-align:center; vertical-align:middle;">' . date("d-m-y", strtotime($jobcardrow['jc_date'])) . '</td>
                      <td style="text-align:center; vertical-align:middle;">' . $jobcardrow['client'] . '</td>
                      <td style="text-align:center; vertical-align:middle;">' . $jobcardrow['instructed_by'] . '</td>
                      <td style="text-align:center; vertical-align:middle;">' . $jobcardrow['proposed_rate'] . '</td>
                      <td style="text-align:center; vertical-align:middle;">' . $jobcardrow['s_location'] . '</td>
                      <td style="text-align:center; vertical-align:middle;">' . date("d-m-y", strtotime($jobcardrow['completion_before'])) . '</td>
                      <td style="text-align:center; vertical-align:middle;">' . $jobcardrow['now_remark'] . '</td>
                      <td style="text-align:center; vertical-align:middle;">' . $jobcardrow['involvements'] . '</td>
                      <td style="text-align:center; vertical-align:middle;">' . $jobcardrow['pre_varification'] . '</td>
                      <td style="text-align:center; vertical-align:middle;">' . $jobcardrow['work_jc'] . '</td>
                      <td style="text-align:center; vertical-align:middle;">' . $jobcardrow['csr'] . '</td>
                    </tr>';
                  }
                  // Close the HTML table for jobcard main
                  $html .= '
                  </tbody>
                </table>
              </div>
            </div>';

            // Output the HTML for jobcard main
            echo $html;

            // Initialize HTML for Creative Items
            $htmlCreative = '';

            // Fetch and display Creative Items
            $creative_mains = mysqli_query($conn, "SELECT * FROM creative_main WHERE jc_number IN ($escapedJCNumbersString)");

            if ($creative_mains && mysqli_num_rows($creative_mains) > 0) {
              $htmlCreative .= '
              <div class="card card-info card-outline">
                <div class="card-header">
                  <h5>Creative Details</h5>
                </div>
                <div class="card-body">
                  <table id="CreativeMainTable" class="table table-bordered table-striped">
                    <thead>
                      <tr>
                        <th style="text-align:center; vertical-align:middle;">JC Number</th>
                        <th style="text-align:center; vertical-align:middle;">Start Date</th>
                        <th style="text-align:center; vertical-align:middle;">Ref No</th>
                        <th style="text-align:center; vertical-align:middle;">Billable</th>
                        <th style="text-align:center; vertical-align:middle;">End Date</th>
                        <th style="text-align:center; vertical-align:middle;">Ref Date</th>
                        <th style="text-align:center; vertical-align:middle;">Status</th>
                        <th style="text-align:center; vertical-align:middle;">Corrections</th>
                        <th style="text-align:center; vertical-align:middle;">Amount</th>
                      </tr>
                    </thead>
                    <tbody>';

                    while ($creativeRow = mysqli_fetch_assoc($creative_mains)) {
                      $htmlCreative .= '
                      <tr>
                        <td style="text-align:center; vertical-align:middle;">' . $creativeRow['jc_number'] . '</td>
                        <td style="text-align:center; vertical-align:middle;">' . date("d-m-y", strtotime($creativeRow['s_start_date'])) . '</td>
                        <td style="text-align:center; vertical-align:middle;">' . $creativeRow['ref_no'] . '</td>
                        <td style="text-align:center; vertical-align:middle;">' . $creativeRow['billable'] . '</td>
                        <td style="text-align:center; vertical-align:middle;">' . date("d-m-y", strtotime($creativeRow['end_date'])) . '</td>
                        <td style="text-align:center; vertical-align:middle;">' . date("d-m-y", strtotime($creativeRow['ref_date'])) . '</td>
                        <td style="text-align:center; vertical-align:middle;">' . $creativeRow['s_status'] . '</td>
                        <td style="text-align:center; vertical-align:middle;">' . $creativeRow['corrections'] . '</td>
                        <td style="text-align:center; vertical-align:middle;">' . $creativeRow['creative_total_amt'] . '</td>
                      </tr>';
                    }

                    // Close the HTML table for Creative Items
                    $htmlCreative .= '
                    </tbody>
                  </table>
                </div>
              </div>';

              // Output the HTML for Creative Items
              echo $htmlCreative;
            }

            // Initialize HTML for Production Items
            $htmlProduction = '';

            // Fetch and display Production Items
            $production_mains = mysqli_query($conn, "SELECT * FROM production_invoice_main WHERE jc_number IN ($escapedJCNumbersString)");

            if ($production_mains && mysqli_num_rows($production_mains) > 0) {
              $htmlProduction .= '
                <div class="card card-info card-outline">
                  <div class="card-header">
                    <h5>Production Details</h5>
                  </div>
                  <div class="card-body">
                    <table id="ProductionMainTable" class="table table-bordered table-striped">
                      <thead>
                        <tr>
                          <th style="text-align:center; vertical-align:middle;">JC Number</th>
                          <th style="text-align:center; vertical-align:middle;">PO Number</th>
                          <th style="text-align:center; vertical-align:middle;">Invoice Number</th>
                          <th style="text-align:center; vertical-align:middle;">Invoice Date</th>
                          <th style="text-align:center; vertical-align:middle;">Amount</th>
                          <th style="text-align:center; vertical-align:middle;">Frieght Charges</th>
                          <th style="text-align:center; vertical-align:middle;">Addl Expences</th>
                          <th style="text-align:center; vertical-align:middle;">Total Expences</th>
                          <th style="text-align:center; vertical-align:middle;">Notes</th>
                        </tr>
                      </thead>
                      <tbody>';

                      while ($productionRow = mysqli_fetch_assoc($production_mains)) {
                        $htmlProduction .= '
                        <tr>
                          <td style="text-align:center; vertical-align:middle;">' . $productionRow['jc_number'] . '</td>
                          <td style="text-align:center; vertical-align:middle;">' . $productionRow['po_number'] . '</td>
                          <td style="text-align:center; vertical-align:middle;">' . $productionRow['invoice_number'] . '</td>
                          <td style="text-align:center; vertical-align:middle;">' . date("d-m-y", strtotime($productionRow['invoice_date'])) . '</td>
                          <td style="text-align:center; vertical-align:middle;">' . $productionRow['amount'] . '</td>
                          <td style="text-align:center; vertical-align:middle;">' . $productionRow['freight'] . '</td>
                          <td style="text-align:center; vertical-align:middle;">' . $productionRow['addl_expences'] . '</td>
                          <td style="text-align:center; vertical-align:middle;">' . $productionRow['total_expences'] . '</td>
                          <td style="text-align:center; vertical-align:middle;">' . $productionRow['s_descriptions'] . '</td>
                        </tr>';
                      }

                      // Close the HTML table for Production Items
                      $htmlProduction .= '
                      </tbody>
                    </table>
                  </div>
                </div>';

                // Output the HTML for Production Items
                echo $htmlProduction;
              }

            // Initialize HTML for Production Items
            $htmlPreverification = '';

            // Fetch and display Production Items
            $pre_verications = mysqli_query($conn, "SELECT * FROM pre_varification_total WHERE jc_number IN ($escapedJCNumbersString)");

            if ($pre_verications && mysqli_num_rows($pre_verications) > 0) {
              $htmlPreverification .= '
              <div class="card card-info card-outline">
                <div class="card-header">
                  <h5>FAB Pre-Verification Details</h5>
                </div>
                <div class="card-body">
                  <table id="Pre VerificationTable" class="table table-bordered table-striped">
                    <thead>
                      <tr>
                        <th style="text-align:center; vertical-align:middle;">JC Number</th>
                        <th style="text-align:center; vertical-align:middle;">Pre Labour Total</th>
                        <th style="text-align:center; vertical-align:middle;">Pre Transport Total</th>
                        <th style="text-align:center; vertical-align:middle;">Pre Other Total</th>
                        <th style="text-align:center; vertical-align:middle;">Status</th>
                        <th style="text-align:center; vertical-align:middle;">Supervisor</th>
                        <th style="text-align:center; vertical-align:middle;">Date</th>
                        <th style="text-align:center; vertical-align:middle;">Total Amount</th>
                      </tr>
                    </thead>
                    <tbody>';

                    while ($preverificationRow = mysqli_fetch_assoc($pre_verications)) {
                      $htmlPreverification .= '
                        <tr>
                          <td style="text-align:center; vertical-align:middle;">' . $preverificationRow['jc_number'] . '</td>
                          <td style="text-align:center; vertical-align:middle;">' . $preverificationRow['total_labour_cost'] . '</td>
                          <td style="text-align:center; vertical-align:middle;">' . $preverificationRow['total_transport_cost'] . '</td>
                          <td style="text-align:center; vertical-align:middle;">' . $preverificationRow['total_other_cost'] . '</td>
                          <td style="text-align:center; vertical-align:middle;">' . $preverificationRow['current_status'] . '</td>
                          <td style="text-align:center; vertical-align:middle;">' . $preverificationRow['pre_supervisor'] . '</td>
                          <td style="text-align:center; vertical-align:middle;">' . date("d-m-y", strtotime($preverificationRow['pv_com_date'])) . '</td>
                          <td style="text-align:center; vertical-align:middle;">' . $preverificationRow['total_amount'] . '</td>
                        </tr>';
                    }

                    // Close the HTML table for Production Items
                    $htmlPreverification .= '
                    </tbody>
                  </table>
                </div>
              </div>';

              // Output the HTML for Production Items
              echo $htmlPreverification;
            }

            // Initialize HTML for Production Items
            $htmlFabrication = '';

            // Fetch and display Production Items
            $fabrication_mains = mysqli_query($conn, "SELECT * FROM fabrication_main WHERE jc_number IN ($escapedJCNumbersString)");

            if ($fabrication_mains && mysqli_num_rows($fabrication_mains) > 0) {
              $htmlFabrication .= '
              <div class="card card-info card-outline">
                <div class="card-header">
                  <h5>FAB Work Details</h5>
                </div>
                <div class="card-body">
                  <table id="FabricationTable" class="table table-bordered table-striped">
                    <thead>
                      <tr>
                        <th style="text-align:center; vertical-align:middle;">JC Number</th>
                        <th style="text-align:center; vertical-align:middle;">Fab Labour Total</th>
                        <th style="text-align:center; vertical-align:middle;">Fab Transport Total</th>
                        <th style="text-align:center; vertical-align:middle;">Fab Other Total</th>
                        <th style="text-align:center; vertical-align:middle;">Material Total</th>
                        <th style="text-align:center; vertical-align:middle;">Status</th>
                        <th style="text-align:center; vertical-align:middle;">Supervisor</th>
                        <th style="text-align:center; vertical-align:middle;">Branch</th>
                        <th style="text-align:center; vertical-align:middle;">Total Amount</th>
                      </tr>
                    </thead>
                    <tbody>';

                    while ($fabricationRow = mysqli_fetch_assoc($fabrication_mains)) {
                      $htmlFabrication .= '
                        <tr>
                          <td style="text-align:center; vertical-align:middle;">' . $fabricationRow['jc_number'] . '</td>
                          <td style="text-align:center; vertical-align:middle;">' . $fabricationRow['labour_total'] . '</td>
                          <td style="text-align:center; vertical-align:middle;">' . $fabricationRow['transport_total'] . '</td>
                          <td style="text-align:center; vertical-align:middle;">' . $fabricationRow['other_total'] . '</td>
                          <td style="text-align:center; vertical-align:middle;">' . $fabricationRow['material_total'] . '</td>
                          <td style="text-align:center; vertical-align:middle;">' . $fabricationRow['fab_status'] . '</td>
                          <td style="text-align:center; vertical-align:middle;">' . $fabricationRow['fab_supervisor'] . '</td>
                          <td style="text-align:center; vertical-align:middle;">' . $fabricationRow['fam_branch'] . '</td>
                          <td style="text-align:center; vertical-align:middle;">' . $fabricationRow['grand_total'] . '</td>
                        </tr>';
                      }

                      // Close the HTML table for Production Items
                      $htmlFabrication .= '
                    </tbody>
                  </table>
                </div>
              </div>

                ';

              // Output the HTML for Production Items
              echo $htmlFabrication;
            }
          }
        }

        $htmlprint = '';

        $printing = "'printableArea'";

        $htmlprint .= '
        
              </div>
              </div>
              <div class="card-footer">
          <div class="row">
            <div class="col-md-6 offset-md-3">
              <button type="button" class="btn btn-primary btn-block" onclick="printDiv('.$printing.')"><i class="fa fa-print"></i> PRINT REPORT</button>
            </div>
          </div>
        </div>';

        echo $htmlprint;       


      ?>