<?php
session_start();
include('../../inc/connection.php');
include('funcs.php');
$req_id = $_SESSION['req'];
$sum=0;
?>
<style type="text/css">
  .req-live-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
  }
  .req-live-empty,
  .req-live-item {
    background: #fff;
    border: 1px solid rgba(108, 78, 31, 0.08);
    border-radius: 10px;
    box-shadow: 0 8px 18px rgba(86, 61, 24, 0.08);
    padding: 10px 12px;
  }
  .req-live-item {
    display: flex;
    align-items: center;
    gap: 10px;
  }
  .req-live-no {
    width: 32px;
    height: 32px;
    border-radius: 10px;
    background: #f9e7cd;
    color: #7e3a20;
    font-size: 12px;
    font-weight: 900;
    display: flex;
    align-items: center;
    justify-content: center;
    flex: 0 0 auto;
  }
  .req-live-copy {
    flex: 1 1 auto;
    min-width: 0;
  }
  .req-live-copy strong,
  .req-live-copy span {
    display: block;
  }
  .req-live-copy strong {
    font-size: 13px;
    font-weight: 900;
    color: #382819;
    line-height: 1.25;
  }
  .req-live-copy span {
    margin-top: 4px;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 0.08em;
    color: #8a6a3a;
    text-transform: uppercase;
  }
  .req-live-meta {
    text-align: right;
    flex: 0 0 auto;
  }
  .req-live-meta strong,
  .req-live-meta a {
    display: block;
  }
  .req-live-meta strong {
    font-size: 18px;
    font-weight: 900;
    color: #4f7e1e;
    line-height: 1;
  }
  .req-live-remove {
    margin-top: 6px;
    padding: 0;
    border: 0;
    background: transparent;
    color: #b02b2b;
    font-size: 11px;
    font-weight: 900;
    letter-spacing: 0.08em;
    text-transform: uppercase;
  }
</style>
<div class="req-live-list">
  <?php 
  $no=0;
  $requisitions = mysqli_query($connection,"SELECT ri.*, i.name AS item_name
    FROM kp_counter_req_items ri
    LEFT JOIN kp_items i ON i.item_id = ri.item_id
    WHERE ri.req_id='$req_id'
    ORDER BY ri.id DESC") or die(mysqli_error($connection));
  if (mysqli_num_rows($requisitions) < 1) {
    ?>
    <div class="req-live-empty">No requisition items added yet.</div>
    <?php
  }
  while ($row = mysqli_fetch_assoc($requisitions)) {
      $item_id = $row['item_id']; 
      $no=$no+1;
      ?>
      <div class="req-live-item">
          <div class="req-live-no"><?php echo $no; ?></div>
          <div class="req-live-copy">
              <strong><?php echo htmlspecialchars($row['item_name'] ?: get_item($item_id), ENT_QUOTES, 'UTF-8'); ?></strong>
              <span>Requested Qty</span>
          </div>
          <div class="req-live-meta">
              <strong><?php echo $row['req_qty']; ?></strong>
              <button type="button" onclick="del_item('<?php echo $item_id; ?>')" class="req-live-remove">Remove</button>
          </div>
      </div>
      <?php
  }
  ?>
</div>
