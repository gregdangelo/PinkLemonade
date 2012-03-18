<?php
include('PinkLemonade.php');
?>
<html>
<head>
	<title>PinkLemonade</title>
	<link href="#" rel="stylesheet" type="text/css"/>
</head>
<body>
<?
	$pk = new PinkLemonade;
	$pk->css_filename('test.css');
	$pk->sprite('sp_test.png',__DIR__.'/images');
	echo __DIR__;
	//$pk->viewTrees();
	$pk->save();
	
?>
</body>
</html>