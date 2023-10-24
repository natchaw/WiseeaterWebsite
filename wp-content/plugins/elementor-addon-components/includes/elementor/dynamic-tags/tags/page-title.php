<?php
/**
 * Class: Eac_Page_Title
 *
 * @return affiche le titre de la page
 * @since 2.0.2
 */

namespace EACCustomWidgets\Includes\Elementor\DynamicTags\Tags;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

use Elementor\Core\DynamicTags\Tag;
use Elementor\Modules\DynamicTags\Module as TagsModule;

class Eac_Page_Title extends Tag {
	public function get_name() {
		return 'eac-addon-page-title';
	}

	public function get_title() {
		return esc_html__( 'Titre de la page', 'eac-components' );
	}

	public function get_group() {
		return 'eac-site-groupe';
	}

	public function get_categories() {
		return array( TagsModule::TEXT_CATEGORY );
	}

	public function render() {
		$title = $this->get_page_title();
		echo wp_kses_post( $title );
	}

	public function get_page_title( $include_context = false ) {
		$title = '';

		if ( is_singular() ) {
			/* translators: %s: Search term. */
			$title = get_the_title();

			if ( $include_context ) {
				$post_type_obj = get_post_type_object( get_post_type() );
				$title         = sprintf( '%s: %s', $post_type_obj->labels->singular_name, $title );
			}
		} elseif ( is_search() ) {
			/* translators: %s: Search term. */
			$title = sprintf( esc_html__( 'Résultats de recherche pour: %s', 'eac-components' ), get_search_query() );

			if ( get_query_var( 'paged' ) ) {
				/* translators: %s: Page number. */
				$title .= sprintf( esc_html__( '&nbsp;&ndash; Page %s', 'eac-components' ), get_query_var( 'paged' ) );
			}
		} elseif ( is_category() ) {
			$title = single_cat_title( '', false );

			if ( $include_context ) {
				/* translators: Category archive title. %s: Category name. */
				$title = sprintf( esc_html__( 'Catégorie: %s', 'eac-components' ), $title );
			}
		} elseif ( is_tag() ) {
			$title = single_tag_title( '', false );
			if ( $include_context ) {
				/* translators: Tag archive title. %s: Tag name. */
				$title = sprintf( esc_html__( 'Étiquette: %s', 'eac-components' ), $title );
			}
		} elseif ( is_author() ) {
			$title = '<span class="vcard">' . get_the_author() . '</span>';

			if ( $include_context ) {
				/* translators: Author archive title. %s: Author name. */
				$title = sprintf( esc_html__( 'Auteur: %s', 'eac-components' ), $title );
			}
		} elseif ( is_post_type_archive() ) {
			$title = post_type_archive_title( '', false );

			if ( $include_context ) {
				/* translators: Post type archive title. %s: Post type name. */
				$title = sprintf( esc_html__( 'Archives: %s', 'eac-components' ), $title );
			}
		} elseif ( is_tax() ) {
			$title = single_term_title( '', false );

			if ( $include_context ) {
				$tax = get_taxonomy( get_queried_object()->taxonomy );
				/* translators: Taxonomy term archive title. 1: Taxonomy singular name, 2: Current taxonomy term. */
				$title = sprintf( esc_html__( '%1$s: %2$s', 'eac-components' ), $tax->labels->singular_name, $title );
			}
		} elseif ( is_archive() ) {
			$title = esc_html__( 'Archives', 'eac-components' );
		} elseif ( is_404() ) {
			$title = esc_html__( 'Page introuvable', 'eac-components' );
		} else {
			$title = esc_html__( 'Type de page inconnu', 'eac-components' );
		}
		return $title;
	}
}
