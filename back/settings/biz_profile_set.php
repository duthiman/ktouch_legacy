<?php
include("../../inc/connection.php");
include("../clude/funcs.php");

// ── Auto-migration: create tables + column if they don't exist yet ────────────
mysqli_query($connection, "
    CREATE TABLE IF NOT EXISTS kp_business_profiles (
        profile_id   INT            NOT NULL AUTO_INCREMENT,
        profile_name VARCHAR(100)   NOT NULL,
        profile_slug VARCHAR(50)    NOT NULL,
        description  TEXT               NULL,
        is_active    TINYINT(1)     NOT NULL DEFAULT 1,
        created_at   TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (profile_id),
        UNIQUE KEY uq_profile_slug (profile_slug)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
");

mysqli_query($connection, "
    CREATE TABLE IF NOT EXISTS kp_profile_features (
        feature_id    INT          NOT NULL AUTO_INCREMENT,
        profile_id    INT          NOT NULL,
        feature_key   VARCHAR(60)  NOT NULL,
        feature_value VARCHAR(255) NOT NULL DEFAULT '1',
        PRIMARY KEY (feature_id),
        UNIQUE KEY uq_profile_feat (profile_id, feature_key),
        FOREIGN KEY (profile_id) REFERENCES kp_business_profiles (profile_id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
");

// Add profile_id column to kp_users if missing
$col_check = mysqli_query($connection,
    "SELECT COUNT(*) AS cnt FROM information_schema.COLUMNS
      WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME   = 'kp_users'
        AND COLUMN_NAME  = 'profile_id'");
$col_row = mysqli_fetch_assoc($col_check);
if ((int)$col_row['cnt'] === 0) {
    mysqli_query($connection, "ALTER TABLE kp_users ADD COLUMN profile_id INT NOT NULL DEFAULT 1");
}

// Seed default profiles if table is empty
$seed_check = mysqli_query($connection, "SELECT COUNT(*) AS cnt FROM kp_business_profiles");
$seed_row   = mysqli_fetch_assoc($seed_check);
if ((int)$seed_row['cnt'] === 0) {
    mysqli_query($connection, "
        INSERT INTO kp_business_profiles (profile_name, profile_slug, description) VALUES
        ('Full-Service Restaurant', 'restaurant', 'Dine-in with kitchen, grill, multi-counter POS, and structured inventory.'),
        ('Quick-Service / Café',    'cafe',        'Single counter, simple purchases, no grill, no sports.'),
        ('Sports Bar',              'sports_bar',  'Bar with sports viewing, grill, multi-counter, simple purchases.'),
        ('Bar (Beverages Only)',    'bar',         'Drinks only. No kitchen, no grill, no store auto-issue, no sports.')
    ");
    mysqli_query($connection, "
        INSERT INTO kp_profile_features (profile_id, feature_key, feature_value) VALUES
        (1,'multi_counter','1'),(1,'counter_count','4'),(1,'has_store','1'),(1,'auto_issue_to_store','0'),
        (1,'has_kitchen','1'),(1,'has_grill','1'),(1,'purchase_flow','structured'),(1,'structure_purchase_required','1'),(1,'has_sports','0'),
        (2,'multi_counter','0'),(2,'counter_count','1'),(2,'has_store','1'),(2,'auto_issue_to_store','1'),
        (2,'has_kitchen','1'),(2,'has_grill','0'),(2,'purchase_flow','simple'),(2,'structure_purchase_required','0'),(2,'has_sports','0'),
        (3,'multi_counter','1'),(3,'counter_count','3'),(3,'has_store','1'),(3,'auto_issue_to_store','1'),
        (3,'has_kitchen','0'),(3,'has_grill','1'),(3,'purchase_flow','simple'),(3,'structure_purchase_required','0'),(3,'has_sports','1'),
        (4,'multi_counter','0'),(4,'counter_count','1'),(4,'has_store','0'),(4,'auto_issue_to_store','0'),
        (4,'has_kitchen','0'),(4,'has_grill','0'),(4,'purchase_flow','simple'),(4,'structure_purchase_required','0'),(4,'has_sports','0')
    ");
}
// ── End auto-migration ────────────────────────────────────────────────────────

// ── Current active profile (read from kp_users — all users share one profile) ─
$current_profile_id = 1;
$res = mysqli_query($connection, "SELECT profile_id FROM kp_users LIMIT 1");
if ($res && $row = mysqli_fetch_assoc($res)) {
    $current_profile_id = (int)$row['profile_id'];
}

// ── All available profiles ────────────────────────────────────────────────────
$profiles = [];
$res_p = mysqli_query($connection,
    "SELECT profile_id, profile_name, profile_slug, description
       FROM kp_business_profiles
      WHERE is_active = 1
      ORDER BY profile_id ASC");
while ($p = mysqli_fetch_assoc($res_p)) {
    $profiles[] = $p;
}

// ── Features of each profile (for the preview card) ──────────────────────────
$all_features = [];
$res_f = mysqli_query($connection,
    "SELECT profile_id, feature_key, feature_value
       FROM kp_profile_features
      ORDER BY profile_id, feature_key");
while ($f = mysqli_fetch_assoc($res_f)) {
    $all_features[$f['profile_id']][$f['feature_key']] = $f['feature_value'];
}

$feature_labels = [
    'multi_counter'               => 'Multiple Counters',
    'counter_count'               => 'Counter Count',
    'has_store'                   => 'Store / Inventory',
    'auto_issue_to_store'         => 'Auto-Issue to Store',
    'has_kitchen'                 => 'Kitchen',
    'has_grill'                   => 'Grill Station',
    'purchase_flow'               => 'Purchase Flow',
    'structure_purchase_required' => 'Purchase Approval Required',
    'has_sports'                  => 'Sports Module',
];
?>
<!-- begin panel -->
<div class="panel">
    <div class="panel-body">
        <div id="results"></div>
        <h4><i class="fa fa-building"></i> Business Profile</h4>
        <p class="text-muted" style="font-size:13px;">
            Select the profile that matches how this branch operates.
            All system modules (POS, kitchen, purchasing, navigation) will
            automatically adapt to the chosen profile.
        </p>

        <form method="post" action="" id="biz_profile_form">
        <div class="row">

            <!-- ── Left: profile selector ───────────────────────────── -->
            <div class="col-sm-5">
                <fieldset>
                    <legend class="m-b-15">SELECT PROFILE</legend>

                    <?php foreach ($profiles as $p): ?>
                    <div class="radio radio-css m-b-10">
                        <input type="radio"
                               name="profile_id"
                               id="p_<?= $p['profile_id'] ?>"
                               value="<?= $p['profile_id'] ?>"
                               <?= $p['profile_id'] == $current_profile_id ? 'checked' : '' ?>
                               onchange="showFeatures(<?= $p['profile_id'] ?>)" />
                        <label for="p_<?= $p['profile_id'] ?>">
                            <strong><?= htmlspecialchars($p['profile_name']) ?></strong><br>
                            <small class="text-muted"><?= htmlspecialchars($p['description']) ?></small>
                        </label>
                    </div>
                    <?php endforeach; ?>

                </fieldset>
            </div>

            <!-- ── Right: feature preview card ─────────────────────── -->
            <div class="col-sm-7">
                <fieldset>
                    <legend class="m-b-15">PROFILE FEATURES</legend>
                    <?php foreach ($profiles as $p):
                        $feats = $all_features[$p['profile_id']] ?? [];
                    ?>
                    <div id="features_<?= $p['profile_id'] ?>"
                         class="profile-features-card"
                         style="display:<?= $p['profile_id'] == $current_profile_id ? 'block' : 'none' ?>;">
                        <table class="table table-sm table-bordered" style="font-size:13px;">
                            <tbody>
                            <?php foreach ($feature_labels as $key => $label):
                                $val = $feats[$key] ?? '—';
                                // Render booleans as badges
                                if (in_array($val, ['1','0'])) {
                                    $badge = $val === '1'
                                        ? '<span class="badge badge-success">YES</span>'
                                        : '<span class="badge badge-secondary">NO</span>';
                                } else {
                                    $badge = '<span class="badge badge-info">' . htmlspecialchars(strtoupper($val)) . '</span>';
                                }
                            ?>
                            <tr>
                                <td style="color:#555;"><?= $label ?></td>
                                <td><?= $badge ?></td>
                            </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endforeach; ?>
                </fieldset>
            </div>

        </div><!-- /.row -->
        </form>
    </div>

    <div class="panel-footer text-right">
        <button type="button" class="btn btn-primary m-r-5" data-toggle="modal" data-target="#addProfileModal">
            <i class="fa fa-plus"></i> Add Profile
        </button>
        <button type="button" class="btn btn-lime" onclick="saveBizProfile();">
            <i class="fa fa-save"></i> Save Profile
        </button>
    </div>
</div>

<!-- ── Add Profile Modal ──────────────────────────────────────────────────── -->
<div class="modal fade" id="addProfileModal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fa fa-plus-circle"></i> Add New Business Profile</h5>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body">
        <div id="modal_results"></div>
        <div class="row">

          <!-- Left col: name / slug / description -->
          <div class="col-sm-5">
            <fieldset>
              <legend class="m-b-10">PROFILE DETAILS</legend>

              <div class="form-group">
                <label style="color:blue;">Profile Name <span class="text-danger">*</span></label>
                <input type="text" id="np_name" class="form-control"
                       placeholder="e.g. Hotel Restaurant"
                       oninput="autoSlug(this.value)" />
              </div>
              <div class="form-group">
                <label style="color:blue;">Slug <span class="text-danger">*</span></label>
                <input type="text" id="np_slug" class="form-control"
                       placeholder="e.g. hotel_restaurant" />
                <small class="text-muted">Lowercase, underscores only. Auto-filled from name.</small>
              </div>
              <div class="form-group">
                <label style="color:blue;">Description</label>
                <textarea id="np_desc" class="form-control" rows="3"
                          placeholder="Short description of this profile"></textarea>
              </div>
            </fieldset>
          </div>

          <!-- Right col: feature toggles -->
          <div class="col-sm-7">
            <fieldset>
              <legend class="m-b-10">FEATURES</legend>
              <table class="table table-sm" style="font-size:13px;">
                <tbody>

                  <tr>
                    <td style="color:#555; vertical-align:middle;">Multiple Counters</td>
                    <td>
                      <div class="radio radio-css radio-inline">
                        <input type="radio" name="np_multi_counter" id="np_mc_yes" value="1" checked
                               onchange="toggleCounterCount(1)" />
                        <label for="np_mc_yes">YES</label>
                      </div>
                      <div class="radio radio-css radio-inline">
                        <input type="radio" name="np_multi_counter" id="np_mc_no" value="0"
                               onchange="toggleCounterCount(0)" />
                        <label for="np_mc_no">NO</label>
                      </div>
                    </td>
                  </tr>

                  <tr id="np_cc_row">
                    <td style="color:#555; vertical-align:middle; padding-left:20px;">↳ Counter Count</td>
                    <td><input type="number" id="np_counter_count" class="form-control form-control-sm"
                               value="2" min="1" max="20" style="width:80px;" /></td>
                  </tr>

                  <tr>
                    <td style="color:#555; vertical-align:middle;">Store / Inventory</td>
                    <td>
                      <div class="radio radio-css radio-inline">
                        <input type="radio" name="np_has_store" id="np_hs_yes" value="1" checked
                               onchange="toggleAutoIssue(1)" />
                        <label for="np_hs_yes">YES</label>
                      </div>
                      <div class="radio radio-css radio-inline">
                        <input type="radio" name="np_has_store" id="np_hs_no" value="0"
                               onchange="toggleAutoIssue(0)" />
                        <label for="np_hs_no">NO</label>
                      </div>
                    </td>
                  </tr>

                  <tr id="np_ai_row">
                    <td style="color:#555; vertical-align:middle; padding-left:20px;">↳ Auto-Issue to Store</td>
                    <td>
                      <div class="radio radio-css radio-inline">
                        <input type="radio" name="np_auto_issue" id="np_ai_yes" value="1" />
                        <label for="np_ai_yes">YES</label>
                      </div>
                      <div class="radio radio-css radio-inline">
                        <input type="radio" name="np_auto_issue" id="np_ai_no" value="0" checked />
                        <label for="np_ai_no">NO</label>
                      </div>
                    </td>
                  </tr>

                  <tr>
                    <td style="color:#555; vertical-align:middle;">Kitchen</td>
                    <td>
                      <div class="radio radio-css radio-inline">
                        <input type="radio" name="np_has_kitchen" id="np_hk_yes" value="1" checked />
                        <label for="np_hk_yes">YES</label>
                      </div>
                      <div class="radio radio-css radio-inline">
                        <input type="radio" name="np_has_kitchen" id="np_hk_no" value="0" />
                        <label for="np_hk_no">NO</label>
                      </div>
                    </td>
                  </tr>

                  <tr>
                    <td style="color:#555; vertical-align:middle;">Grill Station</td>
                    <td>
                      <div class="radio radio-css radio-inline">
                        <input type="radio" name="np_has_grill" id="np_hg_yes" value="1" />
                        <label for="np_hg_yes">YES</label>
                      </div>
                      <div class="radio radio-css radio-inline">
                        <input type="radio" name="np_has_grill" id="np_hg_no" value="0" checked />
                        <label for="np_hg_no">NO</label>
                      </div>
                    </td>
                  </tr>

                  <tr>
                    <td style="color:#555; vertical-align:middle;">Purchase Flow</td>
                    <td>
                      <select id="np_purchase_flow" class="form-control form-control-sm"
                              style="width:140px;" onchange="toggleApproval(this.value)">
                        <option value="simple">Simple</option>
                        <option value="structured">Structured (LPO)</option>
                      </select>
                    </td>
                  </tr>

                  <tr id="np_approval_row" style="display:none;">
                    <td style="color:#555; vertical-align:middle; padding-left:20px;">↳ Approval Required</td>
                    <td>
                      <div class="radio radio-css radio-inline">
                        <input type="radio" name="np_approval" id="np_apr_yes" value="1" checked />
                        <label for="np_apr_yes">YES</label>
                      </div>
                      <div class="radio radio-css radio-inline">
                        <input type="radio" name="np_approval" id="np_apr_no" value="0" />
                        <label for="np_apr_no">NO</label>
                      </div>
                    </td>
                  </tr>

                  <tr>
                    <td style="color:#555; vertical-align:middle;">Sports Module</td>
                    <td>
                      <div class="radio radio-css radio-inline">
                        <input type="radio" name="np_has_sports" id="np_sp_yes" value="1" />
                        <label for="np_sp_yes">YES</label>
                      </div>
                      <div class="radio radio-css radio-inline">
                        <input type="radio" name="np_has_sports" id="np_sp_no" value="0" checked />
                        <label for="np_sp_no">NO</label>
                      </div>
                    </td>
                  </tr>

                </tbody>
              </table>
            </fieldset>
          </div>

        </div><!-- /.row -->
      </div><!-- /.modal-body -->
      <div class="modal-footer">
        <button type="button" class="close-btn btn btn-default" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" onclick="submitNewProfile();">
            <i class="fa fa-save"></i> Create Profile
        </button>
      </div>
    </div>
  </div>
</div>

<script type="text/javascript">
// ── Feature visibility helpers ────────────────────────────────────────────────
function toggleCounterCount(val) {
    document.getElementById('np_cc_row').style.display = val == 1 ? '' : 'none';
}
function toggleAutoIssue(val) {
    document.getElementById('np_ai_row').style.display = val == 1 ? '' : 'none';
}
function toggleApproval(val) {
    document.getElementById('np_approval_row').style.display = val === 'structured' ? '' : 'none';
}
function autoSlug(name) {
    document.getElementById('np_slug').value = name.toLowerCase()
        .replace(/[^a-z0-9\s_]/g, '').replace(/\s+/g, '_').replace(/_+/g, '_');
}

// ── Show the feature card for the selected profile ────────────────────────────
function showFeatures(profileId) {
    document.querySelectorAll('.profile-features-card').forEach(function(el) {
        el.style.display = 'none';
    });
    var card = document.getElementById('features_' + profileId);
    if (card) card.style.display = 'block';
}

// ── Save active profile ───────────────────────────────────────────────────────
function saveBizProfile() {
    var selected = document.querySelector('input[name="profile_id"]:checked');
    if (!selected) {
        $('#results').html('<div class="alert alert-danger">Please select a profile.</div>');
        return;
    }
    $.ajax({
        url: '../settings/biz_profile_act.php',
        type: 'POST',
        data: { profile_id: selected.value },
        dataType: 'html'
    })
    .done(function(data) {
        $('#results').html(data);
        $('#set').scrollTop(0);
    })
    .fail(function() {
        $('#results').html('<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert">&times;</button>Something went wrong. Please try again.</div>');
    });
}

// ── Create new profile ────────────────────────────────────────────────────────
function submitNewProfile() {
    var name = $('#np_name').val().trim();
    var slug = $('#np_slug').val().trim();
    if (!name || !slug) {
        $('#modal_results').html('<div class="alert alert-danger m-b-5">Profile Name and Slug are required.</div>');
        return;
    }

    var data = {
        action:                       'add',
        profile_name:                 name,
        profile_slug:                 slug,
        profile_desc:                 $('#np_desc').val().trim(),
        multi_counter:                $('input[name="np_multi_counter"]:checked').val(),
        counter_count:                $('#np_counter_count').val(),
        has_store:                    $('input[name="np_has_store"]:checked').val(),
        auto_issue_to_store:          $('input[name="np_auto_issue"]:checked').val(),
        has_kitchen:                  $('input[name="np_has_kitchen"]:checked').val(),
        has_grill:                    $('input[name="np_has_grill"]:checked').val(),
        purchase_flow:                $('#np_purchase_flow').val(),
        structure_purchase_required:  $('input[name="np_approval"]:checked').val(),
        has_sports:                   $('input[name="np_has_sports"]:checked').val()
    };

    $.ajax({
        url: '../settings/biz_profile_add.php',
        type: 'POST',
        data: data,
        dataType: 'html'
    })
    .done(function(response) {
        if (response.indexOf('alert-success') !== -1) {
            $('#addProfileModal').modal('hide');
            // Reload the whole tab to show the new profile
            $('#biz-profile').trigger('click');
        } else {
            $('#modal_results').html(response);
        }
    })
    .fail(function() {
        $('#modal_results').html('<div class="alert alert-danger">Something went wrong. Please try again.</div>');
    });
}

// Clear modal state when it's closed
$('#addProfileModal').on('hidden.bs.modal', function() {
    $('#modal_results').html('');
});
</script>
