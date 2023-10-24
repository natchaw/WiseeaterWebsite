<?php
/**
 * Class: Eac_Load_Elements
 *
 * Description: Charge les groups, controls et les composants actifs Pour Elementor
 *
 * @since 1.9.8 Compatibilité des actions et de l'enregistrement des éléments Elementor version >= 3.5.0
 *              Deprecated controls_registered
 *              Deprecated register_control
 *              Deprecated widgets_registered
 *              Deprecated register_widget_type
 *              Ajout des filtres WooCommerce
 * @since 2.0.2 Suppression des actions et fonctions depréciées
 */

namespace EACCustomWidgets\Core;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use EACCustomWidgets\EAC_Plugin;
use EACCustomWidgets\Core\Eac_Config_Elements;

class Eac_Load_Elements {

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
	 * Ajoute les actions pour enregsitrer les goupes, controls et widgets Elementor
	 *
	 * @param $elements La liste des composants et leur état
	 *
	 * @since 1.9.8
	 */
	private function __construct() {

		/**
		 * Les actions 'wp_ajax_xxxxxx' pour le control 'eac-select2' doivent être chargées avant les actions Elementor
		 *
		 * @since 1.9.8
		 */
		require_once EAC_ADDONS_PATH . 'includes/elementor/controls/eac-select2-actions.php';

		/**
		 * @since 1.9.8 Filtres WooCommerce
		 */
		if ( Eac_Config_Elements::is_widget_active( 'woo-product-grid' ) ) {
			require_once EAC_ADDONS_PATH . 'includes/woocommerce/eac-woo-hooks.php';
		} else {
			// On force la suppression de l'option d'intégration WC par sécurité
			delete_option( Eac_Config_Elements::get_woo_hooks_option_name() );
		}

		/**
		 * Création des catégories de composants
		 *
		 * @since 0.0.9
		 */
		add_action( 'elementor/elements/categories_registered', array( $this, 'register_categories' ) );

		/**
		 * Charge les controls
		 * Enregistre les class des controls
		 *
		 * @since 1.8.9
		 * @since 1.9.8 register vs controls_registered
		 * @since 2.0.2 action controls_registered depréciée
		 */
		add_action( 'elementor/controls/register', array( $this, 'register_controls' ) );

		/**
		 * Charge les widgets
		 * Enregistre les class des composants
		 *
		 * @since 0.0.9
		 * @since 1.9.8 register vs widgets_registered
		 * @since 2.0.2 action widgets_registered depréciée
		 */
		add_action( 'elementor/widgets/register', array( $this, 'register_widgets' ) );
	}

	/** @since 2.0.1 Singleton de la class */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Crée les catégories des composants
	 *
	 * @since 0.0.9
	 * @since 1.8.8 Le troisième paramètre est déprécié
	 * @since 1.8.9 Ajout du groupe de composants 'eac-advanced'
	 */
	public function register_categories( $elements_manager ) {
		$elements_manager->add_category(
			'eac-advanced',
			array(
				'title' => esc_html__( 'EAC Avancés', 'eac-components' ),
				'icon'  => 'fa fa-plug',
			)
		);
		$elements_manager->add_category(
			'eac-elements',
			array(
				'title' => esc_html__( 'EAC Basiques', 'eac-components' ),
				'icon'  => 'fa fa-plug',
			)
		);
	}

	/**
	 * Enregistre les nouveaux controls
	 *
	 * Méthode register_control: 'NOM_DU_CONTROL'
	 * Méthode register: pas de nom pour le control
	 * class du control: méthode get_type() { return 'NOM_DU_CONTROL'; }
	 * script gestion du control: elementor.addControlView('NOM_DU_CONTROL', nom object);
	 * widget add_control: 'type' => 'NOM_DU_CONTROL',
	 *
	 * @args $controls_manager Gestionnaire des controls
	 * @since 1.8.9
	 * @since 1.9.8 register vs register_control
	 * @since 2.0.2 fonction register_control depréciée
	 */
	public function register_controls( $controls_manager ) {

		// Enregistre le control 'file-viewer' pour le composant 'PDF viewer'
		require_once EAC_ADDONS_PATH . 'includes/elementor/controls/file-viewer-control.php';

		// Enregistre le control 'eac-select2' pour le control select2
		require_once EAC_ADDONS_PATH . 'includes/elementor/controls/eac-select2-control.php';

		$controls_manager->register( new \EACCustomWidgets\Includes\Elementor\Controls\Simple_File_Viewer_Control() );
		$controls_manager->register( new \EACCustomWidgets\Includes\Elementor\Controls\Ajax_Select2_Control() );
	}

	/**
	 * Enregistre les composants actifs
	 *
	 * @since 0.0.9
	 * @since 1.9.0 Suppression des composants 'Instagram'
	 * @since 1.9.8 register vs register_widget_type
	 *              Charge les traits nécessaires pour les composants qui les utilisent
	 * @since 2.0.2 fonction register_widget_type depréciée
	 */
	public function register_widgets( $widgets_manager ) {

		// Les traits 'slider' et 'Button read more' pour les composants qui implémente le slider swiper
		if ( Eac_Config_Elements::is_widget_active( 'woo-product-grid' ) || Eac_Config_Elements::is_widget_active( 'articles-liste' ) || Eac_Config_Elements::is_widget_active( 'acf-relationship' ) || Eac_Config_Elements::is_widget_active( 'image-galerie' ) ) {
			require_once EAC_WIDGETS_TRAITS_PATH . 'slider-trait.php';
			require_once EAC_WIDGETS_TRAITS_PATH . 'button-read-more-trait.php';
		}

		// Le composant product grid est activé, on charge les traits
		if ( Eac_Config_Elements::is_widget_active( 'woo-product-grid' ) ) {
			require_once EAC_WIDGETS_TRAITS_PATH . 'button-add-to-cart-trait.php';
			require_once EAC_WIDGETS_TRAITS_PATH . 'badge-new-trait.php';
			require_once EAC_WIDGETS_TRAITS_PATH . 'badge-promo-trait.php';
			require_once EAC_WIDGETS_TRAITS_PATH . 'badge-stock-trait.php';
		}

		foreach ( Eac_Config_Elements::get_widgets_active() as $element => $active ) {
			if ( Eac_Config_Elements::is_widget_active( $element ) ) {
				$path       = Eac_Config_Elements::get_widget_path( $element );
				$name_space = Eac_Config_Elements::get_widget_namespace( $element );
				if ( $path ) {
					require_once $path;
					$widgets_manager->register( new $name_space() );
				}
			}
		}
	}
} Eac_Load_Elements::instance();
