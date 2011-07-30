<?php
class ROS_MySQL implements ROS_DataSourceType {
	private $dbConnection;
	private $dbHost;
	private $dbName;
	private $dbUser;
	private $dbPass;

	public function setCredentials($dbhost, $dbname, $dbuser, $dbpass) {
		$this->dbHost = $dbhost;
		$this->dbName = $dbname;
		$this->dbUser = $dbuser;
		$this->dbPass = $dbpass;
		
		if(!$this->dbConnection && !$this->createConnection()) {
			trigger_error('Connection could not be created');
			return FALSE;
		}
		
		return TRUE;
	}

	public function createConnection() {
		if(!($this->dbHost || $this->dbName || $this->dbUser || $this->dbPass)) {
			trigger_error('Database credentials not set');
			return FALSE;
		}
		try {
			$this->dbConnection=new PDO("mysql:host={$this->dbHost};dbname={$this->dbName}", $this->dbUser, $this->dbPass);
		} catch (PDOException $e) {
			trigger_error($e);
			return FALSE;
		}
		return TRUE;
	}

	public function ping() {
		if($this->dbConnection==NULL) return FALSE;
		if($this->dbConnection->getAttribute(PDO::ATTR_CONNECTION_STATUS)) return TRUE; else return FALSE;
	}

	public function dbQuery($query) {
		if(!$this->ping()) return FALSE;
		$results=$this->dbConnection->query($query);
		return $results->fetchAll(PDO::FETCH_ASSOC);
	}

	public function dbParamQuery($query, $params=NULL) {
		if(!$this->ping()) return FALSE;
		$trans=$this->dbConnection->prepare($query);
		if(!is_null($params) && is_array($params)) {
			foreach($params as $key=>$value) {
				if(!is_int($key)) {
					$trans->bindValue($key, $value);
				} else {
					$trans->bindValue(($key+1), $value);
				}
			}
		}
		if(!$trans->execute()) return FALSE;
		return $trans->fetchAll(PDO::FETCH_ASSOC);
	}
	
	public function getClientInfo($id) {
		$results = $this->dbParamQuery(
			'SELECT * FROM `clients` WHERE `id` = :id',
			array(':id'=>$id)
		);
		
		$results = $results[0];
		
		return $results;
	}
	
	public function getClientDetails() {
		return $this->dbQuery('SELECT * FROM `client_details`');
	}
	
	public function searchClients($last_name, $first_name) {
		$results = $this->dbParamQuery(
			'SELECT * FROM `clients` WHERE `last_name` LIKE :last_name AND `first_name` LIKE :first_name',
			array(':last_name'=>"%$last_name%", ':first_name'=>"%$first_name%")
		);
		
		return $results;
	}
	
	public function addClient($last_name, $first_name) {
		$this->dbParamQuery(
			'INSERT INTO `clients` (`last_name`, `first_name`) VALUES (:last_name, :first_name)',
			array(':last_name'=>$last_name, ':first_name'=>$first_name)
		);
	}
	
	public function modifyClient($id, $last_name, $first_name) {
		$this->dbParamQuery(
			'UPDATE `clients` SET `last_name` = :last_name, `first_name` = :first_name WHERE `id` = :id',
			array(
				':id'=>$id,
				':last_name'=>$last_name,
				':first_name'=>$first_name
			)
		);
	}
	
	public function getReservationInfo($id) {
		$results = $this->dbParamQuery(
			'SELECT * FROM `reservations` WHERE `id` = :id',
			array(':id'=>$id)
		);
		
		return $results[0];
	}
	
	public function getReservationComments($id) {
		$results = $this->dbParamQuery(
			'SELECT `id`, `comment` FROM `reservation_comments` WHERE `res_id` = :id',
			array(':id'=>$id)
		);
		
		return $results;
	}
	
	public function commentAdd($id, $comment) {
		$this->dbParamQuery(
			'INSERT INTO `reservation_comments` (`res_id`, `comment`) VALUES (:id, :comment)',
			array(
				':id'=>$id,
				':comment'=>$comment
			)
		);
	}
	
	public function commentRemove($id) {
		$this->dbParamQuery(
			'DELETE FROM `reservation_comments` WHERE `id` = :id',
			array(
				':id'=>$id
			)
		);
	}
	
	public function getReservationsForDate($time) {
		$results = $this->dbParamQuery(
			'SELECT a.`id`, a.`checked_in`, a.`checked_out`, b.`last_name`, b.`first_name`, c.`room_name` FROM `reservations` a, `clients` b, `rooms` c WHERE a.`open_date` <= :time AND a.`close_date` >= :time AND b.`id` = a.`client_id` AND c.`id` = a.`room_id`',
			array(':time'=>$time)
		);
		
		return $results;
	}
	
	public function getReservationsForMonth($start, $end) {
		return $this->dbParamQuery(
			'SELECT * FROM `reservations` WHERE `open_date` >= :open_date AND `close_date` <= :close_date',
			array(
				':open_date'=>$start,
				':close_date'=>$end
			)
		);
	}
	
	public function addReservation($startDate, $endDate, $unit, $client) {
		$this->dbParamQuery(
			'INSERT INTO `reservations` (`open_date`, `close_date`, `made_date`, `client_id`, `checked_in`, `checked_out`, `room_id`) VALUES (:open_date, :close_date, :made_date, :client_id, FALSE, FALSE, :room_id)',
			array(
				':open_date'=>$startDate,
				':close_date'=>$endDate,
				':made_date'=>time(),
				':client_id'=>$client,
				':room_id'=>$unit
			)
		);
	}
	
	public function modifyReservation($id, $startDate, $endDate, $unit, $client) {
		$this->dbParamQuery(
			'UPDATE `reservations` SET `open_date` = :open_date, `close_date` = :close_date, `room_id` = :unit, `client_id` = :client WHERE `id` = :id',
			array(
				':id'=>$id,
				':open_date'=>$startDate,
				':close_date'=>$endDate,
				':unit'=>$unit,
				':client'=>$client
			)
		);
	}
	
	public function checkIn($id) {
		$this->dbParamQuery(
			'UPDATE `reservations` SET `checked_in` = 1 WHERE `id` = :id',
			array(':id'=>$id)
		);
	}
	
	public function checkOut($id) {
		$this->dbParamQuery(
			'UPDATE `reservations` SET `checked_out` = 1 WHERE `id` = :id',
			array(':id'=>$id)
		);
	}
	
	public function getAvailableRoomsForDates($startDate, $endDate) {
		$results = $this->dbParamQuery(
			'SELECT `rooms`.* FROM `rooms` WHERE (SELECT count(*) FROM `reservations` WHERE `reservations`.`room_id` = `rooms`.`id` AND `reservations`.`open_date` <= :open_date AND `reservations`.`close_date` >= :close_date) = 0',
			array(
				':open_date'=>$startDate,
				':close_date'=>$endDate
			)
		);
		
		return $results;
	}
	
	public function getRoomList() {
		$results = $this->dbQuery('SELECT * FROM `rooms`');
		return $results;
	}
	
	public function getRoomInfo($id) {
		$results = $this->dbParamQuery(
			'SELECT * FROM `rooms` WHERE `id` = :room_id',
			array(':room_id'=>$id)
		);
		
		return $results[0];
	}
	
	public function roomAdd($name) {
		$this->dbParamQuery(
			'INSERT INTO `rooms` (`room_name`) VALUES (:name)',
			array(':name'=>$name)
		);
	}
	
	public function roomRemove($id) {
		$this->dbParamQuery(
			'DELETE FROM `rooms` WHERE `id` = :id',
			array(':id'=>$id)
		);
	}
	
	public function roomModify($id, $name) {
		$this->dbParamQuery(
			'UPDATE `rooms` SET `room_name` = :name WHERE `id` = :id',
			array(':id'=>$id, ':name'=>$name)
		);
	}
	
	public function getPaymentMethods($enabled = TRUE) {
		if($enabled) $result = $this->dbQuery('SELECT * FROM `payment_methods` WHERE `enabled` = TRUE');
		else $result = $this->dbQuery('SELECT * FROM `payment_methods`');
		
		return $result;
	}
	
	public function getMethodInfo($id) {
		return $this->dbParamQuery(
			'SELECT * FROM `payment_methods` WHERE `id` = :id',
			array(':id'=>$id)
		);
	}
}

ROS_DataSource::setSource($tmp = new ROS_MySQL());
?>
