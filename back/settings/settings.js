// Add your custom JS code here
$(document ).ready(function(){ 
    var initialTab = 'gen';
    try {
        var params = new URLSearchParams(window.location.search);
        var requestedTab = params.get('tab');
        if (requestedTab && $('#' + requestedTab).length) {
            initialTab = requestedTab;
        }
    } catch (e) {}
    $('#' + initialTab).trigger('click');

    $(document).on('click','#gen', function(e){ 
        //$('#load').html('');  
        //$('#modal-loader').show(); 
        $.ajax({
            url: '../settings/gen_set.php',
            typr: 'POST', 
            data: '',
            dataType: 'html'
        })
        .done(function(data){
            $('#set').html(''); 
            $('#set').html(data);
        })
        .fail(function() {
            $('#set').html('<i class="fa  fa-info-sign"></i> Something went wrong, Please try again...');
           // $('#modal-loader').hide();
        });
    });
    $(document).on('click','#logo-set', function(e){
        $.ajax({
            url: '../settings/logo_set.php',
            type: 'GET',
            dataType: 'html'
        })
        .done(function(data){
            $('#set').html('');
            $('#set').html(data);
        })
        .fail(function() {
            $('#set').html('<i class="fa fa-info-sign"></i> Something went wrong, Please try again...');
        });
    });
    $(document).on('click','#front-images', function(e){
        $.ajax({ url: '../settings/front_images_set.php', type: 'GET', dataType: 'html' })
        .done(function(data){ $('#set').html(data); })
        .fail(function(){ $('#set').html('<i class="fa fa-info-sign"></i> Something went wrong.'); });
    });
    $(document).on('click','#sales', function(e){ 
        //$('#load').html('');  
        //$('#modal-loader').show(); 
        $.ajax({
            url: '../settings/sales_set.php',
            typr: 'POST', 
            data: '',
            dataType: 'html'
        })
        .done(function(data){
            $('#set').html(''); 
            $('#set').html(data);
        })
        .fail(function() {
            $('#set').html('<i class="fa  fa-info-sign"></i> Something went wrong, Please try again...');
           // $('#modal-loader').hide();
        });
    });
    $(document).on('click','#dbback', function(e){  
        $.ajax({
            url: '../settings/db_set.php',
            typr: 'POST', 
            data: '',
            dataType: 'html'
        })
        .done(function(data){
            $('#set').html(''); 
            $('#set').html(data);
        })
        .fail(function() {
            $('#set').html('<i class="fa  fa-info-sign"></i> Something went wrong, Please try again...');
           // $('#modal-loader').hide();
        });
    });
    $(document).on('click','#counter', function(e){  
        $.ajax({
            url: '../settings/counter_set.php',
            typr: 'POST', 
            data: '',
            dataType: 'html'
        })
        .done(function(data){
            $('#set').html(''); 
            $('#set').html(data);
        })
        .fail(function() {
            $('#set').html('<i class="fa  fa-info-sign"></i> Something went wrong, Please try again...');
           // $('#modal-loader').hide();
        });
    });
    $(document).on('click','#printer-set', function(e){  
        $.ajax({
            url: '../settings/printer_set.php',
            type: 'GET', 
            data: '',
            dataType: 'html'
        })
        .done(function(data){
            $('#set').html(''); 
            $('#set').html(data);
        })
        .fail(function() {
            $('#set').html('<i class="fa fa-info-sign"></i> Something went wrong, Please try again...');
        });
    });
     $(document).on('click','#happy', function(e){  
        $.ajax({
            url: '../settings/happy_set.php',
            typr: 'POST', 
            data: '',
            dataType: 'html'
        })
        .done(function(data){
            $('#set').html(''); 
            $('#set').html(data);
        })
        .fail(function() {
            $('#set').html('<i class="fa  fa-info-sign"></i> Something went wrong, Please try again...');
           // $('#modal-loader').hide();
        });
    });
    
    $(document).on('click','#pay-method', function(e){  
        $.ajax({
            url: '../settings/pay_method.php',
            typr: 'POST', 
            data: '',
            dataType: 'html'
        })
        .done(function(data){
            $('#set').html(''); 
            $('#set').html(data);
            ///load_item_list();
        })
        .fail(function() {
            $('#set').html('<i class="fa  fa-info-sign"></i> Something went wrong, Please try again...');
           // $('#modal-loader').hide();
        });
    });
    $(document).on('click','#new-menu', function(e){ 
        $('#load').html('');  
        $('#modal-loader').show(); 
        $.ajax({
            url: '../settings/menu_new.php',
            typr: 'POST', 
            data: '',
            dataType: 'html'
        })
        .done(function(data){
            $('#load').html(''); 
            $('#load').html(data);
        })
        .fail(function() {
            $('#load').html('<i class="fa  fa-info-sign"></i> Something went wrong, Please try again...');
            $('#modal-loader').hide();
        });
    });
    $(document).on('click','#edit', function(e){
        e.preventDefault();
        var uid = $(this).data('id'); // get id of clicked row
        console.log(uid);
        $('#load').html(''); // leave this diff blank
        $('#modal-loader').show(); // load ajax loader button cliked
        $.ajax({
            url: 'menu_edit.php',
            typr: 'POST',
            data: 'id='+uid,
            dataType: 'html'
        })
        .done(function(data){
            $('#load').html(''); // blank before load.
            $('#load').html(data);
        })
        .fail(function(){
            $('#dynamic-content').html('<i class="fa  fa-info-sign"></i> Something went wrong, Please try again...');
            $('#modal-loader').hide();
        });
    });
 
    $(document).on('click','#thermal-print', function(e){ 
        $('#load').html('');  
        $('#modal-loader').show(); 
        $.ajax({
            url: '../settings/thermal.php',
            typr: 'POST', 
            data: '',
            dataType: 'html'
        })
        .done(function(data){
            $('#set').html(''); 
            $('#set').html(data);
        })
        .fail(function() {
            $('#set').html('<i class="fa  fa-info-sign"></i> Something went wrong, Please try again...');
            $('#modal-loader').hide();
        });
    });

    $(document).on('click','#A4-print', function(e){ 
        $('#load').html('');  
        $('#modal-loader').show(); 
        $.ajax({
            url: '../settings/a4.php',
            typr: 'POST', 
            data: '',
            dataType: 'html'
        })
        .done(function(data){
            $('#set').html(''); 
            $('#set').html(data);
        })
        .fail(function() {
            $('#set').html('<i class="fa  fa-info-sign"></i> Something went wrong, Please try again...');
            $('#modal-loader').hide();
        });
    });

    $(document).on('click','#points', function(e){ 
        $('#load').html('');  
        $('#modal-loader').show(); 
        $.ajax({
            url: '../settings/point_set.php',
            typr: 'POST', 
            data: '',
            dataType: 'html'
        })
        .done(function(data){
            $('#set').html(''); 
            $('#set').html(data);
        })
        .fail(function() {
            $('#set').html('<i class="fa  fa-info-sign"></i> Something went wrong, Please try again...');
            $('#modal-loader').hide();
        });
    });

    $(document).on('click','#kpesa-set', function(e){
        $.ajax({ url: '../settings/kpesa_set.php', type: 'GET', dataType: 'html' })
        .done(function(data){ $('#set').html(data); })
        .fail(function(){ $('#set').html('<i class="fa fa-info-sign"></i> Something went wrong.'); });
    });

    $(document).on('click','#kreach-set', function(e){
        $.ajax({ url: '../settings/kreach_set.php', type: 'GET', dataType: 'html' })
        .done(function(data){ $('#set').html(data); })
        .fail(function(){ $('#set').html('<i class="fa fa-info-sign"></i> Something went wrong.'); });
    });

    $(document).on('click','#biz-profile', function(e){
        $.ajax({
            url: '../settings/biz_profile_set.php',
            type: 'GET',
            data: '',
            dataType: 'html'
        })
        .done(function(data){
            $('#set').html('');
            $('#set').html(data);
        })
        .fail(function() {
            $('#set').html('<i class="fa fa-info-sign"></i> Something went wrong, Please try again...');
        });
    });

    $(document).on('click','#kiosk-set', function(e){
        $.ajax({
            url: '../settings/kiosk_set.php',
            type: 'GET',
            data: '',
            dataType: 'html'
        })
        .done(function(data){
            $('#set').html('');
            $('#set').html(data);
        })
        .fail(function() {
            $('#set').html('<i class="fa fa-info-sign"></i> Something went wrong, Please try again...');
        });
    });

    $(document).on('change','#photo', function(e){
    // $("#photo").change(function() {
      $(".upload-msg").empty();  
      var file = this.files[0];
      var imagefile = file.type;
      var imageTypes= ["image/jpeg","image/png","image/jpg"];
        if(imageTypes.indexOf(imagefile) == -1)
        {
          $(".upload-msg").html("<span class='msg-error'>Please Select A valid Image File</span><br /><span>Only jpeg, jpg and png Images type allowed</span>");
          return false;
        }
        else
        {
          var reader = new FileReader();
          reader.onload = function(e){
            $(".img-preview").html('');
            $(".img-preview").html('<img src="' + e.target.result + '" style="margin-bottom: 10px; width: 180px; height:auto;" />');        
          };
          reader.readAsDataURL(this.files[0]);
        }
    });

     $(document).on('click','#edit', function(e){
          e.preventDefault();
          var uid = $(this).data('id'); // get id of clicked row
          $('#load').html(''); // leave this diff blank
          $('#modal-loader').show(); // load ajax loader button cliked
          $.ajax({
            url: '../settings/menu_edit.php',
            typr: 'POST',
            data: 'id='+uid,
            dataType: 'html'
          })
          .done(function(data){
            $('#load').html(''); // blank before load.
            $('#load').html(data);
          })
          .fail(function(){
            $('#load').html('<i class="fa  fa-info-sign"></i> Something went wrong, Please try again...');
            $('#modal-loader').hide();
          });
        });
  
});
