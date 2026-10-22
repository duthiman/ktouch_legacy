<?php
session_start();
include('../../inc/connection.php'); 
include('../cash/funcs.php');
$today = date('Y-m-d');
$user_id = $_SESSION['user_id']; 
$action="LOADED|-|-|-|PRINT MODAL FOR SHIFT SUMMARY";
logs($action,"../../logs");  
logs($action,"logs/");  
?>
<script src="../../assets/js/print.min.js"></script>
<style>
  .cash-summary-modal {
    width: 100%;
    max-width: 920px;
    margin: 0 auto;
  }
  .cash-summary-shell {
    border-radius: 18px;
    overflow: hidden;
    border: 1px solid rgba(108, 78, 31, 0.12);
    box-shadow: 0 20px 40px rgba(0,0,0,0.18);
  }
  .cash-summary-head {
    padding: 14px 16px;
    background: linear-gradient(135deg, #7e2014 0%, #b94828 52%, #e7a33a 100%);
    color: #fff8ea;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
  }
  .cash-summary-head h4 {
    margin: 0;
    font-size: 18px;
    font-weight: 900;
    text-transform: uppercase;
    letter-spacing: 0.05em;
  }
  .cash-summary-body {
    padding: 8px;
    background: #fffdf8;
    max-height: calc(72vh - 72px);
    overflow: auto;
  }
  .cash-summary-foot {
    padding: 12px 14px 14px;
    display: flex;
    gap: 10px;
    justify-content: flex-end;
    background: #fff;
    border-top: 1px solid rgba(108, 78, 31, 0.08);
    position: sticky;
    bottom: 0;
    z-index: 3;
  }
  .cash-summary-btn {
    min-height: 44px;
    min-width: 120px;
    border: 0;
    border-radius: 12px;
    font-size: 13px;
    font-weight: 900;
    text-transform: uppercase;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    text-decoration: none !important;
  }
  .cash-summary-close {
    background: #efe0d3;
    color: #7e2014 !important;
  }
  .cash-summary-print {
    background: linear-gradient(135deg, #22515e 0%, #3e8f9e 100%);
    color: #fff !important;
  }
  @media (max-width: 767.98px) {
    .cash-summary-modal {
      max-width: 100%;
    }
    .cash-summary-shell {
      border-radius: 14px;
    }
    .cash-summary-head {
      padding: 12px 12px;
    }
    .cash-summary-head h4 {
      font-size: 15px;
    }
    .cash-summary-body {
      padding: 6px;
      max-height: calc(68vh - 68px);
    }
    .cash-summary-foot {
      padding: 10px 12px 12px;
      flex-direction: column;
    }
    .cash-summary-btn {
      width: 100%;
      min-width: 0;
    }
  }
</style>
<script type="text/javascript">
  function printShiftReceiptOnly() {
    var receipt = document.getElementById('receipt');
    if (!receipt) {
      return;
    }
    var frame = document.getElementById('shift-summary-print-frame');
    if (!frame) {
      frame = document.createElement('iframe');
      frame.id = 'shift-summary-print-frame';
      frame.style.position = 'fixed';
      frame.style.right = '0';
      frame.style.bottom = '0';
      frame.style.width = '0';
      frame.style.height = '0';
      frame.style.border = '0';
      document.body.appendChild(frame);
    }
    var frameDoc = frame.contentWindow.document;
    var receiptClone = receipt.cloneNode(true);
    var thermalHref = '../assets/css/thermal_print.css';
    var extraStyles = ''
      + '<style>'
      + 'html,body{margin:0 !important;padding:0 !important;background:#fff !important;}'
      + 'body{width:80mm !important;overflow:hidden !important;}'
      + '.modal,.modal-dialog,.modal-content,.cash-summary-shell,.cash-summary-modal,.cash-summary-head,.cash-summary-foot{display:none !important;}'
      + '#receipt{display:block !important;margin:0 auto !important;}'
      + '@page{size:80mm auto;margin:0;}'
      + '</style>';
    frameDoc.open();
    frameDoc.write('<!DOCTYPE html><html><head><title>Shift Summary</title><link rel="stylesheet" href="' + thermalHref + '">' + extraStyles + '</head><body></body></html>');
    frameDoc.close();
    frameDoc.body.innerHTML = '';
    frameDoc.body.appendChild(receiptClone);
    setTimeout(function() {
      frame.contentWindow.focus();
      frame.contentWindow.print();
    }, 250);
  }
</script>
<div class="modal-content cash-summary-shell cash-summary-modal">
  <div id="col-res"></div>
  <div class="cash-summary-head">
    <h4>Shift Summary</h4>
  </div>
  <div class="cash-summary-body">
    <?php include('print_sum.php'); ?>
  </div>
  <div class="cash-summary-foot">
    <button type="button" class="cash-summary-btn cash-summary-close" data-dismiss="modal">Close</button>
    <button type="button" class="cash-summary-btn cash-summary-print" onclick="printShiftReceiptOnly();">Print</button>
  </div>
</div>
