<?php
/**
 * Class: Eac_Injection_Widget_Sticky
 *
 * Description: Injecte la section et les controls dans les sections/Colonnes/Widgets
 * après la section 'Motion effects' sous l'onglet 'Advanced'
 *
 * @since 1.8.1
 * @since 1.8.7 Support des custom breakpoints
 * @since 1.8.9 Changer le nom de la section
 * @since 1.9.5 Changer le nom de la class
 */

namespace EACCustomWidgets\Includes\Elementor\Injection;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Version PRO Elementor, on sort
if ( defined( 'ELEMENTOR_PRO_VERSION' ) ) {
	return;
}

use EACCustomWidgets\EAC_Plugin;
use Elementor\Controls_Manager;
use Elementor\Element_Base;
use Elementor\Plugin;

class Eac_Injection_Widget_Sticky {

	/**
	 * @var $active_breakpoints
	 *
	 * La liste des breakpoints actifs
	 *
	 * @since 1.8.7
	 */
	private $active_breakpoints = array();

	/**
	 * @var $active_devices
	 *
	 * La liste ordonnée des breakpoints actifs
	 *
	 * @since 1.8.7
	 */
	private $active_devices = array();

	/**
	 * @var $device_options
	 *
	 * La liste des breakpoints actifs pour les options du control
	 * $device_options[$device] = $label;
	 *
	 * @since 1.8.7
	 */
	private $device_options = array();

	/**
	 * @var $target_elements
	 *
	 * La liste des éléments cibles
	 *
	 * @since 1.9.5
	 */
	private $target_elements = array( 'container', 'widget', 'column', 'section' );

	/**
	 * Constructeur de la class
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'elementor/element/after_section_end', array( $this, 'inject_section' ), 10, 3 );

		add_action( 'elementor/frontend/section/before_render', array( $this, 'eac_render_sticky' ) );
		add_action( 'elementor/frontend/column/before_render', array( $this, 'eac_render_sticky' ) );
		add_action( 'elementor/frontend/widget/before_render', array( $this, 'eac_render_sticky' ) );
		/** @since 1.9.5 */
		add_action( 'elementor/frontend/container/before_render', array( $this, 'eac_render_sticky' ) );

		add_action( 'elementor/frontend/before_enqueue_scripts', array( $this, 'eac_enqueue_scripts' ) );
	}

	/**
	 * eac_enqueue_scripts
	 *
	 * Mets le script dans le file
	 *
	 *  @since 1.8.1
	 */
	public function eac_enqueue_scripts() {
		wp_enqueue_script( 'eac-element-sticky', EAC_Plugin::instance()->get_register_script_url( 'eac-element-sticky' ), array( 'jquery', 'elementor-frontend' ), '1.8.1', true );
	}

	/**
	 * inject_section
	 *
	 * Inject le control après la section 'section_effects' Advanced tab
	 * pour les sections et widgets
	 *
	 * @param Element_Base $element    The edited element.
	 * @param String       $section_id L'ID de la section
	 * @param array        $args       Section arguments.
	 * @since 1.8.1
	 * @since 1.8.7         Custom breakpoints
	 */
	public function inject_section( $element, $section_id, $args ) {

		if ( ! $element instanceof Element_Base ) {
			return;
		}

		if ( 'section_effects' === $section_id && in_array( $element->get_type(), $this->target_elements ) ) {

			/**
			 * @since 1.8.7 Application des breakpoints
			 */
			// Les breakpoints actifs
			$this->active_breakpoints = Plugin::$instance->breakpoints->get_active_breakpoints();

			if ( version_compare( ELEMENTOR_VERSION, '3.4.0', '>=' ) ) {
				// Les arguments pour ajouter le device 'desktop'
				$args = array(
					'add_desktop' => true,
					'reverse'     => true,
				);

				// La liste des devices
				$this->active_devices = Plugin::$instance->breakpoints->get_active_devices_list( $args );
			} else {
				// Devices need to be ordered from largest to smallest.
				$this->active_devices = array_reverse( array_keys( $this->active_breakpoints ) );

				// Add desktop in the correct position.
				if ( in_array( 'widescreen', $this->active_devices, true ) ) {
					$this->active_devices = array_merge( array_slice( $this->active_devices, 0, 1 ), array( 'desktop' ), array_slice( $this->active_devices, 1 ) );
				} else {
					$this->active_devices = array_merge( array( 'desktop' ), $this->active_devices );
				}
			}

			// Les options du control
			foreach ( $this->active_devices as $device ) {
				$label                           = 'desktop' === $device ? esc_html__( 'Desktop', 'eac-components' ) : $this->active_breakpoints[ $device ]->get_label();
				$this->device_options[ $device ] = $label;
			}

			$element->start_controls_section(
				'eac_custom_element_sticky', // @since 1.8.9 'eac_element_sticky_advanced'
				array(
					'label' => esc_html__( 'EAC effet sticky', 'eac-components' ),
					'tab'   => Controls_Manager::TAB_ADVANCED,
				)
			);

				$element->add_control(
					'eac_element_sticky',
					array(
						'label'        => esc_html__( "Activer l'effect sticky", 'eac-components' ),
						'type'         => Controls_Manager::SWITCHER,
						'label_on'     => esc_html__( 'oui', 'eac-components' ),
						'label_off'    => esc_html__( 'non', 'eac-components' ),
						'return_value' => 'yes',
						'default'      => '',
					)
				);

				$element->add_control(
					'eac_element_sticky_warning',
					array(
						'type'            => Controls_Manager::RAW_HTML,
						'content_classes' => 'elementor-panel-alert elementor-panel-alert-warning',
						'raw'             => esc_html__( "La zone de déplacement est à l'intérieur du conteneur parent. Section: viewport. Colonne & Widget: section.", 'eac-components' ),
						'condition'       => array( 'eac_element_sticky' => 'yes' ),
					)
				);

				/** @since 1.8.7 Application des breakpoints */
				$element->add_control(
					'eac_element_sticky_devices',
					array(
						'label'       => esc_html__( 'Actif avec', 'eac-components' ),
						'type'        => Controls_Manager::SELECT2,
						'multiple'    => true,
						'label_block' => true,
						'default'     => $this->active_devices,
						'options'     => $this->device_options,
						'condition'   => array( 'eac_element_sticky' => 'yes' ),
					)
				);

				$element->add_control(
					'eac_element_sticky_up',
					array(
						'label'     => esc_html__( 'Seuil supérieur de déclenchement', 'eac-components' ),
						'type'      => Controls_Manager::NUMBER,
						'min'       => 0,
						'max'       => 500,
						'step'      => 10,
						'default'   => 50,
						'condition' => array( 'eac_element_sticky' => 'yes' ),
					)
				);

				$element->add_control(
					'eac_element_sticky_down',
					array(
						'label'     => esc_html__( 'Seuil inférieur de déclenchement', 'eac-components' ),
						'type'      => Controls_Manager::NUMBER,
						'min'       => 0,
						'max'       => 500,
						'step'      => 10,
						'default'   => 50,
						'condition' => array( 'eac_element_sticky' => 'yes' ),
					)
				);

				$element->add_control(
					'eac_element_sticky_zindex',
					array(
						'label'     => esc_html__( "Ordre de l'élément (z-index)", 'eac-components' ),
						'type'      => Controls_Manager::NUMBER,
						'min'       => 0,
						'max'       => 10000,
						'step'      => 1,
						'default'   => 9900,
						'condition' => array( 'eac_element_sticky' => 'yes' ),
						'selectors' => array( '{{WRAPPER}}' => 'z-index: {{VALUE}};' ),
					)
				);

			$element->end_controls_section();
		}
	}

	/**
	 * eac_render_sticky
	 *
	 * Ajoute la class et les propriétés dans l'objet avant le rendu
	 *
	 * Les propriétés de la class 'eac-element_sticky-class' notamment la propriété 'position'
	 * sont enregistrées dans le fichier 'eac-components.css'
	 *
	 * @param $element  Element_Base
	 * @since 1.8.1
	 * @since 1.9.5 Changement du nom de la class
	 */
	public function eac_render_sticky( $element ) {
		$settings = $element->get_settings_for_display();

		if ( ! in_array( $element->get_type(), $this->target_elements ) ) {
			return;
		}

		// Le control existe et il est renseigné
		if ( isset( $settings['eac_element_sticky'] ) && 'yes' === $settings['eac_element_sticky'] ) {

			$element_settings = array(
				'id'      => $element->get_data( 'id' ),
				'widget'  => $element->get_name(),
				'sticky'  => $settings['eac_element_sticky'],
				'up'      => isset( $settings['eac_element_sticky_up'] ) ? $settings['eac_element_sticky_up'] : 50,
				'down'    => isset( $settings['eac_element_sticky_down'] ) ? $settings['eac_element_sticky_down'] : 50,
				'devices' => isset( $settings['eac_element_sticky_devices'] ) ? $settings['eac_element_sticky_devices'] : array( 'desktop' ),
			);

			// Elementor utilise data-settings dans les sections
			$element->add_render_attribute(
				'_wrapper',
				array(
					'class'                    => 'eac-element_sticky-class',
					'data-eac_settings-sticky' => json_encode( $element_settings ),
				)
			);
		}
	}
}
new Eac_Injection_Widget_Sticky();
