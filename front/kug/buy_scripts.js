var kugApiUrl = 'API/kug_api.php';
var kugCommonCatalogUrl = '../API/common/api.php';
var kugCatalogState = {
    loaded: false,
    categories: [],
    subcategories: [],
    items: []
};

function kugApiRequest(options) {
    return $.ajax({
        url: kugApiUrl,
        type: options.type || 'GET',
        dataType: 'json',
        data: options.data || {}
    });
}

$(document).ready(function () {
    loadKugCatalog();
    loadKugBootstrap();

    $(document).on('click', '#getItem', function () {
        var uid = $(this).data('id');
        openModalRequest('req_modal.php', { id: uid });
    });

    $(document).on('click', '#cancel, #cancel-mobile', function () {
        kugApiRequest({
            type: 'POST',
            data: { action: 'cancel_modal' }
        }).done(function (res) {
            $('#load').html((res && res.data && res.data.html) ? res.data.html : '');
            showModalShell();
        });
    });

    $(document).on('click', '#btn_sub, #btn_sub_mobile', function () {
        if ($(this).prop('disabled') || $('#supplier-name').text().trim() === 'NONE') {
            alert('Select supplier first.');
            return false;
        }
        kugApiRequest({
            type: 'POST',
            data: { action: 'submit_modal' }
        }).done(function (res) {
            $('#load').html((res && res.data && res.data.html) ? res.data.html : '');
            showModalShell();
        });
    });

    $(document).on('click', '#supplier-btn, #supplier-btn-mobile', function (e) {
        e.preventDefault();
        kugApiRequest({
            type: 'POST',
            data: { action: 'supplier_modal' }
        }).done(function (res) {
            $('#load').html((res && res.data && res.data.html) ? res.data.html : '');
            showModalShell();
        }).fail(function () {
            $('#load').html('<div class="modal-content"><div class="modal-body" style="padding:20px; text-align:center; font-weight:bold;">Something went wrong, Please try again...</div></div>');
            showModalShell();
        });
    });

    $(document).on('click', '#add-new-front-supplier', function () {
        kugApiRequest({
            type: 'GET',
            data: { action: 'supplier_new_modal' }
        }).done(function (res) {
            $('#load').html((res && res.data && res.data.html) ? res.data.html : '');
            showModalShell();
        });
    });

    $(document).on('change', '#buy-tax, #buy-tax-mobile', function () {
        var enabled = $(this).is(':checked') ? 1 : 0;
        $('#buy-tax, #buy-tax-mobile').prop('checked', enabled === 1);
        kugApiRequest({
            type: 'POST',
            data: { action: 'toggle_tax', tax: enabled }
        }).done(function (res) {
            var taxValue = res && res.data ? Number(res.data.buy_tax || 0) : 0;
            $('#buy-tax, #buy-tax-mobile').prop('checked', taxValue === 1);
        }).fail(function () {
            $('#buy-tax, #buy-tax-mobile').prop('checked', false);
        });
    });

    $(document).on('click', '.btn_nav, .kw-mobile-cat', function () {
        var id = $(this).data('cat-id');
        req_navbar(id);
        load_cat_food(id);
    });

    $(document).on('click', '.kw-sub-btn', function () {
        load_sub_food($(this).data('sub-id'));
    });

    $(document).on('click', '#save-supplier', function () {
        var supId = parseInt($('#sup_id').val(), 10) || 0;
        if (supId < 1) {
            alert('Select supplier first.');
            return;
        }
        kugApiRequest({
            type: 'POST',
            data: { action: 'save_supplier', sup_id: supId }
        }).done(function (res) {
            if (!res || !res.ok) {
                return;
            }
            var supplierName = (res.data && res.data.supplier_name) ? res.data.supplier_name : 'NONE';
            var hasSupplier = Number(res.data && res.data.supplier_id ? res.data.supplier_id : 0) > 0;
            $('#supplier-name').text(supplierName);
            $('#supplier-name-mobile').text(hasSupplier ? supplierName : 'NO SUPPLIER SELECTED');
            $('#supplier-status').css(hasSupplier ? {'background-color':'#fff6c7','color':'#6a4d00'} : {'background-color':'#f6d3d9','color':'#7c1f2d'});
            $('#tax-wrap, #tax-wrap-mobile').toggle(hasSupplier);
            $('#btn_sub, #btn_sub_mobile').toggleClass('btn-disabled-soft', !hasSupplier).prop('disabled', !hasSupplier);
            $('#buy-tax, #buy-tax-mobile').prop('checked', Number(res.data.buy_tax || 0) === 1);
            hideModalShell();
        });
    });

    $(document).on('click', '#buy-cancel-confirm', function () {
        $('#buy-cancel-feedback').html('<div class="alert alert-warning" style="margin-bottom:0;">Cancelling draft...</div>');
        kugApiRequest({
            type: 'POST',
            data: { action: 'cancel' }
        }).done(function (res) {
            if (res && res.ok && res.data && res.data.redirect) {
                window.location = res.data.redirect;
                return;
            }
            $('#buy-cancel-feedback').html('<div class="alert alert-danger" style="margin-bottom:0;">' + ((res && res.message) ? res.message : 'Could not cancel the draft.') + '</div>');
        });
    });

    $(document).on('click', '#buy-submit-confirm', function () {
        $('#buy-submit-feedback').html('<div class="alert alert-warning" style="margin-bottom:0;">Submitting purchase...</div>');
        kugApiRequest({
            type: 'POST',
            data: { action: 'submit' }
        }).done(function (res) {
            if (res && res.ok && res.data && res.data.redirect) {
                $('#buy-submit-feedback').html('<div class="alert alert-success" style="margin-bottom:0;">' + (res.message || 'Purchase submitted.') + '</div>');
                window.location = res.data.redirect;
                return;
            }
            $('#buy-submit-feedback').html('<div class="alert alert-danger" style="margin-bottom:0;">' + ((res && res.message) ? res.message : 'Could not submit purchase.') + '</div>');
        });
    });

    $(document).on('click', '#kw-cart-toggle', function () {
        $('#kw-cart-drawer').toggleClass('open');
    });

    $(document).on('click', '#kw-cart-close', function () {
        $('#kw-cart-drawer').removeClass('open');
    });

    $(document).on('click', '#modal [data-dismiss="modal"], #modal .close, #modal #close', function (event) {
        event.preventDefault();
        hideModalShell();
    });

    $(document).on('click', '#modal', function (event) {
        if (event.target === this) {
            hideModalShell();
        }
    });
});

function showModalShell() {
    var $modal = $('#modal');
    $modal.addClass('kw-jq-visible show').css('display', 'block').attr('aria-modal', 'true').removeAttr('aria-hidden');
    $('body').addClass('modal-open');
    if (!$('.modal-backdrop').length) {
        $('<div class="modal-backdrop fade show"></div>').appendTo('body');
    } else {
        $('.modal-backdrop').addClass('show').show();
    }
}

function hideModalShell() {
    $('#modal').removeClass('kw-jq-visible show').css('display', 'none').attr('aria-hidden', 'true').removeAttr('aria-modal');
    $('body').removeClass('modal-open');
    $('.modal-backdrop').remove();
}

function openModalRequest(url, payload) {
    showModalShell();
    $('#load').html('<div class="modal-content"><div class="modal-body" style="padding:20px; text-align:center; font-weight:bold;">Loading...</div></div>');
    $.ajax({
        url: url,
        type: 'POST',
        data: payload,
        dataType: 'html'
    })
    .done(function (data) {
        $('#load').html(data);
        showModalShell();
    })
    .fail(function () {
        $('#load').html('<div class="modal-content"><div class="modal-body" style="padding:20px; text-align:center; font-weight:bold;">Something went wrong, Please try again...</div></div>');
        showModalShell();
    });
}

function req_navbar(id) {
    if (kugCatalogState.loaded) {
        var categoryId = parseInt(id, 10) || 0;
        var subcategories = kugCatalogState.subcategories.filter(function (sub) {
            return parseInt(sub.cat_id, 10) === categoryId;
        });
        kugSetHtmlTargets('#sub_cat, #sub_cat_mobile', renderKugSubcategories(subcategories));
        return;
    }
    kugApiRequest({
        data: { action: 'get_subcategories', id: id }
    }).done(function (res) {
        kugSetHtmlTargets('#sub_cat, #sub_cat_mobile', (res && res.data && res.data.html) ? res.data.html : '');
    });
}

function load_sub_food(id) {
    if (kugCatalogState.loaded) {
        var subId = parseInt(id, 10) || 0;
        kugSetHtmlTargets('#menu, #menu_mobile', renderKugItems(kugCatalogState.items.filter(function (item) {
            return parseInt(item.sub_id, 10) === subId;
        })));
        return;
    }
    kugApiRequest({
        data: { action: 'get_subcategory_items', id: id }
    }).done(function (res) {
        kugSetHtmlTargets('#menu, #menu_mobile', (res && res.data && res.data.html) ? res.data.html : '');
    });
}

function load_cat_food(id) {
    if (kugCatalogState.loaded) {
        var categoryId = parseInt(id, 10) || 0;
        kugSetHtmlTargets('#menu, #menu_mobile', renderKugItems(kugCatalogState.items.filter(function (item) {
            return parseInt(item.cat_id, 10) === categoryId;
        })));
        return;
    }
    kugApiRequest({
        data: { action: 'get_category_items', id: id }
    }).done(function (res) {
        kugSetHtmlTargets('#menu, #menu_mobile', (res && res.data && res.data.html) ? res.data.html : '');
    });
}

function req_action() {
    var item_id = $("#item_id").val();
    var qty = $("#req_qty").val();
    var price = $("#buy_price").val();
    var total = $("#buy_total").val();
    var unit = $("#buy_unit").val();
    var unit_qty = $("#buy_unit_qty").val();
    var unit_size = $("#buy_pack_size").val();
    var unit_cost = $("#buy_unit_cost").val();
    kugApiRequest({
        type: 'POST',
        data: {
            action: 'add_item',
            item_id: item_id,
            qty: qty,
            price: price,
            total: total,
            buy_unit: unit,
            buy_unit_qty: unit_qty,
            buy_pack_size: unit_size,
            buy_unit_cost: unit_cost
        }
    }).done(function (res) {
        if (res && res.ok) {
            kugSetHtmlTargets('#screen, #screen_mobile', (res.data && res.data.list_html) ? res.data.list_html : '');
            updateMobileCartSummary();
            $('#results').html('');
            hideModalShell();
            return;
        }
        $('#results').html('<div class="alert alert-danger"><h5><b>' + ((res && res.message) ? res.message : 'Failed to add the item.') + '</b></h5></div>');
    });
}

function load_item_list() {
    kugApiRequest({
        data: { action: 'get_list' }
    }).done(function (res) {
        kugSetHtmlTargets('#screen, #screen_mobile', (res && res.data && res.data.html) ? res.data.html : '');
        updateMobileCartSummary();
    });
}

function updateMobileCartSummary() {
    var rows = $('#screen table tbody tr');
    var itemCount = rows.length;
    var total = 0;

    rows.each(function () {
        var cellText = $.trim($(this).find('td').eq(4).text()).replace(/,/g, '');
        var value = parseFloat(cellText);
        if (!isNaN(value)) {
            total += value;
        }
    });

    $('#kw-cart-count').text(itemCount);
    $('#kw-cart-total').text(itemCount > 0 ? total.toFixed(0) : '0');
}

function del_item(id) {
    kugApiRequest({
        type: 'POST',
        data: { action: 'delete_item', id: id }
    }).done(function (res) {
        kugSetHtmlTargets('#screen, #screen_mobile', (res && res.data && res.data.list_html) ? res.data.list_html : '');
        updateMobileCartSummary();
    });
}

function loadKugBootstrap() {
    kugApiRequest({
        data: { action: 'bootstrap' }
    }).done(function (res) {
        if (!res || !res.ok) {
            if (res && res.data && res.data.redirect) {
                window.location = res.data.redirect;
                return;
            }
            return;
        }
        var data = res.data || {};
        if (!kugCatalogState.loaded) {
            kugSetHtmlTargets('#cat, #cat_desktop, #tot-category-desktop', data.categories_desktop_html || '');
            kugSetHtmlTargets('#cat_mobile, #tot-category-mobile', data.categories_mobile_html || data.categories_desktop_html || '');
            kugSetHtmlTargets('#menu, #menu_mobile', data.items_html || '');
        }
        kugSetHtmlTargets('#screen, #screen_mobile', data.list_html || '');
        $('#supplier-name').text(data.supplier_name || 'NONE');
        $('#supplier-name-mobile').text((data.supplier_name && data.supplier_name !== 'NONE') ? data.supplier_name : 'NO SUPPLIER SELECTED');
        var hasSupplier = data.supplier_name && data.supplier_name !== 'NONE';
        $('#tax-wrap, #tax-wrap-mobile').toggle(!!hasSupplier);
        $('#btn_sub, #btn_sub_mobile').toggleClass('btn-disabled-soft', !hasSupplier).prop('disabled', !hasSupplier);
        $('#buy-tax, #buy-tax-mobile').prop('checked', Number(data.context && data.context.buy_tax ? data.context.buy_tax : 0) === 1);
        updateMobileCartSummary();
    });
}

function kugCommonCatalogRequest() {
    return $.ajax({
        url: kugCommonCatalogUrl,
        type: 'GET',
        dataType: 'json',
        data: { action: 'fetch_catalog' }
    });
}

function renderKugSubcategories(subcategories) {
    var html = '';
    (subcategories || []).forEach(function (sub) {
        html += '<button type="button" class="kw-sub-btn kug-sub-btn" data-sub-id="' + parseInt(sub.sub_id, 10) + '">' +
            $('<div>').text(sub.name || '').html() + '</button>';
    });
    return html;
}

function renderKugItems(items) {
    var html = '';
    (items || []).forEach(function (item) {
        var color = String(item.stock_color || 'green').toLowerCase();
        if (color !== 'red' && color !== 'orange') color = 'green';
        html += '<button data-id="' + parseInt(item.item_id, 10) + '" class="btn btn-inverse additem kug-item-btn stock-' + color + '" id="getItem" type="button">' +
            $('<div>').text(item.name || '').html() + '</button>';
    });
    return html;
}

function renderKugCategories(categories, mobile) {
    var html = '';
    (categories || []).forEach(function (category) {
        html += '<button type="button" data-cat-id="' + parseInt(category.cat_id, 10) + '" class="' +
            (mobile ? 'kw-mobile-cat' : 'btn btn_nav') + '">' +
            $('<div>').text(category.name || '').html() + '</button>';
    });
    return html;
}

function loadKugCatalog() {
    kugCommonCatalogRequest().done(function (response) {
        if (!response || !response.ok || !response.data) {
            return;
        }
        var data = response.data;
        kugCatalogState.categories = data.categories || [];
        kugCatalogState.subcategories = data.subcategories || [];
        kugCatalogState.items = data.items || [];
        kugCatalogState.loaded = true;

        kugSetHtmlTargets('#cat, #cat_desktop, #tot-category-desktop', renderKugCategories(kugCatalogState.categories, false));
        kugSetHtmlTargets('#cat_mobile, #tot-category-mobile', renderKugCategories(kugCatalogState.categories, true));
        kugSetHtmlTargets('#menu, #menu_mobile', renderKugItems(kugCatalogState.items));
    });
}
