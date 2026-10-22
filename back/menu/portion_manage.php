<?php
$item_id = isset($item_id) ? (int)$item_id : (int)($_REQUEST['id'] ?? 0);
$item_name = isset($item_name) ? (string)$item_name : '';
?>
<div class="modal-content" >
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
		<h4 class="modal-title">MANAGE PORTINING FOR <?php echo htmlspecialchars($item_name, ENT_QUOTES, 'UTF-8'); ?></h4>
	</div>
	<div class="modal-body"> 
    <div id="results"></div>
     <form class="form-horizontal" id="form">
      <div class="form-group row"> 
        <div class="form-group col-md-12 col-sm-12">
          <fieldset>
            <legend>Product Settings</legend>
            <div class="form-group row">
              <div class="form-group col-md-12 col-sm-12" id="div-port-prod">
                <label>Portions Produced:</label>
                <input class="form-control" type="text" id="portions" name="portions" value="0" placeholder="Required"  />
                <input class="form-control" type="hidden" id="id" name="id" value="<?php echo $item_id; ?>" placeholder="Required"  />
              </div>
               
            </div>
          </fieldset> 
          </div> 
        </div>
      </form>
    </div>
    <div class="modal-footer">
      <a href="javascript:;" class="btn btn-xs btn-danger" data-dismiss="modal">Close</a>
      <button type="button" name="new_prod" id="new_prod" class="btn btn-xs btn-success"  >Save</button>
    </div>
  </div>
</div>
<script> 
  document.getElementById('new_prod').addEventListener('click', portion);
  function portion(){  
    var str = $( "#form" ).serialize();  
    console.log(str); 
    $.ajax({
      url: menuApiUrl,
      type: 'POST',                           
      data: str + '&action=save_portion',
      dataType: 'json'
    })
    .done(function(res){
      if (res && res.ok) {
        var hulla = new hullabaloo();
        hulla.send(res.message || 'Portions Successfully Added', "success");
        $('#modal').modal('toggle');
        if ($.fn.DataTable.isDataTable('#data-table-combine')) {
          $('#data-table-combine').DataTable().ajax.reload(null, false);
        }
        return;
      }
      $('#results').html('<div class="alert alert-danger"><a href="#" class="close" data-dismiss="alert">&times;</a><strong>Warning!</strong> ' + ((res && res.message) ? res.message : 'Error occured.') + '</div>');
    })
    .fail(function(){
      $('#results').html('<div class="alert alert-danger"><a href="#" class="close" data-dismiss="alert">&times;</a><strong>Warning!</strong> Error occured.</div>');
    });
  } 
   $( document ).ready(function() {  
    $(document).on('click','#clear_prod', function(e) { 
      var uid = $(this).data('id'); 
      $.ajax({
        url: menuApiUrl,
        type: 'GET',
        data: { action: 'modal', view: 'clear_confirm', id: uid, act: 'port' },
        dataType: 'json'
      })
      .done(function(res){ 
        $('#menu-load').html(''); 
        $('#menu-load').html((res && res.data && res.data.html) ? res.data.html : '');
      })
      .fail(function(){
        $('#menu-load').html('<i class="fa  fa-info-sign"></i> Something went wrong, Please try again...'); 
      });
    });
  });   
</script>
