<?php
/**
 * Class: Image_Diaporama_Widget
 * Name: Diaporama d'arrière-plan
 * Slug: eac-addon-image-diaporama
 *
 * Description: Image_Diaporama_Widget affiche et anime 6 background images
 *
 * @since 1.0.0
 * @since 1.7.0 Active les Dynamic Tags pour les images
 * @since 1.8.7 Support des custom breakpoints
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
use Elementor\Utils;
use Elementor\Group_Control_Typography;
use Elementor\Core\Schemes\Typography;
use Elementor\Core\Schemes\Color;
use Elementor\Repeater;

class Image_Diaporama_Widget extends Widget_Base {

	/**
	 * Constructeur de la class Image_Diaporama_Widget
	 *
	 * Enregistre les scripts et les styles
	 *
	 * @since 1.9.0
	 */
	public function __construct( $data = array(), $args = null ) {
		parent::__construct( $data, $args );

		wp_register_style( 'eac-diaporama', EAC_Plugin::instance()->get_register_style_url( 'image-diaporama' ), array( 'eac' ), '1.0.0' );
	}

	/**
	 * Le nom de la clé du composant dans le fichier de configuration
	 *
	 * @var $slug
	 *
	 * @access private
	 */
	private $slug = 'image-diaporama';

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
	 * @return string Widget icon.
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
		return array( '' );
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
		return array( 'eac-diaporama' );
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
			'dia_galerie_settings',
			array(
				'label' => esc_html__( 'Galerie', 'eac-components' ),
			)
		);

			$this->add_control(
				'dia_item_attention',
				array(
					'type' => Controls_Manager::RAW_HTML,
					'raw'  => esc_html__( 'Attention. Six images, pas plus pas moins.', 'eac-components' ),
				)
			);

			$repeater = new Repeater();

			// @since 1.7.0
			$repeater->add_control(
				'dia_item_image',
				array(
					'label'   => esc_html__( 'Image', 'eac-components' ),
					'type'    => Controls_Manager::MEDIA,
					'dynamic' => array( 'active' => true ),
					'default' => array(
						'url' => Utils::get_placeholder_image_src(),
					),
				)
			);

			$repeater->add_control(
				'dia_item_title',
				array(
					'label'   => esc_html__( 'Titre', 'eac-components' ),
					'type'    => Controls_Manager::TEXT,
					'default' => esc_html__( 'Image #', 'eac-components' ),
				)
			);

			$this->add_control(
				'dia_image_list',
				array(
					'type'        => Controls_Manager::REPEATER,
					'fields'      => $repeater->get_controls(),
					'default'     => array(
						array(
							'dia_item_image' => array( 'url' => Utils::get_placeholder_image_src() ),
							'dia_item_title' => esc_html__( 'Sé-ré-nité', 'eac-components' ),
						),
						array(
							'dia_item_image' => array( 'url' => Utils::get_placeholder_image_src() ),
							'dia_item_title' => esc_html__( 'Équ-a-nimité', 'eac-components' ),
						),
						array(
							'dia_item_image' => array( 'url' => Utils::get_placeholder_image_src() ),
							'dia_item_title' => esc_html__( 'Rel-a-xation', 'eac-components' ),
						),
						array(
							'dia_item_image' => array( 'url' => Utils::get_placeholder_image_src() ),
							'dia_item_title' => esc_html__( 'Com-pos-ition', 'eac-components' ),
						),
						array(
							'dia_item_image' => array( 'url' => Utils::get_placeholder_image_src() ),
							'dia_item_title' => esc_html__( 'Qui-ét-ude', 'eac-components' ),
						),
						array(
							'dia_item_image' => array( 'url' => Utils::get_placeholder_image_src() ),
							'dia_item_title' => esc_html__( 'Tranq-uil-lité', 'eac-components' ),
						),
					),
					'title_field' => '{{{ dia_item_title }}}',
				)
			);

		$this->end_controls_section();

		$this->start_controls_section(
			'dia_settings',
			array(
				'label' => esc_html__( 'Réglages', 'eac-components' ),
			)
		);

			$this->add_control(
				'dia_image_overlay',
				array(
					'label'        => esc_html__( 'Cacher le calque', 'eac-components' ),
					'type'         => Controls_Manager::SWITCHER,
					'label_on'     => esc_html__( 'oui', 'eac-components' ),
					'label_off'    => esc_html__( 'non', 'eac-components' ),
					'return_value' => 'yes',
					'default'      => 'yes',
				)
			);

			/** @since 1.8.7 Application des breakpoints */
			$this->add_responsive_control(
				'dia_settings_height',
				array(
					'label'                => esc_html__( 'Hauteur min.', 'eac-components' ),
					'type'                 => Controls_Manager::SLIDER,
					'size_units'           => array( 'px' ),
					'default'              => array(
						'unit' => 'px',
						'size' => 450,
					),
					'tablet_default'       => array(
						'unit' => 'px',
						'size' => 300,
					),
					'mobile_default'       => array(
						'unit' => 'px',
						'size' => 200,
					),
					'tablet_extra_default' => array(
						'unit' => 'px',
						'size' => 300,
					),
					'mobile_extra_default' => array(
						'unit' => 'px',
						'size' => 200,
					),
					'range'                => array(
						'px' => array(
							'min'  => 150,
							'max'  => 1000,
							'step' => 50,
						),
					),
					'selectors'            => array( '{{WRAPPER}} .cb-slideshow, {{WRAPPER}} .cb-slideshow:after' => 'min-height: {{SIZE}}{{UNIT}};' ),
				)
			);

			/** @since 1.8.7 Application des breakpoints */
			$this->add_responsive_control(
				'dia_settings_position',
				array(
					'label'      => esc_html__( "Position verticale de l'image", 'eac-components' ),
					'type'       => Controls_Manager::SLIDER,
					'size_units' => array( '%' ),
					'default'    => array(
						'unit' => '%',
						'size' => 50,
					),
					'range'      => array(
						'%' => array(
							'min'  => 0,
							'max'  => 100,
							'step' => 5,
						),
					),
					'selectors'  => array( '{{WRAPPER}} .cb-slideshow div span' => 'background-position: 50% {{SIZE}}%;' ),
				)
			);

		$this->end_controls_section();

		/**
		 * Generale Style Section
		 */
		$this->start_controls_section(
			'dia_section_general_style',
			array(
				'label' => esc_html__( 'Global', 'eac-components' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

			$this->add_control(
				'dia_img_style',
				array(
					'label'   => esc_html__( 'Style', 'eac-components' ),
					'type'    => Controls_Manager::SELECT,
					'default' => 'style-1',
					'options' => array(
						'style-1' => 'Style 1',
						'style-2' => 'Style 2',
						'style-3' => 'Style 3',
						'style-4' => 'Style 4',
					),
				)
			);

		$this->end_controls_section();

		$this->start_controls_section(
			'dia_titre_section_style',
			array(
				'label' => esc_html__( 'Titre', 'eac-components' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

			$this->add_control(
				'dia_title_tag',
				array(
					'label'   => esc_html__( 'Étiquette', 'eac-components' ),
					'type'    => Controls_Manager::SELECT,
					'default' => 'h2',
					'options' => array(
						'h1' => 'H1',
						'h2' => 'H2',
						'h3' => 'H3',
						'h4' => 'H4',
						'h5' => 'H5',
						'h6' => 'H6',
					),
				)
			);

			$this->add_control(
				'dia_titre_color',
				array(
					'label'     => esc_html__( 'Couleur', 'eac-components' ),
					'type'      => Controls_Manager::COLOR,
					'scheme'    => array(
						'type'  => Color::get_type(),
						'value' => Color::COLOR_4,
					),
					'selectors' => array( '{{WRAPPER}} .cb-slideshow div div :first-child' => 'color: {{VALUE}};' ),
				)
			);

			$this->add_group_control(
				Group_Control_Typography::get_type(),
				array(
					'name'     => 'dia_titre_typography',
					'label'    => esc_html__( 'Typographie', 'eac-components' ),
					'scheme'   => Typography::TYPOGRAPHY_4,
					'selector' => '{{WRAPPER}} .cb-slideshow div div :first-child',
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
		$maxItems = 6; // Max 6 items

		if ( ! $settings['dia_image_list'] || count( $settings['dia_image_list'] ) != $maxItems ) {
			return;
		}

		$id      = 'image_diaporama_' . uniqid();
		$overlay = $settings['dia_image_overlay'] === 'yes' ? ' no-after' : '';
		$this->add_render_attribute( 'diaporama__instance', 'class', 'diaporama cb-slideshow cb-slideshow-' . $settings['dia_img_style'] . $overlay );
		$this->add_render_attribute( 'diaporama__instance', 'id', $id );
		$this->add_render_attribute( 'diaporama__instance', 'data-settings', $this->get_settings_json( $id ) );
		?>
		<div class="eac-image-diaporama">
			<div <?php echo $this->get_render_attribute_string( 'diaporama__instance' ); ?>>
				<?php $this->render_diaporama(); ?>
			</div>
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
	protected function render_diaporama() {
		$settings    = $this->get_settings_for_display();
		$title_tag   = $settings['dia_title_tag'];
		$open_title  = '<' . $title_tag . '>';
		$close_title = '</' . $title_tag . '>';
		$maxItems    = 6; // Max 6 items

		foreach ( $settings['dia_image_list'] as $key => $item ) {
			if ( ! empty( $item['dia_item_image']['url'] ) ) { // Il y a une image
				$src         = esc_url( $item['dia_item_image']['url'] );
				$title       = sanitize_text_field( $item['dia_item_title'] );
				$image_title = Control_Media::get_image_alt( $item['dia_item_image'] );
				?>
				<div title="<?php echo $image_title; ?>">
					<span style="background-image:url(<?php echo $src; ?>)"></span>
					<div><?php echo $open_title . $title . $close_title; ?></div>
				</div>
				<?php
			}
		}
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
	 * @since     1.0.0
	 * @updated   1.0.7
	 */

	protected function get_settings_json( $id ) {
		$module_settings = $this->get_settings_for_display();

		$settings = array(
			'data_id'    => $id,
			'data_items' => count( $module_settings['dia_image_list'] ),
			'data_style' => $module_settings['dia_img_style'],
		);

		$settings = json_encode( $settings );
		return $settings;
	}

	protected function content_template() {}

}
