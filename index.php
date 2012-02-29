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
	$pk = new PinkLemonade;
	$pk->sprite('sp.png',__DIR__.'/images');
	$nc = Node::nodes();
	$ic = Image::images();
	$sc = Sprite::sprites();
	$d = $nc-$ic;
	echo "There are ".$nc." Node(s) counted<br/>";
	echo "There are ".$ic." Image(s) counted<br/>";
	echo "There are ".$sc." Sprite(s) counted<br/>";
	echo "There is a difference of  ".$d." Nodes to Images<br/>";
	$pk->viewTrees();
	$pk->save();
?>
</body>
</html>