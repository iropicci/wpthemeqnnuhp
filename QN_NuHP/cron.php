<?php

add_action('wp', 'qnnuhp_prefix_setup_schedule');
function qnnuhp_prefix_setup_schedule() {
	$qn_cron = array(
		'qnnuhp_cron_fonticons' => 'daily',
	);
	foreach($qn_cron as $callback => $recurrence) {
		if(wp_next_scheduled($callback)) continue;
		wp_schedule_event(time(), $recurrence, $callback);
	}
}

add_action('qnnuhp_cron_fonticons', 'qnnuhp_register_fonticons');
function qnnuhp_register_fonticons() {
	$cssfonticons = qnnuhp_get_fonticons_from_css();
	if(!$cssfonticons) return FALSE;
	return update_option('qnnuhp_fonticons_list', $cssfonticons);
}

function qnnuhp_get_fonticons_from_css() {
	$csslink = 'http://www.stqn.it/nuhp_static/css/fontastic.css';
	$css = @file_get_contents($csslink);
	if(!preg_match_all('/\.icon-([^\s:{]+)/i', $css, $m, PREG_PATTERN_ORDER)) return FALSE;
	return $m[1];
}

# DECOMMENTARE PER CHIAMARE LA FUNZIONE
# DIRETTAMENTE SENZA PASSARE DAL CRON E
# AGGIORNARE L'ARRAY DI FONTASTIC ICONS
//qnnuhp_register_fonticons();

