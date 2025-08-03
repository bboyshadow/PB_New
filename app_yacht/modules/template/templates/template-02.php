<?php

if ( empty( $templateData['resultArray'] ) ) {
	error_log( 'Empty resultArray in default-template.php' );
	error_log( 'Template data: ' . print_r( $templateData, true ) );
}

$yachtArr = buildYachtInfoArray( $templateData['yachtInfo'] ?? array() );
$calcArr  = buildCalcSnippetArray(
	$templateData['resultArray'] ?? array(),
	$templateData['lowSeasonText'] ?? '',
	$templateData['highSeasonText'] ?? ''
);


if ( empty( $calcArr['structuredBlock'] ) ) {
	error_log( 'Empty structuredBlock in default-template.php' );
	error_log( 'Calc array: ' . print_r( $calcArr, true ) );
}

$block = $calcArr['structuredBlock'] ?? array();
?>
<!-- Template que debe copiarse con el botón Copy Template inicio -->
<div id="yachtInfoContainer" class="template-body-container" style="width: 100%; max-width: 700px; margin: 0 auto;">

	<!-- Header estático (se mantiene igual) -->
	<div class="template-header" style="background-color:#12323a;border-bottom:3px solid #dbe68b;border-top-left-radius: 13px; border-top-right-radius: 74px; border-bottom-right-radius: 5px; border-bottom-left-radius: 5px;text-align:center;color:#ffffff;font-family:Arial,sans-serif;font-size:11px;margin-bottom: 5px;">
		<table role="presentation" align="center" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width:900px;margin:0 auto;">
			<tr>
				<!-- Length -->
				<td width="25%" style="padding:8px 0;">
					<table border="0" cellpadding="0" cellspacing="0" align="center" style="margin:0 auto;">
						<tr>
							<td style="font-family:Arial,sans-serif;color:#ffffff;">
								<span style="display:inline-block;vertical-align:middle;">Length -</span>
								<span id="lengthInfo" style="display:inline-block;vertical-align:middle;padding-left:3px;"><?php echo htmlspecialchars( $yachtArr['length'] ?? '--' ); ?></span>
							</td>
						</tr>
					</table>
				</td>
				
				<!-- Type -->
				<td width="25%" style="padding:8px 0;">
					<table border="0" cellpadding="0" cellspacing="0" align="center" style="margin:0 auto;">
						<tr>
							<td style="font-family:Arial,sans-serif;color:#ffffff;">
								<span style="display:inline-block;vertical-align:middle;">Type -</span>
								<span style="display:inline-block;vertical-align:middle;padding-left:3px;"><?php echo htmlspecialchars( $yachtArr['type'] ?? '--' ); ?></span>
							</td>
						</tr>
					</table>
				</td>
				
				<!-- Builder -->
				<td width="25%" style="padding:8px 0;">
					<table border="0" cellpadding="0" cellspacing="0" align="center" style="margin:0 auto;">
						<tr>
							<td style="font-family:Arial,sans-serif;color:#ffffff;">
								<span style="display:inline-block;vertical-align:middle;">Builder -</span>
								<span id="builderInfo" style="display:inline-block;vertical-align:middle;padding-left:3px;"><?php echo htmlspecialchars( $yachtArr['builder'] ?? '--' ); ?></span>
							</td>
						</tr>
					</table>
				</td>
				
				<!-- Year Built -->
				<td width="25%" style="padding:8px 0;">
					<table border="0" cellpadding="0" cellspacing="0" align="center" style="margin:0 auto;">
						<tr>
							<td style="font-family:Arial,sans-serif;color:#ffffff;">
								<span style="display:inline-block;vertical-align:middle;">Year Built -</span>
								<span id="yearBuiltInfo" style="display:inline-block;vertical-align:middle;padding-left:3px;"><?php echo htmlspecialchars( $yachtArr['yearBuilt'] ?? '--' ); ?></span>
							</td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
	</div>

	<!-- Contenido principal con tablas -->
	<table width="100%" border="0" cellpadding="0" cellspacing="0" style="max-width: 700px; margin: 0 auto; padding: 10px 0;">
		<tbody style="vertical-align: top;">
			<tr>
				<!-- Columna Imagen -->
				<td align="left" valign="top" style="vertical-align: top;">
					<table role="presentation" width="100%" height="100%" border="0" cellpadding="0" cellspacing="0">
						<tr>
							<td valign="top" style="vertical-align: top;">
								<div id="imageSection" class="template-img-section" style="max-width: 395px; min-width: 300px; display: table-cell; vertical-align: top;">
									<?php if ( ! empty( $yachtArr['imageUrl'] ) ) : ?>
										<div style="color:#ffffff;border-top-left-radius: 6px; border-top-right-radius: 5px; border-bottom-right-radius: 50px; border-bottom-left-radius: 0px;font-size:20px;display:block;border-bottom: 4px solid #dbe68b;background: #12323a;">
											<a class="template-yacht-name" href="<?php echo htmlspecialchars( $yachtArr['yachtUrl'] ); ?>" id="yachtLink" style="color:#ffffff;font-weight:bold;text-decoration:none;">
												<div style="padding:3px 10px; background-color:#12323a; border-top-left-radius: 5px; border-top-right-radius: 5px; border-bottom-right-radius: 0px; border-bottom-left-radius: 0px;">
													<img class="logo-compani" src="https://www-worldwideboat-com.exactdn.com/wp-content/themes/worldwideboat/assets/img/new-primary-logo.png?strip=all&lossy=1&quality=92&ssl=1" alt="" style="display:block; width:150px; height:auto;">
												</div>
												<div style="">
													<img class="template-img" id="yachtImage" src="<?php echo htmlspecialchars( $yachtArr['imageUrl'] ); ?>" alt="Yacht" style="width:100%; display:block; border-top-left-radius: 0px; border-top-right-radius: 0px; border-bottom-right-radius: 70px; border-bottom-left-radius: 0px; position:relative; z-index:1;">
													<div style="padding:8px 15px;border-top-left-radius: 0px; border-top-right-radius: 0px; border-bottom-right-radius: 50px; border-bottom-left-radius: 0px;">
														<p style="margin:5px 0; line-height:0.3; text-align: center;"><?php echo htmlspecialchars( $yachtArr['yachtName'] ); ?></p>
													</div>
												</div>      
											</a>
										</div>
									<?php endif; ?>
										
									<div style="padding: 2px 14px;margin-top: 5px;border-bottom:3px solid #dbe68b;border-top-left-radius: 0px; border-top-right-radius: 63px; border-bottom-right-radius: 0px; border-bottom-left-radius: 2px;background-color: #12323a;color: #ffffff;font-size: 12px;max-width: 60%;">
										<table width="100%" border="0" cellpadding="0" cellspacing="0" style="border-spacing: initial;">
											<tr>
												<td><?php echo htmlspecialchars( 'Crew ' . ( $yachtArr['crew'] ?? '--' ) . ', ' ); ?></td>
												<td><?php echo htmlspecialchars( 'Cabins ' . ( $yachtArr['cabins'] ?? '--' ) . ', ' ); ?></td>
												<td><?php echo htmlspecialchars( 'Guest ' . ( $yachtArr['guest'] ?? '--' ) . ', ' ); ?></td>
											</tr>
										</table>
									</div>

									<table role="presentation" border="0" cellspacing="0" cellpadding="0" width="100%" style="background-color:#12323a; border-bottom: 3px solid #dbe68b; border-top-left-radius: 5px; border-top-right-radius: 59px; border-bottom-right-radius: 5px; border-bottom-left-radius: 82px; text-align:center; color:#ffffff; font-family:Arial,sans-serif; font-size: 11px;">
										<tr>
											<td style="padding:5px;">
											<?php
											$cabinConfig = isset( $yachtArr['cabinConfiguration'] ) ? str_replace( "\n", ', ', $yachtArr['cabinConfiguration'] ) : '--';
											echo htmlspecialchars( $cabinConfig );
											?>
											</td>
										</tr>
									</table>
								</div>
							</td>
						</tr>
					</table>    
				</td>

				<!-- Columna Contenedores de Costo -->
				<td align="left" valign="top" style="vertical-align: top;">
					<table role="presentation" width="100%" height="100%" border="0" cellpadding="0" cellspacing="0">
						<?php if ( ! empty( $templateData['resultArray'] ) ) : ?>
							<?php foreach ( $templateData['resultArray'] as $resultData ) : ?>
								<tr>
									<td valign="top" style="vertical-align: top;">
										<div class="template-charter-cost-container" style="border-radius: 4px; font-size: 12px; min-width: 300px; background: #f5faf0;">
											<!-- Sección de temporadas -->
											<?php if ( ! empty( $templateData['lowSeasonText'] ) && ! empty( $templateData['highSeasonText'] ) ) : ?>
												<table role="presentation" width="100%" border="0" cellpadding="0" cellspacing="0" style="border-spacing: 0; border-collapse: collapse;">
													<tr>
														<td style="padding: 0;">
															<table role="presentation" width="100%" border="0" cellpadding="0" cellspacing="0" style="border-spacing: 0; border-collapse: collapse; background-color: #12323a; border-radius: 3px; border-bottom: 2px solid #dbe68b;">
																<tr>
																	<td align="center" style="padding: 5px; color: #ffffff; font-family: Arial, sans-serif; font-size: 12px;">
																		<?php echo htmlspecialchars( $templateData['lowSeasonText'] ); ?>
																	</td>
																</tr>
															</table>
														</td>
													</tr>
													<tr>
														<td style="padding: 0;">
															<table role="presentation" width="100%" border="0" cellpadding="0" cellspacing="0" style="border-spacing: 0; border-collapse: collapse; background-color: #12323a; border-top-left-radius: 0px; border-top-right-radius: 0px; border-bottom-right-radius: 50px; border-bottom-left-radius: 0px; border-bottom: 5px solid #dbe68b;">
																<tr>
																	<td align="center" style="padding: 5px; color: #ffffff; font-family: Arial, sans-serif; font-size: 12px;">
																		<?php echo htmlspecialchars( $templateData['highSeasonText'] ); ?>
																	</td>
																</tr>
															</table>
														</td>
													</tr>
												</table>
											<?php endif; ?>
											<!-- Detalle principal -->
											<table role="presentation" width="100%" border="0" cellpadding="0" cellspacing="0" style="border-spacing: 0; border-collapse: collapse; background-color: #12323a; border-top-left-radius: 0px; border-top-right-radius: 14px; border-bottom-right-radius: 0px; border-bottom-left-radius: 25px; border-bottom: 5px solid #dbe68b;">
												<tr>
													<td style="padding: 5px 9px; font-weight: bold; color: #ffffff; font-family: Arial, sans-serif; font-size: 12px;">
														<table role="presentation" width="100%" border="0" cellpadding="0" cellspacing="0" style="border-spacing: 0; border-collapse: collapse;">
															<tr>
																<td align="left" valign="middle" style="padding: 0 9px; width: 60%; color: #ffffff;">
																	<?php if ( ! empty( $resultData['hours'] ) && $resultData['hours'] !== '--' ) : ?>
																		<?php echo htmlspecialchars( $resultData['hours'] ) . ' Hours, '; ?>
																	<?php else : ?>
																		<?php echo htmlspecialchars( $resultData['nights'] ) . ' Nights, '; ?>
																	<?php endif; ?>
																	<?php echo htmlspecialchars( $resultData['guests'] ) . ' Guests:'; ?>
																</td>
																<td align="right" valign="middle" style="text-align: right; color: #ffffff;">
																	€<?php echo htmlspecialchars( number_format( floatval( str_replace( array( ',', '€', ' ' ), '', $resultData['calculatedBaseRate'] ) ), 0, '.', ',' ) ); ?><?php echo ( isset( $templateData['enableExpenses'] ) && $templateData['enableExpenses'] ) ? ' + Expenses' : ''; ?>
																</td>
															</tr>
														</table>
													</td>
												</tr>
											</table>
											<!-- Descuento -->
											<?php 
											
											if ( ! empty( $resultData['discountType'] ) && 
												isset( $resultData['discountValue'] ) && $resultData['discountValue'] !== '--' && 
												isset( $resultData['discountedRate'] ) && $resultData['discountedRate'] !== '--' ) : 
												?>
												<table role="presentation" width="100%" border="0" cellpadding="0" cellspacing="0" 
													style="border-spacing: 0; border-collapse: collapse; padding: 2px 5px; color: #4b4f54; font-family: Arial, sans-serif; font-size: 12px;">
													<tr>
														<td align="left" valign="middle" style="padding: 5px; color: #4b4f54;">
															<?php if ( $resultData['discountType'] === 'percentage' ) : ?>
																Discount Rate (<?php echo htmlspecialchars( $resultData['discountAmount'] ); ?>%): -<?php echo htmlspecialchars( $resultData['discountValue'] ); ?> =
															<?php else : ?>
																Discount Rate - <?php echo htmlspecialchars( $resultData['discountAmount'] ); ?> =
															<?php endif; ?>
														</td>
														<td align="right" valign="middle" style="padding: 5px; text-align: right; color: #4b4f54;">
															<?php echo htmlspecialchars( $resultData['discountedRate'] ); ?>
														</td>
													</tr>
												</table>
											<?php endif; ?>


											<!-- VAT -->
											<?php
											if ( empty( $templateData['hideElements']['hideVAT'] ) && ! empty( $resultData['vatRateForDisplay'] ) && floatval( $resultData['vatRateForDisplay'] ) > 0
													&& ! empty( $resultData['vatDisplay'] ) 
													&& $resultData['vatDisplay'] !== '€ 0.00' ) :
												?>
												<table role="presentation" width="100%" border="0" cellpadding="0" cellspacing="0" 
													style="border-spacing: 0; border-collapse: collapse; padding: 2px 5px; color: #4b4f54; font-family: Arial, sans-serif; font-size: 12px;">
													<tr>
														<td align="left" valign="middle" style="padding: 5px; color: #4b4f54;">
															VAT (<?php echo htmlspecialchars( $resultData['vatRateForDisplay'] ); ?>%):
														</td>
														<td align="right" valign="middle" style="padding: 5px; text-align: right; color: #4b4f54;">
															€<?php echo htmlspecialchars( number_format( floatval( str_replace( array( ',', '€', ' ' ), '', $resultData['vatDisplay'] ) ), 0, '.', ',' ) ); ?>
														</td>
													</tr>
												</table>
											<?php endif; ?>


											<!-- APA % -->
											<?php
											if ( empty( $templateData['hideElements']['hideAPA'] ) && ! empty( $resultData['apaRateForDisplay'] ) && floatval( $resultData['apaRateForDisplay'] ) > 0
													&& ! empty( $resultData['apaPercDisplay'] ) 
													&& $resultData['apaPercDisplay'] !== '€ 0.00' ) :
												?>
												<table role="presentation" width="100%" border="0" cellpadding="0" cellspacing="0" 
													style="border-spacing: 0; border-collapse: collapse; padding: 2px 5px; color: #4b4f54; font-family: Arial, sans-serif; font-size: 12px;">
													<tr>
														<td align="left" valign="middle" style="padding: 5px; color: #4b4f54;">
															APA (<?php echo htmlspecialchars( $resultData['apaRateForDisplay'] ); ?>%):
														</td>
														<td align="right" valign="middle" style="padding: 5px; text-align: right; color: #4b4f54;">
															€<?php echo htmlspecialchars( number_format( floatval( str_replace( array( ',', '€', ' ' ), '', $resultData['apaPercDisplay'] ) ), 0, '.', ',' ) ); ?>
														</td>
													</tr>
												</table>
											<?php endif; ?>


											<!-- APA Fijo -->
											<?php
											if ( empty( $templateData['hideElements']['hideAPA'] ) && ! empty( $resultData['apaAmountDisplay'] ) 
													&& $resultData['apaAmountDisplay'] !== '--' 
													&& $resultData['apaAmountDisplay'] !== '€ 0.00' ) :
												?>
												<table role="presentation" width="100%" border="0" cellpadding="0" cellspacing="0"
													style="border-spacing: 0; border-collapse: collapse; padding: 2px 5px; color: #4b4f54; font-family: Arial, sans-serif; font-size: 12px;">
													<tr>
														<td align="left" valign="middle" style="padding: 5px; color: #4b4f54;">
															APA (Fixed Amount):
														</td>
														<td align="right" valign="middle" style="padding: 5px; text-align: right; color: #4b4f54;">
															€<?php echo htmlspecialchars( number_format( floatval( str_replace( array( ',', '€', ' ' ), '', $resultData['apaAmountDisplay'] ) ), 0, '.', ',' ) ); ?>
														</td>
													</tr>
												</table>
											<?php endif; ?>


											<!-- Relocation Fee -->
											<?php
											if ( empty( $templateData['hideElements']['hideRelocation'] ) && ! empty( $resultData['relocationDisplay'] ) 
													&& $resultData['relocationDisplay'] !== '€ 0.00' ) :
												?>
												<table role="presentation" width="100%" border="0" cellpadding="0" cellspacing="0"
													style="border-spacing: 0; border-collapse: collapse; padding: 2px 5px; color: #4b4f54; font-family: Arial, sans-serif; font-size: 12px;">
													<tr>
														<td align="left" valign="middle" style="padding: 5px; color: #4b4f54;">
															Relocation fee:
														</td>
														<td align="right" valign="middle" style="padding: 5px; text-align: right; color: #4b4f54;">
															€<?php echo htmlspecialchars( number_format( floatval( str_replace( array( ',', '€', ' ' ), '', $resultData['relocationDisplay'] ) ), 0, '.', ',' ) ); ?>
														</td>
													</tr>
												</table>
											<?php endif; ?>


											<!-- Security Deposit -->
											<?php
											if ( empty( $templateData['hideElements']['hideSecurity'] ) && ! empty( $resultData['securityDisplay'] ) 
													&& $resultData['securityDisplay'] !== '€ 0.00' ) :
												?>
												<table role="presentation" width="100%" border="0" cellpadding="0" cellspacing="0"
													style="border-spacing: 0; border-collapse: collapse; padding: 2px 5px; color: #4b4f54; font-family: Arial, sans-serif; font-size: 12px;">
													<tr>
														<td align="left" valign="middle" style="padding: 5px; color: #4b4f54;">
															Security deposit:
														</td>
														<td align="right" valign="middle" style="padding: 5px; text-align: right; color: #4b4f54;">
															€<?php echo htmlspecialchars( number_format( floatval( str_replace( array( ',', '€', ' ' ), '', $resultData['securityDisplay'] ) ), 0, '.', ',' ) ); ?>
														</td>
													</tr>
												</table>
											<?php endif; ?>

											<!-- Subtotal -->
											<?php if ( isset( $resultData['subtotal'] ) ) : ?>
												<table role="presentation" width="100%" border="0" cellpadding="0" cellspacing="0" style="border-spacing: 0; border-collapse: collapse; background-color: #12323a; border-bottom: 5px solid #dbe68b; color: #ffffff; font-family: Arial, sans-serif; font-size: 12px; border-radius: 14px 0 25px 0;">
													<tr>
														<td align="left" valign="middle" style="padding: 10px 15px; font-weight: bold;">
															Subtotal for charter:
														</td>
														<td align="right" valign="middle" style="padding: 10px 15px; text-align: right; font-weight: bold;">
															€<?php echo htmlspecialchars( number_format( floatval( str_replace( array( ',', '€', ' ' ), '', $resultData['subtotal'] ) ), 0, '.', ',' ) ); ?>
														</td>
													</tr>
												</table>
											<?php endif; ?>


											<!-- Extras -->
											<?php if ( empty( $templateData['hideElements']['hideExtras'] ) && ! empty( $resultData['extras'] ) ) : ?>
												<table role="presentation" width="100%" border="0" cellpadding="0" cellspacing="0"
													style="border-spacing: 0; border-collapse: collapse; background-color:#12323a; border-bottom: 5px solid #dbe68b; color:#ffffff; font-family: Arial, sans-serif; font-size: 12px; border-radius: 0 14px 0 25px;">
													<tr>
														<td align="left" style="padding: 10px 20px; font-weight: bold;">
															Extras:
														</td>
													</tr>
												</table>

												<?php 
												$hasExtras = false;
												foreach ( $resultData['extras'] as $extra ) : 
													
													if ( empty( $extra['name'] ) || empty( $extra['cost'] ) ) {
														continue;
													}
													$hasExtras = true;
													?>
													<table role="presentation" width="100%" border="0" cellpadding="0" cellspacing="0"
														style="border-spacing: 0; border-collapse: collapse; background-color:#f5faf0; color:#4b4f54; font-family: Arial, sans-serif; font-size: 12px;">
														<tr>
															<td align="left" style="padding: 5px 10px;">
																<?php echo htmlspecialchars( $extra['name'] ); ?>
															</td>
															<td align="right" style="padding: 5px 10px; text-align: right;">
																€<?php echo htmlspecialchars( number_format( floatval( str_replace( array( ',', '€', ' ' ), '', $extra['cost'] ) ), 0, '.', ',' ) ); ?>
															</td>
														</tr>
													</table>
												<?php endforeach; ?>

												<?php if ( $hasExtras && isset( $resultData['grandTotal'] ) ) : ?>
													<!-- Grand Total (when extras are shown) -->
													<table role="presentation" width="100%" border="0" cellpadding="0" cellspacing="0"
														style="border-spacing: 0; border-collapse: collapse; background-color:#12323a; border-bottom: 5px solid #dbe68b; color:#ffffff; font-family: Arial, sans-serif; font-size: 12px; border-radius: 50px 0 50px 0;">
														<tr>
															<td align="left" style="padding: 10px 20px; font-weight: bold;">
																Grand Total:
															</td>
															<td align="right" style="padding: 10px 20px; text-align: right;">
																€<?php echo htmlspecialchars( number_format( floatval( str_replace( array( ',', '€', ' ' ), '', $resultData['grandTotal'] ) ), 0, '.', ',' ) ); ?>
															</td>
														</tr>
													</table>
												<?php endif; ?>
											<?php elseif ( isset( $resultData['grandTotal'] ) ) : ?>
												<!-- Grand Total (when extras are hidden) -->
												<table role="presentation" width="100%" border="0" cellpadding="0" cellspacing="0"
													style="border-spacing: 0; border-collapse: collapse; background-color:#12323a; border-bottom: 5px solid #dbe68b; color:#ffffff; font-family: Arial, sans-serif; font-size: 12px; border-radius: 50px 0 50px 0;">
													<tr>
														<td align="left" style="padding: 10px 20px; font-weight: bold;">
															Grand Total:
														</td>
														<td align="right" style="padding: 10px 20px; text-align: right;">
															<?php echo htmlspecialchars( $resultData['grandTotal'] ); ?>
														</td>
													</tr>
												</table>
											<?php endif; ?>


											<!-- Gratuity -->
											<?php if ( empty( $templateData['hideElements']['hideGratuity'] ) && ! empty( $resultData['gratuityRates'] ) ) : ?>
												<table role="presentation" width="100%" border="0" cellpadding="0" cellspacing="0"
													style="border-spacing: 0; border-collapse: collapse; background-color:#f5faf0; color:#4b4f54; font-family: Arial, sans-serif; font-size: 12px;">
													<?php foreach ( $resultData['gratuityRates'] as $gratuity ) : ?>
														<tr>
															<td align="left" style="padding: 5px 10px;">
																Suggested gratuity (<?php echo htmlspecialchars( $gratuity['rate'] ); ?>%):
															</td>
															<td align="right" style="padding: 5px 10px; text-align: right;">
																€<?php echo htmlspecialchars( number_format( floatval( str_replace( array( ',', '€', ' ' ), '', $gratuity['amount'] ) ), 0, '.', ',' ) ); ?>
															</td>
														</tr>
													<?php endforeach; ?>
												</table>
											<?php endif; ?>

											
										</div>
									</td>
								</tr>
							<?php endforeach; ?>
						<?php else : ?>
							<tr>
								<td style="color: #4b4f54; padding: 10px;">No calculator results available.</td>
							</tr>
						<?php endif; ?>
					</table>
				</td>

			</tr>
		</tbody>
	</table>
</div>
<!-- Template que debe copiarse con el botón Copy Template fin -->
