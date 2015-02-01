<?php

if ( ! class_exists('WP_List_Table') ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class MP_Addons_List_Table extends WP_List_Table {
	function __construct() {
		global $status, $page;
                
    //Set parent defaults
    parent::__construct(array(
    	'singular' => 'mp_addon',	//singular name of the listed records
			'plural' => 'mp_addons',	//plural name of the listed records
			'ajax' => false						//does this table support ajax?
    ));	
	}
	
	function get_columns() {
		return array(
			'cb' => '<input type="checkbox" />',
			'label' => __('Name', 'mp'),		
			'desc' => __('Description', 'mp'),
			'status' => __('Status', 'mp'),
			'settings' => '',
		);
	}
	
	function get_sortable_columns() {
		return array();
	}
	
	function get_bulk_actions() {
		return array(
			'enable' => __('Enable', 'mp'),
			'disable' => __('Disable', 'mp'),
		);
	}
	
	function get_data() {
		$data = array();
		$addons = MP_Addons::get_instance()->get_registered();
				
		foreach ( $addons as $addon ) {
			if ( MP_Addons::get_instance()->is_addon_enabled($addon->class) ) {
				$enabled = true;
				$status = '<a class="button mp-enable-disable-addon" title="' . __('Disable add-on', 'mp') . '" href="#"><span class="mp-addon-status enabled"></span>' . __('Enabled', 'mp') . '</a>';
			} else {
				$enabled = false;
				$status = '<a class="button mp-enable-disable-addon" title="' . __('Enable add-on', 'mp') . '" href="#"><span class="mp-addon-status disabled"></span>' . __('Disabled', 'mp') . '</a>';	
			}

			$data[] = array(
				'ID' => $addon->class,
				'label' => $addon->label,
				'desc' => $addon->desc,
				'class' => $addon->class,
				'status' => $status,
				'enabled' => $enabled,
			);
		}
		
		return $data;
	}
	
	function process_bulk_actions() {
		$ids = mp_get_get_value('mp_addon');
		
		if ( ! $ids ) {
			// no ids to process - bail
			return false;
		}
		
		$count = count($ids);
		
		switch ( $this->current_action() ) {
			case 'enable' :
				MP_Addons::get_instance()->enable($ids);
				$notice = sprintf(_n('1 add-on enabled', '%s add-ons enabled', $count, 'mp'), $count);
			break;
			
			case 'disable' :
				MP_Addons::get_instance()->disable($ids);
				$notice = sprintf(_n('1 add-on disabled', '%s add-ons disabled', $count, 'mp'), $count);
			break;
		}
		
		echo '<div class="updated"><p>' .  $notice . '</p></div>';
	}
	
	function prepare_items() {
		$this->process_bulk_actions();
		$columns = $this->get_columns();
		$hidden = array();
		$sortable = $this->get_sortable_columns();
		
		$this->_column_headers = array($columns, $hidden, $sortable);
		$this->items = $this->get_data();
	}
	
	function column_cb( $item ) {
		return '<input type="checkbox" name="mp_addon[]" value="' . $item['ID'] . '" />';
	}
	
	function column_label( $item ) {
		return $item['label'];
	}
	
	function column_desc( $item ) {
		return $item['desc'];
	}
	
	function column_status( $item ) {
		return $item['status'];
	}
	
	function column_settings( $item ) {
		return '<a href="' . add_query_arg('addon', $item['class']) . '">' . __('Settings', 'mp') . '</a>';
	}
}