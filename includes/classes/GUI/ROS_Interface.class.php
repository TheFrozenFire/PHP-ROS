<?php
class ROS_Interface {
	private static $main_window;
	private static $is_fullscreen = FALSE;
	private $v_workspace;
	private $h_workspace;
	
	private $res_workspace;
	private $res_area;
	
	private static $menu;
	private static $quickmenu;
	private static $calendar;
	private static $reservationlist;
	private static $reservationdesc;
	private static $statusbar;
	
	public function __construct($programName) {
		self::$main_window = new GtkWindow();
		$pixbuf = self::$main_window->render_icon(Gtk::STOCK_HOME, Gtk::ICON_SIZE_DIALOG);
		self::$main_window->set_icon($pixbuf);
		self::$main_window->set_title($programName);
		self::$main_window->resize(800, 600);
		self::$main_window->maximize();
		self::$main_window->connect_simple('destroy', array('ROS_Interface', 'shutdown'));
		
		$this->v_workspace = new GtkVBox();
		self::$main_window->add($this->v_workspace);
		
		self::$menu = new ROS_Menu();
		$this->v_workspace->pack_start(self::$menu->getWidget(), FALSE);
		
		$this->h_workspace = new GtkHBox();
		$this->v_workspace->pack_start($this->h_workspace);
		
		self::$quickmenu = new ROS_QuickMenu();
		$this->h_workspace->pack_start(self::$quickmenu->getWidget(), FALSE);
		
		$this->h_workspace->pack_start($tmp = new GtkVSeparator(), FALSE);
		
		$this->res_workspace = new GtkVBox();
		$this->h_workspace->pack_start($this->res_workspace);
		
		$this->res_area = new GtkHBox();
		$this->res_workspace->pack_start($this->res_area, FALSE);
		
		$this->res_workspace->pack_start($tmp = new GtkHSeparator(), FALSE);
		
		self::$calendar = new ROS_Calendar();
		$this->res_area->pack_start(self::$calendar->getWidget(), FALSE);
		
		$this->res_area->pack_start($tmp = new GtkVSeparator(), FALSE);
		
		self::$reservationlist = new ROS_ReservationList();
		$tmp = new GtkScrolledWindow();
		$tmp->set_policy(Gtk::POLICY_AUTOMATIC, Gtk::POLICY_AUTOMATIC);
		$tmp->add(self::$reservationlist->getWidget());
		$this->res_area->pack_start($tmp);
		
		self::$reservationdesc = new ROS_ReservationDesc();
		$this->res_workspace->pack_start(self::$reservationdesc->getWidget());
		
		self::$statusbar = new ROS_StatusBar();
		$this->v_workspace->pack_end(self::$statusbar->getWidget(), FALSE);
		
		self::$main_window->show_all();
		
		self::$menu->prepare();
		self::$quickmenu->prepare();
		self::$calendar->prepare();
		self::$reservationlist->prepare();
		self::$reservationdesc->prepare();
		self::$statusbar->prepare();
	}
	
	public static function refresh() {
		self::$reservationlist->showDate();
		self::$reservationdesc->showRes();
	}
	
	public static function shutdown() {
		Gtk::main_quit();
	}
	
	public static function fullscreen() {
		if(!self::$is_fullscreen) {
			self::$main_window->fullscreen();
			self::$is_fullscreen = TRUE;
		} else {
			self::$main_window->unfullscreen();
			self::$is_fullscreen = FALSE;
		}
	}
	
	public static function getMenu() {
		return self::$menu;
	}
	
	public static function getQuickMenu() {
		return self::$quickmenu;
	}
	
	public static function getCalendar() {
		return self::$calendar;
	}
	
	public static function getReservationList() {
		return self::$reservationlist;
	}
	
	public static function getReservationDesc() {
		return self::$reservationdesc;
	}
	
	public static function getStatusBar() {
		return self::$statusbar;
	}
}
?>
