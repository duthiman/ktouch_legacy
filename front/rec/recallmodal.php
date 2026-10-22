  <?php
session_start(); 
include("../../inc/connection.php");
include("funcs.php");
$user_id = $_SESSION['user_id'];
$req_id = $_REQUEST['req']; 
if ($user_id==0) {
  // code...
}elseif ($req_id==0) {
  // code...
}else{
  ?>
  <style>
    .recall-shell{
      border-radius:18px;
      overflow:hidden;
      border:1px solid rgba(110,75,24,.10);
      box-shadow:0 22px 44px rgba(77,55,21,.18);
    }
    .recall-head{
      background:linear-gradient(135deg,#8a1232 0%, #c3385c 100%);
      color:#fff7ea;
      padding:14px 18px;
    }
    .recall-head h2{
      margin:0;
      font-size:17px;
      font-weight:900;
      text-transform:uppercase;
      line-height:1.3;
    }
    .recall-body{
      background:#fffdf8;
      padding:16px;
    }
    .recall-body .alert{
      border-radius:14px;
      font-weight:800;
    }
    .recall-table-wrap{
      overflow-x:auto;
    }
    .recall-table th{
      font-size:11px;
      text-transform:uppercase;
      letter-spacing:.06em;
      color:#8a6a3a;
    }
    .recall-table td{
      font-size:13px;
      font-weight:700;
      color:#382819;
    }
    .recall-foot{
      display:flex;
      gap:10px;
      padding:12px 16px 16px;
      background:#fffdf8;
      border-top:1px solid rgba(110,75,24,.08);
    }
    .recall-foot .btn{
      flex:1 1 auto;
      min-height:44px;
      border-radius:12px;
      font-weight:900;
      text-transform:uppercase;
      border:0;
    }
    .recall-foot .danger{
      background:#fee2e2;
      color:#b91c1c;
    }
    .recall-foot .success{
      background:linear-gradient(135deg,#2f5d17 0%, #58a03f 100%);
      color:#fff;
    }
  </style>
  <div class="modal-content recall-shell" style="width:100%; max-width:860px; margin:0 auto;">
    <div class="modal-header recall-head">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      <h2 class="modal-title"><B>RECIEVE ALL  & SEND TO STCOK</B></h2>
    </div>
    <div class="modal-body text-center recall-body" id="rest">
      <div class="container-fluid"> 
          <div class="alert alert-danger">
            <h3>WARNING!! ARE YOU SURE YOU HAVE RECEIVED EVERYTHING AS REQUESTED.</h3>
          </div>
        </div>
         <div class="recall-table-wrap">
         <table class="table table-striped recall-table" style="padding: 5px 5px;">
            <thead>
              <tr>
                 <th>#</th>
                 <th>ITEM NAME</th>
                 <th>ORDERED QTY</th>
                 <th>ISSUED QTY</th> 
                 <th>CURRENT STOCK</th>
                 <th>NEW STOCK</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $sales = mysqli_query($connection,"SELECT * FROM kp_counter_req_items WHERE req_id='$req_id' AND s2s=2 ");
              if (mysqli_num_rows($sales) < 1) {
                echo "There are no items on this order";
              }else{
                $no=0;
                while ($sa = mysqli_fetch_assoc($sales)) {
                  $item_id = $sa['item_id'];
                  $rec_status = $sa['rec_status'];
                  $counter = $sa['counter'];
                  $column="cntr".$counter."_qty";
                  $no=$no+1;
                  ?>
                  <tr>
                    <td><?php echo $no; ?></td>
                    <td><?php echo get_item($item_id); ?></td> 
                    <td><?php echo $sa['req_qty']; ?></td>
                    <td><?php echo $sa['issue_qty']; ?></td>  
                    <td><b style="color:brown"><?php echo get_column('kp_items',$column,"item_id='$item_id'"); ?></b></td>
                    <td><b style="color:green;"><?php echo get_column('kp_items',$column,"item_id='$item_id'")+$sa['issue_qty']; ?></b></td>
                  </tr>
                  <?php
                }
              }
              ?>
            </tbody> 
          </table>
          </div>
        </div>
      </div>
      <div class="modal-footer recall-foot">
        <button type="button" class="btn btn danger" data-dismiss="modal">Close</button>
        <a class="btn btn success"  onclick="recall()" id="all">CONTINUE</a>
      </div>
    </div>
    <script type="text/javascript">
      function recall(){ 
        $('#all').text('');
        $('#all').append('<span> Please wait...<i class="fa-sharp fa-solid fa-loader"></i> </span>');
        $('#all').attr('disabled',true);
        $("#rest").html("<img src=../../assets/img/loader/load2.gif>");
        var req_id = <?php echo $req_id; ?>;  
        $.post("rec&send.php", {req:req_id},
        function(data) { 
          $('#rest').html(data); 
          });
      }
    </script>
    <?php 
}
?>
