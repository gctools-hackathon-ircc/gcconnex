<?php

	/**
	 * Test plugin
	 *
	 * These parameters are required for the event API, but we won't use them:
	 * 
	 * @param unknown_type $event
	 * @param unknown_type $object_type
	 * @param unknown_type $object
	 */

		function test_init($event, $object_type, $object = null) {
			
				global $CONFIG;
				
			// Let's add to the pageshell view.
				extend_view("pageshell", "testplugin/pageshell");
				set_view_location("testplugin/pageshell",$CONFIG->pluginspath . "test/views/");
			
			// Test menu item
				add_menu("Test plugin",$CONFIG->wwwroot . "mod/test/",array(
					menu_item("Main test plugin page",$CONFIG->wwwroot."mod/test/"),
												));
				
		}

	// Make sure test_init is called on initialisation
		register_event_handler('init','system','test_init');
		
?>