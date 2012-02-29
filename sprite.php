<?
class Sprite {
	private $images;
	private $name;
	public $path;
	public $output_path;
	public static $sprite_count = 0;

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
				$w = $this->images[0]->width;
				$h = $this->images[0]->height;
				$root = new Node(0,0,$w,$h);
				//echo sprintf("Root Node => x: %d y: %d w: %d h: %d <br/>",0,0,$w,$h);
				//echo sprintf("Root Node => x: %d y: %d w: %d h: %d <br/>",$root->x,$root->y,$root->width,$root->height);
				$i=0;
				//Loop all over the images creating a binary tree
				foreach($this->images as $image){
					//echo "<h3>Iteration ".($i+1)."</h3>";
					//echo "<hr/>{$image->name}:&nbsp;&nbsp;{$image->width},{$image->height}<br/>";
					$node = $root->find($root, $image->width, $image->height);
					if($node){
						//echo "splitting {$i}<br/>";
						$image->node = $root->split($node, $image->width, $image->height);
					}else{
						//echo "growing {$i}<br/>";
						$image->node = $root->grow($image->width, $image->height);
					}
					$i++;
				}
				//echo "Height:".$root->height." - Width:".$root->width."<br/>";
			}

			//$this->save_image();
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
			$x = $image->x() + $image->width;
			$y = $image->y() + $image->height;
            $width  = ($width < $x) ? $x :$width;
            $height = ($height < $y) ? $y : $height;
            //echo sprintf("<br/>%s=> x: %d y: %d w: %d h: %d <br/>",$image->name,$image->x(),$image->y(),$image->width,$image->height);

        }
        //When done tesing it would be smart to remove this kind of thing
        //$width = 218;$height = 207;
        
        //Will want to use allow for using ImageMagik too eventually
        $img = imagecreatetruecolor($height, $width) or die("Cannot Initialize new GD image stream");
        foreach($this->images as $image){
			//Handle our available extensions
			switch($image->extension){
				case 'png':
					$imgsprite = imagecreatefrompng($image->path.'/'.$image->name);
					break;
				case 'jpg':
				case 'jpeg':
					$imgsprite = imagecreatefromjpeg($image->path.'/'.$image->name);
					break;
				case 'gif':
					$imgsprite = imagecreatefromgif($image->path.'/'.$image->name);
					break;
			}
			
			imagecopy( $img,$imgsprite, $image->x(), $image->y(),0, 0, $image->width, $image->height);
        }


        $img_name = 'test.png';//Really? lol, maybe I should use the name I passed in
        $r = imagepng($img,__DIR__.'/sprites/'.$img_name);
        
        //Clean up time
        imagedestroy($img);
    }
    public function printTree(){
    	echo "TREE START<br/><pre>";
    	sizeof($this->images);
    	foreach($this->images as $image){
    		echo $image->name.'<br/>';
    		if($image->node){
    			print_r($image->node);
    		}else{
    			echo sprintf("x:%d y:%d width:%d height:%d<br/>",$image->x(), $image->y(), $image->width, $image->height);
    		}
    	}
    	echo "</pre><br/>TREE END";
    }
}
?>