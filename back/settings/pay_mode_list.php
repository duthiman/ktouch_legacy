<?php
session_start();
include('../../inc/connection.php');
include('../clude/funcs.php');

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

// Migration: ensure kpesa_profile_id exists
$mc = mysqli_query($connection, "SHOW COLUMNS FROM kp_pay_mode LIKE 'kpesa_profile_id'");
if ($mc && mysqli_num_rows($mc) === 0) {
    mysqli_query($connection, "ALTER TABLE kp_pay_mode ADD COLUMN kpesa_profile_id INT NULL DEFAULT NULL");
}
ensure_pay_mode_default_columns($connection);

// Load KPesa profile names for badge display
$kpesa_names = [];
$kp_r = mysqli_query($connection, "SELECT set_id, profile_name FROM kp_kpesa_settings ORDER BY set_id");
if ($kp_r) while ($kp = mysqli_fetch_assoc($kp_r)) $kpesa_names[$kp['set_id']] = $kp['profile_name'];
?>
<style>
.qty{ border:0;min-width:15%;width:auto;border:1px solid #efefef;text-align:center;border:1px solid #d3d8de;box-shadow:none;font-size:12px;line-height:1.42857143;height:25px;padding:6px 12px; }
.kpesa-badge{display:inline-block;padding:2px 8px;border-radius:10px;background:#d4edfa;color:#0c6291;font-size:11px;font-weight:bold;}
.pay-default-badge{display:inline-block;padding:2px 8px;border-radius:10px;background:#f5ebfb;color:#753f91;font-size:11px;font-weight:bold;margin:2px 4px 2px 0;}
.pay-default-badge.is-cash{background:#e9f7ef;color:#2e7d4a;}
.pay-default-badge.is-mpesa{background:#e6f7f0;color:#1d8a62;}
.pay-default-badge.is-card{background:#e8f0ff;color:#2e5aac;}
.pay-default-badge.is-comp{background:#fff1df;color:#a15a0f;}
</style>
<table class="table" id="users">
  <thead>
    <tr>
      <th>Payment Name</th>
      <th>Default Use</th>
      <th>Bank to Affect</th>
      <th>Button Color</th>
      <th>Has REF</th>
      <th>Give Change</th>
      <th>KPesa Profile</th>
      <th>Creation Time</th>
      <th>Created By</th>
      <th>Action</th>
    </tr>
  </thead>
  <tbody>
    <?php
      $get_order_r = mysqli_query($connection,"SELECT * FROM kp_pay_mode WHERE del_status=2") or die("Could not get the orders");
      while ($o = mysqli_fetch_assoc($get_order_r)) {
        $ref    = $o['has_ref']    == 1 ? 'YES' : ($o['has_ref']    == 2 ? 'NO' : 'ERROR');
        $change = $o['has_change'] == 1 ? 'YES' : ($o['has_change'] == 2 ? 'NO' : 'ERROR');
        $kpid   = $o['kpesa_profile_id'] ?? null;
        $kp_badge = ($kpid && isset($kpesa_names[$kpid]))
            ? '<span class="kpesa-badge"><i class="fa fa-mobile-alt"></i> '.htmlspecialchars($kpesa_names[$kpid]).'</span>'
            : '<span class="text-muted" style="font-size:11px;">—</span>';
        $default_badges = [];
        if ((int)($o['is_default_cash'] ?? 2) === 1) {
          $default_badges[] = '<span class="pay-default-badge is-cash">Cash</span>';
        }
        if ((int)($o['is_default_mpesa'] ?? 2) === 1) {
          $default_badges[] = '<span class="pay-default-badge is-mpesa">M-Pesa</span>';
        }
        if ((int)($o['is_default_card'] ?? 2) === 1) {
          $default_badges[] = '<span class="pay-default-badge is-card">Card</span>';
        }
        if ((int)($o['is_default_complimentary'] ?? 2) === 1) {
          $default_badges[] = '<span class="pay-default-badge is-comp">Complementary</span>';
        }
        $default_badge_html = count($default_badges) > 0
            ? implode('', $default_badges)
            : '<span class="text-muted" style="font-size:11px;">—</span>';
    ?>
    <tr style="color:#373a3c;" id="row<?= $o['pay_id'] ?>">
      <td><?= htmlspecialchars($o['name']) ?></td>
      <td><?= $default_badge_html ?></td>
      <td><?= get_bank($o['bank_id']) ?></td>
      <td><?= htmlspecialchars($o['btn_color']) ?></td>
      <td><?= $ref ?></td>
      <td><?= $change ?></td>
      <td><?= $kp_badge ?></td>
      <td><?= $o['datime'] ?></td>
      <td><?= $o['created_by'] ?></td>
      <td class="with-btn-group" nowrap>
        <div class="btn-group">
          <a href="#" class="btn btn-lime btn-sm width-60">Actions</a>
          <a href="#" class="btn btn-lime btn-sm dropdown-toggle width-30 no-caret" data-toggle="dropdown"><span class="caret"></span></a>
          <ul class="dropdown-menu pull-right" style="color:white;">
            <li><a data-toggle="modal" data-target="#pay-modal" onclick="pay_edits('<?= $o['pay_id'] ?>');">Edit</a></li>
            <li><a data-toggle="modal" data-target="#pay-modal" onclick="pay_dis('<?= $o['pay_id'] ?>');">Disable</a></li>
          </ul>
        </div>
      </td>
    </tr>
    <?php } ?>
  </tbody>
</table>
