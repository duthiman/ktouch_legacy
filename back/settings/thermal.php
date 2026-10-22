<?php
include("../../inc/connection.php");
$template_col = mysqli_query($connection, "SHOW COLUMNS FROM kp_settings_print LIKE 'print_template'");
if ($template_col && mysqli_num_rows($template_col) === 0) {
    mysqli_query($connection, "ALTER TABLE kp_settings_print ADD COLUMN print_template TINYINT(1) NOT NULL DEFAULT 1");
}
$qr_col = mysqli_query($connection, "SHOW COLUMNS FROM kp_settings_print LIKE 'show_qr'");
if ($qr_col && mysqli_num_rows($qr_col) === 0) {
    mysqli_query($connection, "ALTER TABLE kp_settings_print ADD COLUMN show_qr TINYINT(1) NOT NULL DEFAULT 2");
}
$logo_height_col = mysqli_query($connection, "SHOW COLUMNS FROM kp_settings_print LIKE 'thermal_logo_height'");
if ($logo_height_col && mysqli_num_rows($logo_height_col) === 0) {
    mysqli_query($connection, "ALTER TABLE kp_settings_print ADD COLUMN thermal_logo_height INT NOT NULL DEFAULT 60");
}

$get_thermal_details = mysqli_query($connection,"SELECT * FROM kp_settings_print WHERE print_doc=1 LIMIT 1") or die("Could not get the print details");
$row = mysqli_fetch_assoc($get_thermal_details);
if (!$row) {
    $row = array(
        'show_logo' => 1,
        'pin' => 1,
        'tax' => 2,
        'mpesa' => 1,
        'thankyou_note' => 'Thank you for your business.',
        'print_template' => 1,
        'show_qr' => 2,
        'thermal_logo_height' => 60
    );
}
if (!isset($row['print_template'])) { $row['print_template'] = 1; }
if (!isset($row['show_qr']))        { $row['show_qr'] = 2; }
if (!isset($row['thermal_logo_height'])) { $row['thermal_logo_height'] = 60; }
$thermal_logo_height = (int)$row['thermal_logo_height'];
if ($thermal_logo_height < 30) { $thermal_logo_height = 30; }
if ($thermal_logo_height > 180) { $thermal_logo_height = 180; }

$get_gen_details = mysqli_query($connection,"SELECT * FROM kp_settings_gen") or die("Could not get the gen details");
$gen = mysqli_fetch_assoc($get_gen_details);
$thermal_logo = !empty($gen['thermal_logo_path']) ? $gen['thermal_logo_path'] : ($gen['image_path'] ?? '');

date_default_timezone_set("Africa/Nairobi");
$today=date("Y-m-d");
?>
<style type="text/css">
    .preview-shell {
        border: 1px solid #d9dee3;
        border-radius: 10px;
        padding: 12px;
        background: #f7f9fb;
    }
    .preview-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
    }
    .preview-badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: bold;
        background: #e8eef9;
        color: #2f4f7f;
        text-transform: uppercase;
        letter-spacing: .5px;
    }
    #receipt{
        padding:1mm;
        margin: 0 auto;
        width: 79mm;
        background: #25e25e;
        color: #000;
        font-family: Arial, Helvetica Neue, Helvetica, sans-serif;
    }
    #bord{
        border: 2px solid #000 !important
    }
    table{ 
        padding:1mm;
        margin: 0 auto;
        width: 75mm;
        background: #25e25e;
        border-collapse: collapse;
    }
    td,th{
        font-size: 13px;
        border: 1px solid #000 !important
    }
    th{
        font-size: 13px;
        padding: 2px;
        margin: 2px;
    }
    .text-center {
        text-align:center!important
    }
    .text-left {
        text-align:left!important
    }
    .text-right {
        text-align:right!important
    }
    .pull-left {
        float:left!important
    }
    .pull-right {
        float:right!important
    }
    h1.title{
        font-size: 26px;
        margin-bottom: 0px; 
    }
    p{
  font-size: 13px;
}
span{
  font-size: 13px; 
}
.template-card {
    border: 2px solid #d9dee3;
    border-radius: 8px;
    padding: 12px;
    margin-bottom: 12px;
    background: #fff;
    min-height: 145px;
}
.template-card.active {
    border-color: #2d8cf0;
    box-shadow: 0 0 0 2px rgba(45, 140, 240, 0.12);
}
.template-card h5 {
    margin-top: 0;
    margin-bottom: 6px;
}
.template-card p {
    margin-bottom: 0;
    color: #555;
}
.preview-template {
    display: none;
}
.preview-template.active {
    display: block;
}
.receipt-2 {
    margin: 0 auto;
    width: 79mm;
    background: #fff;
    color: #000;
    padding: 3mm 2mm;
    font-family: 'Courier New', Courier, monospace;
}
.receipt-2 .text-center {
    text-align:center!important;
}
.receipt-2 .meta {
    display: flex;
    justify-content: space-between;
    font-size: 11px;
    margin: 2px 0;
}
.receipt-2 .meta span {
    font-size: 11px;
}
.receipt-2 .sep-eq {
    border: none;
    border-top: 2px solid #000;
    margin: 4px 0;
}
.receipt-2 .sep-dot {
    border: none;
    border-top: 1px dashed #555;
    margin: 4px 0;
}
.receipt-2 h1.title {
    font-size: 22px;
    margin: 4px 0 2px;
    letter-spacing: 2px;
}
.receipt-2 .items-tbl,
.receipt-2 .totals-tbl {
    width: 100%;
    border-collapse: collapse;
    margin: 2px 0;
}
.receipt-2 .items-tbl th {
    font-size: 11px;
    border-top: 1px solid #000;
    border-bottom: 1px solid #000;
    padding: 2px 1px;
    text-transform: uppercase;
}
.receipt-2 .items-tbl td,
.receipt-2 .totals-tbl td {
    font-size: 12px;
    border: none;
    padding: 2px 1px;
}
.receipt-2 .items-tbl th:first-child,
.receipt-2 .items-tbl td:first-child,
.receipt-2 .totals-tbl td:first-child {
    text-align: left;
}
.receipt-2 .items-tbl th:not(:first-child),
.receipt-2 .items-tbl td:not(:first-child),
.receipt-2 .totals-tbl td:last-child {
    text-align: right;
}
.receipt-2 .sec-label td {
    font-size: 11px;
    font-weight: bold;
    text-decoration: underline;
    padding-top: 4px;
}
.receipt-2 .grand td {
    font-size: 16px;
    font-weight: bold;
    padding-top: 3px;
}
.receipt-2 .mpesa-line {
    text-align: center;
    font-size: 13px;
    font-weight: bold;
    letter-spacing: 1px;
    margin: 3px 0;
}
@media print {
    p.break {
      page-break-after: always;
    }
    body{
      margin: 2mm;
    }
#receipt{ 
  padding:1mm;
  margin: 0 auto;
  width: 79mm;
  background: #25e25e;
  #border{
  border: 2px solid #000 !important
 }
}
#bord{
    border: 2px solid #000 !important
 }
 table{ 
  padding:1mm;
  margin: 0 auto;
  width: 75mm;
  background: #25e25e;
   border-collapse: collapse;
}
td,th{
  font-size: 13px;
  border: 1.5px solid #000 !important
}
th{
  font-size: 13px;
  padding: 2px;
  margin: 2px;
}
 
span{
  font-size: 13px;
  padding: 5px;
}

}
</style> 
<!-- begin panel -->
<div class="panel panel-inverse" data-sortable-id="form-validation-2"> 
  <!-- begin panel-body -->
  <div class="panel-body ">
    <div class="row">
      <div class="col-md-7"> 
        <form method="post" action="" enctype="multipart/form-data" id="myform">
          <div class="form-group row">
            <div class="form-group col-md-6">
              <label class="col-md-6"  style="color:blue;">Show Logo</label>
              <div class="col-md-6">
                <div class="radio radio-css radio-inline">
                  <input type="radio" name="logo" id="logoyes" value="1"  <?php if ($row['show_logo']==1) { echo "checked"; } ?>/>
                  <label for="logoyes" style="color:blue;">YES</label>
                </div>
                <div class="radio radio-css radio-inline">
                  <input type="radio" name="logo" id="logono" value="2"   <?php if ($row['show_logo']==2) { echo "checked"; } ?>/>
                  <label for="logono"  style="color:blue;">NO</label>
                </div>
              </div>
            </div>
          </div> 
          <div class="form-group row">
            <div class="form-group col-md-12">
              <label class="col-md-4" style="color:blue;">Mpesa Label</label>
              <div class="col-md-8">
                <div class="radio radio-css radio-inline">
                  <input type="radio" name="mpesa-select" id="till" value="1" <?php if ((int)$row['mpesa'] === 1) { echo "checked"; } ?> />
                  <label for="till" style="color:blue;">TILL</label>
                </div>
                <div class="radio radio-css radio-inline">
                  <input type="radio" name="mpesa-select" id="paybill" value="2" <?php if ((int)$row['mpesa'] === 2) { echo "checked"; } ?> />
                  <label for="paybill" style="color:blue;">PAYBILL</label>
                </div>
              </div>
            </div>
          </div>
          <br>
          <div class="form-group row">
            <div class="form-group col-md-12">
              <label class="col-md-4"  style="color:blue;">Show PIN</label>
              <div class="col-md-6">
                <div class="radio radio-css radio-inline">
                  <input type="radio" name="pin" id="pinyes" value="1"  <?php if ($row['pin']==1) { echo "checked"; } ?> />
                  <label for="pinyes" style="color:blue;">YES</label>
                </div>
                <div class="radio radio-css radio-inline">
                  <input type="radio" name="pin" id="pinno" value="2" <?php if ($row['pin']==2) { echo "checked"; } ?>/>
                  <label for="pinno"  style="color:blue;">NO</label>
                </div>
              </div>
            </div>
            <br>
            <div class="form-group col-md-12">
              <label class="col-md-6"  style="color:blue;">Show Tax Calculations</label>
              <div class="col-md-6">
                <div class="radio radio-css radio-inline">
                  <input type="radio" name="tax" id="taxyes" value="1" <?php if ($row['tax']==1) { echo "checked"; } ?>/>
                  <label for="taxyes" style="color:blue;">YES</label>
                </div>
                <div class="radio radio-css radio-inline">
                  <input type="radio" name="tax" id="taxno" value="2" <?php if ($row['tax']==2) { echo "checked"; } ?>/>
                  <label for="taxno"  style="color:blue;">NO</label>
                </div>
              </div>
            </div>
          </div>
          <br>
          <div class="form-group row">
            <div class="form-group col-md-12">
              <label class="col-md-6" style="color:blue;">Show QR Code on Receipt</label>
              <div class="col-md-6">
                <div class="radio radio-css radio-inline">
                  <input type="radio" name="show_qr" id="qryes" value="1" <?php if ($row['show_qr']==1) { echo "checked"; } ?>/>
                  <label for="qryes" style="color:blue;">YES</label>
                </div>
                <div class="radio radio-css radio-inline">
                  <input type="radio" name="show_qr" id="qrno" value="2"  <?php if ($row['show_qr']!=1) { echo "checked"; } ?>/>
                  <label for="qrno" style="color:blue;">NO</label>
                </div>
              </div>
            </div>
          </div>
          <br>
          <div class="form-group row">
            <div class="form-group col-md-12">
              <label class="col-md-4" style="color:blue;">Logo Size</label>
              <div class="col-md-8">
                <input
                  type="number"
                  class="form-control"
                  id="thermal_logo_height"
                  name="thermal_logo_height"
                  min="30"
                  max="180"
                  step="1"
                  value="<?php echo $thermal_logo_height; ?>"
                />
                <small class="text-muted">Set the thermal receipt logo height in pixels. Recommended: 50 to 80.</small>
              </div>
            </div>
          </div>
          <br>
          <div class="form-group col-md-12">
            <label class="col-form-label">Thankyou Section:</label> 
            <textarea class="form-control pull-left" id="thank" name="thank"><?php echo htmlspecialchars(trim($row['thankyou_note'])); ?></textarea>  
          </div> 
          <div class="form-group col-md-12">
            <label class="col-form-label" style="color:blue;">Receipt Template</label>
            <div class="row">
              <div class="col-md-6">
                <label class="template-card <?php echo (int)$row['print_template'] === 1 ? 'active' : ''; ?>" for="template-1">
                  <div class="radio radio-css">
                    <input type="radio" name="print_template" id="template-1" value="1" <?php echo (int)$row['print_template'] === 1 ? 'checked' : ''; ?> />
                    <label for="template-1" style="color:blue;">Template 1</label>
                  </div>
                  <h5>Classic Green</h5>
                  <p>Bordered receipt style with bold totals and the original K-Touch layout.</p>
                </label>
              </div>
              <div class="col-md-6">
                <label class="template-card <?php echo (int)$row['print_template'] === 2 ? 'active' : ''; ?>" for="template-2">
                  <div class="radio radio-css">
                    <input type="radio" name="print_template" id="template-2" value="2" <?php echo (int)$row['print_template'] === 2 ? 'checked' : ''; ?> />
                    <label for="template-2" style="color:blue;">Template 2</label>
                  </div>
                  <h5>Clean Monospace</h5>
                  <p>Compact white receipt style with a cleaner cashier-style table and totals block.</p>
                </label>
              </div>
            </div>
            <small class="text-muted">The selected template will be used for customer bill printing in both direct print and print-server queue mode.</small>
          </div>
          <div  class="form-group col-md-12">
            <a href="javascript:;" class="btn btn-sm btn-danger" data-dismiss="modal">Close</a>
            <button type="button" name="btn-thermal" id="btn-thermal" class="btn btn-sm btn-success">Save</button>
          </div> 
        </form>
      </div>    
     
           
    <div class="col-md-5">
      <div id="results"></div>
      <div class="preview-shell">
        <div class="preview-head">
          <strong>Live Preview</strong>
          <span class="preview-badge" id="preview-template-name">Template 1</span>
        </div>

        <div id="preview-template-1" class="preview-template <?php echo (int)$row['print_template'] === 1 ? 'active' : ''; ?>">
          <div id="receipt">
            <div id="bord">
              <div id="receipt-header" class="text-center" style="margin-right:5px;">
                <p style="margin:0;"><?php echo str_repeat('*', 50); ?></p>
                <img id="preview-logo-1" src="<?php echo htmlspecialchars($thermal_logo); ?>" style="height: <?php echo $thermal_logo_height; ?>px; width:auto; margin-left: 30px; margin-top: 20px; <?php echo $row['show_logo']==1 ? '' : 'display:none;'; ?>" /><br>
                <h1 class="title"><?php echo strtoupper($gen['company_name']); ?></h1>
                <?php if (!empty($gen['company_tag'])): ?>
                <p style="font-size: 18px;margin: 0;">(<?php echo $gen['company_tag']; ?>)</p>
                <?php endif; ?>
                <p style="margin: 0;"><?php echo str_repeat('*', 50); ?></p>
                <p style="margin: 0;"><?php echo $gen['address']; ?></p>
                <p style="margin: 0;">Cell: <?php echo $gen['phone1']; ?> | <?php echo $gen['phone2']; ?></p>
                <p style="margin: 0;"><?php echo str_repeat('*', 50); ?></p>
                <span class="pull-left" id="preview-pin-1" style="<?php echo $row['pin']==1 ? '' : 'display:none;'; ?>">PIN: <?php echo $gen['kra_pin']; ?></span>
                <span class="pull-left"><b>SERVED BY: STAFF NAME</b></span>
                <span class="pull-right"><b>BILL No: 1000</b></span><br>
                <span class="pull-left"><b>SHIFT: DAY</b></span>
                <span class="pull-right"><b>TIME: 12:30 PM</b></span>
                <p style="margin: 0;"><?php echo str_repeat('*', 50); ?></p>
              </div>
              <div id="receipt-body">
                <table class="table table-bordered" id="deSo">
                  <tfoot>
                    <tr>
                      <th colspan="5" class="text-center"><b style="font-size: 22px;">Total: <?php echo number_format(2000,2); ?></b></th>
                    </tr>
                  </tfoot>
                  <tbody>
                    <tr>
                      <td>10</td>
                      <td>ITEM 1</td>
                      <td colspan="2">100.00</td>
                      <td>1000.00</td>
                    </tr>
                    <tr>
                      <td>5</td>
                      <td>ITEM 2</td>
                      <td colspan="2">200.00</td>
                      <td>1000.00</td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <div id="receipt-footer" style="margin-right:5px;">
                <div id="preview-tax-1" style="<?php echo $row['tax']==1 ? '' : 'display:none;'; ?>">
                  <p style="margin: 0;"><?php echo str_repeat('*', 50); ?></p>
                  <span class="pull-left">Gross: 2000.00</span>
                  <span class="pull-right">V.A.T(16%): 275.86</span><br>
                  <span class="pull-right">Cat Levy(2%): 39.22</span>
                </div>
                <p style="margin: 0;"><?php echo str_repeat('*', 50); ?></p>
                <p class="text-center" style="margin: 0;"><b>THANK YOU</b></p>
                <p class="text-center" style="margin: 0;" id="preview-thank-1"><?php echo htmlspecialchars($row['thankyou_note']); ?></p>
                <p class="text-center" style="margin: 0;">This is not an ETR. Request one.</p>
                <p style="margin: 0;"><?php echo str_repeat('*', 50); ?></p>
                <h2 class="text-center" id="preview-mpesa-1" style="margin: 0;font-size: 20px;font-weight: bold; font-family: 'Helvetica LT W01 Black Cond';letter-spacing: 3px;"><?php if ($row['mpesa']==2 ){ echo "PAYBILL ".$gen['mpesa_no']." ACCOUNT:".$gen['mpesa_account']; } else { echo "TILL ".$gen['mpesa_no']; } ?></h2>
                <p style="margin: 0;"><?php echo str_repeat('*', 50); ?></p>
                <p class="text-center" style="margin: 0;">K-Touch Point of Sale Designed & Sold by K-SYSTEMS <br> +254716100335 | +254735100335.</p>
                <p style="margin: 0;"><?php echo str_repeat('*', 50); ?></p>
              </div>
            </div>
          </div>
        </div>

        <div id="preview-template-2" class="preview-template <?php echo (int)$row['print_template'] === 2 ? 'active' : ''; ?>">
          <div class="receipt-2">
            <div class="text-center">
              <img id="preview-logo-2" src="<?php echo htmlspecialchars($thermal_logo); ?>" style="height: <?php echo $thermal_logo_height; ?>px; width:auto; margin-top: 10px; <?php echo $row['show_logo']==1 ? '' : 'display:none;'; ?>" /><br>
              <h1 class="title"><?php echo strtoupper($gen['company_name']); ?></h1>
              <p style="margin:0;"><em><?php echo $gen['company_tag']; ?></em></p>
              <hr class="sep-dot">
              <p style="margin:0;"><?php echo $gen['address']; ?></p>
              <p style="margin:0;">Cell: <?php echo $gen['phone1']; ?> | <?php echo $gen['phone2']; ?></p>
              <p id="preview-pin-2" style="margin:0; <?php echo $row['pin']==1 ? '' : 'display:none;'; ?>">PIN: <?php echo $gen['kra_pin']; ?></p>
            </div>
            <hr class="sep-eq">
            <div class="meta"><span>BILL NO: <b>1000</b></span><span><?php echo $today; ?></span></div>
            <div class="meta"><span>CASHIER: <b>STAFF NAME</b></span><span>12:30 PM</span></div>
            <div class="meta"><span>SHIFT: <b>DAY</b></span><span>COUNTER 1</span></div>
            <hr class="sep-eq">
            <table class="items-tbl">
              <thead>
                <tr>
                  <th>Item</th>
                  <th>Qty</th>
                  <th>Price</th>
                  <th>Total</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>ITEM 1</td>
                  <td>10</td>
                  <td>100.00</td>
                  <td>1000.00</td>
                </tr>
                <tr>
                  <td>ITEM 2</td>
                  <td>5</td>
                  <td>200.00</td>
                  <td>1000.00</td>
                </tr>
              </tbody>
            </table>
            <table class="totals-tbl">
              <tr class="grand">
                <td><strong>TOTAL</strong></td>
                <td><strong>2000.00</strong></td>
              </tr>
            </table>
            <div id="preview-tax-2" style="<?php echo $row['tax']==2 ? '' : 'display:none;'; ?>">
              <table class="totals-tbl">
                <tr><td>Gross</td><td>2000.00</td></tr>
                <tr><td>V.A.T (16%)</td><td>275.86</td></tr>
                <tr><td>Cat Levy (2%)</td><td>39.22</td></tr>
              </table>
            </div>
            <hr class="sep-dot">
            <p class="text-center" style="margin:0;"><b>THANK YOU</b></p>
            <p class="text-center" style="margin:0;" id="preview-thank-2"><?php echo htmlspecialchars($row['thankyou_note']); ?></p>
            <p class="text-center" style="margin:0;">This is not an ETR. Request one.</p>
            <hr class="sep-dot">
            <div class="mpesa-line" id="preview-mpesa-2"><?php if ($row['mpesa']==2 ){ echo "PAYBILL ".$gen['mpesa_no']." ACCOUNT: ".$gen['mpesa_account']; } else { echo "TILL ".$gen['mpesa_no']; } ?></div>
            <hr class="sep-dot">
            <p class="text-center" style="margin:0;font-size:11px;">K-Touch Point of Sale Designed & Sold by K-SYSTEMS <br> +254716100335 | +254735100335.</p>
          </div>
        </div>
      </div>
    </div>
    <!-- end panel-body -->
  </div>
  <!-- end panel --> 
<script type="text/javascript">
function updateTemplateCardState() {
  $('.template-card').removeClass('active');
  $('input[name="print_template"]:checked').closest('.template-card').addClass('active');
}

function selectedTemplate() {
  return $('input[name="print_template"]:checked').val() || '1';
}

function currentMpesaLabel() {
  if ($('#paybill').is(':checked')) {
    return 'PAYBILL <?php echo addslashes($gen['mpesa_no']); ?> ACCOUNT: <?php echo addslashes($gen['mpesa_account']); ?>';
  }
  return 'TILL <?php echo addslashes($gen['mpesa_no']); ?>';
}

function updatePreview() {
  var template = selectedTemplate();
  var thankText = $.trim($('#thank').val());
  var logoHeight = parseInt($('#thermal_logo_height').val(), 10);
  if (!thankText) {
    thankText = 'Thank you for your business.';
  }
  if (isNaN(logoHeight)) {
    logoHeight = 60;
  }
  if (logoHeight < 30) {
    logoHeight = 30;
  }
  if (logoHeight > 180) {
    logoHeight = 180;
  }

  $('.preview-template').removeClass('active');
  $('#preview-template-' + template).addClass('active');
  $('#preview-template-name').text(template === '2' ? 'Template 2' : 'Template 1');

  var showLogo = $('#logoyes').is(':checked');
  $('#preview-logo-1, #preview-logo-2').toggle(showLogo);
  $('#preview-logo-1, #preview-logo-2').css('height', logoHeight + 'px');

  var showPin = $('#pinyes').is(':checked');
  $('#preview-pin-1, #preview-pin-2').toggle(showPin);

  var showTax = $('#taxno').is(':checked');
  $('#preview-tax-1, #preview-tax-2').toggle(showTax);

  $('#preview-thank-1, #preview-thank-2').text(thankText);
  $('#preview-mpesa-1, #preview-mpesa-2').text(currentMpesaLabel());
}

$('input[name="print_template"]').on('change', function () {
  updateTemplateCardState();
  updatePreview();
});

$('#logoyes, #logono, #pinyes, #pinno, #taxyes, #taxno, #till, #paybill').on('change', updatePreview);
$('#thank').on('input keyup change', updatePreview);
$('#thermal_logo_height').on('input keyup change', updatePreview);

updateTemplateCardState();
updatePreview();

document.getElementById('btn-thermal').addEventListener('click', thermal);
  function thermal()
  {
    var form = $("#myform"); 
    var formData = new FormData(form[0]);
        $.ajax({
            type: "POST",
            url: '../settings/thermal_act.php',
            //dataType: 'json', //not sure but works for me without this
            data: formData,
            contentType: false, //this is requireded please see answers above
            processData: false, //this is requireded please see answers above
            //cache: false, //not sure but works for me without this
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
   // }





   //var data = $('#myform').serialize();
  // var form = $('#myform')[0];
  // var formData = new FormData(form);
  
  //   $.ajax(
  //       {
  //           url: 'back/settings/thermal_act.php',
  //           type: 'POST',                           
  //           data: formData,
  //           processData: false,
  //           contentType: false,
  //           dataType: 'html'
  //       })
  //       .done(function(data)
  //        {
  //           $('#results').html(''); // blank before load.
  //           $('#results').html(data);

  //       })
  //       .fail(function()
  //       {
  //           $('#results').html('<div class="alert alert-danger"><a href="#" class="close" data-dismiss="alert">&times;</a><strong>Warning!</strong> Error occured.</div>');
            
  //       });
  }
</script>
