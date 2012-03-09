<?

/*
PinkLemonade
*/

require_once('image.php');
require_once('node.php');
require_once('spritecache.php');
require_once('sprite.php');

class PinkLemonade {
	private $sprites;
	private $css_path;
	private $css_file = 'style.css';
	
	public function __construct(){
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
	public function sprite($sprite_file,$images_dir){
		$this->sprites[] = new Sprite($sprite_file,$images_dir);
	}
	public function save(){
		foreach($this->sprites as $sprite){
			/*Save our images and then our css*/
			$sprite->save_image();
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