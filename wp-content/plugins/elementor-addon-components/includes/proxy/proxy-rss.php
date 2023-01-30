<?php
/**
 * Description: Collecte les données des flux RSS
 * RSS/ATOM, PINTEREST, TWITTER, YOUTUBE et VIMEO
 * Ce module est appelé depuis la requête AJAX 'ajaxCallFeed' de 'eac-components.js'
 *
 * @param {string} $_REQUEST['url'] l'url du flux à analyser
 * @return {Object[]} Les données encodées JSON
 * @since 1.3.1
 * @since 1.9.6 Gesion plus fine des erreur 'SimpleXML_Load_String'
 */

namespace EACCustomWidgets\Proxy;

$parse_uri = explode( 'wp-content', $_SERVER['SCRIPT_FILENAME'] );
require_once $parse_uri[0] . 'wp-load.php';

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! isset( $_REQUEST['id'] ) || ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( $_REQUEST['nonce'], 'eac_rss_feed_' . $_REQUEST['id'] ) ) {
	echo esc_html__( 'Jeton invalide. Actualiser la page courante...', 'eac-components' );
	exit;
}

if ( ! isset( $_REQUEST['url'] ) ) {
	exit;
}

$url = filter_var( urldecode( $_REQUEST['url'] ), FILTER_SANITIZE_STRING );
header( 'Content-Type: text/html' );

// On  fait le boulot
if ( ! $results = scrape_rss( $url ) ) {
	exit;
}

// Tableau de collecte des données
$items = array();
$feed  = array();

// RSS ou ATOM
$items = isset( $results->channel->item ) ? $results->channel->item : $results->entry;
if ( count( $items ) === 0 ) {
	echo esc_html__( 'Rien à afficher...', 'eac-components' );
	exit;
}

$feed['profile']['headTitle']       = isset( $results->channel ) ? (string) $results->channel->title : (string) $results->title;
$feed['profile']['headDescription'] = isset( $results->channel ) ? (string) $results->channel->description : '';
if ( isset( $results->channel ) && isset( $results->channel->link ) ) {
	$feed['profile']['headLink'] = (string) $results->channel->link;
} elseif ( isset( $results->author->uri ) ) {
	$feed['profile']['headLink'] = (string) $results->author->uri;
} else {
	$feed['profile']['headLink'] = (string) $results->link['href'];
}
$feed['profile']['headLogo'] = isset( $results->channel ) ? (string) $results->channel->image->url : '';

// Boucle sur les items
$index = 0;
foreach ( $items as $item ) {
	// Le titre
	$feed['rss'][ $index ]['title'] = ! empty( $item->title ) ? (string) $item->title : '[No title]';
	trim( str_replace( '/<[^>]+>/ig', '', $feed['rss'][ $index ]['title'] ) );

	// Le lien sur le titre vers la page idoine
	if ( isset( $item->link[4] ) ) {
		$feed['rss'][ $index ]['lien'] = (string) $item->link[4]['href']; } // Blogspot
	elseif ( isset( $item->link['href'] ) ) {
		$feed['rss'][ $index ]['lien'] = (string) $item->link['href']; } else {
		$feed['rss'][ $index ]['lien'] = (string) $item->link; }

		// Champ description
		if ( isset( $item->media_content->media_description ) ) {
			$feed['rss'][ $index ]['description'] = (string) $item->media_content->media_description; } elseif ( isset( $item->description ) ) {
			$feed['rss'][ $index ]['description'] = (string) $item->description; } elseif ( isset( $item->summary ) ) {
					$feed['rss'][ $index ]['description'] = (string) $item->summary; } elseif ( isset( $item->media_group ) ) {
					$feed['rss'][ $index ]['description'] = (string) $item->media_group->media_description; } else {
						$feed['rss'][ $index ]['description'] = (string) $item->content; }

					// Date de publication
					$feed['rss'][ $index ]['update'] = isset( $item->pubDate ) ? (string) $item->pubDate : (string) $item->updated;
					$feed['rss'][ $index ]['id']     = (string) $item->guid;

					// Le nom de l'auteur
					if ( isset( $item->author->name ) ) {
						$feed['rss'][ $index ]['author'] = (string) $item->author->name; } elseif ( isset( $item->author ) ) {
						$feed['rss'][ $index ]['author'] = (string) $item->author; } elseif ( isset( $item->dc_creator ) ) {
							$feed['rss'][ $index ]['author'] = (string) $item->dc_creator; } else {
							$feed['rss'][ $index ]['author'] = ''; }

							// L'image
							// @since 1.3.1. Huffingtonpost au moins 2 media_content + Attr:medium
							if ( isset( $item->media_content ) && count( $item->media_content ) > 1 && isset( $item->media_content[0]['medium'] ) ) {
								$feed['rss'][ $index ]['img'] = (string) $item->media_content[0]['url']; }
							// Vimeo
							elseif ( isset( $item->media_content->media_thumbnail['url'] ) ) {
								$feed['rss'][ $index ]['img'] = (string) $item->media_content->media_thumbnail['url']; }
							// Youtube
							elseif ( isset( $item->media_group->media_thumbnail ) ) {
								$feed['rss'][ $index ]['img'] = (string) $item->media_group->media_thumbnail['url']; }
							// Feedburner Allociné
							elseif ( isset( $item->media_thumbnail ) ) {
								$feed['rss'][ $index ]['img'] = (string) $item->media_thumbnail['url']; }
							// Flux standard RSS
							elseif ( isset( $item->enclosure ) ) {
								$feed['rss'][ $index ]['img'] = (string) $item->enclosure['url']; }
							// Feedburner
							elseif ( isset( $item->media_group->media_content ) ) {
								$feed['rss'][ $index ]['img'] = (string) $item->media_group->media_content['url']; }
							// 2 media_content. The gardian & Huffingtonpost (2ème peut contenir lien youtube)
							elseif ( isset( $item->media_content[1] ) ) {
								$feed['rss'][ $index ]['img'] = (string) $item->media_content[1]['url']; }
							// Twitter
							elseif ( isset( $item->media_content ) ) {
								$feed['rss'][ $index ]['img'] = (string) $item->media_content['url']; }
							// IMG dans description (Pb avec Reuter)
							elseif ( preg_match( '/<img src="(.*?)"/i', $feed['rss'][ $index ]['description'], $m ) ) {
								preg_match( '/<img src="(.*?)"/i', $feed['rss'][ $index ]['description'], $m );
								$feed['rss'][ $index ]['img'] = (string) $m[1];
							} else {
								$feed['rss'][ $index ]['img'] = isset( $item->link[1] ) ? (string) $item->link[1]['href'] : ''; } // Flux ATOM

							// Le lien image
							// Huffingtonpost (media_content[1] Attr:medium) Vimeo (media:player), Youtube (media:content), the gardian 2 media:content le lien de l'image est sur la vidéo, sinon c'est l'url de l'image
							if ( isset( $item->media_content ) && count( $item->media_content ) > 1 && isset( $item->media_content[1]['medium'] ) ) {
								$feed['rss'][ $index ]['imgLink'] = (string) $item->media_content[1]['url'];  } elseif ( isset( $item->media_content->media_player ) ) {
								$feed['rss'][ $index ]['imgLink'] = (string) $item->media_content->media_player['url']; } elseif ( isset( $item->media_group->media_content ) && isset( $item->media_group->media_thumbnail ) ) {
									$feed['rss'][ $index ]['imgLink'] = (string) $item->media_group->media_content['url']; } elseif ( isset( $item->media_content ) && isset( $item->media_content[1]['url'] ) ) {
									$feed['rss'][ $index ]['imgLink'] = (string) $item->media_content[1]['url']; } elseif ( isset( $item->media_content ) ) {
												$feed['rss'][ $index ]['imgLink'] = (string) $item->media_content['url']; } else {
										$feed['rss'][ $index ]['imgLink'] = $feed['rss'][ $index ]['img']; }

												// Supprime toutes les balises, les retours chariots et les tabulations dans description
												if ( ! empty( $feed['rss'][ $index ]['description'] ) ) {
													$feed['rss'][ $index ]['description'] = preg_replace( '/<[^>]+>/', ' ', $feed['rss'][ $index ]['description'] );
													$feed['rss'][ $index ]['description'] = preg_replace( '#\n|\t|\r#', ' ', $feed['rss'][ $index ]['description'] );
													$feed['rss'][ $index ]['description'] = preg_replace( '/\s\s+/', ' ', $feed['rss'][ $index ]['description'] );
												} else {
													$feed['rss'][ $index ]['description'] = $feed['rss'][ $index ]['title'];
												}
												$index++;
}

echo json_encode( $feed );

/**
 *
 * @since 1.8.5 Teste corectement la valeur de retour de 'file_get_contents'
 */
function scrape_rss( $urlUser ) {
	if ( ( $xml = @file_get_contents( $urlUser ) ) === false ) {
		$error_last = error_get_last();
		if ( preg_match( '/(404)/', $error_last['message'] ) ) {
			preg_match( '/\(([^\)]+)\)/', $error_last['message'], $match );
			echo '"' . urldecode( $match[1] ) . '": ' . esc_html__( "La page demandée n'existe pas.", 'eac-components' );
		} elseif ( preg_match( '/(403)/', $error_last['message'] ) ) {
			preg_match( '/\(([^\)]+)\)/', $error_last['message'], $match );
			echo '"' . urldecode( $match[1] ) . '": ' . esc_html__( 'Accès refusé.', 'eac-components' );
		} elseif ( preg_match( '/(401)/', $error_last['message'] ) ) {
			preg_match( '/\(([^\)]+)\)/', $error_last['message'], $match );
			echo '"' . urldecode( $match[1] ) . '": ' . esc_html__( 'Non autorisé.', 'eac-components' );
		} elseif ( preg_match( '/(503)/', $error_last['message'] ) ) {
			preg_match( '/\(([^\)]+)\)/', $error_last['message'], $match );
			echo '"' . urldecode( $match[1] ) . '": ' . esc_html__( 'Service indisponible. Réessayer plus tard.', 'eac-components' );
		} elseif ( preg_match( '/(405)/', $error_last['message'] ) ) {
			preg_match( '/\(([^\)]+)\)/', $error_last['message'], $match );
			echo '"' . urldecode( $match[1] ) . '": ' . esc_html__( 'Méthode non autorisée.', 'eac-components' );
		} elseif ( preg_match( '/(429)/', $error_last['message'] ) ) {
			preg_match( '/\(([^\)]+)\)/', $error_last['message'], $match );
			echo '"' . urldecode( $match[1] ) . '": ' . esc_html__( 'Trop de requêtes.', 'eac-components' );
		} elseif ( preg_match( '/(495)/', $error_last['message'] ) ) {
			preg_match( '/\(([^\)]+)\)/', $error_last['message'], $match );
			echo '"' . urldecode( $match[1] ) . '": ' . esc_html__( 'Certificat SSL invalide.', 'eac-components' );
		} elseif ( preg_match( '/(496)/', $error_last['message'] ) ) {
			preg_match( '/\(([^\)]+)\)/', $error_last['message'], $match );
			echo '"' . urldecode( $match[1] ) . '": ' . esc_html__( 'Certificat SSL requis.', 'eac-components' );
		} elseif ( preg_match( '/(500)/', $error_last['message'] ) ) {
			preg_match( '/\(([^\)]+)\)/', $error_last['message'], $match );
			echo '"' . urldecode( $match[1] ) . '": ' . esc_html__( 'Erreur Interne du Serveur.', 'eac-components' );
		} else {
			echo esc_html__( 'HTTP: La requête a échoué.', 'eac-components' );
		}
		error_clear_last();

		return false;
	}

	$xml = str_replace( 'dc:creator', 'dc_creator', $xml );
	$xml = str_replace( 'media:content', 'media_content', $xml );
	$xml = str_replace( 'media:description', 'media_description', $xml );
	$xml = str_replace( 'media:thumbnail', 'media_thumbnail', $xml );
	$xml = str_replace( 'media:group', 'media_group', $xml );
	$xml = str_replace( 'media:player', 'media_player', $xml );
	$xml = str_replace( 'media:embed', 'media_embed', $xml );

	libxml_use_internal_errors( true );
	$obj = @SimpleXML_Load_String( $xml, 'SimpleXMLElement', LIBXML_NOCDATA );

	if ( $obj === false ) {
		echo esc_html__( "Une erreur s'est produite lors de la lecture de la source", 'eac-components' );
		libxml_use_internal_errors( false );
		return false;
	}

	libxml_use_internal_errors( false );
	return $obj;
}
