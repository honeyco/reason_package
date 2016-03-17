<?php
	reason_include_once( 'minisite_templates/modules/default.php' );
	//reason_include_once( 'classes/timeliner.php' );

	$GLOBALS[ '_module_class_names' ][ basename( __FILE__, '.php' ) ] = 'TimelineModule';
	
	class TimelineModule extends DefaultMinisiteModule
	{

		function has_content()
		{
			$site_id = $this->site_id;
			$es = new entity_selector($site_id);
			$es->add_type( id_of('timeline_type'));
			$es->add_right_relationship($this->cur_page->id(), relationship_id_of('page_to_timeline'));
			$es->add_rel_sort_field($this->cur_page->id(), relationship_id_of('page_to_timeline'));
			$es->set_order('rel_sort_order'); 
			$posts = $es->run_one();
			if (count($posts) > 0)
			{
				return true;
			}
			return false;
		}
		
		function run()
		{
			$site_id = $this->site_id;
			$es = new entity_selector( $site_id );
			$es->add_type( id_of('timeline_type'));
			$es->add_right_relationship($this->cur_page->id(), relationship_id_of('page_to_timeline'));
			$es->add_rel_sort_field($this->cur_page->id(), relationship_id_of('page_to_timeline'));
			$es->set_order('rel_sort_order'); 
			$timelines = $es->run_one();
			
			foreach($timelines AS $timeline)
			{
				$es = new entity_selector( $this->site_id );
				$es->add_type( id_of('timeline_item_type') );
				$es->add_right_relationship( $timeline->_id, relationship_id_of('timeline_to_timeline_item'));
				$timeline_items = $es->run_one();
				
				// draw the timeline 
			}
		}
	}
?>