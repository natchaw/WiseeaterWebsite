<?php
/*
if(! function_exists('wp_body_open')) {
	function wp_body_open() {
		do_action('wp_body_open');
	}
}

function eac_modify_body_open() {
	//error_log('ERERERER RERERER');
	?>
		<script>
			jQuery('body').append("<div class='my-body-open-class' style='text-align:center;'><p>Contenu prepended au BODY</p></div>");
			//jQuery("<div class='my-body-open-class' style='text-align:center;'><p>Contenu prepended au BODY</p></div>").insertAfter('.entry-header');
		</script>
	<?php
}*/

// add_action('wp_body_open', 'eac_modify_body_open');

/**
 * eac_add_author_infobox
 *
 * Ajoute le contenu du template Author infobox au contenu d'un post_type/posts
 *
 * @since 1.9.1
 */
function eac_embed_author_infobox( $content ) {
	$component_active = get_option( 'eac_options_settings' );
	$options          = get_option( 'eac_options_infobox' );

	// Le composant n'existe pas ou n'est pas actif
	if ( ! isset( $component_active['author-infobox'] ) || ! $component_active['author-infobox'] ) {
		return $content;
	}

	// Page d'accueil
	if ( is_front_page() ) {
		return $content;
	}

	// Pas d'option pour l'infobox
	if ( $options === false ) {
		return $content;
	}

	// Les options de l'infobox
	$id         = $options['post_id'];              // ID du modèle Elementor
	$post_types = $options['post_type'];    // Le post_type qui peut afficher le contenu du template
	$position   = $options['position'];       // La position du contenu du template
	$ids        = $options['post_ids'];            // La liste des IDs qui peuvent afficher le contenu du template

	// L'article courant
	$current_id        = get_the_ID();
	$current_post_type = get_post_type( $current_id );

	/*
	$categories = get_the_category($current_id);
	$category_list = wp_list_pluck($categories, 'name');
	console_log($category_list);*/

	// Le template Elementor est publié
	$template = get_post( $id );
	// error_log($post_name . "::" . $template->post_name);
	if ( $template === null || $template->post_status !== 'publish' ) {
		return $content;
	}

	// Le post_type de l'article courant n'est pas le post_type attendu
	if ( $current_post_type !== $post_types ) {
		return $content;
	}

	// ID de l'article courant n'est pas dans la liste des articles qui peuvent afficher le template
	if ( ( is_array( $ids ) && ! empty( $ids ) ) && ! in_array( $current_id, $ids ) ) {
		return $content;
	}

	// Évite la récursivité
	if ( $current_id === (int) $id ) {
		return $content;
	}

	// if(is_single() && in_the_loop() && is_main_query()) {
		// Filtre wpml
		$id = apply_filters( 'wpml_object_id', $id, 'elementor_library', true );

		// Ajoute le contenu du template selon sa position
	if ( $position === 'before' ) {
		return \Elementor\Plugin::$instance->frontend->get_builder_content_for_display( $id, true ) . $content;
	} else {
		return $content . \Elementor\Plugin::$instance->frontend->get_builder_content_for_display( $id, true );
	}
	// }
}
// 99 pour que le contenu des shortcodes soit affiché avant
add_filter( 'the_content', 'eac_embed_author_infobox', 99 );
