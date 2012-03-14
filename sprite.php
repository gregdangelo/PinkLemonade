<?
/*
things to consider adding
filemtime  - to get the last modified time so we can cache that... we don't want to run the whole sprite creation if nothing has changed
*/
class Sprite {
	private $images;
	private $name;
	private $path;
	private $output_path;
	private $img_cache = array();//stores image file modified time
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
				$class_names = array();
				foreach($this->images as $image){
					$class_names[] = $image->getClassName();
					$this->set_image_cache($image);//double up on the loop
				}
				if(sizeof(array_unique($class_names)) != sizeof($this->images)){
					$dups = array();
					$check_dups = array_count_values($class_names);
					foreach($check_dups as $key=>$value){
						if($value > 1){
							$dups[] = $key;
						}
					}
					if(sizeof($dups)){
						throw new Exception("Error: Some images will have the same class name:".implode(', ',$dups));
					}
				}
				var_dump($this->img_cache);
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
		//we should be jumping out if we have no images
		if(!sizeof($this->images)){
			return false;
		}

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
        //$img = Image::create($width,$height); //wrap in a try catch
        $img_res = new Image();
        $img = $img_res->create($width,$height); //wrap in a try catch;
        foreach($this->images as $image){
			$imgsprite = $image->load();
			if($imgsprite){
				$dim = $image->getDimensions();
				if(Image::$crop){
					$img_res->copy( $img,$imgsprite, $image->x(), $image->y(),$dim[0], $dim[1], $dim[2], $dim[3]);
				}else{
					$img_res->copy( $img,$imgsprite, $image->x(), $image->y(),0, 0, $dim['width'], $dim['height']);
				}
			}
        }

        $r = imagepng($img,__DIR__.'/sprites/'.$this->name);
        
        //Clean up time
        $img_res->destroy($img);
    }
    /*
    * Create the CSS (or LESS maybe) file for this sprite.
    */
    public function save_css(){
		if(!sizeof($this->images)){
			return false;
		}
		
		$output_path = __DIR__.'/css';
		$filename = "test.css";
		$css_filename = $output_path.'/'.$filename;

		$fh = fopen($css_filename,'w+');//create a new file or overwrite our old file if it exists		
		
		$class_names = array();
		foreach($this->images as $image){
			$class_names[] = ".".$image->getClassName();
		}
		$class_names = implode(",\n",$class_names);
		$style = "%s{background-image:url('%s');background-repeat:no-repeat;}\n";
		fwrite($fh,sprintf($style,$class_names,$this->name));

		foreach($this->images as $image){
			$data = array(
				'image_class_name'=>$image->getClassName()
				,'top'=> $image->y() * ($image->y() ? -1 : 0) 
				,'left'=> $image->x() * ($image->x() ? -1 : 0)
				,'width'=> $image->getWidth()
				,'height'=> $image->getHeight()
			);
			$style = ".%s{background-position:%dpx %dpx;";
			$include_size = true;//may want to turn this on and off so we'll have to put this flag somewhere that makes sense
			if($include_size){
				$style .= "width:%dpx; height:%dpx;";
			}
			$style .= "}\n";
			fwrite($fh,sprintf($style,$data['image_class_name'],$data['top'],$data['left'],$data['width'],$data['height']));
		}

		fclose($fh);
    }
    
    /*
    File Caching methods
    -- We're not always going to want to run so we're going to create a cache file
    */
    private function sprite_changed(){
    	$result = true;
    	//Grab the timestamps of the files and compare them with the timestamps of the info_file
    	return $result;
    }
    private function remember_sprite_info(){
    	//Save file timestamps 
    }
    private function info_file(){
    	//figure out the name of the sprite_info file
    	//Directory + sprite_name + .sprite_info [extension]
    }
    private function timestamps(){
    	//find the timestamps of each image file into a timestamp array
    }
    //starting to think I should break these out into it's own class file (FileCache ?)
    private function set_image_cache($image = NULL){
    	$result = false;
    	if($image){
    		$this->img_cache[$image->getName()] = $image->getModifiedTime();
    	}
    	return $result;
    }
    private function load_image_cache(){
    	//read cache file
    }
    private function save_image_cache(){
    
    }
    private function compare_image_cache(){
    
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