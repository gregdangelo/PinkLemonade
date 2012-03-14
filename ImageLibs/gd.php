<?
class ImageLib_gd extends ImageLibrary {
	private $transparent_value = 127;// for GD 127 is our 255...  it's in the PHP doc's but I haven't really looked at why
	
	public function load($image_path = '',$extension = ''){
		$result = false;
		switch($extension){
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
	public function dimensions($image_path = ''){
		$result = false;
		try{
			$result = getimagesize($image_path);
		}catch(Exception $ex){
			error_log($ex);
		}
		return $result;
	}	
	public function create($width=0, $height=0){
		$result = false;
		if($width && $height){
			$result = imagecreatetruecolor($width, $height);
		}
		if(!$result){
			throw new Exception('Could not create Image');
		}
		return $result;
	}	
	public function destroy($resource){
		imagedestroy($resource);
	}	
	public function copy($src,$dst,$dst_x=0,$dst_y=0,$src_x =0, $src_y =0, $src_w =0 , $src_h = 0){
		return false;
	}	
	public function is_pixel_transparent($image_resource,$x=0,$y=0){
		$result = false;
		$info = $this->getpixel($img_resource,$x,$y);
		if($info['alpha'] == $transparent_value){
			$result = true;
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
}
?>