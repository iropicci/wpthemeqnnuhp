<?php

function qn_author_link( $class = "" ) {
	global $post;
	if( is_null( $authordata ) && ! $post->post_author ) return "";
	elseif( $post->post_author ) {
		$autid = $post->post_author;
		$author = get_the_author_meta( "display_name", $post->post_author );
	} else {
		$autid = get_the_author_meta("ID");
		$author = get_the_author();
	}
	return "<a class=\"$class\" href=\"" . get_author_posts_url( $autid ) . "\">$author</a>";
}

function simple_category( $max, $post = FALSE, $info = FALSE, $tag = "p" ) {
	global $post;
	$exclude = array();
	$opt = theme_fp_get_saved_data( "categories" );
	if( $opt["action"] == 1 ) return "";
	elseif( $opt["action"] == 2 ) $exclude = $opt["cats"];
	$cats = get_the_category( $post->ID );
	$ret = "<div class=\"meta\">\n";
	$n = 0;
	foreach( $cats as $cat ) {
		if( in_array( $cat->cat_ID, $exclude ) ) continue;
		if( ++$n > (int)$max ) break;
		$catLink = get_category_link( $cat->cat_ID );
		$catName = ucwords( $cat->cat_name );
		$ret .= "<$tag class=\"category\"><a href=\"$catLink\">$catName</a></$tag>\n";
	}
	if( $info ) {
		$infoHTML = "<span class=\"time\">";
		if( $opt["date"] ) $infoHTML .= @get_the_date( $opt["date"] ) . PHP_EOL;
		if( $opt["author"] ) $infoHTML .= qn_author_link("author");
		$infoHTML .= "</span>\n";
		$ret .= $infoHTML;
	}
	$ret .= "</div>\n";
	return $ret;
}

function display_posts($posts, $type, $args=array()) {
	if(!is_array($posts)) return;
	$num = count($posts);
	ob_start();
		foreach($posts as $i => $post)
			display_post($post, $type, $i, $num);
		$posts = ob_get_contents();
	ob_end_clean();
	switch($type) {
		case "mod_editorial":
			echo '<div class="row editorial"><div class="block1">';
			if($args['title']) {
				printf('<span class="tag" style="background-color:%s">%s</span>', $args['color'], $args['title']);
			}
			echo $posts;
			echo '</div></div>';
			break;
		case "mod_news":
			echo '<div class="notizie-evidenza">';
			if($args['title']) {
				echo '<div class="row">
						<div class="block1">
							<ul class="nav-tabs">
								<li class="active">';
				if($args['icon']) qnnuhp_fonticon($args['icon']);
				echo "				{$args['title']}
								</li>
							</ul>
						</div>
					</div>";
			}
			echo '<div class="tab-content">
					<div id="TabEvidenza" class="tab-pane active">
						' . $posts . '
					</div>
				</div>
			</div>';
			break;
		case "col_newsauto":
			echo '<div class="notizie-automatiche">';
			if($args['title']) echo '<div class="row">' . $args['title'] . '</div>';
			echo '<div class="row">';
			echo $posts;
			echo '</div></div>';
			break;
		default:
			echo $posts;
	}
}

function display_post( $post = FALSE, $type = FALSE, $i = 0, $num = FALSE ) {
	if( $post ) $GLOBALS["post"] = $post;
	elseif( $post === FALSE ) $post = $GLOBALS["post"];
	else return;
	setup_postdata( $post );
	$return = apply_filters( "qn2011_pre_display_post", "", $type, $i, $num );
	if($return) {
		echo $return;
		return;
	}
	$na = $i + 1;
	$link = get_permalink();
	$title = get_the_title();
	$target = function_exists('is_syndicated') && is_syndicated() ? ' target="_blank"' : '';
	switch( $type ) {
		case "headline":
			$picture = the_picture("large", $post, FALSE, array('id'=>'img_principale'), FALSE);
			ob_start();
				the_excerpt();
				$excerpt = ob_get_contents();
				$excerpt = strip_tags($excerpt, '<em><strong>');
			ob_end_clean();
			$noimg = $picture ? '' : 'no-image';
			$return =<<<HereNews
			<div class="block1 $noimg">
				<div id="headline_art_$na" class="article-text">
					<article class="title">
						$picture
						<div class="article-text">
							<h1 class="titleArticle">
								<a href="$link">$title</a>
							</h1>
						</div>
					</article>
					<div class="related">
						<p><a href="$link">$excerpt</a></p>
					</div>
				</div>
			</div>
HereNews;
			break;
		case "mod_editorial":
			$picture = the_picture("small", $post, FALSE, NULL, TRUE);
			$noimg = $picture ? '' : 'no-image';
			$author = qn_author_link();
			$return =<<<HereEdit
			<div class="block3" id="homeEditoriale_$na">
				<article class="title $noimg">
					$picture
					<div class="article-text">
						<h4>
							<a href="$link">$title</a>
						</h4>
					</div>
					<span class="author">di $author</span>
				</article>
			</div>
HereEdit;
			break;
		case "mod_news":
			$picture = the_picture("medium", $post, FALSE, NULL, TRUE);
			$noimg = $picture ? '' : 'no-image';
			$category = simple_category(6, FALSE, 4);
			ob_start();
				the_excerpt();
				$excerpt = ob_get_contents();
				$excerpt = strip_tags($excerpt, '<em><strong>');
			ob_end_clean();
			if($na % 2) $return .= '<div class="row">';
			$return .=<<<HereNews
			<div class="block2 notizia-evidenza">
				<article class="title $noimg">
					$picture
					<div class="article-text">
						<h2 class="titleArticle">
							<a href="$link" $target>$title</a>
						</h2>
					</div>
				</article>
				<div class="related">
					<p>$excerpt</p>
				</div>
			</div>
HereNews;
			if(!($na % 2) || $na == $num) $return .= '</div>';
			break;
		case "single":
            $hide_image = @get_post_meta($post->ID, '_qn_post_hide_image', true) == 'SI';
            $picture = '';
            if(!$hide_image) {
                $picture = get_the_picture("large", $post, TRUE, NULL, FALSE);
                if($picture) $picture =<<<HerePic
			<aside class="aside">
				<div class="photo">
					<div class="format"><i class="icon icon-search"></i></div>
					{$picture[0]}
					<fig-caption class="photo-credit">{$picture[1]}</fig-caption>
				</div>
			</aside>
HerePic;
            }
			ob_start();
				if($post->post_excerpt) the_excerpt();
				elseif(preg_match("/<!--more-->/i", $post->post_content))
					echo preg_replace("/<[^>]+>/", "", array_shift(explode("<!--more-->", $post->post_content, 2)));
				$excerpt = ob_get_contents();
			ob_end_clean();
			$content = the_content_filter($post->post_content);
			$return =<<<HereMag
			<h1 class="titleBig">$title</h1>
			<div class="abstract"><p>$excerpt</p></div>
			$picture
			<div class="description">$content</div>
HereMag;
			break;
		case "archive":
			$hh = $i ? 'h3' : 'h1';
			$rowclass = $i ? '' : 'apertura-piccola';
			$blockclass = $i ? 'notizia-altre' : '';
			$picture = the_picture("medium", $post, FALSE);
			$noimg = $picture ? '' : 'no-image';
			ob_start();
				the_excerpt();
				$excerpt = ob_get_contents();
			ob_end_clean();
			if($i == 1) $return .= '<div class="notizie-altre list-style">';
			$return .=<<<HereArch
			<div class="row $rowclass">
				<div class="block1 $blockclass">
					<article class="title $noimg">
						$picture
						<div class="article-text">
							<$hh>
								<a href="$link" $target>$title</a>
							</$hh>
							<div class="abstract">$excerpt</div>
						</div>
					</article>
				</div>
			</div>
HereArch;
			if($i >= 1 && $na == $num) $return .= '</div>';
			break;
		case "col_newsauto":
			global $pic_args;
			$picture = the_picture("medium", $post, FALSE, $pic_args, TRUE);
			$noimg = $picture ? '' : 'no-image';
			echo <<<HereColNews
			<div class="block1">
				<article class="title $noimg">
					$picture
					<div class="article-text">
						<h3><a href="$link" $target>$title</a></h3>
					</div>
				</article>
			</div>
HereColNews;
			break;
		case "hp_gallery":
			$large = in_array($na, array(1, 6, 8));
			$picture = the_picture($large?"large":"medium", $post, FALSE);
			$return = "<div id=\"mfg_foto$na\" class=\"mfg_foto ".($large?"grande":"piccola")."\">\n";
			$return .= "\t$picture\n</div>\n";
			break;
		default:
			break;
	}
	echo apply_filters( "qn2011_display_post", $return, $type, $post );
}

