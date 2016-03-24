<?php
	reason_include_once( 'minisite_templates/modules/default.php' );
	//reason_include_once( 'classes/timeliner.php' );

	$GLOBALS[ '_module_class_names' ][ basename( __FILE__, '.php' ) ] = 'TimelineModule';

	class TimelineModule extends DefaultMinisiteModule
	{
		function init( $args = array() )
		{
			$head_items = $this->get_head_items();
			$head_items->add_javascript('https://cdn.knightlab.com/libs/timeline3/latest/js/timeline.js');
			$head_items->add_stylesheet('https://cdn.knightlab.com/libs/timeline3/latest/css/timeline.css');
			$head_items->add_stylesheet('https://cdn.knightlab.com/libs/timeline3/latest/css/fonts/font.abril-droidsans.css');
		}

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

			if (!empty($timelines))
			{
				$timeline = reset($timelines);
				$timeline_dom_id = uniqid('timeline_');
				$json = [
					'title' => [],
					'events' => [],
					'eras' => [],
					'scale' => 'human'
				];
				$es = new entity_selector($this->site_id);
				$es->add_type(id_of('timeline_item_type'));
				$es->add_right_relationship($timeline->_id, relationship_id_of('timeline_to_timeline_item'));
				$timeline_items = $es->run_one();

				foreach($timeline_items as $timeline_item)
				{
					$timeline_item_json = [
						'start_date' => [
							'year'  => intval(date('Y', strtotime($timeline_item->get_value('start_date')))),
							'month' => intval(date('m', strtotime($timeline_item->get_value('start_date')))),
							'day'   => intval(date('d', strtotime($timeline_item->get_value('start_date'))))
						],
						'text' => [
							'text' => $timeline_item->get_value('text')
						]
					];

					if ($timeline_item->get_value('media') == 'reason_image')
					{
						$es = new entity_selector($this->site_id);
						$es->add_type(id_of('image'));
						$es->add_right_relationship($timeline_item->_id, relationship_id_of('timeline_item_to_image'));
						$es->set_num(1);
						$images = $es->run_one();

						if (!empty($images))
						{
							$image = reset($images);
							// print_r($image);
							// echo '<img src="' . WEB_PHOTOSTOCK . $image->_id . '.' . $image->get_value('image_type') . '"/></a>'."<br>\n";
							// echo $image->get_value('description')."<br><br>\n";

							$timeline_item_json['media'] = [
								'url'     => WEB_PHOTOSTOCK . $image->_id . '.' . $image->get_value('image_type'),
								'caption' => $image->get_value('description')
							];
							// $json['events'][] = $timeline_item_json;
						}
					}
					else if ($timeline_item->get_value('media') == 'reason_media_work')
					{
						$es = new entity_selector($this->site_id);
						$es->add_type(id_of('av'));
						$es->add_right_relationship($timeline_item->_id, relationship_id_of('timeline_item_to_media_work'));
						$es->set_num(1);

						if (!empty($media_works))
						{
							$media_works = $es->run_one();
							$media_work = reset($media_works);
							var_dump($media_work);
							echo ' ***** end media_work *****<br><br>';


							$es = new entity_selector($this->site_id);
							$es->add_type(id_of('av_file'));
							$es->add_right_relationship($media_work->id(), relationship_id_of('av_to_av_file'));
							$es->set_order('av.media_format ASC, av.av_part_number ASC');
							$es->set_num(1);
							$media_files = $es->run_one();
							if (!empty($media_files))
							{
								$media_file = reset($media_files);
								print_r($media_file);
								echo ' ***** end media_file *****<br><br>';
							}
						}
					}
					else if ($timeline_item->get_value('media') == 'other')
					{
						$timeline_item_json['media'] = [
							'url' => $timeline_item->get_value('other_media')
						];
					}

					$json['events'][] = $timeline_item_json;
				}

				$json = json_encode($json);

				echo "
				<div id=\"$timeline_dom_id\"></div>
				<script>
					if (!window.timelines) window.timelines = []
					timelines.push(new TL.Timeline('$timeline_dom_id', $json))
				</script>";
			}
		}
	}
