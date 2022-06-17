<?php
	use \Export\SmartExport as Export;
	use \Plugin\SmartPlugin as Plugin;

	$progress = sprintf('%.2f%%', esc_html( Export::getProgress() ));
	$sph_config = Plugin::getConfig();
?>
<div id='smart-price-history'>
	<div class='plugin-info'>
		<strong>
			<?php esc_html_e( _x('Information', 'information', 'smart-price-history') ); ?>:
		</strong>
		<?php 
			esc_html_e( _x('The lowest price is displayed only when the product is on sale or promotion. Otherwise, the lowest price for the last month will not be displayed.', 'information', 'smart-price-history') . ' ' . __('The plugin collects information about the lowest price of the product from the last month and presents it on the Woocommerce product page. The history of the lowest price for a product is recorded since the plug-in is enabled and is held daily at midnight.', 'smart-price-history') );
		?>
	</div>
	<section class='overview'>
		<div class='header'>
			<?php esc_html_e( _x('Overwiev', 'status', 'smart-price-history') ); ?>
		</div>
		<div class='status'>
			<?php $export_status = Export::getStatus(); ?>

			<div class='field activity --label'>
				<?php _e('Current export status', 'smart-price-history'); ?>
			</div>
			<div class='field status --value'>
				<?php 
					if (
						$export_status->is_running
					){

						if (
							$export_status->is_dead
						){
							esc_html_e( _x('resuming', 'status', 'smart-price-history') );
						} 
						else {
							esc_html_e( _x('processing', 'status', 'smart-price-history') );
						} 
					} 
					elseif (
						$export_status->is_complete
					){
						esc_html_e( _x('completed', 'status', 'smart-price-history') );
					} 
					else {
						esc_html_e( _x('idle', 'status', 'smart-price-history') );
					} 
				?>
			</div>
			
		</div>
		<div class='progressbar'>
			<div class='bar'>
				<div class='progress' style='width:<?php echo $progress; ?>'>
					<div class='label'>
						<?php echo $progress; ?>
					</div>
				</div>
			</div>
		</div>
		<div class='run'>
			<?php
				global $wpdb;

				$last_export = date_create_from_format(
					'Y-m-d H:i:s'
					, $wpdb->get_var('SELECT MAX(date) FROM `smart_price_history`')
				);
			?>
			<div class='field --label'>
				<?php esc_html_e( _x('Last export time', 'schedule', 'smart-price-history') ); ?>:
			</div>
			<div class='field --value'>
				<?php 
					if ($last_export instanceof \DateTime){
						esc_html_e( wp_date('d F, H:i:s (e)', $last_export->getTimestamp()) );
					} 
					else {
						esc_html_e( _x('not launched yet', 'schedule', 'smart-price-history') );
					} 
				?>
			</div>
			<div class='field --label'>
				<?php esc_html_e( _x('Next scheduled export time', 'schedule', 'smart-price-history') ); ?>:
			</div>
			<div class='field --value'>
				<?php esc_html_e( wp_date('d F, H:i:s (e)', wp_next_scheduled( 'smart_price_history_export' )) ); ?>
			</div>
		</div>
	</section>
	<section class='presentation'>
		<div class='header'>
			<?php esc_html_e( _x('The price presentation style', 'presentation', 'smart-price-history') ); ?>
		</div>
		<form action="<?php menu_page_url( 'smart-price-history' ); ?>" method="post">
			<div class='input active'>
				<input 
					id='config_active' 
					type="checkbox" 
					name="sph_config[active]" 
					<?php esc_html_e( isset( $sph_config->active ) && ( $sph_config->active == 'on' )?( 'checked' ):( '' ) ); ?>
				>
				<label for="config_active">
					<?php esc_html_e( _x('enable price presentation', 'presentation', 'smart-price-history') ); ?>
				</label>
			</div>
			<div class='input size'>
				<input 
					id='config_price_size_custom' 
					type="checkbox" 
					name="sph_config[price_size_custom]" 
					<?php esc_html_e( isset( $sph_config->price_size_custom ) && $sph_config->price_size_custom == 'on'?( 'checked' ):( '' ) ); ?>
				>
				<label for="config_price_size_custom">
					<?php esc_html_e( _x('custom size price', 'presentation', 'smart-price-history') ); ?>
				</label>
				<input 
					id='config_price_size' 
					type="number" 
					min="0"
					step="0.01"
					name="sph_config[price_size]" 
					value="<?php esc_html_e( isset($sph_config->price_size)?($sph_config->price_size):('') ); ?>"
				>
				<select name='sph_config[price_size_unit]'>
					<?php
						$selected_unit = isset($sph_config->price_size_unit) ? ($sph_config->price_size_unit) : ('');
						$units = [
							'px',
							'pt',
							'em',
							'rem',
						];
					?>
					<?php foreach($units as $unit) : ?>
						<option value="<?php esc_html_e( $unit ); ?>" <?php esc_html_e( $unit == $selected_unit?('selected'):('') ); ?>>
							<?php esc_html_e( $unit ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class='input bold'>
				<input 
					id='config_price_bold' 
					type="checkbox" 
					name="sph_config[price_bold]" 
					<?php esc_html_e( isset($sph_config->price_bold) && $sph_config->price_bold == 'on'?('checked'):('') ); ?>
				>
				<label for="config_price_bold">
					<?php esc_html_e( _x('bold price', 'presentation', 'smart-price-history') ); ?>
				</label>
			</div>
			<div class='input color'>
				<input 
					id='config_price_color' 
					type="checkbox" 
					name="sph_config[color_custom]" 
					<?php esc_html_e( isset($sph_config->color_custom) && $sph_config->color_custom == 'on' ? ('checked') : ('') ); ?>
				>
				<label for="config_price_color">
					<?php esc_html_e( _x('color price', 'presentation', 'smart-price-history') ); ?>
				</label>
				<input 
				type="color" 
				name="sph_config[color][custom]" 
				value="<?php esc_html_e( isset($sph_config->color->custom)?($sph_config->color->custom):('#000') ); ?>"
				>
			</div>
			<div class='input bgcolor'>
				<input 
					id='config_price_bg_color' 
					type="checkbox" 
					name="sph_config[bg_color_custom]" 
					<?php esc_html_e( isset($sph_config->bg_color_custom) && ($sph_config->bg_color_custom == 'on') ? ('checked') : ('') ); ?>
				>
				<label for="config_price_bg_color">
					<?php esc_html_e( _x('background color price', 'presentation', 'smart-price-history') ); ?>
				</label>
				<input 
				type="color" 
				name="sph_config[bg_color][custom]" 
				value="<?php esc_html_e( isset($sph_config->bg_color->custom)?($sph_config->bg_color->custom):('#000') ); ?>"
				>
			</div>
			<div class='input price_text'>
				<input 
					id='config_price_text'
					type="text" name="sph_config[price_text]" 
					placeholder="<?php esc_html_e( _x('text displayed before price', 'presentation', 'smart-price-history') ); ?>"
					value="<?php esc_html_e( isset($sph_config->price_text)?($sph_config->price_text):('') ); ?>" 
				>
			</div>
			<?php wp_nonce_field('sph-config-form', 'sph_config_nonce'); ?>
			<input class='button button-primary' type="submit" value="<?php esc_html_e( __('save changes', 'smart-price-history') ); ?>">
		</form>
	</section>
</div>
