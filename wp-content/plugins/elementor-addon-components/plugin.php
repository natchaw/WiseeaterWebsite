<?php
/**
 * Class: EAC_Plugin
 *
 * Description:  Active l'administration du plugin avec les droits d'Admin
 *
 * @since 0.0.9
 * @since 1.9.6 Ajout d'une nouvelle capacité 'eac_manage_options' pour les rôles 'editor' et 'shop_manager'
 * @since 1.9.7 Ajout de la méthode 'get_register_script_url' pour construire le chemin d'accès aux scripts JS
 *              Ajout de la méthode 'get_register_style_url' pour construire le chemin d'accès aux fichiers CSS
 *              Ajout de la méthode 'get_manage_options_name'
 *              Ajout des méthodes 'instance' 'clone' et 'wakeup'
 * @since 1.9.8 Déplacer le chargement des Groupes, des Controls et des Composants Elementor dans le fichier 'includes/eac-load-elements'
 *              Charge la configuration des composants et des fonctionnalités et transfert des tables 'elements_keys' et 'features_keys'
 * @since 1.9.9 Déplacement des fichiers de configuration sous le répertoire 'core'
 * @since 2.0.0 Changer la syntaxe 'require_once' dans tous les fichiers
 *              'require_once' est une déclaration, pas une fonction.
 */

namespace EACCustomWidgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

use EACCustomWidgets\Admin\Settings\EAC_Admin_Settings;
use EACCustomWidgets\Core\Eac_Config_Elements;

/**
 * Main Plugin Class
 *
 * @since 0.0.9
 */
class EAC_Plugin {

	/**
	 * @var $manage_options
	 *
	 * Le libellé de la capacité ajoutée aux rôles 'editor' et 'shop_manager'
	 *
	 * @since 1.9.6
	 */
	private $manage_options = 'eac_manage_options';

	/**
	 * @var $instance
	 *
	 * Garantir une seule instance de la class
	 *
	 * @since 1.9.7
	 */
	private static $instance = null;

	/**
	 * @var suffix_css
	 * Debug des fichiers CSS
	 *
	 * @since 1.9.7
	 *
	 * @access private
	 */
	private $suffix_css = EAC_STYLE_DEBUG ? '.css' : '.min.css';

	/**
	 * @var suffix_js
	 * Debug des fichiers JS
	 *
	 * @since 1.9.7
	 *
	 * @access private
	 */
	private $suffix_js = EAC_SCRIPT_DEBUG ? '.js' : '.min.js';

	/**
	 * Constructeur
	 *
	 * @since 0.0.9
	 *
	 * @access public
	 */
	private function __construct() {

		/**
		 * @since 1.9.8 Charge le fichier configuration des éléments en premier
		 * @since 1.9.9 Déplacement du fichier dans le répertoire 'core'
		 */
		require_once __DIR__ . '/core/eac-load-config.php';

		/** @since 1.9.6 Ajoute une nouvelle capability 'eac_manage_options' aux rôles "editor' et 'shop_manager' */
		if ( current_user_can( 'manage_options' ) ) {
			$this->set_grant_option_page();
		}

		/**
		 * Charge la page d'administration du plugin
		 */
		if ( current_user_can( 'manage_options' ) || current_user_can( $this->manage_options ) ) {
			require_once __DIR__ . '/admin/settings/eac-load-components.php';
		}

		/**
		 * Charge les fonctionnalités
		 * @since 1.9.9 Déplacement du fichier dans le répertoire 'core'
		 */
		require_once __DIR__ . '/core/eac-load-features.php';

		/**
		 * Charge les scripts et les styles globaux
		 * @since 1.9.9 Déplacement du fichier dans le répertoire 'core'
		 */
		require_once __DIR__ . '/core/eac-load-scripts.php';

		/**
		 * Charge les catégories, les controls et les composants Elementor
		 * @since 1.9.9 Déplacement du fichier dans le répertoire 'core'
		 */
		require_once __DIR__ . '/core/eac-load-elements.php';
	}

	/**
	 * instance.
	 *
	 * Garantir une seule instance de la class
	 *
	 * @since 1.9.7
	 *
	 * @return EAC_Plugin une instance de la class
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Singletons should not be cloneable.
	 *
	 * @since 1.9.7
	 */
	protected function __clone() { }

	/**
	 * Singletons should not be restorable from strings.
	 *
	 * @since 1.9.7
	 */
	public function __wakeup() { }

	/**
	 * set_grant_option_page
	 *
	 * Ajoute une nouvelle capability 'eac_manage_options' aux rôles "editor' et 'shop_manager'
	 *
	 * 'option_name' = 'wp_user_roles' de la table options
	 *
	 * @since 1.9.6
	 */
	private function set_grant_option_page() {
		/** Options ACF Options Page && Grant Options Page sont actives */
		$grant_option_page = Eac_Config_Elements::is_feature_active( 'acf-option-page' ) && Eac_Config_Elements::is_feature_active( 'grant-option-page' );
		$role_editor       = get_role( 'editor' );
		$role_shop_manager = get_role( 'shop_manager' );

		if ( $grant_option_page ) {
			if ( false === $role_editor->has_cap( $this->manage_options ) ) {
				wp_roles()->add_cap( 'editor', $this->manage_options );
			}

			if ( ! is_null( $role_shop_manager ) && false === $role_shop_manager->has_cap( $this->manage_options ) ) {
				wp_roles()->add_cap( 'shop_manager', $this->manage_options );
			}
		} else {
			if ( true === $role_editor->has_cap( $this->manage_options ) ) {
				wp_roles()->remove_cap( 'editor', $this->manage_options );
			}

			if ( ! is_null( $role_shop_manager ) && true === $role_shop_manager->has_cap( $this->manage_options ) ) {
				wp_roles()->remove_cap( 'shop_manager', $this->manage_options );
			}
		}
	}

	/**
	 * get_register_script_url
	 *
	 * Construit le chemin du fichier et ajoute l'extension relative à la constant globale
	 *
	 * @since 1.9.7
	 *
	 * @return le chemin absolu du fichier JS passé en paramètre
	 */
	public function get_register_script_url( $file, $admin = false ) {
		if ( $admin ) {
			return EAC_ADDONS_URL . 'admin/js/' . $file . $this->suffix_js;
		} else {
			return EAC_ADDONS_URL . 'assets/js/elementor/' . $file . $this->suffix_js;
		}
	}

	/**
	 * get_register_style_url
	 *
	 * Construit le chemin du fichier et ajoute l'extension relative à la constant globale
	 *
	 * @since 1.9.7
	 *
	 * @return le chemin absolu du fichier CSS passé en paramètre
	 */
	public function get_register_style_url( $file, $admin = false ) {
		if ( $admin ) {
			return EAC_ADDONS_URL . 'admin/css/' . $file . $this->suffix_css;
		} else {
			return EAC_ADDONS_URL . 'assets/css/' . $file . $this->suffix_css;
		}
	}

	/**
	 * get_manage_options_name
	 *
	 * @since 1.9.7
	 *
	 * @return le nom de la capacité 'eac_manage_options'
	 */
	public function get_manage_options_name() {
		return $this->manage_options;
	}

} EAC_Plugin::instance();
