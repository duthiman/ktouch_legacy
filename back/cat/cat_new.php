<?php
include("../../inc/connection.php"); 
?>
<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
						<h4 class="modal-title">NEW CATEGORY</h4>
					</div>
					<div class="modal-body"> 
            <div id="cat-new-results"></div>
            <div class="form-group">
                <label >Category Name</label>
                <input type="text" class="form-control" id="cat-new-name" name="name" placeholder="category" required />
            </div>
           <div class="form-group">
          <label>Department</label>
          <select class="form-control" id="cat-new-dpt" name="dpt" data-parsley-required="true">
            <option value="0" >----SELECT DPT----------</option>
                  <option value="1">BAR</option>
                 <option value="2">KITCHEN</option>  <!-- -->
          </select>
      </div>
      </div>
            <div class="modal-footer">
            <a href="javascript:;" class="btn btn-sm btn-danger" data-dismiss="modal">Close</a>
            <button name="new_cat" id="new_cat" class="btn btn-sm btn-success">Save</button>
          </div>
        </div>
      </div>
