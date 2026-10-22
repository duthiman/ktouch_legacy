<?php
require("../clude/head.php");

$itemTypes = array(
    array(
        'code' => 'raw_material',
        'name' => 'Raw Material',
        'summary' => 'Purchasable stock item used by kitchen or bar operations.',
        'examples' => 'Flour, potatoes, onions, tomatoes, cooking oil',
        'appears_pos' => 'No',
        'notes' => 'Can be issued directly, converted, or consumed through recipe/batch logic.'
    ),
    array(
        'code' => 'sellable_item',
        'name' => 'Sellable Item',
        'summary' => 'Item sold directly to the customer through POS.',
        'examples' => 'Soda, beer, ugali portion, chapati, cocktail',
        'appears_pos' => 'Yes',
        'notes' => 'May use direct sell, recipe, batch, bottle/tot, or variable-price logic.'
    ),
    array(
        'code' => 'batch_item',
        'name' => 'Batch Produced Item',
        'summary' => 'Sellable item produced in bulk from one or more raw materials.',
        'examples' => 'Ugali, chapati, cooked rice, stew portions',
        'appears_pos' => 'Yes',
        'notes' => 'Production adds sellable portions after consuming source stock.'
    ),
    array(
        'code' => 'bottle_item',
        'name' => 'Bottle / Tot Item',
        'summary' => 'Bar-controlled item managed through ml and shot logic.',
        'examples' => 'Whisky shots, vodka tots, gin measures',
        'appears_pos' => 'Yes',
        'notes' => 'Needs bottle volume, shot size, and optional bottle weight checks.'
    ),
    array(
        'code' => 'service_item',
        'name' => 'Service Item',
        'summary' => 'Non-stock item sold without stock deduction.',
        'examples' => 'Table charge, service fee, venue fee',
        'appears_pos' => 'Yes',
        'notes' => 'Usually fixed-price or permission-based variable price.'
    )
);

$controlMethods = array(
    array(
        'name' => 'Direct Sell',
        'used_for' => 'Items sold as-is with simple stock or no stock handling.',
        'examples' => 'Beer bottle, water, snack packet, service item',
        'stock_flow' => 'Sale reduces sellable stock directly, or no stock move for services.'
    ),
    array(
        'name' => 'Recipe Controlled',
        'used_for' => 'Items where ingredient-level deduction matters.',
        'examples' => 'Cocktails, burgers, premium dishes',
        'stock_flow' => 'Sale consumes linked raw materials by recipe quantity.'
    ),
    array(
        'name' => 'Batch Produced',
        'used_for' => 'Items cooked/prepared in batches before being sold by portion.',
        'examples' => 'Ugali, chapati, rice, stew',
        'stock_flow' => 'Production consumes source stock and creates sellable portions.'
    ),
    array(
        'name' => 'Bottle / Tot Control',
        'used_for' => 'Bar items tracked by bottle volume and shot size.',
        'examples' => 'Whisky, vodka, gin',
        'stock_flow' => 'Bottle stock converts into estimated shots or ml balance.'
    ),
    array(
        'name' => 'Conversion Managed',
        'used_for' => 'Bulk purchases converted into usable stock units.',
        'examples' => 'Potatoes by kg, tomatoes by crate/kg, flour by bag/kg',
        'stock_flow' => 'Purchase unit converts into stock/usage unit by ratio.'
    ),
    array(
        'name' => 'Simple Issue',
        'used_for' => 'Kitchen/store items issued in bulk without strict sell-side deduction.',
        'examples' => 'Salt, onions, tomatoes in loose kitchen issue',
        'stock_flow' => 'Store issues stock to department; usage is controlled operationally, not per sale.'
    )
);

$pricingModes = array(
    array('name' => 'Fixed Price', 'desc' => 'Standard selling price only.'),
    array('name' => 'Variable Price', 'desc' => 'User can enter price at sale time.'),
    array('name' => 'Permission-Based Price Change', 'desc' => 'Price can change only for approved users.'),
    array('name' => 'Attribute-Based Price Change', 'desc' => 'Selected attributes can add or subtract price.')
);

$requiredFields = array(
    array('type' => 'Raw Material', 'fields' => 'Name, category, purchase unit, stock unit, conversion ratio if needed, purchase price, reorder level, purchasable yes/no'),
    array('type' => 'Sellable Item', 'fields' => 'Name, category, department, selling price or pricing mode, tax, POS visibility, control method'),
    array('type' => 'Batch Produced Item', 'fields' => 'Name, category, department, batch yield, production unit, sell unit, source item/raw material links'),
    array('type' => 'Bottle / Tot Item', 'fields' => 'Name, linked bottle item, bottle volume ml, shot size ml, empty bottle weight, full bottle weight, price mode'),
    array('type' => 'Service Item', 'fields' => 'Name, selling price or variable-price flag, tax, POS visibility')
);

$buildPhases = array(
    array(
        'phase' => 'Phase 1',
        'title' => 'Item Model and Safe Entry',
        'items' => array(
            'Create the new item type model',
            'Create the control method model',
            'Design the first add/edit flow for sellable items and raw materials',
            'Add required field rules by item type'
        )
    ),
    array(
        'phase' => 'Phase 2',
        'title' => 'Batch, Conversion, and Bottle Logic',
        'items' => array(
            'Batch production for foods like ugali and chapati',
            'Purchase unit to stock unit conversion',
            'Bottle/tot logic with ml and bottle weight support',
            'Clear stock flow rules for each method'
        )
    ),
    array(
        'phase' => 'Phase 3',
        'title' => 'Attributes and Flexible Selling',
        'items' => array(
            'Attributes with optional price changes',
            'Permission-based variable pricing',
            'POS behavior rules from the new item model',
            'Operational dashboards and validation warnings'
        )
    )
);
?>
<div id="page-container" class="page-container fade page-without-sidebar page-header-fixed page-with-top-menu">
  <?php require("../clude/header.php"); ?>
  <?php require("../clude/top_menu.php"); ?>

  <style>
    :root {
      --mn-ink: #17323c;
      --mn-accent: #2f6f7e;
      --mn-accent-soft: #e9f4f6;
      --mn-bg: #f5f8fa;
      --mn-card: #ffffff;
      --mn-border: #d9e4ea;
      --mn-warm: #a04f14;
      --mn-good: #256b4d;
    }
    .mn-wrap {
      background: var(--mn-bg);
      border-radius: 18px;
      padding: 18px;
    }
    .mn-hero {
      border-radius: 16px;
      background: linear-gradient(135deg, #143642 0%, #2f6f7e 100%);
      color: #fff;
      padding: 24px;
      margin-bottom: 18px;
    }
    .mn-hero h2 {
      margin-top: 0;
      margin-bottom: 8px;
    }
    .mn-chip {
      display: inline-block;
      padding: 5px 10px;
      border-radius: 999px;
      font-size: 11px;
      font-weight: 700;
      background: rgba(255,255,255,0.16);
      color: #fff;
      margin-right: 6px;
      margin-bottom: 6px;
      text-transform: uppercase;
      letter-spacing: .4px;
    }
    .mn-toolbar {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      margin-bottom: 18px;
    }
    .mn-navbtn {
      border: 1px solid var(--mn-border);
      background: #fff;
      color: var(--mn-ink);
      border-radius: 999px;
      padding: 9px 14px;
      font-size: 12px;
      font-weight: 700;
      cursor: pointer;
    }
    .mn-navbtn.active {
      background: var(--mn-accent);
      border-color: var(--mn-accent);
      color: #fff;
    }
    .mn-panel {
      display: none;
      background: var(--mn-card);
      border: 1px solid var(--mn-border);
      border-radius: 14px;
      padding: 18px;
      margin-bottom: 18px;
    }
    .mn-panel.active {
      display: block;
    }
    .mn-panel-title {
      margin-top: 0;
      margin-bottom: 14px;
      color: var(--mn-ink);
    }
    .mn-card {
      border: 1px solid var(--mn-border);
      border-radius: 14px;
      background: #fff;
      padding: 16px;
      margin-bottom: 16px;
      height: calc(100% - 16px);
    }
    .mn-card h4,
    .mn-card h5 {
      margin-top: 0;
      color: var(--mn-ink);
    }
    .mn-mini {
      display: inline-block;
      padding: 4px 9px;
      border-radius: 999px;
      font-size: 10px;
      font-weight: 700;
      background: var(--mn-accent-soft);
      color: var(--mn-accent);
      margin-bottom: 10px;
      text-transform: uppercase;
      letter-spacing: .4px;
    }
    .mn-kv {
      margin: 0 0 8px;
      color: #50636d;
      line-height: 1.6;
      font-size: 13px;
    }
    .mn-kv strong {
      color: var(--mn-ink);
    }
    .mn-table {
      width: 100%;
      border-collapse: collapse;
      font-size: 13px;
    }
    .mn-table th,
    .mn-table td {
      border: 1px solid var(--mn-border);
      padding: 10px 12px;
      vertical-align: top;
    }
    .mn-table th {
      background: #eef5f7;
      color: var(--mn-ink);
      font-weight: 700;
    }
    .mn-list {
      margin: 0;
      padding-left: 18px;
      color: #51656f;
      line-height: 1.7;
    }
    .mn-form label {
      color: var(--mn-ink);
      font-size: 12px;
      font-weight: 700;
      margin-bottom: 6px;
      text-transform: uppercase;
      letter-spacing: .3px;
    }
    .mn-form .form-control {
      border-radius: 10px;
      border-color: var(--mn-border);
      box-shadow: none;
    }
    .mn-subcard {
      border: 1px solid var(--mn-border);
      border-radius: 12px;
      background: #fbfdfe;
      padding: 14px;
      margin-bottom: 14px;
    }
    .mn-subtitle {
      margin-top: 0;
      margin-bottom: 10px;
      color: var(--mn-ink);
      font-size: 15px;
    }
    .mn-checkline {
      display: block;
      padding: 6px 0;
      color: #455a64;
      font-size: 13px;
    }
    .mn-sim-card {
      border: 1px dashed #b9cdd5;
      border-radius: 14px;
      background: #fff;
      padding: 16px;
      margin-bottom: 14px;
    }
    .mn-metric {
      display: inline-block;
      min-width: 120px;
      padding: 8px 10px;
      border-radius: 10px;
      background: #eef5f7;
      color: var(--mn-ink);
      font-size: 12px;
      font-weight: 700;
      margin-right: 8px;
      margin-bottom: 8px;
    }
    .mn-banner {
      border-left: 4px solid var(--mn-warm);
      background: #fff7ef;
      color: #6d4b2a;
      border-radius: 10px;
      padding: 12px 14px;
      margin-bottom: 18px;
    }
    .mn-ok {
      border-left-color: var(--mn-good);
      background: #eef8f2;
      color: #2f5a45;
    }
    @media (max-width: 767px) {
      .mn-wrap {
        padding: 12px;
      }
      .mn-hero {
        padding: 18px;
      }
      .mn-table {
        display: block;
        overflow-x: auto;
      }
    }
  </style>

  <div id="content" class="content">
    <div class="mn-wrap">
      <div class="mn-hero">
        <h2>Menu New Model</h2>
        <p style="margin:0 0 10px;">
          This workspace defines how we will handle menu items, raw materials, batches, bottle shots,
          conversions, attributes, and flexible pricing without affecting the live `back/menu` module.
        </p>
        <div>
          <span class="mn-chip">Recipe</span>
          <span class="mn-chip">Batch</span>
          <span class="mn-chip">Tots / Shots</span>
          <span class="mn-chip">Conversions</span>
          <span class="mn-chip">Attributes</span>
          <span class="mn-chip">Price Rules</span>
        </div>
      </div>

      <div class="mn-banner mn-ok">
        <strong>Working direction:</strong> we will not force every item into one stock method.
        Each item will have a role, a control method, a unit model, and a pricing behavior.
      </div>

      <div class="mn-toolbar">
        <button type="button" class="mn-navbtn active" data-target="item-types">Item Types</button>
        <button type="button" class="mn-navbtn" data-target="control-methods">Control Methods</button>
        <button type="button" class="mn-navbtn" data-target="required-fields">Required Fields</button>
        <button type="button" class="mn-navbtn" data-target="pricing-model">Pricing Model</button>
        <button type="button" class="mn-navbtn" data-target="item-builder">Item Builder</button>
        <button type="button" class="mn-navbtn" data-target="sellable-form">Sellable Item Form</button>
        <button type="button" class="mn-navbtn" data-target="raw-form">Raw Material Form</button>
        <button type="button" class="mn-navbtn" data-target="batch-form">Batch Item Form</button>
        <button type="button" class="mn-navbtn" data-target="bottle-form">Bottle / Tot Form</button>
        <button type="button" class="mn-navbtn" data-target="drafts">Drafts</button>
        <button type="button" class="mn-navbtn" data-target="roadmap">Roadmap</button>
      </div>

      <div id="mn-save-results"></div>

      <div class="mn-panel active" id="item-types">
        <h3 class="mn-panel-title">Item Types</h3>
        <div class="row">
          <?php foreach ($itemTypes as $type): ?>
          <div class="col-md-6 col-lg-4">
            <div class="mn-card">
              <span class="mn-mini"><?php echo htmlspecialchars($type['code']); ?></span>
              <h4><?php echo htmlspecialchars($type['name']); ?></h4>
              <p class="mn-kv"><?php echo htmlspecialchars($type['summary']); ?></p>
              <p class="mn-kv"><strong>Examples:</strong> <?php echo htmlspecialchars($type['examples']); ?></p>
              <p class="mn-kv"><strong>Appears on POS:</strong> <?php echo htmlspecialchars($type['appears_pos']); ?></p>
              <p class="mn-kv"><strong>Notes:</strong> <?php echo htmlspecialchars($type['notes']); ?></p>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="mn-panel" id="control-methods">
        <h3 class="mn-panel-title">Control Methods</h3>
        <div class="mn-banner">
          This is the core of the new design. An item type tells us what the item is. A control method tells us how stock and selling behave.
        </div>
        <div class="row">
          <?php foreach ($controlMethods as $method): ?>
          <div class="col-md-6">
            <div class="mn-card">
              <h4><?php echo htmlspecialchars($method['name']); ?></h4>
              <p class="mn-kv"><strong>Used For:</strong> <?php echo htmlspecialchars($method['used_for']); ?></p>
              <p class="mn-kv"><strong>Examples:</strong> <?php echo htmlspecialchars($method['examples']); ?></p>
              <p class="mn-kv"><strong>Stock Flow:</strong> <?php echo htmlspecialchars($method['stock_flow']); ?></p>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="mn-panel" id="required-fields">
        <h3 class="mn-panel-title">Required Fields by Type</h3>
        <table class="mn-table">
          <thead>
            <tr>
              <th style="width: 24%;">Item Type</th>
              <th>Required Fields</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($requiredFields as $row): ?>
            <tr>
              <td><strong><?php echo htmlspecialchars($row['type']); ?></strong></td>
              <td><?php echo htmlspecialchars($row['fields']); ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <div class="mn-panel" id="pricing-model">
        <h3 class="mn-panel-title">Pricing Model</h3>
        <div class="row">
          <?php foreach ($pricingModes as $mode): ?>
          <div class="col-md-6">
            <div class="mn-card">
              <h4><?php echo htmlspecialchars($mode['name']); ?></h4>
              <p class="mn-kv"><?php echo htmlspecialchars($mode['desc']); ?></p>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <div class="mn-banner">
          Recommendation: not every item should allow price edits at the POS. We should support fixed, variable, permission-based, and attribute-based pricing.
        </div>
      </div>

      <div class="mn-panel" id="item-builder">
        <h3 class="mn-panel-title">New Item Builder</h3>
        <div class="mn-banner mn-ok">
          This is the first practical build target for `menu_new`: one creation flow that changes based on item type and control method.
        </div>

        <div class="row">
          <div class="col-md-5">
            <div class="mn-card">
              <h4>Item Setup</h4>
              <form id="mn-item-form">
                <div class="form-group">
                  <label>Item Name</label>
                  <input type="text" class="form-control" id="mn-item-name" placeholder="e.g. Ugali, Jameson Tot, Tomatoes">
                </div>

                <div class="form-group">
                  <label>Item Type</label>
                  <select class="form-control" id="mn-item-type">
                    <option value="raw_material">Raw Material</option>
                    <option value="sellable_item">Sellable Item</option>
                    <option value="batch_item">Batch Produced Item</option>
                    <option value="bottle_item">Bottle / Tot Item</option>
                    <option value="service_item">Service Item</option>
                  </select>
                </div>

                <div class="form-group">
                  <label>Control Method</label>
                  <select class="form-control" id="mn-control-method">
                    <option value="direct_sell">Direct Sell</option>
                    <option value="recipe">Recipe Controlled</option>
                    <option value="batch">Batch Produced</option>
                    <option value="bottle">Bottle / Tot Control</option>
                    <option value="conversion">Conversion Managed</option>
                    <option value="simple_issue">Simple Issue</option>
                  </select>
                </div>

                <div class="form-group">
                  <label>Pricing Mode</label>
                  <select class="form-control" id="mn-pricing-mode">
                    <option value="fixed">Fixed Price</option>
                    <option value="variable">Variable Price</option>
                    <option value="permission">Permission-Based Price Change</option>
                    <option value="attribute">Attribute-Based Price Change</option>
                  </select>
                </div>

                <div class="form-group" id="mn-sell-price-wrap">
                  <label>Selling Price</label>
                  <input type="number" class="form-control" id="mn-sell-price" placeholder="0.00">
                </div>

                <div class="form-group">
                  <label>Department</label>
                  <select class="form-control" id="mn-department">
                    <option value="kitchen">Kitchen</option>
                    <option value="bar">Bar</option>
                    <option value="general">General</option>
                  </select>
                </div>
              </form>
            </div>
          </div>

          <div class="col-md-7">
            <div class="mn-card">
              <h4>Dynamic Rules Preview</h4>
              <p class="mn-kv">
                The fields below simulate what this item will require once we build the actual save flow.
              </p>

              <div id="mn-rule-summary" class="mn-banner"></div>

              <div id="mn-fields-common" class="mn-builder-group">
                <h5>Always Required</h5>
                <ul class="mn-list">
                  <li>Item name</li>
                  <li>Category / subgroup</li>
                  <li>Department or business area</li>
                  <li>Tax behavior</li>
                </ul>
              </div>

              <div id="mn-fields-raw" class="mn-builder-group" style="display:none;">
                <h5>Raw Material Rules</h5>
                <ul class="mn-list">
                  <li>Purchase unit and stock unit</li>
                  <li>Reorder level</li>
                  <li>Purchasable flag</li>
                  <li>Optional conversion ratio</li>
                </ul>
              </div>

              <div id="mn-fields-sellable" class="mn-builder-group" style="display:none;">
                <h5>Sellable Item Rules</h5>
                <ul class="mn-list">
                  <li>POS visibility</li>
                  <li>Selling price or pricing mode</li>
                  <li>Attribute support</li>
                  <li>Operational control method</li>
                </ul>
              </div>

              <div id="mn-fields-batch" class="mn-builder-group" style="display:none;">
                <h5>Batch Production Rules</h5>
                <ul class="mn-list">
                  <li>Batch source item or raw material</li>
                  <li>Yield per batch</li>
                  <li>Produced sale unit</li>
                  <li>Production should increase sellable stock</li>
                </ul>
              </div>

              <div id="mn-fields-bottle" class="mn-builder-group" style="display:none;">
                <h5>Bottle / Tot Rules</h5>
                <ul class="mn-list">
                  <li>Bottle volume in ml</li>
                  <li>Shot size in ml</li>
                  <li>Empty bottle weight</li>
                  <li>Full bottle weight</li>
                </ul>
              </div>

              <div id="mn-fields-recipe" class="mn-builder-group" style="display:none;">
                <h5>Recipe Rules</h5>
                <ul class="mn-list">
                  <li>Linked ingredient list</li>
                  <li>Qty per ingredient</li>
                  <li>Sale should deduct raw materials</li>
                </ul>
              </div>

              <div id="mn-fields-conversion" class="mn-builder-group" style="display:none;">
                <h5>Conversion Rules</h5>
                <ul class="mn-list">
                  <li>Purchase unit to stock unit conversion</li>
                  <li>Ratio or weight-based conversion</li>
                  <li>Works for potatoes, tomatoes, flour and similar items</li>
                </ul>
              </div>

              <div id="mn-fields-simple" class="mn-builder-group" style="display:none;">
                <h5>Simple Issue Rules</h5>
                <ul class="mn-list">
                  <li>Stock is issued from store to kitchen/bar</li>
                  <li>No strict per-sale deduction required</li>
                  <li>Useful for salt, onions, tomatoes and similar loose-consumption items</li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="mn-panel" id="roadmap">
        <h3 class="mn-panel-title">Build Roadmap</h3>
        <div class="row">
          <?php foreach ($buildPhases as $phase): ?>
          <div class="col-md-4">
            <div class="mn-card">
              <span class="mn-mini"><?php echo htmlspecialchars($phase['phase']); ?></span>
              <h4><?php echo htmlspecialchars($phase['title']); ?></h4>
              <ul class="mn-list">
                <?php foreach ($phase['items'] as $item): ?>
                <li><?php echo htmlspecialchars($item); ?></li>
                <?php endforeach; ?>
              </ul>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="mn-panel" id="sellable-form">
        <h3 class="mn-panel-title">Sellable Item Form</h3>
        <div class="mn-banner mn-ok">
          This is the first isolated working form. It does not save yet. It helps us confirm the right fields and rules for a sellable item before we connect any database logic.
        </div>

        <div class="row">
          <div class="col-md-7">
            <form class="mn-form" id="mn-sellable-form">
              <div class="mn-subcard">
                <h4 class="mn-subtitle">Basic Details</h4>
                <div class="row">
                  <div class="col-md-8">
                    <div class="form-group">
                      <label>Item Name</label>
                      <input type="text" class="form-control" id="sf-name" placeholder="e.g. Ugali, Beef Stew, Jameson Tot">
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label>Code</label>
                      <input type="text" class="form-control" id="sf-code" placeholder="Optional code">
                    </div>
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-4">
                    <div class="form-group">
                      <label>Category</label>
                      <select class="form-control" id="sf-category">
                        <option>Main Meals</option>
                        <option>Breakfast</option>
                        <option>Bar</option>
                        <option>Soft Drinks</option>
                        <option>Snacks</option>
                      </select>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label>Sub Category</label>
                      <select class="form-control" id="sf-subcategory">
                        <option>General</option>
                        <option>Local Dishes</option>
                        <option>Whisky</option>
                        <option>Cocktails</option>
                        <option>Sides</option>
                      </select>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label>Department</label>
                      <select class="form-control" id="sf-department">
                        <option value="kitchen">Kitchen</option>
                        <option value="bar">Bar</option>
                        <option value="general">General</option>
                      </select>
                    </div>
                  </div>
                </div>
              </div>

              <div class="mn-subcard">
                <h4 class="mn-subtitle">Selling Behaviour</h4>
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label>Control Method</label>
                      <select class="form-control" id="sf-control-method">
                        <option value="direct_sell">Direct Sell</option>
                        <option value="recipe">Recipe Controlled</option>
                        <option value="batch">Batch Produced</option>
                        <option value="bottle">Bottle / Tot Control</option>
                      </select>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label>Pricing Mode</label>
                      <select class="form-control" id="sf-pricing-mode">
                        <option value="fixed">Fixed Price</option>
                        <option value="variable">Variable Price</option>
                        <option value="permission">Permission-Based Price Change</option>
                        <option value="attribute">Attribute-Based Price Change</option>
                      </select>
                    </div>
                  </div>
                </div>

                <div class="row" id="sf-price-row">
                  <div class="col-md-4">
                    <div class="form-group">
                      <label>Selling Price</label>
                      <input type="number" class="form-control" id="sf-price" value="0" min="0" step="0.01">
                    </div>
                  </div>
                  <div class="col-md-4" id="sf-min-wrap" style="display:none;">
                    <div class="form-group">
                      <label>Min Price</label>
                      <input type="number" class="form-control" id="sf-min-price" value="0" min="0" step="0.01">
                    </div>
                  </div>
                  <div class="col-md-4" id="sf-max-wrap" style="display:none;">
                    <div class="form-group">
                      <label>Max Price</label>
                      <input type="number" class="form-control" id="sf-max-price" value="0" min="0" step="0.01">
                    </div>
                  </div>
                </div>

                <label class="mn-checkline"><input type="checkbox" id="sf-pos" checked> Show on POS</label>
                <label class="mn-checkline"><input type="checkbox" id="sf-taxable" checked> Taxable item</label>
                <label class="mn-checkline"><input type="checkbox" id="sf-active" checked> Active and available for sale</label>
              </div>

              <div class="mn-subcard">
                <h4 class="mn-subtitle">Attributes and Selling Options</h4>
                <div class="row">
                  <div class="col-md-6">
                    <label class="mn-checkline"><input type="checkbox" id="sf-attr-accomp"> Has accompaniments</label>
                    <label class="mn-checkline"><input type="checkbox" id="sf-attr-temp"> Has hot / cold choice</label>
                    <label class="mn-checkline"><input type="checkbox" id="sf-attr-chilli"> Has chilli / non chilli choice</label>
                  </div>
                  <div class="col-md-6">
                    <label class="mn-checkline"><input type="checkbox" id="sf-attr-addon"> Has add-ons with price effect</label>
                    <label class="mn-checkline"><input type="checkbox" id="sf-open-price"> Allow price change at sale time</label>
                    <label class="mn-checkline"><input type="checkbox" id="sf-require-perm"> Require approval for price change</label>
                  </div>
                </div>
              </div>

              <div class="mn-subcard" id="sf-batch-card" style="display:none;">
                <h4 class="mn-subtitle">Batch Production Setup</h4>
                <div class="row">
                  <div class="col-md-4">
                    <div class="form-group">
                      <label>Source Item</label>
                      <input type="text" class="form-control" id="sf-batch-source" placeholder="e.g. 2kg maize flour bag">
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label>Batch Yield</label>
                      <input type="number" class="form-control" id="sf-batch-yield" value="12" min="1" step="1">
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label>Sale Unit</label>
                      <input type="text" class="form-control" id="sf-batch-unit" placeholder="Portions">
                    </div>
                  </div>
                </div>
              </div>

              <div class="mn-subcard" id="sf-bottle-card" style="display:none;">
                <h4 class="mn-subtitle">Bottle / Tot Setup</h4>
                <div class="row">
                  <div class="col-md-3">
                    <div class="form-group">
                      <label>Bottle Ml</label>
                      <input type="number" class="form-control" id="sf-bottle-ml" value="750" min="1">
                    </div>
                  </div>
                  <div class="col-md-3">
                    <div class="form-group">
                      <label>Shot Ml</label>
                      <input type="number" class="form-control" id="sf-shot-ml" value="50" min="1">
                    </div>
                  </div>
                  <div class="col-md-3">
                    <div class="form-group">
                      <label>Empty Weight</label>
                      <input type="number" class="form-control" id="sf-empty-weight" value="0" min="0" step="0.01">
                    </div>
                  </div>
                  <div class="col-md-3">
                    <div class="form-group">
                      <label>Full Weight</label>
                      <input type="number" class="form-control" id="sf-full-weight" value="0" min="0" step="0.01">
                    </div>
                  </div>
                </div>
              </div>

              <div class="mn-subcard" id="sf-recipe-card" style="display:none;">
                <h4 class="mn-subtitle">Recipe Rules</h4>
                <p class="mn-kv">This sellable item should later open into recipe linking where raw materials and their quantities are attached.</p>
              </div>
              <div>
                <button type="button" class="btn btn-success btn-sm" id="save-sellable-draft">Save Sellable Draft</button>
              </div>
            </form>
          </div>

          <div class="col-md-5">
            <div class="mn-sim-card">
              <h4 class="mn-subtitle">Sellable Item Summary</h4>
              <div id="sf-summary-banner" class="mn-banner"></div>
              <div>
                <span class="mn-metric" id="sf-metric-type">Type: Sellable</span>
                <span class="mn-metric" id="sf-metric-control">Control: Direct Sell</span>
                <span class="mn-metric" id="sf-metric-price">Price: Fixed</span>
              </div>
            </div>

            <div class="mn-card">
              <h4>Operational Effect</h4>
              <p class="mn-kv" id="sf-effect-text">
                This item will sell directly on POS with a normal fixed-price flow.
              </p>
              <h5>What This Form Confirms</h5>
              <ul class="mn-list">
                <li>Whether the item is shown on POS</li>
                <li>How price behaves during selling</li>
                <li>Whether stock comes from recipe, batch, or bottle logic</li>
                <li>Whether attributes affect kitchen instructions or pricing</li>
              </ul>
            </div>

            <div class="mn-card">
              <h4>Next Save Model</h4>
              <table class="mn-table">
                <tbody>
                  <tr>
                    <th style="width:34%;">Field Group</th>
                    <th>Planned Destination</th>
                  </tr>
                  <tr>
                    <td>Basic item fields</td>
                    <td>Item master in the new isolated flow</td>
                  </tr>
                  <tr>
                    <td>Control method</td>
                    <td>Item behavior model</td>
                  </tr>
                  <tr>
                    <td>Pricing mode</td>
                    <td>POS sale rules</td>
                  </tr>
                  <tr>
                    <td>Attributes</td>
                    <td>Attribute mapping and optional price impact</td>
                  </tr>
                  <tr>
                    <td>Batch / bottle settings</td>
                    <td>Dedicated detail tables or isolated config records</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <div class="mn-panel" id="raw-form">
        <h3 class="mn-panel-title">Raw Material Form</h3>
        <div class="mn-banner mn-ok">
          This form models raw materials separately from sellable items. It helps us decide how purchases, stock units,
          conversions, reorder levels, and kitchen issue behavior should work before we connect saving logic.
        </div>

        <div class="row">
          <div class="col-md-7">
            <form class="mn-form" id="mn-raw-form">
              <div class="mn-subcard">
                <h4 class="mn-subtitle">Basic Raw Material Details</h4>
                <div class="row">
                  <div class="col-md-8">
                    <div class="form-group">
                      <label>Raw Material Name</label>
                      <input type="text" class="form-control" id="rf-name" placeholder="e.g. Potatoes, Tomatoes, Flour, Cooking Oil">
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label>Item Code</label>
                      <input type="text" class="form-control" id="rf-code" placeholder="Optional code">
                    </div>
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-4">
                    <div class="form-group">
                      <label>Category</label>
                      <select class="form-control" id="rf-category">
                        <option>Dry Store</option>
                        <option>Fresh Produce</option>
                        <option>Butchery</option>
                        <option>Bar Stock</option>
                        <option>General Consumables</option>
                      </select>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label>Store Area</label>
                      <select class="form-control" id="rf-store-area">
                        <option>Main Store</option>
                        <option>Kitchen Store</option>
                        <option>Cold Room</option>
                        <option>Bar Store</option>
                      </select>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label>Control Method</label>
                      <select class="form-control" id="rf-control-method">
                        <option value="simple_issue">Simple Issue</option>
                        <option value="conversion">Conversion Managed</option>
                        <option value="recipe">Recipe Controlled</option>
                        <option value="batch_source">Batch Source Item</option>
                      </select>
                    </div>
                  </div>
                </div>
              </div>

              <div class="mn-subcard">
                <h4 class="mn-subtitle">Purchase and Stock Units</h4>
                <div class="row">
                  <div class="col-md-3">
                    <div class="form-group">
                      <label>Purchase Unit</label>
                      <select class="form-control" id="rf-purchase-unit">
                        <option value="kg">Kg</option>
                        <option value="g">Grams</option>
                        <option value="bag">Bag</option>
                        <option value="crate">Crate</option>
                        <option value="litre">Litre</option>
                        <option value="ml">Ml</option>
                        <option value="pcs">Pieces</option>
                      </select>
                    </div>
                  </div>
                  <div class="col-md-3">
                    <div class="form-group">
                      <label>Stock Unit</label>
                      <select class="form-control" id="rf-stock-unit">
                        <option value="kg">Kg</option>
                        <option value="g">Grams</option>
                        <option value="litre">Litre</option>
                        <option value="ml">Ml</option>
                        <option value="pcs">Pieces</option>
                        <option value="portion">Portions</option>
                      </select>
                    </div>
                  </div>
                  <div class="col-md-3">
                    <div class="form-group">
                      <label>Purchase Price</label>
                      <input type="number" class="form-control" id="rf-purchase-price" value="0" min="0" step="0.01">
                    </div>
                  </div>
                  <div class="col-md-3">
                    <div class="form-group">
                      <label>Reorder Level</label>
                      <input type="number" class="form-control" id="rf-reorder" value="0" min="0" step="0.01">
                    </div>
                  </div>
                </div>
              </div>

              <div class="mn-subcard" id="rf-conversion-card">
                <h4 class="mn-subtitle">Conversion Setup</h4>
                <div class="row">
                  <div class="col-md-4">
                    <div class="form-group">
                      <label>Conversion Basis</label>
                      <select class="form-control" id="rf-conversion-basis">
                        <option value="ratio">Ratio</option>
                        <option value="weight">Weight</option>
                        <option value="volume">Volume</option>
                      </select>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label>1 Purchase Unit Equals</label>
                      <input type="number" class="form-control" id="rf-conversion-qty" value="1" min="0" step="0.01">
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label>Usage / Stock Unit</label>
                      <input type="text" class="form-control" id="rf-conversion-unit" placeholder="e.g. kg, g, portions">
                    </div>
                  </div>
                </div>
                <p class="mn-kv">Example: 1 bag flour = 2 kg, or 1 crate tomatoes = 25 kg.</p>
              </div>

              <div class="mn-subcard" id="rf-issue-card">
                <h4 class="mn-subtitle">Issue Behaviour</h4>
                <label class="mn-checkline"><input type="checkbox" id="rf-purchasable" checked> This item is purchased from suppliers</label>
                <label class="mn-checkline"><input type="checkbox" id="rf-stockable" checked> This item is tracked in store stock</label>
                <label class="mn-checkline"><input type="checkbox" id="rf-direct-issue" checked> This item can be issued directly to kitchen/bar</label>
                <label class="mn-checkline"><input type="checkbox" id="rf-low-control"> This item uses low-control issue logic instead of strict per-sale deduction</label>
              </div>

              <div class="mn-subcard" id="rf-source-card" style="display:none;">
                <h4 class="mn-subtitle">Batch / Recipe Source Role</h4>
                <p class="mn-kv">
                  This raw material is expected to feed recipe deduction or batch production flows in later steps.
                </p>
              </div>
              <div>
                <button type="button" class="btn btn-success btn-sm" id="save-raw-draft">Save Raw Material Draft</button>
              </div>
            </form>
          </div>

          <div class="col-md-5">
            <div class="mn-sim-card">
              <h4 class="mn-subtitle">Raw Material Summary</h4>
              <div id="rf-summary-banner" class="mn-banner"></div>
              <div>
                <span class="mn-metric" id="rf-metric-type">Type: Raw Material</span>
                <span class="mn-metric" id="rf-metric-control">Control: Simple Issue</span>
                <span class="mn-metric" id="rf-metric-unit">Unit: Kg</span>
              </div>
            </div>

            <div class="mn-card">
              <h4>Operational Effect</h4>
              <p class="mn-kv" id="rf-effect-text">
                This raw material will be purchased, stocked, and issued directly to operations.
              </p>
              <h5>What This Form Confirms</h5>
              <ul class="mn-list">
                <li>Whether the item is purchased and tracked in stock</li>
                <li>How purchase unit converts to usage unit</li>
                <li>Whether kitchen uses simple issue or strict conversion logic</li>
                <li>Whether the item is mainly a recipe source or a batch source</li>
              </ul>
            </div>

            <div class="mn-card">
              <h4>Examples</h4>
              <table class="mn-table">
                <tbody>
                  <tr>
                    <th style="width:34%;">Item</th>
                    <th>Expected Model</th>
                  </tr>
                  <tr>
                    <td>Tomatoes</td>
                    <td>Raw material with simple issue or conversion by kg</td>
                  </tr>
                  <tr>
                    <td>Potatoes</td>
                    <td>Raw material with conversion-managed stock</td>
                  </tr>
                  <tr>
                    <td>Maize Flour</td>
                    <td>Raw material that can feed batch production for ugali</td>
                  </tr>
                  <tr>
                    <td>Salt</td>
                    <td>Raw material with low-control simple issue</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <div class="mn-panel" id="batch-form">
        <h3 class="mn-panel-title">Batch Item Form</h3>
        <div class="mn-banner mn-ok">
          This form models foods prepared in batches and later sold as portions. It is meant for items like ugali, chapati, rice, and similar kitchen output.
        </div>

        <div class="row">
          <div class="col-md-7">
            <form class="mn-form" id="mn-batch-form">
              <div class="mn-subcard">
                <h4 class="mn-subtitle">Batch Product Details</h4>
                <div class="row">
                  <div class="col-md-8">
                    <div class="form-group">
                      <label>Batch Item Name</label>
                      <input type="text" class="form-control" id="bf-name" placeholder="e.g. Ugali, Chapati, Pilau Rice">
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label>Department</label>
                      <select class="form-control" id="bf-department">
                        <option>Kitchen</option>
                        <option>Bakery</option>
                        <option>General</option>
                      </select>
                    </div>
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-4">
                    <div class="form-group">
                      <label>Category</label>
                      <select class="form-control" id="bf-category">
                        <option>Main Meals</option>
                        <option>Sides</option>
                        <option>Breakfast</option>
                        <option>Bakery</option>
                      </select>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label>Selling Price</label>
                      <input type="number" class="form-control" id="bf-price" value="0" min="0" step="0.01">
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label>Sale Unit</label>
                      <input type="text" class="form-control" id="bf-sale-unit" placeholder="e.g. Portion, Piece">
                    </div>
                  </div>
                </div>
              </div>

              <div class="mn-subcard">
                <h4 class="mn-subtitle">Production Rules</h4>
                <div class="row">
                  <div class="col-md-5">
                    <div class="form-group">
                      <label>Main Source Item</label>
                      <input type="text" class="form-control" id="bf-source-item" placeholder="e.g. 2kg maize flour bag">
                    </div>
                  </div>
                  <div class="col-md-3">
                    <div class="form-group">
                      <label>Source Qty</label>
                      <input type="number" class="form-control" id="bf-source-qty" value="1" min="0" step="0.01">
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label>Source Unit</label>
                      <input type="text" class="form-control" id="bf-source-unit" placeholder="e.g. bag, kg">
                    </div>
                  </div>
                </div>

                <div class="row">
                  <div class="col-md-4">
                    <div class="form-group">
                      <label>Yield Per Batch</label>
                      <input type="number" class="form-control" id="bf-yield" value="12" min="1" step="1">
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label>Production Unit</label>
                      <input type="text" class="form-control" id="bf-production-unit" placeholder="e.g. portions">
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label>Batch Mode</label>
                      <select class="form-control" id="bf-mode">
                        <option value="single_source">Single Source</option>
                        <option value="multi_source">Multiple Sources</option>
                      </select>
                    </div>
                  </div>
                </div>

                <label class="mn-checkline"><input type="checkbox" id="bf-pos" checked> Show produced item on POS</label>
                <label class="mn-checkline"><input type="checkbox" id="bf-stock-output" checked> Production increases sellable stock</label>
                <label class="mn-checkline"><input type="checkbox" id="bf-source-deduct" checked> Production deducts source stock</label>
              </div>

              <div class="mn-subcard">
                <h4 class="mn-subtitle">Kitchen Notes</h4>
                <div class="form-group">
                  <label>Notes</label>
                  <textarea class="form-control" id="bf-notes" rows="4" placeholder="Optional kitchen rule or production note"></textarea>
                </div>
              </div>
              <div>
                <button type="button" class="btn btn-success btn-sm" id="save-batch-draft">Save Batch Draft</button>
              </div>
            </form>
          </div>

          <div class="col-md-5">
            <div class="mn-sim-card">
              <h4 class="mn-subtitle">Batch Item Summary</h4>
              <div id="bf-summary-banner" class="mn-banner"></div>
              <div>
                <span class="mn-metric" id="bf-metric-type">Type: Batch Item</span>
                <span class="mn-metric" id="bf-metric-yield">Yield: 12</span>
                <span class="mn-metric" id="bf-metric-unit">Unit: Portions</span>
              </div>
            </div>

            <div class="mn-card">
              <h4>Operational Effect</h4>
              <p class="mn-kv" id="bf-effect-text">
                This batch item will consume source stock and add sellable portions to POS stock.
              </p>
              <h5>What This Form Confirms</h5>
              <ul class="mn-list">
                <li>The produced item that appears on POS</li>
                <li>The source stock item used in preparation</li>
                <li>How many portions a batch creates</li>
                <li>Whether production adds stock and deducts raw materials</li>
              </ul>
            </div>
          </div>
        </div>
      </div>

      <div class="mn-panel" id="bottle-form">
        <h3 class="mn-panel-title">Bottle / Tot Form</h3>
        <div class="mn-banner mn-ok">
          This form models bottle-based bar stock where sales are made in shots or tots and current balance can be estimated from ml and bottle weights.
        </div>

        <div class="row">
          <div class="col-md-7">
            <form class="mn-form" id="mn-bottle-form">
              <div class="mn-subcard">
                <h4 class="mn-subtitle">Bottle Item Details</h4>
                <div class="row">
                  <div class="col-md-8">
                    <div class="form-group">
                      <label>Sellable Item Name</label>
                      <input type="text" class="form-control" id="tf-name" placeholder="e.g. Jameson Tot, Smirnoff Shot">
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label>Department</label>
                      <select class="form-control" id="tf-department">
                        <option>Bar</option>
                        <option>Lounge</option>
                      </select>
                    </div>
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label>Linked Bottle Item</label>
                      <input type="text" class="form-control" id="tf-bottle-name" placeholder="e.g. Jameson 750ml Bottle">
                    </div>
                  </div>
                  <div class="col-md-3">
                    <div class="form-group">
                      <label>Selling Price</label>
                      <input type="number" class="form-control" id="tf-price" value="0" min="0" step="0.01">
                    </div>
                  </div>
                  <div class="col-md-3">
                    <div class="form-group">
                      <label>Shot Name</label>
                      <input type="text" class="form-control" id="tf-shot-name" placeholder="Tot">
                    </div>
                  </div>
                </div>
              </div>

              <div class="mn-subcard">
                <h4 class="mn-subtitle">Bottle Logic</h4>
                <div class="row">
                  <div class="col-md-3">
                    <div class="form-group">
                      <label>Bottle Ml</label>
                      <input type="number" class="form-control" id="tf-bottle-ml" value="750" min="1">
                    </div>
                  </div>
                  <div class="col-md-3">
                    <div class="form-group">
                      <label>Shot Ml</label>
                      <input type="number" class="form-control" id="tf-shot-ml" value="50" min="1">
                    </div>
                  </div>
                  <div class="col-md-3">
                    <div class="form-group">
                      <label>Empty Weight</label>
                      <input type="number" class="form-control" id="tf-empty-weight" value="0" min="0" step="0.01">
                    </div>
                  </div>
                  <div class="col-md-3">
                    <div class="form-group">
                      <label>Full Weight</label>
                      <input type="number" class="form-control" id="tf-full-weight" value="0" min="0" step="0.01">
                    </div>
                  </div>
                </div>

                <div class="row">
                  <div class="col-md-4">
                    <div class="form-group">
                      <label>Measured Current Weight</label>
                      <input type="number" class="form-control" id="tf-current-weight" value="0" min="0" step="0.01">
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label>Pricing Mode</label>
                      <select class="form-control" id="tf-pricing-mode">
                        <option value="fixed">Fixed Price</option>
                        <option value="permission">Permission-Based Price Change</option>
                      </select>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <label class="mn-checkline" style="margin-top:28px;"><input type="checkbox" id="tf-pos" checked> Show on POS</label>
                  </div>
                </div>
              </div>

              <div class="mn-subcard">
                <h4 class="mn-subtitle">Control Notes</h4>
                <label class="mn-checkline"><input type="checkbox" id="tf-track-weight" checked> Use bottle weight as a support check</label>
                <label class="mn-checkline"><input type="checkbox" id="tf-track-ml" checked> Use ml as the official stock basis</label>
                <label class="mn-checkline"><input type="checkbox" id="tf-allow-open-bottle"> Allow open bottle counting workflow</label>
              </div>
              <div>
                <button type="button" class="btn btn-success btn-sm" id="save-bottle-draft">Save Bottle / Tot Draft</button>
              </div>
            </form>
          </div>

          <div class="col-md-5">
            <div class="mn-sim-card">
              <h4 class="mn-subtitle">Bottle / Tot Summary</h4>
              <div id="tf-summary-banner" class="mn-banner"></div>
              <div>
                <span class="mn-metric" id="tf-metric-type">Type: Bottle / Tot</span>
                <span class="mn-metric" id="tf-metric-shots">Shots: 15</span>
                <span class="mn-metric" id="tf-metric-remaining">Remain: 0 ml</span>
              </div>
            </div>

            <div class="mn-card">
              <h4>Operational Effect</h4>
              <p class="mn-kv" id="tf-effect-text">
                This bottle item will use ml and shot logic to estimate available shots and current bottle balance.
              </p>
              <h5>What This Form Confirms</h5>
              <ul class="mn-list">
                <li>The bottle item that acts as stock source</li>
                <li>The shot size used for sale</li>
                <li>Estimated shots per bottle</li>
                <li>Optional bottle-weight based control checks</li>
              </ul>
            </div>
          </div>
        </div>
      </div>

      <div class="mn-panel" id="drafts">
        <h3 class="mn-panel-title">Saved Drafts</h3>
        <div class="mn-banner mn-ok">
          These drafts are stored only inside `gpints/back/menu_new/data/items.json` so we can experiment safely before touching the live menu module.
        </div>
        <div id="mn-drafts-list"></div>
      </div>
    </div>
  </div>
</div>

<script src="../../assets/plugins/jquery/jquery-3.2.1.min.js"></script>
<script src="../../assets/plugins/jquery-ui/jquery-ui.min.js"></script>
<script src="../../assets/plugins/jquery/jquery-migrate-1.1.0.min.js"></script>
<script src="../../assets/plugins/bootstrap/4.0.0/js/bootstrap.bundle.min.js"></script>
<script src="../../assets/plugins/slimscroll/jquery.slimscroll.min.js"></script>
<script src="../../assets/plugins/js-cookie/js.cookie.js"></script>
<script src="../../assets/js/theme/default.min.js"></script>
<script src="../../assets/js/apps.min.js"></script>
<script src="menu_new.js"></script>
<script>
  $(document).ready(function () {
    App.init();
  });
</script>
</body>
</html>
