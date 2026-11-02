@echo off
setlocal
powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0connect-hp.ps1" %*
endlocal

