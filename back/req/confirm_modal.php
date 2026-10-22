<?php 
session_start();
include('../../inc/connection.php');  
include('funcs.php');
$user_id = $_SESSION['user_id'];
$get_details = mysqli_query($connection,"SELECT * FROM kp_thesion WHERE user_id='$user_id'") or die("Could not get the user permissions");
$row=mysqli_fetch_assoc($get_details);
$req_id = $row['req_id'];
if ($req_id==0) {
    header("Location: init_page.php");
}else{   
    $action="LOADED CONFIRM MODAL FOR QUOTE NO | ".$req_id;
    $url="../../";
    logs($action,$url);
    
    $no=0; 
}
?>
<div class="modal-content" style="width:600px;">
    <div id="req-results"></div>
	<div class="modal-header"> 
        <h5 class="modal-title">CONFIRM RAISING REQUEST NO <?php echo $req_id; ?></h5>
    </div>
    <div class="modal-body"> 
        <table class="table table-bordered" id="TheSo">
            <thead>
                <tr>
                    <th>No:</th> 
                    <th>Item Name</th>
                    <th>Quantity</th>                 
                </tr>
            </thead>
            <tbody>
                <?php 
                $requisitions = mysqli_query($connection,"SELECT * FROM kp_req_items WHERE  req_id='$req_id'") or die(mysqli_error($connection));
                while ($row = mysqli_fetch_assoc($requisitions)) {
                    $item_id = $row['item_id']; 
                    $no=$no+1;  
                    ?>
                <tr>
                    <td><?php echo $no; ?></td> 
                    <td><?php echo get_item($item_id); ?></td>
                    <td><?php echo $row['req_qty']; ?></td>   
                     
                </tr>
                <?php
                 }
                ?>
            </tbody> 
        </table>
        
    </div> 
    <div class="modal-footer" > 
        <div class="radio col-md-12"> 
            <button type="button" class="btn btn-danger btn-xs" data-dismiss="modal" >Close</button> 
            <button name="complete" class="btn btn-purple btn-xs" id="complete m-l-10" onclick="place_req();">Confirm Request</button> 
        </div> 
    </div> 
    </div>
</div>
<script type="text/javascript">
    function place_req(){ 
        $.ajax({
            url: 'req_place.php',
            type: 'POST',
            data: {},
            dataType: 'html'
        })
        .done(function(data){
            console.log(data);
            $('#req-results').html(''); // blank before load.
            $('#req-results').html(data); // blank before load.
        })
        .fail(function(){
            $('#req-results').html(''); // blank before load.
            $('#req-results').html('<i class="fa  fa-info-sign"></i> Something went wrong, Please try again...');
        });
    }
</script>