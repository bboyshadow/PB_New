<!-- ARCHIVO modules\calc\calculator.php -->

	<!---------- CALCULATOR START ---------->
	<div class="row">
		<div class="col-md-12 d-flex flex-column">
			<h1 class="text-center">Charter Rate Calculator</h1>

			<!-- Calculator Form -->
			<form id="charterForm" method="POST">
				
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
								<input type="checkbox" id="vatRateMix" name="vatRateMix" value="1" onchange="toggleCalcOptionalField('vatCountriesContainer')" aria-controls="vatCountriesContainer" aria-expanded="false"> VAT Rate Mix:
								<button type="button" class="btn btn-sm btn-outline-primary ms-1 btn-add-vat-country" id="addVatCountryBtn" style="display: none;" onclick="VatRateMix.addCountryField()">
									<i class="fas fa-plus"></i>
								</button>
							</label>
							<label class="me-4 form-check-label">
								<input type="checkbox" id="vatCheck" onchange="toggleCalcOptionalField('vatField')" aria-controls="vatField" aria-expanded="false"> VAT Rate:
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

				<!-- Optional Fields Row -->
				<div class="row optional-fields mt-2">
					<!-- VAT Rate Field -->
					<div id="vatField" class="form-group optional-field-container col-3" style="display: none;">
						<label>VAT Rate:</label>
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
						<label>Relocation Fee:</label>
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

                                         <!-- Campo Yacht URL -->
                                       <div class="col-12 col-md-3 col-lg-2 p-1">
                                               <label for="yachtUrl" class="form-label mt-4">Yacht URL</label>
                                               <input type="url" id="yachtUrl" name="yachtUrl" class="form-control w-100" placeholder="https://example.com" pattern="https?://.+" required>
                                       </div>

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
	
	<!-- Scripts for Calculator functionality -->
	<script src="<?php echo get_template_directory_uri(); ?>/app_yacht/modules/calc/js/VatRateMix.js"></script>
	<script src="<?php echo get_template_directory_uri(); ?>/app_yacht/modules/calc/js/interfaz.js"></script>
	<script src="<?php echo get_template_directory_uri(); ?>/app_yacht/modules/calc/js/mix.js"></script>
	<script src="<?php echo get_template_directory_uri(); ?>/app_yacht/modules/calc/js/extraPerPerson.js"></script>
	<script src="<?php echo get_template_directory_uri(); ?>/app_yacht/modules/calc/js/calculate.js"></script>
	<script src="<?php echo get_template_directory_uri(); ?>/app_yacht/modules/calc/js/promotion.js"></script>
