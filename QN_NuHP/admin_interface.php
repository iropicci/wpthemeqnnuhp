<?php

function setup_theme_admin_menu() {
    if(!current_user_can('manage_options')) return;
    add_menu_page(
        'Gestione Front Page',	#$page_title
        'Front Page',			#$menu_title
        'manage_options',		#$capability
        'qn_front_page',		#$menu_slug
        'theme_front_page_settings', #$function
        'none',					#$icon_url
        '62.1'					#$position
    );
    return FALSE;
}
add_action('admin_menu', 'setup_theme_admin_menu');

add_action('admin_head', 'qnnuhp_admin_head');
function qnnuhp_admin_head() {
    wp_enqueue_style('fontastic-monrif', get_template_directory_uri() . '/style/admin.css');
}

function qn_default_value( $value, $type, $default = "" ) {
    $type = explode( "|", $type );
    $return = FALSE;
    if( $value !== FALSE ):
        switch( $type[0] ) {
            case "array":
                if( !isset( $type[1] ) ) break;
                $return = array();
                foreach( (array)$value as $k => $v ) {
                    $dType = is_array( $v ) ? "array|{$type[1]}" : $type[1];
                    $return[ $k ] = qn_default_value( $v, $dType, $default );
                }
                break;
            case "bool":
                $return = $value ? "1" : "0";
                $default = $default ? "1" : "0";
                break;
            case "int":
                $return = preg_replace( "/\D+/", "", $value );
                $default = preg_replace( "/\D+/", "", $default );
                if( $return === "" ) $return = FALSE;
                break;
            default:
                $return = esc_attr( trim( $value ) );
        }
    endif;
    if( $return === FALSE ) return $default;
    return $return;
}

function theme_fp_option( $tree, $opt ) {
    return "front_page-{$tree}_$opt";
}

function qn_slash_option( &$value, $key = FALSE, $typecheck = FALSE ) {
    $value = stripslashes( $value );
    if($typecheck) {
        if(preg_match("/^\d+$/", $value)) settype($value, "int");
        elseif(preg_match("/^\d+\.\d+$/", $value)) settype($value, "float");
    }
}

function qn_get_option( $optname ) {
    $value = @get_option( $optname );
    $serial = @unserialize( $value );
    if( $serial !== FALSE ) $value = $serial;
    if( is_array( $value ) ) array_walk( $value, "qn_slash_option" );
    elseif( is_string( $value ) ) qn_slash_option( $value );
    return $value;
}

function theme_fp_defaultdata() {
    /*
        ATTENZIONE:
        nel settare i valori di default,
        per i campi BOOL impostare sempre 0.
    */
    $defdata = array(
        "qnheader" => array(
            "hide" => array( "bool", 0 ),
        ),
        "menuline" => array(
            "show" => array( "bool", 0 ),
            "icon" => array( "string", "" ),
            "title" => array( "string", "" ),
        ),
        "artsidebar" => array(
            "action" => array( "int", 0 ),
        ),
        "footer" => array(
            "action" => array( "int", 0 ),
            "custom" => array( "string", "" ),
        ),
        "categories" => array(
            "action" => array( "int", 0 ),
            "cats" => array( "array|int", 0 ),
            "date" => array( "string", "j F Y" ),
            "author" => array( "bool", 0 ),
        ),
        "pagination" => array(
            "active" => array( "bool", 0 ),
        ),
        "headline" => array(
            "count" => array( "int", 1 ),
            "cats" => array( "array|int", 0 ),
            "exclude" => array( "array|int", 0 ),
        ),
        "mod_realtime" => array(
            "count" => array( "int", 1 ),
            "title" => array( "string", "" ),
            "color" => array( "string", "#666666" ),
            "cats" => array( "array|int", 0 ),
            "exclude" => array( "array|int", 0 ),
        ),
        "mod_editorial" => array(
            "count" => array( "int", 3 ),
            "title" => array( "string", "" ),
            "color" => array( "string", "#666666" ),
            "cats" => array( "array|int", 0 ),
            "exclude" => array( "array|int", 0 ),
        ),
        "mod_news" => array(
            "count" => array( "int", 8 ),
            "icon" => array( "string", "" ),
            "title" => array( "string", "" ),
            "cats" => array( "array|int", 0 ),
            "exclude" => array( "array|int", 0 ),
        ),
        "mod_newssmall" => array(
            "count" => array( "int" , 12 ),
            "icon" => array( "string", "" ),
            "title" => array( "string", "" ),
            "cats" => array( "array|int", 0 ),
            "exclude" => array( "array|int", 0 ),
        ),
        "mod_quadbox" => array(
            "count" => array( "int", 4 ),
            "cats" => array( "array|int", 0 ),
            "exclude" => array( "array|int", 0 ),
        ),
        "mod_catview" => array(
            "title" => array( "string", "" ),
            "cat" => array( "int", 0 ),
            "exclude" => array( "array|int", 0 ),
        ),
        "mod_mosaic" => array(
            "icon" => array( "string", "" ),
            "title" => array( "string", "" ),
            "cats" => array( "array|int", 0 ),
            "exclude" => array( "array|int", 0 ),
        ),
    );
    return $defdata;
}

// lista inclusi per box vetrine footer
global $theme_fp_vetrine_includes;
$theme_fp_vetrine_includes = array();

function theme_fp_get_saved_data( $key = FALSE ) {
    $defdata = theme_fp_defaultdata();
    $data = array();
    foreach( $defdata as $tree => $opts ) {
        if( $key !== FALSE && $key != $tree ) continue;
        $data[$tree] = array();
        foreach( $opts as $opt => $value ) {
            $optname = theme_fp_option( $tree, $opt );
            $newvalue = qn_get_option( $optname );
            $data[$tree][$opt] = qn_default_value( $newvalue, $value[0], $value[1] );
        }
    }
    //echo "<pre>" . print_r( $data, true ) . "</pre>\n";
    if( $key !== FALSE && count( $data ) == 1 ) $data = array_shift( $data );
    return $data;
}

function theme_dump_var($var, $short = TRUE) {
    $repr = var_export($var, TRUE);
    $complete = "<em>(".gettype($var).")</em> $repr";
    $return = "<strong>" . ($short ? $repr : $complete) . "</strong>";
    return $return;
}

function theme_save_front_page_settings() {
    if( ! current_user_can('administrator') ) wp_die( 'Non sei autorizzato a visualizzare queste opzioni.' );
    $defdata = theme_fp_defaultdata();
    $data = theme_fp_get_saved_data();
    if( @$_POST["update_settings"] !== "SI" ) return $data;
    $changedinfo = array( "new" => array(), "done" => array(), "nope" => array(), "errors" => array() );
    $errs = array();
    foreach( $data as $tree => &$opts ) {
        foreach( $opts as $opt => &$value ) {
            $newvalue = isset( $_POST[$tree][$opt] ) ? $_POST[$tree][$opt] : $defdata[$tree][$opt][1];
            if( is_array( $newvalue ) ) array_walk($newvalue, "qn_slash_option", TRUE);
            elseif( is_string( $newvalue ) ) qn_slash_option($newvalue, FALSE, TRUE);
            $updkey = theme_fp_option( $tree, $opt );
            $updvalue = qn_default_value( $newvalue, $defdata[$tree][$opt][0], $defdata[$tree][$opt][1] );
            $oldvalue = qn_get_option( $updkey );
            //echo "<p>Updating $tree/$opt from ";var_dump($oldvalue);echo" to ";var_dump($updvalue);echo "...</p>\n";
            if( $oldvalue === FALSE ) {
                $value = $updvalue;
                if( add_option( $updkey, $updvalue ) ) {
                    //echo "<div class=\"updated\">Creato $updkey to ";var_dump($updvalue);echo "!</div>\n";
                    $ck = "new";
                } else {
                    //echo "<div class=\"error\">Impossibile creare $updkey to ";var_dump($updvalue);echo "!</div>\n";
                    $errs[] = "Impossibile creare $updkey &rarr; ".theme_dump_var($updvalue);
                    $ck = "errors";
                }
            } elseif( $oldvalue === $updvalue ) {
                $ck = "nope";
            } elseif( update_option( $updkey, $updvalue ) ) {
                $value = $updvalue;
                //echo "<div class=\"updated\">Aggiornato $updkey to ";var_dump($updvalue);echo "!</div>\n";
                $ck = "done";
            } else {
                /*echo "<div class=\"error\">Impossibile aggiornare $updkey : ";
                var_dump($oldvalue); echo " to "; var_dump($updvalue); echo "!</div>\n";*/
                $errs[] = "Impossibile aggiornare $updkey da ".theme_dump_var($oldvalue)." a ".theme_dump_var($updvalue);
                $ck = "errors";
            }
            $changedinfo[ $ck ][] = "{$tree}[ {$opt} ]";
        }
    }
    echo '
        <div id="message" class="updated">
            <p>
                Aggiornamento impostazioni:';
    foreach($changedinfo as $ck => $cv) {
        if($ck == "new") {
            if(!count($cv)) continue;
            $cl = "create,";
        } elseif($ck == "done") $cl = "aggiornate,";
        elseif($ck == "nope")   $cl = "invariate,";
        elseif($ck == "errors") $cl = "errori.";
        echo "\n\t\t\t\t";
        if(!count($cv)) echo count($cv) . " $cl";
        else printf('<span title="%s" style="border-bottom:1px dotted;cursor:help">%d %s</span>', implode("\n", $cv), count($cv), $cl);
    }
    echo '
            </p>
        </div>';
    if(count($errs)) {
        echo "<div class=\"error\">
            <a href=\"#\" onclick=\"this.nextElementSibling.style.display='block'\">Mostra gli errori</a>
            <ul style=\"display:none\">\n";
        foreach($errs as $error) echo "<li>$error</li>\n";
        echo "</ul>
            </div>\n";
    }
    return $data;
}

function qn_module_inner_form($module_id, $widget_id = FALSE, $new_values = array()) {
    $default_data = theme_fp_defaultdata();
    if(!isset($default_data[$module_id])) return '';
    $html = array();
    $html[] = '<div class="widget-content">';
    foreach($default_data[$module_id] as $setting_id => $setting) {
        list($setting_type, $setting_default) = $setting;
        $value = qn_default_value(@$new_values[$setting_id], $setting_type, $setting_default);
        $html_line = '';
        $input_name = $widget_id . "[$setting_id]";
        $input_id = preg_replace('/^(.+)\[(__i__|\d+)\].*$/i', '$1-$2-option-', $widget_id) . $setting_id;
        switch($setting_id) {
            case 'count':
                $html_line .= '<label for="' . $input_id . '">Conteggio box/notizie:</label> ';
                $html_line .= '<input type="text" size="3" name="' . $input_name . '" value="' . $value . '" id="' . $input_id . '" />';
                break;
            case 'icon':
                $html_line .= 'Icona di sezione: ';
                $html_line .= qn_fonticon($input_name, $input_id, $value, FALSE);
                break;
            case 'title':
                $html_line .= '<label for="' . $input_id . '">Titolo:</label> ';
                $html_line .= '<input type="text" name="' . $input_name . '" value="' . $value . '" class="widefat" id="' . $input_id . '" />';
                break;
            case 'color':
                $html_line .= '<label for="' . $input_id . '">Colore sfondo/testo:</label> ';
                $html_line .= '<input type="text" size="8" name="' . $input_name . '" value="' . $value . '" id="' . $input_id . '" />';
                break;
            case 'cats':
            case 'exclude':
                $html_line .= '<label for="' . $input_id . '">' . ($setting_id == 'cats' ? 'Includi' : 'Escludi') . ' categorie:</label> ';
                $input_id = trim(preg_replace('/[\[\]]+/', '-', $input_name), '-');
                $html_line .= cat_select($input_name, $input_id, $value, 6, FALSE);
                break;
            default:
                $html_line .= ucfirst(__($setting_id));
        }
        $html[] = "<p>$html_line</p>";
    }
    $html[] = '</div>';
    echo implode(PHP_EOL, $html);
}

function qn_module_widget($widget_base, $multi_number, $settings = array()) {
    $modules_desc = $GLOBALS['qn_nuhp_modules_desc'];
    $module_desc = (array)@$modules_desc[qn_get_nuhp_module_from_widget($widget_base)];
    $is_multi = $multi_number == '__i__' || is_int($multi_number) && $multi_number > 0;
    $multi_string = $is_multi ? '__i__' : '';
    if(count($settings) > 0) $multi_string = $multi_number;
    echo '<div id="widget-'.$widget_base.'-'.$multi_string.'" class="widget'.($is_multi ? ' ui-draggable" style="width:99%' : '').'">
                <div class="widget-top">
                    <div class="widget-title-action">
                        <a class="widget-action hide-if-no-js" href="#available-widgets"></a>
                    </div>
                    <div class="widget-title">
                        <h4>
                            '.@$module_desc['title'].'
                            <span class="in-widget-title"></span>
                        </h4>
                    </div>
                </div>
                <div class="widget-inside">
                    <form method="post" action="">';
    $mod_name = qn_get_nuhp_module_from_widget($widget_base);
    $area_name = qn_get_nuhp_area_for_module($mod_name);
    qn_module_inner_form($mod_name, "widget-{$widget_base}[{$multi_string}]", $settings);
    $widget_number = $is_multi ? (is_int($multi_number) && $multi_number > 2 ? $multi_number - 1 : '-1') : '';
    echo '				<input class="widget-id" name="widget-id" type="hidden" value="'.$widget_base.'-'.$multi_string.'" />
                        <input class="id_base" name="id_base" type="hidden" value="'.$widget_base.'" />
                        <input class="widget-width" name="widget-width" type="hidden" value="400" />
                        <input class="widget-height" name="widget-height" type="hidden" value="350" />
                        <input class="widget_number" name="widget_number" type="hidden" value="' . $widget_number . '" />
                        <input class="multi_number" name="multi_number" type="hidden" value="' . ($is_multi ? $multi_number : '') . '" />
                        <input class="add_new" name="add_new" type="hidden" value="'.($is_multi ? 'multi' : '').'" />
                        <input class="qnnuhp-modules" name="qnnuhp-modules" type="hidden" value="'.$area_name.'" />
                        <div class="widget-control-actions">
                            <div class="alignleft">
                                <a class="widget-control-remove" href="#remove">Delete</a>
                                |
                                <a class="widget-control-close" href="#close">Close</a>
                            </div>
                            <div class="alignright">
                                <input id="widget-'.$widget_base.'-'.$multi_string.'-savewidget" class="button button-primary widget-control-save right" type="submit" value="Save" name="savewidget" />
                                <span class="spinner"></span>
                            </div>
                            <br style="clear:right" />
                        </div>
                    </form>
                </div>
                <div class="widget-description">'.@$module_desc['desc'].'</div>
            </div> <!-- //draggable -->
            <!-- br class="clear" / -->';
}

function cat_select( $name, $id = FALSE, $selected = "", $multi = 6, $print = TRUE ) {
    if( ! current_user_can('administrator') ) return;
    static $idNum = 1;
    static $cats = 0;
    if( ! $id ) $id = "cat_select" . $idNum++;
    if( ! $cats ) $cats = get_categories(array('hide_empty'=>false,'orderby'=>'parent,name'));
    ob_start();

    $multiple = $opt0 = '';
    if( $multi ) {
        if( ! is_int( $multi ) ) $multi = 6;
        $multi = min( $multi, count( $cats ) );
        $multiH = max( $multi * 16, 22 );
        $multiple = sprintf( ' multiple="multiple" size="%d" style="height:%dpx"', $multi, $multiH );
        $name .= "[]";
        echo "<em style=\"display:block\" class=\"description\">Seleziona zero o più categorie</em>\n";
    } else {
        $opt0 = "\t<option value=\"\">Seleziona</option>\n";
    }
    printf( "<select name=\"%s\" id=\"%s\"%s>\n%s", $name, $id, $multiple, $opt0 );
    foreach( $cats as $cat ) {
        $sel = (
            (array)$selected[0] && (
                in_array( $cat->cat_ID, (array)$selected ) ||
                in_array( $cat->category_nicename, (array)$selected ) ||
                in_array( $cat->cat_name, (array)$selected )
            )
        ) ? ' selected="selected"' : '';
        $opt_id = $id . "-" . $cat->cat_ID;
        printf( "\t<option id=\"%s\" value=\"%d\"%s>%s</option>\n", $opt_id, $cat->cat_ID, $sel, $cat->cat_name );
    }
    echo "</select>\n";

    $ret = ob_get_contents();
    ob_end_clean();

    if( $print ) return print( $ret );
    return $ret;
}

function active_checkbox( $name, $id = FALSE, $checked = 0, $print = TRUE ) {
    if( ! current_user_can('administrator') ) return;
    static $idNum = 1;
    if( ! $id ) $id = "active_checkbox" . $idNum++;
    $check = $checked ? ' checked="checked"' : '';
    ob_start();
        printf('<input type="checkbox" name="%s" id="%s" %s value="SI" />', $name, $id, $check);
        $checkbox = ob_get_contents();
    ob_end_clean();
    if( $print ) return print( $checkbox );
    return $checkbox;
}

function qn_fonticon( $name, $id = FALSE, $value = "", $print = TRUE ) {
    if( ! current_user_can('administrator') ) return;
    static $idNum = 1;
    if( ! $id ) $id = "qn_fonticon" . $idNum++;
    ob_start();
        echo '<div style="font-size:2em;width:50%;min-width:300px;line-height:120%">';
        foreach(qnnuhp_list_fonticons() as $fonticon) {
            $check = $fonticon == $value ? ' checked="checked"' : '';
            echo '<span style="white-space:nowrap">';
            printf('<input type="radio" name="%s" value="%s" id="%s-%2$s" %s /><label for="%3$s-%2$s">', $name, $fonticon, $id, $check);
            qnnuhp_fonticon($fonticon);
            echo '</label></span>' . PHP_EOL;
        }
        echo '</div>';
        $icon = ob_get_contents();
    ob_end_clean();
    if( $print ) return print( $icon );
    return $icon;
}

function theme_front_page_settings() {
    // Check that the user is allowed to update options
    if( ! current_user_can('administrator') ) wp_die( 'Non sei autorizzato a visualizzare queste opzioni.' );
    $loadscripts = explode(',', 'jquery-ui-core,jquery-ui-widget,jquery-ui-mouse,jquery-ui-sortable,jquery-ui-draggable,jquery-ui-dr&amp;load%5B%5D=oppable,admin-widgets');
    foreach($loadscripts as $loadscript) wp_enqueue_script($loadscript);
    $modules_ajax_nonce = wp_create_nonce('qnnuhp-modules');
?>
    <div class="wrap">
        <h2>Gestione Front Page</h2>
        <?php $data = theme_save_front_page_settings(); ?>
        <br />
        <form action="" method="post">
            <input type="hidden" name="update_settings" value="SI" />
            <h3>Opzioni valide per tutto il sito</h3>
            <table class="form-table" id="sitewide-options">
                <tr valign="top" id="header-options">
                    <th scope="row">
                        <label for="qnheader-hide">Impostazione header QN</label>
                    </th>
                    <td colspan="2"><p class="description">
                        <?php active_checkbox("qnheader[hide]", "qnheader-hide", $data["qnheader"]["hide"]); ?>
                        L'header QN comprende link alle ultime notizie dai maggiori siti del network.
                        Selezionare per nasconderlo (solo nel caso la grafica lo richieda).
                    </p></td>
                </tr>
                <tr valign="top" id="menuline-options">
                    <th scope="row">Impostazioni linea link</th>
                    <td colspan="2">
                        <p>
                            <?php active_checkbox("menuline[show]", "menuline-show", $data["menuline"]["show"]); ?>
                            <label for="menuline-show">Mostra un menu simile alla lista edizioni di Q.Net</label>
                        </p>
                        <p>
                            <em>Scegli l'icona da mostrare prima della lista di link</em>
                            <?php qn_fonticon("menuline[icon]", "menuline-icon", $data["menuline"]["icon"]); ?>
                        </p>
                        <p>
                            <input type="text" name="menuline[title]" id="menuline-title" value="<?php echo $data["menuline"]["title"]; ?>" />
                            <label for="menuline-title">Titolo da mostrare prima della lista di link</label>
                        </p>
                    </td>
                </tr>
                <tr valign="top" id="pagination-options">
                    <th scope="row">Impostazioni paginazione</th>
                    <td colspan="2"><p class="description">
                        <?php active_checkbox("pagination[active]", "pagination-active", $data["pagination"]["active"]); ?>
                        Mostra controlli di navigazione per gli articoli paginati nelle viste d'archivio.
                    </p></td>
                </tr>
                <tr valign="top" id="footer-options">
                    <th scope="row">Impostazione footer</th>
                    <td>
                        <?php
                        $footer_options = array(
                            0 => "Mantieni footer Quotidiano.net",
                            1 => "Nascondi qualsiasi footer",
                            2 => "Usa footer personalizzato &rarr;"
                        );
                        $footer_input = '<input type="radio" id="footer-action-%d" name="footer[action]" value="%1$d" %s />
                            <label for="footer-action-%1$d">%s</label><br />';
                        foreach( $footer_options as $footer_option_id => $footer_option_label ) {
                            $footer_selected = $footer_option_id == $data["footer"]["action"] ? 'checked="checked"' : '';
                            printf( $footer_input, $footer_option_id, $footer_selected, $footer_option_label );
                        }
                        ?>
                    </td>
                    <td>
                        <label class="description" for="footer-custom">Footer HTML personalizzato:</label><br />
                        <textarea class="widefat" id="footer-custom" name="footer[custom]"><?php echo $data["footer"]["custom"]; ?></textarea>
                    </td>
                </tr>
                <tr valign="top" id="artsidebar-options">
                    <th scope="row">Impostazioni colonna destra</th>
                    <td colspan="2">
                        <?php
                        $artsidebar_options = array(
                            0 => "Mostra coldx globale anche negli articoli",
                            1 => "Mostra coldx globale sotto a quella degli articoli",
                            2 => "Non mostrare coldx globale negli articoli",
                        );
                        $artsidebar_input = '<input type="radio" id="artsidebar-action-%d" name="artsidebar[action]" value="%1$d" %s />
                            <label for="artsidebar-action-%1$d">%s</label><br />';
                        foreach($artsidebar_options as $artsidebar_option_id => $artsidebar_option_label) {
                            $artsidebar_selected = $artsidebar_option_id == $data["artsidebar"]["action"] ? 'checked="checked"' : '';
                            printf($artsidebar_input, $artsidebar_option_id, $artsidebar_selected, $artsidebar_option_label);
                        }
                        ?>
                    </td>
                </tr>
                <tr valign="top" id="categories-options">
                    <th scope="row">Impostazioni occhielli</th>
                    <td>
                        <p>
                        <?php
                            $categories_options = array(
                                0 => "Mostra tutte le categorie come occhiello",
                                1 => "Nascondi tutte le categorie",
                                2 => "Nascondi specifiche categorie &rarr;"
                            );
                            $categories_input = '<input type="radio" id="categories-action-%d" name="categories[action]" value="%1$d" %s />
                            <label for="categories-action-%1$d">%s</label><br />';
                            foreach( $categories_options as $categories_option_id => $categories_option_label ) {
                                $categories_selected = $categories_option_id == $data["categories"]["action"] ? 'checked="checked"' : '';
                                printf( $categories_input, $categories_option_id, $categories_selected, $categories_option_label );
                            }
                        ?>
                        </p>
                        <p>
                            <?php active_checkbox("categories[author]", "categories-author", $data["categories"]["author"]); ?>
                            <label for="categories-author">Mostra autore del post</label>
                        </p>
                        <p>
                            <label for="categories-date">Mostra data e ora:</label>
                            <input type="text" id="categories-date" name="categories[date]" value="<?php echo $data["categories"]["date"]; ?>" />
                        </p>
                        <p class="description">
                            Lasciare vuoto per nascondere la data,<br />altrimenti utilizzare il
                            <a href="http://it2.php.net/manual/en/function.date.php" target="_blank">formato data di PHP</a>.
                        </p>
                    </td>
                    <td>
                        <label class="description" for="categories-cats">Categorie da nascondere eventualmente:</label><br />
<?php					cat_select( "categories[cats]", "categories-cats", $data["categories"]["cats"] ); ?>
                    </td>
                </tr>
            </table>
            <h3>Sezione articoli in apertura</h3>
            <table class="form-table" id="headline-options">
                <tr valign="top">
                    <th scope="row">
                        <label for="headline-count">Numero di notizie da mostrare</label>
                    </th>
                    <td>
                        <input type="number" id="headline-count" name="headline[count]" value="<?php echo $data["headline"]["count"]; ?>" />
                        <p class="description">
                            N.B. Tipicamente il layout contiene una singola notizia d'apertura.
                        </p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">
                        <label>Categorie degli articoli</label>
                    </th>
                    <td>
                        <table id="headline-cats-options">
                            <tr>
                                <td>
                                    <label for="headline-cats">Includi:</label>
<?php								cat_select( "headline[cats]", "headline-cats", $data["headline"]["cats"] ); ?>
                                </td>
                                <td>
                                    <label for="headline-exclude">Escludi:</label>
<?php								cat_select( "headline[exclude]", "headline-exclude", $data["headline"]["exclude"] ); ?>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
            <p><input type="submit" value="Registra modifiche" class="button-primary" /></p>
        </form>
<?php
        $areas = $GLOBALS['qn_nuhp_modules'];
        $opt_areas = get_option('qnnuhp_areas_modules', array());
        $opt_modules = get_option('qnnuhp_modules_options', array());
?>
        <div id="<?php echo $area; ?>-modules" style="padding:20px 0;clear:both">
            <div style="float:left;clear:left;width:300px">
                <div id="widgets-left">
                    <div id="available-widgets" class="widgets-holder-wrap ui-droppable">
                        <div class="sidebar-name">
                            <h4>Moduli</h4>
                        </div>
                        <div class="widget-holder">
                            <div id="widget-list">
<?php
                            foreach($areas as $area => $modules) {
                                echo '<h4>' . ucwords(str_replace('-', ' ', $area)) . '</h4>';
                                foreach($modules as $module) {
                                    $multi_number = 1;
                                    $widget_base = substr($module, 4);
                                    if(!empty($opt_areas[$area])):
                                    foreach($opt_areas[$area] as $area_module) {
                                        if(!preg_match('/^([\w-]+)-(\d+)$/', $area_module, $m)) continue;
                                        list(,$m_widget_base, $m_multi_number) = $m;
                                        if($m_widget_base != $widget_base) continue;
                                        $multi_number = max($multi_number, (int)$m_multi_number);
                                    }
                                    endif;
                                    qn_module_widget($widget_base, $multi_number + 1);
                                }
                            }
?>
                            </div> <!-- //widget-list -->
                            <br class="clear" />
                        </div> <!-- //widget-holder -->
                    </div> <!-- //available-widgets -->
                </div> <!-- //widgets-left -->
            </div> <!-- //float:left -->
            <div style="margin-left:320px">
                <div id="widgets-right">
<?php
                foreach($areas as $area => $modules) {
?>
                    <div class="widgets-holder-wrap">
                        <div id="qnnuhp-modules_<?php echo $area; ?>" class="widgets-sortables ui-sortable">
                            <div class="sidebar-name">
                                <div class="sidebar-name-arrow"><br /></div>
                                <h3>
                                    Moduli in <?php echo ucwords(str_replace('-', ' ', $area)); ?>
                                    <span class="spinner"></span>
                                </h3>
                            </div>
                            <div class="sidebar-description">
                                <p class="description">Trascina qui i moduli da inserire come componenti nello spazio <?php echo $area; ?> dell'Home Page.</p>
                            </div>
<?php
                            // ATTIVARE LE RIGHE SEGUENTI PER RESETTARE AREE MODULI
                            //delete_option('qnnuhp_areas_modules');
                            //delete_option('qnnuhp_modules_options');
                            //

                            if(!empty($opt_areas[$area])):
                            foreach($opt_areas[$area] as $area_module) {
                                //if(!array_key_exists($area_module, $opt_modules)) continue;
                                if(!preg_match('/^([\w-]+)-(\d+)$/', $area_module, $m)) continue;
                                list(,$widget_base, $multi_number) = $m;
                                qn_module_widget($widget_base, (int)$multi_number, @$opt_modules[$area_module]);
                            }
                            endif;
?>
                        </div>
                        <!-- div class="clear"></div -->
                    </div> <!-- //widgets-holder-wrap -->
<?php
                }
?>
                </div> <!-- //widgets-right -->
            </div> <!-- //margin-left -->
            <form method="post" action="">
                <input id="_wpnonce_widgets" type="hidden" value="<?php echo $modules_ajax_nonce; ?>" name="_wpnonce_widgets">
            </form>
            <br class="clear" />
        </div> <!-- //home-articles-modules -->
        <div class="widgets-chooser">
            <ul class="widgets-chooser-sidebars"></ul>
            <div class="widgets-chooser-actions">
                <button class="button-secondary">Cancel</button>
                <button class="button-primary">Add Widget</button>
            </div>
        </div>
    </div> <!-- //wrap -->
<?php
}

/*
    Qui comincia la modifica post direttamente dall'elenco
    per l'assegnazione in home page - sezione evidenza.
    http://shibashake.com/wordpress-theme/expand-the-wordpress-quick-edit-menu
*/

add_filter( "manage_posts_columns", "qn_columns" );
function qn_columns( $old_columns ) {
    $img = '<img alt="%s" src="/wp-content/themes/QN_NuHP/style/%s.png" title="%1$s" />';
    $new_columns = array(
        "featured" => sprintf( $img, "Evidenza", "evidenza" )
    );
    $columns = array_merge( $old_columns, $new_columns );
    return $columns;
}

add_filter( "manage_posts_custom_column", "qn_show_columns" );
function qn_show_columns( $name ) {
    global $post;
    switch( $name ) {
        case "featured":
                $opt = theme_fp_get_saved_data( "headline" );
                $featured = array_search( $post->ID, (array)$opt["featured"] );
                printf( "<em>%s</em>", $featured ? "Articolo $featured" : "&nbsp;" );
            break;
    }
}

add_filter( "manage_edit-post_sortable_columns", "qn_sortables_columns" );
function qn_sortables_columns( $columns = array() ) {
    $columns["featured"] = "featured";
    return $columns;
}
add_filter( "request", "qn_sort_columns" );
function qn_sort_columns( $vars = array() ) {
    if( @$vars["orderby"] == "featured" ) {
        $opt = theme_fp_get_saved_data( "headline" );
        $vars["post__in"] = (array)$opt["featured"];
        $vars["orderby"] = "date";
    }
    return $vars;
}

add_filter( "quick_edit_custom_box", "qn_quick_edit", 10, 2 );
function qn_quick_edit( $column_name, $post_type ) {
    $headline = theme_fp_get_saved_data( "headline" );
    switch( $column_name ) {
        case "featured":
?>
    <fieldset class="inline-edit-col-right">
        <div class="inline-edit-col">
        <label id="inline-edit-featured">
            <span class="title">In Evidenza</span>
            <?php
        if( ! $headline["count"] ) echo "<em>[disabilitato]</em>";
        else {
            echo '<select id="headline-featured" name="headline-featured">' . PHP_EOL;
            echo "\t<option value=\"0\">Non considerato</option>" . PHP_EOL;
            for( $n = 1; $n <= (int)$headline["count"]; $n++ ) {
                echo "\t<option value=\"$n\">Articolo $n</option>" . PHP_EOL;
            }
            echo "</select>" . PHP_EOL;
        }
            ?>
        </label>
        </div>
    </fieldset>
<?php
            break;
    }
}

add_filter( "save_post", "qn_quick_edit_save" );
function qn_quick_edit_save( $post_id ) {
    if ( ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE )
        || $_POST["post_type"] != "post"
        || ! current_user_can( "edit_post", $post_id )
        || ! isset( $_POST["headline-featured"] )
        )  return $post_id;
    $headline = theme_fp_get_saved_data( "headline" );
    $num = $_POST["headline-featured"];
    $i = array_search( $post_id, $headline["featured"] );
    if( $i !== FALSE ) $headline["featured"][$i] = FALSE;
    if( $num ) $headline["featured"][ $num ] = $post_id;
    ksort( $headline["featured"] );
    update_option( theme_fp_option( "headline", "featured" ), $headline["featured"] );
    return $num;
}

add_action( "admin_footer", "qn_quick_edit_javascript" );
function qn_quick_edit_javascript() {
    global $current_screen;
    if (($current_screen->id != 'edit-post') || ($current_screen->post_type != 'post')) return;
?>
    <script type="text/javascript">
    <!--
        function set_qn_quick_edit_set( featured ) {
            inlineEditPost.revert();
            var select = document.getElementById( "headline-featured" );
            for( var i = 0; i < select.options.length; i++ ) select.options[i].removeAttribute( "selected" );
            select.options[ featured ].setAttribute( "selected", "selected" );
        }
    // -->
    </script>
<?php
}

add_filter( "post_row_actions", "qn_expand_quick_edit_link", 10, 2 );
function qn_expand_quick_edit_link( $actions, $post ) {
    global $current_screen;
    if (($current_screen->id != 'edit-post') || ($current_screen->post_type != 'post')) return $actions;
    $headline = theme_fp_get_saved_data( "headline" );
    $i = (int)array_search( $post->ID, (array)$headline["featured"] );
    $row = $actions['inline hide-if-no-js'];
    $act = '<a href="#" class="editinline" title="';
    $act .= esc_attr( __( 'Edit this item inline' ) ) . '" ';
    $act .= " onclick=\"set_qn_quick_edit_set($i)\">"; 
    $act .= __( 'Quick&nbsp;Edit' );
    $act .= '</a>';
    $row = preg_replace( '/<a[^>]+class="[^"]*editinline[^"]*"[^>]*>[^<]+<\/a>/i', $act, $row );
    $actions['inline hide-if-no-js'] = $row;
    return $actions;
}

add_action('add_meta_boxes', 'qn_meta_boxes', 10, 2);
function qn_meta_boxes($post_type, $post) {
    add_meta_box('qnsocialshare', 'QN NuHP tools', 'qn_post_socialshare', $post_type, 'side', 'core');
}

function qn_post_socialshare($post) {
    wp_nonce_field('qn_socialshare_meta_box', 'qn_socialshare_meta_box_nonce');
    $hide_ss = get_post_meta($post->ID, '_qn_post_hide_socialshare', true);
    $hide_img = get_post_meta($post->ID, '_qn_post_hide_image', true);
    $value = 'SI';
    $check_ss = $hide_ss == $value ? 'checked="checked"' : '';
    $check_img = $hide_img == $value ? 'checked="checked"' : '';
    $id_ss = 'qn_post_hide_socialshare';
    $id_img = 'qn_post_hide_image';
    echo <<<echoHere
        <input id="$id_ss" name="$id_ss" type="checkbox" value="$value" $check_ss />
        <label for="$id_ss">Nascondi strumenti social e condivisione</label><br />
        <input id="$id_img" name="$id_img" type="checkbox" value="$value" $check_img />
        <label for="$id_img">Nascondi immagine in evidenza</label>
echoHere;
}

add_action('save_post', 'qn_save_post_metaboxes');
function qn_save_post_metaboxes($post_id) {
    # Verifiche varie per procedere al salvataggio o meno del meta
    $nonceid = 'qn_socialshare_meta_box';
    $nonce = $nonceid . '_nonce';
    if(!isset($_POST[$nonce])) return $post_id;
    if(!wp_verify_nonce($_POST[$nonce], $nonceid)) return $post_id;
    if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return $post_id;
    if(@$_POST['post_type'] == 'page' && !current_user_can('edit_page', $post_id)) return $post_id;
    elseif(!current_user_can('edit_post', $post_id)) return $post_id;
    # Finite le verifiche passo ai meta veri e propri
    ## META: qn_post_hide_socialshare (nasconde strumenti condivisione su articolo)
    $hide_ss = !empty($_POST['qn_post_hide_socialshare']) ? 'SI' : 'NO';
    $hide_img = !empty($_POST['qn_post_hide_image']) ? 'SI' : 'NO';
    update_post_meta($post_id, '_qn_post_hide_socialshare', $hide_ss);
    update_post_meta($post_id, '_qn_post_hide_image', $hide_img);
}

