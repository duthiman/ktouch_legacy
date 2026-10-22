<?php
// Screen width is a browser value, so the correct PHP view is chosen in JavaScript.
// The query string is preserved when forwarding to the selected layout.
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Loading K-TOUCH Dashboard</title>
  <style>html,body{height:100%;margin:0;background:#f6f7f5;font:14px Arial,sans-serif;color:#188426}.loader{height:100%;display:grid;place-items:center}.loader div{text-align:center}.spinner{width:34px;height:34px;margin:0 auto 12px;border:3px solid #dfe5e2;border-top-color:#188426;border-radius:50%;animation:spin .7s linear infinite}@keyframes spin{to{transform:rotate(360deg)}}</style>
</head>
<body>
  <div class="loader"><div><div class="spinner"></div><b>Loading dashboard…</b></div></div>
  <script>
  (function(){
    const BREAKPOINT = 760;
    const query = window.location.search;
    function target(){ return window.innerWidth <= BREAKPOINT ? 'mobile.php' : 'desktop.php'; }
    function openCorrectView(){
      const next = target() + query;
      if (!window.location.pathname.endsWith('/' + target())) window.location.replace(next);
    }
    openCorrectView();
  })();
  </script>
</body>
</html>

