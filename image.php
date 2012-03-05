<?
class Image {
	public $name;
	public $sprite;
	public $filename;
	public $extension;
	public $path;
	public $width;
	public $height;
	public $padding;
	public $node;
	private $crop_info = [0,0];//x,y
	private $order = array('maxside','width','height','area');
	public static $img_count = 0;
	public static $algorithm = 'maxside';

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
	/*
	Crop the image searching for the smallest possible bounding box
        without losing any non-transparent pixel.

        This crop is only used if the crop flag is set.
	*/
	private function crop(){		
		if(!$this->width || !$this->height){
        	//width, height = self.image.size
        	$image_path =$this->sprite->path .'/'. $this->name;
        	list($this->width,$this->height) = getimagesize($image_path);
        }
        $img = $this->load();
        $width = $this->width;
        $height = $this->height;
        $maxx = $maxy = 0;
        $minx = $miny = 65000;//sys.maxint

		for($x=0;$x<=$width;$x++){
			for($y=0;$y<=$height;$y++){
				if($y > $miny && $y < $maxy && $maxx == $x){
					continue;
				}
				if($this->_is_transparent_pixel($img,$x,$y)){
					$minx = $x < $minx ? $x : $minx;
					$maxx = $x > $maxx ? $x : $maxx;
					$miny = $y < $miny ? $y : $miny;
					$maxy = $y < $maxy ? $y : $maxy;
				}
			}
		}
		
		//self.image = self.image.crop((minx, miny, maxx + 1, maxy + 1))
		//should I actually crop it here or just save the values and crop once I create the sprite 
		$this->_crop_save($minx, $miny, $maxx + 1, $maxy + 1);
		self::destroy($img);
	}
	private function _prep_crop($start_x,$start_y,$end_x,$end_y){
		//$this->width = ;
	}
	private function _crop_save($start_x,$start_y,$end_x,$end_y,$img_resource = null){
		$cleanup = false;
		if(!$img_resource){
			$img = $this->load();
			$cleanup = true;
		}
		$dest = imagecreatetruecolor($end_x-$start_x, $end_y-$start_y);
		//We want to copy part of our image into a new image, but not necessarily overwrite the original
		// Copy
		imagecopy($dest, $img, 0, 0, $start_x,$start_y,$end_x,$end_y);
		//$r = imagepng($img,__DIR__.'/images/'."{$this->filename}.crop.{$this->extension}");
		$r = $this->_create_from($img,__DIR__.'/images/'."{$this->filename}.crop.{$this->extension}");
		if($r){
			//then let's update our image info
		}
		if($cleanup){
			self::destroy($img);
		}
	}
	public function load(){
		$result = null;
		$image_path = $this->path.'/'.$this->name;
		switch($this->extension){
			case 'png':
				$result = imagecreatefrompng($image_path);
				break;
			case 'jpg':
			case 'jpeg':
				$result = imagecreatefromjpeg($image_path);
				break;
			case 'gif':
				$result = imagecreatefromgif($image_path);
				break;
		}
		return $result;
	}
	private function setLib(){
		//try ImageMagik - less of a memory hog
		//try GD - Should be there
		//else fail + throw expection
	}
	/* Static Methods */
	public static function create($width = 0,$height = 0){
		$result = null;
		$result = imagecreatetruecolor($width, $height);
		if(!$result){
			throw new Exception('Could not create Image');
		}
		return $result;
	}
	private function _create_from($img_resource,$filename){
		$result = null;
		switch($this->extension){
			case 'png':
				$result = imagepng($image_path);
				break;
			case 'jpg':
			case 'jpeg':
				$result = imagejpeg($image_path);
				break;
			case 'gif':
				$result = imagegif($image_path);
				break;
		}
		return $result;
	}
	private function getpixel($img_resource,$x,$y){
		$color_index = imagecolorat($img_resource, $x, $y);
		// make it human readable
		$color_tran = imagecolorsforindex($img_resource, $color_index);
		//$color_tran['alpha'];//red,green,blue
		return $color_tran;
	}
	private function _is_transparent_pixel($img_resource,$x,$y){
		$result = false;
		$info = $this->getpixel($img_resource,$x,$y);
		if($info['alpha'] == '255'){
			$result = true;
		}
		return $result;
	}
	public static function destroy($resource){
		imagedestroy($resource);
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