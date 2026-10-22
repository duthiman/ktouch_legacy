<?php  
include('funcs.php');
$user_id = $_SESSION['mwene']; 
$po_id = $_REQUEST['id'];  
$action="LOADED CONFIRM APPROVAL MODAL FOR PURCHASE ORDER";
$url="../../";
logs($url,'back/kugura/','PURCHASE',$po_id,'-','-',$action);
logs("",'back/kugura/','PURCHASE',$po_id,'-','-',$action); 
$no=0;
$url = "../../";    

?>
<div class="modal-content">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">CONFIRM APPROVAL OF  PURCHASE ORDER <?php echo $po_id; ?></h4>
    </div>
    <div class="modal-body"> 
		<div class="row">
			<div class="alert alert-warning col-md-12">
				<p>WARNING!! This action will move you to approval page .</p>
			</div> 
        </div>
    </div>
    <div class="modal-footer">
    	<button type="button" class="btn btn-outline-danger btn-md" data-BS-dismiss="modal">CLOSE MODAL</button>
        <a class="btn btn-outline-success btn-md"    onclick="view_this('<?php echo $po_id; ?>')">GO TO CONFIRM</a>
    </div>
</div> 

<script type="text/javascript">
    function view_this(id){ 
    window.location.href = 'kug/app/apppage.php?id='+id;
    }                      
   </script>