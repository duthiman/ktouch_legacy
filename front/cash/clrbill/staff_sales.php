<?php
session_start();
include('../../../inc/connection.php');
include('../funcs.php');

$db = isset($connection) ? $connection : (isset($conn) ? $conn : null);
$st = isset($_GET['st']) ? mysqli_real_escape_string($db, $_GET['st']) : 2;
$shift = get_column('kp_shift', 'shift', "1");
$user_id = isset($_GET['id']) ? mysqli_real_escape_string($db, $_GET['id']) : 0;
$user_id_int = (int)$user_id;
$user_name = $user_id_int > 0 ? get_user($user_id_int) : 'All Staff';
$max_sale = 0.0;
$current_sale = 0.0;
$usage_percent = 0.0;
$usage_percent_label = '0%';
$usage_class = 'cash-max-progress-green';

if ($user_id_int > 0) {
  $column_check = mysqli_query($db, "SHOW COLUMNS FROM kp_users LIKE 'max_sale'");
  if ($column_check && mysqli_num_rows($column_check) > 0) {
    $max_sale = (float)get_column('kp_users', 'max_sale', "user_id='$user_id_int'");
  }
  $current_sale = (float)column_total('kp_sales', 'amount', "shift='$shift' AND pay_status=2 AND user_id='$user_id_int'");
  if ($max_sale > 0) {
    $usage_percent = min(($current_sale / $max_sale) * 100, 100);
  } elseif ($current_sale > 0) {
    $usage_percent = 100;
  }
  $usage_percent_label = number_format($usage_percent, 1) . '%';
  if ($usage_percent > 90) {
    $usage_class = 'cash-max-progress-red';
  } elseif ($usage_percent >= 70) {
    $usage_class = 'cash-max-progress-orange';
  }
}

if ($st == 1) {
  $sql = "SELECT * FROM kp_sales WHERE shift='$shift' AND pay_status=2 AND user_id='$user_id' ORDER BY sale_id DESC";
  $title = $user_name . " TODAY'S UNPAID BILLS";
} else {
  $sql = "SELECT * FROM kp_sales WHERE shift='$shift' AND pay_status=2 ORDER BY sale_id DESC";
  $title = "ALL STAFF TODAYS UNPAID BILLS";
}

$staff = mysqli_query($db, $sql) or die('Error fetching the staff data.:' . mysqli_error($db));
if (mysqli_num_rows($staff) < 1) {
  echo "There are no staff sales in the system at the moment";
} else {
?>
  <style type="text/css">
    .cash-staff-sales-summary {
      margin: 1px;
      padding: 8px 10px;
      border: 1px solid #eee2d6;
      border-radius: 7px;
      background: linear-gradient(135deg, #fffaf3 0%, #ffffff 100%);
      box-shadow: 0 8px 18px rgba(77, 48, 22, 0.06);
    }
    .cash-staff-sales-summary-head {
      display: flex;
      justify-content: space-between;
      gap: 12px;
      align-items: center;
      margin-bottom: 7px;
      flex-wrap: wrap;
    }
    .cash-staff-sales-summary-head h4 {
      margin: 0 0 3px;
      color: #5b4330;
      font-size: 16px;
      font-weight: 900;
    }
    .cash-staff-sales-summary-head p {
      margin: 0;
      color: #8c7b68;
      font-size: 12px;
    }
    .cash-staff-sales-summary .btn {
      border-radius: 10px;
      font-weight: 800;
    }
    .cash-staff-sales-stats {
      display: grid;
      grid-template-columns: repeat(3, minmax(0, 1fr));
      gap: 10px;
      margin-bottom: 12px;
    }
    .cash-staff-sales-stat {
      border: 1px solid #efe4d7;
      border-radius: 7px;
      background: #fff;
      padding: 8px;
    }
    .cash-staff-sales-stat strong,
    .cash-staff-sales-stat span {
      display: block;
    }
    .cash-staff-sales-stat strong {
      color: #977042;
      font-size: 11px;
      text-transform: uppercase;
      letter-spacing: .05em;
    }
    .cash-staff-sales-stat span {
      margin-top: 5px;
      color: #2e2216;
      font-size: 21px;
      font-weight: 900;
      line-height: 1.1;
    }
    .cash-max-progress-wrap {
      border: 1px solid #efe4d7;
      border-radius: 7px;
      background: #fff;
      padding: 8px;
    }
    .cash-max-progress-meta {
      display: flex;
      justify-content: space-between;
      gap: 10px;
      align-items: center;
      margin-bottom: 8px;
      color: #7d6a58;
      font-size: 12px;
      font-weight: 700;
      flex-wrap: wrap;
    }
    .cash-max-progress-track {
      width: 100%;
      height: 12px;
      border-radius: 999px;
      background: #f1e6d8;
      overflow: hidden;
    }
    .cash-max-progress-bar {
      height: 100%;
      border-radius: 999px;
      transition: width .2s ease;
    }
    .cash-max-progress-green {
      background: linear-gradient(90deg, #1f9d55 0%, #36c275 100%);
    }
    .cash-max-progress-orange {
      background: linear-gradient(90deg, #de8a13 0%, #f0a53a 100%);
    }
    .cash-max-progress-red {
      background: linear-gradient(90deg, #d53032 0%, #f05a5a 100%);
    }
    .cash-staff-select-all { margin:0; display:inline-flex; align-items:center; gap:4px; font-size:11px; cursor:pointer; }
    .cash-staff-select-all input { margin:0; }
    .cash-clearb-work table { margin:1px 0; }
    .cash-clearb-work table th { padding:5px 6px; font-size:10px; }
    .cash-clearb-work table td { padding:5px 6px; font-size:11px; line-height:1.1; }
    .cash-clearb-work table h4 { margin:0; font-size:11px; }
    @media (max-width: 767px) {
      .cash-staff-sales-stats {
        grid-template-columns: 1fr;
      }
    }
  </style>
  <table class="table table-striped">
    <thead>
      <tr>
        <th id="user" data-id='<?php echo $user_id; ?>' data-id2='1' colspan="6" style="font-size: 20px;" class="text-center">
          <?php echo $title; ?>
        </th>
      </tr>
      <tr>
        <th class="text-left"><label class="cash-staff-select-all"><input type="checkbox" id="cash-staff-select-all"> <span>ALL</span></label></th>
        <th><h4>ORDER NO:</h4></th>
        <th><h4>SHIFT</h4></th>
        <th><h4>STAFF</h4></th>
        <th><h4>COUNTER</h4></th>
        <th><h4>AMOUNT</h4></th>
      </tr>
    </thead>
    <?php if ($st == 1 && $user_id_int > 0) { ?>
      <tbody>
        <tr>
          <td colspan="6" style="padding:0;">
            <div class="cash-staff-sales-summary">
              <div class="cash-staff-sales-summary-head">
                <div>
                  <h4><?php echo htmlspecialchars($user_name); ?> Sale Cap</h4>
                  <p>Review the current sale against the user max, then open the modal to increase the cap.</p>
                </div>
                <button
                  type="button"
                  class="btn btn-outline-primary btn-sm"
                  onclick="cashOpenMaxSaleModal(<?php echo $user_id_int; ?>, '<?php echo htmlspecialchars(addslashes($user_name), ENT_QUOTES); ?>')"
                >
                  Increase Max Sale
                </button>
              </div>
              <div class="cash-staff-sales-stats">
                <div class="cash-staff-sales-stat">
                  <strong>Current Sale</strong>
                  <span><?php echo number_format($current_sale, 2); ?></span>
                </div>
                <div class="cash-staff-sales-stat">
                  <strong>Maximum Sale</strong>
                  <span><?php echo number_format($max_sale, 2); ?></span>
                </div>
                <div class="cash-staff-sales-stat">
                  <strong>Cap Used</strong>
                  <span><?php echo $usage_percent_label; ?></span>
                </div>
              </div>
              <div class="cash-max-progress-wrap">
                <div class="cash-max-progress-meta">
                  <span><?php echo number_format($current_sale, 2); ?> of <?php echo number_format($max_sale, 2); ?> used</span>
                  <span>
                    <?php
                      if ($usage_percent > 90) {
                        echo 'Danger Zone';
                      } elseif ($usage_percent >= 70) {
                        echo 'Warning Zone';
                      } else {
                        echo 'Safe Zone';
                      }
                    ?>
                  </span>
                </div>
                <div class="cash-max-progress-track">
                  <div class="cash-max-progress-bar <?php echo $usage_class; ?>" style="width: <?php echo $usage_percent; ?>%;"></div>
                </div>
              </div>
            </div>
          </td>
        </tr>
      </tbody>
    <?php } ?>
    <tbody>
      <?php while ($row = mysqli_fetch_assoc($staff)) {
        $sale_user_id = $row['user_id'];
        $sale_id = $row['sale_id'];
        $sale_shift = $row['shift'];
        $totals = $row['amount'];
        $counter = $row['counter'];
      ?>
        <tr style="font-size:16px; font-weight:bolder;">
          <td class="text-left"><input type="checkbox" name="pay-sale[]" onchange="bulk_pay()" value="<?php echo $sale_id; ?>" class="bulk cash-bill-pick" data-amount="<?php echo (float) $totals; ?>" /></td>
          <td><?php echo $sale_id; ?></td>
          <td><?php echo $sale_shift; ?></td>
          <td><?php echo get_user($sale_user_id); ?></td>
          <td><?php echo get_column('kp_counters', 'name', "cntr_id='$counter'"); ?></td>
          <td><?php echo $totals; ?></td>
        </tr>
      <?php } ?>
    </tbody>
  </table>
  <script type="text/javascript">
    $('#cash-staff-select-all').off('change.cashStaff').on('change.cashStaff', function(){
      $('input.bulk.cash-bill-pick').prop('checked', this.checked).trigger('change');
    });
    function cashOpenMaxSaleModal(userId, userName) {
      window.cashReloadMaxSaleContext = function() {
        if (typeof cashLoadCurrentBills === 'function') {
          cashLoadCurrentBills(userId);
        }
      };
      $('#modal').modal('show');
      $.ajax({
        url: 'clrbill/max_sale_modal.php',
        type: 'GET',
        data: { user_id: userId },
        dataType: 'html'
      })
      .done(function(data){
        $('#load').html(data);
      })
      .fail(function(){
        $('#load').html('<div class="modal-dialog"><div class="modal-content"><div class="modal-body"><div class="alert alert-danger">Could not load max sale for ' + userName + '.</div></div></div></div>');
      });
    }
  </script>
<?php
}
?>
