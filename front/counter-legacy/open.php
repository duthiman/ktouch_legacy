<?php
session_start();
include('../../inc/connection.php');
include('funcs.php');
$user_id = (int)($_SESSION['user_id'] ?? 0);
$user_drole = $user_id > 0 ? (int)get_column('kp_users', 'drole', "user_id='$user_id'") : 0;
$counter_scope = function_exists('front_counter_scope_where') ? front_counter_scope_where($user_drole, 'kp_counters') : '1=0';
?>
<style type="text/css">
  .open-counter-shell .modal-content {
    width: 100%;
    max-width: 1040px;
    margin: 0 auto;
    border-radius: 18px;
    overflow: hidden;
    border: 0;
    box-shadow: 0 20px 40px rgba(33, 19, 6, 0.24);
  }
  .open-counter-shell .modal-header {
    border-bottom: 0;
    padding: 14px 16px;
    background: linear-gradient(135deg, #7e2014 0%, #b94828 52%, #e7a33a 100%);
    color: #fff8ea;
  }
  .open-counter-shell .modal-title {
    margin: 0;
    font-size: 20px;
    font-weight: 900;
    text-transform: uppercase;
  }
  .open-counter-shell .close {
    color: #fff;
    opacity: 1;
  }
  .open-counter-shell .modal-body {
    background: linear-gradient(180deg, #fff9ea 0%, #f7edd2 100%);
    padding: 14px;
  }
  .open-counter-grid {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 12px;
  }
  .open-counter-panel {
    border-radius: 14px;
    background: rgba(255,255,255,0.92);
    border: 1px solid rgba(146,110,52,0.12);
    box-shadow: 0 10px 20px rgba(86, 61, 24, 0.08);
    padding: 12px;
    min-height: 260px;
  }
  .open-counter-title {
    margin: 0 0 10px;
    font-size: 13px;
    font-weight: 900;
    letter-spacing: 0.08em;
    color: #7e3a20;
    text-transform: uppercase;
  }
  .open-counter-empty,
  .open-counter-warning {
    border-radius: 12px;
    padding: 12px;
    font-size: 12px;
    font-weight: 800;
    line-height: 1.45;
  }
  .open-counter-warning {
    background: #fff4ce;
    color: #7a5b14;
  }
  .open-counter-empty {
    background: #f7f1df;
    color: #8a6a3a;
    text-align: center;
  }
  .open-counter-list,
  .cashier-list {
    display: grid;
    grid-template-columns: 1fr;
    gap: 8px;
  }
  .open-counter-btn,
  .cashier-btn {
    width: 100%;
    min-height: 58px;
    border: 0;
    border-radius: 12px;
    background: linear-gradient(135deg, #fffdfa 0%, #f0e2b7 100%);
    color: #02550E !important;
    font-size: 13px;
    font-weight: 900;
    text-transform: uppercase;
    text-align: left;
    padding: 10px 12px;
    display: flex;
    align-items: center;
    gap: 10px;
    box-shadow: inset 0 0 0 2px rgba(97,184,70,0.3);
    text-decoration: none !important;
    white-space: normal;
  }
  .open-counter-btn .badge,
  .cashier-btn .badge {
    width: 34px;
    height: 34px;
    border-radius: 10px;
    background: #02550E;
    color: #fff;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: 900;
    flex: 0 0 auto;
  }
  .open-counter-btn .copy,
  .cashier-btn .copy {
    display: block;
    min-width: 0;
    line-height: 1.2;
  }
  .open-counter-table {
    width: 100%;
    margin: 0;
  }
  .open-counter-table th,
  .open-counter-table td {
    font-size: 12px;
    vertical-align: middle !important;
  }
  .open-counter-shell .modal-footer {
    border-top: 0;
    background: #fffdf8;
    padding: 12px 14px 14px;
  }
  .open-counter-close {
    min-height: 44px;
    border: 0;
    border-radius: 12px;
    background: #ead6d6;
    color: #7e2014;
    font-size: 13px;
    font-weight: 900;
    text-transform: uppercase;
    padding: 0 18px;
  }
  @media (max-width: 991px) {
    .open-counter-shell .modal-content {
      max-width: 100%;
    }
    .open-counter-grid {
      grid-template-columns: 1fr;
    }
    .open-counter-panel {
      min-height: 0;
    }
  }
  @media (max-width: 520px) {
    .open-counter-shell .modal-body {
      padding: 10px;
    }
    .open-counter-btn,
    .cashier-btn {
      min-height: 54px;
      font-size: 12px;
      padding: 9px 10px;
    }
    .open-counter-table th,
    .open-counter-table td {
      font-size: 11px;
    }
  }
</style>
<div class="open-counter-shell">
  <div class="modal-content">
    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      <h4 class="modal-title">Open Counter</h4>
    </div>
    <div class="modal-body">
      <div class="open-counter-grid">
        <div class="open-counter-panel">
          <p class="open-counter-title">Available Counters</p>
          <?php
          if (!function_exists('front_role_can_open_counters') || !front_role_can_open_counters($user_drole)) {
            ?>
            <div class="open-counter-warning">You are not allowed to open counters from this account.</div>
            <?php
          } else {
            $get_tables = "SELECT cntr_id,name FROM kp_counters WHERE del_status=2 AND counter_status<>1 AND $counter_scope";
            $get_tables_r = mysqli_query($connection,$get_tables) or die("Could not fetch the tables".mysqli_error($connection));
            if (mysqli_num_rows($get_tables_r) < 1) {
              ?>
              <div class="open-counter-empty">No pending counters are ready to open for your role.</div>
              <?php
            } else {
              ?>
              <div class="open-counter-list">
                <?php
                while ($tab = mysqli_fetch_assoc($get_tables_r)) {
                  $name = $tab['name'];
                  $cntr_id = $tab['cntr_id'];
                  ?>
                  <button type="button" class="open-counter-btn js-open-counter-select" data-id="<?php echo $cntr_id; ?>">
                    <span class="badge"><?php echo $cntr_id; ?></span>
                    <span class="copy"><?php echo $name; ?></span>
                  </button>
                  <?php
                }
                ?>
              </div>
              <?php
            }
          }
          ?>
        </div>
        <div class="open-counter-panel" id="cashier">
          <p class="open-counter-title">Select Cashier</p>
          <div class="open-counter-empty">Choose a counter first to load available cashiers.</div>
        </div>
        <div class="open-counter-panel" id="open_counters">
          <p class="open-counter-title">Open Counters</p>
          <div class="table-responsive">
            <table class="table open-counter-table">
              <thead>
                <tr>
                  <th>Counter</th>
                  <th>Cashier</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                <?php
                $get_tables = "SELECT cntr_id,name,user_id,take_status FROM kp_counters WHERE del_status=2 AND counter_status=1 AND $counter_scope";
                $get_tables_r = mysqli_query($connection,$get_tables) or die("Could not fetch the tables".mysqli_error($connection));
                if (mysqli_num_rows($get_tables_r) < 1) {
                  ?>
                  <tr>
                    <td colspan="3" class="text-center">No open counters.</td>
                  </tr>
                  <?php
                } else {
                  while ($tab = mysqli_fetch_assoc($get_tables_r)) {
                    ?>
                    <tr>
                      <td><?php echo $tab['name']; ?></td>
                      <td><?php echo get_user($tab['user_id']); ?></td>
                      <td>
                        <?php
                        if ($tab['take_status'] == 2) {
                          echo "NOT STARTED";
                        } elseif ($tab['take_status'] == 3) {
                          echo "CONTINUING";
                        } else {
                          echo "TAKE FINISHED";
                        }
                        ?>
                      </td>
                    </tr>
                    <?php
                  }
                }
                ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
    <div class="modal-footer">
      <button type="button" class="open-counter-close" data-dismiss="modal">Close</button>
    </div>
  </div>
</div>

<script type="text/javascript">
  $(document).ready(function(){
    $(document).on('click', '.js-open-counter-select', function(){
      var uid = $(this).data('id');
      $.ajax({
        url: '../counter/cashier.php',
        typr: 'POST',
        data: 'id=' + uid,
        dataType: 'html'
      })
      .done(function(data){
        $('#cashier').html(data);
      })
      .fail(function() {
        $('#cashier').html('<p class="open-counter-empty">Something went wrong. Please try again.</p>');
      });
    });
  });
</script>
