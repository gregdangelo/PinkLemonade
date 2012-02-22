<?php
include('functions.php');
include('PinkLemonade.php');
?>
<html>
<head>
	<title>PinkLemonade</title>
	<link href="#" rel="stylesheet" type="text/css"/>
</head>
<body>
<?
	//test out our functions here
	/*
	if(setDirectory('/images')){
		echo "Okidokie let's do our thing";
		findFiles();
		createImage();
	}else{
		echo "uh nothing worky";
	}
	*/
	/*
	$sp = new Sprite('sp.png',__DIR__.'/images');
	$sp->save_image();
	*/
	$pk = new PinkLemonade;
	$pk->sprite('sp.png',__DIR__.'/images');
	$pk->viewTrees();
	$pk->save();
?>

</body>
</html>