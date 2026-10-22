<?php 
include('../inc/connection.php');  
if(isset($_POST['search'])){
    $search = $_POST['search'];
    $query = "SELECT * FROM kp_items WHERE name like'%".$search."%'"; //OR barcode like '%".$search."%'
    $result = mysqli_query($connection,$query);
    while($row = mysqli_fetch_array($result) ){ 
        $response[] = array("value"=>$row['item_id'],"label"=>""." ".$row['name']);
    }
    echo json_encode($response);
} 
?> 