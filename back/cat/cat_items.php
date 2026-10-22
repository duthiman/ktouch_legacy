<?php 
include ('../../inc/connection.php');
include('../clude/funcs.php');
$cat_id = mysqli_real_escape_string($connection,$_GET['id']); 
?>
<div style="overflow-y:scroll; height: 700px; " class="panel-body">
<div class="modal-content">
      <div class="modal-header"> 
           <h4 class="modal-title"><?php echo get_cat($cat_id); ?>  Items </h4> 
        </div> 
        <div class="modal-body">
            <div class="table-responsive">
            <table class="table table-valign-middle m-b-0" id="TheSo">
                <thead>
                <tr>
                    <th>Raw Material</th>
                    
                </tr>
                </thead>
                <tbody>
                <?php
                $get_raw_items = mysqli_query($connection,"SELECT * FROM kp_items WHERE cat_id='$cat_id' ") or die("Cound not fetch the raw items");
                while ($row = mysqli_fetch_assoc($get_raw_items)){
                    $name = $row['name'];
                    ?>
                <tr>
                    <td><?php echo $name ?></td> 
                </tr>
                    <?php
                }
                ?>
                </tbody>
            </table> 
           </div>
         </div>
       </div>
 </div>