<?php

include("../../inc/connection.php");
 if(isset($_REQUEST['id']))
    {
        $id = $_REQUEST['id'];
        //echo $id;

        $get_item_details = mysqli_query($connection,"SELECT * FROM kp_sub_cat WHERE sub_id='$id'") or die("Could not get the product details");
        $row = mysqli_fetch_assoc($get_item_details);
          
    }
?>
<div class="modal-content">
   		<div class="modal-header"> 
   			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
   			<h4 class="modal-title"> <i class="fa fa-stats"></i> &nbsp;&nbsp; Edit <?php echo $row['name']; ?> Details</h4> 
   		</div>
   		<div class="modal-body">
   			<div id="results2"></div>
   			<form action="#" method="#" role="form"> 
                 <div class="form-group col-md-6">
                    <label >Item Name</label>
                    <input type="text" class="form-control"  id="name" value="<?php echo $row['name']; ?>" name="item_name" placeholder="Item Name" required />
                </div>  
              <div class="form-group  col-md-6">
              <label>Category:</label> 
                 <select name="category" id="category" class="form-control">
                    <option value="0">---SELECT CATEGORY----</option>
                    <?php
                    $categories = "SELECT * FROM kp_cat";
                    $categories_r = mysqli_query($connection,$categories) or die("Error getting categories");
                    while ($cat = mysqli_fetch_assoc($categories_r)) {
                        $category = $cat['name'];
                        $cat_id = $cat['cat_id'];
                        ?>
                        <option value="<?php echo $cat_id; ?>" <?php if ($cat_id==$row['cat_id']) { echo "selected"; } ?>><?php echo $category; ?></option>
                        <?php
                    }
                    ?>
                 </select>
              </div>
              <div class="form-group  col-md-6">
          <label>Department</label>
          <select class="form-control" id="dpt" name="dpt" data-parsley-required="true">
            <option value="0" >----SELECT DEPARTMENT----------</option>
                  <option value="1" <?php if ($row['dpt']==1) { echo "selected";} ?>>BAR</option>
                  <option value="1" <?php if ($row['dpt']==2) { echo "selected"; } ?>>KITCHEN</option>
          </select>
      </div>
           </div>
					
							<div class="modal-footer">
								<a href="javascript:;" class="btn btn-sm btn-danger" data-dismiss="modal">Close</a>
								<button type="button"  id="edit-sub" class="btn btn-success">Save</button>
							</div>
					 </form>
   		</div>
   	</div> 

<script type="text/javascript">                       

// Add your custom JS code here
$( document ).ready(function() {
    // Handler for .ready() called.
    document.getElementById('edit-sub').addEventListener('click', edit_sub);
        function edit_sub()
        {
            // $('#prods-edit').text('');
            // $('#prods-edit').append('<span> Please wait... </span>');
            // $('#staff-edit').attr('disabled',true);

            var name = $("#name").val(); 
            var sub_id = '<?php echo $id; ?>';
            var cat_id= $("#category").val(); 
            var dpt = $("#dpt").val();
            $.ajax(
                {
                    url: 'sub_edit_act.php',
                    typr: 'POST',                           
                    data: 'name='+name+'&cat_id='+cat_id+'&sub_id='+sub_id+'&dpt='+dpt,
                    dataType: 'html'
                })

                .done(function(data)
                 {
                    $('#results2').html(''); // blank before load.
                    $('#results2').html(data);

                })

                .fail(function()
                {
                    $('#results2').html('<div class="alert alert-danger"><a href="#" class="close" data-dismiss="alert">&times;</a><strong>Warning!</strong> Error occured Try Again.</div>');
                    
                });
        }

    });
                    </script>
					


		<!--modal add-->
		
