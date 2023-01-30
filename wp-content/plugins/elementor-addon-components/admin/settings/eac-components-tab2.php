<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
use EACCustomWidgets\Core\Eac_Config_Elements;
?>
<form action="" method="POST" id="eac-form-features" name="eac-form-features">
	<div id="tab-3" style="display: none;">
		<div class="eac-settings-tabs">
			<div class="eac-elements__table-common">
				<?php
				ob_start();
				foreach ( Eac_Config_Elements::get_features_advanced_active() as $key => $active ) {
					if ( ! Eac_Config_Elements::are_feature_dependencies_enabled( $key ) ) {
						continue;
					}

					$title         = Eac_Config_Elements::get_feature_title( $key );
					$href          = Eac_Config_Elements::get_feature_help_url( $key );
					$href_class    = Eac_Config_Elements::get_feature_help_url_class( $key );
					$class_wrapper = 'eac-elements__common-item features advanced' . Eac_Config_Elements::get_feature_badge_class( $key );

					if ( 'all-features-advanced' === $key ) {
						?>
						<div class="eac-elements__table-common header">
							<div class="eac-elements__common-item header">
								<span class="eac-elements__item-content header"><?php echo $title; ?></span>
								<span>
									<label class="switch">
										<input type="checkbox" class="ios-switch bigswitch" id="<?php echo $key; ?>" name="<?php echo $key; ?>" <?php checked( 1, $active, true ); ?>>
										<div><div></div></div>
									</label>
								</span>
							</div>
						</div>
						<?php
					} else {
						?>
						<div class="<?php echo $class_wrapper; ?>">
							<span class="eac-elements__item-content"><?php echo $title; ?>
								<?php if ( ! empty( $href ) ) : ?>
									<a href="<?php echo $href; ?>" target="_blank" rel="noopener noreferrer">
										<span class="<?php echo $href_class; ?>"></span>
									</a>
								<?php endif; ?>
							</span>
							<span>
								<label class="switch">
									<input type="checkbox" class="ios-switch bigswitch" id="<?php echo $key; ?>" name="<?php echo $key; ?>" <?php checked( 1, $active, true ); ?>>
									<div><div></div></div>
								</label>
							</span>
						</div>
						<?php
					}
				}
				$output = ob_get_clean();
				echo $output;
				?>
			</div> <!-- Table common -->
		</div> <!-- Settings TAB -->
	</div> <!-- TAB 3-->
	
	<div id="tab-4" style="display: none;">
		<div class="eac-settings-tabs">
			<div class="eac-elements__table-common">
				<?php
				ob_start();
				foreach ( Eac_Config_Elements::get_features_common_active() as $key => $active ) {
					if ( ! Eac_Config_Elements::are_feature_dependencies_enabled( $key ) ) {
						continue;
					}

					$title         = Eac_Config_Elements::get_feature_title( $key );
					$href          = Eac_Config_Elements::get_feature_help_url( $key );
					$href_class    = Eac_Config_Elements::get_feature_help_url_class( $key );
					$class_wrapper = 'eac-elements__common-item features common' . Eac_Config_Elements::get_feature_badge_class( $key );

					if ( 'all-features-common' === $key ) {
						?>
						<div class="eac-elements__table-common header">
							<div class="eac-elements__common-item header">
								<span class="eac-elements__item-content header"><?php echo $title; ?></span>
								<span>
									<label class="switch">
										<input type="checkbox" class="ios-switch bigswitch" id="<?php echo $key; ?>" name="<?php echo $key; ?>" <?php checked( 1, $active, true ); ?>>
										<div><div></div></div>
									</label>
								</span>
							</div>
						</div>
						<?php
					} else {
						?>
						<div class="<?php echo $class_wrapper; ?>">
							<span class="eac-elements__item-content"><?php echo $title; ?>
								<?php if ( ! empty( $href ) ) : ?>
									<a href="<?php echo $href; ?>" target="_blank" rel="noopener noreferrer">
										<span class="<?php echo $href_class; ?>"></span>
									</a>
								<?php endif; ?>
							</span>
							<span>
								<label class="switch">
									<input type="checkbox" class="ios-switch bigswitch" id="<?php echo $key; ?>" name="<?php echo $key; ?>" <?php checked( 1, $active, true ); ?>>
									<div><div></div></div>
								</label>
							</span>
						</div>
						<?php
					}
				}
				$output = ob_get_clean();
				echo $output;
				?>
			</div> <!-- Table common -->
		</div> <!-- Settings TAB -->
	</div> <!-- TAB 4-->
	
	<div class="eac-saving-box">
		<input id="eac-sumit" type="submit" value="<?php esc_html_e( 'Enregistrer les modifications', 'eac-components' ); ?>">
		<div id="eac-features-saved"></div>
		<div id="eac-features-notsaved"></div>
	</div>
</form>
