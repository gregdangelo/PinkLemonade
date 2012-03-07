<?
class Node {
	private $x;
	private $y;
	private $width;
	private $height;
	private $used;
	private $down;
	private $right;
	private $findcount=0;
	public static $node_count = 0;

	/*Constructor*/	
	public function __construct($x=0, $y=0, $width=0, $height=0, $used= false,$down= null, $right=null){
		$this->x = $x; //X coordinate.
		$this->y = $y; //Y coordinate.
		$this->width = $width; //Image width
		$this->height = $height; //Image height
		$this->used = $used; //Flag to determine if the node is used.
		$this->down = $down; //Node Class
		$this->right = $right; //Node Class
		self::$node_count++;
	
	}
	public static function nodes(){
		return self::$node_count;
	}
	
	public function x(){ return $this->x ; }
	public function y(){ return $this->y ; }
	
	/*Find a node to allocate this image size (width, height).
	@param node Node to search in
	@param width Pixels to grow down (width).
	@param height Pixels to grow down (height). 
	*/
	public function find($node=null,$width=0,$height=0){
		$result = null;
		$this->findcount++;

		if($node->used){
			$result =  $this->find($node->down, $width, $height);
			if(!$result){
				$result = $this->find($node->right, $width, $height);
			}
		}elseif($node->width >= $width && $node->height >= $height){
			$result = $node;
		}
		return $result;
	}
	/*
	Split the node to allocate a new one of this size.

	@param node Node to be splitted.
	@param width New node width.
	@param height New node height.
	*/
	public function split($node=null,$width=0,$height=0){
		
		$node->used  = true;
		$node->down  = new Node($node->x,$node->y + $height,$node->width,$node->height - $height);
		$node->right = new Node($node->x + $width,$node->y,$node->width - $width,$height);
		
		return $node;
	}
	/*
		Grow the canvas to the most appropriate direction.
		@param width Pixels to grow down (width).
        @param height Pixels to grow down (height).
	*/
	public function grow($width,$height){
		$can_grow_down = $width <= $this->width;
		$can_grow_right = $height <= $this->height;
		
		$should_grow_down = $can_grow_down && $this->width  >= ($this->height + $height);
		$should_grow_right = $can_grow_right && $this->height >= ($this->width + $width);
        if($should_grow_right){
            return $this->grow_right($width, $height);
        }elseif( $should_grow_down){
            return $this->grow_down($width, $height);
        }elseif( $can_grow_right){
            return $this->grow_right($width, $height);
        }elseif( $can_grow_down){
            return $this->grow_down($width, $height);
		}
        return null;
	}
	/*
	Grow the canvas to the right.

        @param width Pixels to grow down (width).
        @param height Pixels to grow down (height).
    */
	private function grow_right($width, $height){
		$that = clone $this;
		$this->used=true;
		$this->x = $this->y = 0;
		$this->width += $width;
		$this->down = $that;
		$this->right = new Node($that->width,0,$width,$this->height);
		$node = $this->find($this,$width,$height);
		if($node){
			return $this->split($node,$width,$height);
		}
		return null;
	}
	/*
	Grow the canvas to the down.

        @param width Pixels to grow down (width).
        @param height Pixels to grow down (height).
    */
	private function grow_down($width, $height){
		$that = clone $this;
		$this->used=true;
		$this->x = $this->y = 0;
		$this->height += $height;
		$this->right = $that;
		$this->down = new Node(0,$that->height,$this->width,$height);
		$node = $this->find($this,$width,$height);
		if($node){
			return $this->split($node,$width,$height);
		}
		return null;
	}
}
?>