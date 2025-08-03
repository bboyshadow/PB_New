<?php
/**
 * Default Email Template (Main Version).
 *
 * This template generates the main HTML email body for yacht charter details.
 * It receives data via the $templateData variable, processes it using helper functions,
 * and populates the HTML structure.
 *
 * @package App_Yacht
 * @subpackage Modules
 * @since 1.0.0
 *
 * @uses buildYachtInfoArray() To structure yacht details.
 * @uses buildCalcSnippetArray() To structure calculation results.
 *
 * @var array $templateData Associative array containing all necessary data:
 *        'yachtInfo'      => (array) Yacht details.
 *        'resultArray'    => (array) Calculation results.
 *        'lowSeasonText'  => (string) Text for low season.
 *        'highSeasonText' => (string) Text for high season.
 */

// Verify received data
if (empty($templateData['resultArray'])) {
    error_log('Empty resultArray in default-template.php');
    error_log('Template data: ' . print_r($templateData, true));
}

// Prepare data arrays using helper functions
$yachtArr = buildYachtInfoArray($templateData['yachtInfo'] ?? []);
$calcArr  = buildCalcSnippetArray(
    $templateData['resultArray'] ?? [],
    $templateData['lowSeasonText'] ?? '',
    $templateData['highSeasonText'] ?? ''
);

// Verify processed data
if (empty($calcArr['structuredBlock'])) {
    error_log('Empty structuredBlock in default-template.php');
    error_log('Calc array: ' . print_r($calcArr, true));
}

// Extract the main structured block for easier access in the template
$block = $calcArr['structuredBlock'] ?? [];
?>
<!-- Template que debe copiarse con el botón Copy Template inicio -->
<div id="yachtInfoContainer" class="template-body-container" style="width: 100%; max-width: 700px; margin: 0 auto;">

    <!-- Header estático (se mantiene igual) -->
    <div class="template-header" style="background-color:#4092df;border-bottom:3px solid #ffbe28;border-top-left-radius: 13px; border-top-right-radius: 74px; border-bottom-right-radius: 5px; border-bottom-left-radius: 5px;text-align:center;color:#ffffff;font-family:Arial,sans-serif;font-size:11px;margin-bottom: 5px;">
        <table role="presentation" align="center" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width:900px;margin:0 auto;">
            <tr>
                <!-- Length -->
                <td width="25%" style="padding:8px 0;">
                    <table border="0" cellpadding="0" cellspacing="0" align="center" style="margin:0 auto;">
                        <tr>
                            <td style="font-family:Arial,sans-serif;color:#ffffff;">
                                <span style="display:inline-block;vertical-align:middle;">Length -</span>
                                <span id="lengthInfo" style="display:inline-block;vertical-align:middle;padding-left:3px;"><?php echo htmlspecialchars($yachtArr['length'] ?? '--'); ?></span>
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
                                <span style="display:inline-block;vertical-align:middle;padding-left:3px;"><?php echo htmlspecialchars($yachtArr['type'] ?? '--'); ?></span>
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
                                <span id="builderInfo" style="display:inline-block;vertical-align:middle;padding-left:3px;"><?php echo htmlspecialchars($yachtArr['builder'] ?? '--'); ?></span>
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
                                <span id="yearBuiltInfo" style="display:inline-block;vertical-align:middle;padding-left:3px;"><?php echo htmlspecialchars($yachtArr['yearBuilt'] ?? '--'); ?></span>
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
                                    <?php if (!empty($yachtArr['imageUrl'])):
                                        // Añadido width: 100%; para asegurar expansión completa
                                    ?>
                                        <div style="color:#ffffff;border-top-left-radius: 6px; border-top-right-radius: 5px; border-bottom-right-radius: 50px; border-bottom-left-radius: 0px;font-size:20px;display:block;border-bottom: 4px solid #ffbe28;background: #4092df; width: 100%;">
                                            <a class="template-yacht-name" href="<?php echo htmlspecialchars($yachtArr['yachtUrl']); ?>" id="yachtLink" style="color:#ffffff;font-weight:bold;text-decoration:none;">
                                                <div style="padding:3px 10px; background-color:#4092df; border-top-left-radius: 5px; border-top-right-radius: 5px; border-bottom-right-radius: 0px; border-bottom-left-radius: 0px;">
                                                    <img class="logo-compani" src="https://www.yacht.vacations/wp-content/uploads/2020/04/1st-logo.png" alt="" style="display:block; width:150px; height:auto;">
                                                </div>
                                                <div style="">
                                                    <img class="template-img" id="yachtImage" src="<?php echo htmlspecialchars($yachtArr['imageUrl']); ?>" alt="Yacht" style="width:100%; display:block; border-top-left-radius: 0px; border-top-right-radius: 0px; border-bottom-right-radius: 70px; border-bottom-left-radius: 0px; position:relative; z-index:1;">
                                                    <div style="padding:8px 15px;border-top-left-radius: 0px; border-top-right-radius: 0px; border-bottom-right-radius: 50px; border-bottom-left-radius: 0px;">
                                                        <p style="margin:5px 0; line-height:0.3; text-align: center;"><?php echo htmlspecialchars($yachtArr['yachtName']); ?></p>
                                                    </div>
                                                </div>      
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                        
                                    <div style="padding: 2px 14px;margin-top: 5px;border-bottom:3px solid #ffbe28;border-top-left-radius: 0px; border-top-right-radius: 63px; border-bottom-right-radius: 0px; border-bottom-left-radius: 2px;background-color: #4092df;color: #ffffff;font-size: 12px; width: 100%;">
                                        <table width="100%" border="0" cellpadding="0" cellspacing="0" style="border-spacing: initial; width: 100%;">
                                            <tr>
                                                <td><?php echo htmlspecialchars('Crew ' . ($yachtArr['crew'] ?? '--') . ', '); ?></td>
                                                <td><?php echo htmlspecialchars('Cabins ' . ($yachtArr['cabins'] ?? '--') . ', '); ?></td>
                                                <td><?php echo htmlspecialchars('Guest ' . ($yachtArr['guest'] ?? '--') . ', '); ?></td>
                                            </tr>
                                        </table>
                                    </div>

                                    <table role="presentation" border="0" cellspacing="0" cellpadding="0" width="100%" style="background-color:#4092df; border-bottom: 3px solid #ffbe28; border-top-left-radius: 5px; border-top-right-radius: 59px; border-bottom-right-radius: 5px; border-bottom-left-radius: 82px; text-align:center; color:#ffffff; font-family:Arial,sans-serif; font-size: 11px; width: 100%;">
                                        <tr>
                                            <td style="padding:5px;"><?php $cabinConfig = isset($yachtArr['cabinConfiguration']) ? str_replace("\n", ", ", $yachtArr['cabinConfiguration']) : '--'; echo htmlspecialchars($cabinConfig); ?></td>
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
                        <?php if (!empty($templateData['resultArray'])): ?>
                            <?php foreach ($templateData['resultArray'] as $resultData): ?>
                                <tr>
                                    <td valign="top" style="vertical-align: top;">
                                        <div class="template-charter-cost-container" style="border-radius: 4px; font-size: 12px; min-width: 300px; background: #f5faf0; padding: 5px;">

                                            <!-- Sección de temporadas -->
                                            <?php if (!empty($templateData['lowSeasonText'])): ?>
                                                <table role="presentation" width="100%" border="0" cellpadding="0" cellspacing="0" style="width: 100%; background-color: #4092df; border-radius: 3px; border-bottom: 2px solid #ffbe28; margin-bottom: 3px; color: #ffffff; font-family: Arial, sans-serif; font-size: 12px; border-spacing: 0; border-collapse: collapse;">
                                                    <tr>
                                                        <td align="left" valign="middle" style="padding: 5px; width: 70%;"><?php echo htmlspecialchars(strstr($templateData['lowSeasonText'], ':', true) ?: $templateData['lowSeasonText']); ?>:</td>
                                                        <td align="right" valign="middle" style="padding: 5px; text-align: right; width: 30%;"><?php echo htmlspecialchars(trim(strstr($templateData['lowSeasonText'], ':') ?: '', ': ')); ?></td>
                                                    </tr>
                                                </table>
                                            <?php endif; ?>
                                            <?php if (!empty($templateData['highSeasonText'])): ?>
                                                <table role="presentation" width="100%" border="0" cellpadding="0" cellspacing="0" style="width: 100%; background-color:#4092df;border-bottom:3px solid #ffbe28;border-top-left-radius: 5px;border-top-right-radius: 5px;border-bottom-right-radius: 50px;border-bottom-left-radius: 5px;font-size:11px; margin-bottom: 3px; color: #ffffff; font-family: Arial, sans-serif; border-spacing: 0; border-collapse: collapse;">
                                                    <tr>
                                                        <td align="left" valign="middle" style="padding: 5px; width: 70%;"><?php echo htmlspecialchars(strstr($templateData['highSeasonText'], ':', true) ?: $templateData['highSeasonText']); ?>:</td>
                                                        <td align="right" valign="middle" style="padding: 5px; text-align: right; width: 30%;"><?php echo htmlspecialchars(trim(strstr($templateData['highSeasonText'], ':') ?: '', ': ')); ?></td>
                                                    </tr>
                                                </table>
                                            <?php endif; ?>


                                            <!-- Detalle principal -->
                                            <table role="presentation" width="100%" border="0" cellpadding="0" cellspacing="0" style="width: 100%; background-color:#4092df;border-bottom:3px solid #ffbe28;border-top-left-radius: 5px;border-top-right-radius: 50px;border-bottom-right-radius: 5px;border-bottom-left-radius: 5px;font-size:11px; border-spacing: 0; border-collapse: collapse;">
                                                <tr>
                                                    <td align="left" valign="middle" style="padding: 5px 11px;font-weight:bold;color:#ffffff;font-family:Arial,sans-serif;font-size:12px; width: 70%;">
                                                        <?php if (!empty($resultData['hours']) && $resultData['hours'] !== '--'): ?>
                                                            <?php echo htmlspecialchars($resultData['hours']) . ' Hours, '; ?>
                                                        <?php else: ?>
                                                            <?php echo htmlspecialchars($resultData['nights']) . ' Nights, '; ?>
                                                        <?php endif; ?>
                                                        <?php echo htmlspecialchars($resultData['guests']) . ' Guests:'; ?>
                                                    </td>
                                                    <td align="right" valign="middle" style="padding: 5px 11px; text-align: right; color: #ffffff; width: 30%;">
                                                        <?php echo htmlspecialchars($resultData['calculatedBaseRate']); ?><?php echo (isset($templateData['enableExpenses']) && $templateData['enableExpenses']) ? ' + Expenses' : ''; ?>
                                                    </td>
                                                </tr>
                                            </table>


                                            <!-- Descuento -->
                                            <?php if (!empty($resultData['discountType']) && $resultData['discountType'] !== '' 
                                                    && $resultData['discountedRate'] !== '--' 
                                                    && $resultData['discountAmount'] !== '€ 0' 
                                                    && $resultData['discountAmount'] !== '0'): ?>
                                                <table role="presentation" width="100%" border="0" cellpadding="0" cellspacing="0"
                                                    style="width: 100%; border-spacing: 0; border-collapse: collapse; padding: 2px 5px; color: #4b4f54; font-family: Arial, sans-serif; font-size: 12px;">
                                                    <tr>
                                                        <td align="left" valign="middle" style="padding: 5px; color: #4b4f54; width: 70%;">
                                                            <?php if ($resultData['discountType'] === 'percentage'): ?>
                                                                Discount Rate (<?php echo htmlspecialchars(number_format(floatval(str_replace([',','%'], '', $resultData['discountAmount'])), 0, '.', ',')); ?>%):
                                                            <?php else: // Assuming 'fixed' ?>
                                                                Discount Rate: - <?php echo htmlspecialchars($resultData['discountAmount']); ?> :
                                                            <?php endif; ?>
                                                        </td>
                                                        <td align="right" valign="middle" style="padding: 5px; text-align: right; color: #4b4f54; width: 30%;">
                                                            <?php echo htmlspecialchars($resultData['discountedRate']); ?>
                                                        </td>
                                                    </tr>
                                                </table>
                                            <?php endif; ?>


                                            <!-- VAT -->
                                            <?php if (empty($templateData['hideElements']['hideVAT']) && !empty($resultData['vatRateForDisplay']) && floatval($resultData['vatRateForDisplay']) > 0
                                                    && !empty($resultData['vatDisplay']) 
                                                    && $resultData['vatDisplay'] !== '€ 0.00'): ?>
                                                <table role="presentation" width="100%" border="0" cellpadding="0" cellspacing="0" 
                                                    style="width: 100%; border-spacing: 0; border-collapse: collapse; padding: 2px 5px; color: #4b4f54; font-family: Arial, sans-serif; font-size: 12px;">
                                                    <tr>
                                                        <td align="left" valign="middle" style="padding: 5px; color: #4b4f54; width: 70%;">
                                                            VAT (<?php echo htmlspecialchars($resultData['vatRateForDisplay']); ?>%):
                                                        </td>
                                                        <td align="right" valign="middle" style="padding: 5px; text-align: right; color: #4b4f54; width: 30%;">
                                                            <?php echo htmlspecialchars($resultData['vatDisplay']); ?>
                                                        </td>
                                                    </tr>
                                                </table>
                                            <?php endif; ?>

                                            <!-- VAT Rate Mix -->
                                            <?php if (!empty($resultData['vatMix']) && is_array($resultData['vatMix'])): ?>
                                                <table role="presentation" width="100%" border="0" cellpadding="0" cellspacing="0" 
                                                    style="width: 100%; border-spacing: 0; border-collapse: collapse; padding: 2px 5px; color: #4b4f54; font-family: Arial, sans-serif; font-size: 12px;">
                                                    <tr>
                                                        <td colspan="3" style="padding: 5px; font-weight: bold;">VAT Rate Mix:</td>
                                                    </tr>
                                                    <?php foreach ($resultData['vatMix'] as $mix): ?>
                                                        <tr>
                                                            <td style="padding: 5px; width: 40%;"><?php echo htmlspecialchars($mix['country']); ?></td>
                                                            <td style="padding: 5px; width: 30%;"><?php echo htmlspecialchars($mix['nights']); ?> Nights</td>
                                                            <td style="padding: 5px; text-align: right; width: 30%;"><?php echo htmlspecialchars($mix['vat']); ?>%</td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </table>
                                            <?php endif; ?>


                                            <!-- APA % -->
                                            <?php if (empty($templateData['hideElements']['hideAPA']) && !empty($resultData['apaRateForDisplay']) && floatval($resultData['apaRateForDisplay']) > 0
                                                    && !empty($resultData['apaPercDisplay']) 
                                                    && $resultData['apaPercDisplay'] !== '€ 0.00'): ?>
                                                <table role="presentation" width="100%" border="0" cellpadding="0" cellspacing="0" 
                                                    style="width: 100%; border-spacing: 0; border-collapse: collapse; padding: 2px 5px; color: #4b4f54; font-family: Arial, sans-serif; font-size: 12px;">
                                                    <tr>
                                                        <td align="left" valign="middle" style="padding: 5px; color: #4b4f54; width: 70%;">
                                                            APA (<?php echo htmlspecialchars($resultData['apaRateForDisplay']); ?>%):
                                                        </td>
                                                        <td align="right" valign="middle" style="padding: 5px; text-align: right; color: #4b4f54; width: 30%;">
                                                            <?php echo htmlspecialchars($resultData['apaPercDisplay']); ?>
                                                        </td>
                                                    </tr>
                                                </table>
                                            <?php endif; ?>


                                            <!-- APA Fijo -->
                                            <?php if (empty($templateData['hideElements']['hideAPA']) && !empty($resultData['apaAmountDisplay']) 
                                                    && $resultData['apaAmountDisplay'] !== '--' 
                                                    && $resultData['apaAmountDisplay'] !== '€ 0.00'): ?>
                                                <table role="presentation" width="100%" border="0" cellpadding="0" cellspacing="0"
                                                    style="width: 100%; border-spacing: 0; border-collapse: collapse; padding: 2px 5px; color: #4b4f54; font-family: Arial, sans-serif; font-size: 12px;">
                                                    <tr>
                                                        <td align="left" valign="middle" style="padding: 5px; color: #4b4f54; width: 70%;">
                                                            APA (Fixed Amount):
                                                        </td>
                                                        <td align="right" valign="middle" style="padding: 5px; text-align: right; color: #4b4f54; width: 30%;">
                                                            <?php echo htmlspecialchars($resultData['apaAmountDisplay']); ?>
                                                        </td>
                                                    </tr>
                                                </table>
                                            <?php endif; ?>


                                            <!-- Relocation Fee -->
                                            <?php if (empty($templateData['hideElements']['hideRelocation']) && !empty($resultData['relocationDisplay']) 
                                                    && $resultData['relocationDisplay'] !== '€ 0.00'): ?>
                                                <table role="presentation" width="100%" border="0" cellpadding="0" cellspacing="0"
                                                    style="width: 100%; border-spacing: 0; border-collapse: collapse; padding: 2px 5px; color: #4b4f54; font-family: Arial, sans-serif; font-size: 12px;">
                                                    <tr>
                                                        <td align="left" valign="middle" style="padding: 5px; color: #4b4f54; width: 70%;">
                                                            Relocation fee:
                                                        </td>
                                                        <td align="right" valign="middle" style="padding: 5px; text-align: right; color: #4b4f54; width: 30%;">
                                                            <?php echo htmlspecialchars($resultData['relocationDisplay']); ?>
                                                        </td>
                                                    </tr>
                                                </table>
                                            <?php endif; ?>


                                            <!-- Security Deposit -->
                                            <?php if (empty($templateData['hideElements']['hideSecurity']) && !empty($resultData['securityDisplay']) 
                                                    && $resultData['securityDisplay'] !== '€ 0.00'): ?>
                                                <table role="presentation" width="100%" border="0" cellpadding="0" cellspacing="0"
                                                    style="width: 100%; border-spacing: 0; border-collapse: collapse; padding: 2px 5px; color: #4b4f54; font-family: Arial, sans-serif; font-size: 12px;">
                                                    <tr>
                                                        <td align="left" valign="middle" style="padding: 5px; color: #4b4f54; width: 70%;">
                                                            Security deposit:
                                                        </td>
                                                        <td align="right" valign="middle" style="padding: 5px; text-align: right; color: #4b4f54; width: 30%;">
                                                            <?php echo htmlspecialchars($resultData['securityDisplay']); ?>
                                                        </td>
                                                    </tr>
                                                </table>
                                            <?php endif; ?>

                                            <!-- Subtotal -->
                                            <?php if (isset($resultData['subtotal'])): ?>
                                                <table role="presentation" width="100%" border="0" cellpadding="0" cellspacing="0" style="width: 100%; background-color:#4092df;border-bottom:3px solid #ffbe28;border-top-left-radius: 5px;border-top-right-radius: 5px;border-bottom-right-radius: 50px;border-bottom-left-radius: 5px;font-size:11px;color: #ffffff; border-spacing: 0; border-collapse: collapse; width: 100%;">
                                                    <tr>
                                                        <td align="left" valign="middle" style="padding: 5px 10px; font-weight: bold; width: 70%;">
                                                            Subtotal for charter:
                                                        </td>
                                                        <td align="right" valign="middle" style="padding: 5px 10px; text-align: right; font-weight: bold; width: 30%;">
                                                            <?php echo htmlspecialchars($resultData['subtotal']); ?>
                                                        </td>
                                                    </tr>
                                                </table>
                                            <?php endif; ?>


                                            <!-- Extras -->
                                            <?php if (!empty($resultData['extras'])): // Show Extras section only if there are extras ?>
                                                <table role="presentation" width="100%" border="0" cellpadding="0" cellspacing="0"
                                                    style="width: 100%; border-spacing: 0; border-collapse: collapse; padding: 2px 5px; color: #4b4f54; font-family: Arial, sans-serif; font-size: 12px; margin-top: 5px;">
                                                    <!-- Extras Title Row -->
                                                    <tr>
                                                        <td colspan="2" align="left" style="padding: 5px; font-weight: bold; font-size: 13px; color: #4b4f54;">
                                                            Extras:
                                                        </td>
                                                    </tr>
                                                    <!-- Extras Data Rows (conditionally hidden) -->
                                                    <?php if (empty($templateData['hideElements']['hideExtras'])): ?>
                                                        <?php foreach ($resultData['extras'] as $extra): ?>
                                                            <tr>
                                                                <td align="left" valign="middle" style="padding: 5px; color: #4b4f54; width: 70%;">
                                                                    <?php echo htmlspecialchars($extra['name']); ?>:
                                                                </td>
                                                                <td align="right" valign="middle" style="padding: 5px; text-align: right; color: #4b4f54; width: 30%;">
                                                                    <?php echo htmlspecialchars($extra['cost'] ?? '--'); ?>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    <?php endif; // End hideExtras condition ?>
                                                </table>
                                            <?php endif; // End !empty($resultData['extras']) condition ?>


                                            <!-- Grand Total -->
                                            <?php if (isset($resultData['grandTotal'])): ?>
                                                <table role="presentation" width="100%" border="0" cellpadding="0" cellspacing="0" style="width: 100%; background-color:#4092df;border-bottom:3px solid #ffbe28;border-top-left-radius: 50px;border-top-right-radius: 5px;border-bottom-right-radius: 50px;border-bottom-left-radius: 5px;font-size:11px;color: #ffffff; border-spacing: 0; border-collapse: collapse; width: 100%;">
                                                    <tr>
                                                        <td align="left" valign="middle" style="padding: 5px 10px; font-weight: bold; width: 70%;">
                                                            Grand Total:
                                                        </td>
                                                        <td align="right" valign="middle" style="padding: 5px 10px; text-align: right; width: 30%;">
                                                            <?php echo htmlspecialchars($resultData['grandTotal']); ?>
                                                        </td>
                                                    </tr>
                                                </table>
                                            <?php endif; ?>


                                            <!-- Gratuity -->
                                            <?php if (empty($templateData['hideElements']['hideGratuity']) && !empty($resultData['gratuityRates'])): ?>
                                                <table role="presentation" width="100%" border="0" cellpadding="0" cellspacing="0"
                                                    style="width: 100%; border-spacing: 0; border-collapse: collapse; background-color:#f5faf0; color:#4b4f54; font-family: Arial, sans-serif; font-size: 12px;">
                                                    <?php foreach ($resultData['gratuityRates'] as $gratuity): ?>
                                                        <tr>
                                                            <td align="left" valign="middle" style="padding: 5px 10px; width: 70%;">
                                                                Suggested gratuity (<?php echo htmlspecialchars($gratuity['rate']); ?>%):
                                                            </td>
                                                            <td align="right" valign="middle" style="padding: 5px 10px; text-align: right; width: 30%;">
                                                                <?php echo htmlspecialchars($gratuity['amount']); ?>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </table>
                                            <?php endif; ?>

                                            
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
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
