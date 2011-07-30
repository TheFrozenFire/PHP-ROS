<?php
class ROS_ReservationDesc {
	private $widget;
	
	private $clientName;
	private $clientDesc;
	private $clientDescStore;
	
	private $resName;
	private $resDesc;
	private $resDescStore;
	
	private $editButton;
	private $paymentButton;
	private $salesButton;
	private $checkInButton;
	private $checkOutButton;
	
	private $comments;
	private $commentsStore;
	
	private $commentAddButton;
	private $commentRemoveButton;
	
	private $client_renderer;
	private $res_renderer;
	private $comments_renderer;
	
	public function __construct() {
		$this->widget = new GtkVBox();
		
		$descArea = new GtkHBox();
		$this->widget->pack_start($descArea);
		$this->widget->pack_start($tmp = new GtkHSeparator(), FALSE);
		
		$this->client_renderer = new GtkCellRendererText();
		$this->res_renderer = new GtkCellRendererText();
		$this->comments_renderer = new GtkCellRendererText();
		
		$clientDesc = new GtkVBox();
		$this->clientName = new GtkLabel('No Selection');
		$this->clientDescStore = new GtkListStore(
			GObject::TYPE_STRING,
			GObject::TYPE_STRING
		);
		$this->clientDesc = new GtkTreeView($this->clientDescStore);
		$this->clientDesc->set_headers_visible(FALSE);
		$clientFieldColumn = new GtkTreeViewColumn('Type', $this->client_renderer, 'text', 0);
		$this->clientDesc->append_column($clientFieldColumn);
		$clientDataColumn = new GtkTreeViewColumn('Info', $this->client_renderer, 'text', 1);
		$this->clientDesc->append_column($clientDataColumn);
		$clientScroll = new GtkScrolledWindow();
		$clientScroll->set_policy(Gtk::POLICY_NEVER, Gtk::POLICY_AUTOMATIC);
		$clientScroll->add($this->clientDesc);
		$clientDesc->pack_start($this->clientName, FALSE);
		$clientDesc->pack_start($clientScroll);
		
		$resDesc = new GtkVBox();
		$this->resName = new GtkLabel('No Selection');
		$this->resDescStore = new GtkListStore(
			GObject::TYPE_STRING,
			GObject::TYPE_STRING
		);
		$this->resDesc = new GtkTreeView($this->resDescStore);
		$this->resDesc->set_headers_visible(FALSE);
		$resFieldColumn = new GtkTreeViewColumn('Type', $this->res_renderer, 'text', 0);
		$this->resDesc->append_column($resFieldColumn);
		$resDataColumn = new GtkTreeViewColumn('Info', $this->res_renderer, 'text', 1);
		$this->resDesc->append_column($resDataColumn);
		$resScroll = new GtkScrolledWindow();
		$resScroll->set_policy(Gtk::POLICY_NEVER, Gtk::POLICY_AUTOMATIC);
		$resScroll->add($this->resDesc);
		$resDesc->pack_start($this->resName, FALSE);
		$resDesc->pack_start($resScroll);
		
		$buttonBox = new GtkVButtonBox();
		$buttonBox->set_layout(Gtk::BUTTONBOX_SPREAD);
		$buttonBox->set_spacing(25);
		
		$this->editButton = new GtkButton('Edit');
		$this->paymentButton = new GtkButton('Payment');
		$this->salesButton = new GtkButton('Sales');
		$this->checkInButton = new GtkButton('Check In');
		$this->checkOutButton = new GtkButton('Check Out');
		
		$this->editButton->set_sensitive(FALSE);
		$this->paymentButton->set_sensitive(FALSE);
		$this->salesButton->set_sensitive(FALSE);
		$this->checkInButton->set_sensitive(FALSE);
		$this->checkOutButton->set_sensitive(FALSE);
		
		$buttonBox->add($this->editButton);
		$buttonBox->add($this->paymentButton);
		$buttonBox->add($this->salesButton);
		$buttonBox->add($this->checkInButton);
		$buttonBox->add($this->checkOutButton);
		
		$this->editButton->connect_simple('clicked', array('ROS_Dialogs', 'editReservation'));
		$this->paymentButton->connect_simple('clicked', array('ROS_Dialogs', 'paymentDialog'));
		$this->checkInButton->connect_simple('clicked', array($this, 'checkIn'));
		$this->checkOutButton->connect_simple('clicked', array($this, 'checkOut'));
		
		$descArea->pack_start($clientDesc);
		$descArea->pack_start($tmp = new GtkVSeparator(), FALSE);
		$descArea->pack_start($resDesc);
		$descArea->pack_start($tmp = new GtkVSeparator(), FALSE);
		$descArea->pack_end($buttonBox, FALSE);
		
		$commentBox = new GtkHBox();
		$this->widget->pack_end($commentBox, FALSE);
		$this->commentsStore = new GtkListStore(GObject::TYPE_LONG, GObject::TYPE_STRING);
		$this->comments = new GtkTreeView($this->commentsStore);
		$commentScroll = new GtkScrolledWindow();
		$commentScroll->set_policy(Gtk::POLICY_AUTOMATIC, Gtk::POLICY_AUTOMATIC);
		$commentScroll->add($this->comments);
		$commentsIDColumn = new GtkTreeViewColumn('ID', $this->comments_renderer, 'text', 0);
		$commentsIDColumn->set_visible(FALSE);
		$commentsColumn = new GtkTreeViewColumn('Comments', $this->comments_renderer, 'text', 1);
		$this->comments->append_column($commentsIDColumn);
		$this->comments->append_column($commentsColumn);
		$commentBox->pack_start($commentScroll);
		$commentBox->pack_start($tmp = new GtkVSeparator(), FALSE);
		
		$commentButtons = new GtkVButtonBox();
		$this->commentAddButton = GtkToolButton::new_from_stock(Gtk::STOCK_ADD);
		$this->commentRemoveButton = GtkToolButton::new_from_stock(Gtk::STOCK_REMOVE);
		$commentButtons->add($this->commentAddButton);
		$commentButtons->add($this->commentRemoveButton);
		$commentBox->pack_start($commentButtons, FALSE);
		
		$this->commentAddButton->connect_simple('clicked', array($this, 'commentAdd'));
		$this->commentRemoveButton->connect_simple('clicked', array($this, 'commentRemove'));
	}
	
	public function getWidget() {
		return $this->widget;
	}
	
	public function prepare() {
		$this->editButton->set_sensitive(FALSE);
		$this->paymentButton->set_sensitive(FALSE);
		$this->salesButton->set_sensitive(FALSE);
		$this->checkInButton->set_sensitive(FALSE);
		$this->checkOutButton->set_sensitive(FALSE);
		$this->commentAddButton->set_sensitive(FALSE);
		$this->commentRemoveButton->set_sensitive(FALSE);
		ROS_Interface::getReservationList()->getWidget()->get_selection()->connect_simple('changed', array($this, 'showRes'));
	}
	
	public function showRes() {
		list($model, $iter) = ROS_Interface::getReservationList()->getWidget()->get_selection()->get_selected();
		if(is_null($iter) || !($model->get_value($iter, 0))) {
			$this->clientName->set_text('No Selection');
			$this->resName->set_text('No Selection');
			$this->clientDescStore->clear();
			$this->resDescStore->clear();
			$this->commentsStore->clear();
			
			$this->editButton->set_sensitive(FALSE);
			$this->paymentButton->set_sensitive(FALSE);
			$this->salesButton->set_sensitive(FALSE);
			$this->checkInButton->set_sensitive(FALSE);
			$this->checkOutButton->set_sensitive(FALSE);
			$this->commentAddButton->set_sensitive(FALSE);
			$this->commentRemoveButton->set_sensitive(FALSE);
		} else {
			$reservation = ROS_DataSource::getReservationInfo($model->get_value($iter, 0));
			$client = ROS_DataSource::getClientInfo($reservation['client_id']);
			$comments = ROS_DataSource::getReservationComments($reservation['id']);
		
			$this->clientName->set_text("{$client['id']}: {$client['last_name']}, {$client['first_name']}");
			$this->clientDescStore->clear();
			if(!empty($client)) {
				$this->clientDescStore->append(array('Client ID', $client['id']));
				$this->clientDescStore->append(array('Last Name', $client['last_name']));
				$this->clientDescStore->append(array('First Name', $client['first_name']));
			}
			
			$roomInfo = ROS_DataSource::getRoomInfo($reservation['room_id']);
			$this->resName->set_text("{$reservation['id']}: {$roomInfo['room_name']}");
			$this->resDescStore->clear();
			if(!empty($reservation)) {
				$this->resDescStore->append(array('Reservation ID', $reservation['id']));
				$this->resDescStore->append(array('Room', $roomInfo['room_name']));
				$this->resDescStore->append(array('First Night', date('F j, Y', $reservation['open_date'])));
				$this->resDescStore->append(array('Check Out Day', date('F j, Y', $reservation['close_date'])));
				$this->resDescStore->append(array('Date Made', date('F j, Y', $reservation['made_date'])));
				$this->resDescStore->append(array('Checked In', ($reservation['checked_in'])?'Yes':'No'));
				$this->resDescStore->append(array('Checked Out', ($reservation['checked_out'])?'Yes':'No'));
			}
			
			$this->editButton->set_sensitive(TRUE);
			$this->paymentButton->set_sensitive(TRUE);
			$this->salesButton->set_sensitive(TRUE);
			$this->checkInButton->set_sensitive((!$reservation['checked_in'] && mktime(0, 0, 0) <= $reservation['close_date']));
			$this->checkOutButton->set_sensitive(($reservation['checked_in'] && !$reservation['checked_out']));
			$this->commentAddButton->set_sensitive(TRUE);
			$this->commentRemoveButton->set_sensitive(TRUE);
			
			$this->commentsStore->clear();
			if(!empty($comments)) foreach($comments as $comment) $this->commentsStore->append(array($comment['id'], $comment['comment']));
		}
	}
	
	public function checkIn() {
		list($model, $iter) = ROS_Interface::getReservationList()->getWidget()->get_selection()->get_selected();
		if($iter) {
			ROS_DataSource::checkIn($model->get_value($iter, 0));
			ROS_Interface::getReservationList()->showDate();
		}
	}
	
	public function checkOut() {
		list($model, $iter) = ROS_Interface::getReservationList()->getWidget()->get_selection()->get_selected();
		if($iter) {
			ROS_DataSource::checkOut($model->get_value($iter, 0));
			ROS_Interface::getReservationList()->showDate();
		}
	}
	
	public function commentAdd($parent = NULL) {
		list($model, $iter) = ROS_Interface::getReservationList()->getWidget()->get_selection()->get_selected();
		if($iter) {
			$dialog = new GtkDialog(
				PROGRAM_NAME.' - Add Comment',
				$parent,
				Gtk::DIALOG_MODAL,
				array(
					Gtk::STOCK_OK, Gtk::RESPONSE_OK,
					Gtk::STOCK_CANCEL, Gtk::RESPONSE_CANCEL
				)
			);
			
			$dialog->vbox->add($entry = new GtkEntry());
			
			$dialog->show_all();
			if($dialog->run() == Gtk::RESPONSE_OK) {
				ROS_DataSource::commentAdd($model->get_value($iter, 0), $entry->get_text());
			}
			$dialog->destroy();
			
			$comments = ROS_DataSource::getReservationComments($model->get_value($iter, 0));
			$this->commentsStore->clear();
			if(!empty($comments)) foreach($comments as $comment) $this->commentsStore->append(array($comment['id'], $comment['comment']));
		}
	}
	
	public function commentRemove($parent = NULL) {
		list($model, $iter) = ROS_Interface::getReservationList()->getWidget()->get_selection()->get_selected();
		if($iter) {
			list($commentModel, $commentIter) = $this->comments->get_selection()->get_selected();
			if($commentIter) {
				ROS_DataSource::commentRemove($commentModel->get_value($commentIter, 0));
				$comments = ROS_DataSource::getReservationComments($model->get_value($iter, 0));
				$this->commentsStore->clear();
				if(!empty($comments)) foreach($comments as $comment) $this->commentsStore->append(array($comment['id'], $comment['comment']));
			}
		}
	}
}
?>
