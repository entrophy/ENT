<?php
class ENT_Template_Header {
	private $title;
	private $description;
	private $keywords;
	private $image;
	
	private $facebook_title;
	private $facebook_description;
	private $facebook_image;
	public function setTitle($title) {
		$this->title = $title;
	}
	public function getTitle() {
		return $this->title;
	}
	
	public function setDescription($description) {
		$this->description = $description;
	}
	public function getDescription() {
		return $this->description;
	}
	public function setKeywords($keywords) {
		$this->keywords = $keywords;
	}
	public function getKeywords() {
		return $this->keywords;
	}
	
	public function setImage($image) {
		$this->image = $image;
	}
	public function getImage() {
		return $this->image;
	}
	
	public function setFacebookTitle($title) {
		$this->facebook_title = $title;
	}
	public function getFacebookTitle() {
		return $this->facebook_title ? : $this->title;
	}
	
	public function setFacebookDescription($description) {
		$this->facebook_description = $description;
	}
	public function getFacebookDescription() {
		return $this->facebook_description ? : $this->description;
	}
	
	public function setFacebookImage($image) {
		$this->facebook_image = $image;
	}
	public function getFacebookImage() {
		return $this->facebook_image ? : $this->image;
	}
}
?>