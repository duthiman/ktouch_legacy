(function () {
    'use strict';

    var input = document.getElementById('item-search');
    var itemId = document.getElementById('item_id');
    var searchBox = input ? input.closest('.item-search') : null;
    var timer = null;
    var requestNumber = 0;
    var itemsBody = document.getElementById('po-items-body');
    var itemsTable = document.getElementById('po-items-table');
    var subtotalValue = document.getElementById('order-subtotal');
    var addButton = document.getElementById('add-selected-item');
    var addProductButton = document.getElementById('add-item');
    var quantityInput = document.getElementById('item-qty');
    var priceInput = document.getElementById('item-price');
    var summaryDiscountInput = document.getElementById('summary-discount');
    var shippingInput = document.getElementById('shipping-amount');
    var taxRateInput = document.getElementById('tax-rate-input');
    var totalSubtotal = document.getElementById('total-subtotal');
    var totalDiscount = document.getElementById('total-discount');
    var totalTaxable = document.getElementById('total-taxable');
    var totalVat = document.getElementById('total-vat');
    var totalGrand = document.getElementById('total-grand');
    var vatLabel = document.getElementById('vat-label');
    var orderSubtotal = 0;
    var taxType = 'inclusive';
    var supplierValue = document.getElementById('po-supplier');
    var dateValue = document.getElementById('po-date');
    var holdButton = document.getElementById('hold-order');
    var cancelButton = document.getElementById('cancel-order');
    var submitButton = document.getElementById('submit-order');
    var dateModalHost = document.createElement('div');
    dateModalHost.className = 'ord-date-modal-host';
    document.body.appendChild(dateModalHost);
    var pageParams = new URLSearchParams(window.location.search);
    var pagePoId = (itemsTable && itemsTable.dataset.poId) || pageParams.get('po_id') || pageParams.get('id') || '';

    function apiUrl(action) {
        var url = 'ord_api.php?action=' + encodeURIComponent(action);
        return pagePoId ? url + '&po_id=' + encodeURIComponent(pagePoId) : url;
    }

    function openProductModal() {
        var modalHost = document.getElementById('load2');
        if (!modalHost) return;
        modalHost.innerHTML = '<div class="modal-content"><div class="modal-body text-center"><i class="fa fa-spinner fa-spin"></i> Loading...</div></div>';
        fetch('../../menu/prods_new.php')
            .then(function (response) {
                if (!response.ok) throw new Error('Could not load the product form.');
                return response.text();
            })
            .then(function (html) {
                modalHost.innerHTML = html;
                // prods_new.php is injected as HTML, so its inline initializer
                // does not run automatically. Provide its API URL and execute
                // the form initializer after the markup is in the DOM.
                window.menuApiUrl = '../../menu/API/menu_api.php';
                modalHost.querySelectorAll('script').forEach(function (script) {
                    if (script.textContent && window.jQuery) {
                        jQuery.globalEval(script.textContent);
                    }
                });
                if (typeof window.initQuickProductModal === 'function') {
                    window.initQuickProductModal();
                }
                if (window.jQuery && jQuery.fn.modal) {
                    jQuery('#modal2').modal('show');
                }
            })
            .catch(function (error) {
                modalHost.innerHTML = '<div class="modal-content"><div class="modal-body"><div class="alert alert-danger">' + error.message + '</div></div></div>';
                if (window.jQuery && jQuery.fn.modal) jQuery('#modal2').modal('show');
            });
    }

    function loadPoStatus() {
        fetch(apiUrl('po_status'), {
            headers: { Accept: 'application/json' }
        })
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('PO status request failed');
                }
                return response.json();
            })
            .then(function (response) {
                var status = response && response.status ? response.status : {};
                if (supplierValue) {
                    supplierValue.textContent = status.supplier || 'Not selected';
                    supplierValue.dataset.supId = status.sup_id || '';
                }
                if (dateValue) {
                    dateValue.textContent = status.date || 'Not set';
                }
            })
            .catch(function () {
                if (supplierValue) supplierValue.textContent = 'Not selected';
                if (dateValue) dateValue.textContent = 'Not set';
            });
    }

    function closeDateModal() {
        dateModalHost.classList.remove('is-open');
        dateModalHost.innerHTML = '';
    }

    function openDateModal() {
        if (!pagePoId) {
            window.alert('No PO ID was supplied in the page URL.');
            return;
        }

        fetch('siku.php?po_id=' + encodeURIComponent(pagePoId))
            .then(function (response) {
                if (!response.ok) throw new Error('Could not load date modal.');
                return response.text();
            })
            .then(function (html) {
                dateModalHost.innerHTML = html;
                dateModalHost.classList.add('is-open');

                var dateInput = dateModalHost.querySelector('#day');
                var saveButton = dateModalHost.querySelector('#new-day');
                var closeButtons = dateModalHost.querySelectorAll('[data-dismiss="modal"]');
                var currentDate = dateValue ? dateValue.textContent.trim() : '';

                if (dateInput && /^\d{4}-\d{2}-\d{2}$/.test(currentDate)) {
                    dateInput.value = currentDate;
                }
                closeButtons.forEach(function (button) {
                    button.addEventListener('click', closeDateModal);
                });
                if (saveButton) {
                    saveButton.addEventListener('click', function (event) {
                        event.preventDefault();
                        var day = dateInput ? dateInput.value : '';
                        if (!day) {
                            window.alert('Date cannot be empty.');
                            return;
                        }

                        saveButton.disabled = true;
                        fetch(apiUrl('update_date'), {
                            method: 'POST',
                            headers: { Accept: 'application/json' },
                            body: new URLSearchParams({ day: day })
                        })
                            .then(function (response) {
                                return response.json().then(function (data) {
                                    if (!response.ok || !data.ok) {
                                        throw new Error(data.message || 'Could not update date.');
                                    }
                                    return data;
                                });
                            })
                            .then(function (data) {
                                if (dateValue) dateValue.textContent = data.date;
                                closeDateModal();
                            })
                            .catch(function (error) {
                                window.alert(error.message || 'Could not update date.');
                                saveButton.disabled = false;
                            });
                    });
                }
            })
            .catch(function (error) {
                window.alert(error.message || 'Could not load date modal.');
            });
    }

    function openSupplierModal() {
        if (!pagePoId) {
            window.alert('No PO ID was supplied in the page URL.');
            return;
        }

        fetch('sup_change.php?po_id=' + encodeURIComponent(pagePoId))
            .then(function (response) {
                if (!response.ok) throw new Error('Could not load supplier modal.');
                return response.text();
            })
            .then(function (html) {
                dateModalHost.innerHTML = html;
                dateModalHost.classList.add('is-open');

                var select = dateModalHost.querySelector('#sup-chg-sel');
                var saveButton = dateModalHost.querySelector('#btn-sup-chg');
                var closeButtons = dateModalHost.querySelectorAll('[data-dismiss="modal"], [data-bs-dismiss="modal"]');
                closeButtons.forEach(function (button) {
                    button.addEventListener('click', closeDateModal);
                });
                if (select && supplierValue && supplierValue.dataset.supId) {
                    select.value = supplierValue.dataset.supId;
                }
                if (select && saveButton) {
                    select.addEventListener('change', function () {
                        saveButton.disabled = !select.value;
                    });
                    saveButton.addEventListener('click', function () {
                        if (!select.value) return;
                        saveButton.disabled = true;
                        fetch(apiUrl('update_supplier'), {
                            method: 'POST',
                            headers: { Accept: 'application/json' },
                            body: new URLSearchParams({ sup_id: select.value })
                        })
                            .then(function (response) {
                                return response.json().then(function (data) {
                                    if (!response.ok || !data.ok) {
                                        throw new Error(data.message || 'Could not update supplier.');
                                    }
                                    return data;
                                });
                            })
                            .then(function (data) {
                                if (supplierValue) supplierValue.textContent = data.supplier;
                                closeDateModal();
                            })
                            .catch(function (error) {
                                window.alert(error.message || 'Could not update supplier.');
                                saveButton.disabled = false;
                            });
                    });
                }
            })
            .catch(function (error) {
                window.alert(error.message || 'Could not load supplier modal.');
            });
    }

    function openHoldModal() {
        if (!pagePoId) {
            window.alert('No PO ID was supplied in the page URL.');
            return;
        }

        fetch('hold_modal.php?po_id=' + encodeURIComponent(pagePoId))
            .then(function (response) {
                if (!response.ok) throw new Error('Could not load hold modal.');
                return response.text();
            })
            .then(function (html) {
                dateModalHost.innerHTML = html;
                dateModalHost.classList.add('is-open');

                var confirmButton = dateModalHost.querySelector('#btn-hold-confirm');
                var closeButtons = dateModalHost.querySelectorAll('[data-dismiss="modal"], [data-bs-dismiss="modal"]');
                closeButtons.forEach(function (button) {
                    button.addEventListener('click', closeDateModal);
                });
                if (confirmButton) {
                    confirmButton.addEventListener('click', function () {
                        confirmButton.disabled = true;
                        fetch(apiUrl('hold_order'), {
                            method: 'POST',
                            headers: { Accept: 'application/json' }
                        })
                            .then(function (response) {
                                return response.json().then(function (data) {
                                    if (!response.ok || !data.ok) {
                                        throw new Error(data.message || 'Could not hold order.');
                                    }
                                    return data;
                                });
                            })
                            .then(function (data) {
                                window.location.href = data.redirect || '../index.php';
                            })
                            .catch(function (error) {
                                window.alert(error.message || 'Could not hold order.');
                                confirmButton.disabled = false;
                            });
                    });
                }
            })
            .catch(function (error) {
                window.alert(error.message || 'Could not load hold modal.');
            });
    }

    function openCancelModal() {
        if (!pagePoId) {
            window.alert('No PO ID was supplied in the page URL.');
            return;
        }

        fetch('cancel_modal.php?po_id=' + encodeURIComponent(pagePoId))
            .then(function (response) {
                if (!response.ok) throw new Error('Could not load cancel modal.');
                return response.text();
            })
            .then(function (html) {
                dateModalHost.innerHTML = html;
                dateModalHost.classList.add('is-open');
                dateModalHost.querySelectorAll('[data-dismiss="modal"], [data-bs-dismiss="modal"]').forEach(function (button) {
                    button.addEventListener('click', closeDateModal);
                });
            })
            .catch(function (error) {
                window.alert(error.message || 'Could not load cancel modal.');
            });
    }

    function openConfirmModal() {
        if (!pagePoId) {
            window.alert('No PO ID was supplied in the page URL.');
            return;
        }

        var numberFromField = function (field) {
            return field ? (Number(field.value) || 0) : 0;
        };
        var numberFromText = function (field) {
            return field ? (Number(String(field.textContent).replace(/[^0-9.-]/g, '')) || 0) : 0;
        };

        var payload = {
            po_id: pagePoId,
            disc: numberFromField(summaryDiscountInput),
            vatable: numberFromText(totalTaxable),
            vatt: numberFromText(totalVat),
            tt: numberFromText(totalGrand),
            perc: numberFromField(taxRateInput),
            tax_type: taxType,
            po_date: dateValue ? dateValue.textContent.trim() : '',
            sup: supplierValue ? (supplierValue.dataset.supId || supplierValue.textContent.trim()) : '',
            supplier_ref: document.getElementById('supplier-ref') ? document.getElementById('supplier-ref').value : ''
        };

        var modal = document.getElementById('modal');
        var load = document.getElementById('load');
        if (!modal || !load) {
            window.alert('The confirmation modal container is missing.');
            return;
        }

        load.innerHTML = '<div class="p-3 text-center">Loading confirmation details...</div>';
        $('#modal').modal('show');

        $.ajax({
            url: 'confirm_modal.php',
            type: 'POST',
            data: payload,
            dataType: 'html'
        })
            .done(function (html) {
                load.innerHTML = html;
                load.querySelectorAll('[data-bs-dismiss="modal"]').forEach(function (button) {
                    button.addEventListener('click', function () {
                        $('#modal').modal('hide');
                    });
                });

                var confirmButton = document.getElementById('btn-confirm-lpo');
                if (confirmButton) {
                    confirmButton.addEventListener('click', function () {
                        var button = this;
                        var autoReceive = document.getElementById('confirm-auto-receive');
                        var updateBuyingPrice = document.getElementById('confirm-update-buying-price');

                        button.disabled = true;
                        button.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Submitting…';

                        $.ajax({
                            url: 'ord_api.php?action=confirm_lpo&po_id=' + encodeURIComponent(pagePoId),
                            type: 'POST',
                            dataType: 'json',
                            data: {
                                auto_receive: autoReceive && autoReceive.checked ? 1 : 0,
                                update_buying_price: updateBuyingPrice && updateBuyingPrice.checked ? 1 : 0,
                                disc: payload.disc,
                                vatable: payload.vatable,
                                vatt: payload.vatt,
                                tt: payload.tt,
                                perc: payload.perc,
                                tax_type: payload.tax_type,
                                po_date: payload.po_date,
                                supplier_ref: payload.supplier_ref
                            }
                        })
                            .done(function (response) {
                                if (!response || !response.ok) {
                                    throw new Error(response && response.message ? response.message : 'Could not confirm the LPO.');
                                }
                                if (window.Swal && typeof window.Swal.fire === 'function') {
                                    window.Swal.fire({
                                        icon: 'success',
                                        title: 'LPO Confirmed',
                                        text: response.message || 'The LPO was confirmed successfully.',
                                        timer: 1800,
                                        showConfirmButton: false
                                    }).then(function () {
                                        window.location.href = '../index.php';
                                    });
                                } else {
                                    window.location.href = '../index.php';
                                }
                            })
                            .fail(function (xhr) {
                                var response = xhr.responseJSON || {};
                                button.disabled = false;
                                button.innerHTML = 'CONFIRM LPO';
                                if (window.Swal && typeof window.Swal.fire === 'function') {
                                    window.Swal.fire({
                                        icon: 'error',
                                        title: 'Confirmation Failed',
                                        text: response.message || 'Could not confirm the LPO.'
                                    });
                                } else {
                                    window.alert(response.message || 'Could not confirm the LPO.');
                                }
                            });
                    });
                }
            })
            .fail(function () {
                load.innerHTML = '<div class="p-3 text-danger"><i class="fa fa-info-sign"></i> Something went wrong, please try again...</div>';
            });
    }

    if (!input || !searchBox) {
        return;
    }

    function escapeHtml(value) {
        return String(value == null ? '' : value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function money(value) {
        return 'KES ' + Number(value || 0).toLocaleString('en-KE', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    function itemTotalMoney(value) {
        return Number(value || 0).toLocaleString('en-KE', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    function renderTotals() {
        var discount = Math.max(0, Number(summaryDiscountInput ? summaryDiscountInput.value : 0) || 0);
        var shipping = Math.max(0, Number(shippingInput ? shippingInput.value : 0) || 0);
        var rate = Math.max(0, Number(taxRateInput ? taxRateInput.value : 0) || 0);
        var taxable = Math.max(0, orderSubtotal - discount + shipping);
        var vat = 0;

        if (taxType === 'inclusive') {
            vat = taxable * rate / (100 + rate);
        } else if (taxType === 'exclusive') {
            vat = taxable * rate / 100;
        }

        if (totalSubtotal) totalSubtotal.textContent = money(orderSubtotal);
        if (totalDiscount) totalDiscount.textContent = money(discount);
        if (totalTaxable) totalTaxable.textContent = money(taxable);
        if (totalVat) totalVat.textContent = money(vat);
        // With inclusive tax, VAT is already inside the taxable amount.
        // Do not add it again to the grand total.
        if (totalGrand) totalGrand.textContent = money(taxType === 'inclusive' ? taxable : taxable + vat);
        if (vatLabel) vatLabel.textContent = 'VAT (' + rate + '%)';
    }

    function renderPoItems(items, subtotal) {
        if (!itemsBody) {
            return;
        }

        if (!items.length) {
            itemsBody.innerHTML = '<tr><td colspan="6" class="empty-items">No PO items yet.</td></tr>';
        } else {
            itemsBody.innerHTML = items.map(function (item) {
                return '<tr>' +
                    '<td><div class="product"><div class="thumb">▦</div><div>' +
                    '<strong>' + escapeHtml(item.name) + '</strong>' +
                    '<div class="meta">' + escapeHtml(item.meta || 'Item ' + item.item_id) + '</div>' +
                    '</div></div></td>' +
                    '<td><input class="qty item-edit-input" type="number" min="0.01" step="0.01" value="' + escapeHtml(item.qty) + '" data-row-id="' + item.id + '"></td>' +
                    '<td><input class="price item-edit-input" type="number" min="0" step="0.01" value="' + escapeHtml(item.price) + '" data-row-id="' + item.id + '"></td>' +
                    '<td><input class="discount item-edit-input" type="number" min="0" step="0.01" value="' + escapeHtml(item.discount) + '" data-row-id="' + item.id + '"></td>' +
                    '<td class="money po-item-total">' + itemTotalMoney(item.total) + '</td>' +
                    '<td><button class="delete" type="button" data-row-id="' + item.id + '">♜</button></td>' +
                    '</tr>';
            }).join('');
        }

        orderSubtotal = Number(subtotal || 0);
        if (subtotalValue) {
            subtotalValue.textContent = money(subtotal);
        }
        renderTotals();
    }

    function loadPoItems() {
        fetch(apiUrl('po_items'), {
            headers: { Accept: 'application/json' }
        })
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('PO items request failed');
                }
                return response.json();
            })
            .then(function (response) {
                if (!response || !response.ok) {
                    throw new Error('PO items response was invalid');
                }
                renderPoItems(response.items || [], response.subtotal || 0);
            })
            .catch(function () {
                if (itemsBody) {
                    itemsBody.innerHTML = '<tr><td colspan="6" class="empty-items">Could not load PO items.</td></tr>';
                }
            });
    }

    function updatePoItem(input) {
        var rowId = input.getAttribute('data-row-id');
        var row = input.closest('tr');
        var qtyField = row ? row.querySelector('.qty') : null;
        var priceField = row ? row.querySelector('.price') : null;
        var qty = Number(qtyField ? qtyField.value : 0);
        var price = Number(priceField ? priceField.value : 0);
        var discountField = row ? row.querySelector('.discount') : null;
        var discount = Number(discountField ? discountField.value : 0);
        if (!rowId || !qty || qty <= 0 || price < 0 || discount < 0 || !Number.isFinite(price) || !Number.isFinite(discount)) {
            showMessage('Enter a valid quantity and price.');
            return;
        }
        input.disabled = true;
        fetch(apiUrl('update_item'), {
            method: 'POST',
            headers: { Accept: 'application/json' },
            body: new URLSearchParams({ id: rowId, qty: String(qty), price: String(price), discount: String(discount) })
        }).then(function(response) {
            return response.json().then(function(data) {
                if (!response.ok || !data.ok) throw new Error(data.message || 'Could not update item.');
                return data;
            });
        }).then(function(data) {
            var totalCell = row ? row.querySelector('.po-item-total') : null;
            if (totalCell) totalCell.textContent = itemTotalMoney(data.total);
            loadPoItems();
        }).catch(function(error) {
            showMessage(error.message || 'Could not update item.');
            loadPoItems();
        }).finally(function() {
            input.disabled = false;
        });
    }

    function addItem() {
        var selectedItemId = Number(itemId && itemId.value ? itemId.value : 0);
        var quantity = Number(quantityInput ? quantityInput.value : 0);
        var price = Number(priceInput ? priceInput.value : 0);

        if (!selectedItemId) {
            showMessage('Select an item from the search results first.');
            return;
        }
        if (!quantity || quantity <= 0 || price < 0 || !Number.isFinite(price)) {
            showMessage('Enter a valid quantity and price.');
            return;
        }

        if (addButton) {
            addButton.disabled = true;
            addButton.textContent = 'Adding...';
        }

        var body = new URLSearchParams({
            item_id: String(selectedItemId),
            qty: String(quantity),
            price: String(price)
        });

        fetch(apiUrl('add_item'), {
            method: 'POST',
            headers: { Accept: 'application/json' },
            body: body
        })
            .then(function (response) {
                return response.json().then(function (data) {
                    if (!response.ok || !data.ok) {
                        throw new Error(data.message || 'Could not add item.');
                    }
                    return data;
                });
            })
            .then(function () {
                input.value = '';
                input.dataset.itemId = '';
                if (itemId) {
                    itemId.value = '';
                }
                clearResults();
                loadPoItems();
            })
            .catch(function (error) {
                showMessage(error.message || 'Could not add item.');
            })
            .finally(function () {
                if (addButton) {
                    addButton.disabled = false;
                    addButton.textContent = '＋ Add selected';
                }
            });
    }

    loadPoItems();
    loadPoStatus();
    [summaryDiscountInput, shippingInput, taxRateInput].forEach(function (field) {
        if (field) {
            field.addEventListener('input', renderTotals);
        }
    });

    var priceCalculator = document.getElementById('price-calculator-modal');
    var openPriceCalculator = document.getElementById('open-price-calculator');
    var closePriceCalculator = document.getElementById('close-price-calculator');
    var cancelPriceCalculator = document.getElementById('cancel-price-calculator');
    var usePricePerPiece = document.getElementById('use-price-per-piece');
    var packagePieces = document.getElementById('package-pieces');
    var packageCount = document.getElementById('package-count');
    var packagePrice = document.getElementById('package-price');
    var packageTotal = document.getElementById('package-total');
    var packageUnit = document.getElementById('package-unit');
    var packagePiecesLabel = document.getElementById('package-pieces-label');
    var packageCountLabel = document.getElementById('package-count-label');
    var packagePriceLabel = document.getElementById('package-price-label');
    var pricePerPiece = document.getElementById('price-per-piece');
    var totalPackagePieces = document.getElementById('total-package-pieces');

    function closePriceCalculatorModal() {
        if (priceCalculator) {
            priceCalculator.classList.remove('is-open');
            priceCalculator.setAttribute('aria-hidden', 'true');
        }
    }

    function showSelectItemAlert() {
        if (window.Swal && typeof window.Swal.fire === 'function') {
            window.Swal.fire({
                icon: 'warning',
                title: 'Select an item first',
                text: 'Please select an item from the search results before using the price calculator.',
                confirmButtonText: 'OK'
            });
        } else {
            window.alert('Please select an item first.');
        }
    }

    function updatePackageLabels() {
        var unit = packageUnit ? packageUnit.value.toLowerCase() : 'crate';
        if (packagePiecesLabel) packagePiecesLabel.textContent = 'Pieces per ' + unit;
        if (packageCountLabel) packageCountLabel.textContent = 'Number of ' + unit + 's ordered';
        if (packagePriceLabel) packagePriceLabel.textContent = 'Price per ' + unit;
    }

    function calculatePricePerPiece(source) {
        var pieces = Number(packagePieces ? packagePieces.value : 0);
        var count = Number(packageCount ? packageCount.value : 0);
        var unitPrice = Number(packagePrice ? packagePrice.value : 0);
        var totalCost = Number(packageTotal ? packageTotal.value : 0);
        if (source === 'total' && count > 0 && totalCost >= 0) {
            unitPrice = totalCost / count;
            if (packagePrice) packagePrice.value = unitPrice.toFixed(2);
        } else if (source !== 'total' && packageTotal && count > 0 && unitPrice >= 0) {
            packageTotal.value = (unitPrice * count).toFixed(2);
        }
        var result = pieces > 0 && unitPrice >= 0 ? unitPrice / pieces : 0;
        var totalPieces = pieces > 0 && count > 0 ? pieces * count : 0;
        if (pricePerPiece) pricePerPiece.textContent = money(result);
        if (totalPackagePieces) totalPackagePieces.textContent = totalPieces.toLocaleString('en-KE');
        return result;
    }

    if (openPriceCalculator) {
        openPriceCalculator.addEventListener('click', function (event) {
            event.preventDefault();
            if (!itemId || !Number(itemId.value)) {
                showSelectItemAlert();
                return;
            }
            if (priceCalculator) {
                priceCalculator.classList.add('is-open');
                priceCalculator.setAttribute('aria-hidden', 'false');
            }
            calculatePricePerPiece('unit');
            if (packagePieces) packagePieces.focus();
        });
    }
    [packagePieces, packageCount, packagePrice].forEach(function (field) {
        if (field) field.addEventListener('input', function () { calculatePricePerPiece('unit'); });
    });
    if (packageTotal) packageTotal.addEventListener('input', function () { calculatePricePerPiece('total'); });
    if (packageUnit) packageUnit.addEventListener('change', updatePackageLabels);
    updatePackageLabels();
    [closePriceCalculator, cancelPriceCalculator].forEach(function (button) {
        if (button) button.addEventListener('click', closePriceCalculatorModal);
    });
    if (usePricePerPiece) {
        usePricePerPiece.addEventListener('click', function () {
            if (!itemId || !Number(itemId.value)) {
                showSelectItemAlert();
                return;
            }
            var result = calculatePricePerPiece(packageTotal && packageTotal.value ? 'total' : 'unit');
            var pieces = Number(packagePieces ? packagePieces.value : 0);
            var count = Number(packageCount ? packageCount.value : 0);
            var unitPrice = Number(packagePrice ? packagePrice.value : 0);
            if (pieces <= 0 || count <= 0 || unitPrice < 0 || !Number.isFinite(unitPrice)) {
                showMessage('Enter the pieces per unit, number of units, and unit price.');
                return;
            }
            if (quantityInput) quantityInput.value = String(pieces * count);
            if (priceInput) priceInput.value = result.toFixed(2);
            closePriceCalculatorModal();
            addItem();
        });
    }
    if (priceCalculator) {
        priceCalculator.addEventListener('click', function (event) {
            if (event.target === priceCalculator) closePriceCalculatorModal();
        });
    }

    var discountModal = document.getElementById('discount-modal');
    var discountMethod = document.getElementById('discount-method');
    var discountScope = document.getElementById('discount-scope');
    var discountValue = document.getElementById('discount-value');
    var discountPreview = document.getElementById('discount-preview');
    var activeDiscountInput = null;

    function closeDiscountModal() {
        if (discountModal) {
            discountModal.classList.remove('is-open');
            discountModal.setAttribute('aria-hidden', 'true');
        }
        activeDiscountInput = null;
    }

    function calculateDiscount() {
        if (!activeDiscountInput) return 0;
        var row = activeDiscountInput.closest('tr');
        var qty = Number(row && row.querySelector('.qty') ? row.querySelector('.qty').value : 0);
        var price = Number(row && row.querySelector('.price') ? row.querySelector('.price').value : 0);
        var value = Math.max(0, Number(discountValue ? discountValue.value : 0) || 0);
        var method = discountMethod ? discountMethod.value : 'amount';
        var scope = discountScope ? discountScope.value : 'price';
        var discount = value;
        if (method === 'percent') {
            discount = scope === 'total'
                ? (qty > 0 ? (qty * price * value / 100) / qty : 0)
                : price * value / 100;
        } else if (scope === 'total') {
            discount = qty > 0 ? value / qty : 0;
        }
        discount = Math.min(price, discount);
        if (discountPreview) {
            discountPreview.textContent = 'Discount per unit: ' + itemTotalMoney(discount) +
                ' | Whole line: ' + itemTotalMoney(discount * qty);
        }
        return discount;
    }

    document.addEventListener('click', function (event) {
        var discountInput = event.target.closest('#po-items-body .discount');
        if (!discountInput) return;
        activeDiscountInput = discountInput;
        if (discountValue) discountValue.value = discountInput.value || 0;
        if (discountModal) {
            discountModal.classList.add('is-open');
            discountModal.setAttribute('aria-hidden', 'false');
        }
        calculateDiscount();
    });

    [discountMethod, discountScope, discountValue].forEach(function (field) {
        if (field) field.addEventListener('input', calculateDiscount);
        if (field) field.addEventListener('change', calculateDiscount);
    });
    [document.getElementById('close-discount-modal'), document.getElementById('cancel-discount-modal')].forEach(function (button) {
        if (button) button.addEventListener('click', closeDiscountModal);
    });
    var applyDiscount = document.getElementById('apply-discount');
    if (applyDiscount) {
        applyDiscount.addEventListener('click', function () {
            if (!activeDiscountInput) return;
            var targetInput = activeDiscountInput;
            targetInput.value = calculateDiscount().toFixed(2);
            closeDiscountModal();
            updatePoItem(targetInput);
        });
    }

    document.addEventListener('change', function(event) {
        if (event.target.matches('#po-items-body .qty, #po-items-body .price, #po-items-body .discount')) {
            updatePoItem(event.target);
        }
    });

    document.querySelectorAll('[data-tax-type]').forEach(function (button) {
        button.addEventListener('click', function () {
            taxType = button.dataset.taxType || 'none';
            document.querySelectorAll('[data-tax-type]').forEach(function (item) {
                item.classList.toggle('active', item === button);
            });
            renderTotals();
        });
    });

    document.querySelectorAll('[data-tax-rate]').forEach(function (button) {
        button.addEventListener('click', function () {
            if (button.dataset.taxRate !== 'custom' && taxRateInput) {
                taxRateInput.value = button.dataset.taxRate;
            }
            document.querySelectorAll('[data-tax-rate]').forEach(function (item) {
                item.classList.toggle('active', item === button);
            });
            if (taxRateInput) taxRateInput.focus();
            renderTotals();
        });
    });

    if (dateValue) {
        dateValue.addEventListener('click', openDateModal);
        dateValue.addEventListener('keydown', function (event) {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                openDateModal();
            }
        });
    }

    if (supplierValue) {
        supplierValue.addEventListener('click', openSupplierModal);
        supplierValue.addEventListener('keydown', function (event) {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                openSupplierModal();
            }
        });
    }

    if (holdButton) {
        holdButton.addEventListener('click', openHoldModal);
    }

    if (cancelButton) {
        cancelButton.addEventListener('click', openCancelModal);
    }

    if (submitButton) {
        submitButton.addEventListener('click', openConfirmModal);
    }

    if (addButton) {
        addButton.addEventListener('click', addItem);
    }
    if (addProductButton) {
        addProductButton.addEventListener('click', openProductModal);
    }

    if (itemsBody) {
        itemsBody.addEventListener('click', function (event) {
            var deleteButton = event.target.closest('.delete');
            if (!deleteButton) {
                return;
            }

            var rowId = Number(deleteButton.dataset.rowId || 0);
            if (!rowId) {
                return;
            }

            function removeItem() {
                deleteButton.disabled = true;
                fetch(apiUrl('delete_item'), {
                method: 'POST',
                headers: { Accept: 'application/json' },
                body: new URLSearchParams({ id: String(rowId) })
                })
                .then(function (response) {
                    return response.json().then(function (data) {
                        if (!response.ok || !data.ok) {
                            throw new Error(data.message || 'Could not remove item.');
                        }
                        return data;
                    });
                })
                .then(function () {
                    loadPoItems();
                })
                .catch(function (error) {
                    window.alert(error.message || 'Could not remove item.');
                    deleteButton.disabled = false;
                });
            }

            if (window.Swal && typeof window.Swal.fire === 'function') {
                window.Swal.fire({
                    icon: 'warning',
                    title: 'Remove item?',
                    text: 'This item will be removed from the purchase order.',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, remove it',
                    cancelButtonText: 'Cancel'
                }).then(function (result) {
                    if (result && result.isConfirmed) removeItem();
                });
            } else if (window.confirm('Remove this item from the purchase order?')) {
                removeItem();
            }
        });
    }

    var results = document.createElement('div');
    results.className = 'item-autocomplete';
    results.hidden = true;
    searchBox.appendChild(results);

    function clearResults() {
        results.innerHTML = '';
        results.hidden = true;
    }

    function showMessage(message) {
        results.innerHTML = '';
        var row = document.createElement('div');
        row.className = 'item-autocomplete-message';
        row.textContent = message;
        results.appendChild(row);
        results.hidden = false;
    }

    function chooseItem(item) {
        input.value = item.name || '';
        input.dataset.itemId = item.id || '';
        if (itemId) {
            itemId.value = item.id || '';
        }
        clearResults();
    }

    function renderItems(items) {
        results.innerHTML = '';

        if (!items.length) {
            showMessage('No matching items found.');
            return;
        }

        items.forEach(function (item) {
            var row = document.createElement('button');
            row.type = 'button';
            row.className = 'item-autocomplete-option';
            row.textContent = item.name || '';
            row.addEventListener('click', function () {
                chooseItem(item);
            });
            results.appendChild(row);
        });

        results.hidden = false;
    }

    function searchItems(term) {
        var currentRequest = ++requestNumber;
        var url = '../../menu/API/menu_api.php?action=search&search=' + encodeURIComponent(term);

        fetch(url, {
            method: 'GET',
            headers: { Accept: 'application/json' }
        })
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('Search request failed');
                }
                return response.json();
            })
            .then(function (response) {
                if (currentRequest !== requestNumber) {
                    return;
                }
                var data = response && response.data ? response.data : {};
                renderItems(response && response.ok ? (data.items || []) : []);
            })
            .catch(function () {
                if (currentRequest === requestNumber) {
                    showMessage('Could not load items.');
                }
            });
    }

    input.addEventListener('input', function () {
        var term = input.value.trim();
        input.dataset.itemId = '';
        if (itemId) {
            itemId.value = '';
        }
        clearTimeout(timer);
        clearResults();

        if (term.length < 2) {
            return;
        }

        showMessage('Searching...');
        timer = setTimeout(function () {
            searchItems(term);
        }, 250);
    });

    document.addEventListener('click', function (event) {
        if (!searchBox.contains(event.target)) {
            clearResults();
        }
    });
}());
