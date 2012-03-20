<?
/*
PinkLemonade
*/

require_once('ImageLibrary.php');
require_once('image.php');
require_once('node.php');
require_once('spritecache.php');
require_once('sprite.php');

class PinkLemonade {
	//need to work on this to work with mutiple files/folders and diff configs for each
	private $sprites;
	private $css_path;
	private $css_file = 'style.css';
	
	public function __construct(){
		clearstatcache();//Clears out any cached stats
		$this->sprites = array();
		$this->css_path = __DIR__.'/css';
		return $this;
	}
	public function css_filename($string = ""){
		if($string){
			$this->css_file = $string;
		}
		return $this->css_file;
	}
	public function sprite($sprite_file,$images_dir,$css_file=''){
		$this->sprites[] = new Sprite($sprite_file,$images_dir);
		if($css_file){
			$sprite_css[sizeof($this->sprites)-1] = $css_file;
		}
	}
	public function save(){
		foreach($this->sprites as $sprite){
			/*Save our images and then our css*/
			$sprite->save_image();
			//I may OR may not want to append or create the new css file
			$sprite->save_css();
		}
	}
	public function viewTrees(){
		foreach($this->sprites as $sprite){
			$sprite->printTree();
		}
	}
}
?>