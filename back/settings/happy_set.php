<?php 
session_start();
 include("../../inc/connection.php");
 include("../clude/funcs.php"); 
?>

<script type="text/javascript">
    
     function happy(){ 
        var siku = $("#siku").val();
        var start_time = $("#start").val();
        var end_time = $("#end").val(); 
        $.ajax({
            url: '../settings/happy_act.php',
            typr: 'POST', 
            data: 'day='+siku+'&start_time='+start_time+'&end_time='+end_time,
            dataType: 'html'
        })
        .done(function(data){ 
            console.log(data);
             $('#results').html(''); // blank before load.
            $('#results').html(data); 
        })
        .fail(function() { 
            var hulla = new hullabaloo();
            hulla.send("Could not create Counter!!!!", "danger"); 
        });
    }
 
</script>
<!-- begin panel -->
    <div class="panel">
    	<!-- begin panel-body -->
    	<div id="results"></div> 
        <div class="panel-body row">
           <div class="col-sm-4"> 
              <fieldset>
                <legend class="m-b-15">SET HAPPY HOUR</legend>
                <div class="form-group  m-b-15">
                <label class="col-md-4 col-sm-4 col-form-label">Day* :</label>
                <select class="form-control" id="siku" name="siku" data-parsley-required="true">
                  <option value="">----------SELECT DAY--------------</option>
                          <option value="Monday">Monday</option>
                          <option value="Tuesday">Tuesday</option>
                          <option value="Wednesday">Wednesday</option>
                          <option value="Thursday">Thursday</option>
                          <option value="Friday">Friday</option>
                          <option value="Saturday">Saturday</option>
                          <option value="Sunday">Sunday</option> 
                  </select>
            </div>  
            <div class="form-group   m-b-15">
                  <label class="col-md-8 col-sm-8 col-form-label">START TIME</label>
                  <div class="col-md-12">
                    <div class="input-group " id="start_time">
                        <input type="text" id="start" class="form-control" />
                        <div class="input-group-addon">
                            <i class="fa fa-clock"></i>
                        </div>
                    </div>
                  </div>
              </div>
              <div class="form-group   m-b-15">
                  <label class="col-md-8 col-sm-8 col-form-label">END TIME</label>
                  <div class="col-md-12">
                        <div class="input-group " id="end_time">
                          <input type="text" id="end" class="form-control" />
                          <span class="input-group-addon">
                              <i class="fa fa-clock"></i>
                          </span>
                      </div>
                    </div>
              </div>
             <button  id="counter_new" onclick="happy();" class="btn btn-lime pull-right">Submit</button> 
                    </fieldset>                 	 
    	    </div>
            <div class="col-sm-6">
                <div class="col-sm-12">
                    <fieldset>
                        <legend class="m-b-15">HAPPY HOUR LIST</legend>
                            <div class="panel-body">
                                 <?php
                  $get_category = mysqli_query($connection,"SELECT * FROM kp_hhr_set") or die("Could not get any items");
                  if (mysqli_num_rows($get_category ) < 1) {
                    echo "There is no happy hour settings in the system";
                  }else{
                ?>
                <table id="data-table-combine" class="table table-striped table-bordered">
                                <thead>
                                    <tr>  
                                        <th>DAY</th>
                                        <th>START TIME</th>
                                        <th>END TIME</th>
                                        <th>CREATED BY</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                while ($row = mysqli_fetch_assoc($get_category)) { 
                                  $day=$row['day']; 
                                  $start_time=$row['start_time']; 
                                  $end_time=$row['end_time']; 
                                  $user_id=$row['set_by'];                                
                                ?>
                                    <tr> 
                                        <td><?php echo $day; ?></td>
                                        <td><?php echo $start_time; ?></td>
                                        <td><?php echo $end_time; ?></td>
                                        <td><?php echo get_user($user_id); ?></td>
                                        <td>
                                          <a data-toggle="modal" data-target="#cat-modal" data-id="<?php echo $cat_id; ?>" id="cat-edit" class="btn btn-xs btn-info"><i  class="fa fa-edit"></i></a>
                                          |&nbsp;
                                          <a data-toggle="modal" data-target="#cat-modal" data-id="<?php echo $cat_id; ?>" id="cat-del" class="btn btn-xs btn-danger"><i  class=" fa fa-times"></i></a>
                                          
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
        </div>     
                    </fieldset>  
                </div>                   
            </div>
        </div>
  
	<!-- end panel-body --> 
</div>
<!-- end panel --> 
<script> 
    $(document).ready(function() { 
      $('#start_time').datetimepicker({
          format: 'LT'
      });
      $('#end_time').datetimepicker({
          format: 'LT'
      });
  });
  <?php include("settings.js");?>
</script> 