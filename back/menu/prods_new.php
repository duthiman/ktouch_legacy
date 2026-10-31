<?php
// AJAX modal partial: load this file inside <div class="modal-dialog" id="modal-dialog">.
?>
<style>
  .quick-item-modal {
    --qi-teal: #07968e;
    --qi-teal-dark: #087f78;
    --qi-ink: #18304b;
    --qi-line: #d5e0e4;
    --qi-soft: #f7fafb;
    color: var(--qi-ink);
    overflow: hidden;
    border: 1px solid #b9d9da;
    border-radius: 15px;
    background: var(--qi-soft);
    box-shadow: 0 28px 80px rgba(1, 20, 24, .30);
  }
  /* Keep the item form inside the visible page and centered in the modal. */
  #modal:has(.quick-item-modal) .modal-dialog {
    width: min(900px, calc(100vw - 24px));
    max-width: none;
    margin: 12px auto;
  }
  .quick-item-modal .quick-item-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    min-height: 108px;
    padding: 20px 28px;
    color: #fff;
    border: 0;
    background: linear-gradient(100deg, #078f88, #12aca2);
  }
  .quick-item-modal .quick-item-heading { display: flex; align-items: center; gap: 18px; }
  .quick-item-modal .quick-item-icon {
    width: 58px; height: 58px; display: grid; place-items: center;
    color: var(--qi-teal); border-radius: 12px; background: #fff; font-size: 29px;
  }
  .quick-item-modal .quick-item-heading h4 { margin: 0; color: #fff; font-size: 29px; font-weight: 800; }
  .quick-item-modal .quick-item-heading small { display: block; margin-top: 4px; color: #e2fffc; font-size: 15px; }
  .quick-item-modal .quick-item-close {
    width: 45px; height: 45px; margin: 0; display: grid; place-items: center;
    float: none; color: #fff; border: 1px solid #fff; border-radius: 9px;
    background: transparent; opacity: 1; font-size: 29px; line-height: 1;
  }
  .quick-item-modal .quick-item-close:hover { color: #fff; background: rgba(255,255,255,.12); opacity: 1; }
  .quick-item-modal .quick-item-body { display: grid; gap: 17px; padding: 22px; background: var(--qi-soft); }
  .quick-item-modal .quick-item-body { max-height: calc(100vh - 190px); overflow-y: auto; overflow-x: hidden; }
  .quick-item-modal .quick-alerts:empty { display: none; }
  .quick-item-modal .quick-alerts { min-height: 0; margin: 0; }
  .quick-item-modal .quick-alerts .quick-validation-alert { margin: 0 0 8px; padding: 7px 10px; border-radius: 6px; font-size: 12px; line-height: 16px; }
  .quick-item-modal .quick-section {
    min-width: 0; margin: 0; padding: 20px 23px;
    border: 1px solid var(--qi-line); border-radius: 11px; background: #fff;
  }
  .quick-item-modal .quick-section-title {
    display: flex; align-items: center; gap: 10px; margin: 0 0 19px;
    color: var(--qi-teal-dark); font-size: 18px; font-weight: 900; text-transform: uppercase;
  }
  .quick-item-modal .quick-section-title i { width: 24px; text-align: center; font-size: 21px; }
  .quick-item-modal .quick-item-grid {
    display: grid; grid-template-columns: repeat(12, minmax(0, 1fr)); gap: 22px 24px;
  }
  .quick-item-modal .quick-price-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 24px; }
  .quick-item-modal .quick-settings-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; }
  .quick-item-modal .qi-col-12 { grid-column: span 12; }
  .quick-item-modal .qi-col-6 { grid-column: span 6; }
  .quick-item-modal .quick-field { min-width: 0; margin: 0; }
  .quick-item-modal .quick-field label { display: block; margin: 0 0 8px; color: var(--qi-ink); font-size: 14px; font-weight: 800; }
  .quick-item-modal .quick-required { color: #ef3e3e; }
  .quick-item-modal .quick-field .form-control,
  .quick-item-modal .quick-field .select2-container .select2-selection--single {
    width: 100%; height: 40px; color: #40556a; border: 1px solid #bdcbd2;
    border-radius: 8px; background: #fff; box-shadow: none;
  }
  .quick-item-modal .quick-field input.form-control { padding: 0 16px; }
  .quick-item-modal .quick-field .select2-container .select2-selection__rendered { padding: 0 38px 0 16px; line-height: 48px; }
  .quick-item-modal .quick-field .select2-container .select2-selection__arrow { height: 48px; right: 8px; }
  .quick-item-modal .quick-field .form-control:focus { border-color: var(--qi-teal); box-shadow: 0 0 0 3px rgba(7,150,142,.12); }
  .quick-item-modal .quick-combo { display: flex; align-items: stretch; gap: 10px; width: 100%; }
  .quick-item-modal .quick-combo > select,
  .quick-item-modal .quick-combo > .select2-container { flex: 1 1 auto; min-width: 0; width: 1% !important; }
  .quick-item-modal .quick-add {
    flex: 0 0 50px; width: 40px; height: 40px; display: grid; place-items: center;
    padding: 0; color: #fff; border: 0; border-radius: 8px;
    background: linear-gradient(#0aa79e, #078f88); font-size: 23px;
  }
  .quick-item-modal .quick-add:hover { color: #fff; background: var(--qi-teal-dark); }
  .quick-item-modal .quick-setting-list { display: grid; }
  .quick-item-modal .quick-setting-row {
    display: flex; align-items: center; justify-content: space-between; gap: 14px;
    min-height: 52px; border-bottom: 1px solid #d9e1e4;
  }
  .quick-item-modal .quick-setting-row:last-child { border-bottom: 0; }
  .quick-item-modal .quick-setting-label { font-weight: 700; }
  .quick-item-modal .quick-info-dot {
    display: inline-grid; place-items: center; width: 17px; height: 17px; margin-left: 4px;
    color: #168fa1; border: 1px solid #41aebe; border-radius: 50%; font-size: 11px;
  }
  .quick-item-modal .quick-choice { display: flex; align-items: center; gap: 18px; white-space: nowrap; }
  .quick-item-modal .quick-choice label { display: inline-flex; align-items: center; gap: 6px; margin: 0; font-weight: 700; }
  .quick-item-modal .quick-choice input { width: 20px; height: 20px; margin: 0; accent-color: var(--qi-teal); }
  .quick-item-modal .quick-stock-type { margin-bottom: 16px; }
  .quick-item-modal .quick-stock-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; }
  .quick-item-modal .quick-stock-note {
    display: flex; align-items: center; gap: 12px; margin-top: 17px; padding: 14px;
    color: #38576a; border: 1px solid #66cce5; border-radius: 8px; background: #effcff;
  }
  .quick-item-modal .quick-stock-note i {
    width: 25px; height: 25px; display: grid; place-items: center; flex: 0 0 25px;
    color: #fff; border-radius: 50%; background: var(--qi-teal);
  }
  .quick-item-modal .quick-item-footer {
    display: flex; align-items: center; justify-content: space-between; gap: 18px;
    padding: 17px 27px; border-top: 1px solid var(--qi-line); background: #fff;
  }
  .quick-item-modal .quick-footer-note { color: #526a7d; font-style: italic; }
  .quick-item-modal .quick-footer-buttons { display: flex; gap: 16px; margin-left: auto; }
  .quick-item-modal .quick-cancel,
  .quick-item-modal .quick-save {
    height: 48px; display: inline-flex; align-items: center; justify-content: center;
    padding: 0 27px; border: 1px solid var(--qi-teal); border-radius: 8px; font-weight: 800;
  }
  .quick-item-modal .quick-cancel { color: var(--qi-teal-dark); background: #fff; }
  .quick-item-modal .quick-save { min-width: 165px; color: #fff; background: linear-gradient(#0aa79e,#078f88); }
  .quick-item-modal .quick-save:hover { color: #fff; background: var(--qi-teal-dark); }
  .quick-item-modal .quick-save-new { height: 32px; padding: 0 14px; color: var(--qi-teal-dark); border: 1px solid var(--qi-teal); border-radius: 8px; background: #fff; font-size: 12px; font-weight: 800; }
  .quick-item-modal .quick-save-new:hover { color: #fff; background: var(--qi-teal); }
  .quick-item-modal .quick-field .form-control[disabled] { color: #9ba7ad; background: #edf1f2; cursor: not-allowed; }
  .quick-counter-stock { margin-top: 8px; padding-top: 7px; border-top: 1px solid #d9e1e4; }
  .quick-counter-title { margin-bottom: 5px; color: var(--qi-teal-dark); font-size: 11px; font-weight: 800; }
  .quick-counter-row { display: grid; grid-template-columns: 24px 1fr 105px 105px; align-items: center; gap: 8px; padding: 4px 0; font-size: 11px; }
  .quick-counter-row label { display: flex; align-items: center; gap: 4px; margin: 0; font-size: 10px; font-weight: 700; }
  .quick-counter-row input { width: 68px; height: 26px; padding: 2px 5px; border: 1px solid #bdcbd2; border-radius: 5px; }
  .quick-counter-row > input[type="checkbox"] { width: 18px; height: 18px; margin: 0; padding: 0; justify-self: center; accent-color: var(--qi-teal); }
  .quick-item-modal .quick-stock-controls { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 10px; }
  .quick-item-modal .quick-stock-controls .quick-stock-type { margin-bottom: 0; }

  /* After Item Details, keep stock on the right and tax/product settings on the left. */
  @media (min-width: 768px) {
    .quick-item-modal .quick-item-body { grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); }
    .quick-item-modal .quick-item-body > .quick-section:first-of-type { grid-column: 1 / -1; }
    .quick-item-modal .quick-item-body > .quick-section:nth-of-type(2) { grid-column: 1; }
    .quick-item-modal .quick-settings-grid { display: contents; }
    .quick-item-modal .quick-settings-grid > section:first-child { grid-column: 1; }
    .quick-item-modal .quick-settings-grid > section:last-child { grid-column: 2; grid-row: 2 / span 2; }
    .quick-item-modal .quick-price-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; }
    .quick-item-modal .quick-price-grid .quick-field:last-child { grid-column: span 1; }
  }

  /* Keep the complete form visible in a normal desktop viewport. */
  .quick-item-modal {
    width: min(880px, calc(100vw - 24px)) !important;
    max-height: calc(100vh - 24px);
  }
  .quick-item-modal .quick-item-header { min-height: 70px; padding: 10px 16px; }
  .quick-item-modal .quick-item-heading { gap: 10px; }
  .quick-item-modal .quick-item-icon { width: 40px; height: 40px; font-size: 20px; }
  .quick-item-modal .quick-item-heading h4 { font-size: 20px; }
  .quick-item-modal .quick-item-heading small { margin-top: 1px; font-size: 11px; }
  .quick-item-modal .quick-item-close { width: 32px; height: 32px; font-size: 22px; }
  .quick-item-modal .quick-item-body { gap: 8px; padding: 10px; }
  .quick-item-modal .quick-section { padding: 10px 13px; border-radius: 8px; }
  .quick-item-modal .quick-section-title { gap: 7px; margin-bottom: 8px; font-size: 13px; }
  .quick-item-modal .quick-section-title i { width: 18px; font-size: 15px; }
  .quick-item-modal .quick-item-grid { gap: 8px 14px; }
  .quick-item-modal .quick-price-grid { gap: 12px; }
  .quick-item-modal .quick-settings-grid { gap: 8px; }
  .quick-item-modal .quick-field label { margin-bottom: 3px; font-size: 11px; }
  .quick-item-modal .quick-field .form-control,
  .quick-item-modal .quick-field .select2-container .select2-selection--single { height: 30px; font-size: 12px; }
  .quick-item-modal .quick-field input.form-control { padding: 0 9px; }
  .quick-item-modal .quick-field .select2-container .select2-selection__rendered { padding: 0 28px 0 9px; line-height: 30px; }
  .quick-item-modal .quick-field .select2-container .select2-selection__arrow { height: 30px; right: 4px; }
  .quick-item-modal .quick-combo { gap: 6px; }
  .quick-item-modal .quick-add { flex-basis: 30px; width: 30px; height: 30px; font-size: 16px; }
  .quick-item-modal .quick-setting-row { min-height: 34px; gap: 8px; font-size: 11px; }
  .quick-item-modal .quick-choice { gap: 9px; }
  .quick-item-modal .quick-choice input { width: 15px; height: 15px; }
  .quick-item-modal .quick-choice label { gap: 3px; font-size: 11px; }
  .quick-item-modal .quick-stock-type { margin-bottom: 8px; }
  .quick-item-modal .quick-stock-grid { gap: 10px; }
  .quick-item-modal .quick-stock-note { gap: 7px; margin-top: 8px; padding: 7px; font-size: 10px; }
  .quick-item-modal .quick-stock-note i { width: 18px; height: 18px; flex-basis: 18px; }
  .quick-item-modal .quick-item-footer { gap: 10px; padding: 8px 13px; }
  .quick-item-modal .quick-footer-note { font-size: 11px; }
  .quick-item-modal .quick-footer-buttons { gap: 8px; }
  .quick-item-modal .quick-cancel,
  .quick-item-modal .quick-save { height: 32px; padding: 0 14px; font-size: 12px; }

  @media (max-width: 767px) {
    #modal:has(.quick-item-modal) .modal-dialog { width: calc(100vw - 16px); margin: 8px auto; }
    .quick-item-modal .quick-item-header { min-height: 82px; padding: 15px; }
    .quick-item-modal .quick-item-icon { width: 45px; height: 45px; font-size: 24px; }
    .quick-item-modal .quick-item-heading h4 { font-size: 21px; }
    .quick-item-modal .quick-item-heading small { font-size: 12px; }
    .quick-item-modal .quick-item-body { padding: 12px; }
    .quick-item-modal .quick-section { padding: 15px; }
    .quick-item-modal .quick-item-grid,
    .quick-item-modal .quick-price-grid,
    .quick-item-modal .quick-settings-grid,
    .quick-item-modal .quick-stock-grid { grid-template-columns: 1fr; }
    .quick-item-modal .qi-col-12,
    .quick-item-modal .qi-col-6 { grid-column: auto; }
    .quick-item-modal .quick-item-footer { align-items: flex-start; flex-direction: column; }
    .quick-item-modal .quick-footer-buttons { width: 100%; margin-left: 0; }
    .quick-item-modal .quick-footer-buttons .btn { flex: 1; padding: 0 12px; }
  }
</style>

<div class="modal-content quick-item-modal">
  <div class="modal-header quick-item-header">
    <div class="quick-item-heading">
      <span class="quick-item-icon"><i class="fa fa-plus"></i></span>
      <div>
        <h4 class="modal-title">Quick Add Item</h4>
        <small>Create a product and configure its menu settings.</small>
      </div>
    </div>
    <button type="button" class="close quick-item-close" data-dismiss="modal" aria-label="Close">&times;</button>
  </div>

  <form id="quick-form" novalidate>
    <div class="modal-body quick-item-body">
      <div id="item-results" class="quick-alerts"></div>

      <section class="quick-section">
        <h3 class="quick-section-title"><i class="fa fa-cubes"></i> Item Details</h3>
        <div class="quick-item-grid">
          <div class="quick-field qi-col-12">
            <label for="quick-name">Item Name <span class="quick-required">*</span></label>
            <input type="text" class="form-control" id="quick-name" name="name" placeholder="Enter the Item Name..." required>
            <p id="error-name" class="text-danger"></p>
          </div>

          <div class="quick-field qi-col-6">
            <label for="cat_id">Select Category</label>
            <div class="quick-combo">
              <select class="form-control pickers" id="cat_id" name="cat_id">
                <option value="0">--SELECT CATEGORY--</option>
              </select>
              <button type="button" class="btn quick-add" id="new-cat" aria-label="Add category"><i class="fa fa-plus"></i></button>
            </div>
          </div>

          <div class="quick-field qi-col-6">
            <label for="sub_id">Select Sub Category</label>
            <div class="quick-combo">
              <select class="form-control pickers" id="sub_id" name="sub_id">
                <option value="0">--SELECT A SUB CATEGORY--</option>
              </select>
              <button type="button" class="btn quick-add" id="new-sub" aria-label="Add subcategory"><i class="fa fa-plus"></i></button>
            </div>
          </div>

          <div class="quick-field qi-col-6">
            <label for="dpt">Department</label>
            <select class="form-control pickers" id="dpt" name="dpt">
              <option value="0">--Select a Department--</option>
              <option value="1">BAR</option>
              <option value="2">KITCHEN</option>
            </select>
          </div>

          <div class="quick-field qi-col-6">
            <label for="uom">Unit of Measure</label>
            <select class="form-control pickers" id="uom" name="uom">
              <option value="0">--Select a Unit--</option>
              <option value="1">Units</option>
              <option value="2">Litres</option>
              <option value="3">Kilograms</option>
            </select>
          </div>
        </div>
      </section>

      <section class="quick-section">
        <h3 class="quick-section-title"><i class="fa fa-tag"></i> Pricing &amp; Tax</h3>
        <div class="quick-price-grid">
          <div class="quick-field">
            <label for="bprice">Buying Price</label>
            <input type="number" class="form-control" id="bprice" name="bprice" min="0" step="0.01" placeholder="Enter the Buying Price">
          </div>
          <div class="quick-field">
            <label for="sprice">Selling Price <span class="quick-required">*</span></label>
            <input type="number" class="form-control" id="sprice" name="sprice" min="0" step="0.01" placeholder="Enter the Selling Price" required>
          </div>
          <div class="quick-field">
            <label for="tax_id">Select Tax</label>
            <select class="form-control pickers" id="tax_id" name="tax_id">
              <option value="0">--SELECT TAX--</option>
            </select>
          </div>
        </div>
      </section>

      <div class="quick-settings-grid">
        <section class="quick-section">
          <h3 class="quick-section-title"><i class="fa fa-cog"></i> Product Settings</h3>
          <div class="quick-setting-list">
            <div class="quick-setting-row">
              <span class="quick-setting-label">Sell in Butchery</span>
              <div class="quick-choice"><label><input type="checkbox" name="butchery_enabled" value="1"> Yes</label></div>
            </div>
            <div class="quick-setting-row">
              <span class="quick-setting-label">Show on menu <i class="quick-info-dot">i</i></span>
              <span class="quick-choice">
                <label><input type="radio" name="menu" value="1" id="myes" checked> Yes</label>
                <label><input type="radio" name="menu" value="2" id="mno"> No</label>
              </span>
            </div>
            <div class="quick-setting-row">
              <span class="quick-setting-label">Track inventory <i class="quick-info-dot">i</i></span>
              <span class="quick-choice">
                <label><input type="radio" name="track_inventory" value="1" id="track-yes" checked> Yes</label>
                <label><input type="radio" name="track_inventory" value="0" id="track-no"> No</label>
              </span>
            </div>
            <div class="quick-setting-row">
              <span class="quick-setting-label">Allow fractional/decimal quantities <i class="quick-info-dot">i</i></span>
              <span class="quick-choice">
                <label><input type="radio" name="point" value="1" id="yes" checked> Yes</label>
                <label><input type="radio" name="point" value="2" id="no"> No</label>
              </span>
            </div>
            <div class="quick-setting-row">
              <span class="quick-setting-label">Happy hour pricing <i class="quick-info-dot">i</i></span>
              <span class="quick-choice">
                <label><input type="radio" name="happy_hour" value="1" id="happy-yes" checked> Yes</label>
                <label><input type="radio" name="happy_hour" value="0" id="happy-no"> No</label>
              </span>
            </div>
          </div>
        </section>

        <section class="quick-section">
          <h3 class="quick-section-title"><i class="fa fa-archive"></i> Stock Settings</h3>
          <div class="quick-stock-controls">
            <div class="quick-field quick-stock-type">
              <label for="stock">Stock Type</label>
              <select class="form-control" id="stock" name="stock">
                <option value="1" selected>Inventory</option>
                <option value="2">Non Inventory</option>
                <option value="3">Service</option>
              </select>
            </div>
            <div class="quick-field">
              <label for="stock_qty">Current Store Stock</label>
              <input type="number" class="form-control" id="stock_qty" name="stock_qty" min="0" step="0.001" value="100">
            </div>
            <div class="quick-field">
              <label for="reorder">Store Reorder Level</label>
              <input type="number" class="form-control" id="reorder" name="reorder" min="0" step="0.001" value="10">
            </div>
          </div>
          <div class="quick-stock-note"><i class="fa fa-info"></i><span id="quick-stock-note-text">Stock quantities are enabled only for Inventory items.</span></div>
          <div id="quick-counter-stock" class="quick-counter-stock"></div>
        </section>
      </div>
    </div>

    <div class="modal-footer quick-item-footer">
      <span class="quick-footer-note">Complete required fields before saving.</span>
      <div class="quick-footer-buttons">
        <button type="button" class="btn quick-cancel" data-dismiss="modal">Cancel</button>
        <button type="submit" name="new_prod_add" id="new_prod_add" class="btn quick-save-new"><i class="fa fa-plus"></i>&nbsp; Save &amp; Add New</button>
        <button type="submit" name="new_prod" id="new_prod" class="btn quick-save"><i class="fa fa-check"></i>&nbsp; Save Item</button>
      </div>
    </div>
  </form>
</div>

<script>
(function ($) {
  'use strict';

  var $form = $('#quick-form');
  var $modal = $('#modal');
  var $stockType = $('#stock');
  var $stockQty = $('#stock_qty');
  var $reorder = $('#reorder');
  var $trackYes = $('#track-yes');
  var $trackNo = $('#track-no');
  var $stockNote = $('#quick-stock-note-text');
  var keepModalOpen = false;

  function renderCounterStock() {
    var counters = (window.menuBootstrapCache && window.menuBootstrapCache.counters) || [];
    var $target = $('#quick-counter-stock').empty();
    if (!counters.length) return;
    $target.append('<div class="quick-counter-title">Counter stock and reorder levels</div>');
    counters.forEach(function (counter) {
      var id = parseInt(counter.id, 10);
      if (!id) return;
      var label = $('<div>').text(counter.name || ('Counter ' + id)).html();
      $target.append('<div class="quick-counter-row"><input type="checkbox" class="quick-counter-check" name="counter_assign[]" value="' + id + '" aria-label="Assign to ' + label + '">' +
        '<strong>' + label + '</strong>' +
        '<label>Stock <input type="number" min="0" step="0.001" name="counter_stock[' + id + ']" value="100" disabled></label>' +
        '<label>Reorder <input type="number" min="0" step="0.001" name="counter_reorder[' + id + ']" value="10" disabled></label></div>');
    });
    $target.off('change.quickCounter').on('change.quickCounter', '.quick-counter-check', function () {
      $(this).closest('.quick-counter-row').find('input[type="number"]').prop('disabled', !this.checked);
    });
  }

  function initSelect2() {
    if (!$.fn.select2) return;
    $form.find('.pickers').each(function () {
      var $select = $(this);
      if ($select.hasClass('select2-hidden-accessible')) $select.select2('destroy');
      $select.select2({ width: '100%', dropdownParent: $modal });
    });
  }

  function syncStockType() {
    var type = String($stockType.val() || '1');
    var isInventory = type === '1';
    $stockQty.prop('disabled', !isInventory);
    $reorder.prop('disabled', !isInventory);
    $trackYes.prop('disabled', !isInventory).prop('checked', isInventory);
    $trackNo.prop('checked', !isInventory);
    if (!isInventory) {
      $stockQty.val(0);
      $reorder.val(0);
    }
    var label = $stockType.find('option:selected').text() || 'selected';
    $stockNote.text(isInventory
      ? 'Stock quantities are enabled only for Inventory items.'
      : 'Stock quantities are disabled for ' + label + ' items.');
  }

  $('#cat_id').off('change.quickItem').on('change.quickItem', function () {
    var selected = $(this).val();
    var $sub = $('#sub_id').prop('disabled', false).empty();
    if (typeof window.fillSubCategoryOptions === 'function') {
      window.fillSubCategoryOptions('#sub_id', selected, 0);
    } else {
      $sub.append('<option value="0">--SELECT A SUB CATEGORY--</option>');
    }
  });

  $stockType.off('change.quickItem').on('change.quickItem', syncStockType);

  if (typeof $.fn.get_cat === 'function') $.fn.get_cat();
  initSelect2();
  syncStockType();
  renderCounterStock();

  if ($.fn.autocomplete) {
    $('#quick-name').autocomplete({
      appendTo: '#modal',
      source: function (request, response) {
        $.ajax({
          url: window.menuApiUrl,
          type: 'GET',
          dataType: 'json',
          data: { action: 'search', search: request.term, start: 0, length: 20 }
        }).done(function (res) {
          var items = res && res.data && Array.isArray(res.data.items) ? res.data.items : [];
          response(items.map(function (item) {
            var label = item.name || item.item_name || '';
            return { label: label, value: label };
          }));
        }).fail(function () {
          response([]);
        });
      }
    });
  }

  $form.off('submit.quickItem').on('submit.quickItem', function (event) {
    event.preventDefault();
    var form = this;
    if (!form.checkValidity()) {
      form.reportValidity();
      return;
    }

    keepModalOpen = event.originalEvent && event.originalEvent.submitter && event.originalEvent.submitter.id === 'new_prod_add';
    $('#new_prod, #new_prod_add').prop('disabled', true);
    $('#item-results').empty();
    $.ajax({
      url: window.menuApiUrl,
      type: 'POST',
      data: $form.serialize() + '&action=create_item',
      dataType: 'json'
    }).done(function (res) {
      if (res && res.ok) {
        if (typeof window.hullabaloo === 'function') {
          new window.hullabaloo().send(res.message || 'Item Added Successfully', 'success');
        }
        if (keepModalOpen) {
          $form[0].reset();
          $('#cat_id, #sub_id, #tax_id, #uom').val('0').trigger('change');
          $('#stock_qty').val(100);
          $('#reorder').val(10);
          $('#item-results').empty();
          renderCounterStock();
          syncStockType();
          $('#quick-name').trigger('focus');
        } else {
          $modal.modal('hide');
        }
        if ($.fn.DataTable && $.fn.DataTable.isDataTable('#data-table-combine')) {
          $('#data-table-combine').DataTable().ajax.reload(null, false);
        }
        return;
      }
      var message = res && res.message ? res.message : 'Error occurred.';
      $('#item-results').html('<div class="alert alert-danger quick-validation-alert" role="alert"><strong>Warning!</strong> ' + $('<div>').text(message).html() + '</div>');
    }).fail(function () {
      $('#item-results').html('<div class="alert alert-danger quick-validation-alert" role="alert"><strong>Warning!</strong> Error occurred.</div>');
    }).always(function () {
      $('#new_prod, #new_prod_add').prop('disabled', false);
    });
  });
})(jQuery);
</script>

<style>
  .quick-api-autocomplete { position: relative; }
  .quick-api-results { position: absolute; z-index: 100000; left: 0; right: 0; top: 100%; max-height: 220px; overflow-y: auto; background: #fff; border: 1px solid #d7dee8; border-radius: 0 0 6px 6px; box-shadow: 0 8px 20px rgba(20,35,55,.16); }
  .quick-api-result { display: block; width: 100%; padding: 9px 12px; border: 0; border-bottom: 1px solid #eef1f5; background: #fff; text-align: left; cursor: pointer; }
  .quick-api-result:hover { background: #eefafa; }
  .quick-api-result small { display: block; color: #778396; }
</style>
<script>
(function (window, document, $) {
  'use strict';

  window.initQuickProductModal = function () {
    var form = document.getElementById('quick-form');
    if (!form || !window.menuApiUrl) return;

    var category = document.getElementById('cat_id');
    var subCategory = document.getElementById('sub_id');
    var tax = document.getElementById('tax_id');
    var name = document.getElementById('quick-name');
    if (!category || !subCategory || !tax || !name) return;

    function escapeHtml(value) {
      return String(value == null ? '' : value).replace(/[&<>'"]/g, function (char) {
        return {'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#039;','"':'&quot;'}[char];
      });
    }

    function apiRequest(action, data) {
      var params = new URLSearchParams(data || {});
      params.set('action', action);
      return fetch(window.menuApiUrl + (window.menuApiUrl.indexOf('?') === -1 ? '?' : '&') + params.toString(), {
        headers: { Accept: 'application/json' }
      }).then(function (response) {
        return response.json().then(function (json) {
          if (!response.ok || (json && json.ok === false)) throw new Error(json.message || 'Menu API request failed.');
          return json;
        });
      });
    }

    function setOptions(select, placeholder, items, label) {
      select.innerHTML = '<option value="0">' + placeholder + '</option>';
      (items || []).forEach(function (item) {
        var option = document.createElement('option');
        option.value = item.id;
        option.textContent = label(item);
        select.appendChild(option);
      });
    }

    function loadLookups() {
      return apiRequest('bootstrap').then(function (response) {
        var data = response.data || {};
        setOptions(category, '---SELECT CATEGORY----', data.categories, function (item) { return item.name; });
        setOptions(tax, '---SELECT TAX----', data.taxes, function (item) { return item.name + ' ' + (item.percent || 0) + '%'; });
        var subItems = data.sub_categories || [];
        function refillSubCategories() {
          var selected = Number(category.value || 0);
          setOptions(subCategory, '---SELECT A SUB CATEGORY----', subItems.filter(function (item) {
            return !selected || Number(item.cat_id) === selected;
          }), function (item) { return item.name; });
        }
        category.addEventListener('change', refillSubCategories);
        refillSubCategories();
      }).catch(function () {
        // Keep server-rendered options when the lookup endpoint is unavailable.
      });
    }

    function initAutocomplete() {
      var wrapper = name.parentElement;
      if (!wrapper || wrapper.querySelector('.quick-api-results')) return;
      wrapper.classList.add('quick-api-autocomplete');
      var results = document.createElement('div');
      results.className = 'quick-api-results';
      results.hidden = true;
      wrapper.appendChild(results);
      var timer;
      name.addEventListener('input', function () {
        clearTimeout(timer);
        var term = name.value.trim();
        results.innerHTML = '';
        results.hidden = true;
        if (term.length < 2) return;
        timer = setTimeout(function () {
          apiRequest('search', { search: term, start: 0, length: 20 }).then(function (response) {
            var items = response.data && response.data.items ? response.data.items : [];
            items.forEach(function (item) {
              var button = document.createElement('button');
              button.type = 'button';
              button.className = 'quick-api-result';
              button.innerHTML = escapeHtml(item.name) + '<small>' + escapeHtml(item.code || item.id || '') + '</small>';
              button.addEventListener('click', function () {
                name.value = item.name || '';
                results.hidden = true;
              });
              results.appendChild(button);
            });
            results.hidden = items.length === 0;
          }).catch(function () {});
        }, 250);
      });
    }

    loadLookups();
    initAutocomplete();
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', window.initQuickProductModal);
  } else {
    window.initQuickProductModal();
  }
})(window, document, window.jQuery);
</script>
