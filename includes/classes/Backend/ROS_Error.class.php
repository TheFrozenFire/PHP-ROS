<?php
class ROS_Error {
	private static $errorLog = array();

	public static function handleError($errno, $errstr, $errfile, $errline) {
		self::$errorLog[] = array('time'=>time(), 'no'=>$errno, 'str'=>$errstr, 'file'=>$errfile, 'line'=>$errline);
	}
	
	public static function getErrors() {
		return self::$errorLog;
	}
	
	public static function initialize() {
		set_error_handler(array('ROS_Error', 'handleError'));
	}
}
?>
