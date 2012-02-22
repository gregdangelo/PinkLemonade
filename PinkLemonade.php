<?
/*
PinkLemonade
*/
//namespace PinkLemonade;
require_once('image.php');
require_once('node.php');
require_once('sprite.php');

class PinkLemonade {
	private $sprites;
	public function __construct(){
		$this->sprites = array();
		return $this;
	}
	public function sprite($sprite_file,$images_dir){
		$this->sprites[] = new Sprite($sprite_file,$images_dir);
	}
	public function save(){
		foreach($this->sprites as $sprite){
			$sprite->save_image();
		}
	}
	public function viewTrees(){
		foreach($this->sprites as $sprite){
			$sprite->printTree();
		}
	}
}
?>