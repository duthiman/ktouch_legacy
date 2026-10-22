<?php
include("../../inc/connection.php");
include('../../inc/gen_funcs.php');

function ensure_pay_mode_default_columns($connection) {
    $default_columns = [
        'is_default_cash',
        'is_default_mpesa',
        'is_default_card',
        'is_default_complimentary'
    ];
    foreach ($default_columns as $column) {
        $c = mysqli_query($connection, "SHOW COLUMNS FROM kp_pay_mode LIKE '$column'");
        if ($c && mysqli_num_rows($c) === 0) {
            mysqli_query($connection, "ALTER TABLE kp_pay_mode ADD COLUMN $column INT NOT NULL DEFAULT 2");
        }
    }
}

ensure_pay_mode_default_columns($connection);

// ── Migrations ────────────────────────────────────────────────────────────────
$c = mysqli_query($connection, "SHOW COLUMNS FROM kp_kpesa_settings LIKE 'profile_name'");
if ($c && mysqli_num_rows($c) === 0) {
    mysqli_query($connection, "ALTER TABLE kp_kpesa_settings ADD COLUMN profile_name VARCHAR(100) NOT NULL DEFAULT 'Default' AFTER set_id");
    mysqli_query($connection, "UPDATE kp_kpesa_settings SET profile_name='Default' WHERE set_id=1");
}
$c2 = mysqli_query($connection, "SHOW COLUMNS FROM kp_pay_mode LIKE 'kpesa_profile_id'");
if ($c2 && mysqli_num_rows($c2) === 0) {
    mysqli_query($connection, "ALTER TABLE kp_pay_mode ADD COLUMN kpesa_profile_id INT NULL DEFAULT NULL");
}

// ── Load KPesa profiles ───────────────────────────────────────────────────────
$kpesa_profiles = [];
$kp_r = mysqli_query($connection, "SELECT set_id, profile_name FROM kp_kpesa_settings WHERE is_active=1 ORDER BY set_id ASC");
if ($kp_r) while ($kp = mysqli_fetch_assoc($kp_r)) $kpesa_profiles[] = $kp;
?>
<!-- begin panel -->
<div class="panel panel-inverse" data-sortable-id="form-validation-2"> 
   <!-- begin panel-body -->
    <div class="panel-body ">
        <fieldset>
            <legend class="m-b-15">Payment methods</legend>
            <div id="results"></div>
            <div class="form-group row m-b-15">
                <div class="form-group row col-md-6">
                    <div class="form-group col-md-12">
                        <label>Payment Name</label>
                        <input type="text" class="form-control" name="name" id="name" />
                    </div> 
                    <div class="form-group col-md-10 col-sm-12">
                        <label >Bank to Affect</label>
                        <select class="form-control" id="bank" name="bank" onchange=""> 
                            <option  value="">--------SELECT BANK-------</option>     <?php
                            $bank = "SELECT * FROM kp_banks WHERE  1";
                            $bank_r = mysqli_query($connection,$bank) or die("Error getting categories");
                            while ($ct = mysqli_fetch_assoc($bank_r)) {
                                $name = $ct['name'];
                                $bank_id = $ct['bank_id'];
                                ?>
                            <option  value="<?php echo $bank_id; ?>"><?php echo $name; ?></option>
                            <?php
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group col-md-10">
                        <div class="row" style="margin: 0 0px;">
                            <label class="col-md-8" >Select Payment Color</label>
                            <input id="color"  class="form-control col-md-6">
                            <div class="form-group-prepend col-md-3" style="margin: 0 -15px;">
                               <a class="btn btn-warning" data-toggle="dropdown">Color</a>
                              <ul class="dropdown-menu">
                                <li><div id="colorpalette1"></div></li>
                              </ul>   
                            </div>
                        </div>  
                    </div>
                    <div class="form-group row col-md-6" >
                        <label class="col-md-8" >ZERO AMOUNT</label>
                        <div class="col-md-12" >
                            <div class="radio radio-css radio-inline">
                                <input type="radio" name="zero" id="zero-yes" value="1" />
                                <label for="zero-yes">Yes</label>
                            </div>
                            <div class="radio radio-css radio-inline">
                                <input type="radio" name="zero" id="zero-no" value="2" />
                                <label for="zero-no">No</label>
                            </div>
                        </div>
                    </div>
                    <div class="form-group row col-md-6" >
                        <label class="col-md-8" >Payment has Code/No./Ref #</label>
                        <div class="col-md-12" >
                            <div class="radio radio-css radio-inline">
                                <input type="radio" name="has-ref" id="yes" value="1" />
                                <label for="yes">Yes</label>
                            </div>
                            <div class="radio radio-css radio-inline">
                                <input type="radio" name="has-ref" id="no" value="2" />
                                <label for="no">No</label>
                            </div>
                        </div>
                    </div>
                    <div class="form-group row col-md-6" >
                      <label class="col-md-8" >Has Change</label>
                        <div class="col-md-12" >
                            <div class="radio radio-css radio-inline">
                                <input type="radio" name="has-change" id="change-yes" value="1" />
                                <label for="change-yes">Yes</label>
                            </div>
                            <div class="radio radio-css radio-inline">
                                <input type="radio" name="has-change" id="change-no" value="2" />
                                <label for="change-no">No</label>
                            </div>
                        </div>
                    </div>
                    <div class="form-group row col-md-6" >
                        <label class="col-md-8" >Has Gateway</label>
                        <div class="col-md-12" >
                            <div class="radio radio-css radio-inline">
                                <input type="radio" name="has-gate" id="gate-yes" value="1" onchange="toggleGatewaySection(1)" />
                                <label for="gate-yes">Yes</label>
                            </div>
                            <div class="radio radio-css radio-inline">
                                <input type="radio" name="has-gate" id="gate-no" value="2" onchange="toggleGatewaySection(0)" />
                                <label for="gate-no">No</label>
                            </div>
                        </div>
                    </div>
                    <div class="form-group col-md-6">
                        <label>Table Column</label>
                        <input type="text" class="form-control" name="gate_col" id="gate_col" />
                    </div>

                    <!-- KPesa Profile Attachment -->
                    <div class="form-group col-md-10" id="kpesa-section" style="display:none;">
                        <div style="border:1px solid #c8e0f4;border-radius:6px;padding:12px;background:#f0f8ff;">
                            <label style="color:#1a6496;font-weight:bold;margin-bottom:8px;">
                                <i class="fa fa-mobile-alt"></i> Attach to KPesa Profile
                            </label>
                            <select class="form-control" id="kpesa_profile_id" name="kpesa_profile_id">
                                <option value="">-- No KPesa Profile --</option>
                                <?php foreach ($kpesa_profiles as $kp): ?>
                                <option value="<?= $kp['set_id'] ?>"><?= htmlspecialchars($kp['profile_name']) ?></option>
                                <?php endforeach; ?>
                                <?php if (empty($kpesa_profiles)): ?>
                                <option value="" disabled>No profiles — configure KPesa first</option>
                                <?php endif; ?>
                            </select>
                            <small class="text-muted">Link this payment method to a KPesa gateway configuration.</small>
                        </div>
                    </div>
                    <div class="form-group col-md-12" style="margin-top:8px;">
                        <div style="border:1px solid #e4d4ef;border-radius:8px;padding:12px;background:#faf5ff;">
                            <label style="color:#753f91;font-weight:bold;margin-bottom:10px;display:block;">Default Use In Sales / Clearance</label>
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="d-block">Cash Default</label>
                                    <div class="radio radio-css radio-inline">
                                        <input type="radio" name="default-cash" id="default-cash-yes" value="1" />
                                        <label for="default-cash-yes">Yes</label>
                                    </div>
                                    <div class="radio radio-css radio-inline">
                                        <input type="radio" name="default-cash" id="default-cash-no" value="2" checked />
                                        <label for="default-cash-no">No</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="d-block">M-Pesa Default</label>
                                    <div class="radio radio-css radio-inline">
                                        <input type="radio" name="default-mpesa" id="default-mpesa-yes" value="1" />
                                        <label for="default-mpesa-yes">Yes</label>
                                    </div>
                                    <div class="radio radio-css radio-inline">
                                        <input type="radio" name="default-mpesa" id="default-mpesa-no" value="2" checked />
                                        <label for="default-mpesa-no">No</label>
                                    </div>
                                </div>
                                <div class="col-md-6" style="margin-top:10px;">
                                    <label class="d-block">Card Default</label>
                                    <div class="radio radio-css radio-inline">
                                        <input type="radio" name="default-card" id="default-card-yes" value="1" />
                                        <label for="default-card-yes">Yes</label>
                                    </div>
                                    <div class="radio radio-css radio-inline">
                                        <input type="radio" name="default-card" id="default-card-no" value="2" checked />
                                        <label for="default-card-no">No</label>
                                    </div>
                                </div>
                                <div class="col-md-6" style="margin-top:10px;">
                                    <label class="d-block">Complementary Default</label>
                                    <div class="radio radio-css radio-inline">
                                        <input type="radio" name="default-complimentary" id="default-complimentary-yes" value="1" />
                                        <label for="default-complimentary-yes">Yes</label>
                                    </div>
                                    <div class="radio radio-css radio-inline">
                                        <input type="radio" name="default-complimentary" id="default-complimentary-no" value="2" checked />
                                        <label for="default-complimentary-no">No</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-group col-md-6 ">
                    <fieldset id="payments"> </fieldset>
                </div>
            </div>
        </fieldset>
    </div>
    <!-- end panel-body -->
    <!--start footer-->
    <div class="panel-footer">
        <button type="button" name="new-pay" id="new-pay" class=" btn btn-lime ">Save</button>
    </div>  
</div>
<!-- end panel --> 
<div  id="pay-modal" class="modal fade bs-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" style="display: none;">
   <div class="modal-dialog"  id="pay_load"></div>
</div>
<script>
function toggleGatewaySection(show) {
    $('#kpesa-section').toggle(show == 1);
    if (!show) $('#kpesa_profile_id').val('');
}

document.getElementById('new-pay').addEventListener('click', general);

function general(){
    var name          = $("#name").val();
    var color         = $("#color").val();
    var bank          = $("#bank").val();
    var zero          = $("input[name='zero']:checked").val();
    var has_ref       = $("input[name='has-ref']:checked").val();
    var has_change    = $("input[name='has-change']:checked").val();
    var has_gate      = $("input[name='has-gate']:checked").val();
    var gate_col      = $("#gate_col").val();
    var kpesa_profile = $("#kpesa_profile_id").val();
    var default_cash  = $("input[name='default-cash']:checked").val();
    var default_mpesa = $("input[name='default-mpesa']:checked").val();
    var default_card  = $("input[name='default-card']:checked").val();
    var default_comp  = $("input[name='default-complimentary']:checked").val();
    $.ajax({
        url: 'pay_meth_act.php',
        type: 'POST',
        data: { name:name, color:color, bank:bank, has_ref:has_ref, has_change:has_change,
                gate_col:gate_col, has_gate:has_gate, kpesa_profile_id:kpesa_profile,
                default_cash:default_cash, default_mpesa:default_mpesa,
                default_card:default_card, default_complimentary:default_comp },
        dataType: 'html'
    })
    .done(function(data){
        $('#results').html(data);
    })
    .fail(function(){
        $('#results').html('<div class="alert alert-danger"><a href="#" class="close" data-dismiss="alert">&times;</a><strong>Warning!</strong> Error occured.</div>');
    });
}
</script>
 
