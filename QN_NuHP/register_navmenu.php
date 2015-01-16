<?php

// NAVIGATOR WALKER CLASS
class NavMenu_Walker extends Walker_Nav_Menu {
	/**
	 * Start the element output.
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param object $item Menu item data object.
	 * @param int $depth Depth of menu item. May be used for padding.
	 * @param array $args Additional strings.
	 * @return void
	 */
	function start_el( &$output, $item, $depth, $args ) {
		$classes = empty ( $item->classes ) ? array () : (array)$item->classes;
		if( ! empty( $args->item_class ) ) $classes[] = $args->item_class;
		$class_names = implode( ' ', apply_filters( 'nav_menu_css_class' , array_filter( $classes ), $item ) );
		$freehtml = "";
		if( ! empty ( $class_names ) ) $class_names = ' class="'. esc_attr( $class_names ) . '"';
		if( ! empty( $item->description ) ) {
			if( $item->url === "http://DESC" ) {
				if( preg_match( "/^{cats_select_js(?:\|[^:]+:[^|}]+)*}$/", $item->description, $m ) ) {
					$args = array();
					if( preg_match_all( "/\|([^:]+):([^|}]+)/", $m[0], $m2, PREG_SET_ORDER ) )
						foreach( $m2 as $mm )
							$args[ $mm[1] ] = $mm[2];
					$args["echo"] = 0;
					$select = wp_dropdown_categories( $args );
					$re = array( "/<select([^>]*)>/", "<select$1 onchange='return this.form.submit()'>" );
					$freehtml = '<form style="display:inline;padding:0 10px" action="' . get_bloginfo('url') . '">';
					$freehtml .= preg_replace( $re[0], $re[1], $select );
					$freehtml .= '<noscript><input type="submit" value="Vai" /></noscript></form>';
				}
				$width = (float)preg_replace( "/\D+/", "", $item->attr_title );
			} else {
				$width = (float)preg_replace( "/\D+/", "", $item->description );
			}
			//if( $width ) $class_names .= ' style="width:' . $width . 'px"';
		}
		if (preg_match('/<img[^>]*alt="([^"]*)"[^>]*\/>/i', $item->title, $matches)) {
			$title = $matches[1];
		} else {
			$title = $item->title;
		}
		$title = preg_replace( "/\W+/", "", strtolower( $title ) );
		$id = "navmenu-{$args->theme_location}-" . $title;
		$output .= "<li id=\"{$id}\" {$class_names}>";

		$attributes = '';
		if( ! empty( $item->attr_title ) ) $attributes .= ' title="' . esc_attr( $item->attr_title ) .'"';
		if( ! empty( $item->target ) ) $attributes .= ' target="' . esc_attr( $item->target ) .'"';
		if( ! empty( $item->xfn ) ) $attributes .= ' rel="' . esc_attr( $item->xfn ) .'"';
		if( ! empty( $item->url ) ) $attributes .= ' href="' . esc_attr( $item->url ) .'"';

		$title = $freehtml ? $freehtml : apply_filters( 'the_title', $item->title, $item->ID );

		$linkTxt = $args->link_before . $title;
		$linkAfter = !@$item->is_last ? $args->link_after : '';

		$rePre = preg_match( "|^https?://[^/]+|i", $item->url, $m ) ? $m[0] : "http://" . $_SERVER["HTTP_HOST"];
		$url = preg_replace( "|^$rePre|", "", $item->url );
		$linkRe = "@^$rePre(?:/category)?" . preg_replace( "/([@\[\]()+?|*])/", '\$1', $url ) . '(?:\b|$)@';

		global $urlorig;
		if( isset( $_REQUEST["urlorig"] ) ) {
			$urlorig = $_REQUEST["urlorig"];
			unset( $_REQUEST["urlorig"], $_GET["urlorig"] );
			foreach( array( "QUERY_STRING", "REQUEST_URI" ) as $u ) {
				$_SERVER[ $u ] = preg_replace( "/&?urlorig=[^&]+/i", "", $_SERVER[ $u ] );
				$_SERVER[ $u ] = trim( $_SERVER[ $u ], "&" );
			}
		}

		$uri = $urlorig ? "/$urlorig" : $_SERVER["REQUEST_URI"];
		$uriRe = "http://{$_SERVER["HTTP_HOST"]}" . $uri;
		if( preg_match( $linkRe, $uriRe ) ) $linkTxt = "<strong>$linkTxt</strong>";

		$item_output = $freehtml ? $linkTxt : "<a $attributes>$linkTxt</a>";
		$item_output = $args->before . $item_output . $linkAfter . $args->after;

		// Since $output is called by reference we don't need to return anything.
		$output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );
	}
}

// MENU DI NAVIGAZIONE
function display_menu( $location = FALSE ) {
	if( ! $location ) return;
	if( has_action("navmenu_qn_$location") ) {
		do_action("navmenu_qn_$location");
		return true;
	}
	if( ! has_nav_menu( $location ) ) {
		do_action( "navmenu_fallback", $location );
		return TRUE;
	}
	$args = array(
		"theme_location" => $location,
		"container" => FALSE,
		"depth" => 1,
		"walker" => new NavMenu_Walker
	);
	switch( $location ) {
		case "navmenu":
			$args["menu_class"] = 'nav nav-tabs section-sub-menu-nav';
			$args["menu_id"] = 'navmenu';
			$args["link_after"] = '<span class="sep">/</span>';
			break;
		case "sottomenu":
			$args["menu_class"] = 'QuickLinksCitta nav nav-tabs';
			$args["menu_id"] = 'sottomenu';
			$args["link_after"] = '<span class="sep">/</span>';
			break;
	}
	wp_nav_menu( $args );
}

// ACTIVATE NAV MENUS
function register_my_menus() {
	register_nav_menus(
		array(
			"navmenu" => "Menu di navigazione",
			"sottomenu" => "Sottomenu"
		)
	);
}
add_action( "init", "register_my_menus" );

