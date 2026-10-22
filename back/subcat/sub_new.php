<?php
include("../../inc/connection.php"); 
?>
<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
						<h4 class="modal-title">NEW SUB CATEGOY</h4>
					</div>
					<div class="modal-body"> 
            <div id="sub-new-results"></div> 
            <div class="form-group">
                <label >Category Name</label>
                <input type="text" class="form-control" id="sub-new-name" name="name" placeholder="subcategory" required />
            </div>
           <div class="form-group"> 
                  <label class="">Main Category:</label>
                     <select name="category" id="sub-new-category" class="form-control">
                        <option value="0">---SELECT CATEGORY----</option>
                      <?php
                      $categories = "SELECT * FROM kp_cat";
                      $categories_r = mysqli_query($connection,$categories) or die("Error getting categories");
                      while ($cat = mysqli_fetch_assoc($categories_r)) {
                        $category = $cat['name'];
                        $cat_id = $cat['cat_id'];
                        ?>
                        <option value="<?php echo $cat_id; ?>"><?php echo $category; ?></option>
                        <?php
                      }
                      ?>
                     </select>
                  </div>
          <div class="form-group">
          <label>Department</label>
          <select class="form-control" id="sub-new-dpt" name="dpt" data-parsley-required="true">
            <option value="0" >----SELECT DEPARTMENT----------</option>
                  <option value="1">BAR</option>
                  <option value="2">KITCHEN</option>
          </select>
      </div>
               </div> 
            <div class="modal-footer">
            <a href="javascript:;" class="btn btn-sm btn-danger" data-dismiss="modal">Close</a>
            <button name="new_prod" id="new_sub" class="btn btn-sm btn-success">Save</button>
          </div>
        </div>
       

