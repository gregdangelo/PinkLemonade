<?
abstract class ImageLibrary{
    public static function factory($type){
    	$type = strtolower($type);
    	//make sure out
        if (include_once 'ImageLibs/' . $type . '.php') {
            $classname = 'ImageLib_' . $type;
            return new $classname;
        } else {
        	//Let's give a nice error
            throw new Exception(file_exists('ImageLibs/')?'Image Library {$type} not found':'Cannot open directory ImageLibs');
        }
    }
    abstract protected function load($image_path = '',$extension = '');
    abstract protected function dimensions($image_path = '');
    
    /*How do you make these abstract and static?*/
    abstract protected function create($width=0,$height=0);
    abstract protected function destroy($image_resource);
    /*
    * Copy part of an image into another
    */
    abstract protected function copy($src,$dst,$dst_x=0,$dst_y=0,$src_x =0, $src_y =0, $src_w =0 , $src_h = 0);
    /*
    * Check if a particular pixel is transparent
    */
    abstract protected function is_pixel_transparent($image_resource,$x=0,$y=0);
}
?>