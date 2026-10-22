<?php
include("../../inc/connection.php");

$col_chk = mysqli_query($connection, "SHOW COLUMNS FROM kp_settings_gen LIKE 'thermal_logo_path'");
if ($col_chk && mysqli_num_rows($col_chk) === 0) {
    mysqli_query($connection, "ALTER TABLE kp_settings_gen ADD COLUMN thermal_logo_path VARCHAR(255) NOT NULL DEFAULT ''");
}

$get_gen_details = mysqli_query($connection,"SELECT * FROM kp_settings_gen") or die("Could not get the gen details");
$row = mysqli_fetch_assoc($get_gen_details);
if (!$row) {
    $row = array(
        'image_path' => '',
        'thermal_logo_path' => ''
    );
}

$main_logo = trim((string)($row['image_path'] ?? ''));
$thermal_logo = trim((string)($row['thermal_logo_path'] ?? ''));
if ($thermal_logo === '') {
    $thermal_logo = $main_logo;
}
?>
<div class="panel">
  <div id="logo-results"></div>
  <form method="post" action="" enctype="multipart/form-data" id="logoform">
    <div class="panel-body row">
      <div class="col-sm-6">
        <fieldset>
          <legend class="m-b-15">Main Logo</legend>
          <p class="text-muted" style="margin-bottom:12px;">Used across the general business profile and non-thermal parts of the system.</p>
          <div class="img-preview" style="margin-bottom:12px;">
            <img src="<?php echo htmlspecialchars($main_logo); ?>" alt="No Main Logo" id="main-logo-preview" style="max-width: 220px; max-height: 180px; width:auto; height:auto; border-radius: 12px; border:1px solid #ddd; padding:8px; background:#fff;">
          </div>
          <input type="file" name="main_logo" id="main_logo" accept="image/*">
        </fieldset>
      </div>
      <div class="col-sm-6">
        <fieldset>
          <legend class="m-b-15">Thermal Print Logo</legend>
          <p class="text-muted" style="margin-bottom:12px;">Used on thermal receipts and printouts. If left blank, thermal printouts fall back to the main logo.</p>
          <div class="img-preview" style="margin-bottom:12px;">
            <img src="<?php echo htmlspecialchars($thermal_logo); ?>" alt="No Thermal Logo" id="thermal-logo-preview" style="max-width: 220px; max-height: 180px; width:auto; height:auto; border-radius: 12px; border:1px solid #ddd; padding:8px; background:#fff;">
          </div>
          <input type="file" name="thermal_logo" id="thermal_logo" accept="image/*">
        </fieldset>
      </div>
    </div>
  </form>
  <div class="panel-footer text-right">
    <button type="submit" id="submit-logo" class="btn btn-lime">Save Logos</button>
  </div>
</div>

<script type="text/javascript">
function previewLogo(input, imgId) {
  if (!input.files || !input.files[0]) return;
  var reader = new FileReader();
  reader.onload = function(e) {
    document.getElementById(imgId).src = e.target.result;
  };
  reader.readAsDataURL(input.files[0]);
}

document.getElementById('main_logo').addEventListener('change', function() {
  previewLogo(this, 'main-logo-preview');
});
document.getElementById('thermal_logo').addEventListener('change', function() {
  previewLogo(this, 'thermal-logo-preview');
});

document.getElementById('submit-logo').addEventListener('click', function () {
  var form = $("#logoform");
  var formData = new FormData(form[0]);
  $.ajax({
    type: "POST",
    url: '../settings/logo_set_act.php',
    data: formData,
    contentType: false,
    processData: false,
  })
  .done(function(data) {
    $('#logo-results').html(data);
  })
  .fail(function() {
    $('#logo-results').html('<div class="alert alert-danger"><a href="#" class="close" data-dismiss="alert">&times;</a><strong>Warning!</strong> Error occured.</div>');
  });
});
</script>
