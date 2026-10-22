

// Add your custom JS code here
$( document ).ready(function() {
    function filterStockRows(){
      var mode=$('.stock-filter-card.active').data('stock-filter')||'all',cat=$('#stock-category-filter').val()||'',sub=$('#stock-subcategory-filter').val()||'';
      $('#data-table-combine tbody tr').each(function(){var r=$(this),q=parseFloat(r.data('stock-qty'))||0,ro=parseFloat(r.data('stock-reorder'))||0,ok=(mode==='all'||(mode==='out'&&q<=0)||(mode==='reorder'&&q<=ro))&&(!cat||String(r.data('stock-category'))===String(cat))&&(!sub||String(r.data('stock-subcategory'))===String(sub));r.toggle(ok);});
    }
    $(document).on('click','.stock-filter-card',function(){$('.stock-filter-card').removeClass('active');$(this).addClass('active');filterStockRows();});
    $(document).on('change','#stock-category-filter,#stock-subcategory-filter',filterStockRows);
    // Handler for .ready() called.
    $(document).on('click','#stock-adj', function(e){
      e.preventDefault(); 
      var uid = $(this).data('id'); // get id of clicked row 
      $('#stock-load').html(''); // leave this diff blank
      $('#modal-loader').show(); // load ajax loader button cliked
      $.ajax({
        url: 'qty_adj.php',
        typr: 'POST',
        data: 'id='+uid,
        dataType: 'html'
      })
      .done(function(data){
        $('#stock-load').html(''); // blank before load.
        $('#stock-load').html(data);
      })
      .fail(function(){
        $('#stock-load').html('<i class="fa  fa-info-sign"></i> Something went wrong, Please try again...');
        $('#modal-loader').hide();
      });
    });

       // Handler for .ready() called.
    $(document).on('click','#re-adj', function(e){
      e.preventDefault(); 
      var uid = $(this).data('id'); // get id of clicked row 
      $('#stock-load').html(''); // leave this diff blank
      $('#modal-loader').show(); // load ajax loader button cliked
      $.ajax({
        url: 're_adj.php',
        typr: 'POST',
        data: 'id='+uid,
        dataType: 'html'
      })
      .done(function(data){
        $('#stock-load').html(''); // blank before load.
        $('#stock-load').html(data);
      })
      .fail(function(){
        $('#stock-load').html('<i class="fa  fa-info-sign"></i> Something went wrong, Please try again...');
        $('#modal-loader').hide();
      });
    });

    $(document).on('click','#stock-levels', function(e){
      e.preventDefault();
      var uid = $(this).data('id');
      $('#stock-load').html('<div class="modal-content"><div class="modal-body text-center">Loading stock levels...</div></div>');
      $.ajax({url:'stock_levels.php',type:'POST',data:{id:uid},dataType:'html'})
        .done(function(data){ $('#stock-load').html(data); })
        .fail(function(){ $('#stock-load').html('<div class="modal-content"><div class="modal-body"><div class="alert alert-danger">Could not load stock levels.</div></div></div>'); });
    });
});
