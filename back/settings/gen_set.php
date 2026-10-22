<?php 
 include("../../inc/connection.php");
//  $get_company_details = mysqli_query($connection,"SELECT * FROM settings") or die("Could not get the product details");
// $row = mysqli_fetch_assoc($get_company_details);
$get_gen_details = mysqli_query($connection,"SELECT * FROM kp_settings_gen") or die("Could not get the gen details");
$row = mysqli_fetch_assoc($get_gen_details);
$print_settings_q = mysqli_query($connection, "SELECT * FROM kp_settings_print WHERE print_doc=1 LIMIT 1");
$print_row = $print_settings_q ? mysqli_fetch_assoc($print_settings_q) : array('mpesa' => 1);
 $mpesaSourceCheck = mysqli_query($connection, "SHOW COLUMNS FROM kp_settings_print LIKE 'mpesa_source'");
 if ($mpesaSourceCheck && mysqli_num_rows($mpesaSourceCheck) === 0) { mysqli_query($connection, "ALTER TABLE kp_settings_print ADD COLUMN mpesa_source VARCHAR(20) NOT NULL DEFAULT 'MAIN'"); }
 $mpesa_source = isset($print_row['mpesa_source']) ? $print_row['mpesa_source'] : 'MAIN';
$counterMpesaColumns = ['mpesa_type' => "VARCHAR(20) NOT NULL DEFAULT 'TILL'", 'mpesa_shortcode' => "VARCHAR(50) NOT NULL DEFAULT ''", 'mpesa_account' => "VARCHAR(100) NOT NULL DEFAULT ''"];
foreach ($counterMpesaColumns as $counterMpesaColumn => $counterMpesaDefinition) { $counterMpesaCheck = mysqli_query($connection, "SHOW COLUMNS FROM kp_counters LIKE '$counterMpesaColumn'"); if ($counterMpesaCheck && mysqli_num_rows($counterMpesaCheck) === 0) { mysqli_query($connection, "ALTER TABLE kp_counters ADD COLUMN $counterMpesaColumn $counterMpesaDefinition"); } }
$counterRows = [];
$counterQuery = mysqli_query($connection, "SELECT cntr_id, name, mpesa_type, mpesa_shortcode, mpesa_account FROM kp_counters WHERE del_status=2 ORDER BY name ASC");
if ($counterQuery) { while ($counterRow = mysqli_fetch_assoc($counterQuery)) { $counterRows[] = $counterRow; } }
if (!$print_row) {
    $print_row = array('mpesa' => 1, 'counter_close_rule' => 1);
}
if (!isset($print_row['counter_close_rule'])) {
    $print_row['counter_close_rule'] = 1;
}
?>
<style>
  #myform > .panel-body > .settings-left,
  #myform > .panel-body > .settings-right { padding-left:10px; padding-right:10px; }
  .settings-left fieldset { margin-bottom:10px; padding:10px 12px; border:1px solid #dce5e1; border-radius:10px; background:#fbfefd; }
  .settings-left legend { margin:0 0 8px; padding:0 4px; font-size:13px; font-weight:800; border:0; color:#237a56; }
  .settings-left .form-group { margin-bottom:8px; }
  .settings-left .form-control { height:32px; padding:5px 8px; font-size:12px; }
  .settings-left label { font-size:12px; margin-bottom:3px; }
  .settings-right fieldset { margin-bottom:14px; padding:14px; border:1px solid #dce5e1; border-radius:10px; background:#fff; }
  .settings-right legend { margin:0 0 12px; padding:0 5px; font-size:15px; font-weight:800; border:0; }
</style>
<!-- begin panel -->
    <div class="panel">
    	<!-- begin panel-body -->
    	<div id="results"></div>
         <form method="post" action=""  enctype="multipart/form-data" id="myform"> 
        <div class="panel-body row">
       	<div class="col-sm-4 settings-left">
                <div class="col-sm-12">
                    <fieldset>
                        <legend class="m-b-15">Company Details</legend>
                        <div class="form-group m-b-15" id="link-div">
                            <label class="col-form-label">Company Name* :</label> 
                            <input class="form-control" type="text" id="name" name="name" value="<?php echo $row['company_name']; ?>" placeholder="Enter Company name" /> 
                        </div> 
                        <div class="form-group m-b-15" id="link-div">
                            <label class="col-form-label">Company Tag* :</label> 
                            <input class="form-control" type="text" id="tag" name="tag" value="<?php echo $row['company_tag']; ?>"  placeholder="Enter Company tag" /> 
                        </div>
                    </fieldset>  
                </div>
                <div class="col-sm-12">
                    <fieldset>
                        <legend class="m-b-15">Contacts Details</legend>  
                        <div class="form-group m-b-15" id="link-div">
                            <label class="col-form-label">Phone 1* :</label> 
                            <input class="form-control" type="text" id="phone" name="phone" value="<?php echo $row['phone1']; ?>"  placeholder="Enter Company Phone 1..." /> 
                        </div> 
                        <div class="form-group m-b-15" id="link-div">
                            <label class="col-form-label">Phone 2* :</label> 
                            <input class="form-control" type="text" id="phone2" name="phone2" value="<?php echo $row['phone2']; ?>"  placeholder="Enter Company Phone 2... " /> 
                        </div> 
                        <div class="form-group m-b-1" id="link-div">
                            <label class="col-form-label">Email* :</label> 
                            <input class="form-control" type="email" id="email" name="email" value="<?php echo $row['email']; ?>"  placeholder="Enter Company Email..." /> 
                        </div>
                         <div class="form-group">
                            <label class="col-form-label">Address:</label> 
                            <textarea class="form-control pull-left" id="address" name="address"><?php echo $row['address']; ?> </textarea>  
                        </div>
                    </fieldset> 
                </div>             	 
		    </div>
            <div class="col-sm-8 settings-right">
                <div class="col-sm-12">
                    <fieldset>
                        <legend class="m-b-15">Tax Details</legend>
                        <div class="form-group m-b-1" id="pin-div">
                            <label class="col-form-label">TAX PIN * :</label> 
                            <input class="form-control" type="text" id="kra-pin" name="kra-pin" value="<?php echo $row['kra_pin']; ?>"  placeholder="Enter Company Email..." /> 
                        </div>
                        <div class="form-group m-b-1" id="pin-div">
                            <label class="col-form-label">VAT * :</label> 
                            <input class="form-control" type="text" id="vat" name="vat" value="<?php echo $row['vat']; ?>"  placeholder="Enter Company Email..." /> 
                        </div>
                        <br>
                        <div class="form-group row">
                            <div class="form-group row col-md-12" >
                                <label class="col-md-8"  style="color:blue;">Tax Calculations Mode</label>
                                <div class="col-md-6" >
                                    <div class="radio radio-css radio-inline">
                                        <input type="radio" name="tax-type" id="inc" value="1" <?php if ($row['tax_type']==1) { echo "checked"; } ?>/>
                                        <label for="inc">Inclusive</label>
                                    </div>
                                    <div class="radio radio-css radio-inline">
                                        <input type="radio" name="tax-type" id="excl" value="2" <?php if ($row['tax_type']==2) { echo "checked"; } ?>/>
                                        <label for="excl">Exclusive</label>
                                    </div>
                                </div>
                            </div>
                    </fieldset>
                </div>
                <br>
                <div class="col-sm-12">
                    <fieldset>
                        <legend class="m-b-15" style="color: green;">MPESA Details</legend> 
                        <div class="form-group" style="border:1px solid #d7e8df;border-radius:10px;padding:14px;background:#f7fffb;">
                            <label style="display:block;color:#137a4b;font-weight:800;font-size:15px;">M-Pesa payment source</label>
                            <p style="margin:4px 0 12px;color:#596b63;">Choose where the Till or PayBill details printed on bills should come from.</p>
                            <label class="radio-inline" style="margin-right:25px;font-weight:700;"><input type="radio" name="mpesa-source" value="MAIN"<?php echo $mpesa_source === 'MAIN' ? ' checked' : ''; ?>> Main settings <small style="display:block;margin-left:20px;color:#68756f;font-weight:400;">Use the global M-Pesa number and account.</small></label>
                            <label class="radio-inline" style="font-weight:700;"><input type="radio" name="mpesa-source" value="COUNTER"<?php echo $mpesa_source === 'COUNTER' ? ' checked' : ''; ?>> By counter <small style="display:block;margin-left:20px;color:#68756f;font-weight:400;">Use each counter's Till or PayBill details.</small></label>
                        </div>
                        <div id="counter-mpesa-settings" style="display:<?php echo $mpesa_source === 'COUNTER' ? 'block' : 'none'; ?>;border:1px solid #d9e1ed;border-radius:10px;padding:12px;margin-bottom:15px;">
                            <strong style="display:block;margin-bottom:8px;color:#24528a;">Counter M-Pesa details</strong>
                            <div class="table-responsive"><table class="table table-bordered table-condensed" style="margin:0;background:#fff;"><thead><tr><th>Counter</th><th>Type</th><th>Shortcode</th><th>Account</th></tr></thead><tbody>
                            <?php foreach ($counterRows as $counterRow) { $cid=(int)$counterRow['cntr_id']; ?>
                              <tr><td><?php echo htmlspecialchars($counterRow['name']); ?></td><td><select class="form-control input-sm" name="counter_mpesa_type[<?php echo $cid; ?>]"><option value="TILL"<?php echo (($counterRow['mpesa_type'] ?? 'TILL') === 'TILL') ? ' selected' : ''; ?>>TILL</option><option value="PAYBILL"<?php echo (($counterRow['mpesa_type'] ?? '') === 'PAYBILL') ? ' selected' : ''; ?>>PAYBILL</option></select></td><td><input class="form-control input-sm" name="counter_mpesa_shortcode[<?php echo $cid; ?>]" value="<?php echo htmlspecialchars($counterRow['mpesa_shortcode'] ?? ''); ?>"></td><td><input class="form-control input-sm" name="counter_mpesa_account[<?php echo $cid; ?>]" value="<?php echo htmlspecialchars($counterRow['mpesa_account'] ?? ''); ?>"></td></tr>
                            <?php } ?></tbody></table></div>
                        </div>
                        <script>$(function(){ $('input[name="mpesa-source"]').on('change',function(){ $('#counter-mpesa-settings').toggle(this.value==='COUNTER' && this.checked); }); });</script>
                        <div class="form-group row">
                            <div class="form-group row col-md-12" >
                                <div class="col-md-6" style="background-color: green;">
                                    <div class="radio radio-css radio-inline">
                                        <input type="radio" name="mpesa-select" id="till" value="1" <?php if ((int)$print_row['mpesa'] === 1) { echo "checked"; } ?>/>
                                        <label for="till" style="color:#fff;">TILL</label>
                                    </div>
                                    <div class="radio radio-css radio-inline">
                                        <input type="radio" name="mpesa-select" id="paybill" value="2" <?php if ((int)$print_row['mpesa'] === 2) { echo "checked"; } ?>/>
                                        <label for="paybill"  style="color:#fff;">PAYBILL</label>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group m-b-15 col-md-6" id="mpesa-div">
                                <label class="col-form-label">Mpesa No* :</label> 
                                <input class="form-control" type="text" id="mpesa-no" name="mpesa-no" value="<?php echo htmlspecialchars($row['mpesa_no']); ?>"  placeholder="Enter Till/Business Number..... " /> 
                            </div> 
                            <div class="form-group m-b-15 col-md-6" id="acc-div" style="<?php echo ((int)$print_row['mpesa'] === 2) ? '' : 'display: none;'; ?>">
                                <label class="col-form-label">Account* :</label> 
                                <input class="form-control" type="text" id="mpesa-account" name="mpesa-account" value="<?php echo htmlspecialchars($row['mpesa_account']); ?>"  placeholder="Enter Paybill Account Number... " /> 
                            </div> 
                        </div>
                    </fieldset>
                </div>
                <div class="col-sm-12">
                    <fieldset>
                        <legend class="m-b-15" style="color: #c05621;">Counter Closing Rule</legend>
                        <div class="form-group row">
                            <div class="col-md-12">
                                <div class="radio radio-css">
                                    <input type="radio" name="counter-close-rule" id="close-rule-strict" value="1" <?php if ((int)$print_row['counter_close_rule'] === 1) { echo "checked"; } ?> />
                                    <label for="close-rule-strict">
                                        Require all unpaid bills to be cleared before closing a counter
                                    </label>
                                </div>
                                <div class="radio radio-css">
                                    <input type="radio" name="counter-close-rule" id="close-rule-relaxed" value="2" <?php if ((int)$print_row['counter_close_rule'] === 2) { echo "checked"; } ?> />
                                    <label for="close-rule-relaxed">
                                        Allow counter close even when some bills are still unpaid
                                    </label>
                                </div>
                                <small class="text-muted">
                                    Use the first option for strict cashier control. Use the second option if your team closes counters before every bill is fully cleared.
                                </small>
                            </div>
                        </div>
                    </fieldset>
                </div>
            </div> 
            
		</div></form> 
	<!-- end panel-body -->
	<!-- START PANEL FOOTER -->
    <div class="panel-footer text-right">
        <!-- <a href="javascript:;" class="btn btn-danger" data-dismiss="modal">Close</a> -->
        <button type="submit" id="submit-gen" class="btn btn-lime">Submit</button>
    </div>
	<!-- END PANEL FOOTER -->
</div>
<!-- end panel --> 
<script type="text/javascript">
    document.getElementById('till').addEventListener('click', till);
    document.getElementById('paybill').addEventListener('click', paybill);
    function till(){
        document.getElementById('acc-div').style.display = 'none'; 
        $("#mpesa-account").val('');
    } 
    function paybill(){
        document.getElementById('acc-div').style.display = 'block'; 
    } 

    document.getElementById('submit-gen').addEventListener('click', thermal);
    function thermal(){
        var form = $("#myform");
        var formData = new FormData(form[0]);
        $.ajax({
                type: "POST",
                url: '../settings/gen_set_act.php',
                data: formData,
                contentType: false, //this is requireded please see answers above
                processData: false, //this is requireded please see answers above
            })
        .done(function(data)
        {
            $('#results').html(''); // blank before load.
            $('#results').html(data);
        })
        .fail(function()
        {
            $('#results').html('<div class="alert alert-danger"><a href="#" class="close" data-dismiss="alert">&times;</a><strong>Warning!</strong> Error occured.</div>');
        });
    }
 
</script>
