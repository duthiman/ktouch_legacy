<?php  
include('funcs.php');
$user_id = $_SESSION['mwene'];  
$action="LOADED NEW PURCHASE MODAL | ";
$url="../../";
logs($url,'#kugura/#dash','PURCHASE','0','-','-',"INITIALISED PUTCHASE");
logs("../",'#kugura/#dash/','PURCHASE','0','-','-',"INITIALISED PUTCHASE");
?>
<style>
    body {  
        background-image: url("../../../assets/img/cover/bg-login.png") ;
        background-size: cover; 
    }  
    table tbody {
        display: block;/**/
        max-height: 450px;
        overflow-y: auto;
    }
    table thead, table tbody tr {
        display: table;
        width: 100%;
        table-layout: fixed;
    }
</style>
<div class="panel"> 
    <div class="panel-body"> 
        <div class="modal-content">
        	<div class="modal-header"> 
                <h4 class="modal-title">GENERATE NEW PURCHASE ORDER</h4>
            </div>
            <div class="modal-body p-2">
            	<div id="qtresult"></div>  
                <div class="row">  
                    <div class="col-12 bg-grey" id="podets">
                        <div class="panel">
                            <div class="panel-heading"> 
                                <div class="pull-right"> 
                                  <a href="../back" name="filter" class="btn btn-outline-info btn-sm pull-left"><i class="fa fa-home"></i> GO TO HOME</a>
                                  &nbsp;&nbsp;
                                  <button name="gennewpo" id="gennewpo" class="btn btn-outline-success btn-sm pull-right">GENERATE NEW PURCHASE</button>
                                </div> 
                            </div>
                            <div class="panel-body" id="init"> 
                                <table  class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <td colspan="5"><h2>ONGOING PURCHASE ORDERS</h2></td>
                                        </tr>
                                        <tr> 
                                            <th>PO ID</th>
                                            <th>SUPPLIER</th>    
                                            <th>USER</th> 
                                            <th>DATIME</th>  
                                            <th>ACTION</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $get_items = mysqli_query($connection,"SELECT * FROM kp_po_status") or die(mysqli_error($connection));
                                        while ($oow = mysqli_fetch_assoc($get_items)) { 
                                            $po_id = $oow['po_id'];   
                                            $user_id=$oow['po_user_id'];  
                                            $sup_id=$oow['sup_id'];
                                            $po_status=$oow['po_status']; 
                                            $datime=$oow['datime'];   
                                            ?>
                                            <tr>
                                                <td><?php echo $po_id; ?></td> 
                                                <td><?php echo get_column('kp_suppliers','sup_name',"sup_id='$sup_id'"); ?></td>
                                                <td><?php echo get_column('kp_users','username',"user_id='$user_id'"); ?></td> 
                                                <td><?php echo $datime; ?></td> 
                                                <td>
                                                    <?php 
                                                    if ($_SESSION['mwene']==$user_id OR empty($user_id) OR $user_id==0) {
                                                        ?>
                                                        <a id="btncont" style="margin-left: 10px;" type="button"  name="filter" class="btn btn-outline-pink btn-sm" data-id="<?php echo $po_id; ?>">CONTINUE PURCHASE</a>  
                                                        <?php
                                                    } 
                                                    ?>
                                                </td>
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
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    // $('.selectpicker').select2({});
    function sup_select(){
        selectVal = $('#sup_id').val();
        if (selectVal == 0) {
            document.getElementById("newpo").disabled =true;
        }else{
            document.getElementById("newpo").disabled =false; 
        }
    }
    $( '#bar').autocomplete({ 
        source: function( request, response ) {  
          $.ajax({
            url: "../..//gen/fetchdata.php",
            type: 'POST',
            dataType: "json",
            data: { search: request.term,table:'kp_suppliers',column:'sup_phone,sup_name',id:'sup_id'},
            success: function( data ) {  
                console.log(data);
                response(data);
            }
          });
        },
        select: function (event, ui) {
          $('#bar').val(ui.item.label);  
          $('#sup_id').val(ui.item.value); 
          sup_select();  
          return false;
        }   
    });   
    // $(document).on('click','#newpo', function(e){  
    //     $('#qtresult').html('');
    //     var sup_id= $('#sup_id').val();
    //     var potype= $('input[name="potype"]').val(); 
    //     var poby= $('input[name="poby"]').val();  
    //     $.ajax({
    //         url: 'kugura/init/init_po.php',
    //         typr: 'POST',
    //         data: 'sup='+sup_id+'&type='+potype+'&poby='+poby+'&st=NEW',
    //         dataType: 'html'
    //     })
    //     .done(function(data){ 
    //         console.log(data);
    //         $('#qtresult').html(''); // blank before load.
    //         $('#qtresult').html(data);
    //     })
    //     .fail(function(){
    //         $('#qtresult').html('<i class="fa  fa-info-sign"></i> Something went wrong, Please try again...');
    //         $('#modal-loader').hide();
    //     });
    // });
    $(document).on('click','#btncont', function(e){  
        $('#qtresult').html('');
        var po_id= $(this).data('id'); 
        $.ajax({
            url: 'kug/init/init_po.php',
            typr: 'POST',
            data: 'id='+po_id+'&cont=1',
            dataType: 'html'
        })
        .done(function(data){ 
            console.log(data);
            $('#qtresult').html(''); // blank before load.
            $('#qtresult').html(data);
        })
        .fail(function(){
            $('#qtresult').html('<i class="fa  fa-info-sign"></i> Something went wrong, Please try again...');
            $('#modal-loader').hide();
        });
    });

    $(document).on('click','#gennewpo', function(e){
        e.preventDefault();
        var uid = $(this).data('id'); // get id of clicked row
        $('#init').html(''); // leave this diff blank 
        $.ajax({
          url: 'kug/init/initset.php',
          typr: 'POST',
          data: 'id='+uid,
          dataType: 'html'
        })
        .done(function(data){ 
          $('#init').html(data);
        })
        .fail(function(){
          $('#init').html('<i class="fa  fa-info-sign"></i> Something went wrong, Please try again...'); 
        });
    }); 
    $('input[name="poby"]').click(function() {    
      if($(this).val() == '1'){  
      $.fn.get_sup(); 
      }else if($(this).val() == '2'){
      $.fn.get_sup();    
      }
    });

    // $('input[name="potype"]').click(function() {    
    //   if($(this).val() == '1'){  
    //     $('#divinv').val('show');
    //   }else if($(this).val() == '2'){
    //     $('#divinv').val('show');   
    //   }
    // });
</script>