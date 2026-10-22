<?php
include("../../inc/connection.php");
include('funcs.php');
$action="LOADED ADD NEW SUPPLIER MODAL| ";
$url="../../";
logs($action,$url); 
?>
<div class="modal-content">
	<div class="modal-header">
		<h4 class="modal-title" style="color: purple;">CREATING NEW SUPPLIER</h4>
	</div>
  <form id="sup_form">
	<div class="modal-body">  
    <div id="results"></div>
    <fieldset >
      <legend style="font-size:18px; color:purple;">SUPLIERS DETAILS</legend>
      <div class="form-group row">
        <div class="form-group col-md-6">
            <label >Supplier Name</label>
            <input type="text" class="form-control form-control-xs" id="sup_name" name="name" placeholder="Item Name" required />
        </div>
        <div class="form-group col-md-6">
            <label >Supplier Phone</label>
            <input type="text" class="form-control form-control-xs" id="sup_phone" name="sup_phone" placeholder="Item Phone" required />
        </div>
        <div class="form-group col-md-6">
            <label >Supplier  Email</label>
            <input type="text" class="form-control form-control-xs" id="sup_email" name="sup_email" placeholder="Item Email" required />
        </div>
        <div class="form-group col-md-6">
            <label >Supplier Location</label>
            <input type="text" class="form-control form-control-xs" id="sup_location" name="sup_location" placeholder="Item Location" required />
        </div> 
        <div class="form-group col-md-8">
          <label>Supplier Address</label> 
          <textarea  class="form-control form-control-xs" name="sup_address" id="sup_address" placeholder="Enter Supplier Address ..." style="text-transform: uppercase" ></textarea>
        </div> 
      </div>
  </fieldset> 
  <fieldset >
    <legend style="font-size:18px; color:purple;">TAX DETAILS</legend>
    <div class="form-group row">
      <div class="form-group col-md-6">
          <label >Supplier Pin</label>
          <input type="text" class="form-control form-control-xs" id="sup_pin" name="sup_pin" placeholder="Item Pin" required />
      </div>
      <div class="form-group col-md-6">
          <label >Supplier Vat</label>
          <input type="text" class="form-control form-control-xs" id="sup_vat" name="sup_vat" placeholder="Item Vat" required />
      </div> 
    </div>
  </fieldset>
  <div class="form-group row">
    <div class="col-md-12">
      <label>Supplier Type</label>
      <select class="form-control form-control-xs" id="sup_type" name="sup_type" required>
        <option value="1">Local Supplier</option>
        <option value="2">International Supplier</option>
      </select>
    </div>
  </div>
  </div>
</form>  
  <div class="modal-footer">
    <a href="javascript:;" class="btn btn-xs btn-danger" data-dismiss="modal">Close</a>
    <button type="submit" name="new_prod" id="new_sup" form="sup_form" class="btn btn-xs btn-success">Save</button>
  </div>
</div> 
