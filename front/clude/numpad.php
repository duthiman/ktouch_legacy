<style>
  .numpad-wrap {
    margin-top: -10px;
    margin-left: -5px;
    margin-bottom: -10px;
  }
  .numpad-wrap .num-pad {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 8px;
    padding: 0 6px 6px;
  }
  .numpad-wrap .span4 {
    width: auto !important;
    float: none !important;
    margin: 0 !important;
    padding: 0 !important;
  }
  .numpad-wrap .num,
  .numpad-wrap .btn {
    width: 100% !important;
    min-height: 68px !important;
    height: 68px !important;
    margin: 0 !important;
    padding: 0 !important;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 10px !important;
    background: #ffffff !important;
    border: 3px solid #62b647 !important;
    box-shadow: inset 0 1px 0 rgba(255,255,255,0.9), 0 8px 14px rgba(74, 57, 27, 0.1);
    overflow: hidden;
    color: #111 !important;
    font-weight: 900;
  }
  .numpad-wrap .number {
    margin: 0 !important;
    font-size: 28px !important;
    line-height: 1 !important;
    font-weight: 900;
  }
  .numpad-wrap .numpad-action {
    background: #f7f1de !important;
  }
  .numpad-wrap .numpad-submit {
    background: #2f7d32 !important;
    color: #fff !important;
  }
  .numpad-wrap .numpad-wide {
    grid-column: span 3;
  }
</style>
<div class="row numpad-wrap">
  <div id="numpad" class="num-pad">
    <div class="span4">
    <div class="num" >
      <h2 class="number">7</h2>
      <div class="txt" style="display:none"> 7 </div>
    </div>
    </div>
    <div class="span4">
    <div class="num">
      <h2 class="number">8</h2>
      <div class="txt"  style="display:none">8</div>
    </div>
    </div>
    <div class="span4">
    <div class="num"> 
      <h2 class="number">9</h2>
      <div class="txt" style="display:none">9</div>
    </div>
    </div>
    <div class="span4">
    <div class="num">
      <h2 class="number">4</h2>
      <div class="txt" style="display:none">4</div>
    </div>
    </div>
    <div class="span4">
    <div class="num">
      <h2 class="number">5</h2>
      <div class="txt" style="display:none">5</div>
    </div>
    </div>
    <div class="span4">
    <div class="num">
      <h2 class="number">6</h2>
      <div class="txt" style="display:none">6</div>
    </div>
    </div>
    <div class="span4">
    <div class="num">
      <h2 class="number">1</h2>
      <div class="txt" style="display:none">1</div>
    </div>
    </div>
    <div class="span4">
    <div class="num">
      <h2 class="number">2</h2>
      <div class="txt" style="display:none">2</div>
    </div>
    </div>
    <div class="span4">
    <div class="num">
      <h2 class="number">3</h2>
      <div class="txt" style="display:none">3</div>
    </div>
    </div>
    <div class="span4">
    <button type="button" id="clear" name="count" class="btn numpad-action" onclick="return window.codexSharedNumpadClear(this);">CLEAR</button>
    </div>
    <?php if (!empty($numpad_decimal) || basename($_SERVER['PHP_SELF'] ?? '') === 'stock_take.php') { ?>
    <div class="span4">
    <div class="num numpad-action" aria-label="Decimal point">
      <h2 class="number">.</h2>
      <div class="txt" style="display:none">.</div>
    </div>
    </div>
    <?php } ?>
    <div class="span4">
    <div class="num">
      <h2 class="number">0</h2>
      <div class="txt" style="display:none">0</div>
    </div>
    </div>
    <div class="span4">
    <button type="button" id="submit_pad" name="count" class="btn numpad-submit" onclick="return window.codexSharedNumpadSubmit(this);">SUBMIT</button>
    </div>
  </div>
</div>
<script>
  window.codexSharedNumpadFindTarget = window.codexSharedNumpadFindTarget || function (trigger) {
    var root = trigger.closest('.modal-dialog, .modal-content, form, .row, body') || document.body;
    var active = document.activeElement;
    if (active && /^(INPUT|TEXTAREA)$/.test(active.tagName)) {
      return active;
    }
    return root.querySelector('input:not([type="hidden"]), textarea');
  };
  window.codexSharedNumpadBackspace = window.codexSharedNumpadBackspace || function (trigger) {
    var target = window.codexSharedNumpadFindTarget(trigger);
    if (!target) {
      return false;
    }
    target.value = (target.value || '').slice(0, -1);
    if (window.jQuery) {
      window.jQuery(target).trigger('input').trigger('change');
    }
    return false;
  };
  window.codexSharedNumpadClear = window.codexSharedNumpadClear || function (trigger) {
    var target = window.codexSharedNumpadFindTarget(trigger);
    if (!target) {
      return false;
    }
    target.value = '';
    if (window.jQuery) {
      window.jQuery(target).trigger('input').trigger('change');
    }
    return false;
  };
  window.codexSharedNumpadSubmit = window.codexSharedNumpadSubmit || function (trigger) {
    var root = trigger.closest('form, .take-form-shell, .oid-body, .modal-content, body') || document.body;
    var submitTarget = root.querySelector('#take_mobile, #take, [data-numpad-submit]');
    if (submitTarget) {
      submitTarget.click();
    }
    return false;
  };
</script>
