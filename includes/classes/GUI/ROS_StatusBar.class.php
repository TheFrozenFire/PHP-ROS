<?php
class ROS_StatusBar {
	private $widget;
	
	public function __construct() {
		$this->widget = new GtkStatusBar();
	}
	
	public function getWidget() {
		return $this->widget;
	}
	
	public function prepare() {
	
	}
}
