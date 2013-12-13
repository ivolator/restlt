<?php
ini_set('display_errors', 'off');
$al = require __DIR__ . '/../../../autoload.php';
set_include_path(get_include_path().':' . __DIR__ . '/../lib:' . __DIR__.'/fixtures');
$al->setUseIncludePath(true);
