<?php 
include('../../../inc/connection.php');

$shift = isset($_GET['shift']) ? $_GET['shift'] : date('Y-m-d');
mysqli_query($connection,"CREATE TABLE IF NOT EXISTS kp_shop_expenses (
  exp_id INT NOT NULL AUTO_INCREMENT,
  shift_date DATE NOT NULL,
  title VARCHAR(120) NOT NULL,
  amount DECIMAL(10,2) NOT NULL DEFAULT 0,
  note TEXT NULL,
  user_id INT NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (exp_id)
)") or die(mysqli_error($connection));

$expenses = mysqli_query($connection,"SELECT * FROM kp_shop_expenses WHERE shift_date='$shift' ORDER BY exp_id DESC LIMIT 12") or die(mysqli_error($connection));
?>
<style type="text/css">
  .cash-expense-table-wrap {
    max-height: 252px;
    overflow-y: auto;
    border-radius: 14px;
    border: 1px solid rgba(146,110,52,0.12);
    background: #fff;
  }
  .cash-expense-table {
    margin: 0;
  }
  .cash-expense-table th,
  .cash-expense-table td {
    vertical-align: middle !important;
    font-size: 12px;
  }
  .cash-expense-table th {
    position: sticky;
    top: 0;
    z-index: 1;
    background: #fbf4de;
    color: #7e3a20;
    font-weight: 900;
    text-transform: uppercase;
    letter-spacing: 0.05em;
  }
  .cash-expense-amount {
    font-size: 15px;
    font-weight: 900;
    color: #c2410c;
    white-space: nowrap;
  }
  .cash-expense-delete {
    min-height: 30px;
    padding: 0 10px;
    border: 0;
    border-radius: 8px;
    background: #c0392b;
    color: #fff;
    font-size: 11px;
    font-weight: 900;
    text-transform: uppercase;
  }
  .cash-expense-empty {
    padding: 14px;
    border-radius: 14px;
    background: #fff7dc;
    color: #8a6a3a;
    font-size: 13px;
    font-weight: 800;
    text-align: center;
  }
</style>
<?php if (mysqli_num_rows($expenses) < 1) { ?>
  <div class="cash-expense-empty">No shop expenses tracked for this shift yet.</div>
<?php } else { ?>
  <div class="cash-expense-table-wrap">
    <table class="table table-striped cash-expense-table">
      <thead>
        <tr>
          <th>Title</th>
          <th>Amount</th>
          <th>Time</th>
          <th>Note</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($row = mysqli_fetch_assoc($expenses)) { ?>
          <tr>
            <td><strong><?php echo htmlspecialchars($row['title']); ?></strong></td>
            <td><span class="cash-expense-amount"><?php echo rtrim(rtrim(number_format($row['amount'], 2, '.', ''), '0'), '.'); ?></span></td>
            <td><?php echo $row['created_at']; ?></td>
            <td><?php echo !empty($row['note']) ? htmlspecialchars($row['note']) : '-'; ?></td>
            <td><button type="button" class="cash-expense-delete" data-id="<?php echo $row['exp_id']; ?>" data-title="<?php echo htmlspecialchars($row['title'], ENT_QUOTES); ?>">Delete</button></td>
          </tr>
        <?php } ?>
      </tbody>
    </table>
  </div>
<?php } ?>
