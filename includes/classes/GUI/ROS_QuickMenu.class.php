<?php
class ROS_QuickMenu {
	private $widget;
	
	public function __construct() {
		$this->widget = new GtkVButtonBox();
		$this->widget->set_layout(Gtk::BUTTONBOX_SPREAD);
		$this->widget->set_spacing(25);
		
		$clientSearch = GtkToolButton::new_from_stock(Gtk::STOCK_FIND);
		$clientModify = GtkToolButton::new_from_stock(Gtk::STOCK_EDIT);
		$reservationCreate = GtkToolButton::new_from_stock(Gtk::STOCK_ADD);
		$reportsList = GtkToolButton::new_from_stock(Gtk::STOCK_PRINT);
		$quitProgram = GtkToolButton::new_from_stock(Gtk::STOCK_QUIT);
		
		$quickToolTips = new GtkTooltips();
		$quickToolTips->set_tip(
			$clientSearch,
			'Search for Client Information'
		);
		$quickToolTips->set_tip(
			$clientModify,
			'Modify a Client\'s Information'
		);
		$quickToolTips->set_tip(
			$reservationCreate,
			'Create a Reservation'
		);
		$quickToolTips->set_tip(
			$reportsList,
			'View/Print Reports'
		);
		$quickToolTips->set_tip(
			$quitProgram,
			'Quit the Program'
		);
		
		$this->widget->add($clientSearch);
		$this->widget->add($clientModify);
		$this->widget->add($reservationCreate);
		$this->widget->add($reportsList);
		$this->widget->add($quitProgram);
		
		$clientSearch->connect_simple('clicked', array('ROS_Dialogs', 'clientSearchDialog'));
		$clientModify->connect_simple('clicked', array('ROS_Dialogs', 'clientModify'));
		$reservationCreate->connect_simple('clicked', array('ROS_Dialogs', 'createReservation'));
		$quitProgram->connect_simple('clicked', array('ROS_Interface', 'shutdown'));
	}
	
	public function getWidget() {
		return $this->widget;
	}
	
	public function prepare() {
	
	}
}
?>
