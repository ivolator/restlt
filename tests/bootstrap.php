<?php
ini_set('display_errors', 'off');
$al = @include __DIR__ . '/../../vendor/autoload.php';
if(!$al){
	$al = @include __DIR__ . '/../../../autoload.php';
}

//$al->add ('fixtures\\',__FILE__ . '/');

require_once 'RestLiteTest.php';
set_include_path(get_include_path().':' . __DIR__ . '/../lib:' . __DIR__.'/fixtures');
$al->setUseIncludePath(true);
