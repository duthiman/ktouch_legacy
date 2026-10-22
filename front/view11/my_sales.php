<?php
define('KT_LIGHTWEIGHT_PAGE', true);
include("../clude/head.php");
$user_id = 0;
$shift = '';
$sales_rows = [];
$sold_rows = $merge_cards = $merge_requests = $finished_merge_rows = $finished_split_rows = [];
$active_merge_requests = 0;
?>
<style>
  body {
    background: url("../assets/img/bg/a2.jpg") no-repeat;
    -webkit-background-size: cover;
    -moz-background-size: cover;
    -o-background-size: cover;
    background-size: cover;
  }
  .desktop-shell { display: block; }
  .mobile-shell { display: none; }
  /* This page is sales-only. Merge and split flows live on their own pages. */
  [data-tab-panel="merge"],
  [data-tab-panel="split"],
  [data-tab-panel="done-merge"],
  [data-tab-panel="done-split"],
  #merge_search_modal { display: none !important; }
  .sales-topbar {
    margin: 18px 0 10px;
    padding: 14px 18px;
    border-radius: 14px;
    background: rgba(255,255,255,0.9);
    box-shadow: 0 10px 24px rgba(0,0,0,0.08);
  }
  #content.content {
    margin-left: 0 !important;
    padding: 0 !important;
  }
  .sales-content-row {
    margin: 0;
    display: flex;
    flex-wrap: wrap;
    align-items: flex-start;
    gap: 12px;
  }
  .sales-main-col,
  .sales-side-col {
    padding-left: 0;
    padding-right: 0;
    margin: 0 !important;
  }
  .sales-main-col {
    flex: 0 0 calc(62% - 6px);
    max-width: calc(62% - 6px);
  }
  .sales-side-col {
    flex: 0 0 calc(38% - 6px);
    max-width: calc(38% - 6px);
  }
  .sales-panel-search {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 120px;
    gap: 10px;
    margin-bottom: 12px;
  }
  .item-orders-btn {
    min-height: 32px;
    padding: 0 10px;
    border: 0;
    border-radius: 8px;
    background: #ead9b4;
    color: #6b3d13;
    font-size: 11px;
    font-weight: 900;
    text-transform: uppercase;
  }
  .sales-topbar-row {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 14px;
    flex-wrap: wrap;
  }
  .sales-topbar h2 {
    margin: 0;
    font-size: 24px;
    font-weight: 900;
    color: #7a2f12;
    text-transform: uppercase;
  }
  .sales-topbar p {
    margin: 6px 0 0;
    color: #6a5837;
    font-weight: 700;
  }
  .sales-top-actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    justify-content: flex-end;
  }
  .sales-top-link {
    min-height: 44px;
    padding: 0 16px;
    border-radius: 12px;
    background: linear-gradient(135deg, #7e2014 0%, #b94828 52%, #e7a33a 100%);
    color: #fff !important;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    text-decoration: none !important;
    font-weight: 900;
    text-transform: uppercase;
    box-shadow: 0 10px 18px rgba(73, 35, 12, 0.16);
  }
  .filter-bar {
    display: grid;
    grid-template-columns: 1fr 180px;
    gap: 10px;
    margin-top: 12px;
  }
  .search-keyboard {
    display: none;
    grid-template-columns: repeat(10, minmax(0, 1fr));
    gap: 6px;
    margin-top: 10px;
    position: fixed;
    left: 50%;
    bottom: 18px;
    transform: translateX(-50%);
    width: min(1100px, calc(100vw - 40px));
    padding: 12px;
    background: rgba(255,255,255,0.98);
    border-radius: 14px;
    box-shadow: 0 18px 36px rgba(0,0,0,0.18);
    z-index: 1200;
  }
  .search-keyboard.show {
    display: grid;
  }
  .keyboard-toggle-bar {
    display: flex;
    justify-content: space-between;
    gap: 8px;
    grid-column: 1 / -1;
    margin-bottom: 2px;
  }
  .keyboard-toggle-btn {
    min-height: 36px;
    border: 0;
    border-radius: 8px;
    padding: 0 12px;
    background: #ead9b4;
    color: #6b3d13;
    font-weight: 900;
    text-transform: uppercase;
  }
  .keyboard-toggle-btn.active {
    background: linear-gradient(135deg, #7e2014 0%, #b94828 52%, #e7a33a 100%);
    color: #fff;
  }
  .keyboard-pane {
    display: contents;
  }
  .keyboard-pane.hidden {
    display: none;
  }
  .numpad-pane {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 10px;
    grid-column: 1 / -1;
    width: 300px;
    margin: 0 auto;
  }
  .numpad-key {
    min-height: 72px;
    border: 0;
    border-radius: 10px;
    background: linear-gradient(180deg, #fffdfa 0%, #f1e3bf 100%);
    border: 2px solid #62b647;
    color: #5d3514;
    font-size: 26px;
    font-weight: 900;
    box-shadow: 0 8px 16px rgba(0,0,0,0.08);
  }
  .numpad-key.action {
    background: #f06c26;
    color: #1f1308;
  }
  .key-btn {
    min-height: 38px;
    border: 0;
    border-radius: 8px;
    background: linear-gradient(180deg, #fffdfa 0%, #f1e3bf 100%);
    border: 1px solid rgba(120, 84, 42, 0.14);
    color: #5d3514;
    font-weight: 900;
    text-transform: uppercase;
  }
  .key-btn.wide {
    grid-column: span 2;
  }
  .filter-input,
  .filter-select {
    height: 44px;
    border-radius: 10px;
    border: 1px solid rgba(120, 84, 42, 0.18);
    padding: 8px 12px;
    font-weight: 700;
  }
  .sales-panel {
    border: 0;
    border-radius: 14px;
    overflow: hidden;
    box-shadow: 0 12px 24px rgba(0,0,0,0.08);
    background: rgba(255,255,255,0.94);
  }
  .sales-panel .panel-body {
    background: rgba(255,255,255,0.96) !important;
    max-height: 72vh;
    overflow-y: auto;
  }
  .sales-panel table {
    width: 100%;
    table-layout: fixed;
  }
  .sales-panel table th,
  .sales-panel table td {
    vertical-align: middle !important;
    word-break: break-word;
  }
  .sales-pagination {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    min-height: 34px;
    margin-top: 10px;
    color: #6f543a;
    font-size: 12px;
    font-weight: 800;
  }
  .sales-page-btn {
    min-width: 34px;
    border: 0;
    border-radius: 8px;
    background: #ead9b4;
    color: #6b3d13;
    font-weight: 900;
  }
  .sales-tabs {
    display: inline-grid;
    grid-template-columns: repeat(5, minmax(0, 1fr));
    gap: 8px;
    margin-top: 14px;
    padding: 8px;
    border-radius: 18px;
    background: #f2e6cf;
  }
  .sales-tab-btn {
    min-height: 42px;
    border: 0;
    border-radius: 12px;
    background: transparent;
    color: #73461b;
    font-size: 13px;
    font-weight: 900;
    text-transform: uppercase;
  }
  .sales-tab-btn.active {
    background: #fff;
    color: #1f7a32;
    box-shadow: 0 10px 18px rgba(0,0,0,0.08);
  }
  .sales-tab-panel[hidden] {
    display: none !important;
  }
  .merge-shell {
    display: grid;
    gap: 12px;
  }
  .merge-head-card,
  .merge-board,
  .merge-request-card,
  .merge-finished-card,
  .split-finished-card,
  .split-card {
    background: rgba(255,255,255,0.96);
    border-radius: 14px;
    box-shadow: 0 12px 24px rgba(0,0,0,0.08);
    padding: 14px;
  }
  .merge-head-card h4,
  .merge-board h4,
  .merge-request-card h4,
  .merge-finished-card h4,
  .split-finished-card h4,
  .split-card h4 {
    margin: 0 0 8px;
    color: #7a2f12;
    font-size: 18px;
    font-weight: 900;
    text-transform: uppercase;
  }
  .merge-head-card p,
  .merge-board p,
  .merge-request-card p,
  .merge-finished-card p,
  .split-finished-card p,
  .split-card p {
    margin: 0;
    color: #6a5837;
    font-weight: 700;
    font-size: 13px;
  }
  .merge-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(112px, 1fr));
    gap: 8px;
    margin-top: 12px;
    min-width: 0;
  }
  .merge-card {
    width: 100%;
    min-width: 0;
    padding: 9px;
    border-radius: 12px;
    border: 2px solid #ebd8c6;
    background: linear-gradient(180deg, #fffdf9 0%, #fff3e4 100%);
    text-align: left;
  }
  .merge-card.is-foreign {
    border-color: #e4c8a9;
    background: linear-gradient(180deg, #fffdf9 0%, #fff7ef 100%);
  }
  .merge-card.is-picked {
    border-color: #1f9d55;
    background: linear-gradient(180deg, #eef8ef 0%, #fff3e4 100%);
  }
  .merge-card small,
  .merge-basket small,
  .request-chip small {
    display: block;
    color: #9a7545;
    font-size: 10px;
    font-weight: 900;
    text-transform: uppercase;
  }
  .merge-card strong,
  .merge-basket strong,
  .request-chip strong {
    display: block;
    margin-top: 4px;
    color: #2d2116;
    font-size: 15px;
    font-weight: 900;
    line-height: 1.2;
    word-break: break-word;
  }
  .merge-card .meta,
  .request-chip .meta {
    margin-top: 6px;
    color: #6e563d;
    font-size: 10px;
    font-weight: 700;
    display: grid;
    gap: 2px;
  }
  .merge-two-col {
    display: grid;
    grid-template-columns: minmax(0, 1.55fr) minmax(0, .85fr);
    gap: 12px;
    align-items: start;
  }
  .merge-basket {
    min-width: 0;
    padding: 14px;
    border-radius: 16px;
    border: 1px solid #eadcca;
    background: linear-gradient(180deg, #fffdfa 0%, #fff8ef 100%);
  }
  .merge-side-stack {
    display: grid;
    gap: 12px;
    min-width: 0;
  }
  .merge-board {
    min-width: 0;
  }
  .merge-search-bar {
    display: grid;
    grid-template-columns: minmax(0, 1fr) auto;
    gap: 8px;
    margin-top: 12px;
    align-items: center;
  }
  .merge-search-input {
    min-height: 40px;
    border-radius: 10px;
    border: 1px solid rgba(120, 84, 42, 0.18);
    padding: 0 12px;
    font-size: 13px;
    font-weight: 800;
    background: #fff;
    color: #5d3514;
  }
  .merge-search-btn {
    min-height: 40px;
    padding: 0 12px;
    border: 0;
    border-radius: 10px;
    background: #ead9b4;
    color: #6b3d13;
    font-size: 12px;
    font-weight: 900;
    text-transform: uppercase;
  }
  .merge-pad-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 10px;
    margin-top: 12px;
  }
  .merge-pad-key {
    min-height: 64px;
    border: 0;
    border-radius: 12px;
    background: linear-gradient(180deg, #fffdfa 0%, #f1e3bf 100%);
    border: 2px solid #62b647;
    color: #5d3514;
    font-size: 24px;
    font-weight: 900;
    box-shadow: 0 8px 16px rgba(0,0,0,0.08);
  }
  .merge-pad-key.action {
    background: #f06c26;
    color: #1f1308;
  }
  .merge-basket-item {
    padding: 12px 0;
    border-bottom: 1px solid rgba(122, 84, 36, 0.12);
  }
  .merge-basket-item:last-child {
    border-bottom: 0;
  }
  .merge-basket-section-title {
    margin: 12px 0 6px;
    color: #9a7545;
    font-size: 10px;
    font-weight: 900;
    text-transform: uppercase;
    letter-spacing: 0.08em;
  }
  .merge-basket-status {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    margin-top: 6px;
    flex-wrap: wrap;
  }
  .merge-basket-actions {
    display: grid;
    gap: 10px;
    margin-top: 12px;
  }
  .merge-request-form {
    display: grid;
    grid-template-columns: minmax(0, 1fr) minmax(220px, 1fr) auto;
    gap: 10px;
    margin-top: 12px;
  }
  .merge-request-badge-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    min-height: 42px;
    padding: 0 14px;
    border: 0;
    border-radius: 12px;
    background: #fff;
    color: #7a2f12;
    font-weight: 900;
    text-transform: uppercase;
    box-shadow: 0 10px 18px rgba(0,0,0,0.08);
  }
  .merge-request-count {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 24px;
    height: 24px;
    padding: 0 7px;
    border-radius: 999px;
    background: #b94828;
    color: #fff;
    font-size: 11px;
    font-weight: 900;
  }
  .merge-request-input,
  .merge-request-select {
    min-height: 44px;
    border-radius: 10px;
    border: 1px solid rgba(120, 84, 42, 0.18);
    padding: 8px 12px;
    font-weight: 700;
  }
  .request-list {
    display: grid;
    gap: 10px;
    margin-top: 12px;
  }
  .merge-finished-list {
    display: grid;
    gap: 10px;
    margin-top: 12px;
  }
  .merge-finished-item {
    padding: 12px 14px;
    border-radius: 14px;
    border: 1px solid #eadcca;
    background: #fffaf3;
  }
  .merge-finished-meta {
    margin-top: 6px;
    color: #6e563d;
    font-size: 11px;
    font-weight: 700;
    display: grid;
    gap: 2px;
  }
  .request-chip {
    padding: 12px 14px;
    border-radius: 14px;
    border: 1px solid #eadcca;
    background: #fffaf3;
  }
  .request-chip .actions {
    display: flex;
    gap: 8px;
    margin-top: 10px;
    flex-wrap: wrap;
  }
  .status-pill {
    display: inline-flex;
    align-items: center;
    min-height: 26px;
    padding: 0 10px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 900;
    text-transform: uppercase;
  }
  .status-pending { background:#fff2cf; color:#8a6114; }
  .status-accepted { background:#e8f7ea; color:#1f7a32; }
  .status-rejected { background:#fde8e8; color:#9a2323; }
  .status-cancelled { background:#ececec; color:#666; }
  .status-used { background:#eef0f3; color:#4f5a67; }
  @media (max-width: 991px) {
    .merge-two-col,
    .merge-request-form {
      grid-template-columns: 1fr;
    }
    .sales-tabs {
      width: 100%;
    }
    .sales-topbar {
      margin: 14px 10px 10px;
    }
    .sales-content-row {
      display: block;
    }
    .sales-main-col,
    .sales-side-col {
      flex: 0 0 100%;
      max-width: 100%;
      padding-left: 0;
      padding-right: 0;
    }
    .sales-panel-search {
      grid-template-columns: 1fr;
    }
  }
  .sales-btn {
    font-family: 'comic sans ms';
    font-weight: bolder;
    color: #FFFFFF !important;
    font-size: 12px;
    min-height: 35px;
    background: #000;
    border: 4px solid #e0bd65;
    border-radius: 8px;
  }
  .sales-btn.ongoing-btn {
    background: #1f7a32;
    border-color: #92d46b;
  }
  .mobile-app {
    max-width: 960px;
    margin: 0 auto;
    padding: 0 8px 18px;
  }
  .mobile-topbar {
    position: sticky;
    top: 0;
    z-index: 20;
    margin-bottom: 10px;
    padding: 12px 14px;
    border-radius: 0 0 14px 14px;
    background: linear-gradient(135deg, #7e2014 0%, #b94828 52%, #e7a33a 100%);
    color: #fff8ea;
    box-shadow: 0 14px 28px rgba(74, 37, 11, 0.18);
  }
  .mobile-topbar h1 {
    margin: 0;
    font-size: 22px;
    font-weight: 900;
    text-transform: uppercase;
  }
  .mobile-topbar p {
    margin: 4px 0 0;
    font-size: 12px;
    font-weight: 700;
  }
  .mobile-actions {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
    margin-top: 10px;
  }
  .mobile-actions a {
    min-height: 48px;
    border-radius: 10px;
    background: rgba(255,255,255,0.16);
    color: #fff !important;
    display: flex;
    align-items: center;
    justify-content: center;
    text-decoration: none !important;
    font-weight: 900;
    text-transform: uppercase;
  }
  .mobile-card,
  .mobile-detail {
    background: rgba(255,255,255,0.94);
    border-radius: 12px;
    box-shadow: 0 12px 24px rgba(0,0,0,0.08);
    padding: 12px;
    margin-bottom: 10px;
  }
  .mobile-section-title {
    margin: 0 0 10px;
    font-size: 13px;
    font-weight: 900;
    color: #7e3a20;
    text-transform: uppercase;
    letter-spacing: 0.06em;
  }
  .mobile-scroll {
    max-height: 50vh;
    overflow-y: auto;
  }
  .sold-list {
    margin: 0;
    padding: 0;
    list-style: none;
  }
  .sold-list li {
    display: flex;
    justify-content: space-between;
    gap: 10px;
    padding: 10px 0;
    border-bottom: 1px solid rgba(120, 84, 42, 0.12);
  }
  .sold-list li:last-child { border-bottom: 0; }
  .sold-list span { color: #6a5837; font-weight: 700; }
  .sold-list b { color: #2f2416; }
  .empty-row td {
    text-align: center;
    font-weight: 700;
    color: #84653c;
  }
  @media (max-width: 991px) {
    body {
      background:
        radial-gradient(circle at top, rgba(246, 208, 137, 0.2), transparent 32%),
        linear-gradient(180deg, #f6f0d7 0%, #efe3bf 36%, #d3b67d 100%);
    }
    .desktop-shell { display: none; }
    .mobile-shell { display: block; }
    .filter-bar {
      grid-template-columns: 1fr;
    }
    .search-keyboard {
      display: none;
    }
    #modal .modal-dialog {
      width: calc(100vw - 24px);
      max-width: 640px;
      margin: 18px auto;
    }
  }
</style>
<body class="fixed-sn deep-orange-skin">
  <div class="desktop-shell">
    <div class="sales-topbar">
      <div class="sales-topbar-row">
        <div>
          <h2>My Sales</h2>
          <p><span class="sales-current-user"></span> | Shift <span class="sales-current-shift"></span></p>
        </div>
        <div class="sales-top-actions">
          <a href="../dash/dash.php" class="sales-top-link">Home</a>
          <a href="../log_out.php" class="sales-top-link">Log Out</a>
        </div>
      </div>
        <div class="sales-tabs">
          <button type="button" class="sales-tab-btn active" data-sales-tab="sales">Sales</button>
          <!-- <button type="button" class="sales-tab-btn" data-sales-tab="merge">Merge</button>
          <button type="button" class="sales-tab-btn" data-sales-tab="split">Split</button>
          <button type="button" class="sales-tab-btn" data-sales-tab="done-merge">Finished Merges</button>
          <button type="button" class="sales-tab-btn" data-sales-tab="done-split">Finished Splits</button> -->
        </div>
      <div class="search-keyboard">
        <div class="keyboard-toggle-bar">
          <button type="button" class="keyboard-toggle-btn active" id="toggle_alpha">Keyboard</button>
          <button type="button" class="keyboard-toggle-btn" id="toggle_numpad">Numpad</button>
        </div>
        <div class="keyboard-pane" id="alpha_keyboard">
        <button type="button" class="key-btn search-key" data-key="1">1</button>
        <button type="button" class="key-btn search-key" data-key="2">2</button>
        <button type="button" class="key-btn search-key" data-key="3">3</button>
        <button type="button" class="key-btn search-key" data-key="4">4</button>
        <button type="button" class="key-btn search-key" data-key="5">5</button>
        <button type="button" class="key-btn search-key" data-key="6">6</button>
        <button type="button" class="key-btn search-key" data-key="7">7</button>
        <button type="button" class="key-btn search-key" data-key="8">8</button>
        <button type="button" class="key-btn search-key" data-key="9">9</button>
        <button type="button" class="key-btn search-key" data-key="0">0</button>
        <button type="button" class="key-btn search-key" data-key="A">A</button>
        <button type="button" class="key-btn search-key" data-key="B">B</button>
        <button type="button" class="key-btn search-key" data-key="C">C</button>
        <button type="button" class="key-btn search-key" data-key="D">D</button>
        <button type="button" class="key-btn search-key" data-key="E">E</button>
        <button type="button" class="key-btn search-key" data-key="F">F</button>
        <button type="button" class="key-btn search-key" data-key="G">G</button>
        <button type="button" class="key-btn search-key" data-key="H">H</button>
        <button type="button" class="key-btn search-key" data-key="I">I</button>
        <button type="button" class="key-btn search-key" data-key="J">J</button>
        <button type="button" class="key-btn search-key" data-key="K">K</button>
        <button type="button" class="key-btn search-key" data-key="L">L</button>
        <button type="button" class="key-btn search-key" data-key="M">M</button>
        <button type="button" class="key-btn search-key" data-key="N">N</button>
        <button type="button" class="key-btn search-key" data-key="O">O</button>
        <button type="button" class="key-btn search-key" data-key="P">P</button>
        <button type="button" class="key-btn search-key" data-key="Q">Q</button>
        <button type="button" class="key-btn search-key" data-key="R">R</button>
        <button type="button" class="key-btn search-key" data-key="S">S</button>
        <button type="button" class="key-btn search-key" data-key="T">T</button>
        <button type="button" class="key-btn search-key" data-key="U">U</button>
        <button type="button" class="key-btn search-key" data-key="V">V</button>
        <button type="button" class="key-btn search-key" data-key="W">W</button>
        <button type="button" class="key-btn search-key" data-key="X">X</button>
        <button type="button" class="key-btn search-key" data-key="Y">Y</button>
        <button type="button" class="key-btn search-key" data-key="Z">Z</button>
        <button type="button" class="key-btn search-key" data-key=".">.</button>
        <button type="button" class="key-btn search-key wide" data-key=" ">Space</button>
        <button type="button" class="key-btn wide" id="search_backspace">Back</button>
        <button type="button" class="key-btn wide" id="search_clear">Clear</button>
        </div>
        <div class="keyboard-pane hidden numpad-pane" id="num_keyboard">
          <button type="button" class="numpad-key search-key" data-key="7">7</button>
          <button type="button" class="numpad-key search-key" data-key="8">8</button>
          <button type="button" class="numpad-key search-key" data-key="9">9</button>
          <button type="button" class="numpad-key search-key" data-key="4">4</button>
          <button type="button" class="numpad-key search-key" data-key="5">5</button>
          <button type="button" class="numpad-key search-key" data-key="6">6</button>
          <button type="button" class="numpad-key search-key" data-key="1">1</button>
          <button type="button" class="numpad-key search-key" data-key="2">2</button>
          <button type="button" class="numpad-key search-key" data-key="3">3</button>
          <button type="button" class="numpad-key action" id="search_clear_num">Clear</button>
          <button type="button" class="numpad-key search-key" data-key="0">0</button>
          <button type="button" class="numpad-key action" id="search_backspace_num">Back</button>
        </div>
      </div>
    </div>
    <div id="content" class="content m-l-40">
      <div class="row sales-tab-panel sales-content-row" data-tab-panel="sales">
        <div class="panel sales-panel col-md-7 col-xs-12 m-5 sales-main-col">
          <div class="panel-body">
            <div class="sales-panel-search">
              <input type="text" class="filter-input" id="sales_filter_desktop_panel" placeholder="Search sale number" readonly>
              <button type="button" class="merge-search-btn" id="open_sales_search_pad">Search</button>
            </div>
            <select id="sales_filter_desktop_panel_type_hidden" style="display:none;">
              <option value="bill" selected>Bill No</option>
            </select>
            <table class="table table-striped">
              <thead>
                <tr>
                  <th style="width: 90px;">#</th>
                  <th style="width: 90px;">Shift</th>
                  <th>Bill No.</th>
                  <th>Counter</th>
                  <th>Bill Total</th>
                  <th class="text-center">Action</th>
                </tr>
              </thead>
              <tbody id="desktop_sales_rows">
                <?php if (empty($sales_rows)) { ?>
                <tr class="empty-row"><td colspan="6">You have not placed any orders today</td></tr>
                <?php } else { ?>
                <?php foreach ($sales_rows as $index => $row) { ?>
                <tr class="sale-row" data-bill="<?php echo strtolower($row['sale_id']); ?>" data-item="<?php echo htmlspecialchars($row['search']); ?>" data-amount="<?php echo strtolower(number_format((float)$row['total'], 2)); ?>" data-kind="<?php echo htmlspecialchars($row['kind']); ?>">
                  <td><?php echo $index + 1; ?></td>
                  <td><?php echo $row['shift']; ?></td>
                  <td><?php echo htmlspecialchars($row['label']); ?></td>
                  <td><?php echo htmlspecialchars($row['counter'] ?? ''); ?></td>
                  <td><?php echo number_format((float)$row['total'], 2); ?></td>
                  <td>
                    <?php if ($row['kind'] === 'merge') { ?>
                    <button type="button" data-merge-id="<?php echo (int)$row['id']; ?>" class="btn btn-sm sales-btn view-merge-review">VIEW</button>
                    <?php } else { ?>
                    <button type="button" data-id="<?php echo $row['sale_id']; ?>" class="btn btn-sm sales-btn view-sale">VIEW</button>
                    <?php } ?>
                  </td>
                </tr>
                <?php } ?>
                <?php } ?>
              </tbody>
            </table>
            <div class="sales-pagination" id="desktop_sales_pagination"></div>
          </div>
        </div>
        <div class="panel sales-panel col-md-4 col-xs-12 m-5 sales-side-col">
          <div class="panel-body" id="cont">
            <table class="table table-striped">
              <thead>
                <tr>
                  <th colspan="3"><h4 class="text-center"><b><?php echo $shift; ?> Items Sold</b></h4></th>
                </tr>
                <tr>
                  <th>No:</th>
                  <th>Item</th>
                  <th>Qty</th>
                  <th>Orders</th>
                </tr>
              </thead>
              <tbody id="desktop_sold_rows">
                <?php if (empty($sold_rows)) { ?>
                <tr class="empty-row"><td colspan="4">No items sold yet</td></tr>
                <?php } else { ?>
                <?php foreach ($sold_rows as $index => $row) { ?>
                <tr>
                  <td><?php echo $index + 1; ?></td>
                  <td><?php echo $row['item']; ?></td>
                  <td><?php echo $row['qty']; ?></td>
                  <td><button type="button" class="item-orders-btn" data-item-name="<?php echo htmlspecialchars($row['item'], ENT_QUOTES); ?>">Orders</button></td>
                </tr>
                <?php } ?>
                <?php } ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
      <div class="sales-tab-panel" data-tab-panel="merge" hidden>
        <div class="merge-shell">
          <div class="merge-head-card">
            <h4>Merge My Bills</h4>
            <p>You can merge your own unpaid bills directly. To merge another person's bill with yours, send a request and wait for the owner to accept it.</p>
          </div>
          <div class="merge-two-col">
            <div class="merge-board">
              <h4>My Eligible Bills</h4>
              <p>Select your own standalone bills or open chains to prepare one merge.</p>
              <div class="merge-search-bar">
                <input type="text" id="merge_bill_search" class="merge-search-input" placeholder="Search bill number" readonly>
                <button type="button" class="merge-search-btn" id="open_merge_search_pad">Search</button>
              </div>
              <div class="merge-grid" id="my-merge-grid">
                <?php if (empty($merge_cards)) { ?>
                  <div class="request-chip"><strong>No unpaid bills available</strong><div class="meta"><span>Your eligible bills for merge will appear here.</span></div></div>
                <?php } else { ?>
                  <?php foreach ($merge_cards as $card) { ?>
                    <button
                      type="button"
                      class="merge-card<?php echo !$card['is_mine'] ? ' is-foreign' : ''; ?>"
                      data-own-merge-sale="<?php echo (int)$card['sale_id']; ?>"
                      data-own-merge-amount="<?php echo number_format((float)$card['amount'], 2, '.', ''); ?>"
                      data-own-merge-type="<?php echo htmlspecialchars($card['item_type']); ?>"
                      data-own-merge-counter="<?php echo htmlspecialchars($card['counter']); ?>"
                      data-own-merge-user="<?php echo htmlspecialchars($card['owner_name']); ?>"
                      data-own-merge-owner-id="<?php echo (int)$card['owner_id']; ?>"
                      data-own-merge-mine="<?php echo $card['is_mine'] ? '1' : '0'; ?>"
                      data-own-merge-approved="<?php echo $card['approved_for_merge'] ? '1' : '0'; ?>"
                      data-own-merge-approved-requester-sale="<?php echo (int)$card['approved_requester_sale_id']; ?>"
                    >
                      <small><?php echo $card['item_type'] === 'open_child' ? 'Child Bill' : 'Bill'; ?></small>
                      <strong>#<?php echo (int)$card['sale_id']; ?></strong>
                      <div class="meta">
                        <span><b>User:</b> <?php echo htmlspecialchars($card['owner_name']); ?></span>
                        <span><b>Amount:</b> <?php echo number_format((float)$card['amount'], 2); ?></span>
                        <?php if ($card['approved_for_merge']) { ?>
                        <span><b>Status:</b> Approved For Merge</span>
                        <?php } ?>
                      </div>
                    </button>
                  <?php } ?>
                <?php } ?>
              </div>
            </div>
            <div class="merge-side-stack">
              <div class="merge-request-card">
                <h4>Merge Requests</h4>
                <p>Tap another user's bill to request approval using the bill already in your merge basket. Requests tied to this merge show here with their live status.</p>
                <?php if ($active_merge_requests > 0) { ?>
                  <?php foreach ($merge_requests as $request) { ?>
                    <?php
                      $reqStatus = (int)$request['status'];
                      if (!in_array($reqStatus, array(1, 2), true)) {
                        continue;
                      }
                      $statusLabel = $reqStatus === 2 ? 'Accepted' : 'Pending';
                      $statusClass = $reqStatus === 2 ? 'status-accepted' : 'status-pending';
                      $isIncoming = (int)$request['target_user_id'] === (int)$user_id;
                    ?>
                    <div class="request-chip" style="margin-bottom:10px;">
                      <small><?php echo $isIncoming ? 'Incoming Request' : 'My Request'; ?></small>
                      <strong>#<?php echo (int)$request['requester_sale_id']; ?> + #<?php echo (int)$request['target_sale_id']; ?></strong>
                      <div class="meta">
                        <span><b>Status:</b> <span class="status-pill <?php echo $statusClass; ?>"><?php echo $statusLabel; ?></span></span>
                        <span><b>Requested:</b> <?php echo htmlspecialchars((string)$request['created_at']); ?></span>
                        <?php if (!empty($request['responded_at'])) { ?>
                        <span><b>Responded:</b> <?php echo htmlspecialchars((string)$request['responded_at']); ?></span>
                        <?php } ?>
                      </div>
                    </div>
                  <?php } ?>
                <?php } ?>
                <button type="button" class="merge-request-badge-btn" id="open_merge_requests">
                  Merge Requests
                  <span class="merge-request-count"><?php echo (int)$active_merge_requests; ?></span>
                </button>
              </div>
              <div class="merge-basket" id="my-merge-basket">
                <small>Merge Basket</small>
                <strong>0.00</strong>
                <p style="margin-top:8px;color:#6e563d;font-weight:700;">Select your own bills to merge them directly.</p>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="sales-tab-panel" data-tab-panel="split" hidden>
        <div class="split-card">
          <h4>Split Bills</h4>
          <p>Every unpaid bill you raised appears here, including child bills, so any specific bill can be chosen for split.</p>
          <div class="merge-grid" id="split-bill-grid">
            <?php if (empty($merge_cards)) { ?>
              <div class="request-chip"><strong>No unpaid bills available</strong><div class="meta"><span>Your split candidates will show here.</span></div></div>
            <?php } else { ?>
              <?php foreach ($merge_cards as $card) { ?>
                <button type="button" class="merge-card" data-split-sale="<?php echo (int)$card['sale_id']; ?>">
                  <small><?php echo $card['item_type'] === 'open_child' ? 'Child Bill' : 'Bill'; ?></small>
                  <strong>#<?php echo (int)$card['sale_id']; ?></strong>
                  <div class="meta">
                    <span><b>Amount:</b> <?php echo number_format((float)$card['amount'], 2); ?></span>
                    <span><b>Counter:</b> <?php echo htmlspecialchars($card['counter']); ?></span>
                    <?php if ((int)$card['root_sale_id'] !== (int)$card['sale_id']) { ?>
                    <span><b>Parent Bill:</b> #<?php echo (int)$card['root_sale_id']; ?></span>
                    <?php } ?>
                  </div>
                </button>
              <?php } ?>
            <?php } ?>
          </div>
        </div>
      </div>
      <div class="sales-tab-panel" data-tab-panel="done-merge" hidden>
        <div class="merge-finished-card">
          <h4>Finished Merges</h4>
          <p>Review merged bills here. They also appear in the sales list so they can still be merged into another bill later.</p>
          <div class="merge-finished-list">
            <?php if (empty($finished_merge_rows)) { ?>
              <div class="request-chip"><strong>No finished merges yet</strong><div class="meta"><span>Your completed merge groups will show here.</span></div></div>
            <?php } else { ?>
              <?php foreach ($finished_merge_rows as $merge_row) { ?>
                <div class="merge-finished-item">
                  <small>Merged Bill</small>
                  <strong><?php echo htmlspecialchars($merge_row['merge_no']); ?></strong>
                  <div class="merge-finished-meta">
                    <span><b>Total:</b> <?php echo number_format((float)$merge_row['total_amount'], 2); ?></span>
                    <span><b>Created:</b> <?php echo htmlspecialchars((string)$merge_row['created_at']); ?></span>
                  </div>
                  <button type="button" class="btn btn-default btn-sm view-merge-review" data-merge-id="<?php echo (int)$merge_row['merge_id']; ?>" style="margin-top:8px;">View Merge</button>
                </div>
              <?php } ?>
            <?php } ?>
          </div>
        </div>
      </div>
      <div class="sales-tab-panel" data-tab-panel="done-split" hidden>
        <div class="split-finished-card">
          <h4>Finished Splits</h4>
          <p>Completed split bills will appear here once the split flow is finalized.</p>
          <div class="merge-finished-list">
            <?php if (empty($finished_split_rows)) { ?>
              <div class="request-chip"><strong>No finished splits yet</strong><div class="meta"><span>Your completed split bills will show here.</span></div></div>
            <?php } ?>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="mobile-shell">
    <div class="mobile-app">
      <div class="mobile-topbar">
        <h1>My Sales</h1>
        <p><span class="sales-current-user"></span> | Shift <span class="sales-current-shift"></span></p>
        <div class="sales-tabs" style="margin-top:12px;">
          <button type="button" class="sales-tab-btn active" data-sales-tab="sales">Sales</button>
          <!-- <button type="button" class="sales-tab-btn" data-sales-tab="merge">Merge</button>
          <button type="button" class="sales-tab-btn" data-sales-tab="split">Split</button>
          <button type="button" class="sales-tab-btn" data-sales-tab="done-merge">Done Merge</button>
          <button type="button" class="sales-tab-btn" data-sales-tab="done-split">Done Split</button> -->
        </div>
        <div class="mobile-actions">
          <a href="../dash/dash.php">Home</a>
          <a href="../log_out.php">Log Out</a>
        </div>
      </div>
      <div class="mobile-card sales-tab-panel" data-tab-panel="sales">
        <p class="mobile-section-title">Recent Bills</p>
        <div class="filter-bar" style="margin-top:0;">
          <input type="text" class="filter-input" id="sales_filter_mobile" placeholder="Search sale number" readonly>
          <button type="button" class="merge-search-btn" id="open_sales_search_pad_mobile">Search</button>
        </div>
        <select id="sales_filter_mobile_type_hidden" style="display:none;">
          <option value="bill" selected>Bill No</option>
        </select>
        <div class="mobile-scroll">
          <table class="table table-striped table-bordered">
            <thead>
              <tr>
                <th>Bill</th>
                <th>Shift</th>
                <th>Counter</th>
                <th>Total</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody id="mobile_sales_rows">
              <?php if (empty($sales_rows)) { ?>
              <tr class="empty-row"><td colspan="5">You have not placed any orders today</td></tr>
              <?php } else { ?>
              <?php foreach ($sales_rows as $row) { ?>
              <tr class="sale-row-mobile" data-bill="<?php echo strtolower($row['sale_id']); ?>" data-item="<?php echo htmlspecialchars($row['search']); ?>" data-amount="<?php echo strtolower(number_format((float)$row['total'], 2)); ?>" data-kind="<?php echo htmlspecialchars($row['kind']); ?>">
                <td><?php echo htmlspecialchars($row['label']); ?></td>
                <td><?php echo $row['shift']; ?></td>
                <td><?php echo htmlspecialchars($row['counter'] ?? ''); ?></td>
                <td><?php echo number_format((float)$row['total'], 2); ?></td>
                <td>
                  <?php if ($row['kind'] === 'merge') { ?>
                  <button type="button" data-merge-id="<?php echo (int)$row['id']; ?>" class="btn btn-sm sales-btn view-merge-review">VIEW</button>
                  <?php } else { ?>
                  <button type="button" data-id="<?php echo $row['sale_id']; ?>" class="btn btn-sm sales-btn view-sale-mobile">VIEW</button>
                  <?php } ?>
                </td>
              </tr>
              <?php } ?>
              <?php } ?>
            </tbody>
          </table>
          <div class="sales-pagination" id="mobile_sales_pagination"></div>
        </div>
      </div>
      <div class="mobile-detail sales-tab-panel" id="mobile_cont" data-tab-panel="sales">
        <p class="mobile-section-title"><?php echo $shift; ?> Items Sold</p>
        <div class="mobile-scroll">
          <ul class="sold-list" id="mobile_sold_rows">
            <?php if (empty($sold_rows)) { ?>
            <li><span>No items sold yet</span><b>0</b></li>
            <?php } else { ?>
            <?php foreach ($sold_rows as $index => $row) { ?>
            <li>
              <span><?php echo $index + 1; ?>. <?php echo $row['item']; ?></span>
              <div style="display:flex; align-items:center; gap:8px;">
                <b><?php echo $row['qty']; ?></b>
                <button type="button" class="item-orders-btn" data-item-name="<?php echo htmlspecialchars($row['item'], ENT_QUOTES); ?>">Orders</button>
              </div>
            </li>
            <?php } ?>
            <?php } ?>
          </ul>
        </div>
      </div>
      <div class="mobile-card sales-tab-panel" data-tab-panel="merge" hidden>
        <div class="merge-shell">
          <div class="merge-head-card">
            <h4>Merge My Bills</h4>
            <p>Choose your own bills directly, or send a request for someone else's bill.</p>
          </div>
          <div class="merge-board">
            <h4>My Eligible Bills</h4>
            <div class="merge-search-bar">
              <input type="text" id="merge_bill_search_mobile" class="merge-search-input" placeholder="Search bill number" readonly>
              <button type="button" class="merge-search-btn" id="open_merge_search_pad_mobile">Search</button>
            </div>
            <div class="merge-grid" id="my-merge-grid-mobile">
              <?php foreach ($merge_cards as $card) { ?>
                <button
                  type="button"
                  class="merge-card<?php echo !$card['is_mine'] ? ' is-foreign' : ''; ?>"
                  data-own-merge-sale="<?php echo (int)$card['sale_id']; ?>"
                  data-own-merge-amount="<?php echo number_format((float)$card['amount'], 2, '.', ''); ?>"
                  data-own-merge-type="<?php echo htmlspecialchars($card['item_type']); ?>"
                  data-own-merge-counter="<?php echo htmlspecialchars($card['counter']); ?>"
                  data-own-merge-user="<?php echo htmlspecialchars($card['owner_name']); ?>"
                  data-own-merge-owner-id="<?php echo (int)$card['owner_id']; ?>"
                  data-own-merge-mine="<?php echo $card['is_mine'] ? '1' : '0'; ?>"
                  data-own-merge-approved="<?php echo $card['approved_for_merge'] ? '1' : '0'; ?>"
                  data-own-merge-approved-requester-sale="<?php echo (int)$card['approved_requester_sale_id']; ?>"
                >
                  <small><?php echo $card['item_type'] === 'open_child' ? 'Child Bill' : 'Bill'; ?></small>
                  <strong>#<?php echo (int)$card['sale_id']; ?></strong>
                  <div class="meta">
                    <span><b>User:</b> <?php echo htmlspecialchars($card['owner_name']); ?></span>
                    <span><b>Amount:</b> <?php echo number_format((float)$card['amount'], 2); ?></span>
                    <?php if ($card['approved_for_merge']) { ?>
                    <span><b>Status:</b> Approved For Merge</span>
                    <?php } ?>
                  </div>
                </button>
              <?php } ?>
            </div>
          </div>
          <div class="merge-basket" id="my-merge-basket-mobile">
            <small>Merge Basket</small>
            <strong>0.00</strong>
            <p style="margin-top:8px;color:#6e563d;font-weight:700;">Select bills to merge them directly.</p>
          </div>
          <div class="merge-request-card">
            <h4>Merge Requests</h4>
            <p>Tap another user's bill after you already have your bill in the basket, then follow the request status here.</p>
            <?php if ($active_merge_requests > 0) { ?>
              <?php foreach ($merge_requests as $request) { ?>
                <?php
                  $reqStatus = (int)$request['status'];
                  if (!in_array($reqStatus, array(1, 2), true)) {
                    continue;
                  }
                  $statusLabel = $reqStatus === 2 ? 'Accepted' : 'Pending';
                  $statusClass = $reqStatus === 2 ? 'status-accepted' : 'status-pending';
                  $isIncoming = (int)$request['target_user_id'] === (int)$user_id;
                ?>
                <div class="request-chip" style="margin-bottom:10px;">
                  <small><?php echo $isIncoming ? 'Incoming Request' : 'My Request'; ?></small>
                  <strong>#<?php echo (int)$request['requester_sale_id']; ?> + #<?php echo (int)$request['target_sale_id']; ?></strong>
                  <div class="meta">
                    <span><b>Status:</b> <span class="status-pill <?php echo $statusClass; ?>"><?php echo $statusLabel; ?></span></span>
                    <span><b>Requested:</b> <?php echo htmlspecialchars((string)$request['created_at']); ?></span>
                    <?php if (!empty($request['responded_at'])) { ?>
                    <span><b>Responded:</b> <?php echo htmlspecialchars((string)$request['responded_at']); ?></span>
                    <?php } ?>
                  </div>
                </div>
              <?php } ?>
            <?php } ?>
            <button type="button" class="merge-request-badge-btn" id="open_merge_requests_mobile">
              Merge Requests
              <span class="merge-request-count"><?php echo (int)$active_merge_requests; ?></span>
            </button>
          </div>
        </div>
      </div>
      <div class="mobile-card sales-tab-panel" data-tab-panel="done-merge" hidden>
        <div class="merge-finished-card">
          <h4>Finished Merges</h4>
          <p>Review merged bills here. They also stay visible in Sales for future merge actions.</p>
          <div class="merge-finished-list">
            <?php if (empty($finished_merge_rows)) { ?>
              <div class="request-chip"><strong>No finished merges yet</strong><div class="meta"><span>Your completed merge groups will show here.</span></div></div>
            <?php } else { ?>
              <?php foreach ($finished_merge_rows as $merge_row) { ?>
                <div class="merge-finished-item">
                  <small>Merged Bill</small>
                  <strong><?php echo htmlspecialchars($merge_row['merge_no']); ?></strong>
                  <div class="merge-finished-meta">
                    <span><b>Total:</b> <?php echo number_format((float)$merge_row['total_amount'], 2); ?></span>
                    <span><b>Created:</b> <?php echo htmlspecialchars((string)$merge_row['created_at']); ?></span>
                  </div>
                  <button type="button" class="btn btn-default btn-sm view-merge-review" data-merge-id="<?php echo (int)$merge_row['merge_id']; ?>" style="margin-top:8px;">View Merge</button>
                </div>
              <?php } ?>
            <?php } ?>
          </div>
        </div>
      </div>
      <div class="mobile-card sales-tab-panel" data-tab-panel="done-split" hidden>
        <div class="split-finished-card">
          <h4>Finished Splits</h4>
          <p>Completed split bills will appear here once the split flow is finalized.</p>
          <div class="merge-finished-list">
            <?php if (empty($finished_split_rows)) { ?>
              <div class="request-chip"><strong>No finished splits yet</strong><div class="meta"><span>Your completed split bills will show here.</span></div></div>
            <?php } ?>
          </div>
        </div>
      </div>
      <div class="mobile-card sales-tab-panel" data-tab-panel="split" hidden>
        <div class="split-card">
          <h4>Split Bills</h4>
          <p>Any real unpaid bill can be picked for split here, even if it is a child bill.</p>
          <div class="merge-grid" id="split-bill-grid-mobile">
            <?php foreach ($merge_cards as $card) { ?>
              <button type="button" class="merge-card" data-split-sale="<?php echo (int)$card['sale_id']; ?>">
                <small><?php echo $card['item_type'] === 'open_child' ? 'Child Bill' : 'Bill'; ?></small>
                <strong>#<?php echo (int)$card['sale_id']; ?></strong>
                <div class="meta">
                  <span><b>Amount:</b> <?php echo number_format((float)$card['amount'], 2); ?></span>
                  <?php if ((int)$card['root_sale_id'] !== (int)$card['sale_id']) { ?>
                  <span><b>Parent Bill:</b> #<?php echo (int)$card['root_sale_id']; ?></span>
                  <?php } ?>
                </div>
              </button>
            <?php } ?>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div id="modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="gridModalLabel" aria-hidden="true">
    <div class="modal-dialog" id="load"></div>
  </div>

  <div id="merge_search_modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" style="max-width:420px;">
      <div class="modal-content" style="border-radius:18px; overflow:hidden;">
        <div class="modal-header" style="background: linear-gradient(135deg, #7e2014 0%, #b94828 52%, #e7a33a 100%); color:#fff8ea;">
          <button type="button" class="close" data-dismiss="modal" style="color:#fff; opacity:1;">&times;</button>
          <h4 class="modal-title"><b>Search Eligible Bills</b></h4>
        </div>
        <div class="modal-body" style="background:#fffdf8;">
          <input type="text" id="merge_search_modal_input" class="merge-search-input" placeholder="Enter bill number" readonly style="width:100%; min-height:48px; text-align:center; font-size:20px;">
          <div class="merge-pad-grid">
            <button type="button" class="merge-pad-key" data-merge-pad="7">7</button>
            <button type="button" class="merge-pad-key" data-merge-pad="8">8</button>
            <button type="button" class="merge-pad-key" data-merge-pad="9">9</button>
            <button type="button" class="merge-pad-key" data-merge-pad="4">4</button>
            <button type="button" class="merge-pad-key" data-merge-pad="5">5</button>
            <button type="button" class="merge-pad-key" data-merge-pad="6">6</button>
            <button type="button" class="merge-pad-key" data-merge-pad="1">1</button>
            <button type="button" class="merge-pad-key" data-merge-pad="2">2</button>
            <button type="button" class="merge-pad-key" data-merge-pad="3">3</button>
            <button type="button" class="merge-pad-key action" data-merge-pad="C">Clear</button>
            <button type="button" class="merge-pad-key" data-merge-pad="0">0</button>
            <button type="button" class="merge-pad-key action" data-merge-pad="B">Back</button>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
          <button type="button" class="btn btn-success" id="apply_merge_search_btn">Apply Search</button>
        </div>
      </div>
    </div>
  </div>

  <div id="sales_search_modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" style="max-width:420px;">
      <div class="modal-content" style="border-radius:18px; overflow:hidden;">
        <div class="modal-header" style="background: linear-gradient(135deg, #7e2014 0%, #b94828 52%, #e7a33a 100%); color:#fff8ea;">
          <button type="button" class="close" data-dismiss="modal" style="color:#fff; opacity:1;">&times;</button>
          <h4 class="modal-title"><b>Search Sale Number</b></h4>
        </div>
        <div class="modal-body" style="background:#fffdf8;">
          <input type="text" id="sales_search_modal_input" class="merge-search-input" placeholder="Enter sale number" readonly style="width:100%; min-height:48px; text-align:center; font-size:20px;">
          <div class="merge-pad-grid">
            <button type="button" class="merge-pad-key" data-sales-pad="7">7</button>
            <button type="button" class="merge-pad-key" data-sales-pad="8">8</button>
            <button type="button" class="merge-pad-key" data-sales-pad="9">9</button>
            <button type="button" class="merge-pad-key" data-sales-pad="4">4</button>
            <button type="button" class="merge-pad-key" data-sales-pad="5">5</button>
            <button type="button" class="merge-pad-key" data-sales-pad="6">6</button>
            <button type="button" class="merge-pad-key" data-sales-pad="1">1</button>
            <button type="button" class="merge-pad-key" data-sales-pad="2">2</button>
            <button type="button" class="merge-pad-key" data-sales-pad="3">3</button>
            <button type="button" class="merge-pad-key action" data-sales-pad="C">Clear</button>
            <button type="button" class="merge-pad-key" data-sales-pad="0">0</button>
            <button type="button" class="merge-pad-key action" data-sales-pad="B">Back</button>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
          <button type="button" class="btn btn-success" id="apply_sales_search_btn">Apply Search</button>
        </div>
      </div>
    </div>
  </div>

  <div id="return_confirm_modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" style="max-width:420px;">
      <div class="modal-content">
        <div class="modal-header" style="background: linear-gradient(135deg, #7e2014 0%, #b94828 52%, #e7a33a 100%); color:#fff8ea;">
          <h4 class="modal-title"><b>Confirm Return</b></h4>
        </div>
        <div class="modal-body">
          <div style="padding:14px; border-radius:12px; background:#fffaf0; border:1px solid #ead7a3; color:#6f2c16; font-weight:700;">
            <p style="margin:0 0 8px;">You are about to start a return for bill no <b id="return_confirm_bill">-</b>.</p>
            <p style="margin:0;">Continue to the return page?</p>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-danger" data-dismiss="modal" style="font-weight:bold;">Cancel</button>
          <a href="#" id="return_confirm_go" class="btn btn-success" style="font-weight:bold;">Continue</a>
        </div>
      </div>
    </div>
  </div>

  <script src="../../assets/plugins/jquery/jquery-3.2.1.min.js"></script>
  <script src="../../assets/plugins/bootstrap/4.0.0/js/bootstrap.bundle.min.js"></script>
  <script type="text/javascript">
    window.initialMergeDraftItems = [];
    window.initialMergeRequestItems = [];
    window.activeMergeDraftId = 0;
    window.pendingMergeRequestCount = 0;
  </script>
  <script type="text/javascript">
  function printContent(id){
    $.ajax({
      type: 'get',
      url: '../chaba/render_bill.php',
      data: {sale_id: id,status:3},
      success: function(response) {
        setTimeout(function() {
          var contents = response;
          var frame1 = document.createElement('iframe');
          frame1.name = "frame1";
          frame1.style.position = "absolute";
          frame1.style.top = "-1000000px";
          document.body.appendChild(frame1);
          var frameDoc = frame1.contentWindow ? frame1.contentWindow : frame1.contentDocument.document ? frame1.contentDocument.document : frame1.contentDocument;
          frameDoc.document.open();
          frameDoc.document.write(contents);
          setTimeout(function () {
            window.frames["frame1"].focus();
            window.frames["frame1"].print();
            document.body.removeChild(frame1);
          }, 500);
        }, 2000);
      }
    });
  }

  function filterSalesRows(inputId, typeId, rowSelector) {
    var term = $.trim($(inputId).val().toLowerCase());
    var type = $(typeId).val();
    $(rowSelector).each(function() {
      var bill = ($(this).data('bill') || '').toString().toLowerCase();
      var item = ($(this).data('item') || '').toString().toLowerCase();
      var amount = ($(this).data('amount') || '').toString().toLowerCase();
      var haystack = bill + ' ' + item + ' ' + amount;
      var match = false;
      if (term === '') {
        match = true;
      } else if (type === 'bill') {
        match = bill.indexOf(term) !== -1;
      } else if (type === 'item') {
        match = item.indexOf(term) !== -1;
      } else if (type === 'amount') {
        match = amount.indexOf(term) !== -1;
      } else {
        match = haystack.indexOf(term) !== -1;
      }
      $(this).toggle(match);
    });
  }

    function loadBillInto(targetSelector, id, openModal) {
      $.ajax({
        url: 'API/view_api.php',
        type: 'POST',
        data: { action: 'bill_items_modal', sale_id: id },
        dataType: 'html'
    })
    .done(function(data){
      if (openModal) {
        $('#load').html(data);
        $('#modal').modal('show');
      } else {
        $(targetSelector).html(data);
      }
    })
    .fail(function(){
      $(targetSelector).html('<i class="fa fa-info-sign"></i> Something went wrong, Please try again...');
      if (openModal) {
        $('#load').html('<div class="modal-content"><div class="modal-body"><i class="fa fa-info-sign"></i> Something went wrong, Please try again...</div></div>');
        $('#modal').modal('show');
        }
      });
    }

    function resolveSaleId($trigger) {
      if (!$trigger || !$trigger.length) {
        return 0;
      }
      var directId = parseInt($trigger.attr('data-sale-id') || $trigger.data('sale-id') || $trigger.data('saleId') || 0, 10);
      if (directId > 0) {
        return directId;
      }
      return parseInt($trigger.attr('data-id') || $trigger.data('id') || 0, 10) || 0;
    }

  var salesPage = 1;
  var salesSearch = '';

  function renderSalesPagination(pagination) {
    pagination = pagination || {};
    var page = parseInt(pagination.page, 10) || 1;
    var pages = parseInt(pagination.pages, 10) || 0;
    var total = parseInt(pagination.total, 10) || 0;
    if (pages < 2) {
      $('.sales-pagination').html(total ? '<span>Showing ' + total + ' sale' + (total === 1 ? '' : 's') + '</span>' : '');
      return;
    }
    var html = '<button type="button" class="btn btn-sm sales-page-btn" data-sales-page="' + Math.max(1, page - 1) + '"' + (page <= 1 ? ' disabled' : '') + '>‹</button>';
    html += '<span>Page ' + page + ' of ' + pages + ' · ' + total + ' sales</span>';
    html += '<button type="button" class="btn btn-sm sales-page-btn" data-sales-page="' + Math.min(pages, page + 1) + '"' + (page >= pages ? ' disabled' : '') + '>›</button>';
    $('.sales-pagination').html(html);
  }

  function renderSoldItems(items) {
    items = Array.isArray(items) ? items : [];
    var esc = window.viewEscape || function(value) {
      return String(value == null ? '' : value).replace(/[&<>\"']/g, function(char) {
        return {'&':'&amp;','<':'&lt;','>':'&gt;','\"':'&quot;',"'":'&#039;'}[char];
      });
    };
    var desktop = items.map(function(item, index) {
      var name = String(item.item || item.item_name || ('Item #' + (item.item_id || '')));
      var qty = Number(item.qty || 0).toFixed(2);
      return '<tr><td>' + (index + 1) + '</td><td>' + esc(name) + '</td><td>' + qty + '</td><td><button type="button" class="item-orders-btn" data-item-name="' + esc(name) + '">Orders</button></td></tr>';
    }).join('');
    var mobile = items.map(function(item, index) {
      var name = String(item.item || item.item_name || ('Item #' + (item.item_id || '')));
      var qty = Number(item.qty || 0).toFixed(2);
      return '<li><span>' + (index + 1) + '. ' + esc(name) + '</span><div style="display:flex; align-items:center; gap:8px;"><b>' + qty + '</b><button type="button" class="item-orders-btn" data-item-name="' + esc(name) + '">Orders</button></div></li>';
    }).join('');
    $('#desktop_sold_rows').html(desktop || '<tr class="empty-row"><td colspan="4">No items sold yet</td></tr>');
    $('#mobile_sold_rows').html(mobile || '<li><span>No items sold yet</span><b>0</b></li>');
  }

  function loadSalesShellData(page, search) {
    salesPage = Math.max(1, parseInt(page, 10) || 1);
    salesSearch = $.trim(String(search == null ? salesSearch : search));
    $.ajax({
      url: 'API/view_api.php',
      type: 'POST',
      data: { action: 'bootstrap', page: salesPage, search: salesSearch },
      dataType: 'json'
    }).done(function(res) {
      var data = res && res.data ? res.data : {};
      var rows = Array.isArray(data.sales_rows) ? data.sales_rows : [];
      var esc = window.viewEscape || function(value) {
        return String(value == null ? '' : value).replace(/[&<>\"']/g, function(char) {
          return {'&':'&amp;','<':'&lt;','>':'&gt;','\"':'&quot;',"'":'&#039;'}[char];
        });
      };
      function rowHtml(row, mobile, index) {
        var kind = row.kind || 'sale';
        var id = parseInt(row.sale_id || row.id || 0, 10) || 0;
        var label = esc(row.label || ('#' + id));
        var shift = esc(row.shift || '');
        var counter = esc(row.counter || ('Counter ' + (row.counter_id || '')));
        var total = Number(row.total || 0).toFixed(2);
        var action = kind === 'merge'
          ? '<button type="button" data-merge-id="' + id + '" class="btn btn-sm sales-btn view-merge-review">VIEW</button>'
          : '<button type="button" data-id="' + id + '" class="btn btn-sm sales-btn ' + (mobile ? 'view-sale-mobile' : 'view-sale') + '">VIEW</button>';
        var cells = mobile
          ? '<td>' + label + '</td><td>' + shift + '</td><td>' + counter + '</td><td>' + total + '</td><td>' + action + '</td>'
          : '<td>' + (index + 1) + '</td><td>' + shift + '</td><td>' + label + '</td><td>' + counter + '</td><td>' + total + '</td><td>' + action + '</td>';
        return '<tr class="' + (mobile ? 'sale-row-mobile' : 'sale-row') + '" data-bill="' + esc(id) + '" data-item="' + esc(row.search || '') + '" data-amount="' + esc(total) + '" data-kind="' + esc(kind) + '">' + cells + '</tr>';
      }
      var desktop = rows.map(function(row, index) { return rowHtml(row, false, ((salesPage - 1) * 10) + index); }).join('');
      var mobile = rows.map(function(row, index) { return rowHtml(row, true, index); }).join('');
      $('#desktop_sales_rows').html(desktop || '<tr><td colspan="6">No sales found for the current shift.</td></tr>');
      $('#mobile_sales_rows').html(mobile || '<tr><td colspan="5">No sales found for the current shift.</td></tr>');
      renderSoldItems(data.sold_rows || data.items || []);
      renderSalesPagination(data.pagination || {});
      if (data.context && data.context.shift) {
        $('.sales-current-user').text(data.context.username || '');
        $('.sales-current-shift').text(data.context.shift || '');
      }
    }).fail(function() {
      $('#desktop_sales_rows').html('<tr><td colspan="6">Could not load current-shift sales.</td></tr>');
      $('#mobile_sales_rows').html('<tr><td colspan="5">Could not load current-shift sales.</td></tr>');
    });
  }

  $(document).ready(function() {
    loadSalesShellData();
    var ownMergeBasket = {};
    var mergeRequestItems = Array.isArray(window.initialMergeRequestItems) ? window.initialMergeRequestItems.slice() : [];
    if (Array.isArray(window.initialMergeDraftItems)) {
      window.initialMergeDraftItems.forEach(function(item) {
        var saleId = parseInt(item.sale_id, 10) || 0;
        if (!saleId) {
          return;
        }
        ownMergeBasket[String(saleId)] = {
          root: saleId,
          amount: parseFloat(item.amount) || 0,
          counter: item.counter || ''
        };
      });
    }
    var mergeSearchTarget = 'desktop';
    var salesSearchTarget = 'desktop';

    function syncSalesTabs(tab) {
      $('[data-sales-tab]').removeClass('active');
      $('[data-sales-tab="' + tab + '"]').addClass('active');
      $('[data-tab-panel]').attr('hidden', true);
      $('[data-tab-panel="' + tab + '"]').removeAttr('hidden');
    }

    function renderOwnMergeBasket() {
      var roots = Object.keys(ownMergeBasket);
      var total = 0;
      var html = '<small>Merge Basket</small><strong>0.00</strong><p style="margin-top:8px;color:#6e563d;font-weight:700;">Select your own bills to merge them directly.</p>';
      if (roots.length) {
        html = '<small>Merge Basket</small>';
        html += '<div class="merge-basket-section-title">Bills Added</div>';
        roots.forEach(function(root) {
          var item = ownMergeBasket[root];
          var linkedRequest = getRequestItemByTargetSale(item.root);
          total += parseFloat(item.amount || 0);
          html += '<div class="merge-basket-item"><strong>#' + item.root + '</strong><p style="margin:6px 0 0;color:#6e563d;font-weight:700;"><b>Amount:</b> ' + Number(item.amount).toFixed(2) + '<br><b>Counter:</b> ' + item.counter;
          if (linkedRequest) {
            html += '<br><b>User:</b> ' + (linkedRequest.target_owner_name || '-') ;
          }
          html += '</p>';
          if (linkedRequest) {
            var addedStatusLabel = parseInt(linkedRequest.status, 10) === 2 ? 'Accepted Request' : 'Pending Request';
            var addedStatusClass = parseInt(linkedRequest.status, 10) === 2 ? 'status-accepted' : 'status-pending';
            html += '<div class="merge-basket-status"><span class="status-pill ' + addedStatusClass + '">' + addedStatusLabel + '</span></div>';
          }
          html += '<button type="button" class="btn btn-danger btn-sm remove-own-merge" data-root="' + item.root + '" style="margin-top:8px;">Remove</button></div>';
        });
        if (mergeRequestItems.length) {
          var pendingHtml = '';
          html += '<div class="merge-basket-section-title">Request Bills</div>';
          mergeRequestItems.forEach(function(request) {
            if (ownMergeBasket[String(request.target_sale_id)]) {
              return;
            }
            var statusLabel = parseInt(request.status, 10) === 2 ? 'Accepted' : 'Pending';
            var statusClass = parseInt(request.status, 10) === 2 ? 'status-accepted' : 'status-pending';
            pendingHtml += '<div class="merge-basket-item">';
            pendingHtml += '<strong>#' + request.target_sale_id + '</strong>';
            pendingHtml += '<p style="margin:6px 0 0;color:#6e563d;font-weight:700;"><b>User:</b> ' + (request.target_owner_name || '-') + '<br><b>Amount:</b> ' + Number(request.amount || 0).toFixed(2) + '</p>';
            pendingHtml += '<div class="merge-basket-status"><span class="status-pill ' + statusClass + '">' + statusLabel + '</span></div>';
            pendingHtml += '</div>';
          });
          html += pendingHtml || '<p style="margin:4px 0 0;color:#7d6648;font-weight:700;">No waiting request bills.</p>';
        }
        html += '<strong>' + total.toFixed(2) + '</strong>';
        if ((parseInt(window.pendingMergeRequestCount, 10) || 0) > 0) {
          html += '<p style="margin-top:10px; color:#a14a1d; font-weight:800;">Pending merge requests must be approved or closed before you can finish this merge.</p>';
        }
        html += '<div class="merge-basket-actions"><button type="button" class="btn btn-default" id="clear-own-merge">Clear Basket</button><button type="button" class="btn btn-success" id="create-own-merge"' + (((parseInt(window.pendingMergeRequestCount, 10) || 0) > 0) ? ' disabled' : '') + '>Merge My Bills</button></div>';
      } else if (mergeRequestItems.length) {
        html = '<small>Merge Basket</small><p style="margin-top:8px;color:#6e563d;font-weight:700;">Start by adding one of your own bills, then wait for the requested bill approval.</p>';
        html += '<div class="merge-basket-section-title">Request Bills</div>';
        mergeRequestItems.forEach(function(request) {
          var statusLabel = parseInt(request.status, 10) === 2 ? 'Accepted' : 'Pending';
          var statusClass = parseInt(request.status, 10) === 2 ? 'status-accepted' : 'status-pending';
          html += '<div class="merge-basket-item">';
          html += '<strong>#' + request.target_sale_id + '</strong>';
          html += '<p style="margin:6px 0 0;color:#6e563d;font-weight:700;"><b>User:</b> ' + (request.target_owner_name || '-') + '<br><b>Amount:</b> ' + Number(request.amount || 0).toFixed(2) + '</p>';
          html += '<div class="merge-basket-status"><span class="status-pill ' + statusClass + '">' + statusLabel + '</span></div>';
          html += '</div>';
        });
      }
      $('#my-merge-basket, #my-merge-basket-mobile').html(html);
      $('[data-own-merge-sale]').each(function() {
        var saleId = String($(this).data('ownMergeSale'));
        $(this).toggleClass('is-picked', !!ownMergeBasket[saleId]);
      });
    }

    function selectedOwnSales() {
      return Object.keys(ownMergeBasket);
    }

    function addSaleToMergeDraft(saleId, amount, counter, done) {
      $.ajax({
        url: 'API/view_api.php',
        type: 'POST',
        data: { action: 'merge_add_sale', sale_id: saleId },
        dataType: 'json'
      })
      .done(function(res) {
        if (!res || !res.ok) {
          alert(res && res.err ? res.err : 'Could not add the bill to the merge draft.');
          return;
        }
        var key = String(saleId);
        ownMergeBasket[key] = {
          root: parseInt(saleId, 10) || 0,
          amount: parseFloat(amount) || 0,
          counter: counter || ''
        };
        renderOwnMergeBasket();
        if (typeof done === 'function') {
          done(res);
        }
      })
      .fail(function() {
        alert('Could not add the bill to the merge draft right now.');
      });
    }

    function removeSaleFromMergeDraft(saleId, done) {
      $.ajax({
        url: 'API/view_api.php',
        type: 'POST',
        data: { action: 'merge_remove_sale', sale_id: saleId },
        dataType: 'json'
      })
      .done(function(res) {
        if (!res || !res.ok) {
          alert(res && res.err ? res.err : 'Could not remove the bill from the merge draft.');
          return;
        }
        delete ownMergeBasket[String(saleId)];
        renderOwnMergeBasket();
        if (typeof done === 'function') {
          done(res);
        }
      })
      .fail(function() {
        alert('Could not remove the bill from the merge draft right now.');
      });
    }

    function applyMergeSearch(term) {
      var value = $.trim(String(term || ''));
      $('#merge_bill_search').val(value);
      $('#merge_bill_search_mobile').val(value);
      $('[data-own-merge-sale]').each(function() {
        var saleId = String($(this).data('ownMergeSale') || '');
        var show = value === '' || saleId.indexOf(value) !== -1;
        $(this).toggle(show);
      });
    }

    function sendMergeRequest(myRoot, targetBill) {
      $.ajax({
        url: 'API/view_api.php',
        type: 'POST',
        data: { action: 'merge_request', my_root_sale_id: myRoot, target_bill_no: targetBill },
        dataType: 'json'
      })
      .done(function(res) {
        if (!res || !res.ok) {
          alert(res && res.err ? res.err : 'Could not send the merge request.');
          return;
        }
        window.location.reload();
      })
      .fail(function() {
        alert('Could not send the merge request right now.');
      });
    }

    function removeRequestItem(requestId) {
      mergeRequestItems = mergeRequestItems.filter(function(request) {
        return parseInt(request.request_id, 10) !== parseInt(requestId, 10);
      });
    }

    function updateRequestItemStatus(requestId, newStatus) {
      mergeRequestItems = mergeRequestItems.map(function(request) {
        if (parseInt(request.request_id, 10) === parseInt(requestId, 10)) {
          request.status = newStatus;
        }
        return request;
      });
    }

    function pruneRequestItemsAfterMerge() {
      mergeRequestItems = [];
      window.pendingMergeRequestCount = 0;
    }

    function getRequestItemByTargetSale(saleId) {
      for (var i = 0; i < mergeRequestItems.length; i++) {
        if (parseInt(mergeRequestItems[i].target_sale_id, 10) === parseInt(saleId, 10)) {
          return mergeRequestItems[i];
        }
      }
      return null;
    }

    function openMergeRequestsModal() {
      $('#load').html('<div class="modal-content"><div class="modal-body" style="padding:24px; text-align:center; font-weight:900;">Loading merge requests...</div></div>');
      $('#modal').modal('show');
      $.ajax({
        url: 'API/view_api.php',
        type: 'GET',
        data: { action: 'merge_requests_modal' },
        dataType: 'html'
      })
      .done(function(html) {
        $('#load').html(html);
      })
      .fail(function() {
        $('#load').html('<div class="modal-content"><div class="modal-body"><div class="alert alert-danger" style="margin-bottom:0;">Could not load merge requests.</div></div></div>');
      });
    }

    function openMergeSearchPad(target) {
      mergeSearchTarget = target || 'desktop';
      var current = mergeSearchTarget === 'mobile' ? $('#merge_bill_search_mobile').val() : $('#merge_bill_search').val();
      $('#merge_search_modal_input').val(current || '');
      $('#merge_search_modal').modal('show');
    }

    function openSalesSearchPad(target) {
      salesSearchTarget = target || 'desktop';
      var current = salesSearchTarget === 'mobile' ? $('#sales_filter_mobile').val() : $('#sales_filter_desktop_panel').val();
      $('#sales_search_modal_input').val(current || '');
      $('#sales_search_modal').modal('show');
    }

    function openMergeBillModal(saleId) {
      $('#load').html('<div class="modal-content"><div class="modal-body" style="padding:24px; text-align:center; font-weight:900;">Loading bill items...</div></div>');
      $('#modal').modal('show');
      $.ajax({
        url: 'API/view_api.php',
        type: 'GET',
        data: { action: 'merge_bill_modal', sale_id: saleId },
        dataType: 'html'
      })
      .done(function(html) {
        $('#load').html(html);
      })
      .fail(function() {
        $('#load').html('<div class="modal-content"><div class="modal-body"><div class="alert alert-danger" style="margin-bottom:0;">Could not load the merge bill preview.</div></div></div>');
      });
    }

    function openMergeReviewModal(mergeId) {
      $('#load').html('<div class="modal-content"><div class="modal-body" style="padding:24px; text-align:center; font-weight:900;">Loading merge review...</div></div>');
      $('#modal').modal('show');
      $.ajax({
        url: 'API/view_api.php',
        type: 'GET',
        data: { action: 'merge_review_modal', merge_id: mergeId },
        dataType: 'html'
      })
      .done(function(html) {
        $('#load').html(html);
      })
      .fail(function() {
        $('#load').html('<div class="modal-content"><div class="modal-body"><div class="alert alert-danger" style="margin-bottom:0;">Could not load the merged bill review.</div></div></div>');
      });
    }

    window.addOwnBillToMergeFromModal = function(saleId, amount, counter) {
      addSaleToMergeDraft(saleId, amount, counter, function() {
        $('#modal').modal('hide');
      });
    };

    window.addApprovedForeignBillToMerge = function(saleId, amount, counter, requesterSaleId) {
      var selected = selectedOwnSales();
      if (selected.length !== 1) {
        alert('Add exactly one of your own bills to the merge basket first.');
        return;
      }
      if (parseInt(selected[0], 10) !== parseInt(requesterSaleId, 10)) {
        alert('This approval is tied to your bill #' + requesterSaleId + '. Put that bill in the basket first.');
        return;
      }
      addSaleToMergeDraft(saleId, amount, counter, function() {
        renderOwnMergeBasket();
        $('#modal').modal('hide');
      });
    };

    window.sendMergeRequestFromModal = function(targetBill) {
      var selected = selectedOwnSales();
      if (selected.length !== 1) {
        alert('Add exactly one of your own bills to the merge basket first.');
        return;
      }
      if (!targetBill) {
        alert('Target bill is missing.');
        return;
      }
      sendMergeRequest(selected[0], targetBill);
    };

    $(document).on('click','#reprint', function(e){
      e.preventDefault();
      var uid = $(this).data('id');
      $('#load').html('');
      $.ajax({
        url: 'API/view_api.php',
        type: 'POST',
        data: { action: 'reprint_modal', sale_id: uid },
        dataType: 'html'
      })
      .done(function(data){
        $('#load').html(data);
      })
      .fail(function(){
        $('#load').html('<i class="fa fa-info-sign"></i> Something went wrong, Please try again...');
      });
    });

    $(document).on('click','#void', function(e){
      e.preventDefault();
      var uid = $(this).data('id');
      var modalOpen = $('#modal').hasClass('in') || $('#modal').hasClass('show');
      $('#load').html('');
      $.ajax({
        url: 'API/view_api.php',
        type: 'POST',
        data: { action: 'void_modal', sale_id: uid },
        dataType: 'html'
      })
      .done(function(data){
        $('#load').html(data);
        if (!modalOpen) {
          $('#modal').modal('show');
        }
      })
      .fail(function(){
        $('#load').html('<i class="fa fa-info-sign"></i> Something went wrong, Please try again...');
        if (!modalOpen) {
          $('#modal').modal('show');
        }
      });
    });

    $(document).on('click','#clear-bill', function(e){
      e.preventDefault();
      var uid = $(this).data('id');
      var cntr = $(this).data('id2');
      $('#load').html('');
      $.ajax({
        url: 'staff_clear_modal.php',
        typr: 'POST',
        data: {id:uid,cntr:cntr},
        dataType: 'html'
      })
      .done(function(data){
        $('#load').html(data);
      })
      .fail(function(){
        $('#load').html('<i class="fa fa-info-sign"></i> Something went wrong, Please try again...');
      });
    });

    $(document).on('click','.view-sale', function(e){
      e.preventDefault();
      var saleId = resolveSaleId($(this));
      if (saleId < 1) {
        return;
      }
      loadBillInto('#load', saleId, true);
    });

    $(document).on('click','.view-sale-mobile', function(e){
      e.preventDefault();
      var saleId = resolveSaleId($(this));
      if (saleId < 1) {
        return;
      }
      loadBillInto('#mobile_cont', saleId, true);
    });

    $(document).on('click','.return-sale-mobile', function(e){
      e.preventDefault();
      var saleId = resolveSaleId($(this));
      if (saleId < 1) {
        alert('Could not find the correct bill for return.');
        return;
      }
      if (confirm('Return bill #' + saleId + '?')) {
        window.location.href = '../ret/ret_page.php?sale_id=' + encodeURIComponent(saleId);
      }
    });

    $(document).on('click','.void-sale-mobile', function(e){
      e.preventDefault();
      var saleId = $(this).data('id');
      if (confirm('Are you sure you want to void bill #' + saleId + '?')) {
        window.location.href = '../kwedia/void_sale.php?sale_id=' + encodeURIComponent(saleId);
      }
    });

    $(document).on('click','.return-sale', function(e){
      e.preventDefault();
      var saleId = resolveSaleId($(this));
      if (saleId < 1) {
        alert('Could not find the correct bill for return.');
        return;
      }
      $('#return_confirm_bill').text(saleId || '-');
      $('#return_confirm_go').attr('href', '../ret/ret_page.php?sale_id=' + encodeURIComponent(saleId));
      $('#return_confirm_modal').modal('show');
    });

    $(document).on('click','.void-sale', function(e){
      e.preventDefault();
      var saleId = $(this).data('id');
      if (confirm('Are you sure you want to void bill #' + saleId + '?')) {
        window.location.href = '../kwedia/void_sale.php?sale_id=' + encodeURIComponent(saleId);
      }
    });

    $(document).on('click','.search-key', function() {
      var input = $('#sales_filter_desktop');
      input.val(input.val() + $(this).data('key')).trigger('input');
      input.focus();
    });

    $(document).on('click','#search_backspace', function() {
      var input = $('#sales_filter_desktop');
      input.val(input.val().slice(0, -1)).trigger('input');
      input.focus();
    });
    $(document).on('click','#search_backspace_num', function() {
      $('#search_backspace').trigger('click');
    });

    $(document).on('click','#search_clear', function() {
      $('#sales_filter_desktop').val('').trigger('input');
    });
    $(document).on('click','#search_clear_num', function() {
      $('#search_clear').trigger('click');
    });

    $(document).on('click','#toggle_alpha', function() {
      $('#alpha_keyboard').removeClass('hidden');
      $('#num_keyboard').addClass('hidden');
      $('#toggle_alpha').addClass('active');
      $('#toggle_numpad').removeClass('active');
    });

    $(document).on('click','#toggle_numpad', function() {
      $('#num_keyboard').removeClass('hidden');
      $('#alpha_keyboard').addClass('hidden');
      $('#toggle_numpad').addClass('active');
      $('#toggle_alpha').removeClass('active');
    });

    $(document).on('click', '.item-orders-btn', function() {
      var itemName = $(this).data('itemName') || '';
      $('#load').html('<div class="modal-content"><div class="modal-body" style="padding:24px; text-align:center; font-weight:900;">Loading orders...</div></div>');
      $('#modal').modal('show');
      $.ajax({
        url: 'API/view_api.php',
        type: 'GET',
        data: { action: 'item_sales_modal', item_name: itemName },
        dataType: 'html'
      })
      .done(function(html) {
        $('#load').html(html);
      })
      .fail(function() {
        $('#load').html('<div class="modal-content"><div class="modal-body"><div class="alert alert-danger" style="margin-bottom:0;">Could not load item orders.</div></div></div>');
      });
    });

    $(document).on('click', '[data-sales-tab]', function() {
      syncSalesTabs($(this).data('salesTab'));
    });

    $(document).on('click', '[data-own-merge-sale]', function() {
      openMergeBillModal($(this).data('ownMergeSale'));
    });

    $(document).on('click', '.view-merge-review', function(e) {
      e.preventDefault();
      openMergeReviewModal($(this).data('mergeId'));
    });

    $(document).on('click', '#open_merge_search_pad, #open_merge_search_pad_mobile', function() {
      openMergeSearchPad(this.id.indexOf('_mobile') !== -1 ? 'mobile' : 'desktop');
    });

    $(document).on('click', '#open_sales_search_pad, #open_sales_search_pad_mobile, #sales_filter_desktop_panel, #sales_filter_mobile', function() {
      openSalesSearchPad(this.id.indexOf('_mobile') !== -1 ? 'mobile' : 'desktop');
    });

    $(document).on('click', '[data-merge-pad]', function() {
      var key = String($(this).data('mergePad'));
      var input = $('#merge_search_modal_input');
      var value = String(input.val() || '');
      if (key === 'C') {
        input.val('');
        return;
      }
      if (key === 'B') {
        input.val(value.slice(0, -1));
        return;
      }
      input.val(value + key);
    });

    $(document).on('click', '#apply_merge_search_btn', function() {
      applyMergeSearch($('#merge_search_modal_input').val());
      $('#merge_search_modal').modal('hide');
    });

    $(document).on('click', '[data-sales-pad]', function() {
      var key = String($(this).data('salesPad'));
      var input = $('#sales_search_modal_input');
      var value = String(input.val() || '');
      if (key === 'C') {
        input.val('');
        return;
      }
      if (key === 'B') {
        input.val(value.slice(0, -1));
        return;
      }
      input.val(value + key);
    });

    $(document).on('click', '#apply_sales_search_btn', function() {
      var term = $.trim($('#sales_search_modal_input').val());
      $('#sales_filter_desktop_panel').val(term);
      $('#sales_filter_mobile').val(term);
      loadSalesShellData(1, term);
      $('#sales_search_modal').modal('hide');
    });

    $(document).on('click', '.sales-page-btn:not(:disabled)', function() {
      loadSalesShellData($(this).data('salesPage'), salesSearch);
    });

    $(document).on('click', '.remove-own-merge', function() {
      removeSaleFromMergeDraft($(this).data('root'));
    });

    $(document).on('click', '#clear-own-merge', function() {
      var saleIds = selectedOwnSales();
      if (!saleIds.length) {
        renderOwnMergeBasket();
        return;
      }
      var queue = saleIds.slice();
      function removeNext() {
        if (!queue.length) {
          return;
        }
        var saleId = queue.shift();
        removeSaleFromMergeDraft(saleId, function() {
          removeNext();
        });
      }
      removeNext();
    });

    $(document).on('click', '#create-own-merge', function() {
      var saleIds = selectedOwnSales();
      if (saleIds.length < 2) {
        alert('Add at least two bills before finishing the merge.');
        return;
      }
      $.ajax({
        url: 'API/view_api.php',
        type: 'POST',
        data: { action: 'merge_finalize' },
        dataType: 'json'
      })
      .done(function(res) {
        if (!res || !res.ok) {
          alert(res && res.err ? res.err : 'Could not finish the merge.');
          return;
        }
        pruneRequestItemsAfterMerge();
        alert('Merge ' + res.merge_no + ' finished successfully.');
        window.location.reload();
      })
      .fail(function() {
        alert('Could not finish the merge right now.');
      });
    });

    $(document).on('click', '#open_merge_requests, #open_merge_requests_mobile', function() {
      openMergeRequestsModal();
    });

    $(document).on('click', '.accept-merge-request', function() {
      var requestId = $(this).data('requestId');
      $.ajax({
        url: 'API/view_api.php',
        type: 'POST',
        data: { action: 'merge_accept', request_id: requestId },
        dataType: 'json'
      })
      .done(function(res) {
        if (!res || !res.ok) {
          alert(res && res.err ? res.err : 'Could not accept the merge request.');
          return;
        }
        updateRequestItemStatus(requestId, 2);
        window.pendingMergeRequestCount = Math.max((parseInt(window.pendingMergeRequestCount, 10) || 0) - 1, 0);
        renderOwnMergeBasket();
        alert('Request accepted. The bill is now approved for this merge.');
        window.location.reload();
      })
      .fail(function() {
        alert('Could not accept the merge request right now.');
      });
    });

    $(document).on('click', '.reject-merge-request', function() {
      var requestId = $(this).data('requestId');
      $.ajax({
        url: 'API/view_api.php',
        type: 'POST',
        data: { action: 'merge_reject', request_id: requestId },
        dataType: 'json'
      })
      .done(function(res) {
        if (!res || !res.ok) {
          alert(res && res.err ? res.err : 'Could not reject the merge request.');
          return;
        }
        window.pendingMergeRequestCount = Math.max((parseInt(window.pendingMergeRequestCount, 10) || 0) - 1, 0);
        removeRequestItem(requestId);
        renderOwnMergeBasket();
        window.location.reload();
      })
      .fail(function() {
        alert('Could not reject the merge request right now.');
      });
    });

    $(document).on('click', '.close-merge-request', function() {
      var requestId = $(this).data('requestId');
      $.ajax({
        url: 'API/view_api.php',
        type: 'POST',
        data: { action: 'merge_close_request', request_id: requestId },
        dataType: 'json'
      })
      .done(function(res) {
        if (!res || !res.ok) {
          alert(res && res.err ? res.err : 'Could not close the merge request.');
          return;
        }
        window.pendingMergeRequestCount = Math.max((parseInt(window.pendingMergeRequestCount, 10) || 0) - 1, 0);
        removeRequestItem(requestId);
        renderOwnMergeBasket();
        window.location.reload();
      })
      .fail(function() {
        alert('Could not close the merge request right now.');
      });
    });

    $(document).on('click', '[data-split-sale]', function() {
      alert('Split bill #' + $(this).data('splitSale') + ' will be wired next.');
    });

    renderOwnMergeBasket();
  });
  </script>
</body>
</html>
