<?
class Image {
	public $name;
	public $sprite;
	public $filename;
	public $extension;
	public $path;
	public $width;
	public $height;
	public static $img_count = 0;
	public $node;
	public static $algorithm = 'maxside';
	private $order = array('maxside','width','height','area');

	/*
	Constructor
	@param name: Image name.
    @param sprite: Sprite instance for this image
    */
	public function __construct($name,&$sprite){
		$this->name = $name;
		$this->sprite = $sprite;
		$extChar = strrpos($this->name,'.');
		$this->filename = substr($this->name,0,$extChar);
		$this->extension = substr($this->name,$extChar+1);
		$this->path = $this->sprite->path;
		$image_path = $this->sprite->path .'/'. $this->name;
		list($this->width,$this->height) = getimagesize($image_path);
		self::$img_count++;
	}
	public static function images(){
		return self::$img_count;
	}
	public function x(){
		$result = 0;
		if($this->node){
			$result = $this->node->x;
		}
		return $result;
	}
	public function y(){
		$result = 0;
		if($this->node){
			$result = $this->node->y;
		}
		return $result;
	}
	private function crop(){
	
	}
	public static function sidesort($a,$b){
		$result = 0;
		if(self::$algorithm == 'width'){
			$result = $a->width <= $b->width;
		}elseif(self::$algorithm == 'height'){
			$result = $a->height <= $b->height;
		}elseif(self::$algorithm == 'area'){
			$result = ($a->height * $a->width) <= ($b->height * $b->width);
		}else{
			$result = ($a->height > $a->width ? $a->height : $a->width) <= ($b->height > $b->width ? $b->height : $b->width);
		}
		return $result;
	}
}
?>