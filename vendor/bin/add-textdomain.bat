@ECHO OFF
setlocal DISABLEDELAYEDEXPANSION
SET BIN_TARGET=%~dp0/../varunsridharan/wp-cli-textdomain/bin/add-textdomain.bat
SET COMPOSER_RUNTIME_BIN_DIR=%~dp0
call "%BIN_TARGET%" %*
