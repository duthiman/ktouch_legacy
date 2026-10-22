<?php
session_start();
include('../../../inc/connection.php');
include('../funcs.php');

cash_ensure_merge_tables();

$user_id = (int)($_SESSION['user_id'] ?? 0);
$shift = get_column('kp_shift', 'shift', "1");
if ($shift == 0) {
    $shift = date('Y-m-d');
}

$sales = mysqli_query($connection, "SELECT sale_id, user_id, amount, counter FROM kp_sales WHERE shift='$shift' AND pay_status=2 ORDER BY sale_id DESC") or die(mysqli_error($connection));
$cards = array();
while ($sales && $row = mysqli_fetch_assoc($sales)) {
    $sale_id = (int)$row['sale_id'];
    $root_sale_id = cash_merge_root_sale_id($sale_id);
    if ($root_sale_id < 1) {
        continue;
    }
    if (isset($cards[$root_sale_id])) {
        continue;
    }
    if (cash_sale_in_open_merge($sale_id, $root_sale_id)) {
        continue;
    }
    $chain_ids = cash_merge_chain_sale_ids($root_sale_id, true);
    if (empty($chain_ids)) {
        continue;
    }
    $root_amount = cash_merge_chain_total($root_sale_id, true);
    if ($root_amount <= 0) {
        continue;
    }
    $counter_id = (int)get_column('kp_sales', 'counter', "sale_id='$root_sale_id'");
    $cards[$root_sale_id] = array(
        'root_sale_id' => $root_sale_id,
        'sale_id' => $sale_id,
        'count' => count($chain_ids),
        'amount' => $root_amount,
        'user_id' => (int)get_column('kp_sales', 'user_id', "sale_id='$root_sale_id'"),
        'counter_id' => $counter_id,
        'last_bill' => max($chain_ids),
        'item_type' => count($chain_ids) > 1 ? 'open_chain' : 'single'
    );
}

$open_merges = cash_merge_open_headers();
?>
<style>
  .cash-merge-shell {
    display: grid;
    gap: 16px;
  }
  .cash-merge-head {
    padding: 16px 18px;
    border-radius: 20px;
    background: linear-gradient(135deg, #fff8ef 0%, #fff1e1 100%);
    border: 1px solid #f0ddc9;
  }
  .cash-merge-head h3 {
    margin: 0;
    color: #5b4330;
    font-size: 24px;
    font-weight: 900;
  }
  .cash-merge-head p {
    margin: 8px 0 0;
    color: #866c57;
    font-size: 13px;
    font-weight: 700;
  }
  .cash-merge-toolbar {
    display: grid;
    grid-template-columns: minmax(0, 1fr) auto;
    gap: 12px;
    align-items: center;
  }
  .cash-merge-search {
    width: 100%;
    min-height: 48px;
    border-radius: 14px;
    border: 1px solid #e4d0bc;
    padding: 0 14px;
    font-size: 14px;
    font-weight: 700;
  }
  .cash-merge-summary-pill {
    min-height: 48px;
    padding: 0 16px;
    border-radius: 14px;
    background: #fff;
    border: 1px solid #eadbca;
    color: #6a4f36;
    display: inline-flex;
    align-items: center;
    font-weight: 900;
  }
  .cash-merge-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 14px;
  }
  .cash-merge-card {
    width: 100%;
    border: 2px solid #ecd9c7;
    border-radius: 18px;
    background: linear-gradient(180deg, #fffdf8 0%, #fff4e7 100%);
    text-align: left;
    padding: 16px;
    box-shadow: 0 14px 26px rgba(82, 52, 25, 0.07);
    transition: transform .16s ease, box-shadow .16s ease, border-color .16s ease;
  }
  .cash-merge-card:hover,
  .cash-merge-card:focus {
    outline: none;
    transform: translateY(-2px);
    border-color: #df9b58;
    box-shadow: 0 18px 28px rgba(82, 52, 25, 0.12);
  }
  .cash-merge-card.is-picked {
    border-color: #1f9d55;
    background: linear-gradient(180deg, #eef9f1 0%, #fff4e7 100%);
  }
  .cash-merge-card small,
  .cash-merge-mergechip small {
    display: block;
    font-size: 11px;
    font-weight: 900;
    color: #9c774d;
    text-transform: uppercase;
    letter-spacing: .06em;
  }
  .cash-merge-card strong {
    display: block;
    margin-top: 8px;
    color: #2f2418;
    font-size: 28px;
    font-weight: 900;
    line-height: 1;
  }
  .cash-merge-meta {
    display: grid;
    gap: 6px;
    margin-top: 12px;
    color: #6e563c;
    font-size: 13px;
    font-weight: 700;
  }
  .cash-merge-badge {
    display: inline-flex;
    align-items: center;
    min-height: 28px;
    padding: 0 10px;
    border-radius: 999px;
    background: #fff;
    border: 1px solid #eadbca;
    color: #9c6a2f;
    font-size: 11px;
    font-weight: 900;
    text-transform: uppercase;
    letter-spacing: .05em;
  }
  .cash-merge-section {
    border: 1px solid #efe0d1;
    border-radius: 20px;
    background: #fff;
    padding: 16px;
  }
  .cash-merge-section h4 {
    margin: 0 0 10px;
    color: #5a422d;
    font-size: 16px;
    font-weight: 900;
  }
  .cash-merge-open-list {
    display: grid;
    gap: 12px;
  }
  .cash-merge-mergechip {
    border: 1px solid #efe0d1;
    border-radius: 16px;
    background: linear-gradient(180deg, #fffdf9 0%, #fff8ef 100%);
    padding: 14px;
  }
  .cash-merge-mergechip strong {
    display: block;
    margin-top: 6px;
    color: #2e2217;
    font-size: 22px;
    font-weight: 900;
  }
  .cash-merge-mergechip p {
    margin: 8px 0 0;
    color: #765b41;
    font-size: 13px;
    font-weight: 700;
  }
  .cash-merge-empty {
    padding: 20px;
    border-radius: 16px;
    background: #fff8ef;
    border: 1px dashed #e6c9aa;
    color: #7c6044;
    text-align: center;
    font-weight: 800;
  }
  .cash-merge-basket {
    display: grid;
    gap: 12px;
  }
  .cash-merge-basket-card {
    padding: 14px;
    border-radius: 16px;
    border: 1px solid #ece0d4;
    background: linear-gradient(180deg, #fffdf8 0%, #fff4e7 100%);
  }
  .cash-merge-basket-card strong {
    display: block;
    color: #2f2418;
    font-size: 22px;
    font-weight: 900;
  }
  .cash-merge-basket-card p {
    margin: 8px 0 0;
    color: #6d553c;
    font-size: 13px;
    font-weight: 700;
  }
  .cash-merge-basket-actions {
    display: flex;
    gap: 10px;
    margin-top: 14px;
  }
  .cash-merge-basket-actions .btn {
    flex: 1 1 0;
    min-height: 44px;
    font-weight: 900;
    border-radius: 12px;
  }
  @media (max-width: 767px) {
    .cash-merge-toolbar {
      grid-template-columns: 1fr;
    }
  }
</style>
<div class="cash-merge-shell">
  <div class="cash-merge-head">
    <h3>Merge Bills</h3>
    <p>Select standalone bills or open chains, build a merge basket, then create one payable merge record for clearing.</p>
  </div>

  <div class="cash-merge-section">
    <div class="cash-merge-toolbar">
      <input type="text" id="cash-merge-search" class="cash-merge-search" placeholder="Search by bill number, waiter, or counter">
      <div class="cash-merge-summary-pill">Eligible Bills: <span id="cash-merge-eligible-count" style="margin-left:6px;"><?php echo count($cards); ?></span></div>
    </div>
  </div>

  <div class="cash-merge-section">
    <h4>Available Bills</h4>
    <?php if (count($cards) < 1) { ?>
      <div class="cash-merge-empty">There are no eligible unpaid bills to merge right now.</div>
    <?php } else { ?>
      <div class="cash-merge-grid" id="cash-merge-grid">
        <?php foreach ($cards as $card) { ?>
          <button
            type="button"
            class="cash-merge-card"
            data-merge-root="<?php echo (int)$card['root_sale_id']; ?>"
            data-merge-sale="<?php echo (int)$card['sale_id']; ?>"
            data-merge-type="<?php echo htmlspecialchars($card['item_type']); ?>"
            data-merge-amount="<?php echo number_format((float)$card['amount'], 2, '.', ''); ?>"
            data-merge-count="<?php echo (int)$card['count']; ?>"
            data-merge-user="<?php echo htmlspecialchars(get_user((int)$card['user_id'])); ?>"
            data-merge-counter="<?php echo htmlspecialchars(get_counter((int)$card['counter_id'])); ?>"
            data-merge-last="<?php echo (int)$card['last_bill']; ?>"
            data-merge-search="<?php echo htmlspecialchars(strtolower('#' . $card['root_sale_id'] . ' ' . get_user((int)$card['user_id']) . ' ' . get_counter((int)$card['counter_id']) . ' ' . $card['item_type'] . ' #' . $card['last_bill'])); ?>"
          >
            <small><?php echo $card['item_type'] === 'open_chain' ? 'Open Chain' : 'Standalone Bill'; ?></small>
            <strong>#<?php echo (int)$card['root_sale_id']; ?></strong>
            <div class="cash-merge-meta">
              <span><b>Waiter:</b> <?php echo htmlspecialchars(get_user((int)$card['user_id'])); ?></span>
              <span><b>Amount:</b> <?php echo number_format((float)$card['amount'], 2); ?></span>
              <span><b>Counter:</b> <?php echo htmlspecialchars(get_counter((int)$card['counter_id'])); ?></span>
              <span><b>Last Bill:</b> #<?php echo (int)$card['last_bill']; ?></span>
            </div>
            <div style="margin-top:12px; display:flex; gap:8px; flex-wrap:wrap;">
              <span class="cash-merge-badge"><?php echo $card['count']; ?> bill<?php echo $card['count'] == 1 ? '' : 's'; ?></span>
              <span class="cash-merge-badge"><?php echo $card['item_type'] === 'open_chain' ? 'Parent Chain' : 'Single Clear'; ?></span>
            </div>
          </button>
        <?php } ?>
      </div>
    <?php } ?>
  </div>

  <div class="cash-merge-section">
    <h4>Open Merge Records</h4>
    <?php if (count($open_merges) < 1) { ?>
      <div class="cash-merge-empty">No open merge records yet. Create one from the available bills above.</div>
    <?php } else { ?>
      <div class="cash-merge-open-list">
        <?php foreach ($open_merges as $merge) { ?>
          <?php
            $merge_items = cash_merge_items((int)$merge['merge_id']);
            $root_labels = array();
            foreach ($merge_items as $merge_item) {
                $root_id = (int)$merge_item['root_sale_id'];
                if ($root_id < 1 || isset($root_labels[$root_id])) {
                    continue;
                }
                $root_labels[$root_id] = '#' . $root_id;
            }
          ?>
          <div class="cash-merge-mergechip">
            <small>Open Merge</small>
            <strong><?php echo htmlspecialchars($merge['merge_no'] ?: ('MERGE #' . $merge['merge_id'])); ?></strong>
            <p><b>Total:</b> <?php echo number_format((float)$merge['total_amount'], 2); ?> | <b>Created By:</b> <?php echo htmlspecialchars(get_user((int)$merge['created_by'])); ?></p>
            <p><b>Included:</b> <?php echo htmlspecialchars(implode(', ', $root_labels)); ?></p>
          </div>
        <?php } ?>
      </div>
    <?php } ?>
  </div>
</div>
<script type="text/javascript">
  (function() {
    var basket = {};

    function renderBasket() {
      var keys = Object.keys(basket);
      var total = 0;
      var html = '';
      keys.forEach(function(key) {
        var item = basket[key];
        total += parseFloat(item.amount || 0);
        html += '<div class="cash-merge-basket-card">' +
          '<small style="display:block;color:#9c774d;font-size:11px;font-weight:900;text-transform:uppercase;">' + (item.type === 'open_chain' ? 'Open Chain' : 'Standalone Bill') + '</small>' +
          '<strong>#' + item.root + '</strong>' +
          '<p><b>Waiter:</b> ' + item.user + '<br><b>Counter:</b> ' + item.counter + '<br><b>Amount:</b> ' + Number(item.amount).toFixed(2) + '</p>' +
          '<button type="button" class="btn btn-outline-danger btn-sm" onclick="cashMergeRemove(' + item.root + ')">Remove</button>' +
        '</div>';
      });

      if (!keys.length) {
        html = '<div class="cash-merge-empty">No bills selected for merge yet.</div>';
      }

      html += '<div class="cash-merge-basket-card" style="background:#fffdf8;">' +
        '<small>Total Selected</small>' +
        '<strong>' + total.toFixed(2) + '</strong>' +
        '<p><b>Bills:</b> ' + keys.length + '</p>' +
        '<div class="cash-merge-basket-actions">' +
          '<button type="button" class="btn btn-outline-secondary" onclick="cashMergeClearBasket()">Clear Basket</button>' +
          '<button type="button" class="btn btn-success" onclick="cashMergeCreate()">Create Merge</button>' +
        '</div>' +
      '</div>';

      $('#sale-items')
        .attr('data-selected-invoices', keys.join(','))
        .html('<div class="cash-panel"><div class="cash-panel-head"><h4>Merge Basket</h4></div><div class="cash-panel-body cash-merge-basket">' + html + '</div></div>');
    }

    function syncPickedState() {
      $('[data-merge-root]').each(function() {
        var root = String($(this).data('mergeRoot'));
        $(this).toggleClass('is-picked', !!basket[root]);
      });
    }

    window.cashMergeRemove = function(rootId) {
      delete basket[String(rootId)];
      syncPickedState();
      renderBasket();
    };

    window.cashMergeClearBasket = function() {
      basket = {};
      syncPickedState();
      renderBasket();
    };

    window.cashMergeCreate = function() {
      var roots = Object.keys(basket);
      if (!roots.length) {
        alert('Select at least one bill or chain to merge.');
        return;
      }
      $.ajax({
        url: 'API/cash_api.php',
        type: 'POST',
        data: { action: 'merge_create', roots: roots },
        dataType: 'json'
      })
      .done(function(res) {
        var payload = res && res.data ? res.data : null;
        if (!res || !res.ok || !payload || !payload.ok) {
          alert(payload && payload.err ? payload.err : (res && res.message ? res.message : 'Could not create the merge.'));
          return;
        }
        cashMergeClearBasket();
        loadDeskPanel('merge/index.php', {}, 'Could not load merge desk.');
        if (payload.preview_html) {
          $('#sale-items').html(payload.preview_html);
        }
      })
      .fail(function() {
        alert('Could not create the merge right now.');
      });
    };

    $(document).off('click.cashMergeCard').on('click.cashMergeCard', '[data-merge-root]', function() {
      var root = String($(this).data('mergeRoot'));
      if (basket[root]) {
        delete basket[root];
      } else {
        basket[root] = {
          root: parseInt($(this).data('mergeRoot'), 10) || 0,
          amount: parseFloat($(this).data('mergeAmount')) || 0,
          type: $(this).data('mergeType') || 'single',
          user: $(this).data('mergeUser') || '',
          counter: $(this).data('mergeCounter') || ''
        };
      }
      syncPickedState();
      renderBasket();
    });

    $('#cash-merge-search').off('input.cashMerge').on('input.cashMerge', function() {
      var term = $.trim(String($(this).val()).toLowerCase());
      var visible = 0;
      $('[data-merge-root]').each(function() {
        var haystack = String($(this).data('mergeSearch') || '');
        var show = term === '' || haystack.indexOf(term) !== -1;
        $(this).toggle(show);
        if (show) {
          visible++;
        }
      });
      $('#cash-merge-eligible-count').text(visible);
    });

    renderBasket();
  })();
</script>
