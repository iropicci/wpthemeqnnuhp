<?php

global $loops, $loopKeys, $wp_query, $pageNo;
$frontpage_data = $fp = theme_fp_get_saved_data();

/*
	Il blocco seguente cicla fra i vari loop e prepara
	gli articoli così da ottimizzare le chiamate al db
	I loop sono definiti nell'array seguente, i valori
	si trovano nella tabella wp_options, divisi in due
	opzioni, entrambe necessarie: "count" e "cats".
*/
$loops = parse_loops( $fp );

get_header();

?>
<div id="SezioneApertura" class="row">
<div class="main">
<?php

$post_index = 0;

if(count($loops['headline'])) {
	echo '<div class="row apertura">';
    $post_index += count(array_filter($loops["headline"]));
	display_posts($loops["headline"], "headline");
	echo '</div><div class="row notizie-altre staccati"></div>';
	echo '<div class="row"><div class="block1">';
	qn2011_sidebar('Headline Widgets');
	echo '</div></div>';
}

qn2011_sidebar('Articles Top Widgets');

$areas_modules = get_option('qnnuhp_areas_modules');
$modules_options = get_option('qnnuhp_modules_options');

if(!empty($areas_modules['home-articles'])):
foreach($areas_modules['home-articles'] as $mod_id):
    $post_index += count(array_filter($loops[$mod_id]));
	display_posts($loops[$mod_id], qn_get_nuhp_module_from_widget($mod_id), @$modules_options[$mod_id]);
endforeach;
endif;

if($post_index) {
	qn_navigate_pagination(TRUE);
} else {
	echo "<p>Nessun articolo da mostrare.</p>
        <ul class=\"pagination\">
            <li class=\"prev\">
                <a href=\"" . get_bloginfo('home') . "\"><i class=\"icon-angle-left\"></i></a>
                Pagina iniziale
            </li>
            <li class=\"next\"></li>
        </ul>
        \n";
}

echo "\t<div class=\"row\">\n";
qn2011_sidebar('Articles Bottom Home Widgets');
qn2011_sidebar('Articles Bottom Widgets');
echo "\t</div>\n";

?>
</div> <!-- main -->

<div id="Sidebar" class="side lp_side">
<!-- COLONNA DESTRA - SIDEBAR -->
<?php qn2011_sidebar('ColonnaDestra'); ?>
</div> <!-- sidebar -->

</div> <!-- sezione -->

<div class="row">
	<?php qn2011_sidebar('Midline Widgets'); ?>
</div>

<?php

if(!empty($areas_modules['home-main-column'])):
foreach($areas_modules['home-main-column'] as $mod_id):
	display_posts($loops[$mod_id], qn_get_nuhp_module_from_widget($mod_id), @$modules_options[$mod_id]);
endforeach;
endif;

get_footer();

?>

