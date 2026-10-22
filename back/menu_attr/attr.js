$(document).ready(function() {
    App.init();
    TableManageCombine.init();

    // Initialize Select2 for item selection
    $("#itemSelect").select2({
        placeholder: "Search for an item",
        allowClear: true,
        dropdownParent: $('#mapModal .modal-body'), // Set dropdown parent to modal body
        ajax: {
            url: menuApiUrl,
            type: 'GET',
            dataType: 'json',
            delay: 250,
            data: function(params) {
                return {
                    action: 'search_unmapped_items',
                    search: params.term || '',
                    attr_id: $('#attr_id').val()
                };
            },
            processResults: function(data) {
                if (!data || !data.ok) {
                    console.error('Select2 error:', data && data.message ? data.message : 'Unknown error');
                    return { results: [] };
                }
                return {
                    results: (data.data && data.data.items ? data.data.items : []).map(function(item) {
                        return { id: item.item_id, text: item.name };
                    })
                };
            },
            error: function(xhr, status, error) {
                console.error('Select2 AJAX error:', status, error);
                return { results: [] };
            },
            cache: true
        },
        minimumInputLength: 0 // Allow empty search to show all unmapped items
    });

    // Handle "Map to Items" button click
    $(document).on('click', '.map-attr', function(e) {
        e.preventDefault();
        var attr_id = $(this).data('id');
        var attr_name = $(this).data('name');

        // Set attribute ID in hidden input and update modal title
        $('#attr_id').val(attr_id);
        $('#mapModalLabel').text('Map Item to ' + attr_name);
        $('#itemSelect').val(null).trigger('change'); // Clear previous selection
        $('#mapModal').modal('show');
    });

    // Handle "View Mapped Items" button click
    $(document).on('click', '.view-mapped', function(e) {
        e.preventDefault();
        var attr_id = $(this).data('id');
        var attr_name = $(this).data('name');

        // Update right panel title
        $('#items_list').html('<h4>Mapped Items for: ' + attr_name + '</h4><div id="items_content"></div>');

        // Fetch mapped items
        $.ajax({
            url: attrApiUrl,
            type: 'POST',
            data: { action: 'load_mapped_items', attr_id: attr_id },
            dataType: 'json',
            beforeSend: function() {
                $('#items_content').html('<p>Loading mapped items...</p>');
            },
            success: function(res) {
                $('#items_content').html((res && res.data && res.data.html) ? res.data.html : '<p>No items mapped to this attribute.</p>');
            },
            error: function(xhr, status, error) {
                console.error('Load mapped items error:', status, error);
                $('#items_content').html('<p>Error loading mapped items. Please try again.</p>');
            }
        });
    });

    // Handle "Map Item" button in modal
    $(document).on('click', '#saveMapping', function(e) {
        e.preventDefault();
        var item_id = $('#itemSelect').val();
        var attr_id = $('#attr_id').val();

        if (!item_id) {
            alert('Please select an item to map.');
            return;
        }

        $.ajax({
            url: attrApiUrl,
            type: 'POST',
            data: { item_id: item_id, attr_id: attr_id, action: 'map_item' },
            dataType: 'json',
            success: function(response) {
                if (response && response.ok) {
                    $('#mapModal').modal('hide');
                    alert(response.message || 'Item mapped successfully.');
                    // Refresh the mapped items list
                    $('.view-mapped[data-id="' + attr_id + '"]').click();
                } else {
                    alert('Error: ' + ((response && response.message) ? response.message : 'Unknown error'));
                }
            },
            error: function(xhr, status, error) {
                console.error('Map item error:', status, error);
                alert('Error processing the request. Please try again.');
            }
        });
    });

    // Handle Unmap button clicks
    $(document).on('click', '.unmap-item', function(e) {
        e.preventDefault();
        var item_id = $(this).data('item_id');
        var attr_id = $(this).data('attr_id');

        if (confirm('Are you sure you want to unmap this item?')) {
            $.ajax({
                url: attrApiUrl,
                type: 'POST',
                data: { item_id: item_id, attr_id: attr_id, action: 'unmap_item' },
                dataType: 'json',
                success: function(response) {
                    if (response && response.ok) {
                        // Refresh the mapped items list
                        $('.view-mapped[data-id="' + attr_id + '"]').click();
                    } else {
                        alert('Error: ' + ((response && response.message) ? response.message : 'Unknown error'));
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Unmap item error:', status, error);
                    alert('Error processing the request. Please try again.');
                }
            });
        }
    });

    // Handle "Add New Attribute" button
    $(document).on('click', '#newattr', function(e) {
        e.preventDefault();
        $('#load').html('');
        $.ajax({
            url: attrApiUrl,
            type: 'GET',
            data: { action: 'modal', view: 'attr_new' },
            dataType: 'json'
        })
        .done(function(res) {
            $('#load').html((res && res.data && res.data.html) ? res.data.html : '');
            $('#modal').modal('show');
        })
        .fail(function(xhr, status, error) {
            console.error('Add new attribute error:', status, error);
            $('#cat-load').html('<i class="fa fa-info-sign"></i> Something went wrong, Please try again...');
            $('#modal-loader').hide();
        });
    });
});
