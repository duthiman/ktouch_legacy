<?php
session_start();
include('../../inc/connection.php');
include('funcs.php');
$kit_id = $_SESSION['kit'];
$sum=0
?><table class="table table-bordered" id="TheSo">
    <thead>
        <tr>
            <th>Item</th>
            <th>Qty</th>                            
            <div  id="hidetd"><th>Action</th></div>
        </tr>
    </thead>
    <tbody>
    <?php 
    $requisitions = mysqli_query($connection,"SELECT * FROM kp_kit_items WHERE kit_id='$kit_id'") or die(mysqli_error($connection));

    while ($row = mysqli_fetch_assoc($requisitions)) {
     $item_id = $row['item_id']; 
     //$qty = $row['qty']; 

     ?>
<tr>
            <td><?php echo get_item($item_id); ?></td>
            <td><?php echo $row['qty']; ?></td>  
             <div id="hidetd"><td >
              <a href="del_item.php?id=<?php echo $item_id; ?>" style=" margin-bottom:  8px; margin-right: 5px; font-size: 12px;" class="btn btn-danger btn-xs">DEL</a>
            </td></div>
        </tr>
<?php
}


?>
    </tbody> 
    
</table>
