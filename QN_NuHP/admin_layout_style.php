<?php

function layoutImage( $sec = "ThemeLayout", $tag = FALSE ) {
	$src = THEMEDIR . "/Layout/$sec.gif";
	$img = sprintf( '<img alt="" src="%s" />', $src );
	return $tag ? $img : $src;
}

function layoutImageStyle_Widgets() {
	$colList = array(
		"colextra_content" => "RotatorExtra",
		"coldx_sidebar" => "Rightbar",
		"coldx_sidebar_articolo" => "Rightbar",
		"archive_widgets" => "Ticker",
		"headline_widgets" => "HeadlineWidgets",
		"articles_top_widgets" => "HeadlineWidgets",
		"articles_bottom_widgets" => "ArtWidgets",
		"articles_bottom_home_widgets" => "ArtWidgets",
		"article_top_widgets" => "HeadlineWidgets",
		"article_bottom_widgets" => "ArtWidgets",
		"midline_widgets" => "MidWidgets",
		"topAdvLots" => "Leaderboard",
		"manchette_1" => "Manchette1",
		"manchette_2" => "Manchette2",
		"ticker" => "Ticker",
		"midbox_1" => "Square1",
		"midbox_2" => "Square2",
		"endingcontent" => "FooterStats",
		"endingpage" => "FooterStats",
		"fotogallery" => "Fotogallery",
		"vetrine" => "Vetrine"
	);
	$str = ".wp-admin .widgets-holder-wrap #%s .sidebar-description";
	$img = " { background-image: url('%s'); }\n";
	echo "<style type=\"text/css\">\n";
	foreach( array_keys( $colList ) as $i => $k ) {
		if( $i ) echo ",\n";
		printf( $str, $k );
	}
	echo " {\n"
		."	border-radius: 4px;\n"
		."	padding: 187px 8px 0 8px;\n"
		."	width: 234px;\n"
		."	background-color: #CCC;\n"
		."	background-repeat: no-repeat;\n"
		."	text-shadow: #DEDEDE 1px 1px 1px;\n"
		."	font-weight: bold;\n"
		."}\n";
	foreach( $colList as $k => $v ) {
		printf( $str, $k );
		printf( $img, layoutImage( $v ) );
	}
	echo "</style>\n";
}

function layoutImageStyle() {
	$page = basename( $_SERVER["REQUEST_URI"] );
	if( $page == "widgets.php" ) layoutImageStyle_Widgets();
}
add_action( 'admin_head', 'layoutImageStyle' );
