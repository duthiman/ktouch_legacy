<?php

include("../../inc/connection.php");
 if(isset($_REQUEST['id']))
	{
        $id = $_REQUEST['id'];
        $get_details = mysqli_query($connection,"SELECT * FROM kp_sub_cat WHERE sub_id='$id'") or die("Could not get the customer details");
        $row = mysqli_fetch_assoc($get_details);
    }
?>
<div class="modal-content">
<div class="modal-body">
    <div id="results3"></div>
    <div class="alert alert-warning">Confirm Delete - <?php echo $row['name']; ?>.</div>
    <input type="hidden" class="form-control" id="sub_id" value="<?php echo $id; ?>" />

     <div class="form-group  col-md-12">
          <label>Select Sub Category to Assign All items assigned to this <?php echo $row['name']; ?></label>
          <select class="form-control" id="sub-cat" name="sub-cat" onchange="sub();"> 
            <option  value="0">--------SELECT SUB CATEGORY-------</option>     <?php
          $sub_cat = "SELECT * FROM kp_sub_cat ORDER BY name ASC";
          $sub_cat_r = mysqli_query($connection,$sub_cat) or die("Error getting sub categories");
          while ($ct = mysqli_fetch_assoc($sub_cat_r)) {
              $name = $ct['name'];
              $sub_id = $ct['sub_id'];
              ?>
              <option  value="<?php echo $sub_id; ?>"><?php echo $name; ?></option>
              <?php
          }
          ?>
          </select>
      </div>
</div>
<div class="modal-footer">
        <button type="button" class="btn btn-default waves-effect" data-dismiss="modal">Close</button>
        <button type="button" id="btn_delete" class="btn btn-danger waves-effect " disabled="true">DELETE</button>
      </div>
   
</div>
<script type="text/javascript">
   function sub(){
    var sub = $("#sub-cat").val();
  var sub_id = document.getElementById("sub_id").value;
  if(sub>0 && !(sub==sub_id)){  
    $('#btn_delete').attr('disabled',false);
  }else{ 
    $('#btn_delete').attr('disabled',true);
  }
}
$( document ).ready(function() {
    // Handler for .ready() called.
    document.getElementById('btn_delete').addEventListener('click', Delete);
        function Delete()
        {
            $('#btn_delete').text('');
            $('#btn_delete').append('<span> Processing... </span>');
            $('#btn_delete').attr('disabled',true);

            var sub_id = $("#sub_id").val();
            var sub = $("#sub-cat").val();
            $.ajax(
                {
                    url: 'sub_del_act.php',
                    typr: 'POST',                           
                    data: 'sub_id='+sub_id+'&sub='+sub,
                    dataType: 'html'
                })

                .done(function(data)
                 {
                    $('#results3').html(''); // blank before load.
                    $('#results3').html(data);

                })

                .fail(function()
                {
                    $('#results3').html('<div class="alert alert-danger"><a href="#" class="close" data-dismiss="alert">&times;</a><strong>Warning!</strong> Error occured Try Again.</div>');
                    
                });
        }

    });
             
    </script>
