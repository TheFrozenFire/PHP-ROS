<?php
class ROS_ReservationList {
	private $widget;
	
	private $cell_renderers = array();
	private $listStore;
	private $columns = array();
	
	public function __construct() {
		$this->cell_renderers['text'] = new GtkCellRendererText();
		$this->cell_renderers['toggle'] = new GtkCellRendererToggle();
		$this->listStore = new GtkListStore(
			GObject::TYPE_LONG, // Reservation ID
			GObject::TYPE_STRING, // Room Number/Name
			GObject::TYPE_STRING, // Client Name
			GObject::TYPE_BOOLEAN, // Checked-In
			GObject::TYPE_BOOLEAN, // Checked-Out
			GObject::TYPE_BOOLEAN // Has Balance
		);
		$this->listStore->set_sort_column_id(2, Gtk::SORT_DESCENDING);
		
		$this->columns['ResID'] = new GtkTreeViewColumn('Res ID', $this->cell_renderers['text'], 'text', 0);
		$this->columns['ResID']->set_expand(FALSE);
		$this->columns['RoomName'] = new GtkTreeViewColumn('Room Name', $this->cell_renderers['text'], 'text', 1);
		$this->columns['RoomName']->set_expand(FALSE);
		$this->columns['ClientName'] = new GtkTreeViewColumn('Client Name', $this->cell_renderers['text'], 'text', 2);
		$this->columns['ClientName']->set_expand(TRUE);
		$this->columns['CheckedIn'] = new GtkTreeViewColumn('Checked In', $this->cell_renderers['toggle'], 'active', 3);
		$this->columns['CheckedIn']->set_expand(FALSE);
		$this->columns['CheckedOut'] = new GtkTreeViewColumn('Checked Out', $this->cell_renderers['toggle'], 'active', 4);
		$this->columns['CheckedOut']->set_expand(FALSE);
		$this->columns['HasBalance'] = new GtkTreeViewColumn('Has Balance', $this->cell_renderers['toggle'], 'active', 5);
		$this->columns['HasBalance']->set_expand(FALSE);
		
		$this->widget = new GtkTreeView($this->listStore);
		
		foreach($this->columns as $column) $this->widget->append_column($column);
	}
	
	public function getWidget() {
		return $this->widget;
	}
	
	public function prepare() {
		$this->showDate();
		ROS_Interface::getCalendar()->getWidget()->connect_simple('day-selected', array($this, 'showDate'));
		$this->getWidget()->get_selection()->set_mode(Gtk::SELECTION_BROWSE);
	}
	
	public function showDate() {
		$this->listStore->clear();
		$date = ROS_Interface::getCalendar()->getWidget()->get_date();
		$time = mktime(0, 0, 0, $date[1]+1, $date[2], $date[0]);
		$list = ROS_DataSource::getReservationsForDate($time);
		
		foreach($list as $res) $this->listStore->append(array($res['id'], $res['room_name'], "{$res['last_name']}, {$res['first_name']}", $res['checked_in'], $res['checked_out'], $res['has_balance']));
	}
}
?>
