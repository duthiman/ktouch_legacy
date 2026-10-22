<?php
include("../../inc/connection.php");
include("../../inc/gen_funcs.php");

$cfg = get_token_config();
$walletCount = column_count('kp_staff_token_wallet', 'user_id', "1");
$entryCount = column_count('kp_staff_token_entries', 'entry_id', "1");
$rangeRows = parse_token_brackets($cfg['token_brackets']);
if (empty($rangeRows)) {
  $rangeRows = array(
    array('min' => 0, 'max' => 100, 'token' => 5),
    array('min' => 100.01, 'max' => 250, 'token' => 12),
    array('min' => 250.01, 'max' => null, 'token' => 20),
  );
}
?>
<style type="text/css">
  .token-shell {
    background: linear-gradient(180deg, #ffffff 0%, #fbf8ff 100%);
    border: 1px solid #eee3f5;
    border-radius: 18px;
    box-shadow: 0 12px 28px rgba(117, 63, 145, 0.08);
    overflow: hidden;
  }

  .token-shell .panel-body {
    padding: 18px 18px 10px;
  }

  .token-shell .panel-footer {
    background: #faf6fd;
    border-top: 1px solid #efe4f7;
    padding: 14px 18px;
  }

  .token-card {
    background: #ffffff;
    border: 1px solid #eee6f5;
    border-radius: 16px;
    box-shadow: 0 10px 24px rgba(117, 63, 145, 0.06);
    padding: 16px;
    margin-bottom: 14px;
  }

  .token-card h5,
  .token-card h6,
  .token-card legend {
    color: #4f285f;
  }

  .token-card .text-muted {
    color: #7b6f86 !important;
  }

  .token-metric {
    display: flex;
    justify-content: space-between;
    gap: 12px;
    padding: 10px 0;
    border-bottom: 1px solid #f1e9f6;
  }

  .token-metric:last-child {
    border-bottom: none;
    padding-bottom: 0;
  }

  .token-metric-label {
    color: #735e81;
    font-weight: 600;
  }

  .token-metric-value {
    color: #2d1538;
    font-weight: 800;
  }

  .token-range-grid {
    display: grid;
    grid-template-columns: minmax(0, 1.7fr) minmax(260px, 0.9fr);
    gap: 16px;
    align-items: start;
  }

  .token-range-editor,
  .token-range-help {
    background: #fcfaff;
    border: 1px solid #eee3f6;
    border-radius: 16px;
    padding: 14px;
  }

  .token-range-help {
    position: sticky;
    top: 10px;
  }

  .token-range-help h6 {
    margin-top: 0;
    margin-bottom: 10px;
    font-size: 14px;
    font-weight: 800;
    color: #5b2f71;
  }

  .token-example-box {
    background: linear-gradient(180deg, #f8f2fc 0%, #ffffff 100%);
    border: 1px solid #ead7f7;
    border-radius: 12px;
    padding: 12px 14px;
  }

  .token-example-line {
    padding: 8px 0;
    border-bottom: 1px dashed #e8dbf1;
    color: #4b3e57;
  }

  .token-example-line:last-child {
    border-bottom: none;
    padding-bottom: 0;
  }

  #token-range-table thead th {
    background: #f5edfb;
    color: #5b2f71;
    border-bottom: 1px solid #eadcf5;
    font-weight: 800;
  }

  #token-range-table tbody td {
    vertical-align: middle;
    background: #fff;
  }

  #token-range-table .form-control {
    border-radius: 10px;
    border-color: #e5d8ef;
    box-shadow: none;
  }

  #add-token-range {
    border-radius: 10px;
    padding: 7px 14px;
    font-weight: 700;
    background: #753f91;
    border-color: #753f91;
  }

  .token-range-remove {
    border-radius: 9px;
    padding: 6px 10px;
    font-weight: 700;
  }

  @media (max-width: 991px) {
    .token-range-grid {
      grid-template-columns: 1fr;
    }

    .token-range-help {
      position: static;
    }
  }
</style>
<div class="panel token-shell">
  <div class="panel-body row">
    <div id="results" class="col-md-12"></div>
    <form method="post" action="" id="token-form" class="col-md-12">
      <div class="col-md-7">
        <fieldset class="token-card">
          <legend class="m-b-15">Waiter Token Engine</legend>
          <p class="text-muted" style="margin-bottom:16px;">
            Tokens are awarded automatically from finalized sales, then adjusted again if a bill is returned or voided. The system keeps a full movement trail for each waiter.
          </p>

          <div class="form-group">
            <label class="col-form-label" style="display:block;">Enable Tokens</label>
            <div class="radio radio-css radio-inline">
              <input type="radio" name="token_enabled" id="token_enabled_yes" value="1" <?php echo (int)$cfg['token_enabled'] === 1 ? 'checked' : ''; ?> />
              <label for="token_enabled_yes">Yes</label>
            </div>
            <div class="radio radio-css radio-inline">
              <input type="radio" name="token_enabled" id="token_enabled_no" value="2" <?php echo (int)$cfg['token_enabled'] !== 1 ? 'checked' : ''; ?> />
              <label for="token_enabled_no">No</label>
            </div>
          </div>

          <div class="form-group">
            <label class="col-form-label" style="display:block;">Award Method</label>
            <div class="radio radio-css">
              <input type="radio" name="token_mode" id="token_mode_piece" value="1" <?php echo (int)$cfg['token_mode'] === 1 ? 'checked' : ''; ?> />
              <label for="token_mode_piece">By pieces sold</label>
            </div>
            <div class="radio radio-css">
              <input type="radio" name="token_mode" id="token_mode_bracket" value="2" <?php echo (int)$cfg['token_mode'] === 2 ? 'checked' : ''; ?> />
              <label for="token_mode_bracket">By bill amount ranges</label>
            </div>
            <div class="radio radio-css">
              <input type="radio" name="token_mode" id="token_mode_percent" value="3" <?php echo (int)$cfg['token_mode'] === 3 ? 'checked' : ''; ?> />
              <label for="token_mode_percent">By percentage of the bill</label>
            </div>
          </div>

          <div class="form-group" id="token-piece-wrap">
            <label class="col-form-label">Token amount per piece sold</label>
            <input class="form-control" type="number" step="0.01" min="0" id="token_piece_value" name="token_piece_value" value="<?php echo htmlspecialchars($cfg['token_piece_value']); ?>" />
            <small class="text-muted">Example: `2` means each sold piece gives 2 tokens.</small>
          </div>

          <div class="form-group" id="token-percent-wrap">
            <label class="col-form-label">Token percentage of the bill</label>
            <input class="form-control" type="number" step="0.01" min="0" id="token_percent_value" name="token_percent_value" value="<?php echo htmlspecialchars($cfg['token_percent_value']); ?>" />
            <small class="text-muted">Use normal percentage values. Example: `5` means 5% of the bill becomes tokens.</small>
          </div>

          <div class="form-group" id="token-bracket-wrap">
            <label class="col-form-label">Range rules</label>
            <div class="token-range-grid">
              <div class="token-range-editor">
                <div class="table-responsive" style="border:1px solid #ece1f4;border-radius:12px;overflow:hidden;">
                  <table class="table table-bordered table-striped" style="margin-bottom:0;" id="token-range-table">
                    <thead>
                      <tr>
                        <th style="width:32%;">From Amount</th>
                        <th style="width:32%;">To Amount</th>
                        <th style="width:24%;">Tokens</th>
                        <th style="width:12%;">Action</th>
                      </tr>
                    </thead>
                    <tbody id="token-range-body">
                      <?php foreach ($rangeRows as $row) { ?>
                      <tr class="token-range-row">
                        <td><input class="form-control range-min" type="number" step="0.01" min="0" name="range_min[]" value="<?php echo htmlspecialchars($row['min']); ?>" /></td>
                        <td><input class="form-control range-max" type="number" step="0.01" min="0" name="range_max[]" value="<?php echo $row['max'] === null ? '' : htmlspecialchars($row['max']); ?>" placeholder="Leave blank for no limit" /></td>
                        <td><input class="form-control range-token" type="number" step="0.01" min="0" name="range_token[]" value="<?php echo htmlspecialchars($row['token']); ?>" /></td>
                        <td class="text-center"><button type="button" class="btn btn-danger btn-xs token-range-remove">Remove</button></td>
                      </tr>
                      <?php } ?>
                    </tbody>
                  </table>
                </div>
                <div style="margin-top:10px;">
                  <button type="button" class="btn btn-primary btn-sm" id="add-token-range">Add Range</button>
                </div>
                <small class="text-muted" style="display:block;margin-top:8px;">
                  Add one row per bill range. Leave <strong>To Amount</strong> blank for the last open-ended range like 250 and above.
                </small>
              </div>
              <div class="token-range-help">
                <h6>Easy Example</h6>
                <p class="text-muted" style="margin-bottom:10px;">If you want waiters to earn more tokens as the bill grows, set the rules like this:</p>
                <div class="token-example-box">
                  <div class="token-example-line"><strong>0</strong> to <strong>100</strong> gives <strong>5</strong> tokens</div>
                  <div class="token-example-line"><strong>100.01</strong> to <strong>250</strong> gives <strong>12</strong> tokens</div>
                  <div class="token-example-line"><strong>250.01</strong> to <strong>No Limit</strong> gives <strong>20</strong> tokens</div>
                </div>
                <p class="text-muted" style="margin-top:10px;margin-bottom:6px;"><strong>Tips</strong></p>
                <p class="text-muted" style="margin-bottom:4px;">Do not let ranges overlap.</p>
                <p class="text-muted" style="margin-bottom:4px;">The last row can stay open by leaving the end amount empty.</p>
                <p class="text-muted" style="margin-bottom:0;">In range mode, tokens are awarded once per final bill.</p>
              </div>
            </div>
            <input type="hidden" id="token_brackets" name="token_brackets" value="<?php echo htmlspecialchars($cfg['token_brackets']); ?>" />
          </div>
        </fieldset>
      </div>

      <div class="col-md-5">
        <fieldset class="token-card">
          <legend class="m-b-15">Tracking Snapshot</legend>
          <div class="token-card" style="margin-bottom:0;background:#fcfaff;">
            <div class="token-metric">
              <div class="token-metric-label">Wallets tracked</div>
              <div class="token-metric-value"><?php echo $walletCount; ?></div>
            </div>
            <div class="token-metric">
              <div class="token-metric-label">Token movements</div>
              <div class="token-metric-value"><?php echo $entryCount; ?></div>
            </div>
            <div class="token-metric">
              <div class="token-metric-label">Current mode</div>
              <div class="token-metric-value">
                <?php
                if ((int)$cfg['token_mode'] === 1) {
                  echo 'Pieces sold';
                } elseif ((int)$cfg['token_mode'] === 2) {
                  echo 'Bill ranges';
                } else {
                  echo 'Percentage';
                }
                ?>
              </div>
            </div>
            <p class="text-muted" style="margin:12px 0 0;">
              Token history is stored per waiter and linked back to the sale that generated it. When a bill is returned or voided, the system writes reversal movements instead of silently overwriting the history.
            </p>
          </div>
        </fieldset>
      </div>
    </form>
  </div>
  <div class="panel-footer text-right">
    <button type="button" id="point-submit" class="btn btn-success">Save Token Settings</button>
  </div>
</div>
<script type="text/javascript">
function toggleTokenModeFields() {
  var mode = $('input[name="token_mode"]:checked').val() || '1';
  $('#token-piece-wrap').toggle(mode === '1');
  $('#token-bracket-wrap').toggle(mode === '2');
  $('#token-percent-wrap').toggle(mode === '3');
}

function tokenRangeRowHtml(min, max, token) {
  return '' +
    '<tr class="token-range-row">' +
      '<td><input class="form-control range-min" type="number" step="0.01" min="0" name="range_min[]" value="' + (min || '') + '" /></td>' +
      '<td><input class="form-control range-max" type="number" step="0.01" min="0" name="range_max[]" value="' + (max || '') + '" placeholder="Leave blank for no limit" /></td>' +
      '<td><input class="form-control range-token" type="number" step="0.01" min="0" name="range_token[]" value="' + (token || '') + '" /></td>' +
      '<td class="text-center"><button type="button" class="btn btn-danger btn-xs token-range-remove">Remove</button></td>' +
    '</tr>';
}

function syncTokenRangeRows() {
  var lines = [];
  $('#token-range-body .token-range-row').each(function () {
    var min = $.trim($(this).find('.range-min').val());
    var max = $.trim($(this).find('.range-max').val());
    var token = $.trim($(this).find('.range-token').val());
    if (min === '' && max === '' && token === '') {
      return;
    }
    lines.push(min + ',' + max + ',' + token);
  });
  $('#token_brackets').val(lines.join("\n"));
}

function validateTokenRanges() {
  var rows = [];
  var openEndedCount = 0;
  var ok = true;
  var message = '';

  $('#token-range-body .token-range-row').each(function (index) {
    var minRaw = $.trim($(this).find('.range-min').val());
    var maxRaw = $.trim($(this).find('.range-max').val());
    var tokenRaw = $.trim($(this).find('.range-token').val());

    if (minRaw === '' && maxRaw === '' && tokenRaw === '') {
      return;
    }

    if (minRaw === '' || tokenRaw === '') {
      ok = false;
      message = 'Each token range needs a From Amount and Tokens value.';
      return false;
    }

    var min = parseFloat(minRaw);
    var max = maxRaw === '' ? null : parseFloat(maxRaw);
    var token = parseFloat(tokenRaw);

    if (isNaN(min) || min < 0 || isNaN(token) || token < 0) {
      ok = false;
      message = 'Amounts and tokens must be valid positive numbers.';
      return false;
    }

    if (max !== null && (isNaN(max) || max < min)) {
      ok = false;
      message = 'Each To Amount must be greater than or equal to the From Amount.';
      return false;
    }

    if (max === null) {
      openEndedCount++;
    }

    rows.push({ min: min, max: max, token: token, row: index + 1 });
  });

  if (!ok) {
    return { ok: false, message: message };
  }

  rows.sort(function (a, b) {
    return a.min - b.min;
  });

  for (var i = 1; i < rows.length; i++) {
    var previous = rows[i - 1];
    var current = rows[i];
    if (previous.max === null || current.min <= previous.max) {
      return { ok: false, message: 'Token ranges should not overlap. Please check the rows around ' + previous.row + ' and ' + current.row + '.' };
    }
  }

  if (openEndedCount > 1) {
    return { ok: false, message: 'Only one range can have no end amount.' };
  }

  syncTokenRangeRows();
  return { ok: true, message: '' };
}

$('input[name="token_mode"]').on('change', toggleTokenModeFields);
toggleTokenModeFields();

$('#add-token-range').on('click', function () {
  $('#token-range-body').append(tokenRangeRowHtml('', '', ''));
});

$(document).on('click', '.token-range-remove', function () {
  if ($('#token-range-body .token-range-row').length <= 1) {
    $(this).closest('tr').find('input').val('');
    syncTokenRangeRows();
    return;
  }
  $(this).closest('tr').remove();
  syncTokenRangeRows();
});

$(document).on('input', '.range-min, .range-max, .range-token', syncTokenRangeRows);
syncTokenRangeRows();

document.getElementById('point-submit').addEventListener('click', function () {
  if (($('input[name="token_mode"]:checked').val() || '1') === '2') {
    var tokenRangeCheck = validateTokenRanges();
    if (!tokenRangeCheck.ok) {
      $('#results').html('<div class="alert alert-danger"><a href="#" class="close" data-dismiss="alert">&times;</a><strong>Warning!</strong> ' + tokenRangeCheck.message + '</div>');
      return;
    }
  }

  var form = $('#token-form');
  var formData = new FormData(form[0]);
  $.ajax({
    type: 'POST',
    url: '../settings/point_set_act.php',
    data: formData,
    contentType: false,
    processData: false
  })
  .done(function(data){
    $('#results').html(data);
  })
  .fail(function(){
    $('#results').html('<div class="alert alert-danger"><a href="#" class="close" data-dismiss="alert">&times;</a><strong>Warning!</strong> Error occurred while saving token settings.</div>');
  });
});
</script>
