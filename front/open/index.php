<?php
include("../clude/head.php");
?>
<style>
  :root {
    --open-green: #61b846;
    --open-green-deep: #2f7a31;
    --open-orange: #f36c26;
    --open-cream: #fff8ea;
    --open-cream-deep: #f6eddc;
    --open-ink: #3b2a18;
    --open-muted: #8a6a3a;
    --open-border: rgba(111, 78, 28, 0.12);
    --open-shadow: 0 24px 44px rgba(70, 51, 20, 0.14);
  }
  body {
    height: 100vh;
    overflow: hidden;
    min-height: 100vh;
    background:
      radial-gradient(circle at top left, rgba(97, 184, 70, 0.18), transparent 28%),
      radial-gradient(circle at top right, rgba(243, 108, 38, 0.16), transparent 26%),
      linear-gradient(180deg, #fffdf8 0%, #f4ead5 100%);
    color: var(--open-ink);
  }
  .open-wrap {
    width: 100%;
    max-width: none;
    margin: 0;
    height: 100vh;
    overflow: hidden;
    box-sizing: border-box;
    padding: 20px 20px 20px;
  }
  .open-topbar {
    display: grid;
    grid-template-columns: 1fr auto;
    gap: 14px;
    align-items: center;
    padding: 18px 20px;
    border-radius: 5px;
    background: linear-gradient(135deg, #2f7a31 0%, #61b846 46%, #f36c26 100%);
    color: #fff;
    box-shadow: var(--open-shadow);
  }
  .open-brand {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
  }
  .open-brand h1 {
    margin: 0;
    font-size: 30px;
    font-weight: 900;
    letter-spacing: 0.03em;
    text-transform: uppercase;
  }
  .open-brand-count {
    min-width: 88px;
    min-height: 38px;
    padding: 6px 10px;
    border-radius: 5px;
    background: rgba(255,255,255,0.16);
    border: 1px solid rgba(255,255,255,0.2);
    color: #fff;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    font-size: 12px;
    font-weight: 900;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-left: auto;
  }
  .open-brand-count span {
    font-size: 18px;
    line-height: 1;
  }
  .open-brand-count strong {
    font-size: 11px;
    font-weight: 900;
  }
  .open-top-actions {
    display: flex;
    gap: 10px;
    flex-wrap: nowrap;
    justify-content: flex-end;
  }
  .open-top-btn {
    min-height: 50px;
    padding: 0 18px;
    border-radius: 5px;
    border: 1px solid rgba(255,255,255,0.18);
    background: rgba(255,255,255,0.14);
    color: #fff !important;
    font-size: 13px;
    font-weight: 900;
    text-transform: uppercase;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    text-decoration: none !important;
    box-shadow: inset 0 0 0 1px rgba(255,255,255,0.12);
  }
  .open-shell {
    display: block;
    margin-top: 1px;
    min-height: 0;
  }
  .open-card {
    background: rgba(255,255,255,0.96);
    border: 1px solid var(--open-border);
    border-radius: 5px;
    box-shadow: var(--open-shadow);
  }
  .open-main {
    padding: 10px;
    display: flex;
    flex-direction: column;
    height: calc(100vh - 115px);
    min-height: 0;
    max-height: calc(100vh - 115px);
    overflow: hidden;
  }
  .open-search-panel[hidden] {
    display: none !important;
  }
  .open-list-head h2 {
    margin: 0;
    font-size: 18px;
    font-weight: 900;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: var(--open-green-deep);
  }
  .open-search-panel {
    margin-bottom: 2px;
  }
  .open-search-box {
    margin-top: 0;
    padding: 8px;
    border-radius: 5px;
    background: linear-gradient(180deg, #fffdf8 0%, #fff3e4 100%);
    border: 1px solid rgba(97, 184, 70, 0.12);
  }
  .open-search-label {
    display: block;
    margin-bottom: 8px;
    font-size: 12px;
    font-weight: 900;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: #7a5424;
  }
  .open-search-input {
    width: 100%;
    height: 58px;
    padding: 0 16px;
    border-radius: 5px;
    border: 1px solid rgba(122, 84, 36, 0.16);
    background: #fff;
    font-size: 24px;
    font-weight: 900;
    color: var(--open-ink);
    text-align: center;
    outline: none;
  }
  .open-search-input:focus {
    border-color: rgba(97, 184, 70, 0.5);
    box-shadow: 0 0 0 4px rgba(97, 184, 70, 0.12);
  }
  .open-search-actions {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
    margin-top: 18px;
  }
  .open-action-btn {
    min-height: 50px;
    border-radius: 5px;
    border: 1px solid rgba(97, 184, 70, 0.18);
    background: #fff;
    color: var(--open-green-deep);
    font-size: 13px;
    font-weight: 900;
    text-transform: uppercase;
  }
  .open-action-btn.primary {
    background: linear-gradient(135deg, #2f7a31 0%, #61b846 42%, #f36c26 100%);
    color: #fff;
    border: 0;
  }
  .open-mobile-search-trigger {
    display: none;
    width: 100%;
    margin-top: 0;
  }
  .open-list-head {
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    gap: 1px;
    align-items: stretch;
    margin-bottom: 1px;
    padding-bottom: 1px;
    border-bottom: 1px solid rgba(97, 184, 70, 0.12);
  }
  .open-list-title {
    flex: 1 1 auto;
    display: flex;
    align-items: center;
    min-height: 30px;
  }
  .open-list-tools {
    display: flex;
    align-items: stretch;
    gap: 1px;
    width: 100%;
  }
  .open-tabs {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 6px;
    padding: 4px;
    border-radius: 5px;
    background: #f4ebdc;
    min-height: 46px;
    align-items: center;
    width: 100%;
  }
  .open-count-pill {
    min-width: 110px;
    padding: 10px 14px;
    border-radius: 5px;
    background: #eef8e8;
    color: var(--open-green-deep);
    text-align: center;
    min-height: 56px;
    display: flex;
    flex-direction: column;
    justify-content: center;
  }
  .open-count-pill strong {
    display: block;
    font-size: 11px;
    font-weight: 900;
    letter-spacing: 0.08em;
    text-transform: uppercase;
  }
  .open-count-pill span {
    display: block;
    margin-top: 4px;
    font-size: 24px;
    font-weight: 900;
  }
  .open-status {
    margin-top: 2px;
    min-height: 22px;
    font-size: 13px;
    font-weight: 800;
    color: #7a5424;
  }
  .open-results {
    margin-top: 2px;
    padding: 10px;
    min-height: 320px;
    max-height: calc(100vh - 360px);
    overflow-y: auto;
    overflow-x: hidden;
    border-radius: 5px;
    border: 1px solid rgba(97, 184, 70, 0.18);
    background: rgba(255, 248, 234, 0.86);
    box-shadow: inset 0 0 0 1px rgba(255,255,255,0.75);
    scrollbar-width: thin;
    scrollbar-color: rgba(97,184,70,0.24) rgba(255,255,255,0.8);
  }
  .open-results::-webkit-scrollbar {
    width: 10px;
  }
  .open-results::-webkit-scrollbar-track {
    background: rgba(255,255,255,0.8);
  }
  .open-results::-webkit-scrollbar-thumb {
    border-radius: 5px;
    background: rgba(97,184,70,0.24);
    border: 2px solid rgba(255,255,255,0.85);
  }
  .open-tab-btn {
    min-height: 38px;
    padding: 0 14px;
    border: 0;
    border-radius: 5px;
    background: transparent;
    color: #7a5424;
    font-size: 12px;
    font-weight: 900;
    text-transform: uppercase;
    letter-spacing: 0.06em;
  }
  .open-tab-btn.active {
    background: #fff;
    color: #2f7a31;
    box-shadow: 0 12px 18px rgba(79, 58, 24, 0.08);
  }
  .open-sales-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 8px;
  }
  .open-sales-grid.open-grid-three {
    grid-template-columns: repeat(3, minmax(0, 1fr));
  }
  .open-sales-grid.open-grid-two {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
  .open-sale-card {
    width: 100%;
    aspect-ratio: 1 / 1;
    min-height: 0;
    padding: 10px;
    border-radius: 5px;
    border: 1px solid rgba(97, 184, 70, 0.14);
    background: linear-gradient(180deg, #fffdf8 0%, #fff5ea 100%);
    text-align: left;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
    box-shadow: 0 8px 14px rgba(79, 58, 24, 0.06);
  }
  .open-sale-card.open-sale-card-open {
    background: linear-gradient(180deg, #fffdf8 0%, #fff5ea 100%);
  }
  .open-sale-card.open-sale-card-new {
    background: linear-gradient(180deg, #fff0e0 0%, #ffe4cc 100%);
    border-color: rgba(243, 108, 38, 0.4);
    justify-content: flex-start;
  }
  .open-sale-card.open-sale-card-new:hover,
  .open-sale-card.open-sale-card-new:focus {
    border-color: rgba(243, 108, 38, 0.6);
    box-shadow: 0 12px 22px rgba(243, 108, 38, 0.15);
  }
  .open-sale-card.open-sale-card-new .open-sale-no {
    color: #d45d1a;
  }
  .open-sale-card.open-sale-card-new .open-sale-amount-line {
    color: #d45d1a;
  }
  .open-sale-head {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 4px;
  }
  .open-sale-card.is-color {
    border-color: var(--open-card-color, rgba(97, 184, 70, 0.28));
    background:
      linear-gradient(180deg, color-mix(in srgb, var(--open-card-color, #61b846) 14%, #fffdf8) 0%, color-mix(in srgb, var(--open-card-color, #61b846) 10%, #fff5ea) 100%);
    box-shadow:
      0 18px 30px color-mix(in srgb, var(--open-card-color, #61b846) 20%, rgba(79, 58, 24, 0.08)),
      inset 0 0 0 1px color-mix(in srgb, var(--open-card-color, #61b846) 18%, rgba(255,255,255,0.4));
  }
  .open-sale-card.is-color strong,
  .open-sale-card.is-color .open-sale-no,
  .open-sale-card.is-color .open-sale-meta span b {
    color: color-mix(in srgb, var(--open-card-color, #61b846) 54%, #2f2418);
  }
  .open-sale-card:hover,
  .open-sale-card:focus {
    transform: translateY(-1px);
    border-color: rgba(243, 108, 38, 0.34);
    box-shadow: 0 12px 22px rgba(79, 58, 24, 0.1);
    outline: none;
  }
  .open-sale-total {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    margin-top: 8px;
    padding: 8px 10px;
    border-radius: 7px;
    background: rgba(47, 122, 49, .1);
    color: #2f7a31;
  }
  .open-sale-total small {
    font-size: 10px;
    font-weight: 900;
    letter-spacing: .05em;
  }
  .open-sale-total strong {
    font-size: 17px;
    font-weight: 900;
    color: #8a1232;
  }
  .open-sale-no {
    display: inline-block;
    margin-top: 0;
    font-size: 20px;
    font-weight: 900;
    color: var(--open-green-deep);
    line-height: 1.05;
    padding: 0;
  }
  .open-sale-alert {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 22px;
    padding: 0 8px;
    border-radius: 5px;
    background: #ffe0e0;
    color: #b42318;
    font-size: 11px;
    font-weight: 900;
    text-transform: uppercase;
    letter-spacing: 0.05em;
  }
  .open-sale-meta {
    margin-top: 6px;
    display: grid;
    gap: 3px;
    font-size: 10px;
    font-weight: 700;
    color: #6f5433;
  }
  .open-sale-meta span {
    display: flex;
    justify-content: space-between;
    gap: 8px;
  }
  .open-sale-meta span b {
    color: #2f7a31;
    min-width: 0;
    flex: 0 0 auto;
  }
  .open-sale-meta span i {
    font-style: normal;
    color: #2b2319;
    text-align: right;
    min-width: 0;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
  }
  .open-id-chip-row {
    display: flex;
    flex-wrap: nowrap;
    gap: 5px;
    margin-top: 6px;
    padding-top: 0;
  }
  .open-sale-card-new .open-id-chip-row {
    margin-top: 4px;
  }
  .open-id-chip {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    min-height: 24px;
    padding: 0 8px;
    border-radius: 5px;
    background: #61b846;
    color: #fff;
    font-size: 11px;
    font-weight: 900;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    flex: 1 1 0;
    min-width: 0;
  }
  .open-sale-card-new .open-id-chip {
    width: 100%;
    flex: 0 0 100%;
  }
  .open-sale-amount-line {
    display: block;
    width: 100%;
    margin-top: 5px;
    font-size: 16px;
    font-weight: 900;
    color: var(--open-green-deep);
    line-height: 1.15;
  }
  .open-sale-amount-line small {
    display: block;
    margin-bottom: 2px;
    font-size: 10px;
    font-weight: 900;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: #7a5424;
  }
  .open-child-list { margin-top: 6px; padding-top: 5px; border-top: 1px dashed rgba(0,0,0,.35); display: flex; flex-direction: column; gap: 2px; font-size: 12px; text-align: left; }
  .open-child-list > b { font-size: 11px; letter-spacing: .04em; }
  .open-id-dot {
    width: 12px;
    height: 12px;
    border-radius: 5px;
    border: 1px solid rgba(0,0,0,0.08);
    flex: 0 0 12px;
  }
  .open-empty {
    padding: 26px 18px;
    border-radius: 5px;
    background: #fff8ea;
    border: 1px dashed rgba(122, 84, 36, 0.26);
    text-align: center;
    color: #7a5424;
    font-size: 15px;
    font-weight: 800;
  }
  .open-hidden-file {
    position: absolute;
    left: -9999px;
    width: 1px;
    height: 1px;
    overflow: hidden;
  }
  @media (min-width: 992px) {
    .open-sales-grid {
      grid-template-columns: repeat(6, minmax(0, 1fr));
      gap: 12px;
    }
    .open-sales-grid.open-grid-three {
      grid-template-columns: repeat(4, minmax(0, 1fr));
    }
    .open-sales-grid.open-grid-two {
      grid-template-columns: repeat(3, minmax(0, 1fr));
    }
    .open-sale-card {
      aspect-ratio: 1 / 1;
      min-height: 120px;
      padding: 12px;
    }
    .open-sale-no {
      font-size: 18px;
    }
    .open-sale-amount-line {
      font-size: 14px;
    }
    .open-sale-meta {
      font-size: 10px;
    }
    .open-id-chip {
      font-size: 11px;
      padding: 0 8px;
      min-height: 22px;
    }
  }
  @media (max-width: 991px) {
    .open-wrap {
      padding: 14px 10px 24px;
    }
    .open-topbar {
      grid-template-columns: 1fr;
      padding: 16px 14px;
      border-radius: 5px;
    }
    .open-brand h1 {
      font-size: 24px;
    }
    .open-search-panel {
      padding: 2px 0 0;
    }
    .open-main {
      padding: 8px;
      height: calc(100vh - 128px);
      max-height: calc(100vh - 128px);
    }
    .open-list-tools {
      width: 100%;
    }
    .open-sales-grid {
      grid-template-columns: repeat(3, minmax(0, 1fr));
      gap: 10px;
    }
    .open-sales-grid.open-grid-three {
      grid-template-columns: repeat(3, minmax(0, 1fr));
    }
    .open-sales-grid.open-grid-two {
      grid-template-columns: repeat(2, minmax(0, 1fr));
    }
    .open-sale-card {
      padding: 10px;
    }
    .open-sale-no {
      font-size: 24px;
    }
  }
  @media (max-width: 575px) {
    .open-mobile-search-trigger {
      display: inline-flex;
      align-items: center;
      justify-content: center;
    }
    .open-search-box {
      display: none;
    }
    .open-search-input {
      height: 54px;
      font-size: 22px;
    }
    .open-sales-grid,
    .open-sales-grid.open-grid-three,
    .open-sales-grid.open-grid-two {
      grid-template-columns: repeat(2, minmax(0, 1fr));
    }
    .open-top-actions {
      justify-content: stretch;
    }
    .open-top-btn {
      flex: 1 1 0;
      min-width: 0;
    }
  }
</style>
<body class="fixed-sn deep-orange-skin">
  <div class="open-wrap">
    <div class="open-topbar">
      <div class="open-brand">
        <h1 id="open-page-title">Open Orders</h1>
        <div class="open-brand-count">
          <strong>My Bills</strong>
          <span id="open-recent-count">0</span>
        </div>
      </div>
      <div class="open-top-actions">
        <a href="../dash/dash.php" class="open-top-btn">Home</a>
        <a href="../log_out.php" class="open-top-btn">Log Out</a>
      </div>
    </div>

    <div class="open-shell">
      <div class="open-card open-main">
        <div class="open-list-head">
          <div class="open-list-title">
            <h2>Select Bill To Continue</h2>
          </div>
          <div class="open-list-tools">
            <div class="open-tabs">
              <button type="button" class="open-tab-btn" data-open-tab="open">Open Bills</button>
              <button type="button" class="open-tab-btn" data-open-tab="new">Closed Bills</button>
            </div>
          </div>
        </div>
        <div class="open-search-panel" id="open-search-panel" hidden>
          <button type="button" class="open-action-btn primary open-mobile-search-trigger" id="open-mobile-search-btn">Search Bill Number</button>
          <div class="open-search-box">
            <label class="open-search-label" for="open-sale-search">Search Bill Number</label>
            <input type="text" id="open-sale-search" class="open-search-input" inputmode="numeric" autocomplete="off" placeholder="Enter bill no">
            <div class="open-search-actions">
              <button type="button" class="open-action-btn primary" id="open-sale-search-btn">Search</button>
              <button type="button" class="open-action-btn" id="open-sale-clear-btn">Clear</button>
            </div>
          </div>
          <input type="file" id="open-scan-file" class="open-hidden-file" accept="image/*" capture="environment">
          <div class="open-status" id="open-scan-status"></div>
        </div>
        <div class="open-status" id="open-results-status">Loading open sales...</div>
        <div class="open-results" id="open-results"></div>
      </div>
    </div>
  </div>

  <div id="modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" id="load"></div>
  </div>
  <div id="closed-bill-confirm-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-sm" role="document">
      <div class="modal-content" style="border-radius:5px; overflow:hidden;">
        <div class="modal-header" style="background:linear-gradient(135deg, #f36c26 0%, #d45d1a 100%); color:#fff;">
          <button type="button" class="close" data-dismiss="modal" style="color:#fff; opacity:1;">&times;</button>
          <h4 class="modal-title">Open Closed Bill</h4>
        </div>
        <div class="modal-body" style="background:#fffaf3; padding:24px; text-align:center;">
          <p style="font-size:15px; font-weight:700; color:#3b2a18; margin:0 0 16px 0;">
            You are about to open a <strong style="color:#d45d1a;">closed bill</strong>.
          </p>
          <p style="font-size:13px; color:#6f5433; margin:0 0 20px 0;">
            This will create a continuation of the closed bill. Do you want to proceed?
          </p>
          <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
            <button type="button" class="open-action-btn" id="closed-bill-cancel-btn">Cancel</button>
            <button type="button" class="open-action-btn primary" id="closed-bill-confirm-btn">Continue</button>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div id="open-search-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-sm" role="document">
      <div class="modal-content" style="border-radius:5px; overflow:hidden;">
        <div class="modal-header" style="background:linear-gradient(135deg, #2f7a31 0%, #61b846 42%, #f36c26 100%); color:#fff;">
          <button type="button" class="close" data-dismiss="modal" style="color:#fff; opacity:1;">&times;</button>
          <h4 class="modal-title">Search Bill</h4>
        </div>
        <div class="modal-body" style="background:#fffaf3;">
          <label class="open-search-label" for="open-sale-search-mobile">Search Bill Number</label>
          <input type="text" id="open-sale-search-mobile" class="open-search-input" inputmode="numeric" autocomplete="off" placeholder="Enter bill no">
          <div style="display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:8px;margin-top:12px;">
            <button type="button" class="open-action-btn" data-mobile-key="1">1</button>
            <button type="button" class="open-action-btn" data-mobile-key="2">2</button>
            <button type="button" class="open-action-btn" data-mobile-key="3">3</button>
            <button type="button" class="open-action-btn" data-mobile-key="4">4</button>
            <button type="button" class="open-action-btn" data-mobile-key="5">5</button>
            <button type="button" class="open-action-btn" data-mobile-key="6">6</button>
            <button type="button" class="open-action-btn" data-mobile-key="7">7</button>
            <button type="button" class="open-action-btn" data-mobile-key="8">8</button>
            <button type="button" class="open-action-btn" data-mobile-key="9">9</button>
            <button type="button" class="open-action-btn" data-mobile-key="C">C</button>
            <button type="button" class="open-action-btn" data-mobile-key="0">0</button>
            <button type="button" class="open-action-btn" data-mobile-key="<-">&larr;</button>
          </div>
          <div class="open-search-actions">
            <button type="button" class="open-action-btn primary" id="open-sale-search-btn-mobile">Search</button>
            <button type="button" class="open-action-btn" id="open-sale-clear-btn-mobile">Clear</button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="../../assets/plugins/jquery/jquery-3.2.1.min.js"></script>
  <script src="../../assets/plugins/bootstrap/4.0.0/js/bootstrap.bundle.min.js"></script>
  <script>
    (function() {
      function logOpenButtonClick(button) {
        if (!button) return;
        var label = (button.innerText || button.getAttribute('aria-label') || '').trim().replace(/\s+/g, ' ').slice(0, 240);
        var metadata = {};
        Array.prototype.forEach.call(button.attributes || [], function(attribute) {
          if (attribute.name.indexOf('data-') === 0) metadata[attribute.name] = attribute.value;
        });
        var payload = new FormData();
        payload.append('action', 'log_button');
        payload.append('button', button.id || button.className || button.tagName || 'button');
        payload.append('label', label);
        payload.append('page', window.location.pathname + window.location.search);
        payload.append('target', button.getAttribute('href') || '');
        payload.append('metadata', JSON.stringify(metadata));
        var endpoint = '../API/common/api.php';
        if (navigator.sendBeacon) {
          navigator.sendBeacon(endpoint, payload);
        } else if (window.jQuery) {
          $.ajax({ url: endpoint, type: 'POST', data: payload, processData: false, contentType: false });
        }
      }

      document.addEventListener('click', function(event) {
        var button = event.target.closest && event.target.closest('button, a, [role="button"]');
        if (button) logOpenButtonClick(button);
      }, true);

      var searchInput = document.getElementById('open-sale-search');
      var mobileSearchInput = document.getElementById('open-sale-search-mobile');
      var searchPanel = document.getElementById('open-search-panel');
      var resultsWrap = document.getElementById('open-results');
      var resultsStatus = document.getElementById('open-results-status');
      var scanStatus = document.getElementById('open-scan-status');
      var scanFile = document.getElementById('open-scan-file');
      var searchTimer = null;
      var activeTab = 'open';
      var pageTitle = document.getElementById('open-page-title');
      var recentCount = document.getElementById('open-recent-count');
      var bootstrapRendered = false;

      function currentUrlTab() {
        try {
          var params = new URLSearchParams(window.location.search || '');
          return params.get('tab') === 'new' ? 'new' : 'open';
        } catch (e) {
          return 'open';
        }
      }

      function setSearchValue(value) {
        searchInput.value = value;
        if (mobileSearchInput) {
          mobileSearchInput.value = value;
        }
      }

      function currentSearchValue() {
        return (mobileSearchInput && $('#open-search-modal').hasClass('show') ? mobileSearchInput.value : searchInput.value).trim();
      }

      function setResultsStatus(message) {
        resultsStatus.textContent = message || '';
      }

      function handleApiFailure(jqXHR, fallbackMessage) {
        if (jqXHR && jqXHR.status === 401) {
          window.location = '../log_out.php';
          return true;
        }
        setResultsStatus(fallbackMessage || 'Unable to load the page right now.');
        return false;
      }

      function loadOpenPageBootstrap(done) {
        $.getJSON('api/open_api.php', {
          action: 'bootstrap',
          q: '',
          tab: currentUrlTab()
        }).done(function(res) {
          var data = res && res.data ? res.data : {};
          if (res && res.ok) {
            bootstrapRendered = true;
            activeTab = data.default_tab || currentUrlTab();
            if (pageTitle) {
              pageTitle.textContent = data.page_title || 'Open Orders';
            }
            if (recentCount) {
              recentCount.textContent = String(data.recent_count || 0);
            }
            renderSales(data.sales || [], '');
          }
          if (typeof done === 'function') {
            done();
          }
        }).fail(function(jqXHR) {
          if (handleApiFailure(jqXHR, 'Unable to load the page right now.')) {
            return;
          }
          activeTab = currentUrlTab();
          if (typeof done === 'function') {
            done();
          }
        });
      }

      function syncTabUi() {
        if (!searchPanel) {
          return;
        }
        searchPanel.hidden = activeTab !== 'new';
        if (window.innerWidth <= 575) {
          var searchBox = searchPanel.querySelector('.open-search-box');
          if (searchBox) {
            searchBox.style.display = activeTab === 'new' ? 'none' : '';
          }
        }
        if (activeTab !== 'new') {
          scanStatus.textContent = '';
        }
      }

      function renderSales(sales, query) {
          var html = '';
          var i;
          if (!sales.length) {
            resultsWrap.innerHTML = '<div class="open-empty">' + (activeTab === 'open' ? 'No open bills were found.' : 'No closed bills were found.') + '</div>';
          } else if (activeTab === 'open') {
            html = '<div class="open-sales-grid open-grid-two">';
            for (i = 0; i < sales.length; i++) {
              var sale = sales[i];
              var label = sale.identifier_label || 'No Tag';
              var typeLabel = sale.bill_mode === 'last' ? 'Open Bill' : 'Continuation';
              var hasColorGlow = !!(sale.identifier_hex || sale.identifier_label);
              var glowColor = sale.identifier_hex || (sale.identifier_type === 'name' ? '#f36c26' : '');
              var cardClass = 'open-sale-card open-sale-card-open' + (hasColorGlow ? ' is-color' : '');
              var cardStyle = glowColor ? ' style="--open-card-color:' + String(glowColor).replace(/"/g, '&quot;') + ';"' : '';
              html += '<button type="button" class="' + cardClass + '"' + cardStyle + ' data-open-sale="' + sale.sale_id + '" data-open-mode="open" data-sale-card="1" data-bill-mode="' + sale.bill_mode + '" data-need-identifier="' + (sale.need_identifier ? '1' : '0') + '">';
              html += '<div class="open-sale-head"><span class="open-sale-no">#' + sale.sale_id + '</span></div>';
              html += '<div class="open-id-chip-row"><span class="open-id-chip">' + label + '</span><span class="open-id-chip">Children ' + sale.child_count + '</span>';
              if (sale.current_open_child > 0) html += '<span class="open-id-chip">Open Child #' + sale.current_open_child + '</span>';
              html += '</div><div class="open-sale-total"><small>' + (sale.bill_mode === 'last' ? 'OPEN ITEMS TOTAL' : 'FAMILY TOTAL') + '</small><strong>' + Number(sale.bill_mode === 'last' ? (sale.open_items_total || 0) : (sale.family_total || 0)).toFixed(2) + '</strong></div><div class="open-sale-meta">';
              html += '<span><b>Parent</b><i>' + Number(sale.parent_amount || 0).toFixed(2) + '</i></span><span><b>Children</b><i>' + Number(sale.children_total || 0).toFixed(2) + '</i></span><span><b>Counter</b><i>' + (sale.counter_name || '') + '</i></span><span><b>Type</b><i>' + typeLabel + '</i></span></div>';
              if (Array.isArray(sale.child_bills) && sale.child_bills.length) {
                html += '<div class="open-child-list"><b>CHILD BILLS</b>';
                sale.child_bills.forEach(function(child) { html += '<span>#' + child.sale_id + ' · ' + Number(child.amount || 0).toFixed(2) + ' · ' + (child.counter_name || 'Counter') + '</span>'; });
                html += '</div>';
              }
              html += '</button>';
            }
            resultsWrap.innerHTML = html + '</div>';
          } else {
            html = '<div class="open-sales-grid open-grid-three">';
            for (i = 0; i < sales.length; i++) {
              var closedSale = sales[i];
              html += '<button type="button" class="open-sale-card open-sale-card-new" data-open-sale="' + closedSale.sale_id + '" data-open-mode="new" data-sale-card="1" data-need-identifier="' + (closedSale.need_identifier ? '1' : '0') + '"><div class="open-sale-head"><span class="open-sale-no">#' + closedSale.sale_id + '</span></div><div class="open-id-chip-row"><span class="open-id-chip">' + (closedSale.counter_name || '') + '</span></div>';
              if (closedSale.is_parent) html += '<div class="open-id-chip-row" style="margin-top:4px;"><span class="open-id-chip">Family ' + (1 + Number(closedSale.child_count || 0)) + ' Bills</span></div><div class="open-sale-amount-line"><small>Parent Total</small>' + Number(closedSale.amount || 0).toFixed(2) + '</div><div class="open-sale-amount-line" style="margin-top:2px;"><small>Family Sum</small>' + Number(closedSale.family_total || 0).toFixed(2) + '</div>';
              else html += '<div class="open-sale-amount-line"><small>Amount</small>' + Number(closedSale.amount || 0).toFixed(2) + '</div>';
              html += '<div class="open-sale-meta" style="margin-top:5px;"><span><b>Status</b><i>' + (closedSale.bill_role || 'closed') + '</i></span></div></button>';
            }
            resultsWrap.innerHTML = html + '</div>';
          }
          var count = sales.length;
          setResultsStatus(query ? (count ? 'Showing ' + count + ' result' + (count === 1 ? '' : 's') + ' for "' + query + '".' : 'No bills found for "' + query + '".') : 'Showing ' + count + ' bill' + (count === 1 ? '' : 's') + '.');
      }

      function loadSalesList(query) {
        var loadingLabel = activeTab === 'open' ? 'Loading open bills...' : 'Loading closed bills...';
        setResultsStatus(query ? 'Searching bill ' + query + '...' : loadingLabel);
        $.getJSON('api/open_api.php', {
          action: 'sales',
          q: query || '',
          tab: activeTab
        }).done(function(res) {
          var data = res && res.data ? res.data : {};
          var sales = data.sales || [];
          if (!res || !res.ok) {
            resultsWrap.innerHTML = '<div class="open-empty">Unable to load the list right now.</div>';
            setResultsStatus(res && res.message ? res.message : 'Unable to load the list right now.');
            return;
          }
          renderSales(sales, query);
          return;
          if (!sales.length) {
            resultsWrap.innerHTML = '<div class="open-empty">' + (activeTab === 'open' ? 'No open bills were found.' : 'No closed bills were found.') + '</div>';
          } else if (activeTab === 'open') {
            html = '<div class="open-sales-grid open-grid-two">';
            for (i = 0; i < sales.length; i++) {
              var sale = sales[i];
              var label = sale.identifier_label || 'No Tag';
              var typeLabel = sale.bill_mode === 'last' ? 'Open Bill' : 'Continuation';
              var hasColorGlow = !!(sale.identifier_hex || sale.identifier_label);
              var glowColor = sale.identifier_hex || (sale.identifier_type === 'name' ? '#f36c26' : '');
              var cardClass = 'open-sale-card open-sale-card-open' + (hasColorGlow ? ' is-color' : '');
              var cardStyle = glowColor ? ' style="--open-card-color:' + String(glowColor).replace(/"/g, '&quot;') + ';"' : '';
              html += '<button type="button" class="' + cardClass + '"' + cardStyle + ' data-open-sale="' + sale.sale_id + '" data-open-mode="open" data-sale-card="1" data-bill-mode="' + sale.bill_mode + '" data-need-identifier="' + (sale.need_identifier ? '1' : '0') + '">';
              html += '<div class="open-sale-head"><span class="open-sale-no">#' + sale.sale_id + '</span></div>';
              html += '<div class="open-id-chip-row"><span class="open-id-chip">' + label + '</span><span class="open-id-chip">Children ' + sale.child_count + '</span>';
              if (sale.current_open_child > 0) html += '<span class="open-id-chip">Open Child #' + sale.current_open_child + '</span>';
              html += '</div>';
              html += '<div class="open-sale-meta">';
              html += '<span><b>Parent</b><i>' + Number(sale.parent_amount || 0).toFixed(2) + '</i></span>';
              html += '<span><b>Children</b><i>' + Number(sale.children_total || 0).toFixed(2) + '</i></span>';
              html += '<span><b>Family</b><i>' + Number(sale.family_total || 0).toFixed(2) + '</i></span>';
              html += '<span><b>Counter</b><i>' + (sale.counter_name || '') + '</i></span>';
              if (Array.isArray(sale.child_bills) && sale.child_bills.length) { html += '<span><b>Child bills</b><i>' + sale.child_bills.map(function(child) { return '#' + child.sale_id + ' ' + Number(child.amount || 0).toFixed(2) + ' ' + (child.counter_name || ''); }).join(' · ') + '</i></span>'; }
              html += '<span><b>Type</b><i>' + typeLabel + '</i></span>';
              html += '</div></button>';
            }
            html += '</div>';
            resultsWrap.innerHTML = html;
          } else {
            html = '<div class="open-sales-grid open-grid-three">';
            for (i = 0; i < sales.length; i++) {
              var closedSale = sales[i];
              html += '<button type="button" class="open-sale-card open-sale-card-new" data-open-sale="' + closedSale.sale_id + '" data-open-mode="new" data-sale-card="1" data-need-identifier="' + (closedSale.need_identifier ? '1' : '0') + '">';
              html += '<div class="open-sale-head"><span class="open-sale-no">#' + closedSale.sale_id + '</span></div>';
              html += '<div class="open-id-chip-row"><span class="open-id-chip">' + (closedSale.counter_name || '') + '</span></div>';
              if (closedSale.is_parent) {
                html += '<div class="open-id-chip-row" style="margin-top:4px;"><span class="open-id-chip">Family ' + (1 + Number(closedSale.child_count || 0)) + ' Bills</span></div>';
                html += '<div class="open-sale-amount-line"><small>Parent Total</small>' + Number(closedSale.amount || 0).toFixed(2) + '</div>';
                html += '<div class="open-sale-amount-line" style="margin-top:2px;"><small>Family Sum</small>' + Number(closedSale.family_total || 0).toFixed(2) + '</div>';
              } else {
                html += '<div class="open-sale-amount-line"><small>Amount</small>' + Number(closedSale.amount || 0).toFixed(2) + '</div>';
              }
              html += '<div class="open-sale-meta" style="margin-top:5px;"><span><b>Status</b><i>' + (closedSale.bill_role || 'closed') + '</i></span></div>';
              html += '</button>';
            }
            html += '</div>';
            resultsWrap.innerHTML = html;
          }
          var count = sales.length;
          if (count > 0) {
            if (query) {
              setResultsStatus('Showing ' + count + ' ' + (activeTab === 'open' ? 'open bill' : 'closed bill') + (count === 1 ? '' : 's') + ' for "' + query + '".');
            } else {
              setResultsStatus('Showing ' + count + ' ' + (activeTab === 'open' ? 'open bill' : 'closed bill') + (count === 1 ? '' : 's') + '.');
            }
          } else {
            if (query) {
              setResultsStatus('No ' + (activeTab === 'open' ? 'open bill' : 'closed bill') + ' found for "' + query + '".');
            } else {
              setResultsStatus(activeTab === 'open' ? 'No open bills found.' : 'No closed bills found.');
            }
          }
        }).fail(function(jqXHR) {
          if (handleApiFailure(jqXHR, 'Unable to load the list right now.')) {
            return;
          }
          resultsWrap.innerHTML = '<div class="open-empty">Something went wrong while loading the list.</div>';
          setResultsStatus('Unable to load the list right now.');
        });
      }

      window.openRefreshSalesList = function() {
        loadSalesList(currentSearchValue());
      };

      function applyKey(key) {
        var currentValue = currentSearchValue();
        if (key === 'C') {
          setSearchValue('');
          loadSalesList('');
          return;
        }
        if (key === '<-') {
          setSearchValue(currentValue.slice(0, -1));
          loadSalesList(currentSearchValue());
          return;
        }
        setSearchValue(currentValue + key);
        loadSalesList(currentSearchValue());
      }

      function decodeScannedImage(file) {
        if (!file) {
          return;
        }
        var targetStatus = scanStatus;
        if (!('BarcodeDetector' in window)) {
          targetStatus.textContent = 'This browser cannot read the bill code here. Enter the number manually.';
          return;
        }
        targetStatus.textContent = 'Reading bill code...';
        createImageBitmap(file).then(function(bitmap) {
          var detector = new BarcodeDetector({ formats: ['qr_code', 'code_128', 'ean_13', 'ean_8'] });
          return detector.detect(bitmap);
        }).then(function(codes) {
          if (!codes || !codes.length) {
            targetStatus.textContent = 'No bill code found. Try again or type the bill number.';
            return;
          }
          var raw = codes[0].rawValue || '';
          var match = raw.match(/(\d{2,})/);
          if (!match) {
            targetStatus.textContent = 'The scan opened, but no bill number was found. Type the bill number instead.';
            return;
          }
          setSearchValue(match[1]);
          targetStatus.textContent = 'Bill #' + match[1] + ' found.';
          loadSalesList(match[1]);
        }).catch(function() {
          targetStatus.textContent = 'The bill could not be read from the camera image. Type the number manually.';
        });
      }

      window.openLoadIdentifierModal = function(saleId, counterId, billMode) {
        $('#modal').modal('show');
        $('#load').html('<div class="modal-content"><div class="modal-body" style="padding:28px;text-align:center;font-weight:900;">Loading identifier...</div></div>');
        $.ajax({
          url: 'identifier_modal.php',
          type: 'GET',
          data: { sale_id: saleId, counter_id: counterId || 0, bill_mode: billMode || '' },
          dataType: 'html'
        }).done(function(html) {
          $('#load').html(html);
        }).fail(function() {
          $('#load').html('<div class="modal-content"><div class="modal-body"><div class="alert alert-danger" style="margin-bottom:0;">Unable to load the identifier step.</div></div></div>');
        });
      };

      window.openLoadContinueTypeModal = function(saleId) {
        $('#modal').modal('show');
        $('#load').html('<div class="modal-content"><div class="modal-body" style="padding:28px;text-align:center;font-weight:900;">Loading bill options...</div></div>');
        $.ajax({
          url: 'continue_type_modal.php',
          type: 'GET',
          data: { sale_id: saleId },
          dataType: 'html'
        }).done(function(html) {
          $('#load').html(html);
        }).fail(function() {
          $('#load').html('<div class="modal-content"><div class="modal-body"><div class="alert alert-danger" style="margin-bottom:0;">Unable to load the bill options.</div></div></div>');
        });
      };

      window.openLoadCounterModal = function(parentSaleId, mode) {
        $('#modal').modal('show');
        $('#load').html('<div class="modal-content"><div class="modal-body" style="padding:28px;text-align:center;font-weight:900;">Loading counters...</div></div>');
        $.ajax({
          url: 'counter_modal.php',
          type: 'GET',
          data: { sale_id: parentSaleId, mode: mode || 'open' },
          dataType: 'html'
        }).done(function(res) {
          $('#load').html(res);
        }).fail(function() {
          $('#load').html('<div class="modal-content"><div class="modal-body"><div class="alert alert-danger" style="margin-bottom:0;">Unable to load counters right now.</div></div></div>');
        });
      };

      window.confirmCloseOpenOrder = function(parentSaleId, printFinal, billCounterId) {
        parentSaleId = parseInt(parentSaleId, 10) || 0;
        if (parentSaleId < 1) {
          alert('Invalid bill selected.');
          return;
        }
        billCounterId = parseInt(billCounterId, 10) || 0;
        $.ajax({
          url: 'api/open_api.php',
          type: 'POST',
          data: {
            action: 'close_order',
            close_parent_sale_id: parentSaleId,
            print_final: printFinal ? 1 : 0,
            bill_cntr: billCounterId,
            open_mode: 'open',
            bill_mode: printFinal ? 'last' : 'each'
          },
          dataType: 'json'
        }).done(function(res) {
          var payload = res && res.data ? res.data : {};
          if (!res || !res.ok) {
            alert(res && res.message ? res.message : 'Unable to close this bill right now.');
            return;
          }
          if (payload.redirect) {
            window.location = payload.redirect;
            return;
          }
          if (window.openRefreshSalesList) {
            window.openRefreshSalesList();
          } else {
            window.location.reload();
          }
          $('#modal').modal('hide');
        }).fail(function() {
          alert('Unable to close this bill right now.');
        });
      };

      var pendingClosedBillId = null;

      document.getElementById('closed-bill-cancel-btn').addEventListener('click', function() {
        $('#closed-bill-confirm-modal').modal('hide');
        pendingClosedBillId = null;
      });

      document.getElementById('closed-bill-confirm-btn').addEventListener('click', function() {
        $('#closed-bill-confirm-modal').modal('hide');
        if (pendingClosedBillId) {
          window.openLoadContinueTypeModal(pendingClosedBillId);
          pendingClosedBillId = null;
        }
      });

      document.addEventListener('click', function(event) {
        var saleCard = event.target.closest('[data-open-sale]');
        if (saleCard) {
          event.preventDefault();
            if (saleCard.getAttribute('data-open-mode') === 'open') {
              if (saleCard.getAttribute('data-need-identifier') === '1') {
                window.openLoadIdentifierModal(
                  saleCard.getAttribute('data-open-sale'),
                  0,
                  saleCard.getAttribute('data-bill-mode') || 'last'
                );
                return;
              }
              window.openLoadCounterModal(saleCard.getAttribute('data-open-sale'), 'open');
            } else {
              pendingClosedBillId = saleCard.getAttribute('data-open-sale');
              $('#closed-bill-confirm-modal').modal('show');
            }
            return;
          }
        var keyButton = event.target.closest('[data-key]');
        if (keyButton) {
          applyKey(keyButton.getAttribute('data-key'));
          return;
        }
        var mobileKeyButton = event.target.closest('[data-mobile-key]');
        if (mobileKeyButton) {
          applyKey(mobileKeyButton.getAttribute('data-mobile-key'));
          return;
        }
        var tabButton = event.target.closest('[data-open-tab]');
        if (tabButton) {
          activeTab = tabButton.getAttribute('data-open-tab') || 'new';
          document.querySelectorAll('[data-open-tab]').forEach(function(btn) {
            btn.classList.toggle('active', btn === tabButton);
          });
          syncTabUi();
          loadSalesList(searchInput.value.trim());
        }
      });

      searchInput.addEventListener('input', function() {
        if (mobileSearchInput) {
          mobileSearchInput.value = searchInput.value;
        }
        if (searchTimer) {
          clearTimeout(searchTimer);
        }
        searchTimer = setTimeout(function() {
          loadSalesList(searchInput.value.trim());
        }, 180);
      });

      if (mobileSearchInput) {
        mobileSearchInput.addEventListener('input', function() {
          setSearchValue(mobileSearchInput.value);
        });
      }

      document.getElementById('open-sale-search-btn').addEventListener('click', function() {
        loadSalesList(currentSearchValue());
      });

      document.getElementById('open-sale-clear-btn').addEventListener('click', function() {
        setSearchValue('');
        scanStatus.textContent = '';
        loadSalesList('');
      });

      if (mobileSearchInput) {
        document.getElementById('open-mobile-search-btn').addEventListener('click', function() {
          setSearchValue(searchInput.value.trim());
          $('#open-search-modal').modal('show');
          window.setTimeout(function() {
            mobileSearchInput.focus();
          }, 220);
        });

        document.getElementById('open-sale-search-btn-mobile').addEventListener('click', function() {
          loadSalesList(currentSearchValue());
          $('#open-search-modal').modal('hide');
        });

        document.getElementById('open-sale-clear-btn-mobile').addEventListener('click', function() {
          setSearchValue('');
          loadSalesList('');
        });
      }

      scanFile.addEventListener('change', function() {
        decodeScannedImage(this.files && this.files[0] ? this.files[0] : null);
        this.value = '';
      });

      loadOpenPageBootstrap(function() {
        document.querySelectorAll('[data-open-tab]').forEach(function(btn) {
          btn.classList.toggle('active', btn.getAttribute('data-open-tab') === activeTab);
        });
        syncTabUi();
        if (!bootstrapRendered) {
          loadSalesList('');
        }
      });
    })();
  </script>
</body>
</html>
