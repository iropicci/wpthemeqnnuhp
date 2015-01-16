<?php qn2011_sidebar('Chiusura pagina'); ?>

</section>

<?php

global $fp;
$footer_options = $fp["footer"];
switch( $footer_options["action"] ) {
	case 0: // Standard Quotidiano.net Footer
		include "/www/edit_generali/include/nuhp/footer.html";
		break;
	case 2: // Custom HTML Footer
		echo "<footer class=\"footer lp_footer\">\n";
		echo htmlspecialchars_decode( @$footer_options["custom"] );
		echo "\n</footer>\n";
		break;
	case 1: // Do not display any footer
	default:
		break;
}
wp_footer();

?>

</div> <!-- //wrapper -->

<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
<script>window.jQuery || document.write('<script src="http://www.stqn.it/nuhp_static/js/vendor/jquery-1.10.2.min.js"><\/script>')</script>
<!-- script src="http://www.stqn.it/nuhp_static/js/combined.min.js"></script -->
<script src="/file_generali/disqus_test/nuhp_static/js/combined.min.js"></script>
<script>QN.init();</script>

<?php qn2011_sidebar('Chiusura documento'); ?>

</body>
</html>
