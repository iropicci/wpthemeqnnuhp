<?php

// CUSTOM DEFAULT OPTIONS

define( 'THEMEDIR', get_bloginfo('stylesheet_directory') );
define( 'SAVEQUERIES', TRUE ); // fo debug

define( 'NO_HEADER_TEXT', true );
define( 'HEADER_TEXTCOLOR', '' );
define( 'HEADER_IMAGE', '%s/style/qnet_magazine.jpg' ); // %s is the template dir uri
define( 'HEADER_IMAGE_WIDTH', 450 ); // use width and height appropriate for your theme
define( 'HEADER_IMAGE_HEIGHT', 100 );
// gets included in the site header
function header_style() {
?>
	<style type="text/css">
		.logoTestata a {
			width: <?php echo HEADER_IMAGE_WIDTH; ?>px;
			height: <?php echo HEADER_IMAGE_HEIGHT; ?>px;
			background-image: url('<?php header_image(); ?>');
		}
	</style>
<?php
}
// gets included in the admin header
function admin_header_style() {
?>
	<style type="text/css">
		#headimg {
			width: <?php echo HEADER_IMAGE_WIDTH; ?>px;
			height: <?php echo HEADER_IMAGE_HEIGHT; ?>px;
			background: no-repeat;
		}
		#headimg * {
			display: none;
		}
	</style>
<?php
}
global $wp_version;
if ( true === false && version_compare( $wp_version, '3.4', '>=' ) ) {
	$cst_img = array(
		'wp-head-callback' => 'header_style',
		'admin-head-callback' => 'admin_header_style'
	);
	add_theme_support('custom_header_upload', array('wp-head-callback'=>'header_style', 'admin-head-callback'=>'admin_header_style'));
	add_action('wp_head', 'header_style');
} else {
	add_custom_image_header('header_style', 'admin_header_style');
}
// post thumbnails
add_theme_support('post-thumbnails', array('post'));
set_post_thumbnail_size(680, 380, true);
add_image_size('small', 30, 30, TRUE);

// WIDGET
include __DIR__ . "/admin_layout_style.php";
include __DIR__ . "/register_sidebars.php";
include __DIR__ . "/register_navmenu.php";
include __DIR__ . "/register_widgets.php";
include __DIR__ . "/post_display.php";
include __DIR__ . "/wp_ajax.php";
include __DIR__ . "/admin_interface.php";
include __DIR__ . "/cron.php";

function qn_wpdebug($todebug=FALSE) {
	if(!$todebug) $todebug = $GLOBALS["wp_query"];
	$return = print_r($todebug, TRUE);
?>

<!--
 __  __                                   ___
/\ \/\ \                                /'___\
\ \ \_\ \     __     __  __     __     /\ \__/  __  __    ___
 \ \  _  \  /'__`\  /\ \/\ \  /'__`\   \ \ ,__\/\ \/\ \ /' _ `\
  \ \ \ \ \/\ \L\.\_\ \ \_/ |/\  __/    \ \ \_/\ \ \_\ \/\ \/\ \
   \ \_\ \_\ \__/.\_\\ \___/ \ \____\    \ \_\  \ \____/\ \_\ \_\
    \/_/\/_/\/__/\/_/ \/__/   \/____/     \/_/   \/___/  \/_/\/_/

<?php
	echo str_replace('<', '&lt;', $return);
	echo "\n-->\n\n";
}

// LAYOUT DATA DISPLAY

function page_title( $echo = TRUE ) {
	global $page, $paged, $pageTitle;
	if( empty( $pageTitle ) ) {
	ob_start();
		wp_title( '|', true, 'right' );
		bloginfo( 'name' );
		$site_description = get_bloginfo( 'description', 'display' );
		if ( $site_description && ( is_home() || is_front_page() ) )
			echo " | $site_description";
		if ( $paged >= 2 || $page >= 2 )
			echo ' | ' . sprintf( "Pagina %s", max( $paged, $page ) );
		$pageTitle = ob_get_contents();
	ob_end_clean();
	}
	if( $echo ) return print( $pageTitle );
	return $pageTitle;
}

function qn2011_head_prefix() {
	$prefixes = array(
		"og" => "http://ogp.me/ns",
		"fb" => "http://ogp.me/ns/fb"
	);
	if( is_singular() ) $prefixes["article"] = "http://ogp.me/ns/article";
	$prefixes = apply_filters( "qn2011_head_prefix", $prefixes );
	foreach( (array)$prefixes as $ns => $url ) if( $url ) echo "$ns: $url# ";
}

function set_meta_description() {
	$desc = "";
	if(is_singular()) $desc = trim(strip_tags(get_the_excerpt()));
	else if(is_category()) {
		$cat_desc = category_description();
		$desc = $cat_desc ? $cat_desc : single_cat_title();
	} else $desc = get_bloginfo('description');
	echo apply_filters( "qn2011_meta_description", $desc );
}

// OPEN GRAPH
function qn2011_og() {
	$opengraph = array();
	$keys = array( "title", "type", "image", "url", "description", "locale" );
	foreach( $keys as $key ) {
		$value = "";
		$properties = array();
		switch( $key ) {
			case "title":
				if( is_home() || is_front_page() ) $value = get_bloginfo("name");
				else $value = page_title( FALSE );
				break;
			case "type":
				if( is_singular() ) {
					$value = "article";
					$categories = get_the_category();
					$properties = array(
						"$value:published_time" => get_the_time("c"),
						"$value:modified_time" => get_the_modified_time("c"),
						"$value:author" => get_author_posts_url( get_the_author_meta("ID") ),
						"$value:section" => $categories[0]->cat_name,
					);
					$tags = get_the_tags();
					if( $tags ) foreach( $tags as $tag ) $properties["$value:tag"][] = $tag->name;
				} else
					$value = "website";
				break;
			case "image":
				if( is_singular() ) {
					$pic = preg_replace("/[\n\r]+/", "", trim(@get_the_picture("thumbnail")));
					$value = preg_replace( '/^.+ src="([^"]+).+$/i', "$1", $pic );
				}
				if( ! $value ) $value = get_header_image();
				break;
			case "url":
				if( is_home() || is_front_page() ) $value = get_bloginfo("url");
				elseif( is_singular() ) $value = get_permalink();
				else $value = "http://{$_SERVER["HTTP_HOST"]}{$_SERVER["REQUEST_URI"]}";
				break;
			case "description":
				if( is_singular() ) $value = trim(strip_tags(get_the_excerpt()));
				else $value = get_bloginfo("description");
				break;
			case "locale":
				$value = str_replace( "-", "_", get_bloginfo("language") );
				break;
		}
		$opengraph[$key] = apply_filters( "qn2011_opengraph_key", $value, $key );
		foreach( $properties as $propkey => $propval ) {
			$propkey_safe = preg_replace( "/\W+/", "_", $propkey );
			$opengraph[$propkey] = apply_filters( "qn2011_opengraph_key", $propval, $propkey_safe );
		}
	}
	$opengraph = array_filter( $opengraph );
	$opengraph = apply_filters( "qn2011_opengraph", $opengraph );
	$ogstr = '<meta property="%s" content="%s" />' . PHP_EOL;
	foreach( $opengraph as $ogkey => $ogval ) { // cicla per ogni chiave OG
		if( ! strpos( $ogkey, ':' ) ) $ogkey = "og:$ogkey";
		foreach( (array)$ogval as $ogsubval ) // cicla per ogni valore OG
			printf( $ogstr, $ogkey, str_replace('"', '&quot;', $ogsubval) );
	}
}

add_action('rss_item', 'qnnuhp_feed_integrate');
add_action('rss2_item', 'qnnuhp_feed_integrate');
function qnnuhp_feed_integrate($void='') {
	global $post;
	$img_obj = get_the_picture('large', $post, TRUE, NULL, FALSE);
	if($img_obj) {
		list($img_obj, $title) = $img_obj;
		$thumb_obj = get_the_picture('medium', $post, FALSE, NULL, FALSE);
		foreach(array('img', 'thumb') as $k) $$k = preg_replace('/^.+\bsrc="([^"]+).+$/i', '$1', ${$k."_obj"});
		$link = get_permalink();
		$img_str =<<<HereStr
	<thumb>$thumb</thumb>
	<image>
\t		<url>$img</url>
\t		<thumb>$thumb</thumb>
\t		<title>$title</title>
\t		<link>$link</link>
\t	</image>
HereStr;
		echo $img_str . PHP_EOL;
	}
}

function qn2011_logo_link( $print = TRUE ) {
	$link = apply_filters( "qn2011_logo_link", FALSE );
	if( ! $link ) $link = get_bloginfo("url");
	if( $print ) return print( $link );
	return $link;
}

function qnnuhp_list_fonticons() {
	$fonticons = array(
		'angle-up', 'angle-down', 'angle-right', 'angle-left',
		'gplus', 'twitter', 'rss', 'video', 'youtube', 'youtube-pos',
		'facebook', 'facebook-app', 'globe', 'star', 'sound',
		'cross', 'linkedin', 'linkedin-filled', 'whatsapp',
		'people', 'megaphone', 'grafico', 'comment', 'search',
		'print', 'foto', 'image', 'blank', 'disc', 'comments',
		'network', 'audio', 'blog', 'documento', 'sondaggio',
		'minus-circled', 'plus-circled', 'mail', 'mail-filled',
		'testo-2', 'apertura-video', 'qs', 'qn', 'toggle',
		'giorno', 'nazione', 'rdc', 'location', 'clock', 'cog',
	);
	$opticons = get_option('qnnuhp_fonticons_list', $fonticons);
	return $opticons;
}

function qnnuhp_fonticon($icon) {
	if(in_array($icon, qnnuhp_list_fonticons())) {
		echo "<i class=\"icon-$icon\"></i>";
	}
}

// HOME PAGE LOOPS
function parse_loops( $fpdata = FALSE ) {
	global $qn_nuhp_modules;
	$exclude = array();
	if( ! $fpdata ) $fpdata = theme_fp_get_saved_data();
	$loops = array();
	$areas_modules = get_option('qnnuhp_areas_modules', array());
	$modules_options = get_option('qnnuhp_modules_options', array());
	$areas_keys = array_keys($qn_nuhp_modules);
	if(is_home()) {
		$loops['headline'] = $fpdata['headline'];
		$area = $areas_keys[0];
		if(!empty($areas_modules[$area])) {
			foreach($areas_modules[$area] as $mod_id) {
				if(!array_key_exists($mod_id, $modules_options)) continue;
				$loops[$mod_id] = $modules_options[$mod_id];
			}
		}
	}
	$area = $areas_keys[1];
	if(!empty($areas_modules[$area])) {
		foreach($areas_modules[$area] as $mod_id) {
			if(!array_key_exists($mod_id, $modules_options)) continue;
			$loops[$mod_id] = $modules_options[$mod_id];
		}
	}
	foreach( $loops as $tree => &$loop ) {
		$data = $loop;
		$loop = array();
		if((int)@$data["count"] < 1) continue;
		$args = array(
			"numberposts" => (int)$data["count"],
			"post__not_in" => $exclude,
			"suppress_filters" => FALSE,
		);
		if($tree == 'headline') {
			$sticky = get_option('sticky_posts', FALSE);
			if($sticky) $args["include"] = implode(',', $sticky);
		} else $args["ignore_sticky_posts"] = TRUE;
		if(isset($data["exclude"])) $args["category__not_in"] = $data["exclude"];
		if(count(array_filter((array)$data["cats"]))) $args["category__in"] = $data["cats"];
		$newPosts = get_posts($args);
		if(!count($newPosts)) $newPosts = array_fill(0, $data["count"], null);
		$loop = array_merge($loop, $newPosts);
		foreach($loop as $post) $exclude[] = $post->ID;
	}
	return $loops;
}

function the_picture($size="medium", $parent=FALSE, $echo=TRUE, $attrs=array(), $lazy=TRUE) {
	$picture = get_the_picture($size, $parent, FALSE, $attrs, $lazy);
	if( $picture ) {
		$target = function_exists('is_syndicated') && is_syndicated() ? ' target="_blank"' : '';
		$picture = '
		<div class="image">
			<a href="' . get_permalink( $parent->ID ) . '" ' . $target . '>
				' . $picture . '
			</a>' . ( empty($attrs["icon"]) ? '' : '
			<div class="format"><i class="icon-' . $attrs["icon"] . '"> </i></div>' ) . '
		</div>';
	}
	if( $echo ) return print( $picture );
	return $picture;
}
function get_the_picture($size="medium", $parent=FALSE, $ret_title=FALSE, $attrs=array(), $lazy=TRUE) {
	global $post_pictures;
	ob_start();
		do_action( "get_the_picture", $size );
		$taken = ob_get_contents();
	ob_end_clean();
	if( $taken ) {
		$picture = '<img alt="" src="' . $taken . '" />';
		return $ret_title ? array( $picture, "" ) : $picture;
	}
	
	if( ! $parent ) {
		if( empty( $GLOBALS["post"] ) ) return "";
		else $parent = $GLOBALS["post"];
	}
	$args = array(
		"numberposts" => 1,
		"order" => "ASC",
		"orderby" => "menu_order",
		"post_type" => "attachment",
		"post_mime_type" => "image",
		"post_parent" => $parent->ID,
		"post_status" => "any"
	);
	$picture = "";
	$img = $title = FALSE;
	$atts = get_posts( $args );
	if($atts && count($atts)): foreach( $atts as $att ):
		$img = array_shift(wp_get_attachment_image_src($att->ID, $size));
		//echo "<pre>".print_r(get_posts("orderby=menu_order&order=ASC&post_type=attachment&post_mime_type=image&post_parent={$parent->ID}&post_status=any"),true)."</pre>\n";
		$title = $att->post_content ? $att->post_content : $att->post_title;
		$title = str_replace( '"', '&quot;', apply_filters( "the_title", $title ) );
		break;
	endforeach; else:
		$post_img = get_the_post_thumbnail($parent->ID, $size);
		if(!$post_img) {
			$enclosures = @get_post_meta($parent->ID , 'the_picture', FALSE);
			if($enclosures):
			foreach($enclosures as $enc):
				if(!preg_match('/\.(jpe?g|png|gif)$/i', $enc)) continue;
				$post_img = $enc;
				$img = $post_img;
				break;
			endforeach;
			endif;
		}
		if(!$post_img) $post_img = $parent->post_content;
		if( preg_match( '/<img[^>]+\bsrc="([^"]+)"[^>]*>/i', $post_img, $m ) ) {
			$img = $m[1];
			if(preg_match('/\b(?:alt|title)="([^"]+)/i', $m[0], $t)) $title = $t[1];
		}
		if(!$title) $title = str_replace( '"', '&quot;', apply_filters( "the_title", $parent->post_title ) );
	endif;
	if( $img && $title ) {
		$post_pictures[ $parent->ID ] = $img;
		$attrs['src'] = $img;
		$attrs['title'] = $title;
		$attrs['alt'] = $title;
		$picture = '<img ';
			foreach($attrs as $k => $v) $picture .= "$k=\"$v\" ";
		$picture .= '/>';
		if($lazy) {
			$attrs['style'] = 'display: inline;' . @$attrs['style'];
			$attrs['class'] = 'lazy ' . @$attrs['class'];
			$attrs['src'] = 'http://www.stqn.it/nuhp_static/img/lazy.png';
			$attrs['data-original'] = $img;
			$noscript = "<noscript>$picture</noscript>";
			$picture = '<img ';
				foreach($attrs as $k => $v) $picture .= "$k=\"$v\" ";
			$picture .= "/>$noscript";
		}
	}
	if(!$picture) return "";
	return $ret_title ? array( $picture, $title ) : $picture;
}

function the_content_filter( $content = '' ) {
	global $post_pictures, $post;
	$new_content = $content;
    $hide_image = @get_post_meta($post->ID, '_qn_post_hide_image', true) == 'SI';
	if(!$hide_image && is_array($post_pictures) && $post_pictures[$post->ID]) {
        $img_src_re = preg_replace('/(?:-\d+x\d+)?(\.[a-z]{3,4})$/i', '(-\\d+x\\d+)?$1', $post_pictures[$post->ID]);
		$regexps = array(
			'@<img[^>]+src=["\']?' . $img_src_re . '[^>]*>@i' => ''
		);
		foreach( $regexps as $re => $sub )
			$new_content = preg_replace( $re, $sub, $new_content );
	}
	$shortcodes = array(
		"youtube" => array( "v" => FALSE, "w" => "660", "h" => "365" )
	);
	foreach( $shortcodes as $shortcode => $pairs ) {
		$function = "qn_shortcode_$shortcode";
		if( ! function_exists($function) ) continue;
		$regexp = "/\[$shortcode(?:\s+([^\]]+))?\]/";
		if( ! preg_match_all($regexp, $new_content, $matches, PREG_SET_ORDER|PREG_OFFSET_CAPTURE) ) continue;
		$offset = 0;
		foreach( $matches as $i => $m ) {
			$atts = empty($m[1][0]) ? FALSE : shortcode_atts($pairs, shortcode_parse_atts($m[1][0]));
			$replace = $function( $atts );
			if( ! $replace ) continue;
			$length = strlen($m[0][0]);
			$new_content = substr_replace( $new_content, $replace, $m[0][1] + $offset, $length );
			$offset += strlen($replace) - $length;
		}
	}
	$new_content = wpautop(trim($new_content));
	$new_content = apply_filters('qn_content_filter', $new_content);
	return trim( $new_content );
}
add_filter( 'get_the_content', 'the_content_filter' );
add_filter( 'qn_content_filter', 'do_shortcode', 11 );

function qn_shortcode_youtube( $atts ) {
	if( ! $atts["v"] ) return FALSE;
	$str = '<iframe width="%s" height="%s" src="http://www.youtube.com/embed/%s" frameborder="0" allowfullscreen></iframe>';
	return sprintf( $str, $atts["w"], $atts["h"], $atts["v"] );
}

function mytheme_tinymce_config( $init ) {
	$valid_iframe = 'iframe[*]';
	if( isset($init['extended_valid_elements']) ) $init['extended_valid_elements'] .= ',' . $valid_iframe;
	else $init['extended_valid_elements'] = $valid_iframe;
	return $init;
}
add_filter('tiny_mce_before_init', 'mytheme_tinymce_config');
add_filter('teeny_mce_before_init', 'mytheme_tinymce_config');

function the_excerpt_filter( $content = '' ) {
	$notag = preg_replace( '/<[^>]+>|[\n\r]+/', '', $content );
	return trim( $notag );
}
add_filter( 'get_the_excerpt', 'the_excerpt_filter', 1 );

function has_pagination() {
	global $has_pagination;
	static $checked = FALSE;
	if( $checked ) return $has_pagination;
	$pagination_opt = theme_fp_get_saved_data( "pagination" );
	$has_pagination = (bool)$pagination_opt["active"];
	$checked = TRUE;
	return $has_pagination;
}

function is_paginated() {
	global $is_paginated, $pageNo;
	static $checked = FALSE;
	if( $checked ) return $is_paginated;
	$is_paginated = FALSE;
	$startNo = $pageNo = 1;
	if( has_pagination() ) {
		$pageNo = max($pageNo, (int)preg_replace("/\D+/", "", @$_GET["page"]));
		$is_paginated = $pageNo > $startNo;
	}
	$checked = TRUE;
	return $is_paginated;
}

function alter_the_query($query) {
	global $pageNo;
	if(!has_pagination() || !$query->is_archive()) return FALSE;
	if(is_paginated()) $query->set('paged', $pageNo);
}
add_filter('pre_get_posts', 'alter_the_query');

function qn_navigate_pagination($ahead = TRUE) {
	global $pageNo, $wp_query;
	if(!has_pagination()) return;
	$prev = $next = '';
	ob_start();
		previous_posts_link('<i class="icon-angle-left"></i>');
		$prev = ob_get_contents();
		ob_clean();
		if($ahead) {
			next_posts_link('<i class="icon-angle-right"></i>');
			$next = ob_get_contents();
		}
	ob_end_clean();
	if($prev) $prev = "Pagina precedente $prev";
	if($next) $next = "Pagina successiva $next";
	echo <<<HerePag
	<ul class="pagination">
		<li class="prev">
			$prev
		</li>
		<li class="next">
			$next
		</li>
	</ul>
HerePag;
}

function qn_fotogallery_shortcode($void = '', $attr) {
	global $post, $fotogallery;
	if ( isset( $attr['orderby'] ) ) {
		$attr['orderby'] = sanitize_sql_orderby( $attr['orderby'] );
		if ( !$attr['orderby'] ) unset( $attr['orderby'] );
	}
	extract(shortcode_atts(array(
		'order'      => 'ASC',
		'orderby'    => 'menu_order ID',
		'id'         => $post->ID,
		'include'    => '',
		'exclude'    => ''
	), $attr));
	$id = intval($id);
	if ( 'RAND' == $order ) $orderby = 'none';
	if ( !empty($include) ) {
		$include = preg_replace( '/[^0-9,]+/', '', $include );
		$_attachments = get_posts( array('include' => $include, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $order, 'orderby' => $orderby) );
		$attachments = array();
		foreach ( $_attachments as $key => $val ) {
			$attachments[$val->ID] = $_attachments[$key];
		}
	} elseif ( !empty($exclude) ) {
		$exclude = preg_replace( '/[^0-9,]+/', '', $exclude );
		$attachments = get_children( array('post_parent' => $id, 'exclude' => $exclude, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $order, 'orderby' => $orderby) );
	} else {
		$attachments = get_children( array('post_parent' => $id, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $order, 'orderby' => $orderby) );
	}
	if ( is_feed() ) {
		if ( empty($attachments) )
			return "<p>Questa fotogallery non contiene immagini.</p>\n";
		$output = "\n";
		foreach ( $attachments as $att_id => $attachment )
			$output .= wp_get_attachment_link($att_id, $size, true) . "\n";
		return $output;
	}
	// IRO 2013
	$fotogallery = array();
	foreach($attachments as $id => $foto) {
		if(!preg_match("|^image/|i", $foto->post_mime_type)) continue;
		$fotogallery[] = (object)array(
			"src"   => $foto->guid,
			"thumb" => wp_get_attachment_thumb_url($foto->ID),
			"title" => $foto->post_title,
			"desc"  => $foto->post_excerpt
		);
	}
	ob_start();
		make_fotogallery();
		$content = ob_get_contents();
	ob_end_clean();
	return $content;
}
add_filter('post_gallery', 'qn_fotogallery_shortcode', 1, 2);

function make_fotogallery() {
	global $post, $fotogallery;
	if(empty($fotogallery)) {
		$html = get_the_content();
		$fotogallery = array();

		$xml = new DOMDocument( '1.0' );
		@$xml->loadHTML( utf8_decode( $html ) );
		/*
		$lines = explode( PHP_EOL, $html );
		foreach( $lines as $i => &$l ) $l = "$i: ".htmlentities($l);
		echo "<p><pre>" . implode( PHP_EOL, $lines ) . "</pre></p>\n";
		*/
		$dts = $xml->getElementsByTagName("dl");
		for( $i = 0; $i < $dts->length; $i++ ) {
			$dt = $dts->item( $i );
			//if( $dt->attributes->getNamedItem("class")->nodeValue != "gallery-item" ) continue;
			$a = @$dt->getElementsByTagName("a")->item(0);
			$img = @$dt->getElementsByTagName("img")->item(0);
			if( ! $a || ! $img ) continue;
			$fotogallery[] = (object)array(
				"src" => $a->attributes->getNamedItem("href")->nodeValue,
				"thumb" => $img->attributes->getNamedItem("src")->nodeValue,
				"title" => $a->attributes->getNamedItem("title")->nodeValue,
				"desc" => @$img->attributes->getNamedItem("desc")->nodeValue
			);
		}
	}
	if( ! count( $fotogallery ) ) {
		echo "<p>Questa fotogallery non contiene immagini.</p>\n";
		return;
	}
	$thumbs = "";
	$current = "<p>Clicca su un'anteprima per visualizzarne l'immagine ingrandita.</p>\n";
	$template = '<a href="?foto=%d" class="%s"><img alt="%s" title="%3$s" src="%s" /></a>';
	$numFoto = count( $fotogallery );
	$currentID = (int)preg_replace( "/\D+/", "", @$_GET["foto"] );
	if( ! $currentID ) $currentID = 1;
	foreach( $fotogallery as $i => $foto ) {
		$id = $i + 1;
		$is_current = $currentID === $id;
		$thumb = sprintf( $template, $id, $is_current ? "current" : "", $foto->title, $foto->thumb );
		$thumbs .= $thumb . PHP_EOL;
		if( ! $is_current ) continue;
		$image = '<img alt="' . $foto->title . '" src="' . $foto->src . '" />';
		$nextTxt = 'Foto successiva: ' . @$fotogallery[$i+1]->title;
		$prevTxt = 'Foto precedente: ' . @$fotogallery[$i-1]->title;
		$next = '<a href="?foto=' . ( $id + 1 ) . '" title="' . $nextTxt . '">%s</a>';
		$prev = '<a href="?foto=' . ( $id - 1 ) . '" title="' . $prevTxt . '">%s</a>';
		$nextBtn = $id < $numFoto ? sprintf( $next, '<span class="next">' . $nextTxt . '</span>' ) : '';
		$prevBtn = $i > 0 ? sprintf( $prev, '<span class="prev">' . $prevTxt . '</span>' ) : '';
		if( $id < $numFoto ) $image = sprintf( $next, $image );
		$description = $foto->desc ? '<p class="description">' . $foto->desc . '</p>' : "";
		$current =<<<HereCur
		<div id="art_fotogallery_current_title">
			<em>Foto <strong>$id</strong> di $numFoto</em>
			<div id="art_fotogallery_current_nav">
				$prevBtn
				$nextBtn
			</div>
			<h2>{$foto->title}</h2>
			$description
		</div>
		<div id="art_fotogallery_current_pic">$image</div>
HereCur;
	}
?>
<div id="art_fotogallery">
	<div id="art_fotogallery_current">
<?php	echo $current; ?>
	</div>
	<div id="art_fotogallery_thumbs">
<?php	echo $thumbs; ?>
	</div>
</div>
<?php
}

// THIS GETS CURRENT MENU ITEM INTO GLOBALS
add_filter('wp_nav_menu_objects', 'qnnuhp_globalize_current_navmenu');
function qnnuhp_globalize_current_navmenu($sorted_menu_items) {
    global $qnnuhp_current_menu_item;
    foreach($sorted_menu_items as $i => &$menu_item) {
        $menu_item->is_last = 0;
        if($menu_item->current) {
            $qnnuhp_current_menu_item = $menu_item;
        }
    }
    $menu_item->is_last = 1;
    return $sorted_menu_items;
}

// ... AND THIS REMOVES CURRENT ITEM FROM MENU
add_filter('walker_nav_menu_start_el', 'qnnuhp_hide_current_navmenu', 1, 4);
function qnnuhp_hide_current_navmenu($item_output, $item, $depth, $args) {
    if(@$args->menu_id == 'navmenu' && @$item->current) return '';
    return $item_output;
}
