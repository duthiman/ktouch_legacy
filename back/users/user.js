// Add your custom JS code here
$( document ).ready(function() {
    // Handler for .ready() called.
    $(document).on('click','#user-add', function(e){
      e.preventDefault(); 
      $('#user-load').html(''); // leave this diff blank
      $('#modal-loader').show(); // load ajax loader button cliked
      $.ajax({
        url: '../users/user_new.php',
        typr: 'POST',
        data: '',
        dataType: 'html'
      })
      .done(function(data){
        $('#user-load').html(''); // blank before load.
        $('#user-load').html(data);
      })
      .fail(function(){
        $('#user-load').html('<i class="fa  fa-info-sign"></i> Something went wrong, Please try again...');
        $('#modal-loader').hide();
      });
    });

    $(document).on('click','#user-perm', function(e){
      e.preventDefault(); 
      var uid = $(this).data('id'); // get id of clicked row
      $('#user-load').html(''); // leave this diff blank
      $('#modal-loader').show(); // load ajax loader button cliked
      $.ajax({
        url: '../users/user_perm.php',
        typr: 'POST',
        data: 'id='+uid,
        dataType: 'html'
      })
      .done(function(data){
        $('#user-load').html(''); // blank before load.
        $('#user-load').html(data);
      })
      .fail(function(){
        $('#user-load').html('<i class="fa  fa-info-sign"></i> Something went wrong, Please try again...');
        $('#modal-loader').hide();
      });
    });

    $(document).on('click','.set-max-sale', function(e){
      e.preventDefault();
      var uid = $(this).data('id');
      $('#user-load').html('');
      $('#modal-loader').show();
      $.ajax({
        url: '../users/max_sale.php',
        typr: 'POST',
        data: 'id='+uid,
        dataType: 'html'
      })
      .done(function(data){
        $('#user-load').html('');
        $('#user-load').html(data);
      })
      .fail(function(){
        $('#user-load').html('<i class="fa  fa-info-sign"></i> Something went wrong, Please try again...');
        $('#modal-loader').hide();
      });
    });

    $(document).on('click','#changepass', function(e){
      e.preventDefault(); 
      var uid = $(this).data('id'); // get id of clicked row
      $('#user-load').html(''); // leave this diff blank
      $('#modal-loader').show(); // load ajax loader button cliked
      $.ajax({
        url: '../users/change_pass.php',
        typr: 'POST',
        data: 'id='+uid,
        dataType: 'html'
      })
      .done(function(data){
        $('#user-load').html(''); // blank before load.
        $('#user-load').html(data);
      })
      .fail(function(){
        $('#user-load').html('<i class="fa  fa-info-sign"></i> Something went wrong, Please try again...');
        $('#modal-loader').hide();
      });
    });

$(document).on('click','#changepin', function(e){
      e.preventDefault(); 
      var uid = $(this).data('id'); // get id of clicked row
      $('#user-load').html(''); // leave this diff blank
      $('#modal-loader').show(); // load ajax loader button cliked
      $.ajax({
        url: '../users/change_pin.php',
        typr: 'POST',
        data: 'id='+uid,
        dataType: 'html'
      })
      .done(function(data){
        $('#user-load').html(''); // blank before load.
        $('#user-load').html(data);
      })
      .fail(function(){
        $('#user-load').html('<i class="fa  fa-info-sign"></i> Something went wrong, Please try again...');
        $('#modal-loader').hide();
      });
    });

        $(document).on('click','#user_edit', function(e){
          e.preventDefault();
          var uid = $(this).data('id'); // get id of clicked row
          $('#user-load').html(''); // leave this diff blank
          $('#modal-loader').show(); // load ajax loader button cliked
          $.ajax({
            url: '../users/user_edit.php',
            typr: 'POST',
            data: 'id='+uid,
            dataType: 'html'
          })
          .done(function(data){
            $('#user-load').html(''); // blank before load.
            $('#user-load').html(data);
          })
          .fail(function(){
            $('#user-load').html('<i class="fa  fa-info-sign"></i> Something went wrong, Please try again...');
            $('#modal-loader').hide();
          });
        });
        $(document).on('click','#user-del', function(e){
          e.preventDefault();
          var uid = $(this).data('id'); // get id of clicked row 
          $('#user-load').html(''); // leave this diff blank
          $('#modal-loader').show(); // load ajax loader button cliked
          $.ajax({
            url: '../users/user_del.php',
            typr: 'POST',
            data: 'id='+uid,
            dataType: 'html'
          })
          .done(function(data){
            $('#user-load').html(''); // blank before load.
            $('#user-load').html(data);
          }).fail(function(){
            $('#user-load').html('<i class="fa  fa-info-sign"></i> Something went wrong, Please try again...');
            $('#modal-loader').hide();
          });
        });

        $(document).on('click','#suspuser', function(e){
          var uid = $(this).data('id');
          $('#user-load').html(''); 
          $.ajax({
            url: 'susp_user.php',
            typr: 'POST',
            data: 'id='+uid,
            dataType: 'html'
          })
          .done(function(data){
            $('#user-load').html(''); // blank before load.
            $('#user-load').html(data);
          })
          .fail(function(){
            $('#user-load').html('<i class="fa  fa-info-sign"></i> Something went wrong, Please try again...');
            $('#modal-loader').hide();
          });
        });

        $(document).on('click','#unsuspuser', function(e){
          var uid = $(this).data('id');
          $('#user-load').html('');
          $.ajax({
            url: 'unsusp_user.php',
            typr: 'POST',
            data: 'id='+uid,
            dataType: 'html'
          })
          .done(function(data){
            $('#user-load').html(''); // blank before load.
            $('#user-load').html(data);
          })
          .fail(function(){
            $('#user-load').html('<i class="fa  fa-info-sign"></i> Something went wrong, Please try again...');
            $('#modal-loader').hide();
          });
        });

    });