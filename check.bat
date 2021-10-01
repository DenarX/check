@echo off
chcp 866 >nul
set time=60
:loop
curl http://checkin/?c
ping 127.0.0.1 -n %time% >nul
Goto :loop