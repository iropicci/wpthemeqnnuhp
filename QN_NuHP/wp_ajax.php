<?php

/*
 * Here are the custom Ajax responses to register
 */

global $qn_nuhp_modules;
$qn_nuhp_modules = array(
	'home-articles' => array(
			'mod_realtime',
			'mod_editorial',
			'mod_news',
			'mod_newssmall',
            'mod_newsedition',
		),
	'home-main-column' => array(
			'mod_quadbox',
			'mod_catview',
			'mod_mosaic',
		),
);

global $qn_nuhp_modules_desc;
$qn_nuhp_modules_desc = array(
	'mod_realtime' => array('title' => 'Tempo reale', 'desc' => 'Rotator di notizie tempo reale.'),
	'mod_editorial' => array('title' => 'Editoriali', 'desc' => 'Link ad editoriali e blog'),
	'mod_news' => array('title' => 'Ultime notizie', 'desc' => 'Flusso ultime notizie'),
	'mod_newssmall' => array('title' => 'Notizie mignon', 'desc' => 'Flusso notizie formato piccolo'),
	'mod_newsedition' => array('title' => 'Notizie edizione', 'desc' => 'Flusso ultime notizie (una per riga)'),
	'mod_quadbox' => array('title' => 'Box quadrupli', 'desc' => 'Quattro notizie mostrate in riga'),
	'mod_catview' => array('title' => 'Sezione categoria', 'desc' => 'Box con ultime notizie da singola categoria'),
	'mod_mosaic' => array('title' => 'Mosaico foto', 'desc' => 'Composizione ultime notizie con foto'),
);

function qn_nuhp_check_module($area, $module=FALSE) {
	$modules = $GLOBALS['qn_nuhp_modules'];
	if(!array_key_exists($area, $modules)) return FALSE;
	if($module) return in_array($module, $modules[$area]);
	return TRUE;
}

function qn_get_nuhp_area_for_module($module) {
	$modules = $GLOBALS['qn_nuhp_modules'];
	foreach($modules as $area => $mods) {
		if(!in_array($module, $mods)) continue;
		return $area;
	}
	return FALSE;
}

function qn_get_nuhp_module_from_widget($widget) {
	$filter = preg_replace('/^\w+-\d+_|-\d+$/', '', $widget);
	return 'mod_' . $filter;
}

function qn_get_nuhp_area_from_sidebar($sidebar) {
	$filter = preg_replace('/^qnnuhp-modules_/i', '', $sidebar);
	return $filter;
}

add_action('wp_ajax_widgets-order', 'qn_ajax_widgets_order', 0);
function qn_ajax_widgets_order() {
	$is_saving_modules = check_ajax_referer('qnnuhp-modules', 'savewidgets', FALSE);
	if($is_saving_modules):
		if(!current_user_can('edit_theme_options')) wp_die(-1);
		unset($_POST['savewidgets'], $_POST['action']);
		if(is_array($_POST['sidebars'])) {
			$areas = array();
			foreach($_POST['sidebars'] as $key => $val) {
				$area = qn_get_nuhp_area_from_sidebar($key);
				if(!qn_nuhp_check_module($area)) continue;
				$sb = array();
				if(!empty($val)) {
					$val = explode(',', $val);
					foreach($val as $k => $v) {
						if(strpos($v, 'widget-') === false) continue;
						$v = substr($v, strlen('widget-'));
						$mod = qn_get_nuhp_module_from_widget($v);
						if(!qn_nuhp_check_module($area, $mod)) continue;
						$sb[$k] = substr($v, strpos($v, '_'));
					}
				}
				$areas[$area] = $sb;
			}
			$ret = qn_set_areas_modules($areas) ? 1 : -1;
			wp_die($ret);
		}
		wp_die(-1);
	endif;
}

function qn_set_areas_modules($areas) {
	$option_name = 'qnnuhp_areas_modules';
	$oldareas = get_option($option_name);
    $newareas = array();
    foreach($areas as $spot => $area) {
        $newareas[$spot] = array();
        foreach($area as $module) {
            if(preg_match('/\d+$/', $module, $multi_string)) {
                if((int)$multi_string[0] >= 9000) continue;
            }
            $newareas[$spot][] = $module;
        }
    }
	if($oldareas === FALSE) return add_option($option_name, $newareas);
	if($oldareas === $newareas) return TRUE;
	return update_option($option_name, $newareas);
}

add_action('wp_ajax_save-widget', 'qn_ajax_save_widget', 0);
function qn_ajax_save_widget() {
	$is_saving_modules = check_ajax_referer('qnnuhp-modules', 'savewidgets', FALSE);
	if($is_saving_modules):
		if(!current_user_can('edit_theme_options') || !isset($_POST['id_base'])) wp_die(-1);
		unset($_POST['savewidgets'], $_POST['action']);
		// get post params
		$id_base = $_POST['id_base'];
		$module_id = qn_get_nuhp_module_from_widget($id_base);
		$widget_id = $_POST['widget-id'];
		$sidebar_id = $_POST['sidebar'];
		$area_id = qn_get_nuhp_area_from_sidebar($sidebar_id);
		$multi_number = !empty($_POST['multi_number']) ? (int)$_POST['multi_number'] : 0;
        if($multi_number >= 9000) wp_die("<p><strong>Error:</strong> This module cannot be manually edited. Sorry bro.</p>");
		$settings = isset($_POST['widget-' . $id_base]) && is_array($_POST['widget-' . $id_base]) ? $_POST['widget-' . $id_base] : false;
		$errors = array(
			1 => __('The module doesn\'t belong to the selected area.'),
			2 => __('The module can\'t be deleted for it doesn\'t exist.'),
			3 => __('This module cannot be placed this way. Try another solution.'),
			4 => __('The deleting operation failed. Please reload the page and try again.'),
			5 => __('An unidentified error has occurred. Please reload the page and try again.'),
		);
		// get areas
		if(!qn_nuhp_check_module($area_id, $module_id)) wp_die("<p><strong>Error:</strong> {$errors[1]}</p>");
		$modules = get_option('qnnuhp_modules_options');
		if($modules === FALSE) {
			add_option('qnnuhp_modules_options', array());
			$modules = array();
		}
		// delete
		if(!empty($_POST['delete_widget'])) {
			$areas = get_option('qnnuhp_areas_modules', array());
			$area = isset($areas[$area_id]) ? $areas[$area_id] : array();
			if(!isset($modules[$widget_id])) wp_die("<p><strong>Error:</strong> {$errors[2]}</p>");
			$area = array_diff($area, array($widget_id));
			$_POST = array(
				'sidebar' => $sidebar_id,
				'widget-' . $id_base => array(),
				'the-widget-id' => $widget_id,
				'delete_widget' => '1'
			);
		} elseif($settings && preg_match('/__i__|%i%/', key($settings))) {
			if(!$multi_number) wp_die("<p><strong>Error:</strong> {$errors[3]}</p>");
			$_POST['widget-' . $id_base] = array($multi_number => array_shift($settings));
			$widget_id = $id_base . '-' . $multi_number;
			$sidebar[] = $widget_id;
		}
		$_POST['widget-id'] = $sidebar;
		$defaults = theme_fp_defaultdata();
		$new_module = array();
		foreach($defaults[$module_id] as $setting => $setting_value) {
			list($set_type, $set_default) = $setting_value;
			$new_module[$setting] = qn_default_value(@$settings[$multi_number][$setting], $set_type, $set_default);
		}
		$modules[$widget_id] = $new_module;
		if(!empty($_POST['delete_widget'])) {
			$areas[$area_id] = $area;
			wp_die(qn_set_areas_modules($areas) ? "deleted:$widget_id" : "<p><strong>Error:</strong> {$errors[4]}</p>");
		}
		if(!empty($_POST['add_new'])) wp_die();
		$updated = update_option('qnnuhp_modules_options', $modules);
		if($updated) {
			ob_start();
				qn_module_inner_form($module_id, "widget-{$id_base}[{$multi_number}]", $new_module);
				$inner = ob_get_contents();
			ob_end_clean();
			wp_die($inner);
		}
		wp_die("<p><strong>Error:</strong> {$errors[5]}</p>");
	endif;
}

