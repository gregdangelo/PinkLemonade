<?
class Sprite {
	private $images;
	private $name;
	public $path;
	public $output_path;

	public function __construct($name='',$path=''){
		$this->images = array();
		$this->name = $name;
		$this->path = $path;
		$this->process();
	}
	private function gather_images(){
		//d(is_dir($this->path) ? 'yes':'no');
		//echo '<br/>';
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
				//Sort our Images
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
			if(!sizeof($this->images)){
				throw new Exception('No IMAGES');
			}
			if( sizeof($this->images) ){
				$w = $this->images[0]->width;
				$h = $this->images[0]->height;
				//echo "{$w}:{$h}<br/>";
				$root = new Node(0,0,$w,$h);
				$i=0;
				//Loop all over the images creating a binary tree
				foreach($this->images as $image){
					//echo "<h3>Iteration ".($i+1)."</h3>";
					//echo "{$image->name}:{$image->width},{$image->height}<br/>";
					$node = $root->find($root, $image->width, $image->height);
					//var_dump($node);
					//echo "<br/><br/>";
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
    	//echo "saving time<br/>";
    	$this->output_path = __DIR__.'/images';
    	// Search for the max x and y (Necessary to generate the canvas).
        $width = $height = 0;
        
        foreach($this->images as $image){
			$x = $image->x() + $image->width;
			$y = $image->y() + $image->height;
            $width  = ($width < $x) ? $x :$width;
            $height = ($height < $y) ? $y : $height;
        }
        $width = 218;$height = 207;
        $img = imagecreatetruecolor($height, $width) or die("Cannot Initialize new GD image stream");
        foreach($this->images as $image){
			$imgsprite = imagecreatefrompng($image->path.'/'.$image->name);
			imagecopy( $img,$imgsprite, $image->x(), $image->y(),0, 0, $image->width, $image->height);
        }
        //echo "Save File<br/>";
        $img_name = 'test.png';
        $r = imagepng($img,__DIR__.'/sprites/'.$img_name);
        //echo ($r ? 'created':'oops');
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