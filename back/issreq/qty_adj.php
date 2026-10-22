<?php
include("../../inc/connection.php");
include("../../inc/gen_funcs.php");
if(isset($_REQUEST['id'])){
  $id = $_REQUEST['id']; 
  $qty = get_column('kp_items','ware_qty',"item_id='$id'");
  $name=strtoupper(get_column('kp_items','name',"item_id='$id'"));
?>
<div class="modal-content">
  <div id="results"></div>
  <div class="modal-header">
    <h4 class="modal-title"> <strong> ADJUST  <?php echo $name; ?> STOCK</strong></h4> 
  </div>
  <div class="modal-body">
      <div class="form-group">
          <label >Quantity</label>
          <input type="text" class="form-control" name="new-qty" id="new-qty" value="<?php echo $qty; ?>"  />
      </div>
      <div class="form-group">
        <label >Reason</label>
        <textarea name="reason" class="form-control" placeholder="Reason" id="reason"> </textarea>
      </div>  
  </div>
  <div class="modal-footer">
    <a href="javascript:;" class="btn btn-lg btn-outline-danger" data-dismiss="modal">CLOSE MODAL</a>
    <button type="button" name="adj_qty" id="adj_qty" class="btn btn-lg btn-outline-success">ADJUST ITEM QTY</button>
  </div>
</div>

<script type="text/javascript">
document.getElementById('adj_qty').addEventListener('click', adjust);
  function adjust(){
    var init = '<?php echo $qty; ?>';
    var qty = $("#new-qty").val(); 
    var item_id = '<?php echo $id; ?>';
    var reason = $("#reason").val();  
    $.ajax(
        {
          url: 'qty_adj_act.php',
          typr: 'POST',                           
          data: 'init='+init+'&qty='+qty+'&item_id='+item_id+'&reason='+reason,
          dataType: 'html'
        })
        .done(function(data)
         {
            $('#results').html(''); // blank before load.
            $('#results').html(data);
            console.log(data);
        })
        .fail(function()
        {
            $('#results').html('<div class="alert alert-danger"><a href="#" class="close" data-dismiss="alert">&times;</a><strong>Warning!</strong> Error occured.</div>');
            
        });
  }
</script> 
<?php
  }
?>