<?php
session_start();
if(isset($_REQUEST['items'])){
  $items = $_REQUEST['items']; 
  $action="LOADED|-|-|PRODUCT DISCONTINUE MODAL |";
  logs($action,"../../"); 
  $arr = explode(',', $items);
}
?>
<div class="modal-content">
<div class="modal-body">
    <div id="del-results"></div>
    <form id="del-form">
    <div class="alert alert-warning">Are you sure you want to Discontinue - <?php echo count($arr); ?> items. </div>
    <input type="hidden"  class="form-control" value="<?php echo $items; ?>" name="items" id="items">
</form>
</div>
<div class="modal-footer">
        <button type="button" class="btn btn-default btn-sm waves-effect" data-dismiss="modal">NO</button>
        <button type="button" id="btn_delete" class="btn btn-danger btn-sm waves-effect ">YES</button>
      </div>
   
</div>
<script type="text/javascript">
    // Add your custom JS code here
$( document ).ready(function() {
    // Handler for .ready() called.
    document.getElementById('btn_delete').addEventListener('click', del);
        function del(){ 
            var str = $( "#del-form" ).serialize();  
            $('#results').html('');
            $.ajax({
              url: menuApiUrl,
              type: 'POST',                           
              data: str + '&action=discontinue_items',
              dataType: 'json'
            })
            .done(function(res){
              if (res && res.ok) {
                var hulla = new hullabaloo();
                hulla.send(res.message || 'Selected items discontinued.', "success");
                $('#modal').modal('toggle');
                if ($.fn.DataTable.isDataTable('#data-table-combine')) {
                  $('#data-table-combine').DataTable().ajax.reload(null, false);
                }
                return;
              }
              $('#del-results').html('<div class="alert alert-danger"><a href="#" class="close" data-dismiss="alert">&times;</a><strong>Warning!</strong> ' + ((res && res.message) ? res.message : 'Error occured.') + '</div>');
            })
            .fail(function(){
              $('#del-results').html('<div class="alert alert-danger"><a href="#" class="close" data-dismiss="alert">&times;</a><strong>Warning!</strong> Error occured.</div>');
            });
        }
    });
             
    </script>
