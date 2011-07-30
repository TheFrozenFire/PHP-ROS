<?php
class ROS_Availability {
	const left_offset = 1;
	const top_offset = 1;

	private static $window;
	
	private static $grid;

	public static function showAvailability() {
		self::$window = new GtkWindow();
		
		self::$grid = GtkSheet::new_browser(5, 5, 'Availability');
		
		self::$window->add(self::$grid);
		
		self::$window->show_all();
	}
}
?>
