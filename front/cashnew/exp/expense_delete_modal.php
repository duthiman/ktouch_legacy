<?php
session_start();
include(dirname(__DIR__, 3) . '/inc/connection.php');
require_once(dirname(__DIR__, 3) . '/inc/gen_funcs.php');

$user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
$exp_id = isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : 0;

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

$expense_q = mysqli_query($connection,"SELECT * FROM kp_shop_expenses WHERE exp_id='$exp_id' LIMIT 1") or die(mysqli_error($connection));
$expense = mysqli_num_rows($expense_q) > 0 ? mysqli_fetch_assoc($expense_q) : null;
?>
<style type="text/css">
  .cash-expense-delete-shell .modal-content {
    width: 100%;
    max-width: 500px;
    margin: 0 auto;
    border-radius: 18px;
    overflow: hidden;
    border: 0;
    box-shadow: 0 20px 40px rgba(33, 19, 6, 0.24);
  }
  .cash-expense-delete-shell .modal-header {
    border-bottom: 0;
    padding: 14px 16px;
    background: linear-gradient(135deg, #8a1f1f 0%, #b94828 56%, #efc15d 100%);
    color: #fff8ea;
  }
  .cash-expense-delete-shell .modal-title {
    margin: 0;
    font-weight: 900;
    text-transform: uppercase;
  }
  .cash-expense-delete-shell .close {
    color: #fff;
    opacity: 1;
  }
  .cash-expense-delete-shell .modal-body {
    padding: 16px;
    background: linear-gradient(180deg, #fff9ea 0%, #f7edd2 100%);
  }
  .cash-expense-delete-shell .modal-footer {
    border-top: 0;
    background: #fffdf8;
    padding: 12px 14px 14px;
    display: flex;
    gap: 10px;
  }
  .cash-expense-delete-card {
    margin-bottom: 14px;
    padding: 14px;
    border-radius: 14px;
    background: #fff;
    border: 1px solid rgba(146,110,52,0.12);
  }
  .cash-expense-delete-card strong,
  .cash-expense-delete-card span {
    display: block;
  }
  .cash-expense-delete-card strong {
    margin-bottom: 4px;
    color: #7e2014;
    font-size: 12px;
    font-weight: 900;
    text-transform: uppercase;
    letter-spacing: 0.06em;
  }
  .cash-expense-delete-card span {
    color: #332411;
    font-size: 14px;
    font-weight: 800;
  }
  .cash-expense-delete-shell .form-control {
    height: 44px;
    border-radius: 10px;
  }
  .cash-expense-delete-btn {
    min-height: 46px;
    border: 0;
    border-radius: 12px;
    font-size: 13px;
    font-weight: 900;
    text-transform: uppercase;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    text-decoration: none !important;
    flex: 1 1 0;
  }
  .cash-expense-delete-close {
    background: #ead6d6;
    color: #7e2014;
  }
  .cash-expense-delete-approve {
    background: linear-gradient(135deg, #7e2014 0%, #c45a28 100%);
    color: #fff;
  }
</style>
<div class="cash-expense-delete-shell">
  <div class="modal-content">
    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      <h4 class="modal-title">Delete Expense Approval</h4>
    </div>
    <div class="modal-body">
      <div id="expense-delete-results"></div>
      <?php if (!$expense) { ?>
        <div class="alert alert-warning">That expense could not be found. Refresh the page and try again.</div>
      <?php } else { ?>
        <div class="cash-expense-delete-card">
          <strong>Expense</strong>
          <span><?php echo htmlspecialchars($expense['title']); ?></span>
        </div>
        <div class="cash-expense-delete-card">
          <strong>Amount</strong>
          <span><?php echo number_format((float)$expense['amount'], 2); ?></span>
        </div>
        <div class="cash-expense-delete-card">
          <strong>Tracked By</strong>
          <span><?php echo get_column('kp_users','username',"user_id='".(int)$expense['user_id']."'"); ?></span>
        </div>
        <form id="expense-delete-form">
          <input type="hidden" name="id" value="<?php echo $exp_id; ?>">
          <input type="password" name="pass" class="form-control" placeholder="Enter supervisor password">
          <textarea name="reason" class="form-control" placeholder="Reason for deleting this expense" style="margin-top: 10px; min-height: 96px; border-radius: 10px; resize: vertical;"></textarea>
        </form>
      <?php } ?>
    </div>
    <div class="modal-footer">
      <button type="button" class="cash-expense-delete-btn cash-expense-delete-close" data-dismiss="modal">Close</button>
      <?php if ($expense) { ?>
        <button type="button" class="cash-expense-delete-btn cash-expense-delete-approve" id="approve-expense-delete">Approve Delete</button>
      <?php } ?>
    </div>
  </div>
</div>
<script type="text/javascript">
  $(document).off('click.cashExpenseDeleteApprove').on('click.cashExpenseDeleteApprove', '#approve-expense-delete', function(e){
    e.preventDefault();
    $.ajax({
      url: 'API/cash_api.php',
      type: 'POST',
      data: $('#expense-delete-form').serialize() + '&action=expense_delete',
      dataType: 'html'
    })
    .done(function(data){
      $('#expense-delete-results').html(data);
      if (data.indexOf('expense-delete-ok') !== -1) {
        if (typeof loadExpensesPanel === 'function') {
          loadExpensesPanel();
        }
        setTimeout(function(){
          $('#modal').modal('hide');
        }, 700);
      }
    })
    .fail(function(){
      $('#expense-delete-results').html('<div class="alert alert-danger">Could not process the approval. Please try again.</div>');
    });
  });
</script>
