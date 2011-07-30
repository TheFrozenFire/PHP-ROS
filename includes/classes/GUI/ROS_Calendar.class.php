<?php
class ROS_Calendar {
	private $widget;
	
	public function __construct() {
		$this->widget = new GtkCalendar();
	}
	
	public function getWidget() {
		return $this->widget;
	}
	
	public function prepare() {
	
	}
}
?>
