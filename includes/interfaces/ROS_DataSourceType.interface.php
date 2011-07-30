<?php
interface ROS_DataSourceType {
	public function setCredentials($dbhost, $dbname, $dbuser, $dbpass);

	public function getClientInfo($id);
	public function searchClients($last_name, $first_name);
	public function addClient($last_name, $first_name);
	public function getReservationInfo($id);
	public function getReservationComments($id);
	public function getReservationsForDate($time);
	public function addReservation($startDate, $endDate, $unit, $client);
	public function modifyReservation($id, $startDate, $endDate, $unit, $client);
	public function getAvailableRoomsForDates($startDate, $endDate);
	public function getRoomList();
	public function getRoomInfo($id);
	public function roomAdd($name);
	public function roomRemove($id);
	public function roomModify($id, $name);
	public function getPaymentMethods($enabled = TRUE);
	public function getMethodInfo($id);
}
?>
