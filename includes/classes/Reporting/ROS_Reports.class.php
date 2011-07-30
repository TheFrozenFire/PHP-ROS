<?php
class ROS_Reports {
	public static function selectReport($parent, $arguments) {
		$reports = ROS_DataSource::getReports($arguments['type']);
	}
}
?>
