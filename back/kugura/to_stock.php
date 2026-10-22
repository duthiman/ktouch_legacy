<?php
session_start();
include("../../inc/connection.php"); 
include("../clude/funcs.php");

// Restrict access to authorized users
$user_id = $_SESSION['user_id'];
page_restrict();

// Fetch user permissions (PO and GRN IDs)
$get_details = mysqli_query($connection, "SELECT * FROM kp_thesion WHERE user_id='$user_id'") or die("Could not get the user permissions");
$row = mysqli_fetch_assoc($get_details);
$po_id = $row['po_id'];  
$grn_id = $row['grn_id']; 

// Get the item ID from the request
$item_id = $_REQUEST['item'];

// Fetch details for the item in the current GRN
$get_po_details = mysqli_query($connection, "SELECT * FROM kp_grn_items WHERE grn_id='$grn_id' AND item_id='$item_id'") or die("Could not get the purchase details ");
$row = mysqli_fetch_assoc($get_po_details);
$qty = $row['qty'];
$price = $row['price'];

?>
<div class="modal-content">
  <div id="result"></div>
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h4 class="modal-title">
      <i class="fa fa-stats"></i> &nbsp;&nbsp;ADD <u><?php echo get_item($item_id); ?></u> TO STOCK FOR P.O NO: <?php echo $po_id; ?> IN GRN NO: <?php echo $grn_id; ?>
    </h4>
  </div>  

  <form id="stock-form">
    <div class="modal-body">
      <input type="hidden" class="form-control" name="po" value="<?php echo $po_id; ?>"/>
      <input type="hidden" class="form-control" name="id" value="<?php echo $item_id; ?>"/>
      <p style="color: red; font-weight: bolder;">REMEMBER!!! YOU CANNOT RECEIVE ZERO, LESS THAN OR GREATER THAN <u style="font-size: 16px;"><?php echo $qty; ?></u></p>

      <div class="row">
        <?php
        // Fetch store IDs where the item is available
        $get_stores = mysqli_query($connection,"SELECT * FROM kp_items WHERE item_id='$item_id'") or die("Could not get any items");
        $row = mysqli_fetch_assoc($get_stores);  
        $store_id = str_replace([' ', '.00'], '', $row['store_id']);
        $arr = explode(',', $store_id);
        $arry = array_unique($arr);

        foreach ($arry as $i) {  
          ?>
          <div class="col-md-10 row">
            <label class="col-md-4"><h4 style="margin-top: 5px;"><?php echo get_store($i); ?>:</h4></label>
            <input type="text" class="form-control col-md-8" id="qty<?php echo $i; ?>" name="qty<?php echo $i; ?>" value="0"/>
          </div> 
        <?php 
        } 
        ?>
      </div>
    </div>


    <div class="modal-footer">
      <button type="button" class="btn btn-default waves-effect" data-dismiss="modal">Close</button> 
      <button type="button" id="btn-confirm" class="btn btn-warning pull-right">Confirm</button>
    </div>
  </form>
</div>

<script>  
  $(document).on('click', '#btn-confirm', function(e){ 
    // Prevent multiple clicks and disable the button
    if ($(this).attr('disabled')) {
      return false;  // If already disabled, prevent further execution
    }

    $('#btn-confirm').text('');
    $('#btn-confirm').append('<span> Please wait... </span>');
    $('#btn-confirm').attr('disabled', true);

    // Serialize form data
    var str = $("#stock-form").serialize();    

    // Send AJAX request to save stock data
    $.ajax({
      url: 'to_stock_act.php',
      type: 'POST',
      data: str,
      dataType: 'html'
    })
    .done(function(data){
      // Update result div with response data
      $('#result').html('');
      $('#result').html(data); 
    })
    .fail(function(){
      // Show error message if AJAX fails
      $('#result').html('<div class="alert alert-danger"><a href="#" class="close" data-dismiss="alert">&times;</a><strong>Warning!</strong> Error occurred.</div>');
      // Re-enable button if there's an error
      $('#btn-confirm').text('Confirm');
      $('#btn-confirm').attr('disabled', false);
    });

    return false;  // Prevent default form submission behavior
  }); 
</script>
