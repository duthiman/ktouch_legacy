<?php 
session_start();
include('../../inc/connection.php'); 
include('../clude/funcs.php');

$user_id = $_SESSION['user_id'];
$get_details = mysqli_query($connection,"SELECT * FROM kp_thesion WHERE user_id='$user_id'") or die("Could not get the user permissions");
$row=mysqli_fetch_assoc($get_details); 
$req_id = $row['req_id']; 
$no=0; 
?>
 <table class="table table-bordered" id="TheSo">
    <thead>
        <tr>
           <th>No:</th> 
            <th>Item Name</th>
            <th>Quantity</th>                        
            <div  id="hidetd"><th>Action</th></div>
        </tr>
    </thead>
    <tbody>
        <?php 
            $requisitions = mysqli_query($connection,"SELECT * FROM kp_req_items WHERE  req_id='$req_id' ORDER BY ID DESC") or die(mysqli_error($connection));
            while ($row = mysqli_fetch_assoc($requisitions)) {
                $item_id = $row['item_id']; 
                $no=$no+1; 
        ?>
        <tr>
            <td><?php echo $no; ?></td>   
            <td><?php echo get_item($item_id); ?></td>
            <td><?php echo $row['req_qty']; ?></td> 
             <div id="hidetd"><td >
              <a class="btn btn-danger btn-xs" onclick="del_this('<?php echo $item_id; ?>')" >x</a>
            </td></div>
        </tr>
    </tbody>
    <?php
     }
    ?> 
</table>