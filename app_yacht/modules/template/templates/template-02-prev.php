<?php
// Verificar datos recibidos
if (empty($templateData['resultArray'])) {
    error_log('Empty resultArray in default-template.php');
    error_log('Template data: ' . print_r($templateData, true));
}

$yachtArr = buildYachtInfoArray($templateData['yachtInfo'] ?? []);
$calcArr  = buildCalcSnippetArray(
    $templateData['resultArray'] ?? [],
    $templateData['lowSeasonText'] ?? '',
    $templateData['highSeasonText'] ?? ''
);

// Verificar datos procesados
if (empty($calcArr['structuredBlock'])) {
    error_log('Empty structuredBlock in default-template.php');
    error_log('Calc array: ' . print_r($calcArr, true));
}

$block = $calcArr['structuredBlock'] ?? [];
?>
<!-- Template que debe copiarse con el botón Copy Template inicio -->
<div id="yachtInfoContainer" class="template-body-container" style="width: 100%; max-width: 700px; margin: 0 auto;">

    <!-- Header estático - Corrección para Outlook -->
    <!--[if mso]>
    <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" arcsize="12%" strokecolor="#dbe68b" strokeweight="3px" style="width:100%;height:auto;padding:0;margin:0;">
    <![endif]-->
    <div class="template-header" style="background-color:#12323a;border-bottom:3px solid #dbe68b;border-radius: 13px 74px 5px 5px;text-align:center;color:#ffffff;font-family:Arial,sans-serif;font-size:11px;position:relative;">
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
    <!--[if mso]></v:roundrect><![endif]-->

    <!-- Contenido principal con tablas -->
    <table width="100%" border="0" cellpadding="0" cellspacing="0" style="max-width: 700px; margin: 0 auto; padding: 10px 0;">
        <tr>
            <!-- Columna Imagen -->
            <td width="395" valign="top" style="padding-right: 10px;">
                <div id="imageSection" style="max-width: 395px;min-width: 300px;">
                    <?php if (!empty($yachtArr['imageUrl'])): ?>
                    <!--[if mso]>
                    <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" arcsize="15%" strokecolor="#dbe68b" strokeweight="4px" style="width:100%;height:auto;padding:0;margin:0;">
                    <![endif]-->
                    <div style="color:#ffffff;border-radius:6px 5px 50px 0;font-size:20px;display:block;border-bottom: 4px solid #dbe68b;background: #12323a;position:relative;">
                        <a class="template-yacht-name" href="<?php echo htmlspecialchars($yachtArr['yachtUrl']); ?>" id="yachtLink" style="color:#ffffff;font-weight:bold;text-decoration:none;display:block;">
                            <table width="100%" border="0" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="padding:3px 10px; background-color:#12323a; border-radius:5px 5px 0 0;">
                                        <img src="https://www-worldwideboat-com.exactdn.com/wp-content/themes/worldwideboat/assets/img/new-primary-logo.png?strip=all&lossy=1&quality=92&ssl=1" alt="" style="display:block; width:150px; height:auto;">
                                    </td>
                                </tr>
                                <tr>
                                    <td style="position:relative;">
                                        <img src="<?php echo htmlspecialchars($yachtArr['imageUrl']); ?>" alt="Yacht" style="width:100%; display:block; border-radius:0 0 70px 0;">
                                        <div style="padding:8px 15px;border-radius: 0 0 50px 0;position:absolute;bottom:0;width:100%;box-sizing:border-box;">
                                            <p style="margin:5px 0; line-height:0.3; text-align: center;font-size:20px;"><?php echo htmlspecialchars($yachtArr['yachtName']); ?></p>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </a>
                    </div>
                    <!--[if mso]></v:roundrect><![endif]-->
                    <?php endif; ?>
                    
                    <!-- Sección de especificaciones -->
                    <table width="100%" border="0" cellpadding="0" cellspacing="0" style="margin-top:5px;">
                        <tr>
                            <td style="padding: 2px 14px;border-bottom:3px solid #dbe68b;border-radius: 0px 63px 0px 2px;background-color: #12323a;color: #ffffff;font-size: 12px;">
                                <table width="100%" border="0" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td width="33%" style="padding:3px;text-align:center;"><?php echo htmlspecialchars('Crew ' . ($yachtArr['crew'] ?? '--')); ?></td>
                                        <td width="33%" style="padding:3px;text-align:center;"><?php echo htmlspecialchars('Cabins ' . ($yachtArr['cabins'] ?? '--')); ?></td>
                                        <td width="34%" style="padding:3px;text-align:center;"><?php echo htmlspecialchars('Guest ' . ($yachtArr['guest'] ?? '--')); ?></td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding:5px;background-color:#12323a;border-bottom: 3px solid #dbe68b;border-radius:5px 59px 5px 82px;text-align:center;color:#ffffff;font-family:Arial,sans-serif;font-size:11px;">
                                <?php $cabinConfig = isset($yachtArr['cabinConfiguration']) ? str_replace("\n", ", ", $yachtArr['cabinConfiguration']) : '--'; echo htmlspecialchars($cabinConfig); ?>
                            </td>
                        </tr>
                    </table>
                </div>
            </td>

            <!-- Columna Contenedores de Costo -->
            <td valign="top">
                <table width="100%" border="0" cellpadding="0" cellspacing="5">
                    <?php if (!empty($templateData['resultArray'])): ?>
                        <?php foreach ($templateData['resultArray'] as $resultData): ?>
                        <tr>
                            <td>
                                <!--[if mso]>
                                <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" arcsize="8%" strokecolor="#dbe68b" strokeweight="2px" style="width:100%;height:auto;padding:0;margin:0;">
                                <![endif]-->
                                <div style="border-radius:4px;font-size:12px;min-width:300px;background:#f5faf0;position:relative;">
                                    <?php if (!empty($templateData['lowSeasonText']) && !empty($templateData['highSeasonText'])): ?>
                                    <table width="100%" border="0" cellpadding="0" cellspacing="0">
                                        <tr>
                                            <td style="padding:5px;background-color:#12323a;border-radius:3px;border-bottom:2px solid #dbe68b;color:#ffffff;">
                                                <?php echo htmlspecialchars($templateData['lowSeasonText']); ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="padding:5px;background-color:#12323a;border-radius:0 0 50px 0;border-bottom:5px solid #dbe68b;color:#ffffff;">
                                                <?php echo htmlspecialchars($templateData['highSeasonText']); ?>
                                            </td>
                                        </tr>
                                    </table>
                                    <?php endif; ?>

                                    <!-- Detalle principal -->
                                    <table width="100%" border="0" cellpadding="0" cellspacing="0" style="background-color:#12323a;border-radius:0 14px 0 25px;border-bottom:5px solid #dbe68b;">
                                        <tr>
                                            <td style="padding:10px 15px;">
                                                <table width="100%" border="0" cellpadding="0" cellspacing="0">
                                                    <tr>
                                                        <td width="60%" style="color:#ffffff;font-family:Arial,sans-serif;">
                                                            <?php if (!empty($resultData['hours']) && $resultData['hours'] !== '--'): ?>
                                                                <?php echo htmlspecialchars($resultData['hours']) . ' Hours, '; ?>
                                                            <?php else: ?>
                                                                <?php echo htmlspecialchars($resultData['nights']) . ' Nights, '; ?>
                                                            <?php endif; ?>
                                                            <?php echo htmlspecialchars($resultData['guests']) . ' Guests:'; ?>
                                                        </td>
                                                        <td width="40%" style="text-align:right;color:#ffffff;font-family:Arial,sans-serif;">
                                                            <?php echo htmlspecialchars($resultData['calculatedBaseRate']); ?><?php echo !empty($templateData['enableExpenses']) ? ' + Expenses' : ''; ?>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                    </table>

                                    <!-- Secciones dinámicas -->
                                    <?php 
                                    $sections = [
                                        'discount' => [
                                            'cond' => !empty($resultData['discountType']) && $resultData['discountAmount'] !== '€ 0',
                                            'label' => 'Discount Rate - ' . htmlspecialchars($resultData['discountAmount']) . ':',
                                            'value' => $resultData['discountedRate']
                                        ],
                                        'vat' => [
                                            'cond' => !empty($resultData['vatRateForDisplay']) && $resultData['vatDisplay'] !== '€ 0.00',
                                            'label' => 'VAT (' . htmlspecialchars($resultData['vatRateForDisplay']) . '%):',
                                            'value' => $resultData['vatDisplay']
                                        ],
                                        'apa' => [
                                            'cond' => !empty($resultData['apaRateForDisplay']) && $resultData['apaPercDisplay'] !== '€ 0.00',
                                            'label' => 'APA (' . htmlspecialchars($resultData['apaRateForDisplay']) . '%):',
                                            'value' => $resultData['apaPercDisplay']
                                        ],
                                        'apaAmount' => [
                                            'cond' => !empty($resultData['apaAmountDisplay']) && $resultData['apaAmountDisplay'] !== '€ 0.00',
                                            'label' => 'APA (Fixed Amount):',
                                            'value' => $resultData['apaAmountDisplay']
                                        ],
                                        'relocation' => [
                                            'cond' => !empty($resultData['relocationDisplay']) && $resultData['relocationDisplay'] !== '€ 0.00',
                                            'label' => 'Relocation fee:',
                                            'value' => $resultData['relocationDisplay']
                                        ],
                                        'security' => [
                                            'cond' => !empty($resultData['securityDisplay']) && $resultData['securityDisplay'] !== '€ 0.00',
                                            'label' => 'Security deposit:',
                                            'value' => $resultData['securityDisplay']
                                        ]
                                    ];
                                    
                                    foreach ($sections as $section): 
                                        if ($section['cond']):
                                    ?>
                                    <table width="100%" border="0" cellpadding="0" cellspacing="0" style="padding:5px;">
                                        <tr>
                                            <td width="70%" style="color:#4b4f54;font-family:Arial,sans-serif;"><?php echo $section['label']; ?></td>
                                            <td width="30%" style="text-align:right;color:#4b4f54;font-family:Arial,sans-serif;"><?php echo $section['value']; ?></td>
                                        </tr>
                                    </table>
                                    <?php 
                                        endif;
                                    endforeach; 
                                    ?>

                                    <?php if (isset($resultData['subtotal'])): ?>
                                    <table width="100%" border="0" cellpadding="0" cellspacing="0" style="background-color:#12323a;border-bottom:5px solid #dbe68b;border-radius:14px 0 25px 0;">
                                        <tr>
                                            <td style="padding:10px 15px;color:#ffffff;">
                                                <table width="100%" border="0" cellpadding="0" cellspacing="0">
                                                    <tr>
                                                        <td width="70%">Subtotal for charter:</td>
                                                        <td width="30%" style="text-align:right;"><?php echo htmlspecialchars($resultData['subtotal']); ?></td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                    </table>
                                    <?php endif; ?>

                                    <?php if (!empty($resultData['extras'])): ?>
                                    <table width="100%" border="0" cellpadding="0" cellspacing="0">
                                        <tr>
                                            <td style="padding:10px 15px;font-weight:bold;background-color:#12323a;border-bottom:5px solid #dbe68b;color:#ffffff;">
                                                Extras:
                                            </td>
                                        </tr>
                                        <?php foreach ($resultData['extras'] as $extra): ?>
                                        <tr>
                                            <td style="padding:5px;">
                                                <table width="100%" border="0" cellpadding="0" cellspacing="0">
                                                    <tr>
                                                        <td width="70%" style="color:#4b4f54;"><?php echo htmlspecialchars($extra['name']); ?>:</td>
                                                        <td width="30%" style="text-align:right;color:#4b4f54;"><?php echo htmlspecialchars($extra['cost']); ?></td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                        
                                        <?php if (isset($resultData['grandTotal'])): ?>
                                        <tr>
                                            <td style="padding:10px 15px;font-weight:bold;background-color:#12323a;border-bottom:5px solid #dbe68b;color:#ffffff;">
                                                <table width="100%" border="0" cellpadding="0" cellspacing="0">
                                                    <tr>
                                                        <td width="70%">Grand Total:</td>
                                                        <td width="30%" style="text-align:right;"><?php echo htmlspecialchars($resultData['grandTotal']); ?></td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                        <?php endif; ?>
                                    </table>
                                    <?php endif; ?>

                                    <?php if (!empty($resultData['gratuityRates'])): ?>
                                            <?php foreach ($resultData['gratuityRates'] as $gratuity): ?>
                                                <tr>
                                                    <td style="padding: 2px 5px; color: #4b4f54;">
                                                        <table width="100%" border="0" cellpadding="0" cellspacing="0" style="border-spacing: initial;">
                                                            <tr>
                                                                <td align="left">Suggested gratuity (<?php echo htmlspecialchars($gratuity['rate']); ?>%):</td>
                                                                <td align="right" style="text-align: right;"><?php echo htmlspecialchars($gratuity['amount']); ?></td>
                                                            </tr>
                                                        </table>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                        
                                    </table>
                                </div>
                                <!--[if mso]></v:roundrect><![endif]-->
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
    </table>
</div>
<!-- Template que debe copiarse con el botón Copy Template fin -->

                                       