<?php 
 include("../../inc/connection.php");
 include("../clude/funcs.php");
 // Load print/db settings row
 $res_set = mysqli_query($connection, "SELECT * FROM kp_settings_print WHERE print_doc=1 LIMIT 1");
 $row = $res_set ? mysqli_fetch_assoc($res_set) : [];
 if (!$row) $row = ['pin' => 2];
?>
<!-- begin panel -->
    <div class="panel">
    	<!-- begin panel-body -->
    	<div id="results"></div>
         <form method="post" action=""  enctype="multipart/form-data" id="myform"> 
        <div class="panel-body row">
           	<div class="col-sm-6">
                <div class="col-sm-12">
                    <fieldset>
                        <legend class="m-b-15">DB BACK UP SETTINGS</legend>
                        <div class="form-group row">
                            <div class="form-group col-md-12">
                                <label class="col-md-4"  style="color:blue;">Auto Back Up</label>
                                <div class="col-md-6">
                                <div class="radio radio-css radio-inline">
                                  <input type="radio" name="pin" id="pinyes" value="1"  <?php if ($row['pin']==1) { echo "checked"; } ?> />
                                  <label for="pinyes" style="color:blue;">YES</label>
                                </div>
                                <div class="radio radio-css radio-inline">
                                  <input type="radio" name="pin" id="pinno" value="2" <?php if ($row['pin']==2) { echo "checked"; } ?>/>
                                  <label for="pinno"  style="color:blue;">NO</label>
                                </div>
                              </div>
                            </div>  
                        </div>
                    </fieldset>  
                </div>              	 
    	    </div>
            <div class="col-sm-6">
                <div class="col-sm-12">
                    <fieldset>
                        <legend class="m-b-15">BACK UP LIST</legend>
                            <div class="panel-body">
                                <table id="data-table-combine" class="table table-striped table-bordered"> 
                                    
                                        <thead>
                                            <tr>
                                                <td bgcolor="#CCCCCC" class="bodytext3">&nbsp;</td>
                                                <td bgcolor="#CCCCCC" class="bodytext3"><strong>File Name </strong></td>
                                                <td bgcolor="#CCCCCC" class="bodytext3"><strong>File Date Time </strong></td>
                                                <td bgcolor="#CCCCCC" class="bodytext3"><div align="center"><strong>Download </strong></div></td>
                                            </tr>
                                        </thead> 
                                        <tbody>
                      <?php
                    $query1 = "SELECT back_id, backupfilename, backupfiledate FROM kp_backdb ORDER BY backupfiledate DESC LIMIT 5";
                    $exec1 = mysqli_query($connection, $query1) or die ("Error in Query1".mysqli_error($connection));
                    $colorloopcount = 0;
                    while ($res1 = mysqli_fetch_assoc($exec1))
                    {
                    $auto_number = $res1["back_id"];
                    $backupfilename = $res1["backupfilename"];
                    $backupfiledate = $res1["backupfiledate"];
            
                    $colorloopcount = $colorloopcount + 1;
                    $showcolor = ($colorloopcount & 1); 
                    if ($showcolor == 0)
                    {
                        $colorcode = 'bgcolor="#CBDBFA"';
                    }
                    else
                    {
                        $colorcode = 'bgcolor="#D3EEB7"';
                    }
                      
                    ?>
                    <tr <?php echo $colorcode; ?>>
                        <td width="6%" align="left" valign="top"  class="bodytext3"><div align="center">
                        <a href="db_del_back.php?id=<?php echo $auto_number; ?>" onClick="return confirm('Delete this backup?')">
                        <img src="images/b_drop.png" width="16" height="16" border="0" /></a></div></td>
                        <td width="58%" align="left" valign="top"  class="bodytext3"><?php echo $backupfilename; ?> </td>
                        <td width="36%" align="left" valign="top"  class="bodytext3"><?php echo $backupfiledate; ?></td>
                        <td width="36%" align="left" valign="top"  class="bodytext3">
                        <a href="db_download.php?filename=<?php echo $backupfilename; ?>">
                        <div align="center">Download</div></a></td>
                      </tr>
                      <?php
        }
        ?>
                      <tr>
                        <td align="middle" colspan="4" >&nbsp;</td>
                      </tr>
                    </tbody>
                  </table>
        </div>     
                    </fieldset>  
                </div>                   
            </div>
        </div>
    </form> 
	<!-- end panel-body -->
	<!-- START PANEL FOOTER -->
    <div class="panel-footer text-right">
        <!-- <a href="javascript:;" class="btn btn-danger" data-dismiss="modal">Close</a> -->
        <button type="submit" id="submit-sales" onclick="sales();" class="btn btn-lime">Submit</button>
        <button type="submit" id="create_back" onclick="thermal();" class="btn btn-warning">Create Back Up</button>
    </div>
	<!-- END PANEL FOOTER -->
</div>
<!-- end panel --> 
<script type="text/javascript">
     function thermal(){ 
         $.ajax({
            url: '../settings/dbback.php',
            typr: 'POST', 
            data: '',
            dataType: 'html'
        })
        .done(function(data){ 
             var hulla = new hullabaloo();
            hulla.send("Back Up Created Successfully!!!!", "success"); 
        })
        .fail(function() { 
            var hulla = new hullabaloo();
            hulla.send("Could not create Back Up!!!!", "danger"); 
        });
    }
 
</script>