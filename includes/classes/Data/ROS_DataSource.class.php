<?php
class ROS_DataSource {
	private static $source;

	public static function initialize($path, $host, $database, $username, $password) {
		include($path);
		
		self::$source->setCredentials($host, $database, $username, $password);
	}
	
	public static function setSource($source) {
		self::$source = $source;
	}
	
	public static function getClientInfo($id) {
		return self::$source->getClientInfo($id);
	}
	
	public static function getClientDetails() {
		return self::$source->getClientDetails();
	}
	
	public static function searchClients($last_name, $first_name) {
		return self::$source->searchClients($last_name, $first_name);
	}
	
	public static function addClient($last_name, $first_name) {
		self::$source->addClient($last_name, $first_name);
	}
	
	public static function modifyClient($id, $last_name, $first_name) {
		self::$source->modifyClient($id, $last_name, $first_name);
	}
	
	public static function getReservationInfo($id) {
		return self::$source->getReservationInfo($id);
	}
	
	public static function getReservationComments($id) {
		return self::$source->getReservationComments($id);
	}
	
	public static function getReservationsForDate($time) {
		return self::$source->getReservationsForDate($time);
	}
	
	public static function getReservationsForMonth($month, $year) {
		$start = mktime(0, 0, 0, $month, 1, $year);
		$end = mktime(0, 0, 0, $month, date('t', $start), $year);
		
		return self::$source->getReservationsForMonth($start, $end);
	}
	
	public static function addReservation($startDate, $endDate, $unit, $client) {
		self::$source->addReservation($startDate, $endDate, $unit, $client);
	}
	
	public static function modifyReservation($id, $startDate, $endDate, $unit, $client) {
		self::$source->modifyReservation($id, $startDate, $endDate, $unit, $client);
	}
	
	public static function checkIn($id) {
		self::$source->checkIn($id);
	}
	
	public static function checkOut($id) {
		self::$source->checkOut($id);
	}
	
	public static function commentAdd($id, $comment) {
		self::$source->commentAdd($id, $comment);
	}
	
	public static function commentRemove($id) {
		self::$source->commentRemove($id);
	}
	
	public static function getAvailableRoomsForDates($startDate, $endDate) {
		return self::$source->getAvailableRoomsForDates($startDate, $endDate);
	}
	
	public static function getRoomList() {
		return self::$source->getRoomList();
	}
	
	public static function getRoomInfo($id) {
		return self::$source->getRoomInfo($id);
	}
	
	public static function roomAdd($name) {
		self::$source->roomAdd($name);
	}
	
	public static function roomRemove($id) {
		self::$source->roomRemove($id);
	}
	
	public static function roomModify($id, $name) {
		self::$source->roomModify($id, $name);
	}
	
	public static function getPaymentMethods($enabled = TRUE) {
		return self::$source->getPaymentMethods($enabled);
	}
	
	public static function getMethodInfo($id) {
		return self::$source->getMethodInfo($id);
	}
}
?>
