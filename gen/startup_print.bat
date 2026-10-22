@echo off
REM ============================================================
REM  K-TOUCH Print Server – Auto Startup Script
REM  Add this to Windows Task Scheduler to automatically open
REM  the print server tab when a user logs in.
REM
REM  Task Scheduler Settings:
REM    Trigger  : At log on (for all users or specific user)
REM    Action   : Start a program
REM    Program  : (path to this .bat file)
REM               OR use msedge.exe directly with arguments below
REM ============================================================

REM Try Microsoft Edge first (installed on all Windows 10/11)
SET PS_URL=http://localhost/clients/ktouch/gpints/front/chaba/index.php

REM --- Microsoft Edge ---
SET EDGE="C:\Program Files (x86)\Microsoft\Edge\Application\msedge.exe"
IF EXIST %EDGE% (
    START "" %EDGE% --new-window "%PS_URL%"
    GOTO :EOF
)

REM --- Google Chrome fallback ---
SET CHROME="C:\Program Files\Google\Chrome\Application\chrome.exe"
IF EXIST %CHROME% (
    START "" %CHROME% --new-window "%PS_URL%"
    GOTO :EOF
)

REM --- Default browser fallback ---
START "" "%PS_URL%"

:EOF
