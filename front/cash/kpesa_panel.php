<?php
session_start();
include('../../inc/connection.php');
require_once('../../inc/gen_funcs.php');

$unapproved = mysqli_query($connection, "SELECT * FROM kp_split_message WHERE claim_status=1 AND assign_status=2 ORDER BY concat(date,' ',str_to_date(time,'%l:%i %p')) DESC LIMIT 120") or die(mysqli_error($connection));
$unsplit = mysqli_query($connection, "SELECT * FROM mpesa_message WHERE split_status=0 ORDER BY datime DESC LIMIT 120") or die(mysqli_error($connection));
$unapproved_count = mysqli_num_rows(mysqli_query($connection, "SELECT id FROM kp_split_message WHERE claim_status=1 AND assign_status=2"));
$unsplit_count = mysqli_num_rows(mysqli_query($connection, "SELECT id FROM mpesa_message WHERE split_status=0"));
?>
<style type="text/css">
  .kpesa-shell {
    border: 1px solid #eadfce;
    border-radius: 18px;
    background: #fffdf9;
    overflow: hidden;
  }
  .kpesa-head {
    padding: 16px 18px;
    border-bottom: 1px solid #efe2d3;
    background: linear-gradient(135deg, #eef7ff 0%, #fffdfa 100%);
  }
  .kpesa-head h3 {
    margin: 0 0 4px;
    color: #154a73;
    font-weight: 900;
  }
  .kpesa-head p {
    margin: 0;
    color: #64748b;
  }
  .kpesa-body {
    padding: 16px 18px 18px;
  }
  .kpesa-stats {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 10px;
    margin-bottom: 14px;
  }
  .kpesa-stat {
    border: 1px solid #dbe6f2;
    border-radius: 14px;
    padding: 12px;
    background: #fff;
  }
  .kpesa-stat strong,
  .kpesa-stat span {
    display: block;
  }
  .kpesa-stat strong {
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: .05em;
    color: #5d7992;
  }
  .kpesa-stat span {
    margin-top: 6px;
    font-size: 24px;
    font-weight: 900;
    color: #12344d;
  }
  .kpesa-grid {
    display: grid;
    grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
    gap: 14px;
  }
  .kpesa-card {
    border: 1px solid #dbe6f2;
    border-radius: 16px;
    overflow: hidden;
    background: #fff;
  }
  .kpesa-card-head {
    padding: 12px 14px;
    background: #f5faff;
    border-bottom: 1px solid #e3edf7;
  }
  .kpesa-card-head h4 {
    margin: 0 0 3px;
    color: #1e4f72;
    font-size: 15px;
    font-weight: 900;
  }
  .kpesa-card-head p {
    margin: 0;
    color: #6b8194;
    font-size: 12px;
  }
  .kpesa-table-wrap {
    max-height: 520px;
    overflow: auto;
  }
  .kpesa-table {
    margin: 0;
  }
  .kpesa-table th {
    position: sticky;
    top: 0;
    background: #fbfdff;
    z-index: 1;
  }
  .kpesa-empty {
    padding: 18px 14px;
    color: #6a7f90;
  }
  @media (max-width: 991px) {
    .kpesa-grid,
    .kpesa-stats {
      grid-template-columns: 1fr;
    }
  }
</style>
<div class="kpesa-shell">
  <div class="kpesa-head">
    <h3>KPesa Desk</h3>
    <p>Approve claimed split messages and split new M-Pesa messages from one place.</p>
  </div>
  <div class="kpesa-body">
    <div class="kpesa-stats">
      <div class="kpesa-stat"><strong>Split Unapproved</strong><span><?php echo (int)$unapproved_count; ?></span></div>
      <div class="kpesa-stat"><strong>Unsplit Messages</strong><span><?php echo (int)$unsplit_count; ?></span></div>
    </div>
    <div class="kpesa-grid">
      <div class="kpesa-card">
        <div class="kpesa-card-head">
          <h4>Split Unapproved</h4>
          <p>Approve waiter claims that are waiting.</p>
        </div>
        <div class="kpesa-table-wrap">
          <?php if (!$unapproved || mysqli_num_rows($unapproved) < 1) { ?>
            <div class="kpesa-empty">No split approvals are waiting.</div>
          <?php } else { ?>
            <table class="table table-striped kpesa-table">
              <thead>
                <tr>
                  <th>Action</th>
                  <th>Waiter</th>
                  <th>Code</th>
                  <th class="text-right">Amount</th>
                  <th>Time</th>
                  <th>Name</th>
                </tr>
              </thead>
              <tbody>
                <?php while ($pow = mysqli_fetch_assoc($unapproved)) {
                  $claim_staff = (int)$pow['claim_staff'];
                  ?>
                  <tr>
                    <td><button class="btn btn-xs btn-lime" id="approve" data-id="<?php echo $pow['id']; ?>">Approve</button></td>
                    <td><strong><?php echo htmlspecialchars(get_column('kp_users', 'username', "user_id='$claim_staff'")); ?></strong></td>
                    <td><?php echo htmlspecialchars($pow['code']); ?></td>
                    <td class="text-right"><?php echo number_format((float)$pow['amount'], 2); ?></td>
                    <td><?php echo htmlspecialchars($pow['time']); ?></td>
                    <td><?php echo htmlspecialchars($pow['name']); ?></td>
                  </tr>
                <?php } ?>
              </tbody>
            </table>
          <?php } ?>
        </div>
      </div>

      <div class="kpesa-card">
        <div class="kpesa-card-head">
          <h4>Unsplit Messages</h4>
          <p>Split incoming M-Pesa messages and assign them.</p>
        </div>
        <div class="kpesa-table-wrap">
          <?php if (!$unsplit || mysqli_num_rows($unsplit) < 1) { ?>
            <div class="kpesa-empty">No unsplit M-Pesa messages found.</div>
          <?php } else { ?>
            <table class="table table-striped kpesa-table">
              <thead>
                <tr>
                  <th>Message</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <?php while ($pow = mysqli_fetch_assoc($unsplit)) { ?>
                  <tr>
                    <td style="min-width:260px;"><?php echo htmlspecialchars($pow['message']); ?></td>
                    <td><button class="btn btn-sm btn-outline-info" id="split" data-id="<?php echo $pow['id']; ?>">Split Message</button></td>
                  </tr>
                <?php } ?>
              </tbody>
            </table>
          <?php } ?>
        </div>
      </div>
    </div>
  </div>
</div>
