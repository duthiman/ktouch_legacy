
$(document).ready(function(){
  load_item_list();
      $(document).on('click','#getItem', function(e){ 
         var uid = $(this).data('id'); // get id of clicked row 
         $.ajax({
            url: 'req_modal.php',
            typr: 'POST',
            data: {id:uid},
            dataType: 'html'
         })
         .done(function(data){ 
            $('#load').html(''); // blank before load.
            $('#load').html(data);
          })
         .fail(function(){
            $('#load').html('<i class="fa  fa-info-sign"></i> Something went wrong, Please try again...'); 
         });
      });
      $(document).on('click','#cancel', function(e){   
    $.ajax({
      url: 'cancel_modal.php',
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
    });
  });
    });
function req_navbar(id){
   xmlhttp = new XMLHttpRequest();
  xmlhttp.open("GET","req_nav.php?id="+id,false);
   xmlhttp.send(null);
   document.getElementById("sub_cat").innerHTML = xmlhttp.responseText;
   document.getElementById("sub_cat").style.visibility = "visible";
}
 function load_sub_food(id){

  xmlhttp = new XMLHttpRequest();
  xmlhttp.open("GET","sub_food.php?id="+id,false);
   xmlhttp.send(null);
   document.getElementById("menu").innerHTML = xmlhttp.responseText;
   document.getElementById("menu").style.visibility = "visible";
}
 function load_cat_food(id){

  xmlhttp = new XMLHttpRequest();
  xmlhttp.open("GET","cat_food.php?id="+id,false);
   xmlhttp.send(null);
   document.getElementById("menu").innerHTML = xmlhttp.responseText;
   document.getElementById("menu").style.visibility = "visible";
}
function req_action(){
    var item_id = $("#item_id").val();
    var qty = $("#req_qty").val(); 
    $.post("kit_action.php", { item_id: item_id, qty:qty},
       function(data) {
        //console.log(data);
        load_item_list();
         $('#results').html(data);
          $('#modal').modal('toggle');
      });
}
 function load_item_list(){
  xmlhttp = new XMLHttpRequest();
  xmlhttp.open("GET","req_list.php",false);
   xmlhttp.send(null);
   document.getElementById("screen").innerHTML = xmlhttp.responseText;
   document.getElementById("screen").style.visibility = "visible";
}
 