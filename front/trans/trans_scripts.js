function setTransHtml(ids, html) {
  for (var i = 0; i < ids.length; i++) {
    var el = document.getElementById(ids[i]);
    if (el) {
      el.innerHTML = html;
      el.style.visibility = 'visible';
    }
  }
}

$(document).ready(function(){
    $.ajaxSetup({ cache: false });
    loadTransBootstrap();
    load_item_list();

    function bindTransTap(selector, handler) {
        var tapLimit = 12;

        $(document).on('pointerdown', selector, function(event){
            this.__transTapStartX = event.clientX;
            this.__transTapStartY = event.clientY;
            this.__transTapMoved = false;
        });

        $(document).on('pointermove', selector, function(event){
            if (typeof this.__transTapStartX === 'undefined') {
                return;
            }
            if (Math.abs(event.clientX - this.__transTapStartX) > tapLimit || Math.abs(event.clientY - this.__transTapStartY) > tapLimit) {
                this.__transTapMoved = true;
            }
        });

        $(document).on('pointerup', selector, function(event){
            if (this.__transTapMoved) {
                return;
            }
            this.__transPointerHandled = true;
            event.preventDefault();
            handler.call(this, event);
        });

        $(document).on('click', selector, function(event){
            if (this.__transPointerHandled) {
                this.__transPointerHandled = false;
                event.preventDefault();
                return;
            }
            event.preventDefault();
            handler.call(this, event);
        });
    }

    function handleTransCatTap(event) {
        var catId = $(this).data('cat-id');
        if (!catId) {
            return;
        }
        req_navbar(catId);
        load_cat_food(catId);
    }

    bindTransTap('.req-mobile-cat-btn', handleTransCatTap);

    $(document).on('click','#req-cart-toggle', function(){
        $('#req-cart-drawer').toggleClass('open');
    });
    $(document).on('click','#req-cart-close', function(){
        $('#req-cart-drawer').removeClass('open');
    });

    $(document).on('click','#getItem', function(e){ 
         var uid = $(this).data('id');
         $.ajax({
            url: 'API/trans_api.php',
            type: 'POST',
            data: {action: 'item_modal', item_id: uid},
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
            url: 'API/trans_api.php',
            type: 'POST', 
            data: { action: 'cancel_modal' },
            dataType: 'html'
        })
        .done(function(data){
            $('#load').html(''); 
            $('#load').html(data);
            $('#modal').modal('show');
        })
        .fail(function() {
            $('#load').html('<i class="fa  fa-info-sign"></i> Something went wrong, Please try again...'); 
            $('#modal').modal('show');
        });
    });

    $(document).on('click','#btn_sub, #btn_sub_mobile', function(e){   
        $('#load').html('');
        $.ajax({
            url: 'API/trans_api.php',
            type: 'POST', 
            data: { action: 'submit_modal' },
            dataType: 'html'
        })
        .done(function(data){
            $('#load').html(data);
            $('#modal').modal('show');
        })
        .fail(function() {
            $('#load').html('<i class="fa  fa-info-sign"></i> Something went wrong, Please try again...'); 
            $('#modal').modal('show');
        });
    });
});

function loadTransBootstrap() {
  $.ajax({
    url: 'API/trans_api.php',
    type: 'GET',
    data: { action: 'bootstrap' },
    dataType: 'json'
  })
  .done(function(res) {
    if (!res || !res.ok) {
      if (res && res.message === 'Session expired.') {
        window.location.href = '../log_out.php';
      }
      return;
    }
    var data = res.data || {};
    renderTransContext(data.context || {});
    renderTransCategories(data.categories || []);
    if ((data.categories || []).length > 0) {
      var firstCat = parseInt(data.categories[0].cat_id, 10) || 0;
      if (firstCat > 0) {
        req_navbar(firstCat);
        load_cat_food(firstCat);
      }
    }
  });
}

function renderTransContext(context) {
  $('#trans-counter-name-mobile').text(context.counter_name || '');
  $('#trans-counter-name-strip').text(context.counter_name || '');
  $('#trans-draft-number').text(context.trans_id || '0');
  $('#trans-draft-number-mobile').text(context.trans_id || '0');
  $('#trans-draft-number-strip').text(context.trans_id || '0');
}

function renderTransCategories(categories) {
  var desktopHtml = '';
  var mobileHtml = '';
  if (!categories.length) {
    desktopHtml = '<div>No categories available.</div>';
    mobileHtml = '<div>No categories available.</div>';
  } else {
    for (var i = 0; i < categories.length; i++) {
      var row = categories[i] || {};
      var catId = parseInt(row.cat_id, 10) || 0;
      var name = escapeHtml(row.name || '');
      desktopHtml += '<button type="button" onclick="req_navbar(' + catId + ');load_cat_food(' + catId + ')" class="btn_nav">' + name + '</button>';
      mobileHtml += '<button type="button" onclick="req_navbar(' + catId + ');load_cat_food(' + catId + ')" class="req-mobile-cat req-mobile-cat-btn" data-cat-id="' + catId + '">' + name + '</button>';
    }
  }
  setTransHtml(['trans-desktop-categories'], desktopHtml);
  setTransHtml(['trans-mobile-categories'], mobileHtml);
}

function escapeHtml(value) {
  return String(value)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');
}

function req_navbar(id){
   xmlhttp = new XMLHttpRequest();
  xmlhttp.open('GET','API/trans_api.php?action=subcategories_html&cat_id='+id,false);
   xmlhttp.send(null);
   setTransHtml(['sub_cat', 'sub_cat_mobile'], xmlhttp.responseText);
}

function load_sub_food(id){
  xmlhttp = new XMLHttpRequest();
  xmlhttp.open('GET','API/trans_api.php?action=items_by_sub_html&sub_id='+id,false);
   xmlhttp.send(null);
   setTransHtml(['menu', 'menu_mobile'], xmlhttp.responseText);
}

function load_cat_food(id){
    xmlhttp = new XMLHttpRequest();
    xmlhttp.open('GET','API/trans_api.php?action=items_by_cat_html&cat_id='+id,false);
    xmlhttp.send(null);
    setTransHtml(['menu', 'menu_mobile'], xmlhttp.responseText);
}

function trans_action(){ 
    var item_id = $('#item_id').val();
    var qty = $('#trans_qty').val(); 
    $.post('API/trans_api.php', { action: 'add_item', item_id: item_id, qty:qty},
       function(data) { 
        load_item_list();
         $('#results').html(data); 
          $('#modal').modal('toggle');
      });
}

function load_item_list(){
  xmlhttp = new XMLHttpRequest();
  xmlhttp.open('GET','API/trans_api.php?action=list_html',false);
   xmlhttp.send(null);
   setTransHtml(['screen', 'screen_mobile'], xmlhttp.responseText);
   updateTransCartState();
}

function del_item(id){ 
    $.get('API/trans_api.php', {action: 'delete_item', id:id}, 
   function(data) {  
    load_item_list();
   });
}

function updateTransCartState() {
  var rows = $('#screen_mobile .trans-live-item').length;
  $('#req-cart-count').text(rows);
  $('#req-cart-total').text(rows ? rows + ' items' : '0');
}
 
