<?
//Let's look at using something like YAML to make our life easier...
class SpriteCache {
	private $path;
	private $filename;
	private $sprite_name;
	private static $cache = array();
	
	public function __construct($spritename = null, $filepath = ''){
		$filepath = trim($filepath);
		if($filepath && $spritename){
			//Let's make sure we don't have duplicate sprite names
			if(isset(self::$cache[$spritename])){
				$tmpname = $sprite_name;
				//put a hard break on the for loop so that we can only run 50 iterations MAX... which should be more than enough
				for($x=0;$x<50;$x++){
					if(!isset(self::$cache[$tmpname."_{$x}"])){
						$sprite_name .= "_{$x}";
						break;
					}
				}
			}
			$this->$sprite_name = $spritename;
			//Initialize our cache var
			self::$cache[$this->$sprite_name] = array();
			$this->load($filepath,$cache[$this->$sprite_name]);
		}
	}
	public function load($filepath = '', &$cache_key){
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
		$result = null;
		if($key){
			
		}
		return $result;	
	}
	public function get($key = ''){
		$result = null;
		if($key){
			$result = self::$cache[$this->sprite_name][$key];
		}
		return $result;
	}
}
?>