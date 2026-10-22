function showCounterModalShell() {
  if ($('#modal-dialog').length) {
    $('#modal-dialog').modal('show');
  } else if ($('#modal').length) {
    $('#modal').modal('show');
  } else if ($('#modal-loader').length) {
    $('#modal-loader').modal('show');
  } else if ($('#load').closest('.modal').length) {
    $('#load').closest('.modal').modal('show');
  }
}

function printsum(id,shift,summaryId){
  $.ajax({
    url: 'printsum.php',
    type: 'POST',
    data: {id: id, shift: shift, summary_id: summaryId || 0},
    dataType: 'html'
  })
  .done(function(data){
    $('#load').html('');
    $('#load').html(data);
    showCounterModalShell();
  })
  .fail(function(){
    $('#load').html('<i class="fa fa-info-sign"></i> Could not load the counter summary preview right now.');
    showCounterModalShell();
  });
}
function stocktakeview(counterId, shift, view, summaryId){
  $.ajax({
    url: 'stock_take_modal.php',
    type: 'POST',
    data: {cntr_id: counterId, shift: shift, view: view, summary_id: summaryId || 0},
    dataType: 'html'
  })
  .done(function(data){
    $('#load').html('');
    $('#load').html(data);
    showCounterModalShell();
  })
  .fail(function(){
    $('#load').html('<i class="fa fa-info-sign"></i> Something went wrong, Please try again...');
    showCounterModalShell();
  });
}
$(document).ready(function() {
    App.init();
    TableManageCombine.init();
    $.ajax({
        url: 'cntrstat.php',
        type: 'POST',
        data: 'st=1',
        dataType: 'html'
      })
      .done(function(data){ 
        $('#cntrstat').html('');
        $('#cntrstat').html(data);
      })
    $(document).on('click','#filter', function(e){
      e.preventDefault();  
      var shift = $('#shift').val();
      $.ajax({
        url: 'cntrstat.php',
        type: 'POST',
        data: {st:2,shift:shift},
        dataType: 'html'
      })
      .done(function(data){ 
        $('#cntrstat').html('');
        $('#cntrstat').html(data);
      })
      .fail(function(){
        $('#cntrstat').html('<i class="fa  fa-info-sign"></i> Something went wrong, Please try again...');
        $('#modal-loader').hide();
      });
    });
    $(document).on('click','#close', function(e){
      e.preventDefault();  
      var id = $(this).data('id');
      $.ajax({
        url: 'close_counter.php',
        type: 'POST',
        data: 'id='+id,
        dataType: 'html'
      })
      .done(function(data){ 
        $('#load').html(''); // blank before load.
        $('#load').html(data);
        showCounterModalShell();
      })
      .fail(function(){
        $('#load').html('<i class="fa  fa-info-sign"></i> Something went wrong, Please try again...');
        $('#modal-loader').hide();
      });
    });

    $(document).on('click','#open', function(e){
      e.preventDefault();  
      var id = $(this).data('id');
      $.ajax({
        url: 'open_counter.php',
        type: 'POST',
        data: 'id='+id,
        dataType: 'html'
      })
      .done(function(data){ 
        $('#load').html(''); // blank before load.
        $('#load').html(data);
        showCounterModalShell();
      })
      .fail(function(){
        $('#load').html('<i class="fa  fa-info-sign"></i> Something went wrong, Please try again...');
        $('#modal-loader').hide();
      });
    });

    $(document).on('click','#assign', function(e){
      e.preventDefault();
      var id = $(this).data('id');  
      $.ajax({
        url: 'assign_cashier.php',
        type: 'POST',
        data: 'id='+id,
        dataType: 'html'
      })
      .done(function(data){ 
        $('#load').html(''); // blank before load.
        $('#load').html(data);
        showCounterModalShell();
      })
      .fail(function(){
        $('#load').html('<i class="fa  fa-info-sign"></i> Something went wrong, Please try again...');
        $('#modal-loader').hide();
      });
    });
    $(document).on('click','#change-stat', function(e){
      e.preventDefault();
      var uid = $(this).data('id'); // get id of clicked row  
      $.ajax({
        url: 'change_stat.php',
        type: 'POST',
        data: 'id='+uid,
        dataType: 'html'
      })
      .done(function(data){
        console.log(data);
        $('#load').html(''); // blank before load.
        $('#load').html(data);
        showCounterModalShell();
      }).fail(function(){
        $('#load').html('<i class="fa  fa-info-sign"></i> Something went wrong, Please try again...'); 
      });
    });
    }); 
 
