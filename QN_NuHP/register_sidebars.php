<?php

// SIDEBARS
register_sidebar( # HEAD
	array(
		"name" => "Head",
		"id" => "head_sidebar",
		"description" => "Sezione HTML Head",
		"before_widget" => "", "after_widget" => "",
		"before_title" => "", "after_title" => ""
	)
);

/*register_sidebar( # ROTATOR
	array(
		"name" => "Colonna Rotator Extra",
		"id" => "colextra_content",
		"description" => "Colonna extra del Rotator",
		"before_widget" => '<div class="boxdx">',
		"after_widget" => "</div>",
		"before_title" => '<span class="titolo_box">',
		"after_title" => "</span>"
	)
);*/

$default_before_title = '<div class="block1"><ul class="nav-tabs">';
$default_before_title .= '<li class="active"><i class="icon-blank"></i> ';
$default_after_title = '</li></ul></div>';

foreach(array("", " ARTICOLO") as $k) {
	register_sidebar( # BARRE DX
		array(
			"name" => "ColonnaDestra$k",
			"id" => "coldx_sidebar" . str_replace(' ', '_', strtolower($k)),
			"description" => "Colonna di destra$k",
			"before_widget" => '<div class="row"><div class="block1">',
			"after_widget" => "</div></div>",
			"before_title" => '<ul class="nav-tabs"><li class="active"><i class="icon-blank"></i>',
			"after_title" => '</li></ul></div><div class="block1 widget">'
		)
	);
}
foreach( array(
				"headline", /*"archive",*/ "articles top", "articles bottom",
				"articles bottom home", "article top", "article bottom", "midline"
			) as $k ):
register_sidebar( # FULLINEs
	array(
		"name" => ucwords($k) . " Widgets",
		"id" => str_replace(" ", "_", $k) . "_widgets",
		"description" => "Spazi widget orizzontali",
		"before_widget" => '<div class="widget_container">',
		"after_widget" => "</div>",
		"before_title" => $default_before_title,
		"after_title" => $default_after_title
	)
);
endforeach;
register_sidebar( # ADV: LEADERBOARD, BACKGROUND, POPUP...
	array(
		"name" => "TopAdvertising",
		"id" => "topAdvLots",
		"description" => "Spazi pubblicitari: background, popup, leaderboard",
		"before_widget" => "",
		"after_widget" => ""
	)
);
/*for( $i = 1; $i <= 2; $i++ ) {
	register_sidebar( # ADV: MANCHETTEs
		array(
			"name" => "Manchette$i",
			"id" => "manchette_$i",
			"description" => "Spazi pubblicitari: manchette",
			"before_widget" => "",
			"after_widget" => ""
		)
	);
}*/
register_sidebar( # ADV: TICKER
	array(
		"name" => "Ticker",
		"id" => "ticker",
		"description" => "Spazi pubblicitari: ticker",
		"before_widget" => "",
		"after_widget" => ""
	)
);
/*for( $i = 1; $i <= 2; $i++ ) {
	register_sidebar( # ADV: MIDBOXes
		array(
			"name" => "Midbox$i",
			"id" => "midbox_$i",
			"description" => "Spazi pubblicitari: midbox",
			"before_widget" => "",
			"after_widget" => ""
		)
	);
}*/
register_sidebar( # CHIUSURA PAGINA / PRE-FOOTER
	array(
		"name" => "Chiusura pagina",
		"id" => "endingcontent",
		"description" => "Spazio libero sopra al footer",
		"before_widget" => '',
		"after_widget" => '',
		"before_title" => $default_before_title,
		"after_title" => $default_after_title
	)
);
register_sidebar( # ADV: CHIUSURA PAGINA
	array(
		"name" => "Chiusura documento",
		"id" => "endingpage",
		"description" => "Ultimi spazi pubblicitari, statistiche e tracciamento",
		"before_widget" => "",
		"after_widget" => ""
	)
);

function qn2011_sidebar( $id = null ) {
	global $wp_registered_sidebars, $qn_current_sidebar;
	if( ! $id ) return false;
	if ( is_int($id) ) $index = "sidebar-$index";
	else {
		$index = sanitize_title($id);
		foreach ( (array) $wp_registered_sidebars as $key => $value ) {
			if ( sanitize_title($value['name']) == $index ) {
				$index = $key;
				break;
			}
		}
	}
	$sidebar = @$wp_registered_sidebars[ $index ];
	if( $sidebar ) {
		$qn_current_sidebar = $sidebar["id"];
		do_action( "qn_sidebar", $sidebar );
	}
	dynamic_sidebar( $id );
	return true;
}

function salutiamo_i_widget( $widgets ) {
	global $qn_current_sidebar, $qn_toast_widgets;
	if( array_key_exists($qn_current_sidebar, $widgets) ) {
		if( in_array($qn_current_sidebar, $qn_toast_widgets) )
			$widgets[$qn_current_sidebar] = array();
		elseif( array_key_exists($qn_current_sidebar, $qn_toast_widgets) ) {
			$erase = array_unique((array)@$qn_toast_widgets[$qn_current_sidebar]);
			//echo "\n<!-- ".print_r($erase,true).print_r($widgets[$qn_current_sidebar],true)." -->\n";
			foreach( $widgets[$qn_current_sidebar] as $i => $wid )
				if( in_array(preg_replace("/-\d+$/","",$wid), $erase) )
					unset( $widgets[$qn_current_sidebar][$i] );
		}
	}
	return $widgets;
}

$GLOBALS["qn_toast_widgets"] = array();

