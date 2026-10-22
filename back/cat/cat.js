

// Add your custom JS code here
$( document ).ready(function() {
    // Handler for .ready() called.
    $(document).on('click','#cat-add', function(e){
      e.preventDefault(); 
      $('#cat-load').html(''); // leave this diff blank
      $('#modal-loader').show(); // load ajax loader button cliked
      $.ajax({
        url: '../cat/cat_new.php',
        typr: 'POST',
        data: '',
        dataType: 'html'
      })
      .done(function(data){
        $('#cat-load').html(''); // blank before load.
        $('#cat-load').html(data);
      })
      .fail(function(){
        $('#cat-load').html('<i class="fa  fa-info-sign"></i> Something went wrong, Please try again...');
        $('#modal-loader').hide();
      });
    });

    $(document).on('click.catCreate', '#new_cat', function(e){
      e.preventDefault();
      var $button = $(this);
      var name = $.trim($('#cat-new-name').val());
      var dpt = $('#cat-new-dpt').val();
      if (!name) {
        $('#cat-new-results').html('<div class="alert alert-danger">Please enter a category name.</div>');
        return;
      }
      if (!dpt || dpt === '0') {
        $('#cat-new-results').html('<div class="alert alert-warning">Please select a department.</div>');
        return;
      }
      $button.prop('disabled', true).text('Saving...');
      $.ajax({
        url: '../cat/cat_new_act.php',
        type: 'POST',
        data: { name: name, dpt: dpt },
        dataType: 'html'
      }).done(function(data){
        $('#cat-new-results').html(data);
        if (data.indexOf('alert-success') !== -1) {
          setTimeout(function(){ window.location.reload(); }, 700);
        }
      }).fail(function(){
        $('#cat-new-results').html('<div class="alert alert-danger">Unable to add the category. Please try again.</div>');
      }).always(function(){
        $button.prop('disabled', false).text('Save');
      });
    });

        $(document).on('click','#cat-edit', function(e){
          e.preventDefault();
          var uid = $(this).data('id'); // get id of clicked row
          $('#cat-load').html(''); // leave this diff blank
          $('#modal-loader').show(); // load ajax loader button cliked
          $.ajax({
            url: '../cat/cat_edit.php',
            typr: 'POST',
            data: 'id='+uid,
            dataType: 'html'
          })
          .done(function(data){
            $('#cat-load').html(''); // blank before load.
            $('#cat-load').html(data);
          })
          .fail(function(){
            $('#cat-load').html('<i class="fa  fa-info-sign"></i> Something went wrong, Please try again...');
            $('#modal-loader').hide();
          });
        });
        $(document).on('click','#cat-del', function(e){
          e.preventDefault();
          var uid = $(this).data('id'); // get id of clicked row 
          $('#cat-load').html(''); // leave this diff blank
          $('#modal-loader').show(); // load ajax loader button cliked
          $.ajax({
            url: '../cat/cat_del.php',
            typr: 'POST',
            data: 'id='+uid,
            dataType: 'html'
          })
          .done(function(data){
            $('#cat-load').html(''); // blank before load.
            $('#cat-load').html(data);
          }).fail(function(){
            $('#cat-load').html('<i class="fa  fa-info-sign"></i> Something went wrong, Please try again...');
            $('#modal-loader').hide();
          });
        });

    });
