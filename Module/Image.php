<?php
class RAD_Module_Image {
	const LOAD_FILE = 'image/load=file';
	
	private $type;
	private $image;
	private $imageData;
	private $originalWidth;
	private $originalHeight;
	private $originalAspectRatio;
	private $originalImgae;
	private $width;
	private $height;
	private $aspectRatio;
	private $mimeType;
	
	private $maintainAspectRatio = false;
	private $maxWidth = null;
	private $maxHeight = null;
	private $center = false; /* ATTEMPT TO CENTER IN THE MIDDLE OF MAX WIDTH/HEIGHT */
	private $drawBackground = false;
	private $backgroundColor = array(
		'red' => 255,
		'green' => 255,
		'blue' => 255
	);
	public function load($data, $type) {
		switch ($type) {
			case self::LOAD_FILE:
			default:
				$this->imageData = getimagesize($data);
				
				$width = $this->imageData[0];
				$height = $this->imageData[1];
				$aspectRatio = $width / $height;
				$mimeType = $this->imageData['mime'];
				
				$this->originalWidth = $this->width = $width;
				$this->originalHeight = $this->height = $height;
				$this->originalAspectRatio = $this->aspectRatio = $aspectRatio;
				$this->mimeType = trim($mimeType);
				
				$this->loadImageFromFile($data);
				break;
		}
		
		return $this;
	}
	
	private function loadImageFromFile($data) {
		switch ($this->getType()) {
			case 'jpg':
				$this->image = $this->originalImage = imagecreatefromjpeg($data);
				break;
			case 'png':
				$this->image = $this->originalImage = imagecreatefrompng($data);
				break;
			case 'gif':
				$this->image = $this->originalImage = imagecreatefromgif($data);
				break;
		}
	}
	
	public function getMimeType() {
		return $this->mimeType;
	}
	public function getType() {
		switch ($this->mimeType) {
			case 'image/png':
				$type = 'png';
				break;
			case 'image/gif':
				$type = 'gif';
				break;
			case 'image/jpeg':
				$type = 'jpg';
				break;
		}
		return $type;
	}
	
	public function setObjectData($data) {
		foreach ($data as $key => $value) {
			$this->$key = $value;
		}
	}
	
	public function copy() {
		$image = RAD::getModule('image');
		$image->setObjectData(get_object_vars($this));
		return $image;
	}
	
	public function maintainAspectRatio($value = true) {
		$this->maintainAspectRatio = $value;
	}
	public function center($value = true) {
		$this->center = $value;
	}
	
	public function setMaxWidth($width) {
		$this->maxWidth = $width;
	}
	public function setMaxHeight($height) {
		$this->maxHeight = $height;
	}
	
	public function setBackgroundColor($red, $green, $blue) {
		$this->backgroundColor['red'] = $red;
		$this->backgroundColor['green'] = $green;
		$this->backgroundColor['blue'] = $blue;
	}
	public function drawBackground($value = true) {
		if ($value) {
			$this->drawBackground = false;
		} else {
			$this->drawBackground = true;
		}
	}
	
	public function save($file = null, $quality = 75) {
		if ($this->maxWidth && $this->width > $this->maxWidth) {
			$this->width = $this->maxWidth;
			
			if ($this->maintainAspectRatio) {
				$this->height = $this->width / $this->originalAspectRatio;
			}
		}
		
		if ($this->maxHeight && $this->height > $this->maxHeight) {
			$this->height = $this->maxHeight;
			
			if ($this->maintainAspectRatio) {
				$this->width = $this->height * $this->originalAspectRatio;
			}
		}
	
		if ($this->center && $this->drawBackground) {
			$this->image = imagecreatetruecolor(50, 50);
			$bgColor = imagecolorallocate($this->image, $this->backgroundColor['red'], $this->backgroundColor['green'], $this->backgroundColor['blue']);
			imagefill($this->image, 0, 0, $bgColor);
			
			$x = ($this->maxWidth - $this->width);
			if ($x) {
				$x = $x / 2;
			}
			$x = round($x);
			
			$y = ($this->maxHeight - $this->height);
			if ($y) {
				$y = $y / 2;
			}
			
			imagecopyresampled($this->image, $this->originalImage, $x, $y, 0, 0, $this->width, $this->height, $this->originalWidth, $this->originalHeight);
		} else {
			$this->image = imagecreatetruecolor($this->width, $this->height);
			
			if ($this->getType() == 'png') {
				imagealphablending($this->image, false);
				imagesavealpha($this->image, true);
				$transparent = imagecolorallocatealpha($this->image, 255, 255, 255, 127);
				imagefilledrectangle($this->image, 0, 0, $this->width, $this->height, $transparent);
			}
			if ($this->getType() == 'gif') {
				$trnprt_indx = imagecolortransparent($this->originalImage);
				if ($trnprt_indx >= 0 && $trnprt_indx < 255) {
					$trnprt_color = imagecolorsforindex($this->originalImage, $trnprt_indx);
					$trnprt_indx = imagecolorallocate($this->image, $trnprt_color['red'], $trnprt_color['green'], $trnprt_color['blue']);
					imagefill($this->image, 0, 0, $trnprt_indx);
					imagecolortransparent($this->image, $trnprt_indx);
				}
			}

			imagecopyresampled($this->image, $this->originalImage, 0, 0, 0, 0, $this->width, $this->height, $this->originalWidth, $this->originalHeight);
		}
		
		if ($file) {
			switch ($this->getType()) {
				case 'jpg':
					imagejpeg($this->image, $file, $quality);
					break;
				case 'png':
					imagepng($this->image, $file, 9);
					break;
				case 'gif':
					imagegif($this->image, $file, $quality);
					break;
			}
		}
	}
	
	public function render($output = true, $quality = 75) {
		ob_start();
		switch ($this->getType()) {
			case 'jpg':		
				imagejpeg($this->image, null, $quality);
				break;
			case 'png':
				imagepng($this->image, null, 9);
				break;
			case 'gif':
				imagegif($this->image, null, $quality);
				break;
		}
		$content = ob_get_contents();
		ob_end_clean();
		
		if ($output) {
			echo $content;
		} else {
			return $content;
		}
	}
}
?>
