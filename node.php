<?
class Node {
	public $x;
	public $y;
	public $width;
	public $height;
	public $used;
	public $down;
	public $right;
	public $findcount=0;

	/*Constructor*/	
	public function __construct($x=0, $y=0, $width=0, $height=0, $used= false,$down= null, $right=null){
		$this->x = $x; //X coordinate.
		$this->y = $y; //Y coordinate.
		$this->width = $width; //Image width
		$this->height = $height; //Image height
		$this->used = $used; //Flag to determine if the node is used.
		$this->down = $down; //Node Class
		$this->right = $right; //Node Class
	
	}
	/*Find a node to allocate this image size (width, height).
	@param node Node to search in
	@param width Pixels to grow down (width).
	@param height Pixels to grow down (height). 
	*/
	public function find($node=null,$width=0,$height=0){
		$result = null;
		$this->findcount++;
		//var_dump($node);
		//echo "<br/><br/>";
		$msg = 'empty(false)';
		if($node->used){
			$msg = "used";
			//echo "find {$this->findcount}:u: ".$msg . "<br/>";
			$result =  $this->find($node->down, $width, $height);
			if(!$result){
				$result = $this->find($node->right, $width, $height);
			}
			//echo "AND result is&hellip;<br/>";
			//var_dump($result);
			//echo "<br/>";
		}elseif($node->width >= $width && $node->height >= $height){
			$msg="width check";
			$result = $node;
		}
		//echo "find {$this->findcount}: ".$msg . "<br/>";
		return $result;
	}
	/*
	Split the node to allocate a new one of this size.

	@param node Node to be splitted.
	@param width New node width.
	@param height New node height.
	*/
	public function split($node=null,$width=0,$height=0){
		
		$node->used = true;
		
		$node->down = new Node($node->x,$node->y + $height,$node->width,$node->height - $height);
		
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
		//echo "Growth ability:{$can_grow_down},{$can_grow_right},{$should_grow_down},{$should_grow_right}<br/>";
        if($should_grow_right){
        	//echo "should grow right<br/>";
            return $this->grow_right($width, $height);
        }elseif( $should_grow_down){
        	//echo "should grow down<br/>";
            return $this->grow_down($width, $height);
        }elseif( $can_grow_right){
        	//echo "can grow right<br/>";
            return $this->grow_right($width, $height);
        }elseif( $can_grow_down){
        	//echo "can grow down<br/>";
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
		//$this->x = $this->y = 0;
		$this->width += $width;
		$this->down = $that;
		$this->right = new Node($that->width,0,$width,$this->height);
		
		$node = $this->find($this,$width,$height);
		if($node){
			$this->split($node,$width,$height);
		}
		return null;
	}
	/*
	Grow the canvas to the down.

        @param width Pixels to grow down (width).
        @param height Pixels to grow down (height).
    */
	private function grow_down($width, $height){
		//echo "Down we grow {$width}, {$height}<br/>";
		$that = clone $this;
		$this->used=true;
		//$this->x = $this->y = 0;
		$this->height += $height;
		$this->right = $that;
		$this->down = new Node(0,$that->height,$this->width,$height);
		//print_r($this);
		//echo sizeof($that)."<br/><br/>";
		//echo "Finding from Grow down<br/>";
		$node = $this->find($this,$width,$height);
		//echo "DONE Finding from Grow down<br/>";
		if($node){
			//echo $node;
			//echo "node is an object? " .(is_object($node)?'yes':'no')."<br/>";
			//echo "grow down->splitsville<br/>";
			$this->split($node,$width,$height);
		}
		return null;
	}
}
?>