<?php

namespace EACCustomWidgets\Proxy;

$parse_uri = explode( 'wp-content', $_SERVER['SCRIPT_FILENAME'] );
require_once $parse_uri[0] . 'wp-load.php';

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! isset( $_REQUEST['id'] ) || ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( $_REQUEST['nonce'], 'eac_file_osm_nonce_' . $_REQUEST['id'] ) ) {
	header( 'Content-Type: text/plain' );
	echo esc_html__( 'Jeton invalide. Actualiser la page courante...', 'eac-components' );
	exit;
}

if ( ! ini_get( 'allow_url_fopen' ) || ! isset( $_REQUEST['url'] ) ) {
	header( 'Content-Type: text/plain' );
	echo esc_html__( '"allow_url_fopen" est désactivé', 'eac-components' );
	exit;
}

$file = filter_var( urldecode( $_REQUEST['url'] ), FILTER_SANITIZE_STRING );

$ctx = stream_context_create( array( 'http' => array( 'timeout' => 15 ) ) );

if ( ( $file_source = @file_get_contents( $file, false, $ctx ) ) === false ) {
		header( 'Content-Type: text/plain' );
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
		echo '"' . urldecode( $match[1] ) . '": ' . esc_html__( 'Certificat SSL invalide.', 'eac-components' );// SSL Certificate Error
	} elseif ( preg_match( '/(496)/', $error_last['message'] ) ) {
		preg_match( '/\(([^\)]+)\)/', $error_last['message'], $match );
		echo '"' . urldecode( $match[1] ) . '": ' . esc_html__( 'Certificat SSL requis.', 'eac-components' );// SSL Certificate Required
	} elseif ( preg_match( '/(500)/', $error_last['message'] ) ) {
		preg_match( '/\(([^\)]+)\)/', $error_last['message'], $match );
		echo '"' . urldecode( $match[1] ) . '": ' . esc_html__( 'Erreur Interne du Serveur.', 'eac-components' );
	} else {
		echo esc_html__( 'HTTP: La requête a échoué.', 'eac-components' );
	}
		error_clear_last();

		exit;
}

header( 'Content-Type: application/json' );
echo $file_source;
