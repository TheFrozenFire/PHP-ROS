<?php
abstract class Payment_Method {
	public function getWidget() {
		list($model, $iter) = ROS_Interface::getReservationList()->getWidget()->get_selection()->get_selected();
		$reservation_id = $model->get_value($iter, 0);
	
		$widget = new GtkVBox();
		
		$widget->pack_start(new GtkLabel('Process Payment'), FALSE);
		$widget->pack_start(new GtkHSeparator(), FALSE);
		
		$owingBox = new GtkHBox();
		$owingBox->pack_start(new GtkLabel('Amount Owing: '));
		$owingBox->pack_start($owingEntry = new GtkEntry(0.00));
		$owingEntry->set_editable(FALSE);
		$widget->pack_start($owingBox);
		
		$amountBox = new GtkHBox();
		$amountBox->pack_start(new GtkLabel('Amount Received: '));
		$amountBox->pack_start($amountEntry = new GtkEntry(0.00));
		$widget->pack_start($amountBox);
		
		$changeBox = new GtkHBox();
		$changeBox->pack_start(new GtkLabel('Change: '));
		$changeBox->pack_start($changeEntry = new GtkEntry(0.00));
		$widget->pack_start($changeBox);
		
		$widget->show_all();
		
		return $widget;
	}
}
?>
