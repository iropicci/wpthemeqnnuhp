<?php

// BANNER (testo senza div)
class QNBanner_Widget extends WP_Widget {
	function __construct() {
		$widget_ops = array('classname' => 'qnbanner_widget', 'description' => __('Spazio bianco per banner/tracciamento'));
		$control_ops = array('width' => 400, 'height' => 350);
		parent::__construct('qnbanner_widget', 'QN Banner Widget', $widget_ops, $control_ops);
	}
	function widget( $args, $instance ) {
		extract($args);
		$text = apply_filters( 'widget_text', empty( $instance['text'] ) ? '' : $instance['text'], $instance );
		echo "$before_widget\n$text\n$after_widget";
	}
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		if ( current_user_can('unfiltered_html') ) $instance['text'] =  $new_instance['text'];
		else $instance['text'] = stripslashes( wp_filter_post_kses( addslashes($new_instance['text']) ) );
		return $instance;
	}
	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'text' => '' ) );
		$text = esc_textarea($instance['text']);
		echo '
		<textarea class="widefat" rows="16" cols="20" id="' . $this->get_field_id('text') . '" name="' . $this->get_field_name('text') . '">' . $text . "</textarea>\n";
	}
} // class QNBanner_Widget

// MOSTRA ARTICOLI COL DX
class QNPostList_Widget extends WP_Widget {
	private $default_values;
	function __construct() {
		$widget_options = array("description" => "Mostra gli ultimi post di una categoria WP");
		$control_options = array("id_base" => "qnpostlist_widget");
		$this->default_values = array("count" => "5");
		parent::__construct("qnpostlist_widget", "QN PostList Widget", $widget_options, $control_options);
	}
	public function widget($args, $instance) {
		extract($args);
		$instance = wp_parse_args((array)$instance, $this->default_values);
		$title = apply_filters("widget_title", $instance["title"]);
		$cat = (int)preg_replace("/\D+/", "", $instance["cat"]);
		$count = (int)preg_replace("/\D+/", "", $instance["count"]);
		$icon = strip_tags($instance["icon"]);
		$wp_args = array(
			"cat" => $cat,
			"posts_per_page" => $count,
			"orderby" => "date",
			"order" => "DESC",
		);
		$posts = new WP_Query($wp_args);
		global $post;
		echo $before_widget;
		echo '<div class="block1">';
		global $pic_args;
		$pic_args = NULL;
		if($icon) $pic_args = array("icon" => $icon);
		$display_args = array();
		if($title) $display_args['title'] = $before_title . $title . $after_title;
		if($posts->have_posts()) display_posts($posts->posts, 'col_newsauto');
		wp_reset_postdata();
		echo '</div>';
		echo $after_widget;
	}
	public function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$instance["title"] = strip_tags($new_instance["title"]);
		$instance["icon"] = strip_tags($new_instance["icon"]);
		$instance["cat"] = preg_replace("/\D+/", "", $new_instance["cat"]);
		$instance["count"] = preg_replace("/\D+/", "", $new_instance["count"]);
		return $instance;
	}
	public function form($instance) {
		$instance = wp_parse_args((array)$instance, $this->default_values);
		$fields = array(
			"title" => "Titolo box",
			"cat" => "ID categoria da mostrare",
			"count" => "Numero notizie da mostrare",
			"icon" => "Codice icona da mostrare (facoltativo)",
		);
		foreach($fields as $field => $title):
			$name = $this->get_field_name($field);
			$ID = $this->get_field_id($field);
			echo <<<HereField
	<p>
		<label for="$ID">$title</label>
		<input class="widefat" id="$ID" name="$name" type="text" value="{$instance[$field]}" />
	</p>
HereField;
		endforeach;
	}
}

// ULTIMI ARTICOLI PER CATEGORIA
class QNLatest_Widget extends WP_Widget {
	private $default_values;
	public function __construct() {
		$widget_options = array( "description" => "Mostra gli ultimi articoli Q.net per la categoria scelta" );
		$control_options = array( "id_base" => "qnlatest_widget" );
		$this->default_values = array( "title" => "Altri articoli", "source" => "gossip" );
		parent::__construct( "qnlatest_widget", "QN Latest Widget", $widget_options, $control_options );
	}
	public function widget( $args, $instance ) {
		extract( $args );
		$instance = wp_parse_args( (array)$instance, $this->default_values );
		$title = apply_filters( "widget_title", $instance["title"] );
		$source = preg_replace( "/\W+/", "", $instance["source"] );
		echo $before_widget;
		if( $title ) echo $before_title . $title . $after_title;
		include @"/www/edit_generali/news_pag/liz/news_cat_$source.shtml";
		echo $after_widget;
	}
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance["title"] = strip_tags( $new_instance["title"] );
		$instance["source"] = $new_instance["source"];
		return $instance;
	}
	public function form( $instance ) {
		$instance = wp_parse_args( (array)$instance, $this->default_values );
		$tID = $this->get_field_id("title");
		$tName = $this->get_field_name("title");
		$sID = $this->get_field_id("source");
		$sName = $this->get_field_name("source");
?>
	<p>
		<label for="<?php echo $tID; ?>">Titolo box</label>
		<input class="widefat" id="<?php echo $tID; ?>" name="<?php echo $tName; ?>" type="text" value="<?php echo $instance["title"]; ?>" />
	</p>
	<p>
		<label for="<?php echo $sID; ?>">Sorgente/categoria</label>
		<select class="widefat" id="<?php echo $sID; ?>" name="<?php echo $sName; ?>">
<?php
		$sources = array(
			"qnet", "basket", "caffe", "calciomercato", "calcio", "ciclismo", "cinema", "conafi", "cronaca", "cultura",
			"curiosita", "due_ruote", "economia", "esteri", "formula1", "gossip", "moda", "motomondiale",
			"motori", "musica", "pazzo_mondo", "politica", "primo_piano", "pubbliredazionali", "rugby",
			"salute", "sci", "spettacoli", "sport", "tecnologia", "tennis", "tv", "vela", "volley"
		);
		foreach( $sources as $source )
			echo "\t\t\t<option value=\"$source\"" . (
				$source == $instance["source"] ? ' selected="selected"' : ''
				) . ">$source</option>\n";
?>
		</select>
<?php
	}
} // class QNLatest_Widget

// BOX INCLUSO RANDOM
class QNInclude_Widget extends WP_Widget {
	public function __construct() {
		$widget_options = array( "description" => "Include un file via HTTP" );
		$control_options = array( "id_base" => "qninclude_widget" );
		parent::WP_Widget( "qninclude_widget", "QN Include Widget", $widget_options, $control_options );
	}
	public function widget( $args, $instance ) {
		extract( $args );
		$title = apply_filters( "widget_title", (string)@$instance["title"] );
		$url = (string)@$instance["url"];
		if( !$url || parse_url( $url ) === FALSE ) return;
		echo $before_widget;
		if( $title ) echo $before_title . $title . $after_title;
		echo @file_get_contents( $url );
		echo $after_widget;
	}
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance["title"] = $new_instance["title"];
		$instance["url"] = strip_tags( $new_instance["url"] );
		return $instance;
	}
	public function form( $instance ) {
		$fields = array(
			"title" => "Titolo box",
			"url" => "URL incluso"
		);
		foreach( $fields as $var => $label ) {
			$id = $this->get_field_id( $var );
			$name = $this->get_field_name( $var );
			echo '
	<p>
		<label for="' . $id . '">' . $label . '</label>
		<input class="widefat" id="' . $id . '" name="' . $name . '" type="text" value="' . @$instance[$var] . '" />
	</p>';
		}
	}
}

// TAB PIU LETTI ETC
class QNTabs_Widget extends WP_Widget {
	public function __construct() {
		$widget_options = array("description" => "Crea uno o più box organizzati a tab contenenti inclusi");
		$control_options = array("id_base" => "qntabs_widget");
		parent::WP_Widget("qntabs_widget", "QN Tabs Widget", $widget_options, $control_options);
	}
	public function form($instance=array()) {
		$fields = array("title"=>"Titolo tab", "include"=>"Path/URL incluso -o- HTML");
		$toCount = @$instance[array_shift(array_keys($fields))];
		$tabsNo = count($toCount ? (array)$toCount : array()) + 1;
		$p_style = "margin:0 0 4px 0;border-bottom:1px solid #DFDFDF;padding:0 0 4px 0";
		$fid = "boxid";
		$id = $this->get_field_id($fid);
		$name = $this->get_field_name($fid);
		$value = @$instance[$fid];
		echo "
		<p style=\"$p_style\">
			<label for=\"$id\">ID box</label>
			<input class=\"widefat\" id=\"$id\" name=\"$name\" type=\"text\" value=\"$value\" />
		</p>";
		for($i = 0; $i < $tabsNo; $i++) {
			echo "
			<p style=\"$p_style\">
				<strong>TAB $i</strong>";
			foreach($fields as $k => $label) {
				$id = $this->get_field_id($k) . "_$i";
				$name = $this->get_field_name($k) . "[$i]";
				$value = @$instance[$k][$i] ? str_replace('"', '&quot;', $instance[$k][$i]) : "";
				echo "
				<br /><label for=\"$id\">$label</label>
				<input class=\"widefat\" id=\"$id\" name=\"$name\" type=\"text\" value=\"$value\" />";
			}
			echo "</p>\n";
		}
		$addBtnID = $this->get_field_id("add");
		echo<<<Here
		<span id="$addBtnID">[+] Aggiungi tab</span>
		<script type="text/javascript">
			function qnlist_widget_init() {
				var btn = document.getElementById("$addBtnID");
				btn.onclick = function(){
					var ps = this.parentNode.getElementsByTagName("p");
					var n = ps.length - 1;
					var p = ps[ps.length-1].cloneNode(true);
					this.parentNode.insertBefore(p, this);
					var inputs = p.getElementsByTagName("input");
					var strong = p.getElementsByTagName("strong")[0];
					if(strong) strong.innerHTML = strong.innerHTML.replace(/\d+$/, n);
					for(var i=0; i < inputs.length; i++) {
						inputs[i].id = inputs[i].id.replace(/\d+$/, n);
						inputs[i].previousElementSibling.htmlFor = inputs[i].id;
						inputs[i].name = inputs[i].name.replace(/\[\d+\]$/, "["+n+"]");
						inputs[i].value = "";
					}
				}
				var ps = btn.parentNode.getElementsByTagName("p");
				for(var i=2; i < ps.length; i++) {
					ps[i].style.position = "relative";
					var del = document.createElement("span");
					with(del.style) {
						position = "absolute";
						top = "0"; right = "0";
					}
					del.innerHTML = "[-] Elimina tab";
					ps[i].insertBefore(del, ps[i].children[1]);
					del.onclick = function(){
						this.parentNode.parentNode.removeChild(this.parentNode);
					}
				}
			}
			qnlist_widget_init();
		</script>
Here;
	}
	public function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$instance["boxid"] = (string)@$new_instance["boxid"];
		$instance["title"] = $instance["include"] = array();
		foreach((array)$new_instance["title"] as $i => $title) {
			if(!($include = $new_instance["include"][$i])) continue;
			$instance["title"][] = $title;
			$instance["include"][] = $include;
		}
		return $instance;
	}
	public function widget( $args, $instance ) {
		extract($args);
		$boxid = (string)@$instance["boxid"];
		echo "
		<div id=\"$boxid\" class=\"boxtabs\">
		<div class=\"boxtabs_content\">";
		foreach((array)$instance["title"] as $i => $title) {
			if(!$title) continue;
			$title = apply_filters("widget_title", (string)$title);
			$url = $instance["include"][$i];
			echo "
			<div class=\"boxdx\">
				<span class=\"titolo_box\">$title</span>
				<div class=\"boxdx_news\">\n";
			echo preg_match("@^(/|http)@i", $url) ? @file_get_contents($url) : $url;
			echo "
				</div>
			</div>";
		}
		echo "
		</div>\n</div>
		<script type=\"text/javascript\">QN.init('boxtabs');</script>\n";
	}
}


// REGISTRA WIDGET DEL TEMA
function register_theme_widgets() {
	if( ! function_exists( "register_widget" ) ) return;
	$widgets = array(
		"QNBanner_Widget", "QNLatest_Widget",
		"QNInclude_Widget", "QNTabs_Widget",
		"QNPostList_Widget",
	);
	foreach( $widgets as $w ) register_widget( $w );
}
add_action( "widgets_init", "register_theme_widgets" );

