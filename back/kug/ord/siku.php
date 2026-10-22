<?php
require_once __DIR__ . '/../../../inc/connection.php';
require_once __DIR__ . '/../../../inc/gen_funcs.php';

$user_id = (int)($_SESSION['mwene'] ?? $_SESSION['user_id'] ?? 0);
$url_po_id = (int)($_GET['po_id'] ?? $_GET['id'] ?? 0);
$po_id = $url_po_id > 0
  ? $url_po_id
  : (int)get_column('kp_thesion', 'trans_id', "user_id='{$user_id}'");

logs("../../","front/ord/",'DAY CHANGE',$po_id,'-','-','MODAL LOAD'); 
?>
<div class="modal-content">
  <div class="modal-header">
    <h4 class="modal-title">CHANGE THE SALES DATE</h4>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
  </div>
  <div class="modal-body"> 
      <label>DATE</label>
      <input type="date" id="day" class="form-control" data-date-format="yyyy-mm-dd"  max="<?php echo  today(); ?>"  name="day"  > 

  </div>
  <div class="modal-footer">
  	<button type="button" class="btn btn-outline-danger btn-lg" data-dismiss="modal">CLOSE</button>
    <button class="btn btn-outline-purple btn-lg"   id="new-day" >CONTINUE</button>
  </div>
</div>
