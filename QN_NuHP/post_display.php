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

function simple_category( $max, $post = FALSE, $info = FALSE, $strict = TRUE, $tag = "div" ) {
	global $post;
	$exclude = array();
	$opt = theme_fp_get_saved_data( "categories" );
    $ret_ar = array(
        "cats" => "", "date" => "", "author" => "",
        "has_cats" => $opt["action"] != 1,
        "has_date" => (bool)@$opt["date"],
        "has_author" => (bool)@$opt["author"],
    );
	if( !$strict || $opt["action"] != 1 ) {
        if( $opt["action"] == 2 ) $exclude = $opt["cats"];
        $cats = get_the_category( $post->ID );
        $ret = "<$tag class=\"category\">in\n";
        $n = 0;
        $cat_ar = array();
        foreach( $cats as $cat ) {
            if( in_array( $cat->cat_ID, $exclude ) ) continue;
            if( ++$n > (int)$max ) break;
            $catLink = get_category_link( $cat->cat_ID );
            $catName = ucwords( $cat->cat_name );
            $cat_ar []= "\t<a href=\"$catLink\">$catName</a>";
        }
        $ret .= implode(",\n", $cat_ar);
        $ret .= "\n</$tag>\n";
        if(count($cat_ar)) $ret_ar["cats"] = $ret;
    }
	if($info) {
        $date_format = @$opt["date"];
        if(!$strict || $date_format) {
            if(!$date_format) $date_format = "j F y";
            $date_str = '<time class="published" datetime="%s">%s</time>';
            $ret_ar["date"] = sprintf($date_str, get_the_date("c"), @get_the_date($date_format));
        }
		if(!$strict || @$opt["author"]) $ret_ar["author"] = sprintf('<span class="author">di %s</span>', qn_author_link("author"));
	}
	return $info ? $ret_ar : $ret_ar["cats"];
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
        case "mod_newsedition":
            echo '<div class="notizie-altre list-style">';
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
            echo $posts;
            echo '</div>';
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
    list($category, $date, $author, $has_category, $has_date, $has_author) = array_values(simple_category(6, $post, true, false));
    $real_category = $has_category ? $category : "";
    $real_date = $has_date ? $date : "";
    $real_author = $has_author ? $author : "";
    $abstract = $real_date || $excerpt ? "
        <div class=\"abstract\">
            <p>$real_date %s</p>
        </div>" : "";
	switch( $type ) {
		case "headline":
			$picture = the_picture("wide_680", $post, FALSE, array('id'=>'img_principale'), FALSE);
			ob_start();
				the_excerpt();
				$excerpt = ob_get_contents();
				$excerpt = strip_tags($excerpt, '<em><strong><a>');
			ob_end_clean();
            $abstract = sprintf($abstract, $excerpt);
			$noimg = $picture ? '' : 'no-image';
			$return =<<<HereNews
			<div class="block1 $noimg">
				<div id="headline_art_$na" class="article-text">
					<article class="title">
						$picture
                        $real_category
						<div class="article-text">
							<h1 class="titleArticle">
								<a href="$link">$title</a>
							</h1>
                            $abstract
						</div>
                        $real_author
					</article>
					<div class="related"></div>
                    <div class="extend usher"></div>
				</div>
			</div>
HereNews;
			break;
		case "mod_editorial":
			$picture = the_picture("small", $post, FALSE, NULL, TRUE);
			$noimg = $picture ? '' : 'no-image';
			$return =<<<HereEdit
			<div class="block3" id="homeEditoriale_$na">
				<article class="title $noimg">
					$picture
					<div class="article-text">
						<h4><a href="$link">$title</a></h4>
					</div>
					$author
				</article>
			</div>
HereEdit;
			break;
		case "mod_news":
			$picture = the_picture("wide_325", $post, FALSE, NULL, TRUE);
			$noimg = $picture ? '' : 'no-image';
			ob_start();
				the_excerpt();
				$excerpt = ob_get_contents();
				$excerpt = strip_tags($excerpt, '<em><strong><a>');
			ob_end_clean();
            $abstract = sprintf($abstract, $excerpt);
			if($na % 2) $return .= '<div class="row">';
			$return .=<<<HereNews
			<div class="block2 notizia-evidenza">
				<article class="title $noimg">
					$picture
                    $real_category
					<div class="article-text">
						<h2 class="titleArticle">
							<a href="$link" $target>$title</a>
						</h2>
                        $abstract
					</div>
                    $real_author
				</article>
			</div>
HereNews;
			if(!($na % 2) || $na == $num) $return .= '</div>';
			break;
        case "mod_newsedition":
			$picture = the_picture("wide_233", $post, FALSE, NULL, TRUE);
			$noimg = $picture ? '' : 'no-image';
			ob_start();
				the_excerpt();
				$excerpt = ob_get_contents();
				$excerpt = strip_tags($excerpt, '<em><strong><a>');
			ob_end_clean();
            $abstract = sprintf($abstract, $excerpt);
			$return .=<<<HereNews
            <div class="row">
			<div class="block1 notizia-altre">
				<article class="title $noimg">
					$picture
                    $real_category
					<div class="article-text">
						<h2 class="titleArticle">
							<a href="$link" $target>$title</a>
						</h2>
                        $abstract
					</div>
                    $real_author
				</article>
			</div>
			</div>
HereNews;
            break;
		case "single":
            $hide_image = @get_post_meta($post->ID, '_qn_post_hide_image', true) == 'SI';
            $picture = '';
            if(!$hide_image) {
                $picture = get_the_picture("wide_680", $post, TRUE, NULL, FALSE);
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
			<div class="abstract">
                $real_date
                <p>$excerpt</p>
                $real_author
                $real_category
            </div>
			$picture
			<div class="description">$content</div>
HereMag;
			break;
		case "archive":
			$hh = $i ? 'h3' : 'h1';
			$rowclass = $i ? '' : 'apertura-piccola';
			$blockclass = $i ? 'notizia-altre' : '';
			$picture = the_picture("wide_233", $post, FALSE);
			$noimg = $picture ? '' : 'no-image';
			ob_start();
				the_excerpt();
				$excerpt = ob_get_contents();
			ob_end_clean();
            $abstract = sprintf($abstract, $excerpt);
			if($i == 1) $return .= '<div class="notizie-altre list-style">';
			$return .=<<<HereArch
			<div class="row $rowclass">
				<div class="block1 $blockclass">
					<article class="title $noimg">
						$picture
                        $real_category
						<div class="article-text">
							<$hh>
								<a href="$link" $target>$title</a>
							</$hh>
							$abstract
						</div>
                        $real_author
					</article>
				</div>
			</div>
HereArch;
			if($i >= 1 && $na == $num) $return .= '</div>';
			break;
		case "col_newsauto":
			global $pic_args;
			$picture = the_picture("wide_233", $post, FALSE, $pic_args, TRUE);
			$noimg = $picture ? '' : 'no-image';
			echo <<<HereColNews
			<div class="block1">
				<article class="title $noimg">
					$picture
                    $real_category
					<div class="article-text">
						<h3><a href="$link" $target>$title</a></h3>
					</div>
                    $real_author
				</article>
			</div>
HereColNews;
			break;
		case "hp_gallery":
			$large = in_array($na, array(1, 6, 8));
			$picture = the_picture($large?"wide_680":"wide_233", $post, FALSE);
			$return = "<div id=\"mfg_foto$na\" class=\"mfg_foto ".($large?"grande":"piccola")."\">\n";
			$return .= "\t$picture\n</div>\n";
			break;
		default:
			break;
	}
	echo apply_filters( "qn2011_display_post", $return, $type, $post );
}

