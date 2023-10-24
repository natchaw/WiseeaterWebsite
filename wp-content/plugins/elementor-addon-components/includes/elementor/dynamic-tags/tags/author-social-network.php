<?php
/**
 * Class: Eac_Author_Social_network
 *
 * @return La liste formatées des URL des médias sociaux pour l'utilisateur courant
 *
 * @since 1.6.0
 * @since 1.7.6 Tranférer la liste des réseaux sociaux dans la class 'Eac_Tools_Util'
 * @since 1.9.1 Ajouter le traitement de l'e-mail et du website
 *              Test de la Global $authordata
 */

namespace EACCustomWidgets\Includes\Elementor\DynamicTags\Tags;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

use EACCustomWidgets\Core\Utils\Eac_Tools_Util;
use Elementor\Controls_Manager;
use Elementor\Core\DynamicTags\Tag;
use Elementor\Modules\DynamicTags\Module as TagsModule;

class Eac_Author_Social_Network extends Tag {

	public function get_name() {
		return 'eac-addon-author-social-network';
	}

	public function get_title() {
		return esc_html__( 'Auteur réseaux sociaux', 'eac-components' );
	}

	public function get_group() {
		return 'eac-author-groupe';
	}

	public function get_categories() {
		return array(
			TagsModule::TEXT_CATEGORY,
			TagsModule::POST_META_CATEGORY,
		);
	}

	public function get_panel_template_setting_key() {
		return 'author_social_network';
	}

	protected function register_controls() {
		$this->add_control(
			'author_social_network',
			array(
				'label'       => esc_html__( 'Champs', 'eac-components' ),
				'type'        => Controls_Manager::SELECT2,
				'label_block' => true,
				'multiple'    => true,
				'default'     => '',
				'options'     => Eac_Tools_Util::get_all_social_networks(), // @since 1.7.6
			)
		);
	}

	public function render() {
		global $authordata;

		// @since 1.9.1 Global $authordata n'est pas instancié
		if ( ! isset( $authordata->ID ) ) {
			return;
		}

		$keys = $this->get_settings( 'author_social_network' );
		if ( empty( $keys ) ) {
			return;
		}

		$values = '<div class="dynamic-tags_social-container">';

		foreach ( $keys as $key ) {
			$value = get_the_author_meta( $key, $authordata->ID );
			$key = esc_attr( $key );

			if ( '' !== $value ) {
				// @since 1.9.1 Protection de l'adresse e-mail contre les spams
				if ( 'email' === $key ) {
					$href = '<a href="' . esc_url( 'mailto:' . antispambot( sanitize_email( $value ) ) ) . '" rel="nofollow">';
				} else {
					$href = '<a href="' . esc_url( $value ) . '" rel="nofollow">';
				}

				$span = '<span class="dynamic-tags_social-icon ' . $key . '" title="' . ucfirst( $key ) . '">';

				// @since 1.9.1 Différentes fontes awesome
				if ( 'tiktok' === $key ) {
					$faw = '<i class="fab fa-tiktok"></i></span></a>';
				} elseif ( 'github' === $key ) {
					$faw = '<i class="fab fa-github"></i></span></a>';
				} elseif ( 'email' === $key ) {
					$faw = '<i class="fa fa-envelope"></i></span></a>';
				} elseif ( 'url' === $key ) {
					$faw = '<i class="fa fa-globe"></i></span></a>';
				} else {
					$faw = '<i class="fa fa-' . $key . '"></i></span></a>';
				}

				$values .= $href . $span . $faw;
			}
		}
		$values .= '</div>';

		echo wp_kses_post( $values );
	}
}
