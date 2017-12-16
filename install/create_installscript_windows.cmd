@echo off
::When editing this file please make sure to also update create_installscript_linux
copy install.sql install_full.sql
for /F "tokens=*" %%f in ('dir /S /b update.sql') do (
        echo Appending "%%f".
		type "%%f". >> install_full.sql
)
echo Installscript succesfully generated.
pause