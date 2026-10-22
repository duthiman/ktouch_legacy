<!DOCTYPE html>
<html lang="en" id="whole">
<head>
<title>Staff Login </title>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" />
<link rel="stylesheet" href="assets/css/bootstrap.css"> 
<link rel="stylesheet" href="assets/css/form-elements.css">
<link rel="stylesheet" href="assets/css/style.css">

<script src="assets/js/jquery.js"></script>

<script>
window.onload = function () {
    document.getElementById("whole").style.backgroundImage = 'url("assets/img/bg/55.jpg")';
    document.getElementById("whole").style.backgroundRepeat="no-repeat" 
    document.getElementById("whole").style.backgroundSize="cover"
    function changeImage() {   
        var BackgroundImg=["assets/img/bg/u1.jpg",
        "assets/img/bg/22.jpg",
        "assets/img/bg/33.jpg",
        "assets/img/bg/44.jpg",
        "assets/img/bg/55.jpg"
        ]; 
        var i = Math.floor((Math.random() * 4));
        document.getElementById("whole").style.backgroundImage = 'url("'+ BackgroundImg[i] +'")'; 
        document.getElementById("whole").style.backgroundRepeat="no-repeat"
        document.getElementById("whole").style.backgroundSize="cover" 
    }
    window.setInterval(changeImage, 3000);
}
</script>
<style>
body { 
  background-color: transparent !important;
} 
.num,
button,
.btn {
  touch-action: manipulation;
}
.front-login-shell {
  min-height: 100vh;
  display: flex;
  align-items: center;
}
.cont{
    width: 390px;
    height:auto;
    border-radius: 10px; 
    margin-top: 10%;
    margin-left: auto;
    margin-right: -200px;
}
.front-login-card {
    background: rgba(255, 252, 245, 0.92);
    border: 1px solid rgba(124, 78, 32, 0.12);
    border-radius: 18px;
    box-shadow: 0 22px 48px rgba(0, 0, 0, 0.22);
    overflow: hidden;
}
@media (min-width: 768px) {
    .front-login-shell .row {
        width: 100%;
    }
    .front-login-shell .cont {
        margin-left: auto !important;
        margin-right: calc(5% - 200px) !important;
        float: none;
    }
}
.front-login-head {
    padding: 16px 18px 14px;
    background: linear-gradient(135deg, rgba(240, 108, 38, 0.94), rgba(98, 182, 71, 0.92));
    color: #fffdf6;
}
.front-login-head strong {
    display: block;
    font-size: 20px;
    font-weight: 900;
    text-transform: uppercase;
    letter-spacing: 0.04em;
}
.front-login-head span {
    display: block;
    margin-top: 4px;
    font-size: 12px;
    font-weight: 700;
}
.front-login-body {
    padding: 12px 10px 14px;
    display: flex;
    justify-content: center;
}
.front-login-input {
    width: 100%;
    margin: 0;
    height: 52px;
    border-radius: 14px;
    border: 1px solid #eadcbf;
    text-align: center;
    font-size: 22px;
    font-weight: 900;
    letter-spacing: 0.08em;
    background: #fff;
    color: #111 !important;
    -webkit-text-fill-color: #111 !important;
    caret-color: #f06c26;
}
.front-login-input:focus {
    border-color: #f06c26;
    box-shadow: 0 0 0 0.2rem rgba(240, 108, 38, 0.14);
    color: #f06c26 !important;
    -webkit-text-fill-color: #f06c26 !important;
}
.front-login-results {
    padding: 6px 10px 0;
}
.num-pad {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 8px;
    padding: 0 10px 10px;
    width: 100%;
    max-width: 340px;
    margin: 0 auto;
}
.num-pad .numpad-top-row {
    grid-column: span 3;
    display: grid;
    grid-template-columns: minmax(0, 1fr) 110px;
    gap: 8px;
    align-items: stretch;
}
.num-pad .span4.numpad-wide {
    grid-column: span 3;
}
.num-pad .span4 {
    width: auto !important;
    float: none !important;
    margin: 0 !important;
    padding: 0 !important;
}
.num,
.num-pad .btn {
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
.number {
    margin: 0 !important;
    font-size: 28px !important;
    line-height: 1 !important;
    font-weight: 900;
}
#clearLoginButton,
#backspaceLoginButton {
    background: #f7f1de !important;
    color: #111 !important;
}
#backspaceLoginButton {
    height: 52px !important;
    min-height: 52px !important;
}
#clearLoginButton {
    background: #f06c26 !important;
}
#submitLoginButton {
    background: #f06c26 !important;
    color: #111 !important;
    font-size: 14px !important;
    letter-spacing: 0.06em;
}

@media (max-width: 767.98px) {
    .front-login-shell {
        display: block;
        padding: 18px 0 22px;
    }
    .cont {
        width: 100%;
        max-width: 390px;
        margin: 0 auto;
        float: none;
    }
    .front-login-card {
        border-radius: 16px;
        margin: 0 8px;
    }
    .front-login-head {
        padding: 14px 14px 12px;
    }
    .front-login-head strong {
        font-size: 18px;
    }
    .front-login-head span {
        font-size: 11px;
    }
    .front-login-body {
        padding: 10px 8px 12px;
        display: block;
    }
    .front-login-input {
        width: 100% !important;
        margin: 0 !important;
        height: 48px;
        font-size: 20px;
        border-radius: 12px;
    }
    .front-login-results {
        padding: 4px 6px 0;
    }
    .num-pad {
        gap: 6px;
        padding: 0 6px 6px;
        max-width: 95%;
    }
    .num-pad .numpad-top-row {
        grid-template-columns: minmax(0, 1fr) 72px;
        gap: 6px;
    }
    .num,
    .num-pad .btn {
        min-height: 64px !important;
        height: 64px !important;
        border-radius: 12px !important;
    }
    #backspaceLoginButton {
        height: 48px !important;
        min-height: 48px !important;
    }
    .number {
        font-size: 26px !important;
    }
}
       
    </style>
</head>
<body class="bb">
   <!--  <header>
        <nav class="navbar navbar-fixed-top scrolling-navbar double-nav"> </nav>
     </header> -->
<div class="container front-login-shell">
    <div class="row"> 
        <div class="cont col-md-offset-8 col-sm-offset-2">
            <div class="front-login-card">
                <div class="front-login-head">
                    <strong>Staff Login</strong>
                    <span>Enter your passcode using the keypad below.</span>
                </div>
                <div class="front-login-body">
                <div class="row">
                    <div class="front-login-results" id="results"></div>
                    <div class="num-pad">
                        <div class="numpad-top-row">
                        <div>
                        <input type="password"  name="password" class="form-control front-login-input" id="telNumber" value="" inputmode="none" readonly>
                        </div>
                        <button type="button" id="backspaceLoginButton" name="count" class="btn" style="
                    background-color: #f7f1de !important;
                    padding: 0px;
                    font-weight: bold;
                    color:black;
                    height: 70px;
                    width:100%; 
                    border:2px solid #62b647; 
                    border-radius: 10px;"> &larr; </button>
                        </div>
                 <div class="span4">
                    <div class="num" >
                        <h2 class="number">7</h2>
                        <div class="txt" style="display:none">7</div>
                    </div>
                </div>
                <div class="span4">
                    <div class="num">
                        <h2 class="number">8</h2>
                        <div class="txt"  style="display:none">
                            8 
                        </div>
                    </div>
                </div>
                <div class="span4">
                    <div class="num"> 
                        <h2 class="number">9</h2>
                        <div class="txt" style="display:none">
                            9 
                        </div>
                        
                    </div>
                </div>
                <div class="span4">
                    <div class="num">
                        <h2 class="number">4</h2>
                        <div class="txt" style="display:none">
                            4 
                        </div>
                    </div>
                </div>
                <div class="span4">
                    <div class="num">
                        <h2 class="number">5</h2>
                        <div class="txt" style="display:none">
                            5
                        </div>
                    </div>
                </div>
                <div class="span4">
                    <div class="num">
                        <h2 class="number">6</h2>
                        <div class="txt" style="display:none">
                            6 
                        </div>
                    </div>
                </div>
                 <div class="span4">
                    <div class="num">
                        <h2 class="number">1</h2>
                         <div class="txt" style="display:none">
                            1
                        </div>
                    </div>
                </div>  
                <div class="span4">
                    <div class="num">
                        <h2 class="number">2</h2>
                        <div class="txt" style="display:none">
                            2 
                        </div>
                    </div>
                </div>
                <div class="span4">
                    <div class="num">
                        <h2 class="number">3</h2>
                        <div class="txt" style="display:none">3</div>
                    </div>
                </div>
                <div class="span4">
                    <button type="button" onclick="document.getElementById('telNumber').value='';" name="count" class="btn btn- " style="background-color: #f06c26 !important;
                    padding: 0px;
                    font-weight: bold;
                    color:black;
                    height: 70px;
                    width:70px; 
                    border:2px solid #62b647; 
                    border-radius: 10px;" id="clearLoginButton">CLEAR </button>
                
                </div>
                <div class="span4">
                    <div class="num">
                        <h2 class="number">0</h2>
                        <div class="txt" style="display:none">
                            0 
                        </div>
                    </div>
                </div>
               <div class="span4">
                    <div class="num">
                        <h2 class="number">.</h2>
                        <div class="txt" style="display:none">.</div>
                    </div>
               </div>
               <div class="span4 numpad-wide">
                    <button type="button" onclick="login()" id="submitLoginButton" name="count" class="btn" style="
                    background-color: #f06c26 !important;
                    padding: 0px;
                    font-weight: bold;
                    color:black;
                    height: 70px;
                    width:100%; 
                    border:2px solid #62b647; 
                    border-radius: 10px;"> LOGIN </button>
               </div>
            </div>
        </div>
      </div>
    </div>
  </div>
<script>
    $(document).ready(function () {
        $('.num').click(function () {
        var num = $(this);
        var text = $.trim(num.find('.txt').clone().children().remove().end().text());
        var telNumber = $('#telNumber');
        $(telNumber).val(telNumber.val() + text);
        });
        $('#backspaceLoginButton').click(function () {
        var telNumber = $('#telNumber');
        telNumber.val(telNumber.val().slice(0, -1));
        });
    });
    function login(){
        var password = $("#telNumber").val();
        $.post("kuingira.php", { password: password,},
            function(data) { 
                $('#results').html(data); 
            });
    }
</script>
</body>
</html>
