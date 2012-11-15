<?
/*
* Still too GD dependant
*
*/
class Image {
	/*Public Vars*/
	/*Private Vars*/
	private $name;
	private $sprite;
	private $filename;
	private $extension;
	private $class_name;
	private $path;
	private $width;
	private $height;
	private $file_modified;
	public $node; //shouldn't really be public
	private $crop_info = array(0,0,0,0,'x'=>0,'y'=>0,'x2'=>0,'y2'=>0);//this should end up as private
	private $padding_info; //Not used yet
	private $order = array('maxside','width','height','area');
	/*Static Vars*/
	public static $img_count = 0;
	public static $crop = false;
	private static $algorithm = 'maxside';
	private static $image_lib = null;

	/*
	Multiple Constructors
	Constructor (no args)	
	
	Constructor 
	@param name: Image name.
    @param sprite: Sprite instance for this image
    */
	public function __construct(){
		//We need to set out image library regardless of what else happens
		//hmmm think I need to rethink this a bit... would I want to set the library from the constructor?
		if(!self::$image_lib){
			self::setLib();			
		}
		$a = func_get_args(); 
        $i = func_num_args();
        if($i==2){
        	$f='__construct_sprite';
        	//second param needs to be passed as a reference
        	call_user_func_array(array($this,$f),array($a[0],&$a[1]));
        }
	}
	public function __construct_sprite($name,&$sprite){
		$this->name = $name;
		$this->sprite = $sprite;
		$extChar = strrpos($this->name,'.');
		$this->filename = substr($this->name,0,$extChar);
		$this->extension = substr($this->name,$extChar+1);
		$this->path = $this->sprite->getPath();
		$image_path = $this->path .'/'. $this->name;
		$this->file_modified = filemtime($image_path);
		$this->class_name = $this->_class_name();
		list($this->width,$this->height) = self::$image_lib->dimensions($image_path);
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
	public function getWidth(){
		return $this->width;
	}
	public function getHeight(){
		return $this->height;
	}
	public function getName(){
		return $this->name;
	}
	public function getClassName(){
		return $this->class_name;
	}
	public function getModifiedTime(){
		return $this->file_modified;
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
        	list($this->width,$this->height) = self::$image_lib->dimensions($image_path);
        }
        
        $width = $this->width-1;
        $height = $this->height-1;
        $maxx = $maxy = 0;
        $minx = $miny = 65000;//sys.maxint

		$img = self::$image_lib->load($this->path .'/'. $this->name,$this->extension);//Load our image, we're going to need to read it's pixels
		for($x=$width;$x>=0;$x--){
			for($y=$height;$y>=0;$y--){
				if($y > $miny && $y < $maxy && $maxx == $x){
					continue;
				}
				if(!self::$image_lib->is_pixel_transparent($img,$x,$y)){
					$minx = $x < $minx ? $x : $minx;
					$maxx = $x > $maxx ? $x : $maxx;
					$miny = $y < $miny ? $y : $miny;
					$maxy = $y > $maxy ? $y : $maxy;
				}
			}
		}
		self::$image_lib->destroy($img);//we only want the image loaded for as little as possible
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
		return self::$image_lib->load($this->path.'/'.$this->name,$this->extension);
	}
	/*
	* Load our library based on our ImageLibrary class
	* There's no way right now to easily extend this just by adding a driver class... this function call will need to be refactored... at some point
	*/
	private static function setLib(){
		//try ImageMagik - less of a memory hog
		if(function_exists("img_magick")){// just a placeholder
			//self::$image_lib = ImageLibrary::factory('imgmagick');
		}
		//try GD - Should be there
		if(function_exists("gd_info") && !self::$image_lib){
			self::$image_lib = ImageLibrary::factory('gd');
		}
		if(!self::$image_lib){
			throw new Exception("No Image Library found");
		}
	}
	public  function create($width = 0,$height = 0){
		$result = null;
		try{
			$result = self::$image_lib->create($width, $height);
		}catch(Exception $ex){
			error_log($ex);//Log our error
		}
		return $result;
	}
	public function destroy($resource){
		self::$image_lib->destroy($resource);
	}
	public function copy($src,$dst,$dst_x=0,$dst_y=0,$src_x =0, $src_y =0, $src_w =0 , $src_h = 0){
		return self::$image_lib->copy($src,$dst,$dst_x,$dst_y,$src_x , $src_y , $src_w  , $src_h );
	}

	public function save($resource,$path){
		return self::$image_lib->save($resource,$path);
	}
	/* Static Methods */
	//This might be able to shed it's STATIC nature
	public static function sidesort($a,$b){
		$result = 0;
		if(self::$algorithm == 'width'){
			$result = $a->width <= $b->width;
		}elseif(self::$algorithm == 'height'){
			$result = $a->height <= $b->height;
		}elseif(self::$algorithm == 'area'){
			$result = ($a->height * $a->width) <= ($b->height * $b->width);
		}else{ //maxside
			$result = ($a->height > $a->width ? $a->height : $a->width) <= ($b->height > $b->width ? $b->height : $b->width);
		}
		return $result;
	}
}
?>