@ECHO OFF
SET BIN_TARGET=%~dp0/../vendor/leafo/scssphp/bin/pscss
php "%BIN_TARGET%" %*
