  <?php
session_start(); 
include("../../inc/connection.php");
include("../../inc/gen_funcs.php");
$user_id = $_SESSION['user_id'];
$req_id = $_REQUEST['id']; 
if ($user_id==0) {
  // code...
}elseif ($req_id==0) {
  // code...
}else{
  ?>
  <div class="modal-content">
    <div class="modal-header"> 
      <h2 class="modal-title"><B>ISSUE ALL  REQUESTED STCOK</B></h2>
    </div>
    <div class="modal-body text-center" id="rest">
      <div class="container-fluid"> 
        <div class="alert alert-danger">
          <h3>WARNING!! MAKE SURE THE BAR MAN RECEIVES EVERYTHING.</h3>
        </div>
      </div>
      <table class="table table-striped" style="padding: 5px 5px;">
        <thead>
          <tr>
             <th>#</th>
             <th>ITEM NAME</th>
             <th>ORDERED QTY</th>
             <th>ISSUED QTY</th> 
             <th style="color:red;">NEW STOCK BAL</th> 
          </tr>
        </thead>
        <tbody>
          <?php
          $sales = mysqli_query($connection,"SELECT * FROM kp_counter_req_items WHERE req_id='$req_id' AND issue_status=2");
          if (mysqli_num_rows($sales) < 1) {
            echo "There are no items on this order";
          }else{
            $no=0;
            while ($sa = mysqli_fetch_assoc($sales)) {
              $item_id = $sa['item_id'];
              $rec_status = $sa['rec_status'];
              $no=$no+1;
              ?>
              <tr>
                <td><?php echo $no; ?></td>
                <td><?php echo get_item($item_id); ?></td> 
                <td><?php echo $sa['req_qty']; ?></td>
                <td><?php echo $sa['req_qty']; ?></td>  
                <td style="color:red;"><?php echo get_column('kp_items','store_qty',"item_id='$item_id'")-$sa['req_qty']; ?></td>  
              </tr>
              <?php
            }
          }
          ?>
          </tbody> 
        </table>
    </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-danger btn-lg" data-dismiss="modal">CLOSE ISSUE</button>
        <a class="btn btn-outline-success btn-lg"  onclick="isall()" id="all">ISSUE ALL</a>
      </div>
    </div> 
    <script type="text/javascript">
      function isall(){ 
        $('#all').text('');
        $('#all').append('<span> Please wait...<i class="fa-sharp fa-solid fa-loader"></i> </span>');
        $('#all').attr('disabled',true);
        $("#rest").html("<img src=../../assets/img/loader/load2.gif>");
        var req_id = <?php echo $req_id; ?>;  
        $.ajax({
          url: 'API/inventory_api.php',
          type: 'POST',
          dataType: 'json',
          data: {
            action: 'issue_all_request',
            req: req_id
          }
        }).done(function(res) {
          var alertClass = res && res.ok ? 'success' : 'danger';
          var message = res && res.message ? res.message : 'Could not issue all items.';
          $('#rest').html('<div class="alert alert-' + alertClass + '"><strong>' + message + '</strong></div>');
          if (res && res.ok && res.data && res.data.redirect) {
            window.setTimeout(function() {
              window.location.href = res.data.redirect;
            }, 250);
          }
        }).fail(function() {
          $('#rest').html('<div class="alert alert-danger"><strong>Something went wrong, please try again.</strong></div>');
        });
      }
    </script>
    <?php 
}
?>
