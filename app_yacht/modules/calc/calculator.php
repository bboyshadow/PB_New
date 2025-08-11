<!-- ARCHIVO modules\calc\calculator.php -->

	<!---------- CALCULATOR START ---------->
	<div class="row">
		<div class="col-md-12 d-flex flex-column">
			<h1 class="text-center">Charter Rate Calculator</h1>

			<!-- Calculator Form -->
			<form id="charterForm" method="POST">

                <!-- Yacht Info Module -->
                <div class="row mb-3">
                    <div class="col-md-8">
                        <label for="yacht-url" class="form-label">Yacht URL</label>
                        <input type="url" class="form-control" id="yacht-url" placeholder="Enter yacht listing URL...">
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="button" id="get-yacht-info" class="btn btn-primary">Get Info</button>
                        <div class="form-check mt-2 ms-3">
                            <input type="checkbox" class="form-check-input" id="force-refresh">
                            <label class="form-check-label" for="force-refresh"><?php esc_html_e('Force Refresh', 'creativoypunto'); ?></label>
                        </div>
                    </div>
                </div>
                
                <!-- Container for yacht information -->
                <div id="yacht-info-container" style="display: none;"></div>
				
				<!-- Currency Selection -->
				<div class="row">
					<div class="col-md-3">
						<label for="currency">Currency:</label>
						<select class="form-control" id="currency" name="currency" required onchange="updateCurrencySymbols()">
							<option value="" disabled selected>Select Currency</option>
							<option value="€">€ (Euros)</option>
							<option value="$USD">$ (USD Dollars)</option>
							<option value="$AUD">A$ (AUD Australian Dollars)</option>
						</select>
					</div>
					<div class="col-md-3 form-check d-flex align-items-center mt-2 checkbox-wrapper-mix">
						<input type="checkbox" class="form-check-input me-2 mt-1" id="enableMixedSeasons">
						<label class="form-check-label" for="enableMixedSeasons">Mixed Seasons</label>
					</div>
					<div class="col-md-3 form-check d-flex align-items-center mt-2 checkbox-wrapper-mix">
						<input type="checkbox" class="form-check-input me-2 mt-1" id="enableOneDayCharter">
						<label class="form-check-label" for="enableOneDayCharter">One day Charter</label>
					</div>
					<div class="col-md-3 form-check d-flex align-items-center mt-2 checkbox-wrapper-mix">
						<input type="checkbox" class="form-check-input me-2 mt-1" id="enableExpenses">
						<label class="form-check-label" for="enableExpenses">Add + Expenses to Base Charter Rate</label>
					</div>
				</div>

				<!-- Mixed Seasons Container -->
				<div id="mixedSeasonsContainer" class="row mixed-seasons-container mt-2"></div>
				<!-- Dynamic Charter Rate Fields -->
				<div id="charterRateContainer" class="row mt-2"></div>
				
				<!-- Checkbox Options Row -->
				<div class="row mt-3 align-items-center">
					<div class="col-md-12">
						<div class="d-flex flex-wrap checkbox-taxas">
							<!-- Each Checkbox with Label -->

							<label class="me-4 form-check-label">
								<input type="checkbox" id="vatCheck" onchange="toggleCalcOptionalField('vatField')" aria-controls="vatField" aria-expanded="false"> VAT Rate: 
								<div class="form-check form-switch m-0 vat-mix-controls" style="display:none;">
									<input class="form-check-input" type="checkbox" id="vatRateMix" name="vatRateMix" aria-controls="vatCountriesContainer" title="Prorated Mix" onchange="toggleCalcOptionalField('vatCountriesContainer')" aria-expanded="false">
									<label class="form-check-label small ms-1" for="vatRateMix">Mix</label>
								</div>
								<button type="button" class="btn btn-sm btn-outline-primary ms-2 btn-add-vat-country vat-mix-controls" id="addVatCountryBtn" style="display: none;" onclick="VatRateMix.addCountryField()">
									<i class="fas fa-plus"></i>
								</button>
							</label>
							<label class="me-4 form-check-label">
								<input type="checkbox" id="apaCheck" onchange="toggleCalcOptionalField('apaField')" aria-controls="apaField" aria-expanded="false"> APA :
							</label>
							<label class="me-4 form-check-label">
								<input type="checkbox" id="apaPercentageCheck" onchange="toggleCalcOptionalField('apaPercentageField')" aria-controls="apaPercentageField" aria-expanded="false"> APA %:
							</label>
							<label class="me-4 form-check-label">
								<input type="checkbox" id="relocationCheck" onchange="toggleCalcOptionalField('relocationField')" aria-controls="relocationField" aria-expanded="false"> Relocation Fee:
								
							</label>
							<label class="me-4 form-check-label">
								<input type="checkbox" id="securityCheck" onchange="toggleCalcOptionalField('securityField')" aria-controls="securityField" aria-expanded="false"> Security Deposit:
							</label>
							
						</div>
					</div>
				</div>

				<!-- Container for Mixed Taxes country fields -->
				<div id="vatCountriesContainer" class="row mt-2 gx-2 align-items-center" style="display: none;"></div>

<!-- Relocation Auto Calculator (hidden by default) -->
<div id="relocationAutoContainer" class="mt-3" style="display:none;">
    <p class="fw-bold mb-2">Relocation calculator</p>
    <div class="row flex-wrap align-items-end gx-2">
        <div class="col-auto">
            <input id="reloc-distance-check" type="checkbox"> Distance (NM)
            <input id="reloc-distance" type="number" class="form-control mt-1" placeholder="Distance" />
        </div>
        <div class="col-auto">
            <input id="reloc-speed-check" type="checkbox"> Speed (knots)
            <input id="reloc-cruising-speed" type="number" class="form-control mt-1" placeholder="Knots" />
        </div>
        <div class="col-auto">
            <input id="reloc-hours-check" type="checkbox"> Hours
            <input id="reloc-hours" type="number" class="form-control mt-1" placeholder="Hours" />
        </div>
        <div class="col-auto">
            <input id="reloc-fuel-consumption-check" type="checkbox"> Consumption (l/h or l/nm)
            <input id="reloc-fuel-consumption" type="number" class="form-control mt-1" placeholder="Consumption" />
        </div>
        <div class="col-auto">
            <input id="reloc-fuel-price-check" type="checkbox"> Fuel price
            <input id="reloc-fuel-price" type="number" step="0.01" class="form-control mt-1" placeholder="€/L" />
        </div>
        <div class="col-auto">
            <input id="reloc-crew-count-check" type="checkbox"> Crew
            <input id="reloc-crew-count" type="number" class="form-control mt-1" placeholder="Crew count" />
        </div>
        <div class="col-auto">
            <input id="reloc-crew-wage-check" type="checkbox"> Daily wage
            <input id="reloc-crew-wage" type="number" step="0.01" class="form-control mt-1" placeholder="€/day" />
        </div>
        <div class="col-auto">
            <input id="reloc-port-fees-check" type="checkbox"> Port fees
            <input id="reloc-port-fees" type="number" step="0.01" class="form-control mt-1" placeholder="€" />
        </div>
        <div class="col-auto">
            <input id="reloc-extra-check" type="checkbox"> Other costs
            <input id="reloc-extra" type="number" step="0.01" class="form-control mt-1" placeholder="€" />
        </div>
    </div>
    <div class="mt-2">
        <button id="applyRelocationButton" type="button" class="btn btn-secondary">Apply</button>
        <span id="relocation-auto-result" class="ms-3 fw-bold"></span>
    </div>
</div>

				<!-- Optional Fields Row -->
				<div class="row optional-fields mt-2">
					<!-- VAT Rate Field -->
					<div id="vatField" class="form-group optional-field-container col-3" style="display: none;">
						<label class="mb-1">VAT Rate:</label>
						<div class="input-group" style="flex-wrap: nowrap;">
							<input type="text" class="form-control" id="vatRate" name="vatRate" placeholder="VAT %" oninput="formatNumber(this)" required>
							<span class="input-group-text">%</span>
						</div>
					</div>
					<!-- APA Percentage Field -->
					<div id="apaPercentageField" class="form-group optional-field-container col-3" style="display: none;">
						<label>APA (%):</label>
						<div class="input-group" style="flex-wrap: nowrap;">
							<input type="text" class="form-control" id="apaPercentage" name="apaPercentage" placeholder="APA %" oninput="formatNumber(this)" required>
							<span class="input-group-text">%</span>
						</div>
					</div>
					<!-- APA Fixed Amount Field -->
					<div id="apaField" class="form-group optional-field-container col-3" style="display: none;">
						<label>APA (Fixed):</label>
						<div class="input-group" style="flex-wrap: nowrap;">
							<input type="text" class="form-control" id="apaAmount" name="apaAmount" placeholder="APA Amount" oninput="formatNumber(this)" required>
							<span class="input-group-text" id="apaCurrencySymbol">€</span>
						</div>
					</div>
					<!-- Relocation Fee Field -->
					<div id="relocationField" class="form-group optional-field-container col-3" style="display: none;">
						<div class="d-flex align-items-center mb-1">
    <label class="me-2 mb-0">Relocation Fee:</label>
    <div class="form-check form-switch m-0">
        <input class="form-check-input" type="checkbox" id="relocationAutoCheck" aria-controls="relocationAutoContainer" title="Auto calculate">
        <label class="form-check-label small ms-1" for="relocationAutoCheck">Auto calculate</label>
    </div>
</div>
						<div class="input-group" style="flex-wrap: nowrap;">
							<input type="text" class="form-control" id="relocationFee" name="relocationFee" placeholder="Relocation fee" oninput="formatNumber(this)" required>
							<span class="input-group-text" id="relocationCurrencySymbol">€</span>
						</div>
					</div>
					<!-- Security Deposit Field -->
                                        <div id="securityField" class="form-group optional-field-container col-3" style="display: none;">
                                                <label>Security Deposit:</label>
                                                <div class="input-group" style="flex-wrap: nowrap;">
                                                        <input type="text" class="form-control" id="securityFee" name="securityFee" placeholder="Security fee" oninput="formatNumber(this)" required>
                                                        <span class="input-group-text" id="securityCurrencySymbol">€</span>
                                                </div>
                                        </div>
                                </div>



                                <!-- Extra Fields Container -->
                                <div id="extrasContainer" class="row optional-fields mt-2"></div>

				<!-- Error Message -->
				<div id="errorMessage" class="text-danger" style="display: none;" role="alert" aria-live="assertive">Please fill in all required fields.</div>

				<!-- Hide Elements Options Row -->
				<div class="row mt-3 mb-2">
					<div class="col-md-12">
						<div class="d-flex flex-wrap">
							<h6 class="w-100 mb-2">Hide elements in result:</h6>
							<label class="me-4 form-check-label">
								<input type="checkbox" id="hideVAT" class="hide-element-check"> VAT
							</label>
							<label class="me-4 form-check-label">
								<input type="checkbox" id="hideAPA" class="hide-element-check"> APA
							</label>
							<label class="me-4 form-check-label">
								<input type="checkbox" id="hideRelocation" class="hide-element-check"> Relocation Fee
							</label>
							<label class="me-4 form-check-label">
								<input type="checkbox" id="hideSecurity" class="hide-element-check"> Security Deposit
							</label>
							<label class="me-4 form-check-label">
								<input type="checkbox" id="hideExtras" class="hide-element-check"> Extras
							</label>
							<label class="me-4 form-check-label">
								<input type="checkbox" id="hideGratuity" class="hide-element-check"> Gratuity
							</label>
						</div>
					</div>
				</div>

				<!-- Action Buttons Row -->
				<div class="row d-flex align-items-center justify-content-between">
					<!-- Botón Extras -->
					<div class="col-4 col-md-3 col-lg-2 p-1">
						<button type="button" class="btn btn-secondary w-100 mt-4 btn-estras">Extras</button> <!-- onclick removido -->
					</div>
					<!-- Botón Guest Fee -->
					<div class="col-4 col-md-3 col-lg-2 p-1">
						<button type="button" class="btn btn-secondary w-100 mt-4" onclick="addExtraPerPersonField()">Guest Fee</button>
					</div>
					<!-- Botón Calculate -->
					<div class="col-4 col-md-3 col-lg-2 p-1">
						<button type="button" class="btn btn-success w-100 mt-4" id="calculateButton">Calculate</button>
					</div>

					<!-- Botón Copy Result -->
					<div class="col-4 col-md-3 col-lg-2 p-1">
						<button type="button" class="btn btn-primary w-100 mt-4" id="copyButton" onclick="copyToClipboard()" disabled>Copy Result</button>
					</div>

                                         <!-- Yacht URL is handled by the yachtinfo module above -->

					<!-- Botón Create Template -->
					<div class="col-6 col-md-3 col-lg-2 p-1">
						<button type="button" class="btn btn-info w-100 mt-4" id="createTemplateButton">Create Template</button>
					</div>

				</div>

			</form>
		</div>
		<!-- Results Display Container -->
		<div class="col-md-12 d-flex flex-column">
			<div class="result" id="result" aria-live="polite"></div>
		</div>

	</div>
	<!---------- CALCULATOR END ---------->
	
	<!-- Scripts load via wp_enqueue_scripts in app_yacht/core/yacht-functions.php to avoid duplicates -->
