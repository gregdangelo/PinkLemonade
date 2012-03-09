<?
class SpriteCache {
	private $path;
	private $filename;
	private $sprite_name;
	
	public function __construct($filepath = ''){
		$filepath = trim($filepath);
		if($filepath){
			$this->load($filepath);
		}
	}
	public function load($filepath = ''){
		$result = false;
		$filepath = trim($filepath);
		if($filepath){
			/*
			fopen();
			fread();
			fclose();
			*/
		}
		return $result;
	}
	public function set($key = '',$value = ''){
	
	}
}
?>