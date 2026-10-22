<?php
session_start();
include("../../inc/connection.php");
include("funcs.php");
$user_id = $_SESSION['user_id'];
$username = get_column('kp_users', 'username', "user_id='$user_id'");
?>
<style type="text/css">
  .cpass-modal {
    width: min(100%, 420px);
    margin: 18px auto;
    border: 0;
    border-radius: 24px;
    overflow: hidden;
    background: #fff9f1;
    box-shadow: 0 24px 48px rgba(73, 67, 25, 0.22);
  }
  .cpass-head {
    padding: 18px 18px 16px;
    background: linear-gradient(135deg, #2f7a31 0%, #61b846 46%, #f36c26 100%);
    color: #fffaf2;
  }
  .cpass-head-top {
    display: flex;
    justify-content: space-between;
    gap: 12px;
    align-items: flex-start;
  }
  .cpass-head h3 {
    margin: 0;
    font-size: 19px;
    font-weight: 900;
    letter-spacing: 0.04em;
    text-transform: uppercase;
  }
  .cpass-head p {
    margin: 6px 0 0;
    font-size: 13px;
    font-weight: 700;
    opacity: 0.95;
  }
  .cpass-close {
    border: 0;
    background: rgba(255,255,255,0.16);
    color: #fff;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    font-size: 24px;
    line-height: 1;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    box-shadow: inset 0 0 0 1px rgba(255,255,255,0.2);
  }
  .cpass-body {
    padding: 16px 16px 12px;
    background:
      radial-gradient(circle at top right, rgba(97, 184, 70, 0.12), transparent 26%),
      linear-gradient(180deg, #fffdf8 0%, #fff7ec 100%);
  }
  .cpass-results {
    margin-bottom: 10px;
  }
  .cpass-results .alert {
    margin-bottom: 0;
    border-radius: 16px;
    border: 0;
    font-weight: 800;
  }
  .cpass-results .alert-danger {
    background: rgba(243, 108, 38, 0.14);
    color: #9a3e12;
  }
  .cpass-results .alert-info,
  .cpass-results .alert-success {
    background: rgba(97, 184, 70, 0.14);
    color: #205e24;
  }
  .cpass-field {
    margin-bottom: 14px;
  }
  .cpass-field label {
    display: block;
    margin-bottom: 8px;
    color: #2f7a31;
    font-size: 12px;
    font-weight: 900;
    letter-spacing: 0.06em;
    text-transform: uppercase;
  }
  .cpass-input {
    width: 100%;
    height: 68px;
    border-radius: 18px;
    border: 2px solid rgba(97, 184, 70, 0.28);
    background: #fff;
    color: #2f2418;
    font-size: 34px;
    font-weight: 900;
    text-align: center;
    letter-spacing: 0.18em;
    box-shadow: inset 0 1px 0 rgba(255,255,255,0.9), 0 10px 20px rgba(73, 67, 25, 0.08);
  }
  .cpass-input:focus {
    outline: none;
    border-color: #61b846;
    box-shadow: 0 0 0 4px rgba(97, 184, 70, 0.14);
  }
  .cpass-foot {
    display: grid;
    grid-template-columns: 1fr 1.4fr;
    gap: 10px;
    padding: 0 16px 16px;
    background: #fff7ec;
  }
  .cpass-btn {
    min-height: 56px;
    border-radius: 16px;
    border: 0;
    font-size: 14px;
    font-weight: 900;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    box-shadow: 0 12px 20px rgba(73, 67, 25, 0.12);
  }
  .cpass-btn-close {
    background: #ffffff;
    color: #b94d19;
    box-shadow: inset 0 0 0 2px rgba(243, 108, 38, 0.18);
  }
  .cpass-btn-save {
    background: linear-gradient(135deg, #2f7a31 0%, #61b846 46%, #f36c26 100%);
    color: #fffef9;
  }
  .cpass-modal .numpad-wrap {
    margin: 0;
  }
  .cpass-modal .numpad-wrap .num-pad {
    gap: 10px;
    padding: 0;
  }
  .cpass-modal .numpad-wrap .num,
  .cpass-modal .numpad-wrap .btn {
    min-height: 64px !important;
    height: 64px !important;
    border-radius: 16px !important;
    border: 2px solid rgba(97, 184, 70, 0.24) !important;
    background: #ffffff !important;
    color: #2f7a31 !important;
    box-shadow: inset 0 1px 0 rgba(255,255,255,0.92), 0 10px 18px rgba(73, 67, 25, 0.08);
  }
  .cpass-modal .numpad-wrap .number {
    font-size: 26px !important;
    color: #2f7a31;
  }
  .cpass-modal .numpad-wrap .numpad-action {
    background: #e9f6df !important;
    color: #2f7a31 !important;
  }
  .cpass-modal .numpad-wrap .numpad-clear {
    background: #fff1e7 !important;
    border-color: rgba(243, 108, 38, 0.28) !important;
    color: #b94d19 !important;
  }
  @media (max-width: 575px) {
    .cpass-modal {
      width: calc(100% - 16px);
      margin: 8px auto;
      border-radius: 20px;
    }
    .cpass-head {
      padding: 16px 14px 14px;
    }
    .cpass-head h3 {
      font-size: 16px;
    }
    .cpass-head p {
      font-size: 12px;
    }
    .cpass-close {
      width: 36px;
      height: 36px;
      font-size: 22px;
    }
    .cpass-body {
      padding: 12px 12px 10px;
    }
    .cpass-input {
      height: 60px;
      font-size: 28px;
      border-radius: 16px;
    }
    .cpass-foot {
      grid-template-columns: 1fr;
      padding: 0 12px 12px;
    }
    .cpass-btn {
      min-height: 52px;
      border-radius: 14px;
      font-size: 13px;
    }
    .cpass-modal .numpad-wrap .num-pad {
      gap: 8px;
    }
    .cpass-modal .numpad-wrap .num,
    .cpass-modal .numpad-wrap .btn {
      min-height: 58px !important;
      height: 58px !important;
      border-radius: 14px !important;
    }
    .cpass-modal .numpad-wrap .number {
      font-size: 22px !important;
    }
  }
</style>
<div class="modal-content cpass-modal">
  <div class="cpass-head">
    <div class="cpass-head-top">
      <div>
        <h3>Change Your Pass</h3>
        <p><?php echo htmlspecialchars($username); ?></p>
      </div>
      <button type="button" class="cpass-close" data-dismiss="modal" aria-label="Close">×</button>
    </div>
  </div>
  <div class="cpass-body">
    <div id="results" class="cpass-results"></div>
    <div class="cpass-field">
      <label for="pass">New Pin</label>
      <input type="password" name="pass" id="pass" class="cpass-input" inputmode="numeric" autocomplete="off" autofocus />
    </div>
    <?php include("../clude/numpad.php"); ?>
  </div>
  <div class="cpass-foot">
    <button type="button" class="cpass-btn cpass-btn-close" data-dismiss="modal">Close</button>
    <button type="button" class="cpass-btn cpass-btn-save" onclick="cpass()">Change Pass</button>
  </div>
</div>
<script type="text/javascript">
  function cpass() {
    $("#results").html('');
    var pass = $("#pass").val();
    $.post("../staff/cpassact.php", { pass: pass }, function(data) {
      $("#results").html(data);
    });
  }

  $(document).ready(function(){
    $('.num').click(function () {
      var num = $(this);
      var text = $.trim(num.find('.txt').clone().children().remove().end().text());
      var pass = $('#pass');
      $(pass).val(pass.val() + text).trigger('input');
    });

    $('#clear').click(function () {
      $('#pass').val('').trigger('input');
    });
  });
</script>
