<?php
	/* example stdClass Object
		(
			[price_bold] => on
			[color_custom] => on
			[color] => stdClass Object
				(
					[custom] => #e0ed2c
				)

			[price_text] => Najniższa cena z ostatniego miesiąca
		) 
	*/

	$sph_config = json_decode ( get_option ( 'sph_config', '{}' ) );

	if ( $sph_config->active == 'on' ):
		$price_classess = [];
		$price_styles   = [];
		$box_styles     = [];
		
		if ( $sph_config->price_bold ) $price_classess[]               = '--bold';
		if ( $sph_config->color_custom ) $price_styles[]               = "color:{$sph_config->color->custom}";
		if ( $sph_config->price_size_custom == 'on' ) $price_styles[]  = "font-size:{$sph_config->price_size}{$sph_config->price_size_unit}";
		if ( $sph_config->bg_color_custom ) $box_styles[]              = "background-color:{$sph_config->bg_color->custom}";
	?>
		<div 
			id='smart-price-history-info'
			style='<?php echo implode ( ';', $box_styles ); ?>'
		>
			<div class='label'>
				<?php echo $sph_config? ( $sph_config->price_text ): ( '' ); ?>
			</div>
			<div 
				class='value <?php echo implode ( ' ', $price_classess ); ?>'
				style='<?php echo implode ( ';', $price_styles ); ?>'
			>
				<?php 
					printf (
						get_woocommerce_price_format ( )
						, get_woocommerce_currency_symbol ( )
						, number_format (
							sph_get_lowest_price ( )
							, wc_get_price_decimals ( )
							, wc_get_price_decimal_separator ( )
							, wc_get_price_thousand_separator ( )
						)
					);
				?>
			</div>
		</div>
	<?php endif; ?>
