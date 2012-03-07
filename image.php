<?
/*
* Still too GD dependant
*
*/
class Image {
	private $name;
	private $sprite;
	private $filename;
	private $extension;
	private $class_name;
	private $path;
	private $width;
	private $height;
	public $node; //shouldn't really be public
	private $crop_info = array(0,0,0,0,'x'=>0,'y'=>0,'x2'=>0,'y2'=>0);//this should end up as private
	private $padding_info; //Not used yet
	private $order = array('maxside','width','height','area');
	public static $img_count = 0;
	public static $crop = true;
	private static $algorithm = 'maxside';
	private static $TRANSPARENT = 127;// for GD 127 is our 255...  it's in the PHP doc's but I haven't really looked at why
	private static $image_lib = null;

	/*
	Constructor
	@param name: Image name.
    @param sprite: Sprite instance for this image
    */
	public function __construct($name,&$sprite){
		if(!self::$image_lib){
			self::setLib();			
		}
		$this->name = $name;
		$this->sprite = $sprite;
		$extChar = strrpos($this->name,'.');
		$this->filename = substr($this->name,0,$extChar);
		$this->extension = substr($this->name,$extChar+1);
		$this->path = $this->sprite->getPath();
		$image_path = $this->path .'/'. $this->name;
		$this->class_name = $this->_class_name();
		list($this->width,$this->height) = getimagesize($image_path);
		if(self::$crop){
			$this->_crop();
		}
		self::$img_count++;
	}
	public static function images(){
		return self::$img_count;
	}
	public function x(){
		$result = 0;
		if($this->node){
			$result = $this->node->x();
		}
		return $result;
	}
	public function y(){
		$result = 0;
		if($this->node){
			$result = $this->node->y();
		}
		return $result;
	}
	public function getDimensions(){
		return array_merge($this->crop_info,array('width'=>$this->width,'height'=>$this->height));
	}
	/*
	Crop the image searching for the smallest possible bounding box
        without losing any non-transparent pixel.

        This crop is only used if the crop flag is set... there is no crop flag to set yet
	*/
	private function _crop(){		
		if(!$this->width || !$this->height){
        	$image_path =$this->sprite->path .'/'. $this->name;
        	list($this->width,$this->height) = getimagesize($image_path);
        }
        
        $width = $this->width-1;
        $height = $this->height-1;
        $maxx = $maxy = 0;
        $minx = $miny = 65000;//sys.maxint

		$img = $this->load();//Load our image, we're going to need to read it's pixels
		for($x=$width;$x>=0;$x--){
			for($y=$height;$y>=0;$y--){
				if($y > $miny && $y < $maxy && $maxx == $x){
					continue;
				}
				if(!$this->_is_transparent_pixel($img,$x,$y)){//the NOT is important
					$minx = $x < $minx ? $x : $minx;
					$maxx = $x > $maxx ? $x : $maxx;
					$miny = $y < $miny ? $y : $miny;
					$maxy = $y > $maxy ? $y : $maxy;
				}
			}
		}
		self::destroy($img);//we only want the image loaded for as little as possible
		//just prep the crop here
		$this->_prep_crop($minx, $miny, $maxx + 1, $maxy + 1);
	}
	private function _prep_crop($start_x,$start_y,$end_x,$end_y){
		//Without the +1 sprites sometimes overlap... there could be a more optimal solution but this is what I have thus far
		$this->width  = $end_x - $start_x +1;
		$this->height = $end_y - $start_y +1;
		$this->crop_info[0] = $this->crop_info['x'] = $start_x;
		$this->crop_info[1] = $this->crop_info['y'] = $start_y;
		$this->crop_info[2] = $this->crop_info['x2'] = $end_x;
		$this->crop_info[3] = $this->crop_info['y2'] = $end_y;
	}
	private function _class_name(){
/*
        """Return the CSS class name for this file.

        This CSS class name will have the following format:

        ``.[namespace]-[sprite_name]-[image_name]{ ... }``

        The image_name will only contain alphanumeric characters,
        ``-`` and ``_``. The default namespace is ``sprite``, but it could
        be overridden using the ``--namespace`` optional argument.


        * ``animals/cat.png`` CSS class will be ``.sprite-animals-cat``
        * ``animals/cow_20.png`` CSS class will be ``.sprite-animals-cow``
        """
        name = self.filename
        if not self.sprite.manager.config.ignore_filename_paddings:
            padding_info_name = '-'.join(self._padding_info)
            if padding_info_name:
                padding_info_name = '_%s' % padding_info_name
            name = name[:len(padding_info_name) * -1 or None]
        name = re.sub(r'[^\w\-_]', '', name)
        return '%s-%s' % (self.sprite.namespace, name)
*/
		$name = $this->filename;
		return sprintf("%s-%s",$this->sprite->namespace,$name); 
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
	private static function setLib(){
		//try ImageMagik - less of a memory hog
		if(function_exists("img_magick")){// just a placeholder
			self::$image_lib = 'imgmagick';
		}
		//try GD - Should be there
		if(function_exists("gd_info") && !self::$image_lib){
			self::$image_lib = 'gd';
		}
		if(!self::$image_lib){
			throw new Exception("No Image Library found");
		}
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
		//make it human readable
		$color_tran = imagecolorsforindex($img_resource, $color_index);
		//Returns an array red,green,blue,alpha
		return $color_tran;
	}
	private function _is_transparent_pixel($img_resource,$x,$y){
		$result = false;
		$info = $this->getpixel($img_resource,$x,$y);
		if($info['alpha'] == self::$TRANSPARENT){
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