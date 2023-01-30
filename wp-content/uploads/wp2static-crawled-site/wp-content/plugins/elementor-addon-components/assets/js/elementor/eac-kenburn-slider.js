
/**
 * Description: Cette méthode est déclenchée lorsque la section 'eac-addon-kenburn-slider' est chargée dans la page
 *
 * @param {selector} $scope. Le contenu de la section
 * @since 0.0.9
 */
;(function($, elementor) {

	'use strict';

	var EacAddonsKenBurnSlider = {

		init: function() {
			elementor.hooks.addAction('frontend/element_ready/eac-addon-kenburn-slider.default', EacAddonsKenBurnSlider.widgetKenBurnSlider);
		},
		
		widgetKenBurnSlider: function widgetKenBurnSlider($scope) {
			var $target = $scope.find('.kbs-slides'),
				$imagesInstance = $target.find('img'),
				settings = $target.data('settings'),
				KBOptions = {
					effectDuration:	6000,
					effectModifier:	1.4,
					effect:	'panUp',
					effectEasing: 'ease-in-out',
					captions: 'false',
					navigation: 'false',
				};
			
			if (! $target.length || ! settings) {
				return;
			}
			
			$.extend(KBOptions, settings);
			
			$imagesInstance.each(function() {
				$(this).attr('src', $(this).data('src'));
				if($(this).complete) {
					$(this).load();
				}
			});
			
			$target.imagesLoaded().progress(function(instance, image) {
				if(image.isLoaded) {
					$(image.img).addClass('kbs-image-loaded');
				}
			}).done(function() {
				// Création de l'objet de l'effet KB
				$('#' + settings.data_id).smoothSlides(KBOptions);
			});
		},
	};
	
	
	/**
	* Description: Cette méthode est déclenchée lorsque le frontend Elementor est initialisé
	*
	* @return (object) Initialise l'objet EacAddonsKenBurnSlider
	* @since 0.0.9
	*/
	$(window).on('elementor/frontend/init', EacAddonsKenBurnSlider.init);
	
}(jQuery, window.elementorFrontend));