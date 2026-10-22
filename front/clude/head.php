<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$filename = "../../inc/connection.php";
if (file_exists("inc/connection.php")){
    include_once('inc/connection.php');
    $url="";
}elseif (file_exists("../inc/connection.php")){
    include_once('../inc/connection.php');
    $url="../";
}elseif (file_exists($filename)){
    include_once('../../inc/connection.php');
    $url="../../";
}elseif (file_exists("../../../inc/connection.php")){
    include_once('../../../inc/connection.php');
    $url="../../../";
}else{
    throw new RuntimeException('Unable to load inc/connection.php');
}

if (!defined('KT_LIGHTWEIGHT_PAGE')) {
    $filename = "../../inc/gen_funcs.php";
    if (file_exists("inc/gen_funcs.php")){
        include_once('inc/gen_funcs.php');
    }elseif (file_exists("../inc/gen_funcs.php")){
        include_once('../inc/gen_funcs.php');
    }elseif (file_exists($filename)){
        include_once('../../inc/gen_funcs.php');
    }elseif (file_exists("../../../inc/gen_funcs.php")){
        include_once('../../../inc/gen_funcs.php');
    }else{
        throw new RuntimeException('Unable to load inc/gen_funcs.php');
    }
    $frontFuncsPath = realpath('funcs.php');
    if ($frontFuncsPath && file_exists($frontFuncsPath)) {
        include_once($frontFuncsPath);
    }
}
?>
<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8"> <![endif]-->
<!--[if !IE]><!-->
<html lang="en">
<!--<![endif]-->
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
<meta http-equiv="x-ua-compatible" content="ie=edge" />
<title>K-TOUCH</title>
<!-- ================== BEGIN BASE CSS STYLE ================== --> 
<link href="<?php echo $url; ?>assets/plugins/jquery-ui/jquery-ui.min.css" rel="stylesheet" />
<link href="<?php echo $url; ?>assets/plugins/bootstrap/4.0.0/css/bootstrap.min.css" rel="stylesheet" /> 
<!-- Consolidated desktop / mobile CSS -->
<link href="<?php echo $url; ?>assets/css/default/desktop.css" rel="stylesheet" media="screen and (min-width: 768px)" />
<link href="<?php echo $url; ?>assets/css/default/mobile.css" rel="stylesheet" media="screen and (max-width: 767px)" />
<!-- ================== END BASE CSS STYLE ================== -->
</head>
<style type="text/css">
	body {overflow-x: hidden;}
  .kt-session-overlay {
    position: fixed;
    inset: 0;
    z-index: 99999;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 20px;
    background: rgba(34, 18, 10, 0.58);
    backdrop-filter: blur(4px);
  }
  .kt-session-overlay.is-visible {
    display: flex;
  }
  .kt-session-card {
    width: min(100%, 420px);
    border-radius: 22px;
    overflow: hidden;
    background: #fffaf3;
    box-shadow: 0 24px 50px rgba(48, 28, 12, 0.26);
    border: 1px solid rgba(139, 89, 36, 0.14);
  }
  .kt-session-head {
    padding: 16px 18px;
    background: linear-gradient(135deg, #7e2014 0%, #b94828 52%, #e7a33a 100%);
    color: #fff8ea;
  }
  .kt-session-head h4 {
    margin: 0;
    font-size: 20px;
    font-weight: 900;
    text-transform: uppercase;
    letter-spacing: .04em;
  }
  .kt-session-body {
    padding: 18px;
    color: #5a3b1b;
  }
  .kt-session-body p {
    margin: 0 0 12px;
    font-size: 14px;
    font-weight: 700;
    line-height: 1.5;
  }
  .kt-session-countdown {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 56px;
    min-height: 56px;
    border-radius: 999px;
    background: #fff;
    border: 2px solid #efcf9e;
    color: #8c3b14;
    font-size: 24px;
    font-weight: 900;
    box-shadow: inset 0 0 0 1px rgba(229, 163, 58, 0.18);
  }
  .kt-session-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 14px;
    flex-wrap: wrap;
  }
  .kt-session-btn {
    min-height: 44px;
    padding: 0 16px;
    border: 0;
    border-radius: 12px;
    font-size: 13px;
    font-weight: 900;
    text-transform: uppercase;
    cursor: pointer;
  }
  .kt-session-btn.primary {
    background: #a2311d;
    color: #fff;
  }
  @media (max-width: 575px) {
    .kt-session-actions {
      justify-content: stretch;
    }
    .kt-session-btn {
      width: 100%;
    }
  }
</style>
<div class="kt-session-overlay" id="kt_session_overlay" aria-hidden="true">
  <div class="kt-session-card" role="dialog" aria-modal="true" aria-labelledby="kt_session_title">
    <div class="kt-session-head">
      <h4 id="kt_session_title">Session Expired</h4>
    </div>
    <div class="kt-session-body">
      <p>Your session has expired. You will be logged out automatically in <span class="kt-session-countdown" id="kt_session_countdown">5</span> seconds.</p>
      <p>You can also log out now using the button below.</p>
      <div class="kt-session-actions">
        <button type="button" class="kt-session-btn primary" id="kt_session_logout_btn">Log Out Now</button>
      </div>
    </div>
  </div>
</div>
<script type="text/javascript">
  (function() {
    var sessionOverlay = document.getElementById('kt_session_overlay');
    var sessionCountdown = document.getElementById('kt_session_countdown');
    var sessionLogoutBtn = document.getElementById('kt_session_logout_btn');
    var checkTimer = null;
    var logoutTimer = null;
    var remainingSeconds = 5;
    var modalVisible = false;

    function appRootPath() {
      var path = window.location.pathname || '';
      var frontIndex = path.indexOf('/front/');
      if (frontIndex !== -1) {
        return path.slice(0, frontIndex + '/front'.length);
      }
      return '';
    }

    function logoutUrl() {
      return appRootPath() + '/log_out.php';
    }

    function sessionStatusUrl() {
      return appRootPath() + '/session_status.php';
    }

    function goToLogout() {
      window.location.replace(logoutUrl());
    }

    function showSessionExpiredModal() {
      if (modalVisible || !sessionOverlay) {
        return;
      }
      modalVisible = true;
      remainingSeconds = 5;
      sessionCountdown.textContent = String(remainingSeconds);
      sessionOverlay.classList.add('is-visible');
      sessionOverlay.setAttribute('aria-hidden', 'false');
      logoutTimer = window.setInterval(function() {
        remainingSeconds -= 1;
        sessionCountdown.textContent = String(Math.max(remainingSeconds, 0));
        if (remainingSeconds <= 0) {
          window.clearInterval(logoutTimer);
          goToLogout();
        }
      }, 1000);
    }

    function checkSessionStatus() {
      if (modalVisible) {
        return;
      }
      fetch(sessionStatusUrl(), {
        method: 'GET',
        credentials: 'same-origin',
        headers: { 'Accept': 'application/json' },
        cache: 'no-store'
      })
      .then(function(response) {
        return response.ok ? response.json() : { active: true };
      })
      .then(function(data) {
        if (!data || !data.active) {
          showSessionExpiredModal();
        }
      })
      .catch(function() {
        // Ignore transient network issues and keep the page usable.
      });
    }

    if (sessionLogoutBtn) {
      sessionLogoutBtn.addEventListener('click', goToLogout);
    }

    document.addEventListener('visibilitychange', function() {
      if (!document.hidden) {
        checkSessionStatus();
      }
    });

    window.addEventListener('focus', checkSessionStatus);
    checkSessionStatus();
    checkTimer = window.setInterval(checkSessionStatus, 30000);
  })();
</script>
