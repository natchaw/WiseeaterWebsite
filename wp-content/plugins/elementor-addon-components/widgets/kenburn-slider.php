<?php
/**
 * Class: KenBurn_Slider_Widget
 * Name: Carrousel Ken Burn
 * Slug: eac-addon-kenburn-slider
 *
 * Description: KenBurn_Slider_Widget affiche des images animées
 * avec effet Ken Burn
 *
 * @since 0.0.9
 * @since 1.9.0 Intégration des scripts et des styles dans le constructeur de la class
 */

namespace EACCustomWidgets\Widgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

use EACCustomWidgets\EAC_Plugin;
use EACCustomWidgets\Core\Eac_Config_Elements;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Control_Media;

class KenBurn_Slider_Widget extends Widget_Base {

	/**
	 * Constructeur de la class KenBurn_Slider_Widget
	 *
	 * Enregistre les scripts et les styles
	 *
	 * @since 1.9.0
	 */
	public function __construct( $data = array(), $args = null ) {
		parent::__construct( $data, $args );

		wp_register_script( 'eac-smoothslides', EAC_ADDONS_URL . 'assets/js/kenburnslider/smoothslides.min.js', array( 'jquery' ), '2.2.1', true );
		wp_register_script( 'eac-kenburn-slider', EAC_Plugin::instance()->get_register_script_url( 'eac-kenburn-slider' ), array( 'jquery', 'elementor-frontend' ), '1.0.0', true );

		wp_register_style( 'eac-smoothslides', EAC_ADDONS_URL . 'assets/css/kenburn-slider.min.css', array( 'eac' ), '1.0.0' );
	}

	/**
	 * Le nom de la clé du composant dans le fichier de configuration
	 *
	 * @var $slug
	 *
	 * @access private
	 */
	private $slug = 'kenburn-slider';

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
		return array( 'eac-smoothslides', 'eac-kenburn-slider' );
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
		return array( 'eac-smoothslides' );
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
			'kbs_images_settings',
			array(
				'label' => esc_html__( 'Galerie', 'eac-components' ),
			)
		);

			$this->add_control(
				'kbs_galerie',
				array(
					'label'   => esc_html__( 'Ajouter des images', 'eac-components' ),
					'type'    => Controls_Manager::GALLERY,
					'default' => array(),
				)
			);

			$this->add_control(
				'kbs_galerie_attention',
				array(
					'type'            => Controls_Manager::RAW_HTML,
					'content_classes' => 'eac-editor-panel_warning',
					'raw'             => esc_html__( 'Les images doivent être de mêmes dimensions. Le container (Section - Largeur du contenu) doit être plus petit que les images.', 'eac-components' ),
				)
			);

		$this->end_controls_section();

		$this->start_controls_section(
			'kbs_slider_settings',
			array(
				'label' => esc_html__( 'Réglages', 'eac-components' ),
			)
		);

			$this->add_control(
				'kbs_slides_duree',
				array(
					'label'       => esc_html__( "Durée de l'effet (millisecondes)", 'eac-components' ),
					'type'        => Controls_Manager::NUMBER,
					'min'         => 2000,
					'max'         => 10000,
					'step'        => 1000,
					'default'     => 4000,
					'label_block' => false,
				)
			);

			$this->add_control(
				'kbs_slides_zoom',
				array(
					'label'       => esc_html__( 'Facteur de zoom', 'eac-components' ),
					'description' => esc_html__( 'Si les images se chevauchent, augmenter le facteur de zoom.', 'eac-components' ),
					'type'        => Controls_Manager::NUMBER,
					'min'         => 1,
					'max'         => 2,
					'step'        => 0.1,
					'default'     => 1.4,
					'label_block' => false,
				)
			);

			$this->add_control(
				'kbs_slides_ease',
				array(
					'label'       => esc_html__( 'Transition', 'eac-components' ),
					'description' => esc_html__( 'Vitesse de transition. Début/Fin', 'eac-components' ),
					'type'        => Controls_Manager::SELECT,
					'default'     => 'ease-in-out',
					'options'     => array(
						'linear'      => esc_html__( 'Linéaire', 'eac-components' ),
						'ease'        => esc_html__( 'Lente, rapide et lente', 'eac-components' ),
						'ease-in'     => esc_html__( 'Lente et rapide', 'eac-components' ),
						'ease-out'    => esc_html__( 'Rapide et lente', 'eac-components' ),
						'ease-in-out' => esc_html__( 'Lente et lente', 'eac-components' ),
					),
					'label_block' => false,
				)
			);

			$this->add_control(
				'kbs_slides_navigation',
				array(
					'label'        => esc_html__( 'Navigation', 'eac-components' ),
					'description'  => esc_html__( 'Afficher la navigation.', 'eac-components' ),
					'type'         => Controls_Manager::SWITCHER,
					'label_on'     => esc_html__( 'oui', 'eac-components' ),
					'label_off'    => esc_html__( 'non', 'eac-components' ),
					'return_value' => 'yes',
					'default'      => '',
				)
			);

			$this->add_control(
				'kbs_slides_caption',
				array(
					'label'        => esc_html__( 'Légende', 'eac-components' ),
					'description'  => esc_html__( "Attribut 'ALT' ou 'Légende' de l'image", 'eac-components' ),
					'type'         => Controls_Manager::SWITCHER,
					'label_on'     => esc_html__( 'oui', 'eac-components' ),
					'label_off'    => esc_html__( 'non', 'eac-components' ),
					'return_value' => 'yes',
					'default'      => '',
				)
			);

		$this->end_controls_section();

		/**
		 * Generale Style Section
		 */
		$this->start_controls_section(
			'kbs_general_style',
			array(
				'label' => esc_html__( 'Effets', 'eac-components' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);
			$this->add_control(
				'kbs_select_effect',
				array(
					'label'       => esc_html__( 'Sélection', 'eac-components' ),
					'description' => esc_html__( 'Très consommateur de ressources...', 'eac-components' ),
					'type'        => Controls_Manager::SELECT2,
					'multiple'    => true,
					'options'     => array(
						'panUp'                    => esc_html__( 'Panoramique haut', 'eac-components' ),
						'panDown'                  => esc_html__( 'Panoramique bas', 'eac-components' ),
						'panLeft'                  => esc_html__( 'Panoramique gauche', 'eac-components' ),
						'panRight'                 => esc_html__( 'Panoramique droit', 'eac-components' ),
						'zoomIn'                   => esc_html__( 'Zoom interne', 'eac-components' ),
						'zoomOut'                  => esc_html__( 'Zoom externe', 'eac-components' ),
						'diagTopLeftToBottomRight' => esc_html__( 'Bas Droit', 'eac-components' ),
						'diagTopRightToBottomLeft' => esc_html__( 'Bas Gauche', 'eac-components' ),
						'diagBottomRightToTopLeft' => esc_html__( 'Haut Gauche', 'eac-components' ),
						'diagBottomLeftToTopRight' => esc_html__( 'Haut Droit', 'eac-components' ),
					),
					'default'     => array( 'panUp' ),
					'label_block' => true,
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
		$settings = $this->get_settings_for_display();
		if ( ! $settings['kbs_galerie'] ) {
			return;
		}

		$id = 'kbs_slides_' . uniqid();
		$this->add_render_attribute( 'kbs_slide', 'class', 'kbs-slides' );
		$this->add_render_attribute( 'kbs_slide', 'id', $id );
		$this->add_render_attribute( 'kbs_slide', 'data-settings', $this->get_settings_json( $id ) );
		?>
		<div class="eac-kenburn-slider">
			<div <?php echo $this->get_render_attribute_string( 'kbs_slide' ); ?>>
				<?php $this->render_galerie(); ?>
			</div>
		</div>
		<?php
	}

	protected function render_galerie() {
		$settings = $this->get_settings_for_display();
		$html     = '';

		/**
		 * @since 1.9.8 L'image a été effacée
		 */
		foreach ( $settings['kbs_galerie'] as $image ) {
			$attachment = get_post( $image['id'] );
			$image_alt  = ! empty( $attachment->post_excerpt ) ? $attachment->post_excerpt : Control_Media::get_image_alt( $image );
			$image_data = wp_get_attachment_image_src( $image['id'], 'full' );
			if ( ! $image_data ) {
				continue;
			}
			$current_image = sprintf( '<img class="eac-image-loaded" src="%s" alt="%s" width="%d" height="%d" />', esc_url( $image_data[0] ), $image_alt, $image_data[1], $image_data[2] );
			$html         .= $current_image;
		}

		echo $html;
	}

	/**
	 * get_settings_json()
	 *
	 * Retrieve fields values to pass at the widget container
	 * Convert on JSON format
	 *
	 * @uses      json_encode()
	 *
	 * @return    JSON oject
	 *
	 * @access    protected
	 * @since     0.0.9
	 * @updated   1.0.7
	 */
	protected function get_settings_json( $dataid ) {
		$module_settings = $this->get_settings_for_display();

		// Les effets sélectionnés
		if ( empty( $module_settings['kbs_select_effect'] ) ) {
			$effets = 'panUp'; } else {
			$effets = implode( ',', $module_settings['kbs_select_effect'] ); }

			$settings = array(
				'effect'         => $effets,
				'data_id'        => $dataid,
				'effectDuration' => empty( $module_settings['kbs_slides_duree'] ) ? '6000' : $module_settings['kbs_slides_duree'],
				'effectModifier' => empty( $module_settings['kbs_slides_zoom'] ) ? '1.4' : $module_settings['kbs_slides_zoom'],
				'effectEasing'   => $module_settings['kbs_slides_ease'],
				'navigation'     => $module_settings['kbs_slides_navigation'] === 'yes' ? 'true' : 'false',
				'captions'       => $module_settings['kbs_slides_caption'] === 'yes' ? 'true' : 'false',
			);

			$settings = json_encode( $settings );
			return $settings;
	}

	protected function content_template() {}

}
