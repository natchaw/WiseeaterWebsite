<?php
/**
 * Class: Open_Streetmap_Widget
 * Name: OpenStreetMap
 * Slug: eac-addon-open-streetmap
 *
 * Icon by: https://templatic.com/newsblog/100-free-templatic-map-icons/
 *
 * Description: Affiche une Map et ses marqueurs avec le projet OpenStreetMap alternatif à GoogleMap
 * Projet collaboratif de cartographie en ligne qui vise à constituer une base de données géographiques libre du monde
 *
 * @since 1.8.8
 * @since 1.9.0 Intégration des scripts et des styles dans le constructeur de la class
 * @since 1.9.5 Intégration de la configuration des tuiles
 *              Intégration de la configuration des marqueurs
 *              Intégration de l'import de données d'un fichier geoJSON
 */

namespace EACCustomWidgets\Widgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

use EACCustomWidgets\EAC_Plugin;
use EACCustomWidgets\Core\Utils\Eac_Tools_Util;
use EACCustomWidgets\Core\Eac_Config_Elements;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Core\Schemes\Typography;
use Elementor\Core\Schemes\Color;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Repeater;
use Elementor\Group_Control_Css_Filter;

class Open_Streetmap_Widget extends Widget_Base {

	/**
	 * $config_layers
	 *
	 * URL du fichier de configuration des tuiles (tiles)
	 *
	 * @since 1.9.5
	 */
	private $config_layers = EAC_ADDONS_URL . 'includes/config/osm/osmTiles.json';

	/**
	 * $layer_default
	 *
	 * La tuile par défaut
	 *
	 * @since 1.9.5
	 */
	private $layer_default = 'Basic';


	/**
	 * $base_layers
	 *
	 * Liste des tuiles (tiles) extraitent du fichier de configuration 'json'
	 *
	 * @since 1.9.5
	 */
	private $base_layers = array();

	/**
	 * $base_layers_default
	 *
	 * Liste des tuiles (tiles) par défaut
	 *
	 * @since 1.8.8
	 */
	private $base_layers_default = array(
		'osm_basic'     => 'OSM Basic',
		'osm_fr'        => 'OSM France',
		'osm_de'        => 'OSM Deutschland',
		'osm_bw'        => 'OSM B&W',
		'stamenToner'   => 'Toner',
		'stamenColor'   => 'Watercolor',
		'stamenLite'    => 'Toner Lite',
		'stamenTerrain' => 'Terrain',
		'topoMap'       => 'Topo Map',
	);

	/**
	 * $config_icons
	 *
	 * URL du fichier de configuration des icones
	 *
	 * @since 1.9.5
	 */
	private $config_icons = EAC_ADDONS_URL . 'includes/config/osm/osmIcons.json';

	/**
	 * $base_icons
	 *
	 * Liste des icones extraitent du fichier de configuraion 'json'
	 *
	 * @since 1.9.5
	 */
	private $base_icons = array();

	/**
	 * $base_icons_sizes
	 *
	 * Les dimensions des icones extraitent du fichier de configuraion 'json'
	 *
	 * @since 1.9.5
	 */
	private $base_icons_sizes = array();

	/**
	 * $sizes_icons_default
	 *
	 * Les dimensions par défaut des icones
	 *
	 * @since 1.9.5
	 */
	private $sizes_icons_default = '33,44';

	/**
	 * $base_icons_default
	 *
	 * Liste des icones par défaut pour les marqueurs (markers)
	 *
	 * @since 1.8.8
	 */
	private $base_icons_default = array(
		'default.png'           => 'Default',
		'automotive.png'        => 'Automotive',
		'bars.png'              => 'Bars',
		'books-media.png'       => 'Books & Media',
		'clothings.png'         => 'Clothings',
		'commercial-places.png' => 'Commercial places',
		'doctors.png'           => 'Doctors',
		'exhibitions.png'       => 'Exhibitions',
		'fashion.png'           => 'Fashion',
		'food.png'              => 'Food',
		'government.png'        => 'Government',
		'health-medical.png'    => 'Health Medical',
		'hotels.png'            => 'Hotels',
		'industries.png'        => 'Industries',
		'libraries.png'         => 'Libraries',
		'magazines.png'         => 'Magazines',
		'movies.png'            => 'Movies',
		'museums.png'           => 'Museums',
		'nightlife.png'         => 'Nightlife',
		'parks.png'             => 'Parks',
		'places.png'            => 'Places',
		'real-estate.png'       => 'Real Estate',
		'restaurants.png'       => 'Restaurants',
		'schools.png'           => 'Schools',
		'sports.png'            => 'Sports',
		'swimming-pools.png'    => 'Swimming-pools',
		'transport.png'         => 'Transport',
		'travel.png'            => 'Travel',
	);

	/**
	 * $title_default
	 *
	 * La propriété 'title' par défaut
	 *
	 * @since 1.9.5
	 */
	private $title_default = 'No Title';

	/**
	 * Constructeur de la class Open_Streetmap_Widget
	 *
	 * Enregistre les scripts et les styles
	 *
	 * @since 1.9.0
	 */
	public function __construct( $data = array(), $args = null ) {
		parent::__construct( $data, $args );

		// @since 1.9.5 Valorise la liste des tuiles (tiles)
		$this->setTilesConfig();

		// @since 1.9.5 Valorise la liste des icones
		$this->setIconsConfig();

		wp_register_script( 'leaflet', 'https://unpkg.com/leaflet@1.7.1/dist/leaflet.js', array(), '1.7.1', true );
		wp_register_script( 'marker-cluster', 'https://unpkg.com/leaflet.markercluster@1.5.0/dist/leaflet.markercluster.js', array(), '1.5.0', true );
		wp_register_script( 'eac-openstreetmap', EAC_Plugin::instance()->get_register_script_url( 'eac-openstreetmap' ), array( 'jquery', 'elementor-frontend' ), '1.8.8', true );

		wp_register_style( 'marker-cluster', 'https://unpkg.com/leaflet.markercluster@1.5.0/dist/MarkerCluster.css', array(), '1.5.0' );
		wp_register_style( 'marker-cluster-default', 'https://unpkg.com/leaflet.markercluster@1.5.0/dist/MarkerCluster.Default.css', array(), '1.5.0' );
		wp_register_style( 'eac-leaflet', EAC_Plugin::instance()->get_register_style_url( 'leaflet' ), array( 'eac' ), '1.8.8' );
	}

	/**
	 * Le nom de la clé du composant dans le fichier de configuration
	 *
	 * @var $slug
	 *
	 * @access private
	 */
	private $slug = 'open-streetmap';

	/**
	 * Retrieve widget name.
	 *
	 * @access public
	 *
	 * @return widget name.
	 */
	public function get_name() {
		return Eac_Config_Elements::get_widget_name( $this->slug );
	}

	/**
	 * Retrieve widget title.
	 *
	 * @access public
	 *
	 * @return widget title.
	 */
	public function get_title() {
		return Eac_Config_Elements::get_widget_title( $this->slug );
	}

	/**
	 * Retrieve widget icon.
	 *
	 * @access public
	 *
	 * @return widget icon.
	 * https://char-map.herokuapp.com/
	 */
	public function get_icon() {
		return Eac_Config_Elements::get_widget_icon( $this->slug );
	}

	/**
	 * Affecte le composant à la catégorie définie dans plugin.php
	 *
	 * @access public
	 *
	 * @return widget category.
	 */
	public function get_categories() {
		return Eac_Config_Elements::get_widget_categories( $this->slug );
	}

	/**
	 * Load dependent libraries
	 *
	 * @access public
	 *
	 * @return libraries list.
	 */
	public function get_script_depends() {
		return array( 'leaflet', 'marker-cluster', 'eac-openstreetmap' );
	}

	/**
	 * Load dependent styles
	 * Les styles sont chargés dans le footer
	 *
	 * @access public
	 *
	 * @return CSS list.
	 */
	public function get_style_depends() {
		return array( 'eac-leaflet', 'marker-cluster', 'marker-cluster-default' );
	}

	/**
	 * Get widget keywords.
	 *
	 * Retrieve the list of keywords the widget belongs to.
	 *
	 * @since 1.7.0
	 * @access public
	 *
	 * @return array Widget keywords.
	 */
	public function get_keywords() {
		return Eac_Config_Elements::get_widget_keywords( $this->slug );
	}

	/**
	 * Get help widget get_custom_help_url.
	 *
	 * @since 1.7.0
	 * @access public
	 *
	 * @return URL help center
	 */
	public function get_custom_help_url() {
		return Eac_Config_Elements::get_widget_help_url( $this->slug );
	}

	/**
	 * Register widget controls.
	 *
	 * Adds different input fields to allow the user to change and customize the widget settings.
	 *
	 * @access protected
	 */
	protected function register_controls() {

		$this->start_controls_section(
			'osm_settings_map',
			array(
				'label' => esc_html__( 'Carte', 'eac-components' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

			/*
			$this->add_control('osm_settings_client_geolocate',
				[
					'label' => esc_html__("Localiser le visiteur (Géolocaliser)", 'eac-components'),
					'type' => Controls_Manager::SWITCHER,
					'description' => esc_html__('La gélocalisation doit être activée', 'eac-components'),
					'label_on' => esc_html__('oui', 'eac-components'),
					'label_off' => esc_html__('non', 'eac-components'),
					'return_value' => 'yes',
					'default' => '',
					'conditions' => [
						'terms' => [
							['name' => 'osm_settings_client_ip', 'operator' => '!==', 'value' => 'yes'],
							['name' => 'osm_settings_search', 'operator' => '!==', 'value' => 'yes'],
						],
					],
				]
			);*/

			$this->add_control(
				'osm_settings_client_ip',
				array(
					'label'        => esc_html__( 'Localiser le visiteur (Adresse IP)', 'eac-components' ),
					'type'         => Controls_Manager::SWITCHER,
					'description'  => esc_html__( "Localisation par l'adresse IP ne fonctionne pas sur un serveur local.", 'eac-components' ),
					'label_on'     => esc_html__( 'oui', 'eac-components' ),
					'label_off'    => esc_html__( 'non', 'eac-components' ),
					'return_value' => 'yes',
					'default'      => '',
					'conditions'   => array(
						'terms' => array(
							// ['name' => 'osm_settings_client_geolocate', 'operator' => '!==', 'value' => 'yes'],
							array(
								'name'     => 'osm_settings_search',
								'operator' => '!==',
								'value'    => 'yes',
							),
						),
					),
				)
			);

			$this->add_control(
				'osm_settings_client_warning',
				array(
					'type'            => Controls_Manager::RAW_HTML,
					'content_classes' => 'elementor-panel-alert elementor-panel-alert-warning',
					'raw'             => esc_html__( 'La géolocalisation vers la bonne ville peut être moins fiable pour les adresses IP distribuées par les opérateurs mobiles.', 'eac-components' ),
					'condition'       => array( 'osm_settings_client_ip' => 'yes' ),
				)
			);

			$this->add_control(
				'osm_settings_search',
				array(
					'label'        => esc_html__( 'Rechercher une adresse', 'eac-components' ),
					'type'         => Controls_Manager::SWITCHER,
					'label_on'     => esc_html__( 'oui', 'eac-components' ),
					'label_off'    => esc_html__( 'non', 'eac-components' ),
					'return_value' => 'yes',
					'default'      => '',
					'conditions'   => array(
						'terms' => array(
							// ['name' => 'osm_settings_client_geolocate', 'operator' => '!==', 'value' => 'yes'],
							array(
								'name'     => 'osm_settings_client_ip',
								'operator' => '!==',
								'value'    => 'yes',
							),
						),
					),
				)
			);

			$this->add_control(
				'osm_settings_search_help',
				array(
					'label'     => esc_html__( '', 'eac-components' ),
					'type'      => Controls_Manager::RAW_HTML,
					'raw'       => __( "<span style='font-size:10px;'>Entrer l'adresse puis bouton 'Search'</span>", 'eac-components' ),
					'condition' => array( 'osm_settings_search' => 'yes' ),
				)
			);

			$this->add_control(
				'osm_settings_search_addresse', // elementor-control-osm_settings_search_addresse
				array(
					'label'       => esc_html__( 'Adresse', 'eac-components' ),
					'type'        => Controls_Manager::RAW_HTML,
					'raw'         => '<form onsubmit="getNominatimAddress(this);" action="javascript:void(0);"><input type="text" id="eac-get-nominatim-address" class="eac-get-nominatim-address" style="margin-top:10px; margin-bottom:10px;"><input type="submit" value="Search" class="elementor-button elementor-button-success" style="padding:8px 0;" onclick="getNominatimAddress(this)"></form>',
					'label_block' => true,
					'condition'   => array( 'osm_settings_search' => 'yes' ),
				)
			);

			$this->add_control(
				'osm_settings_center_lat', // elementor-control-osm_settings_center_lat
				array(
					'label'       => esc_html__( 'Latitude', 'eac-components' ),
					'type'        => Controls_Manager::TEXT,
					'dynamic'     => array( 'active' => true ),
					'label_block' => true,
					'condition'   => array( 'osm_settings_search' => 'yes' ),
				)
			);

			$this->add_control(
				'osm_settings_center_lng', // elementor-control-osm_settings_center_lng
				array(
					'label'       => esc_html__( 'Longitude', 'eac-components' ),
					'type'        => Controls_Manager::TEXT,
					'dynamic'     => array( 'active' => true ),
					'label_block' => true,
					'condition'   => array( 'osm_settings_search' => 'yes' ),
				)
			);

			$this->add_control(
				'osm_settings_center_help',
				array(
					'label'     => esc_html__( '', 'eac-components' ),
					'type'      => Controls_Manager::RAW_HTML,
					'raw'       => __( '<span style="font-size:10px;">Cliquez <a href="https://www.coordonnees-gps.fr/" target="_blank" rel="nofollow noopener noreferrer" >ici</a> pour obtenir des coordonnées de localisation</span>', 'eac-components' ),
					'condition' => array( 'osm_settings_search' => 'yes' ),
				)
			);

			$this->add_control(
				'osm_settings_center_title', // elementor-control-osm_settings_center_title
				array(
					'label'       => esc_html__( "Titre de l'infobulle", 'eac-components' ),
					'type'        => Controls_Manager::TEXT,
					'placeholder' => esc_html__( "Titre de l'infobulle", 'eac-components' ),
					'dynamic'     => array( 'active' => true ),
					'label_block' => true,
					'separator'   => 'before',
				)
			);

			$this->add_control(
				'osm_settings_center_content',
				array(
					'label'       => esc_html__( "Contenu de l'infobulle", 'eac-components' ),
					'type'        => Controls_Manager::TEXTAREA,
					'placeholder' => esc_html__( "Contenu de l'infobulle", 'eac-components' ),
					'dynamic'     => array( 'active' => true ),
					'label_block' => true,
				)
			);

		$this->end_controls_section();

		$this->start_controls_section(
			'osm_markers',
			array(
				'label' => esc_html__( 'Marqueurs', 'eac-components' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

			$repeater = new Repeater();

			$repeater->start_controls_tabs( 'osm_markers_tabs' );

				$repeater->start_controls_tab(
					'osm_markers_tab_position',
					array(
						'label' => '<i class="awesome-position" aria-hidden="true"></i>',
					)
				);

					$repeater->add_control(
						'osm_markers_search_help',
						array(
							'label' => esc_html__( '', 'eac-components' ),
							'type'  => Controls_Manager::RAW_HTML,
							'raw'   => __( "<span style='font-size:10px;'>Entrer l'adresse puis bouton 'Search'</span>", 'eac-components' ),
						)
					);

					$repeater->add_control(
						'osm_markers_search_addresse', // elementor-control-osm_markers_search_addresse
						array(
							'label'       => esc_html__( 'Adresse', 'eac-components' ),
							'type'        => Controls_Manager::RAW_HTML,
							'raw'         => '<form onsubmit="getNominatimRepeaterAddress(this);" action="javascript:void(0);"><input type="text" id="eac-get-nominatim-address" class="eac-get-nominatim-address" style="margin-top:10px; margin-bottom:10px;"><input type="submit" value="Search" class="elementor-button elementor-button-success" style="padding:8px 0;" onclick="getNominatimRepeaterAddress(this)"></form>',
							'label_block' => true,
						)
					);

					$repeater->add_control(
						'osm_markers_tooltip_lat', // elementor-control-osm_markers_tooltip_lat
						array(
							'label'       => esc_html__( 'Latitude', 'eac-components' ),
							'type'        => Controls_Manager::TEXT,
							'dynamic'     => array( 'active' => true ),
							'label_block' => true,
						)
					);

					$repeater->add_control(
						'osm_markers_tooltip_lng', // elementor-control-osm_markers_tooltip_lng
						array(
							'label'       => esc_html__( 'Longitude', 'eac-components' ),
							'type'        => Controls_Manager::TEXT,
							'dynamic'     => array( 'active' => true ),
							'label_block' => true,
						)
					);

					$repeater->add_control(
						'osm_markers_tooltip_help',
						array(
							'label' => esc_html__( '', 'eac-components' ),
							'type'  => Controls_Manager::RAW_HTML,
							'raw'   => __( '<span style="font-size:10px;">Cliquez <a href="https://www.coordonnees-gps.fr/" target="_blank" rel="nofollow noopener noreferrer" >ici</a> pour obtenir des coordonnées de localisation</span>', 'eac-components' ),
						)
					);

				$repeater->end_controls_tab();

				$repeater->start_controls_tab(
					'osm_markers_tab_content',
					array(
						'label' => '<i class="awesome-content" aria-hidden="true"></i>',
					)
				);

					$repeater->add_control(
						'osm_markers_tooltip_title', // elementor-control-osm_markers_tooltip_title
						array(
							'label'       => esc_html__( "Titre de l'infobulle", 'eac-components' ),
							'type'        => Controls_Manager::TEXT,
							'placeholder' => esc_html__( "Titre de l'infobulle", 'eac-components' ),
							'dynamic'     => array( 'active' => true ),
							'label_block' => true,
						)
					);

					$repeater->add_control(
						'osm_markers_tooltip_content',
						array(
							'label'       => esc_html__( "Contenu de l'infobulle", 'eac-components' ),
							'type'        => Controls_Manager::TEXTAREA,
							'placeholder' => esc_html__( "Contenu de l'infobulle", 'eac-components' ),
							'dynamic'     => array( 'active' => true ),
							'label_block' => true,
							// 'render_type' => 'none',
						)
					);

					$repeater->add_control(
						'osm_markers_tooltip_marker',
						array(
							'label'       => esc_html__( 'Sélectionner une icône', 'eac-components' ),
							'type'        => Controls_Manager::SELECT,
							'options'     => $this->base_icons,
							'default'     => 'default.png',
							'label_block' => true,
						)
					);

				$repeater->end_controls_tab();

			$repeater->end_controls_tabs();

			$this->add_control(
				'osm_markers_import_list',
				array(
					'label'        => esc_html__( 'Importer des marqueurs', 'eac-components' ),
					'type'         => Controls_Manager::SWITCHER,
					'label_on'     => esc_html__( 'oui', 'eac-components' ),
					'label_off'    => esc_html__( 'non', 'eac-components' ),
					'return_value' => 'yes',
					'default'      => '',
				)
			);

			$this->add_control(
				'osm_markers_import_type',
				array(
					'label'       => esc_html__( 'Type de lien', 'eac-components' ),
					'description' => esc_html__( "Local = le fichier est dans le répertoire '/includes/config/osm/markers'.", 'eac-components' ),
					'type'        => Controls_Manager::SELECT,
					'default'     => 'none',
					'options'     => array(
						'none' => esc_html__( 'Aucun', 'eac-components' ),
						'url'  => esc_html__( 'URL', 'eac-components' ),
						'file' => esc_html__( 'Local', 'eac-components' ),
					),
					'condition'   => array( 'osm_markers_import_list' => 'yes' ),
				)
			);

			$this->add_control(
				'osm_markers_import_url',
				array(
					'label'       => esc_html__( 'URL', 'eac-components' ),
					'description' => esc_html__( "Coller le chemin absolu du fichier 'geoJSON'", 'eac-components' ),
					'type'        => Controls_Manager::URL,
					'placeholder' => 'http://your-link.com/file-geojson.json',
					'condition'   => array(
						'osm_markers_import_list' => 'yes',
						'osm_markers_import_type' => 'url',
					),
				)
			);

			$this->add_control(
				'osm_markers_import_file',
				array(
					'label'       => esc_html__( 'Sélectionner le fichier', 'eac-components' ),
					'description' => esc_html__( "Format de données 'geoJSON' avec 'json' comme extension de fichier.", 'eac-components' ),
					'type'        => Controls_Manager::SELECT,
					'default'     => 'none',
					'options'     => Eac_Tools_Util::get_directory_files_list( 'includes/config/osm/markers', 'application/json' ),
					'label_block' => true,
					'condition'   => array(
						'osm_markers_import_list' => 'yes',
						'osm_markers_import_type' => 'file',
					),
				)
			);

			$this->add_control(
				'osm_markers_import_keywords',
				array(
					'label'       => esc_html__( 'Mots-clés', 'eac-components' ),
					'description' => esc_html__( "Liste de 'propriété|label' séparée par le caractère '|' avec une paire par ligne.", 'eac-components' ),
					'placeholder' => 'property|label' . chr( 13 ) . 'property|label' . chr( 13 ) . 'property|label',
					// 'default' => 'com_nom|Town'.chr(13).'name|Name'.chr(13).'marque|Brands'.chr(13).'cinema3d|3D'.chr(13).'nb_screens|Number of screens'.chr(13).'capacity|Capacity'.chr(13).'wheelchair|Wheelchair'.chr(13).'phone|Phone'.chr(13).'website|Web site',
					'type'        => Controls_Manager::TEXTAREA,
					'dynamic'     => array( 'active' => true ),
					'label_block' => true,
					'condition'   => array( 'osm_markers_import_list' => 'yes' ),
				)
			);

			$this->add_control(
				'osm_markers_import_marker',
				array(
					'label'       => esc_html__( 'Sélectionner une icône', 'eac-components' ),
					'type'        => Controls_Manager::SELECT,
					'options'     => $this->base_icons,
					'default'     => 'default.png',
					'label_block' => true,
					'condition'   => array( 'osm_markers_import_list' => 'yes' ),
				)
			);

			$this->add_control(
				'osm_markers_list',
				array(
					'label'       => esc_html__( 'Liste des marqueurs', 'eac-components' ),
					'type'        => Controls_Manager::REPEATER,
					'fields'      => $repeater->get_controls(),
					'title_field' => '{{{ osm_markers_tooltip_title }}}',
					'condition'   => array( 'osm_markers_import_list!' => 'yes' ),
					'separator'   => 'before',
				)
			);

		$this->end_controls_section();

		$this->start_controls_section(
			'osm_settings',
			array(
				'label' => esc_html__( 'Réglages', 'eac-components' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

			$this->add_control(
				'osm_settings_zoom_auto',
				array(
					'label'        => esc_html__( 'Zoom automatique', 'eac-components' ),
					'type'         => Controls_Manager::SWITCHER,
					'description'  => esc_html__( 'Afficher tous les marqueurs dans le viewport.', 'eac-components' ),
					'label_on'     => esc_html__( 'oui', 'eac-components' ),
					'label_off'    => esc_html__( 'non', 'eac-components' ),
					'return_value' => 'yes',
					'default'      => '',
					'conditions'   => array(
						'relation' => 'or',
						'terms'    => array(
							array(
								'terms' => array(
									array(
										'name'     => 'osm_markers_import_list',
										'operator' => '===',
										'value'    => 'yes',
									),
									array(
										'name'     => 'osm_markers_import_type',
										'operator' => '===',
										'value'    => 'none',
									),
								),
							),
							array(
								'terms' => array(
									array(
										'name'     => 'osm_markers_import_list',
										'operator' => '!==',
										'value'    => 'yes',
									),
								),
							),
						),
					),
				)
			);

			$this->add_control(
				'osm_settings_zoom',
				array(
					'label'      => esc_html__( 'Facteur de zoom', 'eac-components' ),
					'type'       => Controls_Manager::NUMBER,
					'min'        => 1,
					'max'        => 20,
					'default'    => 12,
					'step'       => 1,
					'conditions' => array(
						'relation' => 'or',
						'terms'    => array(
							array(
								'terms' => array(
									array(
										'name'     => 'osm_markers_import_list',
										'operator' => '===',
										'value'    => 'yes',
									),
									array(
										'name'     => 'osm_markers_import_type',
										'operator' => '===',
										'value'    => 'none',
									),
									array(
										'name'     => 'osm_settings_zoom_auto',
										'operator' => '!==',
										'value'    => 'yes',
									),
								),
							),
							array(
								'terms' => array(
									array(
										'name'     => 'osm_markers_import_list',
										'operator' => '!==',
										'value'    => 'yes',
									),
									array(
										'name'     => 'osm_settings_zoom_auto',
										'operator' => '!==',
										'value'    => 'yes',
									),

								),
							),
						),
					),
				)
			);

			$this->add_responsive_control(
				'osm_settings_height',
				array(
					'label'       => esc_html__( 'Hauteur min.', 'eac-components' ),
					'type'        => Controls_Manager::SLIDER,
					'size_units'  => array( 'px' ),
					'default'     => array(
						'unit' => 'px',
						'size' => 350,
					),
					'range'       => array(
						'px' => array(
							'min'  => 150,
							'max'  => 1000,
							'step' => 50,
						),
					),
					'selectors'   => array( '{{WRAPPER}} .osm-map_wrapper-map' => 'min-height: {{SIZE}}{{UNIT}};' ),
					'render_type' => 'template',
				)
			);

			$this->add_control(
				'osm_settings_layers',
				array(
					'label'       => esc_html__( 'Sélectionner le calque par défaut', 'eac-components' ),
					'type'        => Controls_Manager::SELECT,
					'options'     => $this->base_layers,
					'default'     => 'osm_basic',
					'label_block' => true,
				)
			);

		$this->end_controls_section();

		$this->start_controls_section(
			'osm_content',
			array(
				'label' => esc_html__( 'Controls', 'eac-components' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

			/*
			$this->add_control('osm_content_tiles_control',
				[
					'label' => esc_html__("Réduire le menu des tuiles", 'eac-components'),
					'type' => Controls_Manager::SWITCHER,
					'label_on' => esc_html__('oui', 'eac-components'),
					'label_off' => esc_html__('non', 'eac-components'),
					'return_value' => 'yes',
					'default' => 'yes',
				]
			);*/

			$this->add_control(
				'osm_content_zoom_position',
				array(
					'label'        => esc_html__( 'Zoom en bas à gauche', 'eac-components' ),
					'type'         => Controls_Manager::SWITCHER,
					'label_on'     => esc_html__( 'oui', 'eac-components' ),
					'label_off'    => esc_html__( 'non', 'eac-components' ),
					'return_value' => 'yes',
					'default'      => '',
				)
			);

			$this->add_control(
				'osm_content_zoom',
				array(
					'label'        => esc_html__( 'Zoomer avec la souris', 'eac-components' ),
					'type'         => Controls_Manager::SWITCHER,
					'label_on'     => esc_html__( 'oui', 'eac-components' ),
					'label_off'    => esc_html__( 'non', 'eac-components' ),
					'return_value' => 'yes',
					'default'      => 'yes',
				)
			);

			$this->add_control(
				'osm_content_dblclick',
				array(
					'label'        => esc_html__( 'Double click pour zoomer', 'eac-components' ),
					'type'         => Controls_Manager::SWITCHER,
					'label_on'     => esc_html__( 'oui', 'eac-components' ),
					'label_off'    => esc_html__( 'non', 'eac-components' ),
					'return_value' => 'yes',
					'default'      => 'yes',
				)
			);

			$this->add_control(
				'osm_content_draggable',
				array(
					'label'        => esc_html__( 'Faire glisser la carte', 'eac-components' ),
					'type'         => Controls_Manager::SWITCHER,
					'label_on'     => esc_html__( 'oui', 'eac-components' ),
					'label_off'    => esc_html__( 'non', 'eac-components' ),
					'return_value' => 'yes',
					'default'      => 'yes',
				)
			);

			$this->add_control(
				'osm_content_click_popup',
				array(
					'label'        => esc_html__( 'Clicker pour fermer les info-bulles', 'eac-components' ),
					'type'         => Controls_Manager::SWITCHER,
					'label_on'     => esc_html__( 'oui', 'eac-components' ),
					'label_off'    => esc_html__( 'non', 'eac-components' ),
					'return_value' => 'yes',
					'default'      => 'yes',
				)
			);

		$this->end_controls_section();

		/**
		 * Generale Style Section
		 */
		$this->start_controls_section(
			'osm_global_style',
			array(
				'label' => esc_html__( 'Carte', 'eac-components' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

			$this->add_group_control(
				Group_Control_Border::get_type(),
				array(
					'name'     => 'osm_global_border',
					'selector' => '{{WRAPPER}} .osm-map_wrapper-map',
				)
			);

			$this->add_control(
				'osm_global_border_radius',
				array(
					'label'      => esc_html__( 'Rayon de la bordure', 'eac-components' ),
					'type'       => Controls_Manager::DIMENSIONS,
					'size_units' => array( 'px', '%' ),
					'selectors'  => array( '{{WRAPPER}} .osm-map_wrapper-map' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
				)
			);

			$this->add_group_control(
				Group_Control_Box_Shadow::get_type(),
				array(
					'name'     => 'osm_global_border_shadow',
					'label'    => esc_html__( 'Ombre', 'eac-components' ),
					'selector' => '{{WRAPPER}} .osm-map_wrapper-map',
				)
			);

			$this->add_group_control(
				Group_Control_Css_Filter::get_type(),
				array(
					'name'     => 'css_filters',
					'selector' => '{{WRAPPER}} .osm-map_wrapper-map',
				)
			);

		$this->end_controls_section();

		$this->start_controls_section(
			'osm_title_style',
			array(
				'label' => esc_html__( "Titre de l'infobulle", 'eac-components' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

			$this->add_control(
				'osm_title_color',
				array(
					'label'     => esc_html__( 'Couleur', 'eac-components' ),
					'type'      => Controls_Manager::COLOR,
					'scheme'    => array(
						'type'  => Color::get_type(),
						'value' => Color::COLOR_4,
					),
					'default'   => '#000000',
					'selectors' => array( '{{WRAPPER}} .leaflet-popup-content .osm-map_popup-title' => 'color: {{VALUE}};' ),
				)
			);

			$this->add_group_control(
				Group_Control_Typography::get_type(),
				array(
					'name'     => 'osm_title_typography',
					'label'    => esc_html__( 'Typographie', 'eac-components' ),
					'scheme'   => Typography::TYPOGRAPHY_1,
					'selector' => '{{WRAPPER}} .leaflet-popup-content .osm-map_popup-title',
				)
			);

			$this->add_responsive_control(
				'osm_title_position',
				array(
					'label'     => esc_html__( 'Alignement', 'eac-components' ),
					'type'      => Controls_Manager::CHOOSE,
					'default'   => 'center',
					'options'   => array(
						'left'   => array(
							'title' => esc_html__( 'Gauche', 'eac-components' ),
							'icon'  => 'eicon-text-align-left',
						),
						'center' => array(
							'title' => esc_html__( 'Centre', 'eac-components' ),
							'icon'  => 'eicon-text-align-center',
						),
						'right'  => array(
							'title' => esc_html__( 'Droit', 'eac-components' ),
							'icon'  => 'eicon-text-align-right',
						),
					),
					'selectors' => array( '{{WRAPPER}} .leaflet-popup-content .osm-map_popup-title' => 'text-align: {{VALUE}};' ),
				)
			);

		$this->end_controls_section();

		$this->start_controls_section(
			'osm_content_style',
			array(
				'label' => esc_html__( "Contenu de l'infobulle", 'eac-components' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

			$this->add_control(
				'osm_content_color',
				array(
					'label'     => esc_html__( 'Couleur', 'eac-components' ),
					'type'      => Controls_Manager::COLOR,
					'scheme'    => array(
						'type'  => Color::get_type(),
						'value' => Color::COLOR_4,
					),
					'default'   => '#000000',
					'selectors' => array( '{{WRAPPER}} .leaflet-popup-content .osm-map_popup-content, {{WRAPPER}} .leaflet-popup-content .osm-map_popup-content a' => 'color: {{VALUE}};' ),
				)
			);

			$this->add_group_control(
				Group_Control_Typography::get_type(),
				array(
					'name'     => 'osm_content_typography',
					'label'    => esc_html__( 'Typographie', 'eac-components' ),
					'scheme'   => Typography::TYPOGRAPHY_1,
					'selector' => '{{WRAPPER}} .leaflet-popup-content .osm-map_popup-content, {{WRAPPER}} .leaflet-popup-content .osm-map_popup-content a',
				)
			);

		$this->end_controls_section();
	}


	/**
	 * Render widget output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @access protected
	 */
	protected function render() {
		?>
		<div class="eac-open-streetmap">
			<input type="hidden" id="osm_nonce" name="osm_nonce" value="<?php echo wp_create_nonce( 'eac_file_osm_nonce_' . $this->get_id() ); ?>" />
			<?php $this->render_map(); ?>
		</div>
		<?php
	}

	/**
	 * Render widget output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @access protected
	 */
	protected function render_map() {
		$settings = $this->get_settings_for_display();
		$id       = $this->get_id();

		// Les balises acceptées pour le contenu du tooltip
		$allowed_content = array(
			'br'     => array(),
			'p'      => array(),
			'strong' => array(),
			'a'      => array(
				'href'   => array(),
				'target' => array(),
				'rel'    => array(),
			),
		);

		// Les valeurs par défaut: Paris
		$center_lat     = 48.8579;
		$center_lng     = 2.3491;
		$center_title   = ! empty( $settings['osm_settings_center_title'] ) ? wp_kses_post( $settings['osm_settings_center_title'] ) : esc_html__( "Titre de l'infobulle", 'eac-components' );
		$center_content = ! empty( $settings['osm_settings_center_content'] ) ? wp_kses( $settings['osm_settings_center_content'], $allowed_content ) : '';

		// @since 1.9.5
		$has_mapmarkers = $settings['osm_markers_import_list'] === 'yes' ? false : true;

		// La liste des marqueurs
		$map_markers = $has_mapmarkers === true ? $settings['osm_markers_list'] : array();

		// Coordonnées à partir de l'adresse IP
		$client_ip = $settings['osm_settings_client_ip'] === 'yes' ? true : false;

		// Le moteur de recherche
		$client_search = $settings['osm_settings_search'] === 'yes' ? true : false;

		if ( $client_search && ! empty( $settings['osm_settings_center_lat'] ) && ! empty( $settings['osm_settings_center_lng'] ) ) {
				$center_lat = ! empty( $settings['osm_settings_center_lat'] ) ? $settings['osm_settings_center_lat'] : $center_lat;
				$center_lng = ! empty( $settings['osm_settings_center_lng'] ) ? $settings['osm_settings_center_lng'] : $center_lng;

			// Calcule de l'adresse IP du client
		} elseif ( $client_ip && isset( $_SERVER['REMOTE_ADDR'] ) && ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
			$ip_address = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
			// $ip = unserialize(file_get_contents('http://ip-api.com/json/'. $ip_address));
			$ip = unserialize( file_get_contents( "http://www.geoplugin.net/php.gp?ip=$ip_address" ) );
			if ( $ip['geoplugin_status'] === 200 ) {
				$center_lat   = isset( $ip['geoplugin_latitude'] ) ? $ip['geoplugin_latitude'] : $center_lat;
				$center_lng   = isset( $ip['geoplugin_longitude'] ) ? $ip['geoplugin_longitude'] : $center_lng;
				$center_title = isset( $ip['geoplugin_city'] ) ? wp_kses_post( $ip['geoplugin_city'] ) : $center_title;
				if ( isset( $ip['geoplugin_countryName'] ) ) {
					$center_title .= ', ' . wp_kses_post( $ip['geoplugin_countryName'] ); }
			}
			// console_log($ip);
		}

		// La div wrapper
		$this->add_render_attribute( 'osm_wrapper', 'class', 'osm-map_wrapper' );
		$this->add_render_attribute( 'osm_wrapper', 'data-settings', $this->get_settings_json() );

		// La div du marqueur central
		$this->add_render_attribute( 'osm_marker', 'class', 'osm-map_wrapper-markercenter' );
		$this->add_render_attribute( 'osm_marker', 'data-lat', sanitize_text_field( $center_lat ) );
		$this->add_render_attribute( 'osm_marker', 'data-lng', sanitize_text_field( $center_lng ) );
		?>
		<div <?php echo $this->get_render_attribute_string( 'osm_wrapper' ); ?>>
			<!-- La div de la carte -->
			<div id="<?php echo $id; ?>" class="osm-map_wrapper-map"></div>
			
			<!-- Le marqueur central -->
			<div <?php echo $this->get_render_attribute_string( 'osm_marker' ); ?>>
				<div class="osm-map_marker-title"><?php echo $center_title; ?></div>
				<div class="osm-map_marker-content"><?php echo $center_content; ?></div>
			</div>
			<?php
			/** Les marqueurs du repeater */
			foreach ( $map_markers as $index => $marker ) {
				if ( ! empty( $marker['osm_markers_tooltip_lat'] ) && ! empty( $marker['osm_markers_tooltip_lng'] ) ) {
					$key = 'osm_markers_' . $index;
					$this->add_render_attribute(
						$key,
						array(
							'class'      => 'osm-map_wrapper-marker',
							'data-lat'   => sanitize_text_field( $marker['osm_markers_tooltip_lat'] ),
							'data-lng'   => sanitize_text_field( $marker['osm_markers_tooltip_lng'] ),
							'data-icon'  => $marker['osm_markers_tooltip_marker'],
							/** @since 1.9.5 */
							'data-sizes' => ! empty( $this->base_icons_sizes ) && isset( $this->base_icons_sizes[ $marker['osm_markers_tooltip_marker'] ] ) ? $this->base_icons_sizes[ $marker['osm_markers_tooltip_marker'] ] : $this->sizes_icons_default,
						)
					);
					?>
					<div <?php echo $this->get_render_attribute_string( $key ); ?>>
						<div class="osm-map_marker-title"><?php echo wp_kses_post( $marker['osm_markers_tooltip_title'] ); ?></div>
						<div class="osm-map_marker-content"><?php echo wp_kses( $marker['osm_markers_tooltip_content'], $allowed_content ); ?></div>
					</div>
					<?php
				}
			}
			?>
		</div>
		<?php
	}

	/**
	 * get_settings_json
	 *
	 * Retrieve fields values to pass at the widget container
	 * Convert on JSON format
	 * Read by 'openstreetmap.js' file when the component is loaded on the frontend
	 *
	 *  @uses      json_encode()
	 *
	 * @return    JSON oject
	 *
	 * @access    protected
	 * @updated   1.8.8
	 * @updated   1.9.5
	 */
	protected function get_settings_json() {
		$module_settings = $this->get_settings_for_display();
		$locate          = false; // $module_settings['osm_settings_client_geolocate'] === 'yes' ? true : false;
		$zoomauto        = $module_settings['osm_settings_zoom_auto'] === 'yes' ? true : false;
		$layer           = isset( $this->base_layers[ $module_settings['osm_settings_layers'] ] ) ? $this->base_layers[ $module_settings['osm_settings_layers'] ] : $this->layer_default;
		$has_import      = $module_settings['osm_markers_import_list'] === 'yes' ? true : false;
		$file_import     = '';
		if ( $has_import === true && $module_settings['osm_markers_import_type'] === 'url' && ! empty( $module_settings['osm_markers_import_url']['url'] ) ) {
			$file_import = $module_settings['osm_markers_import_url']['url'];
		} elseif ( $has_import === true && $module_settings['osm_markers_import_type'] === 'file' && ! empty( $module_settings['osm_markers_import_file'] ) && $module_settings['osm_markers_import_file'] !== 'none' ) {
			$file_import = $module_settings['osm_markers_import_file'];
		}
		$importIconSizes = ! empty( $this->base_icons_sizes ) && isset( $this->base_icons_sizes[ $module_settings['osm_markers_import_marker'] ] ) ? $this->base_icons_sizes[ $module_settings['osm_markers_import_marker'] ] : $this->sizes_icons_default;

		$settings = array(
			'data_id'            => $this->get_id(),
			'data_geolocate'     => $locate,
			'data_zoom'          => $zoomauto ? 12 : $module_settings['osm_settings_zoom'],
			'data_zoompos'       => $module_settings['osm_content_zoom_position'] === 'yes' ? true : false,
			'data_zoomauto'      => $zoomauto,
			'data_layer'         => $layer,
			'data_wheelzoom'     => $module_settings['osm_content_zoom'] === 'yes' ? true : false,
			'data_dblclick'      => $module_settings['osm_content_dblclick'] === 'yes' ? true : false,
			'data_draggable'     => $module_settings['osm_content_draggable'] === 'yes' ? true : false,
			'data_clickpopup'    => $module_settings['osm_content_click_popup'] === 'yes' ? true : false,
			'data_import'        => $has_import,
			'data_import_url'    => $file_import,
			'data_import_icon'   => $module_settings['osm_markers_import_marker'],
			'data_import_sizes'  => $importIconSizes,
			'data_keywords'      => ! empty( $module_settings['osm_markers_import_keywords'] ) ? preg_replace( "/\r|\n/", ',', $module_settings['osm_markers_import_keywords'] ) : '',
			'data_collapse_menu' => true, // $module_settings['osm_content_tiles_control'] === 'yes' ? true : false,
		);

		$settings = json_encode( $settings );
		return $settings;
	}

	/**
	 * setTilesConfig
	 *
	 * Récupère la liste des tuiles du fichier de configuration
	 * et affecte les variables nécessaires à la constitution de la liste
	 *
	 * @since 1.9.5
	 */
	private function setTilesConfig() {
		$filename = $this->config_layers;
		$layers   = array();

		$json   = @file_get_contents( $filename );
		$layers = json_decode( $json, true );
		if ( $layers == true ) {
			foreach ( $layers as $code => $args ) {
				$this->base_layers[ $code ] = isset( $args['options']['title'] ) ? $args['options']['title'] : $this->title_default;
			}
		} else {
			$this->base_layers = $this->base_layers_default;
		}
	}

	/**
	 * setIconsConfig
	 *
	 * Récupère la liste des icones du fichier de configuration
	 * et affecte les variables nécessaires à la constitution de la liste
	 *
	 * @since 1.9.5
	 */
	private function setIconsConfig() {
		$filename = $this->config_icons;
		$icons    = array();

		$json  = @file_get_contents( $filename );
		$icons = json_decode( $json, true );
		if ( $icons == true ) {
			foreach ( $icons as $code => $args ) {
				$this->base_icons[ $code ]       = isset( $args['title'] ) ? $args['title'] : $this->title_default;
				$this->base_icons_sizes[ $code ] = isset( $args['sizes'] ) ? $args['sizes'] : $this->sizes_icons_default;
			}
		} else {
			$this->base_icons = $this->base_icons_default;
		}
	}

	protected function content_template() {}
}
