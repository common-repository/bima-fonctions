<?php
/**
* Plugin Name: BimaFonctions
* Plugin URI: https://bima.re/creation/bimafonctions/
* Description: Ajoute plusieurs fonctions utiles à votre site: 1.Shortcode polylang [lang-switch] 2.Transfert automatique de toutes les erreurs 404 vers la homepage 3.Suppression de la barre d'admin pour les non admin 4.Redirection automatique [redirect url='https://www.url.destination' sec='nbdesecondes'] 5.La mise à jour automatique du panier de woocommerce et la suppression du bouton "mise-à-jour du panier" dans la page "panier"
* Author: Patrice Bima (Bima.RE Informatique et services)
* Version: 0.3.4
* Author URI: https://bima.re/
*/

/* Enregistrement du groupe des options */
			function bima_enr_options() { //étape 2 Enregistrement des options
				register_setting('bima_grp_options','bima_opt_redir'); //étape3 donne le nom de l'option qui fera partie du groupe d'options et qui activera ou désactivera notre callback
				register_setting('bima_grp_options','bima_opt_abar'); //option qui permet l'activation/désactivation de la barre d'admin pour les non-admin
                register_setting('bima_grp_options','bima_opt_auto_cart'); //option qui permet l'activation/désactivation de la mise à jour automatique du panier
			}
			add_action('admin_init','bima_enr_options'); //étape 1 initialise le fait qu'il va y avoir des options pour notre callback
/* Enregistrement du groupe des options */


/* Toutes les fonctions */

// polylang shortcode [lang-switch]
			function bima_polylang_shortcode() {
				ob_start();
				pll_the_languages(array('show_flags'=>1,'show_names'=>0));
				$flags = ob_get_clean();
				return $flags;
			}
			add_shortcode( 'lang-switch', 'bima_polylang_shortcode' );
// polylang shortcode [lang-switch]


// Rediriger toutes les erreurs 404 vers la homepage
			add_action('init','bima_change_redir'); //ajoute l'action de changer la valeur de redirection
			function bima_change_redir(){
				$option = get_option('bima_opt_redir');
					if($option == "yes"){
					//rediriger
					if( !function_exists('bima_redir_404_to_homepage') ){
						add_action( 'template_redirect', 'bima_redir_404_to_homepage' );
						function bima_redir_404_to_homepage(){
						if(is_404()):
								wp_safe_redirect( home_url('/') );
								exit;
							endif;
						}
					}
					//rediriger
					}
			}
// Rediriger toutes les erreurs 404 vers la homepage


// Supprimer la barre d'admin pour les non-admin //ne fonctionne pas
add_action('init','bima_change_abar');
		function bima_change_abar()
	{
			$option = get_option('bima_opt_abar');
				if($option == "yes" )
				{
					//cacher la barre d'admin si non-admin
					if ( !current_user_can( 'manage_options' ) )
					{
					add_filter('show_admin_bar', '__return_false');
					//cacher la barre d'admin si non-admin
					}
				}
	}
// Supprimer la barre d'admin pour les non-admin


// Redirection apres un certain temps
			add_shortcode('redirect', 'bima_scr_do_redir');
			function bima_scr_do_redir($atts)
			{
				ob_start();
				$myURL = (isset($atts['url']) && !empty($atts['url']))?esc_url($atts['url']):"";
				$mySEC = (isset($atts['sec']) && !empty($atts['sec']))?esc_attr($atts['sec']):"0";
				if(!empty($myURL))
			{
			?>
					<meta http-equiv="refresh" content="<?php echo esc_attr($mySEC); ?>; url=<?php echo esc_attr($myURL); ?>">
					<a href="<?php echo esc_attr($myURL); ?>">Dans 10 secondes vous allez être redirigé vers la page d'accueil ou cliquez-ici pour y aller plus vite!</a>
			<?php
				}
				return ob_get_clean();
			}
// Redirection apres un certain temps


// Mise à jour automatique du panier de woocommerce
if (!defined('ABSPATH')) {
    exit; // Prevent direct access
}

/* Checking WooCommerce is active or not */
if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {

    /**
     * Custom Script
     */
    add_action('wp_footer', 'cart_update_qty_script');
    function cart_update_qty_script() {
        if (is_cart()) {
            $autoCartOption = get_option('bima_opt_auto_cart');
            
            if ($autoCartOption == "yes") {
                ob_start();
                ?>
                <style type="text/css">
                    /* Hide the update cart button */
                    .woocommerce-cart-form [name="update_cart"] {
                        display: none;
                    }
                </style>
                <script type="text/javascript">
                    jQuery(document).ready(function ($) {
                        // Enable update cart button on page load
                        $('[name="update_cart"]').removeAttr('disabled');

                        // Enable update cart button when cart totals are updated
                        $(document.body).on('updated_cart_totals', function () {
                            $('[name="update_cart"]').removeAttr('disabled');
                        });

                        // Trigger update cart button click when quantity changes
                        $('div.woocommerce').on('change', '.qty', function () {
                            $('[name="update_cart"]').trigger('click');
                        });
                    });
                </script>
                <?php
                echo ob_get_clean();
            }
        }
    }
}

// Mise à jour automatique du panier de woocommerce



/* Toutes les fonctions */


/* Création d'une page d'option */

// Lien de l'extensions vers la page réglage dans le menu
add_filter( 'plugin_action_links_'.plugin_basename( __FILE__ ), 'bima_settings_action_links', 10, 2 );
function bima_settings_action_links( $links, $file ){
	array_unshift( $links, '<a href="' . admin_url( 'admin.php?page=bima-fonctions-menu' ) . '">' . __( 'Settings' ) . '</a>' );
    return $links;
}
// Lien de l'extensions vers la page réglage dans le menu

// Ajout de la page d'options pour le plugin
function bima_fonctions() {
  add_options_page('BimaFonctions', 'BimaFonctions', 'manage_options', 'bima-fonctions-menu', 'bima_options');
}
add_action( 'admin_menu', 'bima_fonctions' );
// Ajout de la page d'options pour le plugin

// Permission d'accès à la page d'options
function bima_options(){
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
// Permission d'accès à la page d'options

/* Création d'une page d'option */

?>
<!-- Contenu de la page d'option -->

<!--Wrapper HTML qui intègre les options et variables-->
	<div class="wrap">
		<h2>Bima Fonctions</h2>
		<p>Retrouvez nos services et nos créations de plugin sur notre site internet <a href="https://bima.re/" target="blank">Bima.re</a></p>
			<p>Le module <strong>Bima Fonctions</strong> permet d'ajouter des options utiles à votre site internet. (Made in Réunion-Island)</p>
			<h4><a href="https://bima.re/creation/bimafonctions/" target="blank">Cliquez-ici pour faire un don!</a></h4>
		<hr>
		<h2>Options de Bima Fonctions</h2>
		<form action="options.php" method="post">
			<?php
				settings_fields('bima_grp_options'); //étape6 déclaration des champs d'options
			?>
				<input id="desactive-redir" type="checkbox" name="bima_opt_redir" value="yes" <?php checked( get_option('bima_opt_redir'),'yes' ) ?> > <!--formulaire d'entrée de la valeur de l'option ici une checkbox qui va influer sur bima_opt_redir qui stocke la valeur -->
				<label for="desactive-redir">Activer la redirection des erreurs 404 vers la homepage?</label> <!--label pour l'option--><br/>
		<em>Votre site est régulièrement indexé par les moteurs de recherche, lors de la création de votre site, certaines pages changent de nom ou peuvent devenir inexistantes. <br/> Cette option va rediriger automatiquement toutes les requêtes devenues inexistantes vers votre page de garde au lieu de produire une erreur 404.</em><br/>
				<br/>
				<input id="desactive-adminbar" type="checkbox" name="bima_opt_abar" value="yes" <?php checked( get_option('bima_opt_abar'),'yes' ) ?> > <!--formulaire d'entrée de la valeur de l'option ici une checkbox qui va influer sur bima_opt_redir qui stocke la valeur -->
			<label for="desactive-adminbar">Rendre invisible la barre d'admin pour les non-admin?</label> <!--label pour l'option--><br/>
				<em>Les utilisateurs du site ne devraient pas voir la barre d'édition du site.</em><br/>
                <br/>
            	<input id="desactive-auto-cart" type="checkbox" name="bima_opt_auto_cart" value="yes" <?php checked(get_option('bima_opt_auto_cart'), 'yes'); ?>>
				<label for="desactive-auto-cart">Activer la mise à jour automatique du panier WooCommerce?</label><br/>
				<em>Vous pouvez activer l'option de mise à jour automatique du panier de woocommerce qui cache également le bouton de mise-à-jour sur la page panier.</em>
				<?php submit_button('Sauvegarder'); ?>
		</form>
		<hr>
		<h3>Shortcode [lang-switch]</h3>
		<p>Le shortcode <strong>[lang-switch]</strong> permet d'ajouter une sélection de langue à votre site internet via le module <strong>Polylang</strong> à installer au préalable.</p>
		<h3>Redirection vers la page de garde après un certain temps</h3>
		<p>Coller le shortcode dans une page pour provoquer la redirection vers l'url de destination apres un certain temps <strong>[redirect url='https://www.url.destination' sec='nbdesecondes']</strong>.</p>
		<!--Formulaire qui permet d'envoyer une image-->
	</div>
<!-- Partie en HTML -->

<!-- Contenu de la page d'option -->

	<?php
}

/*--* Fin page option *--*/

?>