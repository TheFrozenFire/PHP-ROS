<?php
chdir(dirname(__FILE__));
date_default_timezone_set('America/Vancouver');
define('PROGRAM_NAME', 'PHP Reservation Operating System');
define('PROGRAM_VERSION', 'Pre-Alpha');

if(!class_exists('gtk')) {
	die("The GTK extension is not loaded. Please install it and add it to your local php.ini.");
}

require_once('includes/classes/Backend/ROS_Error.class.php');

require_once('includes/classes/GUI/ROS_Menu.class.php');
require_once('includes/classes/GUI/ROS_QuickMenu.class.php');
require_once('includes/classes/GUI/ROS_Calendar.class.php');
require_once('includes/classes/GUI/ROS_ReservationList.class.php');
require_once('includes/classes/GUI/ROS_ReservationDesc.class.php');
require_once('includes/classes/GUI/ROS_StatusBar.class.php');
require_once('includes/classes/GUI/ROS_Dialogs.class.php');
require_once('includes/classes/GUI/ROS_Interface.class.php');

require_once('includes/interfaces/ROS_DataSourceType.interface.php');
require_once('includes/classes/Data/ROS_DataSource.class.php');

require_once('includes/classes/Payment/Payment_Method.class.php');

$config = unserialize(file_get_contents('database.cfg'));

ROS_Error::initialize();
ROS_DataSource::initialize('ROS_MySQL.class.php', $config['host'], $config['database'], $config['username'], $config['password']);

$interface = new ROS_Interface(PROGRAM_NAME);

Gtk::main();
?>
