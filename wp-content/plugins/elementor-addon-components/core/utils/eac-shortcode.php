<?php
/*
add_action('elementor/editor/wp_head', function() {
	if (\Elementor\Plugin::$instance->editor->is_edit_mode()) {
		echo "<script>window.addEventListener('DOMContentLoaded', function() {
				var checkExist = setInterval(function() {
					if (document.querySelector('#elementor-panel-header-title') != null) {
						document.querySelector('#elementor-panel-header-title').innerHTML = '';
						document.querySelector('#elementor-panel-header-title').innerHTML = '<div>EAC SWAPED</div>';
						clearInterval(checkExist);
					}
				}, 100);
		});</script>";
	}
});*/

/**
 * eac_register_shortcode
 *
 * Crée le point d'accès Shortcode pour les images externes 'eac_img_shortcode'
 * Crée le point d'accès pour l'intégration des Templates Elementor
 * Affiche la valeur de la colonne 'Shortcode' dans la vue Elementor Templates
 *
 * @since 1.5.3
 * @since 1.6.0
 * @since 1.6.3 Suppression du shortcode 'eac_media_shortcode'
 */
add_action( 'init', 'eac_register_shortcode', 0 );
function eac_register_shortcode() {
	add_shortcode( 'eac_img', 'eac_img_shortcode' );
	add_shortcode( 'eac_elementor_tmpl', 'eac_elementor_add_tmpl' );
	if ( class_exists( 'WooCommerce' ) ) {
		add_shortcode( 'product_rating', 'eac_display_product_rating' );
		add_shortcode( 'woo_session_cart', 'eac_display_session_cart' );
	}
	add_action( 'manage_elementor_library_posts_columns', 'eac_add_colonnes_elementor' );
	add_action( 'manage_elementor_library_posts_custom_column', 'eac_data_colonnes_elementor', 10, 2 );
}

// https://stackoverflow.com/questions/38546354/woocommerce-cookies-and-sessions-get-the-current-products-in-cart
if ( class_exists( 'WooCommerce' ) && ! function_exists( 'eac_display_session_cart' ) ) {
	function eac_display_session_cart( $atts ) {
		$session_id = null;
		$values     = null;

		foreach ( $_COOKIE as $key => $value ) {
			if ( stripos( $key, 'wp_woocommerce_session_' ) === false ) {
				continue;
			}
			$values = explode( '||', $value );
		}

		$session_id   = $values[0];
		$session      = new WC_Session_Handler();
		$session_data = $session->get_session( $session_id );

		// Contains array of items in cart including product ids, quantities, totals, etc.
		$cart_data = unserialize( $session_data['cart'] );

		return highlight_string( "<?php\nWoo Cokkie =\n" . var_export( $cart_data, true ) . ";\n?>" );
		// return json_encode($cart_data);
	}
}

// WooCommerce est installé
if ( class_exists( 'WooCommerce' ) && ! function_exists( 'eac_display_product_rating' ) ) {
	function eac_display_product_rating( $atts ) {
		// Shortcode attributes
		$atts = shortcode_atts( array( 'id' => '' ), $atts, 'product_rating' );

		if ( isset( $atts['id'] ) && $atts['id'] > 0 ) {
			// Get an instance of the WC_Product Object
			$product = wc_get_product( $atts['id'] );

			// The product average rating (or how many stars this product has)
			$average = $product->get_average_rating();
		}

		if ( isset( $average ) ) {
			return wc_get_rating_html( $average );
		}
	}
}

/**
 * eac_img_shortcode
 * Shortcode d'intégration d'une image avec lien externe, fancybox et caption
 *
 * Ex:  [eac_img src="https://www.cestpascommode.fr/wp-content/uploads/2019/04/fauteuil-louis-philippe-zebre-01.jpg" fancybox="yes" caption="Fauteuil Zèbre"]
 *      [eac_img src="https://www.cestpascommode.fr/wp-content/uploads/2020/04/chaise-victoria-01.jpg" link="https://www.cestpascommode.fr/realisations/chaise-victoria" caption="Chaise Victoria"]
 *      [eac_img link="https://www.cestpascommode.fr/realisations/bergere-louis-xv-et-sa-chaise" embed="yes"]
 *
 * @since 1.6.0
 * @since 1.6.2 Forcer 'data-elementor-open-lightbox' à 'no' pour ouvrir l'image dans la popup Fancybox
 * @since 1.9.2 Ajout des attributs "noopener noreferrer" pour les liens ouverts dans un autre onglet
 */
function eac_img_shortcode( $params = array() ) {
	extract(
		shortcode_atts(
			array(
				'src'      => '',
				'link'     => '',
				'fancybox' => 'no',
				'caption'  => '',
				'embed'    => 'no',
			),
			$params
		)
	);

	$html_default = '';
	$source       = trim( $src );
	$linked       = ! empty( trim( $link ) ) ? trim( $link ) : $link;
	$fancy_box    = in_array( trim( $fancybox ), array( 'yes', 'no' ) ) ? trim( $fancybox ) : $fancybox;
	$fig_caption  = ! empty( trim( $caption ) ) ? trim( $caption ) : 'Hooops';
	$embed_link   = in_array( trim( $embed ), array( 'yes', 'no' ) ) ? trim( $embed ) : $embed;

	if ( empty( $source ) ) {
		return $html_default; }
	// if (! empty($source) && ! preg_match("/\.(gif|png|jpg|jpeg|bmp)$/i", $source)) { return $html_default; }

	if ( $embed_link === 'yes' ) {
		// print_r($linked); // Embed le lien
		// Lien externe
	} elseif ( ! empty( $linked ) ) {
		$html_default =
			'<figure>
                <a href="' . $linked . '" target="_blank" rel="nofollow noopener noreferrer">
                    <img src="' . $source . '" alt="' . $fig_caption . '"/>
                    <figcaption>' . $fig_caption . '</figcaption>
                </a>
            </figure>';
		// @since 1.6.2 Fancybox
	} elseif ( $fancy_box === 'yes' ) {
		$html_default =
			'<figure>
                <a href="' . $source . '" data-elementor-open-lightbox="no" data-fancybox="eac-img-shortcode" data-caption="' . $fig_caption . '">
                    <img src="' . $source . '" alt="' . $fig_caption . '"/>
                    <figcaption>' . $fig_caption . '</figcaption>
                </a>
            </figure>';
	} else {
		$html_default =
			'<figure>
                <img src="' . $source . '" alt="' . $fig_caption . '"/>
                <figcaption>' . $fig_caption . '</figcaption>
            </figure>';
	}

	// Return HTML code
	return $html_default;
}

/**
 * eac_elementor_tmpl
 * Shortcode d'intégration d'un modèle Elementor
 *
 * Ex: [eac_elementor_tmpl id="XXXXX"]
 *
 * @since 1.6.0
 * @since 1.6.2 Ajout du filtre language 'WPML'
 */
function eac_elementor_add_tmpl( $params = array() ) {
	extract(
		shortcode_atts(
			array(
				'id'  => '',
				'css' => 'true',
			),
			$params
		)
	);

	$id_tmpl  = trim( $id );
	$css_tmpl = trim( $css );

	if ( empty( $id_tmpl ) || ! get_post( $id_tmpl ) ) {
		return '';
	}

	// Évite la récursivité
	if ( get_the_ID() === (int) $id_tmpl ) {
		return esc_html__( 'ID du modèle ne peut pas être le même que le modèle actuel', 'eac-components' );
	}

	// @since 1.6.2 Filtre wpml
	$id_tmpl = apply_filters( 'wpml_object_id', $id_tmpl, 'elementor_library', true );

	return \Elementor\Plugin::$instance->frontend->get_builder_content_for_display( $id_tmpl, $css_tmpl );
}

/**
 * eac_add_colonnes_elementor
 * Ajout de la colonne 'Shortcode' dans la vue Elementor Templates
 *
 * @since 1.6.0
 */
function eac_add_colonnes_elementor( $columns ) {
	return array_merge( $columns, array( 'eac_shortcode' => esc_html__( 'Shortcode', 'eac-components' ) ) );
}

/**
 * eac_data_colonnes_elementor
 * Affiche la valeur de la colonne 'Shortcode' dans la vue Elementor Templates
 *
 * @since 1.6.0
 * @since 1.9.1 Check post_status
 */
function eac_data_colonnes_elementor( $column_name, $post_id ) {
	$poste = get_post( $post_id );
	if ( $poste->post_status === 'publish' && 'eac_shortcode' === $column_name ) {
		echo '<input type="text" class="widefat" onfocus="this.select()" value=\'[eac_elementor_tmpl id="' . $post_id . '"]\' readonly>';
	}
}

/**
 * console_log
 * Affiche des traces dans la console du navigateur
 *
 * @since 1.6.5
 */
function console_log( $output, $with_script_tags = true ) {
	$js_code = 'console.log(' . json_encode( $output, JSON_HEX_TAG ) . ');';
	if ( $with_script_tags ) {
		$js_code = '<script>' . $js_code . '</script>';
	}
	echo $js_code;
}
