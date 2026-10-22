function setReqHtml(ids, html) {
  for (var i = 0; i < ids.length; i++) {
    var el = document.getElementById(ids[i]);
    if (el) {
      el.innerHTML = html;
      el.style.visibility = "visible";
    }
  }
}

$(document).ready(function(){
    $.ajaxSetup({ cache: false });
    load_item_list();
    function bindReqTap(selector, handler) {
        var tapLimit = 12;

        $(document).on('pointerdown', selector, function(event){
            this.__reqTapStartX = event.clientX;
            this.__reqTapStartY = event.clientY;
            this.__reqTapMoved = false;
        });

        $(document).on('pointermove', selector, function(event){
            if (typeof this.__reqTapStartX === "undefined") {
                return;
            }
            if (Math.abs(event.clientX - this.__reqTapStartX) > tapLimit || Math.abs(event.clientY - this.__reqTapStartY) > tapLimit) {
                this.__reqTapMoved = true;
            }
        });

        $(document).on('pointerup', selector, function(event){
            if (this.__reqTapMoved) {
                return;
            }
            this.__reqPointerHandled = true;
            event.preventDefault();
            handler.call(this, event);
        });

        $(document).on('click', selector, function(event){
            if (this.__reqPointerHandled) {
                this.__reqPointerHandled = false;
                event.preventDefault();
                return;
            }
            event.preventDefault();
            handler.call(this, event);
        });
    }

    function handleReqCatTap(event) {
        var catId = $(this).data('cat-id');
        if (!catId) {
            return;
        }
        req_navbar(catId);
        load_cat_food(catId);
    }
    function handleReqSubTap(event) {
        var subId = $(this).data('sub-id');
        if (!subId) {
            return;
        }
        load_sub_food(subId);
    }
    bindReqTap('.req-mobile-cat-btn', handleReqCatTap);
    bindReqTap('.req-mobile-sub-btn', handleReqSubTap);
    $(document).on('click','#req-cart-toggle', function(){
        $('#req-cart-drawer').toggleClass('open');
    });
    $(document).on('click','#req-cart-close', function(){
        $('#req-cart-drawer').removeClass('open');
    });
    $(document).on('click','#getItem', function(e){ 
         var uid = $(this).data('id');
         $.ajax({
            url: 'req_modal.php',
            type: 'POST',
            data: {id:uid},
            dataType: 'html'
         })
         .done(function(data){ 
            $('#load').html('');
            $('#load').html(data);
          })
         .fail(function(){
            $('#load').html('<i class="fa  fa-info-sign"></i> Something went wrong, Please try again...'); 
         });
      });
    $(document).on('click','#cancel, #cancel_mobile', function(e){   
        $.ajax({
            url: 'cancel_modal.php',
            type: 'POST',
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
    $(document).on('click','#btn_sub, #btn_sub_mobile', function(e){   
        $('#load').html('');
        $.ajax({
            url: 'submit_modal.php',
            type: 'POST',
            data: '',
            dataType: 'html'
        })
        .done(function(data){
            $('#load').html(data);
        })
        .fail(function() {
            $('#load').html('<i class="fa  fa-info-sign"></i> Something went wrong, Please try again...'); 
        });
    });
});
function req_navbar(id){
  setReqHtml(["sub_cat", "sub_cat_mobile"], '<div class="req-loading">Loading sections...</div>');
  return $.get("req_nav.php", {id: id})
    .done(function(response){
      setReqHtml(["sub_cat", "sub_cat_mobile"], response);
    })
    .fail(function(){
      setReqHtml(["sub_cat", "sub_cat_mobile"], '<div class="req-loading req-loading-error">Could not load sections.</div>');
    });
}
function load_sub_food(id){
  setReqHtml(["menu", "menu_mobile"], '<div class="req-loading">Loading items...</div>');
  return $.get("sub_food.php", {id: id})
    .done(function(response){
      setReqHtml(["menu", "menu_mobile"], response);
    })
    .fail(function(){
      setReqHtml(["menu", "menu_mobile"], '<div class="req-loading req-loading-error">Could not load items.</div>');
    });
}
function load_cat_food(id){
    setReqHtml(["menu", "menu_mobile"], '<div class="req-loading">Loading items...</div>');
    return $.get("cat_food.php", {id: id})
      .done(function(response){
        setReqHtml(["menu", "menu_mobile"], response);
      })
      .fail(function(){
        setReqHtml(["menu", "menu_mobile"], '<div class="req-loading req-loading-error">Could not load items.</div>');
      });
}
function req_action(){ 
    var item_id = $("#item_id").val();
    var qty = "";
    if (typeof window.getFinalQty === "function") {
      qty = window.getFinalQty();
    } else {
      qty = $("#req_qty").val();
    }
    $.post("req_action.php", { item_id: item_id, qty:qty},
       function(data) { 
         load_item_list();
         $('#results').html(data);
          $('#modal').modal('toggle');
      });
}
function load_item_list(){
  setReqHtml(["screen", "screen_mobile"], '<div class="req-loading">Loading request...</div>');
  return $.get("req_list.php")
    .done(function(response){
      setReqHtml(["screen", "screen_mobile"], response);
      updateReqCartState();
    })
    .fail(function(){
      setReqHtml(["screen", "screen_mobile"], '<div class="req-loading req-loading-error">Could not load the request.</div>');
    });
}
function del_item(id){ 
    $.get("del_item.php", {id:id}, 
   function(data) {  
    load_item_list();
   });
} 
function updateReqCartState() {
  var rows = $('#screen_mobile .req-live-item').length;
  $('#req-cart-count').text(rows);
  $('#req-cart-total').text(rows ? rows + ' items' : '0');
}
