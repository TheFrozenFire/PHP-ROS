<?php
class ROS_Menu {
	private $widget;
	
	private $menuItems = array();
	
	public function __construct() {
		$this->widget = new GtkMenuBar();
		
		$menuFileMain = new GtkMenuItem('_File');
		$this->widget->append($menuFileMain);
		$menuViewMain = new GtkMenuItem('_View');
		$this->widget->append($menuViewMain);
		$menuSetupMain = new GtkMenuItem('_Setup');
		$this->widget->append($menuSetupMain);
		$menuHelpMain = new GtkMenuItem('_Help');
		$this->widget->append($menuHelpMain);
		
		$menuFile = new GtkMenu();
		$menuFileMain->set_submenu($menuFile);
		$menuView = new GtkMenu();
		$menuViewMain->set_submenu($menuView);
		$menuSetup = new GtkMenu();
		$menuSetupMain->set_submenu($menuSetup);
		$menuHelp = new GtkMenu();
		$menuHelpMain->set_submenu($menuHelp);
		
		$menuFilePrint = new GtkMenuItem('_Reports');
		$menuFile->add($menuFilePrint);
		$menuFile->add($tmp = new GtkSeparatorMenuItem());
		$menuFileQuit = new GtkMenuItem('_Quit');
		$menuFile->add($menuFileQuit);
		
		$menuViewFullscreen = new GtkMenuItem('_Fullscreen');
		$menuView->add($menuViewFullscreen);
		
		$menuSetupRooms = new GtkMenuItem('_Rooms');
		$menuSetup->add($menuSetupRooms);
		
		$menuHelpErrors = new GtkMenuItem('_Errors');
		$menuHelp->add($menuHelpErrors);
		$menuHelpAbout = new GtkMenuItem('_About');
		$menuHelp->add($menuHelpAbout);
		
		$menuFileQuit->connect_simple('activate', array('ROS_Interface', 'shutdown'));
		
		$menuViewFullscreen->connect_simple('activate', array('ROS_Interface', 'fullscreen'));
		
		$menuSetupRooms->connect_simple('activate', array('ROS_Dialogs', 'setupRooms'));
		
		$menuHelpErrors->connect_simple('activate', array('ROS_Dialogs', 'errorLogDialog'));
		$menuHelpAbout->connect_simple('activate', array('ROS_Dialogs', 'aboutDialog'));
	}
	
	public function getWidget() {
		return $this->widget;
	}
	
	public function prepare() {
	
	}
}
?>
