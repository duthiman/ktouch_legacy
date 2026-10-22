<?php
session_start();
include("../../inc/connection.php");
include("funcs.php");
$item_id = $_REQUEST['id'];

$get_qty = mysqli_query($connection, "SELECT port_status,portions FROM kp_items WHERE item_id='$item_id'") or die("Could not get the stock");
$row = mysqli_fetch_assoc($get_qty);
$port_status = $row['port_status'];
$portions = $row['portions'];
?>
<style type="text/css">
    .buy-modal-dialog {
        width: 820px;
        max-width: calc(100vw - 24px);
    }
    .buy-modal-header {
        padding: 12px 14px;
        background: linear-gradient(135deg, #7c0a20 0%, #b10421 50%, #f0a53a 100%);
        color: #fff8ea;
    }
    .buy-modal-header h2 {
        margin: 0;
        font-size: 28px;
        font-weight: 900;
        line-height: 1.15;
    }
    .buy-modal-body {
        background: linear-gradient(180deg, #fffaf3 0%, #fff5e5 100%);
        padding: 14px;
    }
    .buy-modal-layout {
        display: flex;
        gap: 18px;
        align-items: flex-start;
    }
    .buy-modal-pad {
        width: 278px;
        flex: 0 0 278px;
        padding: 12px;
        border-radius: 18px;
        background: #ffffff;
        border: 2px solid #f1d4a6;
        box-shadow: 0 14px 26px rgba(122, 61, 11, 0.10);
    }
    .buy-modal-form {
        flex: 1 1 auto;
        min-width: 0;
    }
    .buy-form-card {
        background: rgba(255,255,255,0.96);
        border-radius: 18px;
        border: 2px solid #f1d4a6;
        box-shadow: 0 14px 26px rgba(122, 61, 11, 0.10);
        padding: 12px 14px 16px;
        display: flex;
        flex-direction: column;
        gap: 3px;
    }
    .buy-form-card > .buy-row:first-of-type { order: 1; }
    .buy-form-card > .buy-row:nth-of-type(2) { order: 2; }
    .buy-form-card > .buy-row:nth-of-type(3) { order: 3; }
    .buy-form-card > .buy-total-strip { order: 4; }
    .buy-form-card > .buy-section-copy { order: 5; }
    .buy-row {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
        margin: 0;
        width: 100%;
    }
    .buy-col-half {
        width: 100%;
        max-width: none;
        flex: none;
        padding-left: 0 !important;
        padding-right: 0 !important;
    }
    .buy-field {
        margin-top: 7px;
    }
    .buy-field label {
        display: block;
        margin-bottom: 6px;
        color: #7c0a20;
        font-size: 12px;
        font-weight: 900;
        letter-spacing: 0.05em;
        text-transform: uppercase;
    }
    .buy-input,
    .buy-select {
        height: 50px;
        width: 100%;
        min-width: 0;
        font-size: 18px;
        font-weight: 900;
        border-radius: 14px;
        border: 2px solid #e7c891;
        color: #5f2e09;
        background: #fffdfa;
        box-shadow: inset 0 1px 2px rgba(0,0,0,0.04);
        padding-top: 6px;
        padding-bottom: 6px;
        white-space: normal;
    }
    .buy-select option {
        color: #5f2e09;
        background: #fffdfa;
        font-size: 17px;
        font-weight: 700;
    }
    @media screen and (min-width: 768px) {
        select.form-control:not([size]):not([multiple]) {
            height: 49px;
        }
    }
    .buy-modal-dialog,
    .buy-modal-dialog .modal-body,
    .buy-form-card {
        overflow: visible;
    }
    .buy-input:focus,
    .buy-select:focus {
        border-color: #b10421;
        box-shadow: 0 0 0 0.2rem rgba(177, 4, 33, 0.10);
    }
    .buy-readonly {
        background: #f9efe0;
        color: #7b4f18;
    }
    .buy-section-copy {
        margin: 7px 0 0;
        padding: 8px 10px;
        border-radius: 10px;
        background: #fff7d6;
        color: #7d4b00;
        font-size: 14px;
        font-weight: 700;
    }
    .buy-total-strip {
        margin-top: 10px;
        padding: 12px 14px;
        border-radius: 12px;
        background: linear-gradient(135deg, #14532d 0%, #2f7a31 42%, #61b846 100%);
        color: #fff;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 14px;
    }
    .buy-total-strip > div {
        flex: 1 1 auto;
        min-width: 0;
        display: flex;
        flex-direction: column;
        justify-content: center;
        gap: 5px;
    }
    .buy-total-strip small {
        display: block;
        font-size: 12px;
        font-weight: 900;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        opacity: 0.92;
    }
    .buy-total-strip strong {
        display: block;
        font-size: 24px;
        font-weight: 900;
        line-height: 1;
        white-space: nowrap;
    }
    .buy-total-strip input {
        border: 0;
        background: transparent;
        color: #fff;
        font-size: 34px;
        font-weight: 900;
        text-align: right;
        width: 230px;
        max-width: 45%;
        min-width: 0;
        line-height: 1;
        height: 42px;
        display: block;
        margin: 0;
        padding: 0;
        box-shadow: none;
    }
    .buy-total-strip input:focus {
        outline: none;
        box-shadow: none;
    }
    .numpad-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 7px;
    }
    .num {
        height: 72px;
        width: 100%;
        border-radius: 12px;
        font-size: 1rem;
        border: solid 3px transparent;
        background-image: linear-gradient(#fff, #fff), linear-gradient(135deg, #b10421 0%, #f0a53a 100%);
        background-origin: border-box;
        background-clip: padding-box, border-box;
        text-align: center;
        box-shadow: 0 10px 16px rgba(122, 61, 11, 0.10);
    }
    .number {
        font-weight: 900;
        margin-top: 18%;
        color: #7c0a20;
        font-size: 27px;
    }
    .span4 {
        width: 100%;
        margin: 0;
    }
    .buy-modal-footer {
        background: #fffaf3;
        padding: 12px 14px;
    }
    @media (max-width: 767px) {
        .buy-modal-dialog {
            width: calc(100vw - 20px);
            max-width: calc(100vw - 20px);
        }
        .buy-modal-layout {
            display: block;
        }
        .buy-modal-pad {
            display: none;
        }
        .buy-form-card {
            padding: 10px 12px 14px;
        }
        .buy-col-half {
            width: 100%;
            max-width: none;
        }
        .buy-total-strip {
            display: block;
        }
        .buy-total-strip input {
            width: 100%;
            max-width: none;
            text-align: left;
            margin-top: 8px;
        }
    }
</style>
<div class="modal-content buy-modal-dialog">
    <div class="modal-header buy-modal-header">
        <h2 class="text-center">ADD BUY ITEM<br><u><?php echo get_column('kp_items', 'name', "item_id='$item_id'"); ?></u></h2>
    </div>
    <div class="modal-body buy-modal-body">
        <?php if ($port_status) { ?>
        <div class="form-group">
            <p style="margin: 0px; color: #0f4c81; font-weight: 700;">This item has portions. Buy using the portions <?php echo $portions; ?>.</p>
        </div>
        <?php } ?>
        <div id="results"></div>

        <div class="buy-modal-layout">
            <div class="buy-modal-pad" id="desktop-numpad-wrap">
                <div class="numpad-grid" id="numpad">
                    <?php foreach (array('7','8','9','4','5','6','1','2','3') as $num) { ?>
                    <div class="span4">
                        <div class="num">
                            <h2 class="number"><?php echo $num; ?></h2>
                            <div class="txt" style="display:none"><?php echo $num; ?></div>
                        </div>
                    </div>
                    <?php } ?>
                    <div class="span4">
                        <button type="button" id="clear" class="btn" style="background:linear-gradient(135deg,#f0a53a 0%,#f36c26 100%); padding: 0px; font-weight: 900; color:#402100; height: 88px; width:100%; border:0; border-radius: 16px;">CLEAR</button>
                    </div>
                    <div class="span4">
                        <div class="num">
                            <h2 class="number">0</h2>
                            <div class="txt" style="display:none">0</div>
                        </div>
                    </div>
                    <div class="span4">
                        <div class="num">
                            <h2 class="number">.</h2>
                            <div class="txt" style="display:none">.</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="buy-modal-form">
                <div class="buy-form-card">
                    <input type="hidden" class="form-control" id="item_id" value="<?php echo $item_id; ?>">

                    <div class="row buy-row">
                        <div class="col-6 buy-col-half buy-field" style="padding-right: 4px;">
                            <label><b>BUY AS</b></label>
                            <select id="buy_unit" class="form-control buy-select calc-field">
                                <option value="pcs">Pieces (PCS)</option>
                                <option value="crates">Crates</option>
                                <option value="boxes">Boxes</option>
                                <option value="packs">Packs</option>
                            </select>
                        </div>
                        <div class="col-6 buy-col-half buy-field" id="buy-unit-qty-wrap" style="padding-left: 4px;">
                            <label><b id="buy-unit-qty-label">NO. OF PCS</b></label>
                            <input type="number" inputmode="decimal" step="any" name="buy_unit_qty" id="buy_unit_qty" class="form-control buy-input calc-field calc-input" autofocus>
                        </div>
                    </div>

                    <div class="row buy-row" id="unit-pack-row">
                        <div class="col-6 buy-col-half buy-field" style="padding-right: 4px;" id="pack-size-wrap">
                            <label><b id="buy-pack-size-label">PCS PER CRATE</b></label>
                            <input type="number" inputmode="decimal" step="any" name="buy_pack_size" id="buy_pack_size" class="form-control buy-input calc-field calc-input">
                        </div>
                        <div class="col-6 buy-col-half buy-field" style="padding-left: 4px;" id="unit-cost-wrap">
                            <label><b id="buy-unit-cost-label">COST PER CRATE</b></label>
                            <input type="number" inputmode="decimal" step="any" name="buy_unit_cost" id="buy_unit_cost" class="form-control buy-input calc-field calc-input">
                        </div>
                    </div>

                    <div class="row buy-row">
                        <div class="col-6 buy-col-half buy-field" style="padding-right: 4px;">
                            <label><b>TOTAL PCS</b></label>
                            <input type="number" inputmode="decimal" step="any" name="req_qty" id="req_qty" class="form-control buy-input calc-field calc-input">
                        </div>
                        <div class="col-6 buy-col-half buy-field" style="padding-left: 4px;">
                            <label><b>PRICE PER PIECE</b></label>
                            <input type="number" inputmode="decimal" step="any" name="buy_price" id="buy_price" class="form-control buy-input calc-field calc-input">
                        </div>
                    </div>

                    <div class="buy-total-strip">
                        <div>
                            <small>Grand Total</small>
                            <strong id="buy-total-title">AUTO CALCULATED</strong>
                        </div>
                        <input type="text" name="buy_total" id="buy_total" readonly value="">
                    </div>

                    <div class="buy-section-copy">
                        <span id="buy-helper-text">Enter pcs and price per piece, or enter crate details and let the system calculate automatically.</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-footer buy-modal-footer">
        <button type="button" data-dismiss="modal" class="btn btn-outline-danger btn-lg">CLOSE</button>
        <button type="submit" onclick="req_action()" class="btn btn-outline-success btn-lg">SUBMIT</button>
    </div>
</div>
<script type="text/javascript">
$(document).ready(function () {
    var activeField = '#buy_unit_qty';
    var syncing = false;

    function parseNumber(value) {
        var cleaned = String(value || '').replace(/,/g, '');
        var number = parseFloat(cleaned);
        return isNaN(number) ? 0 : number;
    }

    function hasValue(value) {
        return $.trim(String(value || '')) !== '';
    }

    function formatMoney(value) {
        return (Math.round(value * 100) / 100).toFixed(2);
    }

    function getUnitWords(unit) {
        if (unit === 'crates') {
            return { singular: 'crate', plural: 'crates' };
        }
        if (unit === 'boxes') {
            return { singular: 'box', plural: 'boxes' };
        }
        if (unit === 'packs') {
            return { singular: 'pack', plural: 'packs' };
        }
        return { singular: 'pc', plural: 'pcs' };
    }

    function isMobileLayout() {
        return window.matchMedia('(max-width: 767px)').matches;
    }

    function syncLabels() {
        var selectedUnit = $('#buy_unit').val();
        var unitWords = getUnitWords(selectedUnit);
        var isPieces = selectedUnit === 'pcs';

        $('#buy-unit-qty-label').text(isPieces ? 'NO. OF PCS' : 'NO. OF ' + unitWords.plural.toUpperCase());
        $('#buy-pack-size-label').text('PCS PER ' + unitWords.singular.toUpperCase());
        $('#buy-unit-cost-label').text(isPieces ? 'COST PER PC' : 'COST PER ' + unitWords.singular.toUpperCase());
        $('#buy-helper-text').text(
            isPieces
                ? 'Enter pcs and price per piece, or cost per pc. Total will calculate automatically.'
                : 'Enter number of ' + unitWords.plural + ', pieces per ' + unitWords.singular + ', cost per ' + unitWords.singular + ', or total pcs plus price per piece.'
        );

        if (isPieces) {
            $('#pack-size-wrap, #unit-cost-wrap').hide();
            $('#buy-unit-qty-wrap').removeClass('col-12').addClass('col-6 buy-col-half');
            $('#req_qty').prop('readonly', false).removeClass('buy-readonly');
        } else {
            $('#pack-size-wrap, #unit-cost-wrap').show();
            $('#buy-unit-qty-wrap').removeClass('col-12').addClass('col-6 buy-col-half');
            $('#req_qty').prop('readonly', false).removeClass('buy-readonly');
        }
    }

    function syncNumpadVisibility() {
        if (isMobileLayout()) {
            $('#desktop-numpad-wrap').hide();
        } else {
            $('#desktop-numpad-wrap').show();
        }
    }

    function fillIfEmpty(selector, value) {
        if (!hasValue($(selector).val()) && value > 0) {
            $(selector).val(formatMoney(value));
        }
    }

    function updateFromUnitFields(changedField) {
        var unitQty = parseNumber($('#buy_unit_qty').val());
        var packSize = parseNumber($('#buy_pack_size').val());
        var unitCost = parseNumber($('#buy_unit_cost').val());
        var selectedUnit = $('#buy_unit').val();

        if (selectedUnit === 'pcs') {
            packSize = 1;
            unitCost = parseNumber($('#buy_unit_cost').val()) || parseNumber($('#buy_price').val());
        }

        if (unitQty > 0 && packSize > 0 && changedField !== '#req_qty') {
            $('#req_qty').val(formatMoney(unitQty * packSize));
        }
        if (packSize > 0 && unitCost > 0 && changedField !== '#buy_price') {
            $('#buy_price').val(formatMoney(unitCost / packSize));
        }
        if (unitQty > 0 && unitCost > 0 && changedField !== '#buy_total') {
            $('#buy_total').val(formatMoney(unitQty * unitCost));
        }
    }

    function updateFromPieceFields(changedField) {
        var selectedUnit = $('#buy_unit').val();
        var unitQty = parseNumber($('#buy_unit_qty').val());
        var packSize = selectedUnit === 'pcs' ? 1 : parseNumber($('#buy_pack_size').val());
        var totalQty = parseNumber($('#req_qty').val());
        var piecePrice = parseNumber($('#buy_price').val());

        if (selectedUnit === 'pcs') {
            if (totalQty > 0 && changedField !== '#buy_unit_qty') {
                $('#buy_unit_qty').val(formatMoney(totalQty));
            }
            $('#buy_pack_size').val('1.00');
            if (piecePrice > 0 && changedField !== '#buy_unit_cost') {
                $('#buy_unit_cost').val(formatMoney(piecePrice));
            }
            if (piecePrice > 0 && changedField !== '#buy_total') {
                $('#buy_total').val(formatMoney(totalQty * piecePrice));
            }
            return;
        }

        if (unitQty > 0 && packSize > 0) {
            fillIfEmpty('#req_qty', unitQty * packSize);
            totalQty = parseNumber($('#req_qty').val());
        }

        if (unitQty > 0 && totalQty > 0 && !hasValue($('#buy_pack_size').val())) {
            $('#buy_pack_size').val(formatMoney(totalQty / unitQty));
            packSize = parseNumber($('#buy_pack_size').val());
        }

        if (packSize > 0 && totalQty > 0 && !hasValue($('#buy_unit_qty').val())) {
            $('#buy_unit_qty').val(formatMoney(totalQty / packSize));
            unitQty = parseNumber($('#buy_unit_qty').val());
        }

        if (packSize > 0 && piecePrice > 0 && changedField !== '#buy_unit_cost') {
            $('#buy_unit_cost').val(formatMoney(packSize * piecePrice));
        }

        if (totalQty > 0 && piecePrice > 0 && changedField !== '#buy_total') {
            $('#buy_total').val(formatMoney(totalQty * piecePrice));
        } else if (unitQty > 0 && parseNumber($('#buy_unit_cost').val()) > 0) {
            if (changedField !== '#buy_total') {
                $('#buy_total').val(formatMoney(unitQty * parseNumber($('#buy_unit_cost').val())));
            }
        }
    }

    function normalizePcsMode(changedField) {
        if ($('#buy_unit').val() === 'pcs') {
            $('#buy_pack_size').val('1.00');
            var piecePrice = parseNumber($('#buy_price').val());
            var unitCost = parseNumber($('#buy_unit_cost').val());
            if (piecePrice > 0 && unitCost <= 0) {
                $('#buy_unit_cost').val(formatMoney(piecePrice));
            }
            // Do not overwrite keypad input while the user is building a
            // multi-digit PCS value (for example 1, then 0, to make 10).
            if (changedField !== '#buy_unit_qty' && hasValue($('#req_qty').val())) {
                $('#buy_unit_qty').val($('#req_qty').val());
            }
        }
    }

    function updateFields(changedField) {
        if (syncing) {
            return;
        }
        syncing = true;

        syncLabels();
        normalizePcsMode(changedField);

        if (changedField === '#buy_price' || changedField === '#req_qty') {
            updateFromPieceFields(changedField);
        } else {
            updateFromUnitFields(changedField);
            updateFromPieceFields(changedField);
        }

        var totalQty = parseNumber($('#req_qty').val());
        var piecePrice = parseNumber($('#buy_price').val());
        if (totalQty > 0 && piecePrice > 0) {
            $('#buy_total').val(formatMoney(totalQty * piecePrice));
        }

        syncing = false;
    }

    syncLabels();
    syncNumpadVisibility();
    updateFields('#buy_unit_qty');

    $('.calc-input').on('focus', function () {
        activeField = '#' + $(this).attr('id');
    });

    $('#buy_unit').on('change', function () {
        if ($(this).val() === 'pcs') {
            $('#buy_pack_size').val('1.00');
        } else if (parseNumber($('#buy_pack_size').val()) <= 1) {
            $('#buy_pack_size').val('');
            $('#buy_unit_cost').val('');
        }
        activeField = '#buy_unit_qty';
        $('#buy_unit_qty').focus();
        updateFields('#buy_unit');
    });

    $('.calc-input').on('input', function () {
        updateFields('#' + $(this).attr('id'));
    });

    $('.num').click(function () {
        if (isMobileLayout()) {
            return;
        }
        var num = $(this);
        var text = $.trim(num.find('.txt').clone().children().remove().end().text());
        var field = $(activeField);
        if (!field.length || !field.is(':visible')) {
            return;
        }
        field.val(field.val() + text);
        updateFields(activeField);
    });

    $('#clear').click(function () {
        $('#buy_unit_qty, #buy_pack_size, #buy_unit_cost, #req_qty, #buy_price, #buy_total').val('');
        if ($('#buy_unit').val() === 'pcs') {
            $('#buy_pack_size').val('1.00');
        }
        $('#buy_unit_qty').focus();
        activeField = '#buy_unit_qty';
        updateFields('#buy_unit_qty');
    });

    $(window).on('resize', function () {
        syncNumpadVisibility();
    });
});
</script>
