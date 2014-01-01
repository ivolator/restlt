<?php
ini_set('display_errors', 'off');
$al = @include __DIR__ . '/../vendor/autoload.php';
if(!$al){
	$al = @include __DIR__ . '/../../../autoload.php';
}
$al->add ('fixtures\\',__FILE__ . '/');

$al->setUseIncludePath(true);
