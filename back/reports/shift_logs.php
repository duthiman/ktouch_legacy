<?php 
require("../clude/head.php");
$no=0;
$shift=$_GET['day'];
?>
 
<!-- begin #page-container -->
<div id="page-container" class="fade page-sidebar-fixed page-header-fixed">
  <!-- begin #header -->  
  <?php 
    require("../clude/header.php");
    
  ?>
  <!-- end #header -->  
  <!-- begin #sidebar -->
  <?php 
    require("../clude/menu.php");
  ?>
  <!-- end #sidebar -->
<div id="content" class="content">
		<!-- <div class="row"> -->
               <div class="col-md-12">
                	<div class="panel panel-success" data-sortable-id="index-1">
					<div class="panel-body">
			<table border="1" id="data-table-combine" class="table table-striped table-bordered">
			<thead>
				<tr>
					<th>STAFF</th>
					<th>ACTION</th>
					<th>QTY</th>
					<th>ITEM</th>
					<th>-</th>
					<th>ORDER NO</th>
					<th>TIME</th>
					<th>SHIFT</th>
				</tr>
			</thead>			 
						  <?php
 
$file = fopen("../../front/logs/$shift.txt", "r") or die("Unable to open file!");
while (!feof($file)){   
    $data = fgets($file); 
    list($time, $staff, $action, $qty, $item, $act2, $order_no, $shift) = explode("|", $data);
   
    // echo "<tr><td>" . str_replace('|','</td><td>',$data) . '</td></tr>';
?>
<tr>
        <td><?=$staff ?></td>
        <td><?=$action ?></td>
        <td><?=$qty ?></td>
        <td><?=$item ?></td>
        <td><?=$act2 ?></td>
        <td><?=$order_no ?></td>
        <td><?=$shift ?></td>
        <td><?=$time ?></td>
    </tr>
<?php
} 
fclose($file);


?>
	
</table> 
                    </div>

		<div id="edit_item" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none;">
			<div class="modal-dialog">
				<div id="dynamic-content"></div>
			</div>
		</div>


	</div>
</div>
			<!-- end invoice 
		</div>-->
			
	</div>
	

	<!-- end page container -->
	
<?php  
include("../clude/foot.php");
?>
