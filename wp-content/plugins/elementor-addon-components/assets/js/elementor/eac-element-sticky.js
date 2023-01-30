
/**
 * Description: Cette méthode est déclenchée lorsque la section 'eac_element_sticky_advanced' est chargée dans la page
 *
 * @param {selector} $scope. Le contenu de la section/colonne/widget
 * 
 * @since 1.8.1
 */
;(function($, elementor) {

	'use strict';

	var EacAddonsElementSticky = {

		init: function() {
			elementor.hooks.addAction('frontend/element_ready/section', EacAddonsElementSticky.elementSticky);
			elementor.hooks.addAction('frontend/element_ready/column', EacAddonsElementSticky.elementSticky);
			elementor.hooks.addAction('frontend/element_ready/widget', EacAddonsElementSticky.elementSticky);
			
			/** @since 1.9.5 */
			elementor.hooks.addAction('frontend/element_ready/container', EacAddonsElementSticky.elementSticky);
		},
		
		elementSticky: function($scope) {
			var configSticky = {
				$target: $scope,
				elType: $scope.data('element_type'),
				elId: $scope.data('id'),
				elTopSection: $scope.hasClass('elementor-top-section'),
				isEditMode: Boolean(elementor.isEditMode()),
				settings: $scope.data('eac_settings-sticky') || {}, /** @since 1.9.5 */
				isSticky: false,
				stickyClass: 'eac-element_sticky-class',
				stickyControlSwitcher: 'eac_element_sticky',
				stickyControlDevices: 'eac_element_sticky_devices',
				stickyControlUp: 'eac_element_sticky_up',
				stickyControlDown: 'eac_element_sticky_down',
				stickySettings: 'data-eac_settings-sticky',
				optionsObserve: {
					root: null,
					rootMargin: '0px',
					threshold: 0.92,							// Ratio du déclenchement de IntersectionObserver
				},
				topBarHeigth: '',//EacAddonsElementSticky.getTopOffset(),
				
				/**
				 * init
				 *
				 * @since 1.8.1
				 */
				init: function() {
					// Dans l'éditeur on construit l'attribut data settings
					if(this.isEditMode) {
						var sticky = this.getElementSettingInEditor(this.stickyControlSwitcher, this.$target) || 'no';
						// L'élément est sticky
						if(sticky === 'yes') {
							var element_settings = {
								"id": this.elId,
								"widget": this.elType,
								"sticky": sticky,
								"up": this.getElementSettingInEditor(this.stickyControlUp, this.$target) || 50,
								"down": this.getElementSettingInEditor(this.stickyControlDown, this.$target) || 50,
								"devices": this.getElementSettingInEditor(this.stickyControlDevices, this.$target) || ['desktop', 'tablet'],
							};
							
							// Affecte l'attribut au widget
							this.$target.attr(this.stickySettings, JSON.stringify(element_settings));
							// Et à l'objet
							this.settings = JSON.parse(this.$target.attr(this.stickySettings));
							this.isSticky = true;
						}
					} else {
						// Pas dans l'éditeur on check l'existence de l'attribut data-eac_settings-sticky et si l'élément est sticky
						if(Object.keys(this.settings).length > 0 && (typeof this.settings.sticky != 'undefined' && this.settings.sticky === 'yes')) {
							this.isSticky = true;
						}
					}
					
					/**
					 * Le mode sticky n'est pas sélectionné
					 * Supprime la class, les attributs du widget et le positionnement
					 */
					if(!this.isSticky) {
						if(this.$target.hasClass(this.stickyClass)) {
							this.$target.removeClass(this.stickyClass);
							this.$target.removeAttr(this.stickySettings);
							this.$target.css('top', '');
							this.$target.css('bottom', '');
							if(this.elType === 'column') {
								this.$target.css('align-self', '');
							}
						}
						return;
					}
					
					/**
					 *
					 * On applique la class et le css au widget
					 * Si le device courant n'est pas dans la liste des devices du widget
					 * ce sera géré dans le callback du ResizeObserver
					 *
					 */
					if(!this.elTopSection) {
						this.$target.addClass(this.stickyClass);
						this.$target.css('top', this.settings.up + 'px');
						this.$target.css('bottom', this.settings.down + 'px');
						
						// C'est une colonne
						if(this.elType === 'column') {
							this.$target.css('align-self', 'baseline');
						}
						
						// Gestion des événements 'resize' et 'orientationchange'
						var resizeObserver = new ResizeObserver(this.observeResizeViewport);
						resizeObserver.observe(this.$target[0]);
					} else {
						var intersectObserver = new IntersectionObserver(this.observeElementInViewport.bind(this), this.optionsObserve);
						intersectObserver.observe(this.$target[0]);
					}
				},
				
				/**
				 * getElementSettingInEditor
				 *
				 * Utile en mode édition
				 * L'attribut data-eac_settings-sticky n'est pas renseigné en mode édition
				 * action 'eac_render_sticky' dans 'eac-injection-sticky.php'
				 * On passe par les propriétés Elementor
				 *
				 * @since 1.8.1
				 */
				getElementSettingInEditor: function(controlValue, $target) {
					var attributs = {};
							
					if(!elementor.hasOwnProperty('config')) { return; }
					if(!elementor.config.hasOwnProperty('elements')) { return; }
					if(!elementor.config.elements.hasOwnProperty('data')) { return; }
					
					var modelCID = $target.data('model-cid');
					var editorElementData = elementor.config.elements.data[modelCID];
					if(!editorElementData) { return; }
					if(!editorElementData.hasOwnProperty('attributes')) { return; }
							
					attributs = editorElementData.attributes || {};
					if(!attributs[controlValue]) { return; }
					
					return attributs[controlValue];
				},
				
				/**
				 * observeElementInViewport
				 *
				 * callBack de IntersectionObserver déclenché par les options 'optionsObserve'
				 *
				 * @since 1.8.1
				 */
				observeElementInViewport: function(entries, observer) {
					//console.log('observeElementInViewport::'+entries[0].isIntersecting+"::"+entries[0].intersectionRatio);
					//if(entries[0].intersectionRatio <= 0.92) return;
					if(entries[0].isIntersecting) {
						var target = entries[0].target,
							settings = JSON.parse(target.getAttribute('data-eac_settings-sticky'));
						
						target.classList.add('eac-element_sticky-class');
						target.style.top = settings.up + 'px';
						target.style.bottom = settings.down + 'px';
						
						// Arrête l'observation
						observer.disconnect();
						
						// Gestion des événements 'resize' et 'orientationchange'
						var resizeObserver = new ResizeObserver(this.observeResizeViewport);
						resizeObserver.observe(target);
					}
				},
				
				/**
				 * observeResizeViewport
				 *
				 * callBack de ResizeObserver déclenché par les événements 'resize' et 'orientationchange'
				 *
				 * @since 1.8.1
				 */
				observeResizeViewport: function(entries) {
					var target = entries[0].target,
						contentRectWith = entries[0].contentRect.width,
						clientWidth = document.documentElement.clientWidth,
						currentDevice = elementor.getCurrentDeviceMode(),
						settings = JSON.parse(target.getAttribute('data-eac_settings-sticky'));
					
					if(settings === null || typeof settings === 'undefined') {
						return;
					}
					//console.log(settings.devices);
					//console.log(currentDevice);
					/** Le device courant est dans la liste des devices sélectionnés pour le widget */
					if(settings.devices.indexOf(currentDevice) > -1) {
						target.classList.add('eac-element_sticky-class');
						target.style.top = settings.up + 'px';
						target.style.bottom = settings.down + 'px';
						if(settings.widget === 'column') {
							target.style.alignSelf = 'baseline';
						}
					} else {
						if(target.classList.contains('eac-element_sticky-class')) {
							target.classList.remove('eac-element_sticky-class');
							target.style.top = '';
							target.style.bottom = '';
							if(settings.widget === 'column') {
								target.style.alignSelf = '';
							}
						}
					}
					//console.log('observeResize contentRectWith::'+contentRectWith+'::clientWidth:'+clientWidth);
					//console.log("==>"+JSON.stringify(settings)+"::Styles::"+target.style.cssText);
				},
			};
			
			configSticky.init();
		},
		
		
	
	};
	
	
	/**
	* Description: Cette méthode est déclenchée lorsque le frontend Elementor est initialisé
	*
	* @return (object) Initialise l'objet EacAddonsElementSticky
	* @since 0.0.9
	*/
	$(window).on('elementor/frontend/init', EacAddonsElementSticky.init);
	
}(jQuery, window.elementorFrontend));