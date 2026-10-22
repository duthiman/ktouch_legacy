<?php
include('../../inc/connection.php');
include('../clude/funcs.php');
$shift = isset($_GET['shift']) ? trim($_GET['shift']) : '';
$shift = mysqli_real_escape_string($connection, $shift);
$table="kp_store_stock_take";  
$sales = mysqli_query($connection, "
  SELECT s.*, COALESCE(t.total_out, 0) AS total_out, COALESCE(t.total_in, 0) AS total_in
  FROM {$table} s
  LEFT JOIN (
    SELECT item_id, shift,
      SUM(CASE WHEN status = 'OUT' THEN qty ELSE 0 END) AS total_out,
      SUM(CASE WHEN status = 'IN' THEN qty ELSE 0 END) AS total_in
    FROM kp_store_stock_track
    WHERE shift = '$shift'
    GROUP BY item_id, shift
  ) t ON t.item_id = s.item_id AND t.shift = s.shift
  WHERE s.shift = '$shift'
  ORDER BY s.take_id ASC
") or die('Could not load stock take: ' . htmlspecialchars(mysqli_error($connection), ENT_QUOTES, 'UTF-8'));
if (mysqli_num_rows($sales) < 1) {
  ?> 
  <div class="alert alert-info">Stock was not taken on <?php echo htmlspecialchars($shift, ENT_QUOTES, 'UTF-8'); ?>.</div>
  <?php
}else{
?>

     
                 <div class="">
                     <table class="table" style="margin-left: 5px; margin-right: 10px;">
             <thead> 
              <tr style="border-top:2px solid #000;">
                  <th colspan="11" class="text-center" style="font-size: 18px; font-weight: bold;">STORE <?php echo $shift; ?> STOCK TAKE</th> 
                <th style="width: 50px;" class="hidden-print" > </th>
               </tr>
                 <tr style="border-top:2px solid #000;">
                  <th style="width: 10px;">NO</th>
                   <th style="width: 100px;">ITEMS</th>
                   <th style="width: 50px;">SYS CLOSING</th>
                   <th style="width: 50px;">COUNTED STOCK</th>
                   <th style="width: 50px;">VARIANCE</th> 
                   <th style="width: 50px;">ISSUES</th>
                   <th style="width: 50px;">TOTAL</th>
                   <th style="width: 50px;">CLOSING STOCK</th>
                   <th style="width: 50px;">TOTAL OUT</th>
                   <th style="width: 50px;">SELLING PRICE</th>
                   <th style="width: 50px;">SALES</th>
                   <th style="width: 50px;" class="hidden-print" >Status</th> 
               </tr>
             </thead>
            <tbody style="font-size: 10px;">
                          <?php
                $no = 0;
                while ($sa = mysqli_fetch_assoc($sales)) {
                  $take_id= $sa['take_id'];
                  $item_id= $sa['item_id'];
                  $opening= $sa['opening'];
                  $counted = $sa['counted'];
                  $closing=$sa['closing'];
                  // Older stock-take records may not have a status field.
                  $status = isset($sa['status']) ? (int)$sa['status'] : 2;
                  $variance=  $counted -$opening;

                  if ($variance==0 && $status==2) {
                    $color="#63bf7b";
                  }elseif ($variance!=0 && $status==2) {
                    $color="#d9534f";
                  }elseif ($variance!=0 && $status==3) {
                    $color="#f0ad4e";
                  }else{
                    $color="#777";
                  }

                                  $totalout = (float)$sa['total_out'];
                                  $totalin = (float)$sa['total_in'];

                                    $stock_total=$counted+$totalin;

                                  // get_price() is a JavaScript helper; read the current
                                  // selling price from the item table instead.
                                  $price = (float)get_column('kp_items', 'price', "item_id='$item_id'");


                  ?>
                  <tr>
                    <td><?php echo $no=$no+1; ?></td>
                    <td><?php echo htmlspecialchars(get_item($item_id), ENT_QUOTES, 'UTF-8'); ?></td>
                    <td style="color: #a82529;"><?php echo $opening; ?></td>
                    <td style="color: #a82529;"><?php echo $counted; ?></td>
                    <td style="color: red; width: 50px;"><?php echo $variance; ?></td>
                    <td><?php echo $totalin; ?></td>
                    <td><?php echo $stock_total ?></td>
                    <td  style="color:#f9a64b;"><?php echo $closing; ?></td> 
                    <td><?php echo $totalout; ?></td>
                    <td  style="color:#f9a64b;"><?php echo number_format((float)$price, 2); ?></td>
                    <td  style="color:#f9a64b;"><?php echo number_format($totalout * (float)$price, 2); ?></td>
                    <td class="hidden-print" id="<?php echo $take_id; ?>" style="background-color: <?php echo $color;?>">
                      <?php 
                        if ($variance!=0 && $status==2) {
                          ?> <a  data-toggle="modal" data-target="#item_take" data-id="<?php echo $take_id; ?>" style=" font-size: 14px; margin-bottom:  8px;margin-left: 5px; color: white; font-weight: bold;s" id="ret" >ADJUST</a>  
                      <?php
                        }else{}
                      ?>
                    </td>
              </tr>
                  <?php 
                }
               }
               ?>

             
            
              </tbody>
            </table>
              </div>
