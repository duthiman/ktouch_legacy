

// Add your custom JS code here
$( document ).ready(function() {
    // Handler for .ready() called.
    $(document).on('click','#sub-add', function(e){
      e.preventDefault(); 
      $('#sub-load').html(''); // leave this diff blank
      $('#modal-loader').show(); // load ajax loader button cliked
      $.ajax({
        url: '../subcat/sub_new.php',
        typr: 'POST',
        data: '',
        dataType: 'html'
      })
      .done(function(data){
        $('#sub-load').html(''); // blank before load.
        $('#sub-load').html(data);
      })
      .fail(function(){
        $('#sub-load').html('<i class="fa  fa-info-sign"></i> Something went wrong, Please try again...');
        $('#modal-loader').hide();
      });
    });

    $(document).on('click.subCreate', '#new_sub', function(e){
      e.preventDefault();
      var $button = $(this);
      var name = $.trim($('#sub-new-name').val());
      var catId = $('#sub-new-category').val();
      var dpt = $('#sub-new-dpt').val();
      if (!name) {
        $('#sub-new-results').html('<div class="alert alert-danger">Please enter a subcategory name.</div>');
        return;
      }
      if (!catId || catId === '0') {
        $('#sub-new-results').html('<div class="alert alert-warning">Please select a main category.</div>');
        return;
      }
      if (!dpt || dpt === '0') {
        $('#sub-new-results').html('<div class="alert alert-warning">Please select a department.</div>');
        return;
      }
      $button.prop('disabled', true).text('Saving...');
      $.ajax({
        url: '../subcat/sub_new_act.php',
        type: 'POST',
        data: { name: name, cat_id: catId, dpt: dpt },
        dataType: 'html'
      }).done(function(data){
        $('#sub-new-results').html(data);
        if (data.indexOf('alert-success') !== -1) {
          setTimeout(function(){ window.location.reload(); }, 700);
        }
      }).fail(function(){
        $('#sub-new-results').html('<div class="alert alert-danger">Unable to add the subcategory. Please try again.</div>');
      }).always(function(){
        $button.prop('disabled', false).text('Save');
      });
    });

        $(document).on('click','#sub-edit', function(e){
          e.preventDefault();
          var uid = $(this).data('id'); // get id of clicked row
          $('#sub-load').html(''); // leave this diff blank
          $('#modal-loader').show(); // load ajax loader button cliked
          $.ajax({
            url: '../subcat/sub_edit.php',
            typr: 'POST',
            data: 'id='+uid,
            dataType: 'html'
          })
          .done(function(data){
            $('#sub-load').html(''); // blank before load.
            $('#sub-load').html(data);
          })
          .fail(function(){
            $('#sub-load').html('<i class="fa  fa-info-sign"></i> Something went wrong, Please try again...');
            $('#modal-loader').hide();
          });
        });
        $(document).on('click','#sub-del', function(e){
          e.preventDefault();
          var uid = $(this).data('id'); // get id of clicked row 
          $('#sub-load').html(''); // leave this diff blank
          $('#modal-loader').show(); // load ajax loader button cliked
          $.ajax({
            url: '../subcat/sub_del.php',
            typr: 'POST',
            data: 'id='+uid,
            dataType: 'html'
          })
          .done(function(data){
            $('#sub-load').html(''); // blank before load.
            $('#sub-load').html(data);
          }).fail(function(){
            $('#sub-load').html('<i class="fa  fa-info-sign"></i> Something went wrong, Please try again...');
            $('#modal-loader').hide();
          });
        });

    });
