$(".selectpicker").select2();  
$(document).ready(function() {
  App.init();
  // General.init(); 
  load_items();
  $.fn.fetch_items();
  $.fn.get_sup();
  po_total();  
  $(document).on('click','#btnsup_save', function(e){
    var  name = $('#sup_name').val();
    var  email = $('#sup_email').val(); 
    var loc = $('#sup_loc').val(); 
    var phone = $('#sup_phone').val();
    var address = $('#sup_address').val();
    var vat= $('#vat').val(); 
    var pin = $('#pin').val();      
    $.ajax({
      url: '../../gen/sup_act.php',
      typr: 'POST',
      data: 'name='+name+'&email='+email+'&loc='+loc+'&phone='+phone+'&pin='+pin+'&vat='+vat+'&address='+address,
      dataType: 'html'
    })
    .done(function(data){
      console.log(data);
      $('#sup_result').html(''); // blank before load.
      $('#sup_result').html(data); 
      $("#sup_form").trigger('reset');
      $('#supModal').modal('toggle');
      $('#sup_result').empty(); 
      $.fn.get_sup();
    })
    .fail(function(){
      $('#sup_result').html('<i class="fa  fa-info-sign"></i> Something went wrong, Please try again... NOT LOADING PAGE'); 
    });
  });

  $('input[name="tax"]').click(function() { 
    var total =$('#ttt').html();
    var vat=0;
    if($(this).val() == '0') {
      $('#div-perc').hide();  
      $('#vat').val(0);
      $('#tt').html(total);
      $('#total').val(total); 
      $('#vatable').html(''); 
      $('#vatt').html('');
      $('#perc-input').val(0); 
      $('#vatable').html(0); 
      $('#vatt').html(0);


    }else if($(this).val() == '1'){ 
      $('#div-perc').show();
      $('#others').prop("disabled", true);
      $('#vatable').html(0); 
      $('#vatt').html(0);
      $('#others').prop("disabled", true);
      $('#perc-input').val(16);  
      $('#perc-input').prop("disabled", true);
      tax_perc();

    }else if($(this).val() == '2'){   
      $('#div-perc').show();
      $('#others').prop("disabled", true);
      $('#vatable').html(0); 
      $('#vatt').html(0); 
      $('#others').prop("disabled", true);
      $('#perc-input').val(16);  
      $('#perc-input').prop("disabled", true);
      tax_perc();
    }
   });

  $('input[name="perc"]').click(function() {
    if($(this).val() == '1') {
      $('#others').prop("disabled", true);
      $('#perc-input').val(16);  
      $('#perc-input').prop("disabled", true);
      tax_perc();     
    }else{ 
      $('#vatable').html(0); 
      $('#vatt').html(0);
      $('#perc-input').val(0);
      $('#others').prop("disabled", false);
      $('#perc-input').prop("disabled", false);  
    }
   }); 
 
 }); 
function clear_tax(){
  $("#none").click();
  var total =$('#ttt').html();
  var disc= $('#disc').val();
  total=total-disc;
  $('#tt').html(total);
}

function load_items(){
    po_total();
    xmlhttp = new XMLHttpRequest();
    xmlhttp.open("GET","../kugura/po_items.php",false);
    xmlhttp.send(null);
    document.getElementById("d1").innerHTML = xmlhttp.responseText;
    document.getElementById("d1").style.visibility = "visible";
  }

function tax_perc(){
  var vat_type=$('input[name="tax"]:checked').val();
  var perc= $('#perc-input').val();
  var total =$('#ttt').html();
  var disc= $('#disc').val();
  total=total-disc;
  var add=parseInt(100) + parseInt(perc);
  if (vat_type==1) {
    //inclusive 
    var multi=parseInt(perc)/add; 
    var vat=parseInt(total)*multi;
    var vatt=vat.toFixed(2);
    var vatable=total- vatt;
    var vatable=vatable.toFixed(2);
    var tt=total;
  } else if (vat_type==2){
    //exclusive 
    var multi=parseInt(add)/100;
    var vat= total *multi;
    var vatt=  vat - total ;
    var vatt=vatt.toFixed(2);
    var vatable= total ;
    var tt=vat.toFixed(2);
  }  
  $('#vatable').html(vatable); 
  $('#vatt').html(vatt);
  $('#tt').html(tt);  
} 

function po_total(){ 
  var total=$('#ttt').html();   
  $('#tt').html(total); 
}

function confirm_po(){
  var disc= $('#disc').val();
  var vatable= $('#vatable').html(); 
  var vatt= $('#vatt').html();
  var perc= $('#perc-input').val();
  var ship= $('#ship').val();
  var sup = document.getElementById("sup_id").value; 
  var tt= $('#tt').html();
  if (tt==0) {
    var hulla = new hullabaloo();
      hulla.send('Warning!!! Sale Cannot be Empty', "warning");
      $('#modal').modal('toggle');
  } else {
    $.ajax({
      url: 'confirm_modal.php',
      type: 'POST',
      data: {disc:disc,vatable:vatable,vatt:vatt,tt:tt,perc:perc,sup:sup,ship:ship},
      dataType: 'html'
    })
    .done(function(data){ 
      $('#modal-load').html(data); // blank before load.
    })
    .fail(function(){ 
      $('#modal-load').html('<i class="fa  fa-info-sign"></i> Something went wrong, Please try again...');
    });
  }
}

function hold(){
  
  $.ajax({
    url: '../kugura/hold_modal.php',
    type: 'POST',
    data: ' ',
    dataType: 'html'
  })
  .done(function(data){ 
    $('#modal-load').html(data); 
  })
  .fail(function(){ 
    $('#modal-load').html('<i class="fa  fa-info-sign"></i> Something went wrong, Please try again...');
  });
}

function cancel(){
  $.ajax({
    url: '../kugura/cancel_modal.php',
    type: 'POST',
    data: ' ',
    dataType: 'html'
  })
  .done(function(data){
    $('#modal-load').html(''); // blank before load.
    $('#modal-load').html(data); // blank before load.
  })
  .fail(function(){
    $('#modal-load').html(''); // blank before load.
    $('#modal-load').html('<i class="fa  fa-info-sign"></i> Something went wrong, Please try again...');
  });
}
function sup(){
  var sup = document.getElementById("sup_id").value;
  if(sup>0){ 
    document.getElementById("print-po").style.display = "block";
  }else{
    document.getElementById("print-po").style.display = "none";
  }
}

$.fn.get_sup = function(){ 
  $('#sup_id').empty(); 
  $.ajax({
    type: "GET",
    url: '../../gen/get_sup.php',
    data: { },
    success:  function(data) { 
      $('#sup_id').append(data);
    }
  });
}

$.fn.fetch_items = function(){ 
  $('#item_id').empty(); 
  $.ajax({
    type: "GET",
    url: '../../gen/fetchitems.php', 
    data: { },
    success:  function(data) {  
      $('#item_id').append(data);
    }
  });
}

$.fn.get_cat = function(){ 
  $('#cat_id').empty();
  $.ajax({
    type: "GET",
    url: '../../gen/get_cat.php', 
    data: { },
    success:  function(data) { 
      $('#cat_id').append(data);
    }
  });
} 

function quick_item(){
   $.ajax({
    type: 'GET',
    url: '../../back/prods/prods_new.php',
    data: { }, 
     success:  function(data) {
        $.fn.get_cat();
      $('#modal-load').html(data); 
    }
  });
}

function btnSubmitForm() {
  var item_id = $("#item_id").val();
  var quantity = $("#qty").val();
  var price = $("#price").val();
  $.post("../kugura/po_action.php", { item_id: item_id, quantity: quantity ,price : price },
  function(data) {
    $('#results').html(data);
    $('#sales_form')[0].reset();
    load_items();
    po_total();
  });
}
 
function showbtn(){ 
  var id=$('input:focus').attr('id'); 
  $(".btn-div").hide();
  $("."+id).show(); 
}
function showbtndisc(){ 
  var id=$('input:focus').attr('id'); 
  $(".btndisc-div").hide();
  $("."+id).show(); 
}

function showbtnprice(){ 
  var id=$('input:focus').attr('id'); 
  $(".btnprice-div").hide();
  $("."+id).show(); 
}

function updatepo(id) {  
  var qty = $("#btn"+id).val();  
  $.ajax({
    url: 'update_po.php',
    type: 'POST',
    data: 'item_id='+id+'&qty='+qty,
    dataType: 'html'
  })
  .done(function(data){
    load_items();
    po_total();
    $('#results').html(''); // blank before load.
    $('#results').html(data); // blank before load.;
  })
  .fail(function(){
    $('#results').html(''); // blank before load.
    $('#results').html('<i class="fa  fa-info-sign"></i> Something went wrong, Please try again...');
  });
}

function updatepodisc(id) {  
  var disc = $("#btndisc"+id).val();  
  $.ajax({
    url: 'update_podisc.php',
    type: 'POST',
    data: 'item_id='+id+'&disc='+disc,
    dataType: 'html'
  })
  .done(function(data){
    load_items();
    po_total();
    $('#results').html(''); // blank before load.
    $('#results').html(data); // blank before load.;
  })
  .fail(function(){
    $('#results').html(''); // blank before load.
    $('#results').html('<i class="fa  fa-info-sign"></i> Something went wrong, Please try again...');
  });
}

function updatepoprice(id) {  
  var price = $("#btnprice"+id).val();  
  $.ajax({
    url: 'update_poprice.php',
    type: 'POST',
    data: 'item_id='+id+'&price='+price,
    dataType: 'html'
  })
  .done(function(data){
    load_items();
    po_total();
    $('#results').html(''); // blank before load.
    $('#results').html(data); // blank before load.;
  })
  .fail(function(){
    $('#results').html(''); // blank before load.
    $('#results').html('<i class="fa  fa-info-sign"></i> Something went wrong, Please try again...');
  });
}

function del_this(item_id){ 
  $.ajax({
    url: 'po_item_del.php',
    type: 'POST',
    data: 'item_id='+item_id,
    dataType: 'html'
  })
  .done(function(data) { 
    load_items();
    po_total();
  })
  .fail(function() { 
    $('#d1').html(''); // blank before load.
    $('#d1').html('<i class="fa  fa-info-sign"></i> Something went wrong, Please try again...');
  });
}
