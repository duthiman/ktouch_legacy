<?php 
session_start();
 include("../../inc/connection.php");
 include("../clude/funcs.php"); 
 $mpesaCols = ['mpesa_type' => "ALTER TABLE kp_counters ADD COLUMN mpesa_type VARCHAR(20) NOT NULL DEFAULT 'TILL'", 'mpesa_shortcode' => "ALTER TABLE kp_counters ADD COLUMN mpesa_shortcode VARCHAR(50) NOT NULL DEFAULT ''", 'mpesa_account' => "ALTER TABLE kp_counters ADD COLUMN mpesa_account VARCHAR(100) NOT NULL DEFAULT ''"];
 foreach ($mpesaCols as $mpesaCol => $mpesaSql) { $mpesaCheck = mysqli_query($connection, "SHOW COLUMNS FROM kp_counters LIKE '$mpesaCol'"); if ($mpesaCheck && mysqli_num_rows($mpesaCheck) === 0) { mysqli_query($connection, $mpesaSql); } }
?>

<script type="text/javascript">
    
     function counter(){ 
        var name = $("#name").val();
        var dpt = $("#dpt").val(); 
        $.ajax({
            url: '../settings/counter_act.php',
            typr: 'POST', 
            data: 'name='+name+'&dpt='+dpt,
            dataType: 'html'
        })
        .done(function(data){  
             $('#results').html(''); // blank before load.
            $('#results').html(data); 
        })
        .fail(function() { 
            var hulla = new hullabaloo();
            hulla.send("Could not create Counter!!!!", "danger"); 
        });
    }

    function setCounterPrinter(cntr_id) {
        var printer_name = $('#printer_name_' + cntr_id).val().trim();
        $.post('../settings/counter_act.php', { action: 'set_printer', cntr_id: cntr_id, printer_name: printer_name }, function(data) {
            $('#printer_results').html(data);
        }, 'html').fail(function() {
            var hulla = new hullabaloo();
            hulla.send("Could not save printer name!", "danger");
        });
    }
    function setCounterMpesa(cntr_id) {
        $.post('../settings/counter_act.php', { action:'set_mpesa', cntr_id:cntr_id, mpesa_type:$('#mpesa_type_'+cntr_id).val(), mpesa_shortcode:$('#mpesa_shortcode_'+cntr_id).val(), mpesa_account:$('#mpesa_account_'+cntr_id).val() }, function(data){ $('#printer_results').html(data); }, 'html');
    }
 
</script>
<!-- begin panel -->
    <div class="panel">
    	<!-- begin panel-body -->
    	<div id="results"></div> 
        <div class="panel-body row">
           	<div class="col-sm-4"> 
                    <fieldset>
                        <legend class="m-b-15">CREATE NEW COUNTER</legend>
                         
                           <div class="form-group  m-b-15">
                <label>Counter Name* :</label>
                    <input class="form-control" type="text" id="name" name="name"  placeholder="Enter Menu Name..."  data-parsley-required="true" />
                 
            </div>  
            <div class="form-group m-b-15"">
                  <label>Department</label>
                  <select class="form-control" id="dpt" name="dpt" data-parsley-required="true">
                          <option value="0">----SELECT COUNTER-----</option>
                          <option value="1">BAR</option>
                          <option value="2">KITCHEN</option>
                  </select>
              </div>
             <button  id="counter_new" onclick="counter();" class="btn btn-lime pull-right">Submit</button> 
                    </fieldset>                 	 
    	    </div>
            <div class="col-sm-6">
                <div class="col-sm-12">
                    <fieldset>
                        <legend class="m-b-15">COUNTER LIST</legend>
                            <div class="panel-body">
                                 <?php
                  $get_category = mysqli_query($connection,"SELECT * FROM kp_counters") or die("Could not get any items");
                  if (mysqli_num_rows($get_category ) < 1) {
                    echo "There are no registered categories in the database";
                  }else{
                ?>
                <table id="data-table-combine" class="table table-striped table-bordered">
                                <thead>
                                    <tr>  
                                        <th>Counter Name</th>
                                        <th>Department</th>
                                        <th>Printer Name (Windows)</th>
                                        <th>M-Pesa</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                while ($row = mysqli_fetch_assoc($get_category)) { 
                                  $name=$row['name'];
                                  $cntr_id=$row['cntr_id'];
                                  $printer_name = isset($row['printer_name']) ? $row['printer_name'] : '';
                                  if ($row['dpt']==1) {
                                    $dpt="BAR";
                                  }elseif ($row['dpt']==2) {
                                    $dpt="KITCHEN";
                                  }else{
                                    $dpt="ERROR";
                                  }                                
                                ?>
                                    <tr> 
                                        <td><?php echo $name; ?></td>
                                        <td><?php echo $dpt;  ?></td>
                                        <td style="min-width:200px;">
                                          <div class="input-group input-group-sm">
                                            <input type="text" id="printer_name_<?php echo $cntr_id; ?>" class="form-control" value="<?php echo htmlspecialchars($printer_name); ?>" placeholder="e.g. POS-80 Printer">
                                            <span class="input-group-btn">
                                              <button class="btn btn-sm btn-success" onclick="setCounterPrinter(<?php echo $cntr_id; ?>);"><i class="fa fa-save"></i></button>
                                            </span>
                                          </div>
                                        </td>
                                        <td style="min-width:260px;">
                                          <select id="mpesa_type_<?php echo $cntr_id; ?>" class="form-control input-sm"><option value="TILL"<?php echo (($row['mpesa_type'] ?? 'TILL') === 'TILL') ? ' selected' : ''; ?>>TILL</option><option value="PAYBILL"<?php echo (($row['mpesa_type'] ?? '') === 'PAYBILL') ? ' selected' : ''; ?>>PAYBILL</option></select>
                                          <input id="mpesa_shortcode_<?php echo $cntr_id; ?>" class="form-control input-sm" placeholder="Shortcode" value="<?php echo htmlspecialchars($row['mpesa_shortcode'] ?? ''); ?>">
                                          <input id="mpesa_account_<?php echo $cntr_id; ?>" class="form-control input-sm" placeholder="Account (PayBill)" value="<?php echo htmlspecialchars($row['mpesa_account'] ?? ''); ?>">
                                          <button class="btn btn-xs btn-success" onclick="setCounterMpesa(<?php echo $cntr_id; ?>);"><i class="fa fa-save"></i> Save</button>
                                        </td>
                                        <td>
                                          <a data-toggle="modal" data-target="#cat-modal" data-id="<?php echo $cntr_id; ?>" id="cat-edit" class="btn btn-xs btn-info"><i  class="fa fa-edit"></i></a>
                                          |&nbsp;
                                          <a data-toggle="modal" data-target="#cat-modal" data-id="<?php echo $cntr_id; ?>" id="cat-del" class="btn btn-xs btn-danger"><i  class=" fa fa-times"></i></a>
                                        </td> 
                                    </tr>
                                    
                                    <?php
                                      }
                                    ?>
                                </tbody>
                            </table>
                            <?php
                        }
                            ?> 
        <div id="printer_results"></div>
        </div>     
                    </fieldset>  
                </div>                   
            </div>
        </div>
  
	<!-- end panel-body --> 
</div>
<!-- end panel --> 
