<?php
/**
 * Class: Eac_Load_Scripts
 *
 * Description: Affecte les actions nécessaires et enregistre les scripts/styles
 *
 * @since 1.9.2
 * @since 1.9.5 Ajout des paramètres nécessaires au script OSM
 */

namespace EACCustomWidgets\Core;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use EACCustomWidgets\EAC_Plugin;

class Eac_Load_Scripts {

	/**
	 * @var suffix_js
	 * Debug des fichiers JS
	 *
	 * @since 1.6.7
	 *
	 * @access private
	 */
	private $suffix_js = EAC_SCRIPT_DEBUG ? '.js' : '.min.js';

	/**
	 * @var $instance
	 *
	 * Garantir une seule instance de la class
	 *
	 * @since 1.9.7
	 */
	private static $instance = null;

	/**
	 * Constructeur de la class
	 *
	 * @since 0.0.9
	 *
	 * @access private
	 */
	private function __construct() {

		/**
		 * Action pour enregistrer les scripts des composants
		 *
		 * @since 0.0.9
		 */
		add_action( 'elementor/frontend/after_register_scripts', array( $this, 'register_scripts' ) );

		/**
		 * Action pour insérer les scripts obligatoires dans la file
		 *
		 * @since 0.0.9
		 */
		add_action( 'elementor/frontend/before_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		/**
		 * Action pour insérer les scripts et les fonts Awesome dans l'éditeur
		 *
		 * @since 1.8.8
		 */
		add_action( 'elementor/editor/before_enqueue_scripts', array( $this, 'enqueue_scripts_editor' ) );

		/**
		 * Action pour insérer les styles par défaut et les mettre dans la file
		 *
		 * @since 0.0.9
		 */
		add_action( 'elementor/frontend/after_enqueue_styles', array( $this, 'enqueue_styles' ) );

		/**
		 * Action pour insérer les styles dans le panel Elementor
		 *
		 * @since 1.7.0
		 */
		add_action( 'elementor/editor/wp_head', array( $this, 'enqueue_panel_styles' ) );
	}

	/** @since 2.0.1 Singleton de la class */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * register_scripts
	 *
	 * Enregistre les scripts globaux
	 *
	 * @since 0.0.9
	 */
	public function register_scripts() {
		// Le gestionnaire d'image est toujours enregistré
		wp_register_script( 'eac-imagesloaded', EAC_ADDONS_URL . 'assets/js/isotope/imagesloaded.pkgd.min.js', array( 'jquery' ), '4.1.4', true );
	}

	/**
	 * enqueue_scripts
	 *
	 * Enregistre les scripts généraux
	 *
	 * @since 0.0.9
	 * @since 1.6.2
	 * @since 1.9.5 Ajout des keys/values pour OSM
	 */
	public function enqueue_scripts() {
		/**
		 * @since 1.6.2 La Fancybox est toujours chargée pour le 'Shortcode Image' et le Dynamic Tags 'External image'
		 * qui peuvent être insérés dans un article/page sans composant
		 */
		wp_enqueue_script( 'eac-fancybox', EAC_ADDONS_URL . 'assets/js/fancybox/jquery.fancybox' . $this->suffix_js, array( 'jquery' ), '3.5.7', true );

		/** Le script principal qui exécute le code de chaque composant quand il est affiché dans la page */
		wp_enqueue_script( 'eac-elements', EAC_ADDONS_URL . 'assets/js/eac-components' . $this->suffix_js, array( 'jquery', 'elementor-frontend' ), '1.9.0', true );

		/**
		 * Passe les URLs absolues du plugin aux objects javascript
		 *
		 * @since 1.9.5
		 */
		wp_localize_script(
			'eac-elements',
			'eacElementsPath',
			array(
				'proxies'   => EAC_ADDONS_URL . 'includes/proxy/',
				'pdfJs'     => EAC_ADDONS_URL . 'assets/js/pdfjs/',
				'osmImages' => EAC_ADDONS_URL . 'assets/images/',
				'osmConfig' => EAC_ADDONS_URL . 'includes/config/osm/',
			)
		);
	}

	/**
	 * enqueue_scripts_editor
	 *
	 * Enregistre les scripts utiles dans l'éditeur
	 * Nominatim pour OpenStreetMap
	 * Font Awesome
	 *
	 * @since 1.8.8
	 */
	public function enqueue_scripts_editor() {
		wp_enqueue_script( 'eac-nominatim', EAC_ADDONS_URL . 'assets/js/openstreetmap/search-osm' . $this->suffix_js, array( 'jquery' ), '1.8.8', true );

		/**
		 * Semblerait que les fonts Awesome ne soient pas chargées dans l'éditeur Elementor
		 */
		// if (! wp_style_is('font-awesome', 'enqueued')) {
			wp_enqueue_style( 'font-awesome-5-all', plugins_url( '/elementor/assets/lib/font-awesome/css/all.min.css' ), false, '5.15.3' );
		// }
	}

	/**
	 * enqueue_styles
	 *
	 * Enqueue les styles globaux pour Elementor
	 *
	 * @since 0.0.9
	 */
	public function enqueue_styles() {
		wp_enqueue_style( 'eac', EAC_Plugin::instance()->get_register_style_url( 'eac-components' ), false, '1.0.0' );
		wp_enqueue_style( 'eac-image-fancybox', EAC_Plugin::instance()->get_register_style_url( 'jquery.fancybox' ), array( 'eac' ), '3.5.7' );
	}

	/**
	 * enqueue_panel_styles
	 *
	 * Enregistre les styles dans le panel de l'éditeur Elementor
	 * Propriété 'content_classes' de control RAW_HTML
	 * Classes de font Awesome pour les control 'start_controls_tab' OpenStreetMap
	 *
	 * @since 1.7.0
	 * @since 1.8.9 File viewer
	 */
	public function enqueue_panel_styles() {
		wp_enqueue_style( 'eac-editor-panel', EAC_Plugin::instance()->get_register_style_url( 'eac-editor-panel' ), false, '1.0.0' );
	}

} Eac_Load_Scripts::instance();
