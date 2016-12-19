<?php
	the_post();
	get_header();
?>

<div class="row">
<div class="main">

<!-- briciole di pane -->
<?php if(is_single()) { ?>
<div class="row">
<div class="breadcrumbs block1"><div id="left">
	<a href="<?php bloginfo('siteurl'); ?>">HOMEPAGE</a> &gt;
<?php
	$cat = array_shift(get_the_category());
	echo get_category_parents($cat, true, ' &gt; ');
	the_title();
?>
</div></div>
</div>
<?php } ?>
<!-- //briciole di pane -->

<?php
	$hide_socialshare = @get_post_meta($post->ID, '_qn_post_hide_socialshare', true) == 'SI';
	qn2011_sidebar('Article Top Widgets');
	echo '<article class="' . ($hide_socialshare ? 'noshare' : '') . '">';
	display_post($post, "single");
	echo '</article>';
	if(!$hide_socialshare) {
?>
<div class="row hidden-mobile">
	<div class="block1 share">
		<h4 class="tab-title"><i class="icon-blank"></i>Strumenti</h4>
		<ul>
			<li class="box email">
				<a href="javascript:void(0);" onclick="location.assign('mailto:?body='+encodeURIComponent(location.href.replace(/[#?].+$/,''))+'&subject='+encodeURIComponent(document.title))"><i class="icon-mail"></i>INVIA</a>
			</li>
			<li class="box print"><a href="javascript:window.print()"><i class="icon-print"></i>STAMPA</a></li>
		</ul>
	</div>
</div>
<?php
	}
	qn2011_sidebar('Article Bottom Widgets');
	if(!is_page() && /*get_option('default_comment_status') == "open" &&*/ $post->comment_status == "open") {
		echo '<div id="commenti">' . PHP_EOL;
		comments_template( '', true );
		echo '</div>' . PHP_EOL;
	}
?>

</div> <!-- //main -->

<aside class="side">
<?php
    $opt_artsidebar = theme_fp_get_saved_data('artsidebar');
    $artsidebar_action = @$opt_artsidebar['action'];
    if(!$artsidebar_action) qn2011_sidebar('ColonnaDestra');
	qn2011_sidebar('ColonnaDestra ARTICOLO');
    if($artsidebar_action == 1) qn2011_sidebar('ColonnaDestra');
?>
</aside>

</div> <!-- //row -->

<div class="row">
	<?php qn2011_sidebar('Midline Widgets'); ?>
</div>

<?php get_footer(); ?>
