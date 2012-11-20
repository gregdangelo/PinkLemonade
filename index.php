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
	
	
	
	$fileName = __DIR__.'/images/logo.png';
	list($width, $height) = getimagesize($fileName);

	$newFilename = __DIR__.'/sprites/sp_alpha.png';
	list($newwidth, $newheight) = array(100,35);
	$thumb = imagecreatetruecolor($newwidth, $newheight);
	imagealphablending($thumb, false);
	imagesavealpha($thumb, true);  
	
	$source = imagecreatefrompng($fileName);
	imagealphablending($source, true);
	
	imagecopyresampled($thumb, $source, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
	
	imagepng($thumb,$newFilename);
?>
<br/>
<img src="sprites/sp_test.png"/>
<img src="images/logo.png"/>
<img src="sprites/sp_alpha.png"/>
</body>
</html>