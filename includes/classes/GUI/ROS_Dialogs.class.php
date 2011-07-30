<?php
class ROS_Dialogs {
	public static function createReservation() {
		// Select Days
		$calendarDialog = new GtkDialog(
			PROGRAM_NAME.' - Create Reservation (Select Days)',
			NULL,
			Gtk::DIALOG_MODAL,
			array(
				Gtk::STOCK_CANCEL, Gtk::RESPONSE_CANCEL,
				Gtk::STOCK_OK, Gtk::RESPONSE_OK
			)
		);
		
		$calendarLabel = new GtkLabel('Create a Reservation');
		
		$calendarDialog->vbox->pack_start($calendarLabel, FALSE);
		$calendarDialog->vbox->pack_start($tmp = new GtkHSeparator(), FALSE);
		
		$calendarWorkspace = new GtkHBox();
		$calendarDialog->vbox->add($calendarWorkspace);
		
		$startArea = new GtkVBox();
		$startLabel = new GtkLabel('First Night');
		$startArea->pack_start($startLabel, FALSE);
		$startCalendar = new GtkCalendar();
		$startArea->pack_start($startCalendar, FALSE);
		$calendarWorkspace->pack_start($startArea);
		
		$endArea = new GtkVBox();
		$endLabel = new GtkLabel('Last Night');
		$endArea->pack_start($endLabel, FALSE);
		$endCalendar = new GtkCalendar();
		$endArea->pack_start($endCalendar, FALSE);
		$calendarWorkspace->pack_start($endArea);
		
		$calendarCheck = function($cals) {
			$startDateArray = $cals['start']->get_date();
			$startDate = mktime(0, 0, 0, $startDateArray[1], $startDateArray[2], $startDateArray[0]);
			$endDateArray = $cals['end']->get_date();
			$endDate = mktime(0, 0, 0, $endDateArray[1], $endDateArray[2], $endDateArray[0]);
		
			if($endDate < $startDate) {
				$cals['end']->select_month($startDateArray[1], $startDateArray[0]);
				$cals['end']->select_day($startDateArray[2]);
				return TRUE;
			}
		
			if(array_key_exists('units', $cals)) {
				$availableRooms = ROS_DataSource::getAvailableRoomsForDates($startDate, $endDate);
				if(!empty($availableRooms)) {
					$cals['units']->clear();
					foreach($availableRooms as $unit) $cals['units']->append(array($unit['id'], $unit['room_name']));
				}
			}
		};
		
		$startCalendar->connect_simple('day-selected', $calendarCheck, array('start'=>$startCalendar, 'end'=>$endCalendar));
		$endCalendar->connect_simple('day-selected', $calendarCheck, array('start'=>$startCalendar, 'end'=>$endCalendar));
		
		$calendarDialog->show_all();
		$result = $calendarDialog->run();
		
		$startDateArray = $startCalendar->get_date();
		$startDate = mktime(0, 0, 0, $startDateArray[1]+1, $startDateArray[2], $startDateArray[0]);
		$endDateArray = $endCalendar->get_date();
		$endDate = mktime(0, 0, 0, $endDateArray[1]+1, $endDateArray[2], $endDateArray[0]);
		
		$calendarDialog->destroy();
		
		if($result == Gtk::RESPONSE_CANCEL) return FALSE;
		
		$availableRooms = ROS_DataSource::getAvailableRoomsForDates($startDate, $endDate);
		
		// Select Unit
		$unitDialog = new GtkDialog(
			PROGRAM_NAME.' - Create Reservation (Select Unit)',
			NULL,
			Gtk::DIALOG_MODAL,
			array(
				Gtk::STOCK_CANCEL, Gtk::RESPONSE_CANCEL,
				Gtk::STOCK_OK, Gtk::RESPONSE_OK
			)
		);
		
		$unitLabel = new GtkLabel('Select a Unit');
		$unitDialog->vbox->pack_start($unitLabel, FALSE);
		
		$unitWorkspace = new GtkHBox();
		$unitDialog->vbox->add($unitWorkspace);
		
		$unitCellRenderer = new GtkCellRendererText();
		$unitListStore = new GtkListStore(GObject::TYPE_LONG, GObject::TYPE_STRING);
		
		if(is_array($availableRooms)) {
			foreach($availableRooms as $unit) $unitListStore->append(array($unit['id'], $unit['room_name']));
		} else {
			$noAvailDialog = new GtkDialog(
				PROGRAM_NAME.' - Create Reservation (No Availability)',
				$parent,
				Gtk::DIALOG_MODAL,
				array(
					Gtk::STOCK_OK, Gtk::RESPONSE_OK
				)
			);
			
			$noAvailLabel = new GtkLabel('Sorry, there is no availability for the dates specified.');
			$noAvailDialog->vbox->add($noAvailLabel);
			
			$noAvailDialog->show_all();
			$noAvailDialog->run();
			$noAvailDialog->destroy();
			return FALSE;
		}
		
		$unitTreeView = new GtkTreeView($unitListStore);
		
		$unitColumnID = new GtkTreeViewColumn('ID', $unitCellRenderer, 'text', 0);
		$unitColumnName = new GtkTreeViewColumn('Unit Name', $unitCellRenderer, 'text', 1);
		$unitTreeView->append_column($unitColumnID);
		$unitTreeView->append_column($unitColumnName);
		
		$unitWorkspace->add($unitTreeView);
		
		$unitDialog->show_all();
		$result = $unitDialog->run();
		list($model, $iter) = $unitTreeView->get_selection()->get_selected();
		$unitDialog->destroy();
		
		if($result == Gtk::RESPONSE_CANCEL) return FALSE;
		
		$unit = array();
		$unit['id'] = $model->get_value($iter, 0);
		$unit['name'] = $model->get_value($iter, 1);
		
		$client = ROS_Dialogs::clientSearchDialog();
		$client = ROS_DataSource::getClientInfo($client);
		
		$finalDialog = new GtkDialog(
			PROGRAM_NAME.' - Create Reservation',
			NULL,
			Gtk::DIALOG_MODAL,
			array(
				Gtk::STOCK_OK, Gtk::RESPONSE_OK,
				Gtk::STOCK_CANCEL, Gtk::RESPONSE_CANCEL
			)
		);
		
		$finalLabel = new GtkLabel('Finalize Reservation');
		$finalDialog->vbox->pack_start($finalLabel, FALSE);
		$finalDialog->vbox->pack_start($tmp = new GtkHSeparator(), FALSE);
		
		$finalWorkspace = new GtkHBox();
		$finalDialog->vbox->add($finalWorkspace);
		
		$finalDateArea = new GtkVBox();
		$finalWorkspace->add($finalDateArea);
		$finalWorkspace->pack_start($tmp = new GtkVSeparator(), FALSE);
		$finalDateLabel = new GtkLabel('Dates:');
		$finalDateDates = new GtkLabel(
			"First Night: ".date('F j, Y', $startDate)."\n".
			"Last Night: ".date('F j, Y', $endDate)
		);
		$finalDateArea->pack_start($finalDateLabel, FALSE);
		$finalDateArea->add($finalDateDates);
		
		$finalUnitArea = new GtkVBox();
		$finalWorkspace->add($finalUnitArea);
		$finalWorkspace->pack_start($tmp = new GtkVSeparator(), FALSE);
		$finalUnitLabel = new GtkLabel('Unit:');
		$finalUnitDesc = new GtkLabel(
			"ID: {$unit['id']}\n".
			"Name: {$unit['name']}"
		);
		$finalUnitArea->pack_start($finalUnitLabel, FALSE);
		$finalUnitArea->add($finalUnitDesc);
		
		$finalClientArea = new GtkVBox();
		$finalWorkspace->add($finalClientArea);
		$finalClientLabel = new GtkLabel('Client:');
		$finalClientDesc = new GtkLabel(
			"ID: {$client['id']}\n".
			"Last Name: {$client['last_name']}\n".
			"First Name: {$client['first_name']}"
		);
		$finalClientArea->pack_start($finalClientLabel, FALSE);
		$finalClientArea->add($finalClientDesc);
		
		$finalDialog->show_all();
		$result = $finalDialog->run();
		$finalDialog->destroy();
		
		if($result == Gtk::RESPONSE_CANCEL) return FALSE;
		
		ROS_DataSource::addReservation($startDate, $endDate, $unit['id'], $client['id']);
		
		ROS_Interface::refresh();
		return TRUE;
	}
	
	public static function editReservation() {
		$dialog = new GtkDialog(
			PROGRAM_NAME.' - Edit Reservation',
			$parent,
			Gtk::DIALOG_MODAL,
			array(
				Gtk::STOCK_CANCEL, Gtk::RESPONSE_CANCEL,
				Gtk::STOCK_OK, Gtk::RESPONSE_OK
			)
		);
		
		$resList = ROS_Interface::getReservationList()->getWidget();
		
		list($model, $iter) = $resList->get_selection()->get_selected();
		
		if($iter) $resInfo = ROS_DataSource::getReservationInfo($model->get_value($iter, 0)); else return FALSE;
		
		$dialog->vbox->pack_start($tmp = new GtkLabel('Edit Reservation'), FALSE);
		$dialog->vbox->pack_start($tmp = new GtkHSeparator(), FALSE);
		
		$calendarWorkspace = new GtkHBox();
		$dialog->vbox->pack_start($calendarWorkspace, FALSE);
		
		$startArea = new GtkVBox();
		$startLabel = new GtkLabel('First Night');
		$startArea->pack_start($startLabel, FALSE);
		$startCalendar = new GtkCalendar();
		$startArea->pack_start($startCalendar, FALSE);
		$calendarWorkspace->pack_start($startArea);
		
		$endArea = new GtkVBox();
		$endLabel = new GtkLabel('Last Night');
		$endArea->pack_start($endLabel, FALSE);
		$endCalendar = new GtkCalendar();
		$endArea->pack_start($endCalendar, FALSE);
		$calendarWorkspace->pack_start($endArea);
		
		$resStartDate = getdate($resInfo['open_date']);
		$startCalendar->select_month($resStartDate['mon']-1, $resStartDate['year']);
		$startCalendar->select_day($resStartDate['mday']);
		
		$resEndDate = getdate($resInfo['close_date']);
		$endCalendar->select_month($resEndDate['mon']-1, $resEndDate['year']);
		$endCalendar->select_day($resEndDate['mday']);
		
		$dialog->vbox->pack_start($tmp = new GtkHSeparator(), FALSE);
		
		$clientRenderer = new GtkCellRendererText();
		$clientDesc = new GtkVBox();
		$clientDescStore = new GtkListStore(
			GObject::TYPE_STRING,
			GObject::TYPE_STRING
		);
		$clientDescView = new GtkTreeView($clientDescStore);
		$clientDescView->set_headers_visible(FALSE);
		$clientFieldColumn = new GtkTreeViewColumn('Type', $clientRenderer, 'text', 0);
		$clientDescView->append_column($clientFieldColumn);
		$clientDataColumn = new GtkTreeViewColumn('Info', $clientRenderer, 'text', 1);
		$clientDescView->append_column($clientDataColumn);
		$clientScroll = new GtkScrolledWindow();
		$clientScroll->set_policy(Gtk::POLICY_NEVER, Gtk::POLICY_AUTOMATIC);
		$clientScroll->add($clientDescView);
		$clientDesc->pack_start($clientScroll);
		$dialog->vbox->pack_start($clientDesc);
		
		$clientInfo = ROS_DataSource::getClientInfo($resInfo['client_id']);
		
		if(!empty($clientInfo)) {
			$clientDescStore->append(array('Client ID', $clientInfo['id']));
			$clientDescStore->append(array('Last Name', $clientInfo['last_name']));
			$clientDescStore->append(array('First Name', $clientInfo['first_name']));
		}
		
		$changeClientButton = new GtkButton('Change Client');
		$dialog->vbox->pack_start($changeClientButton, FALSE);
		
		$changeClientButton->connect_simple('clicked', function($store) {
			$clientID = self::clientSearchDialog();
		
			if($clientID) {
				$clientInfo = ROS_DataSource::getClientInfo($clientID);
		
				if(!empty($clientInfo)) {
					$store->clear();
					$clientDescStore->append(array('Client ID', $clientInfo['id']));
					$clientDescStore->append(array('Last Name', $clientInfo['last_name']));
					$clientDescStore->append(array('First Name', $clientInfo['first_name']));
				}
			}
		}, $clientDescStore);
		
		$dialog->vbox->pack_start($tmp = new GtkHSeparator(), FALSE);
		
		$startDateArray = $startCalendar->get_date();
		$startDate = mktime(0, 0, 0, $startDateArray[1]+1, $startDateArray[2], $startDateArray[0]);
		$endDateArray = $endCalendar->get_date();
		$endDate = mktime(0, 0, 0, $endDateArray[1]+1, $endDateArray[2], $endDateArray[0]);
		
		$availableRooms = ROS_DataSource::getAvailableRoomsForDates($startDate, $endDate);
		
		$unitWorkspace = new GtkHBox();
		
		$unitCellRenderer = new GtkCellRendererText();
		$unitListStore = new GtkListStore(GObject::TYPE_LONG, GObject::TYPE_STRING);
		
		$currentUnit = ROS_DataSource::getRoomInfo($resInfo['room_id']);
		
		$unitListStore->append(array($currentUnit['id'], $currentUnit['room_name']));
		
		if(!empty($availableRooms)) foreach($availableRooms as $unit) $unitListStore->append(array($unit['id'], $unit['room_name']));

		$unitTreeView = new GtkTreeView($unitListStore);
		
		$unitColumnID = new GtkTreeViewColumn('ID', $unitCellRenderer, 'text', 0);
		$unitColumnName = new GtkTreeViewColumn('Unit Name', $unitCellRenderer, 'text', 1);
		$unitTreeView->append_column($unitColumnID);
		$unitTreeView->append_column($unitColumnName);
		
		$firstIter = $unitListStore->get_iter_first();
		$unitSelection = $unitTreeView->get_selection();
		$unitSelection->select_iter($firstIter);
		
		$unitWorkspace->add($unitTreeView);
		
		$dialog->vbox->pack_start($unitWorkspace);
		
		$calendarCheck = function($parent, $cals) {
			$startDateArray = $cals['start']->get_date();
			$startDate = mktime(0, 0, 0, $startDateArray[1], $startDateArray[2], $startDateArray[0]);
			$endDateArray = $cals['end']->get_date();
			$endDate = mktime(0, 0, 0, $endDateArray[1], $endDateArray[2], $endDateArray[0]);
		
			if($endDate < $startDate) {
				$cals['end']->select_month($startDateArray[1], $startDateArray[0]);
				$cals['end']->select_day($startDateArray[2]);
				return TRUE;
			}
		
			if(array_key_exists('units', $cals)) {
				$availableRooms = ROS_DataSource::getAvailableRoomsForDates($startDate, $endDate);
				if(!empty($availableRooms)) {
					$cals['units']->clear();
					foreach($availableRooms as $unit) $cals['units']->append(array($unit['id'], $unit['room_name']));
				}
			}
		};
		
		$startCalendar->connect('day-selected', $calendarCheck, array('start'=>$startCalendar, 'end'=>$endCalendar, 'units'=>$unitListStore));
		$endCalendar->connect('day-selected', $calendarCheck, array('start'=>$startCalendar, 'end'=>$endCalendar, 'units'=>$unitListStore));
		
		$dialog->set_default_response(Gtk::RESPONSE_OK);
		
		$dialog->show_all();
		$dialog->resize(640, 480);
		
		while($dialog->run() == Gtk::RESPONSE_OK) {
			$startDateArray = $startCalendar->get_date();
			$endDateArray = $endCalendar->get_date();
			list($model, $iter) = $unitSelection->get_selected();
			if(!$iter) continue;
			ROS_DataSource::modifyReservation($resInfo['id'], mktime(0, 0, 0, $startDateArray[1]+1, $startDateArray[2], $startDateArray[0]), mktime(0, 0, 0, $endDateArray[1]+1, $endDateArray[2], $endDateArray[0]), $model->get_value($iter, 0), $clientDescStore->get_value($clientDescStore->get_iter_first(), 1));
			break;
		}
		ROS_Interface::refresh();
		$dialog->destroy();
	}
	
	public static function clientSearchDialog() {
		$dialog = new GtkDialog(
			PROGRAM_NAME.' - Client Search',
			NULL,
			Gtk::DIALOG_MODAL,
			array(
				Gtk::STOCK_OK, Gtk::RESPONSE_OK,
				Gtk::STOCK_CANCEL, Gtk::RESPONSE_CANCEL
			)
		);
		
		$dialogLabel = new GtkLabel('Client Search');
		$dialog->vbox->pack_start($dialogLabel, FALSE);
		
		$searchArea = new GtkHBox();
		$dialog->vbox->pack_start($searchArea, FALSE);
		
		$searchAreaEntries = new GtkVBox();
		$searchArea->add($searchAreaEntries);
		
		$searchLastName = new GtkHBox();
		$searchLastNameLabel = new GtkLabel('Last Name:');
		$searchLastNameEntry = new GtkEntry();
		$searchLastName->pack_start($searchLastNameLabel, FALSE);
		$searchLastName->add($searchLastNameEntry);
		$searchAreaEntries->add($searchLastName);
		
		$searchFirstName = new GtkHBox();
		$searchFirstNameLabel = new GtkLabel('First Name:');
		$searchFirstNameEntry = new GtkEntry();
		$searchFirstName->pack_start($searchFirstNameLabel, FALSE);
		$searchFirstName->add($searchFirstNameEntry);
		$searchAreaEntries->add($searchFirstName);
		
		$searchButton = new GtkButton('Search');
		$searchArea->pack_start($searchButton, FALSE);
		
		$addClientButton = new GtkButton("Add\nClient");
		$searchArea->pack_start($addClientButton, FALSE);
		
		$searchLastNameEntry->connect_simple('activate', array($searchButton, 'clicked'));
		$searchFirstNameEntry->connect_simple('activate', array($searchButton, 'clicked'));
		
		$dialog->vbox->pack_start($tmp = new GtkHSeparator(), FALSE);
		
		$resultStore = new GtkListStore(
			GObject::TYPE_LONG,
			GObject::TYPE_STRING,
			GObject::TYPE_STRING
		);
		$resultRenderer = new GtkCellRendererText();
		$resultArea = new GtkTreeView($resultStore);
		$resultColumnID = new GtkTreeViewColumn('ID', $resultRenderer, 'text', 0);
		$resultColumnLastName = new GtkTreeViewColumn('Last Name', $resultRenderer, 'text', 1);
		$resultColumnFirstName = new GtkTreeViewColumn('First Name', $resultRenderer, 'text', 2);
		$resultArea->append_column($resultColumnID);
		$resultArea->append_column($resultColumnLastName);
		$resultArea->append_column($resultColumnFirstName);
		
		$dialog->vbox->pack_start($resultArea);
		
		$searchButton->connect_simple(
			'clicked',
			function($a) {
				$a['store']->clear();
				$results = ROS_DataSource::searchClients($a['last_name']->get_text(), $a['first_name']->get_text());
				if(!empty($results)) foreach($results as $result) $a['store']->append(array($result['id'], $result['last_name'], $result['first_name']));
			},
			array(
				'last_name'=>$searchLastNameEntry,
				'first_name'=>$searchFirstNameEntry,
				'store'=>$resultStore
			)
		);
		
		$addClientButton->connect_simple('clicked', array('ROS_Dialogs', 'clientAdd'));
		
		$dialog->show_all();
		$dialog->resize(640, 480);
		
		while($dialog->run() == Gtk::RESPONSE_OK) {
			list($model, $iter) = $resultArea->get_selection()->get_selected();
			if($iter) {
				$dialog->destroy();
				return $model->get_value($iter, 0);
			}
		}
		
		$dialog->destroy();
		return FALSE;
	}
	
	public static function clientAdd($parent = NULL) {
		$dialog = new GtkDialog(
			PROGRAM_NAME.' - Add Client',
			$parent,
			Gtk::DIALOG_MODAL,
			array(
				Gtk::STOCK_OK, Gtk::RESPONSE_OK,
				Gtk::STOCK_CANCEL, Gtk::RESPONSE_CANCEL
			)
		);
		
		$dialog->vbox->pack_start($tmp = new GtkLabel('Add Client'), FALSE);
		$dialog->vbox->pack_start($tmp = new GtkHSeparator(), FALSE);
		
		$lastName = new GtkHBox();
		$lastName->pack_start($tmp = new GtkLabel('Last Name: '), FALSE);
		$lastName->pack_start($lastNameEntry = new GtkEntry());
		
		$firstName = new GtkHBox();
		$firstName->pack_start($tmp = new GtkLabel('First Name: '), FALSE);
		$firstName->pack_start($firstNameEntry = new GtkEntry());
		
		$dialog->vbox->add($lastName);
		$dialog->vbox->add($firstName);
		
		$clientInfoStatic = new GtkCellRendererText();
		$clientInfoEditable = new GtkCellRendererText();
		$clientInfoEditable->set_property('editable', TRUE);
		
		$clientInfoStore = new GtkListStore(GObject::TYPE_LONG, GObject::TYPE_STRING, GObject::TYPE_STRING);
		$clientInfo = new GtkTreeView($clientInfoStore);
		$clientInfo->set_headers_visible(FALSE);
		$clientInfoID = new GtkTreeViewColumn('ID', $clientInfoStatic, 'text', 0);
		$clientInfoID->set_visible(FALSE);
		$clientInfoAttribute = new GtkTreeViewColumn('Attribute', $clientInfoStatic, 'text', 1);
		$clientInfoValue = new GtkTreeViewColumn('Value', $clientInfoEditable, 'text', 2);
		
		$clientInfo->append_column($clientInfoID);
		$clientInfo->append_column($clientInfoAttribute);
		$clientInfo->append_column($clientInfoValue);
		
		$attributes = ROS_DataSource::getClientDetails();
		
		foreach($attributes as $attribute) $clientInfoStore->append(array($attribute['id'], $attribute['name'], ''));
		
		$dialog->vbox->add($clientInfo);
		
		$dialog->show_all();
		$result = $dialog->run();
		
		if($result == Gtk::RESPONSE_CANCEL) {
			$dialog->destroy();
			return FALSE;
		}
		
		ROS_DataSource::addClient($lastNameEntry->get_text(), $firstNameEntry->get_text());
		
		$dialog->destroy();
	}
	
	public static function clientModify($parent = NULL) {
		$client = ROS_Dialogs::clientSearchDialog($parent);
		
		if($client == FALSE) return FALSE;
		
		$client = ROS_DataSource::getClientInfo($client);
		
		$dialog = new GtkDialog(
			PROGRAM_NAME.' - Modify Client',
			$parent,
			Gtk::DIALOG_MODAL,
			array(
				Gtk::STOCK_OK, Gtk::RESPONSE_OK,
				Gtk::STOCK_CANCEL, Gtk::RESPONSE_CANCEL
			)
		);
		
		$dialog->vbox->pack_start($tmp = new GtkLabel('Modify Client'), FALSE);
		$dialog->vbox->pack_start($tmp = new GtkHSeparator(), FALSE);
		
		$dialog->vbox->pack_start($tmp = new GtkLabel("ID: {$client['id']}"));
		
		$lastName = new GtkHBox();
		$lastName->pack_start($tmp = new GtkLabel('Last Name: '), FALSE);
		$lastName->pack_start($lastNameEntry = new GtkEntry($client['last_name']));
		$dialog->vbox->pack_start($lastName);
		
		$firstName = new GtkHBox();
		$firstName->pack_start($tmp = new GtkLabel('First Name: '), FALSE);
		$firstName->pack_start($firstNameEntry = new GtkEntry($client['first_name']));
		$dialog->vbox->pack_start($firstName);
		
		$clientInfoStatic = new GtkCellRendererText();
		$clientInfoEditable = new GtkCellRendererText();
		$clientInfoEditable->set_property('editable', TRUE);
		
		$clientInfoStore = new GtkListStore(GObject::TYPE_LONG, GObject::TYPE_STRING, GObject::TYPE_STRING);
		$clientInfo = new GtkTreeView($clientInfoStore);
		$clientInfo->set_headers_visible(FALSE);
		$clientInfoID = new GtkTreeViewColumn('ID', $clientInfoStatic, 'text', 0);
		$clientInfoID->set_visible(FALSE);
		$clientInfoAttribute = new GtkTreeViewColumn('Attribute', $clientInfoStatic, 'text', 1);
		$clientInfoValue = new GtkTreeViewColumn('Value', $clientInfoEditable, 'text', 2);
		
		$clientInfo->append_column($clientInfoID);
		$clientInfo->append_column($clientInfoAttribute);
		$clientInfo->append_column($clientInfoValue);
		
		$attributes = ROS_DataSource::getClientDetails();
		$info = ROS_DataSource::getClientInfo($client['id']);
		
		foreach($attributes as $attribute) $clientInfoStore->append(array($attribute['id'], $attribute['name'], ''));
		
		$dialog->vbox->add($clientInfo);
		
		$dialog->show_all();
		
		if($dialog->run() == Gtk::RESPONSE_OK) {
			ROS_DataSource::modifyClient($client['id'], $lastNameEntry->get_text(), $firstNameEntry->get_text());
		}
		
		ROS_Interface::refresh();
		
		$dialog->destroy();
	}
	
	public static function setupRooms($parent = NULL) {
		$dialog = new GtkDialog(
			PROGRAM_NAME.' - Setup Rooms',
			$parent,
			Gtk::DIALOG_MODAL,
			array(
				Gtk::STOCK_OK, Gtk::RESPONSE_OK
			)
		);
		
		$dialogLabel = new GtkLabel('Setup Rooms');
		$dialog->vbox->pack_start($dialogLabel, FALSE);
		$dialog->vbox->pack_start($tmp = new GtkHSeparator(), FALSE);
		
		$workspace = new GtkHBox();
		$dialog->vbox->pack_start($workspace);
		
		$roomListRenderer = new GtkCellRendererText();
		$roomListStore = new GtkListStore(GObject::TYPE_LONG, GObject::TYPE_STRING);
		$roomList = new GtkTreeView($roomListStore);
		
		$roomListColumnID = new GtkTreeViewColumn('ID', $roomListRenderer, 'text', 0);
		$roomListColumnName = new GtkTreeViewColumn('Name', $roomListRenderer, 'text', 1);
		
		$roomList->append_column($roomListColumnID);
		$roomList->append_column($roomListColumnName);
		
		$roomListList = ROS_DataSource::getRoomList();
		if(!empty($roomListList)) foreach($roomListList as $room) $roomListStore->append(array($room['id'], $room['room_name']));
		
		$workspace->pack_start($roomList);
		$workspace->pack_start($tmp = new GtkVSeparator(), FALSE);
		
		$buttonBox = new GtkVButtonBox();
		$buttonBox->set_layout(Gtk::BUTTONBOX_SPREAD);
		$buttonBox->set_spacing(25);
		
		$addButton = GtkToolButton::new_from_stock(Gtk::STOCK_ADD);
		$removeButton = GtkToolButton::new_from_stock(Gtk::STOCK_REMOVE);
		$modifyButton = GtkToolButton::new_from_stock(Gtk::STOCK_EDIT);
		
		$buttonBox->add($addButton);
		$buttonBox->add($removeButton);
		$buttonBox->add($modifyButton);
		
		$addButton->connect_simple('clicked', function($list) {
			$dialog = new GtkDialog(
				PROGRAM_NAME.' - Add Room',
				NULL,
				Gtk::DIALOG_MODAL,
				array(
					Gtk::STOCK_OK, Gtk::RESPONSE_OK,
					Gtk::STOCK_CANCEL, Gtk::RESPONSE_CANCEL
				)
			);
		
			$name = new GtkHBox();
			$name->add($tmp = new GtkLabel('Name: '));
			$name->add($nameEntry = new GtkEntry());
		
			$dialog->vbox->add($name);
		
			$dialog->show_all();
			$result = $dialog->run();
		
			if($result == Gtk::RESPONSE_CANCEL) {
				$dialog->destroy();
				return FALSE;
			}
		
			ROS_DataSource::roomAdd($nameEntry->get_text());
		
			$store = $list->get_model();
			$store->clear();
			$roomList = ROS_DataSource::getRoomList();
			if(!empty($roomList)) foreach($roomList as $room) $store->append(array($room['id'], $room['room_name']));
		
			$dialog->destroy();
		}, $roomList);
		
		$removeButton->connect_simple('clicked', function($list) {
			$selection = $list->get_selection();
			if(!$selection) return FALSE;
			list($model, $iter) = $selection->get_selected();
			if(!$model) return FALSE;
		
			ROS_DataSource::roomRemove($model->get_value($iter, 0));
		
			$store = $list->get_model();
			$store->clear();
			$roomList = ROS_DataSource::getRoomList();
			if(!empty($roomList)) foreach($roomList as $room) $store->append(array($room['id'], $room['room_name']));
		}, $roomList);
		
		$modifyButton->connect_simple('clicked', function($list) {
			$dialog = new GtkDialog(
				PROGRAM_NAME.' - Add Room',
				NULL,
				Gtk::DIALOG_MODAL,
				array(
					Gtk::STOCK_OK, Gtk::RESPONSE_OK,
					Gtk::STOCK_CANCEL, Gtk::RESPONSE_CANCEL
				)
			);
		
			$selection = $list->get_selection();
			if(!$selection) return FALSE;
			list($model, $iter) = $selection->get_selected();
		
			if(!$model) return FALSE;
		
			$name = new GtkHBox();
			$name->add($tmp = new GtkLabel('Name: '));
			$name->add($nameEntry = new GtkEntry($model->get_value($iter, 1)));
		
			$dialog->vbox->add($name);
		
			$dialog->show_all();
			$result = $dialog->run();
		
			if($result == Gtk::RESPONSE_CANCEL) {
				$dialog->destroy();
				return FALSE;
			}
		
			ROS_DataSource::roomModify($model->get_value($iter, 0), $nameEntry->get_text());
		
			$store = $list->get_model();
			$store->clear();
			$roomList = ROS_DataSource::getRoomList();
			if(!empty($roomList)) foreach($roomList as $room) $store->append(array($room['id'], $room['room_name']));
		
			$dialog->destroy();
		}, $roomList);
		
		$workspace->pack_end($buttonBox, FALSE);
		
		$dialog->show_all();
		$dialog->resize(640, 480);
		$dialog->run();
		$dialog->destroy();
	}

	public static function aboutDialog($parent = NULL) {
		$aboutDialog = new GtkAboutDialog();
		
		$pixbuf = $aboutDialog->render_icon(Gtk::STOCK_HOME, Gtk::ICON_SIZE_DIALOG);
		$aboutDialog->set_icon($pixbuf);
		
		$aboutDialog->set_logo($aboutDialog->render_icon(Gtk::STOCK_HOME, Gtk::ICON_SIZE_LARGE_TOOLBAR));
		$aboutDialog->set_program_name(PROGRAM_NAME);
		$aboutDialog->set_version(PROGRAM_VERSION);
		$aboutDialog->set_copyright('Copyright (C) 2009');
		$aboutDialog->set_authors(array('Justin Martin'));
		$aboutDialog->run();
		$aboutDialog->destroy();
	}
	
	public static function errorLogDialog($parent = NULL) {
		$dialog = new GtkDialog(
			PROGRAM_NAME.' - Error Log',
			$parent,
			Gtk::DIALOG_MODAL,
			array(Gtk::STOCK_OK, Gtk::RESPONSE_OK)
		);
		$renderer = new GtkCellRendererText();
		$store = new GtkListStore(GObject::TYPE_STRING, GObject::TYPE_STRING, GObject::TYPE_STRING, GObject::TYPE_STRING, GObject::TYPE_STRING);
		$list = new GtkTreeView($store);
		
		$list->append_column($tmp = new GtkTreeViewColumn('Date', $renderer, 'text', 0));
		$list->append_column($tmp = new GtkTreeViewColumn('Type', $renderer, 'text', 1));
		$list->append_column($tmp = new GtkTreeViewColumn('File', $renderer, 'text', 2));
		$list->append_column($tmp = new GtkTreeViewColumn('Line', $renderer, 'text', 3));
		$list->append_column($tmp = new GtkTreeViewColumn('Message', $renderer, 'text', 4));
		
		$errors = ROS_Error::getErrors();
		
		if($errors) foreach($errors as $error) {
			switch($error['no']) {
			case E_USER_ERROR:
				$errorType = 'Error';
				break;
			case E_USER_WARNING:
				$errorType = 'Warning';
				break;
			case E_USER_NOTICE:
				$errorType = 'Notice';
				break;
			default:
				$errorType = 'Unknown';
				break;
			}
			$store->append(array(date('F j, Y, g:i a', $error['time']), $errorType, basename($error['file']), $error['line'], $error['str']));
		}
		
		$viewport = new GtkScrolledWindow();
		$viewport->add($list);
		
		$dialog->vbox->add($viewport);
		$dialog->show_all();
		$dialog->resize(800, 600);
		$dialog->run();
		$dialog->destroy();
	}
	
	public static function paymentDialog($parent = NULL) {
		$dialog = new GtkDialog(
			PROGRAM_NAME.' - Payment',
			$parent,
			Gtk::DIALOG_MODAL,
			array(Gtk::STOCK_OK, Gtk::RESPONSE_OK)
		);
		$dialog->resize(800, 600);
		
		$workspace = new GtkHBox();
		$dialog->vbox->pack_start($workspace);
		
		$sideBar = new GtkVBox();
		$workspace->pack_start($sideBar, FALSE);
		
		$paymentDesc = new GtkVBox();
		$sideBar->pack_start($paymentDesc, FALSE);
		
		list($model, $iter) = ROS_Interface::getReservationList()->getWidget()->get_selection()->get_selected();
		$reservation = ROS_DataSource::getReservationInfo($model->get_value($iter, 0));
		$client = ROS_DataSource::getClientInfo($reservation['client_id']);
		$room = ROS_DataSource::getRoomInfo($reservation['room_id']);
		
		$paymentDesc->pack_start(new GtkLabel("Client ID: {$client['id']}"));
		$paymentDesc->pack_start(new GtkLabel("Client Name: {$client['last_name']}, {$client['first_name']}"));
		$paymentDesc->pack_start(new GtkLabel("Reservation ID: {$reservation['id']}"));
		$paymentDesc->pack_start(new GtkLabel("Room: {$room['room_name']}"));
		
		$sideBar->pack_start(new GtkHSeparator(), FALSE);
		
		$methodSelect = new GtkVBox();
		$sideBar->pack_start($methodSelect, FALSE);
		
		$methodSelect->pack_start(new GtkLabel('Payment Method:'), FALSE);
		$combo = new GtkComboBox();
		$list = new GtkListStore(GObject::TYPE_LONG, GObject::TYPE_STRING);
		$combo->set_model($list);
		$renderer = new GtkCellRendererText();
		$combo->pack_start($renderer);
		$combo->set_attributes($renderer, 'text', 1);
		$methodSelect->pack_start($combo, FALSE);
		
		$methods = ROS_DataSource::getPaymentMethods();
		if($methods) foreach($methods as $method) $list->append(array($method['id'], $method['name']));
		
		$paymentArea = new GtkFrame();
		$paymentArea->add(new GtkLabel('Please select a payment method'));
		$workspace->pack_end($paymentArea);
		
		$combo->connect('changed', function($combo, $paymentArea) {
			$paymentArea->remove($paymentArea->get_child());
			$paymentArea->add(new GtkLabel('Loading..'));
		
			$model = $combo->get_model();
			$iter = $combo->get_active_iter();
		
			$method = ROS_DataSource::getMethodInfo($model->get_value($iter, 0));
			$method = $method[0];
		
			include("includes/classes/Payment/Methods/{$method['filename']}");
		
			$method = new $method['class_name'];
		
			$widget = $method->getWidget();
			$paymentArea->remove($paymentArea->get_child());
			$paymentArea->add($widget);
		}, $paymentArea);
		
		$dialog->show_all();
		$dialog->run();
		$dialog->destroy();
	}
}
