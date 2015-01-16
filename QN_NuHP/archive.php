<?php

global $wp_query, $pageNo, $wpdb, $fp;

get_header();

if(empty($fp)) $fp = theme_fp_get_saved_data();
$posts_per_page = (int)@$wp_query->query_vars["posts_per_page"];
$paged = (int)@$wp_query->query_vars["paged"];
$page = (int)@$wp_query->query_vars["page"];
$found_posts = (int)@$wp_query->found_posts;

/*
$args = array();
$args["posts_per_page"] = @$wp_query->query["posts_per_page"] ? @$wp_query->query["posts_per_page"] : 10;
// trick per permettere la paginazione degli articoli home page
	$prev_category = @$wp_query->query["category__in"];
	if($prev_category && (array)$prev_category == array(0)) $args["category__in"] = FALSE;
if( is_paginated() ) {
	$headline_offset = 0;
	if($fp["headline"]["count"] && $fp["headline"]["cats"] == $fp["news"]["cats"]) {
		// aumenta i post da passare se la categoria delle news
		// è la stessa delle notizie d'apertura
		$headline_offset += (int)$fp["headline"]["count"];
	}
	$args["offset"] = ($pageNo - 1) * $args["posts_per_page"] + $headline_offset;
	//$args["paged"] = $pageNo;
}
query_posts( array_merge( $wp_query->query, $args ) );
*/

$archive_posts = array();
if( have_posts() ) {
	while( have_posts() ) {
		the_post();
		$archive_posts[] = $post;
	}
}

?>

<div class="row">
<div class="main">

<?php

if(is_search()) {
	$s_page = max(1, $paged) - 1;
	$s_start = $posts_per_page * $s_page + 1;
	$s_end = $posts_per_page * ($s_page + 1);
	echo "
	<div class=\"searchTerms\">
		<h1>Ricerca effettuata per <em>".str_replace('<', '&lt;', $wp_query->query_vars["s"])."</em></h1>
		Risultati trovati: <strong>$found_posts</strong>, visualizzati da $s_start a $s_end
	</div>";
}
qn2011_sidebar('Archive Widgets');
qn2011_sidebar('Articles Top Widgets');

if(have_posts()) {
	display_posts($archive_posts, "archive");
	qn_navigate_pagination(TRUE);
} else {
	echo "<p>Nessun articolo da mostrare.</p>\n";
	if(is_paginated() && $found_posts) qn_navigate_pagination(FALSE);
}

qn2011_sidebar('Articles Bottom Widgets');

?>

</div> <!-- //main -->

<aside id="Sidebar" class="side">
	<?php qn2011_sidebar('ColonnaDestra'); ?>
</aside>

</div> <!-- //row -->

<?php get_footer(); ?>
