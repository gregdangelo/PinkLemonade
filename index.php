<?php
include('PinkLemonade.php');
?>
<html>
<head>
	<title>PinkLemonade</title>
	<link href="#" rel="stylesheet" type="text/css"/>
</head>
<body style="background-color:#ccc;">
<a href="#Red" onclick="makeRed();return false;">Red</a>
<?
	$pk = new PinkLemonade;
	$pk->css_filename('test.css');
	//$pk->sprite('sp_test.png',__DIR__.'/images');
	$pk->sprite('sp_test.png',__DIR__.'/images/twn');
	
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
<img src="images/twn/app-android.png"/>
<script>
function makeRed(){
	var elm = document.getElementsByTagName('body');
	elm = elm[0];
	elm.style.backgroundColor = "#F00";
	var elm = document.getElementsByTagName('a');
	elm = elm[0];
	elm.href = "#Grey";
	elm.onclick = makeGrey;
	elm.innerHTML = "Grey";
	return false;
}
function makeGrey(){
	var elm = document.getElementsByTagName('body');
	elm = elm[0];
	elm.style.backgroundColor = "#CCC";
	var elm = document.getElementsByTagName('a');
	elm = elm[0];
	elm.href = "#White";
	elm.onclick = makeWhite;
	elm.innerHTML = "White";
	return false;
}
function makeWhite(){
	var elm = document.getElementsByTagName('body');
	elm = elm[0];
	elm.style.backgroundColor = "#FFF";
	var elm = document.getElementsByTagName('a');
	elm = elm[0];
	elm.href = "#Red";
	elm.onclick = makeRed;
	elm.innerHTML = "Red";
	return false;
}
</script>
</body>
</html>