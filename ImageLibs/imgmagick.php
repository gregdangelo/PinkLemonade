<?
/*
I'm just a placeholer for now... eventually I'll be more
*/
class ImageLib_imgmagick extends ImageLibrary {
	public function load($image_path = '',$extension = ''){
		return false;
	}
	public function dimensions($image_path = ''){
		return false;
	}	
	public function create(){
		return false;
	}	
	public function destroy(){
		return false;
	}	
	public function copy($src,$dst){
		return false;
	}	
	public function is_pixel_transparent($img_resource,$x,$y){
		return false;
	}
}
?>