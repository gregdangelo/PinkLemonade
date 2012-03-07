<?
class Sprite {
	private $images;
	private $name;
	private $path;
	private $output_path;
	public $namespace = 'sprite';
	public  static $sprite_count = 0;

	public function __construct($name='',$path=''){
		$this->images = array();
		$this->name = $name;
		$this->path = $path;
		self::$sprite_count++;
		$this->process();
	}
	public static function sprites(){
		return self::$sprite_count;
	}
	public function getPath(){
		return $this->path;
	}
	private function gather_images(){
		try{
			if (!is_dir($this->path)) {
				throw new Exception('Path provides is not a valid directory');		
			}
			try{
				$dh = opendir($this->path);
			}catch(Exception $ex){
				throw new Exception('Cannot open directory');
			}
			if($dh){
				while (($file = readdir($dh)) !== false) {
					if(filetype($this->path.'/' . $file) == 'file'){
						$ext = substr($file,strrpos($file,'.')+1);
						switch($ext){
							//Any other image types could go here
							case 'png':
							case 'gif':
							case 'jpg':
							case 'jpeg':
								$this->images[] = new Image($file,$this);
								break;
						}
					}
				}
				closedir($dh);
				//Sort our Images... gotta work on that function name though
				usort($this->images,"Image::sidesort");
				//foreach($this->images as $image){
/*
# Check if there are duplicate class names
        class_names = [i.class_name for i in images]
        if len(set(class_names)) != len(images):
            dup = [i for i in images if class_names.count(i.class_name) > 1]
            raise MultipleImagesWithSameNameError(dup)

*/
				//}
			}
		}catch(Exception $ex){
			 echo 'Caught exception: ',  $ex->getMessage(), "\n";
		}
		
	}
	/*
	Process a sprite path searching for all the images and then
    allocate all of them in the most appropriate position.
	*/
	private function process(){
		try{
			if(!sizeof($this->images)){
				$this->gather_images();
			}
			//Make sure we have some images
			if(!sizeof($this->images)){
				throw new Exception('No IMAGES');
			}
			if( sizeof($this->images) ){
				$dim = $this->images[0]->getDimensions();
				$w = $dim['width'];
				$h = $dim['height'];
				unset($dim);
				$root = new Node(0,0,$w,$h);
				$i=0;
				//Loop all over the images creating a binary tree
				foreach($this->images as $image){
					$dim = $image->getDimensions();
					$node = $root->find($root, $dim['width'], $dim['height']);
					if($node){
						$image->node = $root->split($node, $dim['width'], $dim['height']);
					}else{
						$image->node = $root->grow($dim['width'], $dim['height']);
					}
					$i++;
				}
			}
		}catch(Exception $ex){
			 echo 'Caught exception: ',  $ex->getMessage(), "\n";
		}
	}
	/*Create the image file for this sprite.*/
    public function save_image(){

    	$this->output_path = __DIR__.'/images';
    	// Search for the max x and y (Necessary to generate the canvas).
        $width = $height = 0;

        //Find the height and width to use for our image
        foreach($this->images as $image){
        	$dim = $image->getDimensions();
			$x = $image->x() + $dim['width'];
			$y = $image->y() + $dim['height'];
            $width  = ($width < $x) ? $x :$width;
            $height = ($height < $y) ? $y : $height;
        }
        
        //Will want to use allow for using ImageMagik too eventually... ok you can do work on that in Image Class now
        $img = Image::create($width,$height); //wrap in a try catch
        foreach($this->images as $image){
			$imgsprite = $image->load();
			if($imgsprite){
				$dim = $image->getDimensions();
				if(Image::$crop){
					imagecopy( $img,$imgsprite, $image->x(), $image->y(),$dim[0], $dim[1], $dim[2], $dim[3]);
				}else{
					imagecopy( $img,$imgsprite, $image->x(), $image->y(),0, 0, $dim['width'], $dim['height']);
				}
			}
        }

        $img_name = 'test.png';//Really? lol, maybe I should use the name I passed in
        $r = imagepng($img,__DIR__.'/sprites/'.$this->name);
        
        //Clean up time
        Image::destroy($img);
    }
    public function save_css(){
/*
        """Create the CSS or LESS file for this sprite."""
        format = 'less' if self.config.less else 'css'
        self.manager.log("Creating '%s' %s file..." % (self.name, format))

        output_path = self.manager.output_path('css')
        filename = '%s.%s' % (self.filename, format)
        css_filename = os.path.join(output_path, filename)

        # Fix css urls on Windows
        css_filename = '/'.join(css_filename.split('\\'))

        css_file = open(css_filename, 'w')

        # get all the class names and join them
        class_names = ['.%s' % i.class_name for i in self.images]
        class_names = ',\n'.join(class_names)

        # create an unique style for all the sprites for less bloat
        style = "%s{background-image:url('%s');background-repeat:no-repeat;}\n"
        css_file.write(style % (class_names, self.image_url))

        for image in self.images:
            data = {'image_class_name': image.class_name,
                    'top': image.node.y * -1 if image.node.y else 0,
                    'left': image.node.x * -1 if image.node.x else 0,
                    'width': image.width,
                    'height': image.height}

            style = (".%(image_class_name)s{"
                     "background-position:%(left)ipx %(top)ipx;")

            if self.config.size:
                # if it's required add the image size to the sprite
                style += "width:%(width)spx; height:%(height)spx;"

            style += "}\n"

            css_file.write(style % data)

        css_file.close()

*/
    }
    /*
    * Simply print the node trees for out images
    *
    */
    public function printTree(){
    	echo "TREE START<br/><pre>";
    	sizeof($this->images);
    	foreach($this->images as $image){
    		echo $image->name.'<br/>';
    		if($image->node){
    			print_r($image->node);
    		}else{
    			$dim = $image->getDimensions();
    			echo sprintf("x:%d y:%d width:%d height:%d<br/>",$image->x(), $image->y(), $dim['width'], $dim['height']);
    		}
    	}
    	echo "</pre><br/>TREE END";
    }
}
?>