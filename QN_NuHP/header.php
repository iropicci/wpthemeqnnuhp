<?php
	global $fp, $qnnuhp_current_menu_item;
	$fp = theme_fp_get_saved_data();
?>

<!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7" <?php language_attributes(); ?>> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8" <?php language_attributes(); ?>> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9" <?php language_attributes(); ?>> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" <?php language_attributes(); ?>> <!--<![endif]-->
<html <?php language_attributes(); ?>>
<head prefix="<?php qn2011_head_prefix(); ?>">
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
	<title><?php page_title(); ?></title>
	<meta name="description" content="<?php set_meta_description(); ?>" />
	<meta http-equiv="Content-Type" content="text/html;charset=<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width">
	<!--[if lt IE 9]><script>
			document.createElement('header');
			document.createElement('nav');
			document.createElement('section');
			document.createElement('article');
			document.createElement('aside');
			document.createElement('footer');
			document.createElement('hgroup');
		</script><![endif]-->
	<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />
	<?php qn2011_og(); ?>
	<link rel="stylesheet" type="text/css" href="http://www.stqn.it/nuhp_static/css/style.min.css" />
	<!--[if lt IE 9]><link rel="stylesheet" type="text/css" href="/file_generali/css/nuhp/style.min.css" /><![endif]-->
	<link rel="stylesheet" type="text/css" href="<?php bloginfo( 'stylesheet_url' ); ?>" />
	<?php wp_head(); ?>
	<?php qn2011_sidebar('Head'); ?>
</head>
<body <?php //body_class(); ?>>

<div class="container wrapper-skin-qn <?php echo !is_singular() ? 'homepage' : 'article'; ?> testata testata-wp">

<!-- Background, Popup, Leaderboard... -->
<?php qn2011_sidebar('TopAdvertising'); ?>

<?php if(!$fp['qnheader']['hide']) { ?>
<div id="Header" class="header lp_main_menu">
<header id="Head" class="row">
	<div class="block1">
		<div id="Branding"><a href="http://qn.quotidiano.net/"><i class="icon-qn"></i></a></div>
		<div class="nav-toggle"><a href="" class="usher" data-target="#NavContainer"><i class="icon-toggle"></i></a></div>
		<div id="NavContainer">
			<nav id="PrimaryNavigation" class="primary-navigation">
			<ul>
				<li><a href="http://qn.quotidiano.net/" class="slidify" data-index="0" data-target=".section-news">NEWS</a></li>
				<li><a href="http://qn.quotidiano.net/sport/" class="slidify" data-index="1" data-target=".section-sport">SPORT</a></li>
				<li><a href="http://www.motorionline.com/" class="slidify" data-index="2" data-target=".section-motori">MOTORI</a></li>
				<li><a href="http://qn.quotidiano.net/donna/" class="slidify" data-index="3" data-target=".section-donna">DONNA</a></li>
				<li><a href="http://www.luxgallery.it/" class="slidify" data-index="4" data-target=".section-lifestyle">LIFESTYLE</a></li>
				<li><a href="http://www.lospettacolo.it/" class="slidify" data-index="5" data-target=".section-spettacolo">SPETTACOLO</a></li>
				<li><a href="http://www.hwupgrade.it/" class="slidify" data-index="6" data-target=".section-tech">TECH</a></li>
				<li><a href="http://multimedia.quotidiano.net/video/">HD</a></li>
				<li><a href="http://www.impresadigitale.net/" class="slidify" data-index="7" data-target=".section-servizi">SERVIZI</a></li>
			</ul>
			</nav>
			<div id="Search">
				<a href=""><i class="icon-search"></i></a>
			</div>
			<div id="Weather">
			<script id="WeatherHeaderTemplate" type="text/x-handlebars-template" data-weather="render">
				<a href="#" class="usher" data-target="#Head .WeatherWidget"><i class="icon-{{weather-icon}}"></i>{{degrees}}&deg;</a>
			</script>
			</div>
			<article class="WeatherWidget">
			<script id="WeatherWidgetTemplate" type="text/x-handlebars-template" data-weather="render">
				<div class="WeatherTitle">
					<h3>{{city}}<a href=""><i class="icon-cog"></i></a></h3>
				</div>
				<div class="TheWeather">
					<div class="CurrentTime">{{time}}</div>
					<div class="WeatherIcon"><i class="icon-{{weather-icon}}"></i></div>
					<div class="celcius">{{degrees}}&deg;</div>
				</div>
				<div class="WeatherOther">
					<div class="third">
						<h4>Umidità</h4>
						<p>{{humidity}}%</p>
					</div>
					<div class="third">
						<h4>Pioggia</h4>
						<p>{{precipitation_probability}}%</p>
					</div>
					<div class="third">
						<h4>Vento</h4>
						<p>da {{wind_direction}}<br />a {{wind}}km/h</p>
					</div>
				</div>
				<h5><a href="{{url}}">Tutte le previsioni</a></h5>
			</script>
			</article>
			<div class="row" id="SearchBar">
				<div class="block1">
					<form name="quicksearch" action="http://ricerca.quotidiano.net/cachedindex.php" method="get">
						<i class="icon-search"></i>
						<input type="text" name="ricerca_libera" title="Cerca nel sito" id="SearchBox">
						<div id="SubmitButtonWrapper" class="pull-right">
							<input type="hidden" value="swishlastmodified" name="sort">
							<input type="submit" id="SubmitSearchBtn" class="btn" value="Search">
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</header>
</div>
<?php } ?>

<div class="row logoTestata">
	<a href="<?php qn2011_logo_link(); ?>"><span><?php bloginfo('name'); ?></span></a>
</div>

<?php
	if($fp['menuline']['show'] && has_nav_menu("sottomenu")) {
?>
<nav class="row Citta lp_city_menu">
	<div class="block1">
        <div class="Tutte hidden-desktop usher" data-target=".QuickLinksCitta"><a href="">Apri menu<i class="icon-angle-right"></i></a></div>
<?php if($fp['menuline']['icon'] || $fp['menuline']['title']) { ?>
		<h4>
			<?php if($fp['menuline']['icon']) qnnuhp_fonticon($fp['menuline']['icon']); ?>
			<?php echo @$fp['menuline']['title']; ?>
		</h4>
<?php } ?>
		<?php display_menu('sottomenu'); ?>
	</div>
</nav>
<?php
	}
?>

<div id="headerTicker" class="advTicker">
	<?php qn2011_sidebar('Ticker'); ?>
</div>

<section id="<?php
				if(is_home()) echo 'Home';
				elseif(is_singular()) echo 'Article';
				else echo 'Channel';
			?>">

<?php
    if(has_nav_menu("navmenu")) {
        $navmenu = '';
        $qnnuhp_current_menu_item = FALSE;
        ob_start();
            display_menu("navmenu");
            $navmenu = ob_get_contents();
        ob_end_clean();
?>
<div class="row">
    <nav id="ChannelMenu" class="block1 section-sub-menu lp_sottomenu">
        <div class="nav-toggle"><a class="usher" data-target="#OtherSections" href=""><i class="icon-toggle"></i></a></div>
        <ul class="section-drop nav nav-tabs section-sub-menu-nav">
            <li class="usher" data-target="#OtherSections"><a href="#"><strong>ALTRO</strong> <span class="icon icon-plus-circled"></span></a></li>
        </ul>
        <div id="SectionTitle" class="h2">
            <a href=""><?php
                if(!$qnnuhp_current_menu_item) echo '<i class="icon-angle-right"></i>';
                else echo @$qnnuhp_current_menu_item->title;
            ?></a>
        </div>
        <div id="OtherSections" class="tendina">
            <?php echo $navmenu ?>
        </div>
    </nav>
</div>
<?php
	}
?>
