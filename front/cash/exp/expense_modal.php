<?php
session_start();
include('../../../inc/connection.php');
include('../funcs.php');

$user_id = $_SESSION['user_id'];
$shift = get_column('kp_shift','shift',"1");
if ($shift == 0) {
  $shift = date('Y-m-d');
}
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
?>
<style type="text/css">
  .cash-expense-shell .modal-content {
    width: 100%;
    max-width: 460px;
    margin: 0 auto;
    border-radius: 18px;
    overflow: hidden;
    border: 0;
    box-shadow: 0 20px 40px rgba(33, 19, 6, 0.24);
  }
  .cash-expense-shell .modal-header {
    border-bottom: 0;
    padding: 14px 16px;
    background: linear-gradient(135deg, #7e2014 0%, #b94828 52%, #e7a33a 100%);
    color: #fff8ea;
  }
  .cash-expense-shell .modal-title {
    margin: 0;
    font-weight: 900;
    text-transform: uppercase;
  }
  .cash-expense-shell .close {
    color: #fff;
    opacity: 1;
  }
  .cash-expense-shell .modal-body {
    padding: 16px;
    background: linear-gradient(180deg, #fff9ea 0%, #f7edd2 100%);
  }
  .cash-expense-shell .form-control {
    height: 44px;
    border-radius: 10px;
    margin-bottom: 10px;
  }
  .cash-expense-shell textarea.form-control {
    height: auto;
    min-height: 88px;
  }
  .cash-expense-note {
    margin: 0 0 12px;
    padding: 10px 12px;
    border-radius: 12px;
    background: #fff;
    color: #7e3a20;
    font-size: 12px;
    font-weight: 800;
    text-transform: uppercase;
  }
  .cash-expense-shell .modal-footer {
    border-top: 0;
    background: #fffdf8;
    padding: 12px 14px 14px;
    display: flex;
    gap: 10px;
  }
  .cash-expense-btn {
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
  .cash-expense-cancel {
    background: #ead6d6;
    color: #7e2014;
  }
  .cash-expense-save {
    background: linear-gradient(135deg, #355f16 0%, #58a03f 100%);
    color: #fff;
  }
</style>
<div class="cash-expense-shell">
  <div class="modal-content">
    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      <h4 class="modal-title">Shop Expense</h4>
    </div>
    <div class="modal-body">
      <p class="cash-expense-note">Track today's shop expense for shift <?php echo $shift; ?></p>
      <div id="expense-results"></div>
      <form id="expense-form">
        <input type="hidden" name="shift_date" value="<?php echo $shift; ?>">
        <input type="text" name="title" class="form-control" placeholder="Expense title" required>
        <input type="number" step="0.01" min="0" name="amount" class="form-control" placeholder="Amount" required>
        <textarea name="note" class="form-control" placeholder="Short note"></textarea>
      </form>
    </div>
    <div class="modal-footer">
      <button type="button" class="cash-expense-btn cash-expense-cancel" data-dismiss="modal">Close</button>
      <button type="button" class="cash-expense-btn cash-expense-save" id="save-expense">Save Expense</button>
    </div>
  </div>
</div>
<script type="text/javascript">
  $(document).off('click.cashExpenseSave').on('click.cashExpenseSave', '#save-expense', function(e){
    e.preventDefault();
    $.ajax({
      url: 'exp/expense_save.php',
      type: 'POST',
      data: $('#expense-form').serialize(),
      dataType: 'html'
    })
    .done(function(data){
      $('#expense-results').html(data);
      if (data.indexOf('expense-saved-ok') !== -1) {
        if (typeof loadExpensesPanel === 'function') {
          loadExpensesPanel();
        }
        setTimeout(function(){
          $('#modal').modal('hide');
        }, 600);
      }
    })
    .fail(function(){
      $('#expense-results').html('<div class="alert alert-danger">Could not save expense. Please try again.</div>');
    });
  });
</script>
