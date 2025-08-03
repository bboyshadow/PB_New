<div class="template-body-container" id="yachtInfoContainer" style="display: flex; flex-direction: column; width: 100%; max-width: 1280px; margin: 0 auto; border: 1px solid #ddd; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); padding: 0; box-sizing: border-box;">
            <!-- Header estático, siempre visible -->
            <div class="template-header" style="display: flex; flex-wrap: wrap; padding: 10px; background-color: #5aa1e39e; text-align: center; justify-content: space-around; align-items: center; font-size: 14px; border-width: 0px; border-style: solid; width: 100%; max-width: 1280px; margin: 0 auto; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); gap: 10px;">
                <!-- Length -->
                <div class="template-header-icon-container" style="display: flex; align-items: center; justify-content: center; margin: 5px 0; flex: 1 1 120px; gap: 10px;">
                    <span class="template-header-icon" style="font-size: 16px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-rulers" viewBox="0 0 16 16">
                            <path d="M1 0a1 1 0 0 0-1 1v14a1 1 0 0 0 1 1h5v-1H2v-1h4v-1H4v-1h2v-1H2v-1h4V9H4V8h2V7H2V6h4V2h1v4h1V4h1v2h1V2h1v4h1V4h1v2h1V2h1v4h1V1a1 1 0 0 0-1-1z"></path>
                        </svg>
                    </span>
                    <p class="template-header-title" style="margin: 0; font-weight: bold;">Length:</p>
                    <span class="template-header-info" id="lengthInfo" style="font-size: 14px;">160 Feet</span>
                </div>
                <!-- Builder -->
                <div class="template-header-icon-container" style="display: flex; align-items: center; justify-content: center; margin: 5px 0; flex: 1 1 120px; gap: 10px;">
                    <span class="template-header-icon" style="font-size: 16px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-tools" viewBox="0 0 16 16">
                            <path d="M1 0 0 1l2.2 3.081a1 1 0 0 0 .815.419h.07a1 1 0 0 1 .708.293l2.675 2.675-2.617 2.654A3.003 3.003 0 0 0 0 13a3 3 0 1 0 5.878-.851l2.654-2.617.968.968-.305.914a1 1 0 0 0 .242 1.023l3.27 3.27a.997.997 0 0 0 1.414 0l1.586-1.586a.997.997 0 0 0 0-1.414l-3.27-3.27a1 1 0 0 0-1.023-.242L10.5 9.5l-.96-.96 2.68-2.643A3.005 3.005 0 0 0 16 3q0-.405-.102-.777l-2.14 2.141L12 4l-.364-1.757L13.777.102a3 3 0 0 0-3.675 3.68L7.462 6.46 4.793 3.793a1 1 0 0 1-.293-.707v-.071a1 1 0 0 0-.419-.814zm9.646 10.646a.5.5 0 0 1 .708 0l2.914 2.915a.5.5 0 0 1-.707.707l-2.915-2.914a.5.5 0 0 1 0-.708M3 11l.471.242.529.026.287.445.445.287.026.529L5 13l-.242.471-.026.529-.445.287-.287.445-.529.026L3 15l-.471-.242L2 14.732l-.287-.445L1.268 14l-.026-.529L1 13l.242-.471.026-.529.445-.287.287-.445.529-.026z"></path>
                        </svg>
                    </span>
                    <p class="template-header-title" style="margin: 0; font-weight: bold;">Builder:</p>
                    <span class="template-header-info" id="builderInfo" style="font-size: 14px;">Trinity</span>
                </div>
                <!-- Year Built -->
                <div class="template-header-icon-container" style="display: flex; align-items: center; justify-content: center; margin: 5px 0; flex: 1 1 120px; gap: 10px;">
                    <span class="template-header-icon" style="font-size: 16px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-calendar3" viewBox="0 0 16 16">
                            <path d="M14 0H2a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2M1 3.857C1 3.384 1.448 3 2 3h12c.552 0 1 .384 1 .857v10.286c0 .473-.448.857-1 .857H2c-.552 0-1-.384-1-.857z"></path>
                            <path d="M6.5 7a1 1 0 1 0 0-2 1 1 0 0 0 0 2m3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2m3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2m-9 3a1 1 0 1 0 0-2 1 1 0 0 0 0 2m3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2m3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2m3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2m-9 3a1 1 0 1 0 0-2 1 1 0 0 0 0 2m3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2m3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2"></path>
                        </svg>
                    </span>
                    <p class="template-header-title" style="margin: 0; font-weight: bold;">Year Built:</p>
                    <span class="template-header-info" id="yearBuiltInfo" style="font-size: 14px;">1999</span>
                </div>
                <!-- Crew -->
                <div class="template-header-icon-container" style="display: flex; align-items: center; justify-content: center; margin: 5px 0; flex: 1 1 120px; gap: 10px;">
                    <span class="template-header-icon" style="font-size: 16px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-people-fill" viewBox="0 0 16 16">
                            <path d="M7 14s-1 0-1-1 1-4 5-4 5 3 5 4-1 1-1 1zm4-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6m-5.784 6A2.24 2.24 0 0 1 5 13c0-1.355.68-2.75 1.936-3.72A6.3 6.3 0 0 0 5 9c-4 0-5 3-5 4s1 1 1 1zM4.5 8a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5"></path>
                        </svg>
                    </span>
                    <p class="template-header-title" style="margin: 0; font-weight: bold;">Crew:</p>
                    <span class="template-header-info" id="crewInfo" style="font-size: 14px;">10</span>
                </div>
                <!-- Cabins -->
                <div class="template-header-icon-container" style="display: flex; align-items: center; justify-content: center; margin: 5px 0; flex: 1 1 120px; gap: 10px;">
                    <span class="template-header-icon" style="font-size: 16px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-house-fill" viewBox="0 0 16 16">
                            <path d="M8.707 1.5a1 1 0 0 0-1.414 0L.646 8.146a.5.5 0 0 0 .708.708L8 2.207l6.646 6.647a.5.5 0 0 0 .708-.708L13 5.793V2.5a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5v1.293z"></path>
                            <path d="m8 3.293 6 6V13.5a1.5 1.5 0 0 1-1.5 1.5h-9A1.5 1.5 0 0 1 2 13.5V9.293z"></path>
                        </svg>
                    </span>
                    <p class="template-header-title" style="margin: 0; font-weight: bold;">Cabins:</p>
                    <span class="template-header-info" id="cabinsInfo" style="font-size: 14px;">6</span>
                </div>
                <!-- Guests -->
                <div class="template-header-icon-container" style="display: flex; align-items: center; justify-content: center; margin: 5px 0; flex: 1 1 120px; gap: 10px;">
                    <span class="template-header-icon" style="font-size: 16px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-people-fill" viewBox="0 0 16 16">
                            <path d="M7 14s-1 0-1-1 1-4 5-4 5 3 5 4-1 1-1 1zm4-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6m-5.784 6A2.24 2.24 0 0 1 5 13c0-1.355.68-2.75 1.936-3.72A6.3 6.3 0 0 0 5 9c-4 0-5 3-5 4s1 1 1 1zM4.5 8a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5"></path>
                        </svg>
                    </span>
                    <p class="template-header-title" style="margin: 0; font-weight: bold;">Guest:</p>
                    <span class="template-header-info" id="guestInfo" style="font-size: 14px;">12</span>
                </div>
            </div>



            <!-- Content -->
            <div style="display: flex; align-items: flex-start; padding: 20px; gap: 20px; flex-wrap: wrap;">

                <!-- Contenedor de imagen y nombre del yate -->
                <div id="imageSection" class="template-img-section" style="display: flex; flex-wrap: wrap; gap: 20px; box-sizing: border-box; flex: 1; min-width: 300px;">
                    <div class="template-img-yacht" style="position: relative; width: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center; background-color: #5aa1e39e;">
                        <div style="position: relative; width: 100%;">
                            <!-- Imagen dinámica del yate, vacía al cargar -->
                            <img class="template-img" id="yachtImage" src="https://www.centralyachtagent.com/yachtadmin/yachtlg/yacht9373/9373brochure1.jpg" alt="Yacht" style="width: 100%; height: auto; display: block; box-shadow: 0px 2px 3px;">
                            <a class="template-yacht-name" href="https://cyaeb.com/2869/pobh/crn/9373/" id="yachtLink" style="position: absolute; bottom: 10px; right: 10px; background-color: rgba(0, 0, 0, 0.6); color: white; padding: 5px 10px; border-radius: 4px; font-size: 16px; font-weight: bold; text-decoration: none; box-shadow: rgb(51, 51, 51) 0px 1px 4px; display: block;">NO BAD IDEAS</a>
                        </div>
                        
                        <!-- Configuración de cabinas debajo de la imagen -->
                        <div class="template-cabin-container" id="cabinConfigurationSection" style="width: 100%; font-weight: bold; padding: 5px; background-color: #5aa1e39e; border-radius: 3px; box-shadow: 0px 4px 6px -5px; border-bottom: 1px solid #fff; border-right: 1px solid #fff; text-align: center;">
                            <div class="template-cabin-tittle" style="font-size: 14px; font-weight: bold; margin-bottom: 5px;">Cabin Configuration:</div>
                            <div class="template-cabin-details" id="cabinConfiguration" style="font-size: 12px; color: #333;">Cabin Configuration:6 King(s), 2 Twin(s)</div>
                        </div>
        
                    </div>
                </div>

                <!-- Contenedor de bloques de información detallada del yate -->
                <div class="template-info-blocks" id="infoBlocks" style="display: flex; flex-wrap: wrap; gap: 30px; box-sizing: border-box; flex: 1;">
                     <!-- Bloque de costos del charter -->
                    <div class="template-charter-cost-container" style="width: calc(33.333% - 20px); border-radius: 4px; font-size: 12px; box-sizing: border-box; min-width: 250px;">
                        <div class="template-charter-cost" style="font-weight: bold; margin: 0 0 5px 0; padding: 5px; background-color: #5aa1e39e; border-radius: 3px; box-shadow: 0px -4px 6px -5px; border-top: 1px solid #fff; border-right: 1px solid #fff; border-left: 1px solid #fff;">
                            5 nights, 5 Guests: €83,334
                        </div>
                        <div class="template-charter-discount" style="font-weight: bold; margin: 0 0 5px 0; padding: 5px; background-color: #5aa1e39e; border-radius: 3px; box-shadow: 0px 4px 6px -5px; border-bottom: 1px solid #fff; border-right: 1px solid #fff; border-left: 1px solid #fff;">
                            Discount Rate - 50% = €41,667
                        </div>
                        <div class="template-values" style="display: flex; justify-content: space-between; align-items: center; padding: 5px 5px;">
                            <p class="template-vat" style="margin: 0;">VAT (35.5%):</p>
                            <p class="template-vat-result" style="margin: 0;">€14,791.79</p>
                        </div>
                        <div class="template-values" style="display: flex; justify-content: space-between; align-items: center; padding: 5px 5px;">
                            <p class="template-apa" style="margin: 0;">APA (25.5%):</p>
                            <p class="template-apa-result" style="margin: 0;">€10,625.09</p>
                        </div>
                        <div class="template-values" style="display: flex; justify-content: space-between; align-items: center; padding: 5px 5px;">
                            <p class="template-relocation-fee" style="margin: 0;">Relocation fee:</p>
                            <p class="template-relocation-result" style="margin: 0;">€2,000.00</p>
                        </div>
                        <div class="template-values" style="display: flex; justify-content: space-between; align-items: center; padding: 5px 5px;">
                            <p class="template-security-deposit" style="margin: 0;">Security deposit:</p>
                            <p class="template-security-result" style="margin: 0;">€3,000.00</p>
                        </div>
                        <div class="template-subtotal" style="font-weight: bold; margin: 0 0 5px 0; padding: 5px; background-color: #5aa1e39e; border: 1px solid #fff; border-radius: 3px; box-shadow: 0px 0px 3px -1px;">
                            Subtotal for charter: €72,084
                        </div>
                        <div class="template-extras-title" style="font-weight: bold; margin: 0 0 5px 0; padding: 5px; background-color: #5aa1e39e; border: 1px solid #fff; border-radius: 3px; box-shadow: 0px 0px 3px -1px;">
                            Extras:
                        </div>
                        <div class="template-values" style="display: flex; justify-content: space-between; align-items: center; padding: 5px 5px;">
                            <p class="template-extra-x" style="margin: 0;">Extra X:</p>
                            <p class="template-extra-result" style="margin: 0;">€400.00</p>
                        </div>
                        <div class="template-values" style="display: flex; justify-content: space-between; align-items: center; padding: 5px 5px;">
                            <p class="template-extra-y" style="margin: 0;">Extra Y:</p>
                            <p class="template-extra-result" style="margin: 0;">€5,000.00</p>
                        </div>
                        <div class="template-grand-total" style="font-weight: bold; margin: 0 0 5px 0; padding: 5px; background-color: #5aa1e39e; border: 1px solid #fff; border-radius: 3px; box-shadow: 0px 0px 3px -1px;">
                            Grand Total: €77,484
                        </div>
                        <div class="template-values" style="display: flex; justify-content: space-between; align-items: center; padding: 5px 5px;">
                            <p class="template-gratuity-10" style="margin: 0;">Suggested gratuity (10%):</p>
                            <p class="template-gratuity-result" style="margin: 0;">€8,334.00</p>
                        </div>
                        <div class="template-values" style="display: flex; justify-content: space-between; align-items: center; padding: 5px 5px;">
                            <p class="template-gratuity-15" style="margin: 0;">Suggested gratuity (15%):</p>
                            <p class="template-gratuity-result" style="margin: 0;">€12,501.00</p>
                        </div>
                    </div>
                </div>
                <!-- Contenedor de bloques de información detallada del yate -->
                <div class="template-info-blocks" id="infoBlocks2" style="display: flex; flex-wrap: wrap; gap: 30px; box-sizing: border-box; flex: 1;">
                     <!-- Bloque de costos del charter -->
                    <div class="template-charter-cost-container" style="width: calc(33.333% - 20px); border-radius: 4px; font-size: 12px; box-sizing: border-box; min-width: 250px;">
                        <div class="template-charter-cost" style="font-weight: bold; margin: 0 0 5px 0; padding: 5px; background-color: #5aa1e39e; border-radius: 3px; box-shadow: 0px -4px 6px -5px; border-top: 1px solid #fff; border-right: 1px solid #fff; border-left: 1px solid #fff;">
                            5 nights, 5 Guests: €83,334
                        </div>
                        <div class="template-charter-discount" style="font-weight: bold; margin: 0 0 5px 0; padding: 5px; background-color: #5aa1e39e; border-radius: 3px; box-shadow: 0px 4px 6px -5px; border-bottom: 1px solid #fff; border-right: 1px solid #fff; border-left: 1px solid #fff;">
                            Discount Rate - 50% = €41,667
                        </div>
                        <div class="template-values" style="display: flex; justify-content: space-between; align-items: center; padding: 5px 5px;">
                            <p class="template-vat" style="margin: 0;">VAT (35.5%):</p>
                            <p class="template-vat-result" style="margin: 0;">€14,791.79</p>
                        </div>
                        <div class="template-values" style="display: flex; justify-content: space-between; align-items: center; padding: 5px 5px;">
                            <p class="template-apa" style="margin: 0;">APA (25.5%):</p>
                            <p class="template-apa-result" style="margin: 0;">€10,625.09</p>
                        </div>
                        <div class="template-values" style="display: flex; justify-content: space-between; align-items: center; padding: 5px 5px;">
                            <p class="template-relocation-fee" style="margin: 0;">Relocation fee:</p>
                            <p class="template-relocation-result" style="margin: 0;">€2,000.00</p>
                        </div>
                        <div class="template-values" style="display: flex; justify-content: space-between; align-items: center; padding: 5px 5px;">
                            <p class="template-security-deposit" style="margin: 0;">Security deposit:</p>
                            <p class="template-security-result" style="margin: 0;">€3,000.00</p>
                        </div>
                        <div class="template-subtotal" style="font-weight: bold; margin: 0 0 5px 0; padding: 5px; background-color: #5aa1e39e; border: 1px solid #fff; border-radius: 3px; box-shadow: 0px 0px 3px -1px;">
                            Subtotal for charter: €72,084
                        </div>
                        <div class="template-extras-title" style="font-weight: bold; margin: 0 0 5px 0; padding: 5px; background-color: #5aa1e39e; border: 1px solid #fff; border-radius: 3px; box-shadow: 0px 0px 3px -1px;">
                            Extras:
                        </div>
                        <div class="template-values" style="display: flex; justify-content: space-between; align-items: center; padding: 5px 5px;">
                            <p class="template-extra-x" style="margin: 0;">Extra X:</p>
                            <p class="template-extra-result" style="margin: 0;">€400.00</p>
                        </div>
                        <div class="template-values" style="display: flex; justify-content: space-between; align-items: center; padding: 5px 5px;">
                            <p class="template-extra-y" style="margin: 0;">Extra Y:</p>
                            <p class="template-extra-result" style="margin: 0;">€5,000.00</p>
                        </div>
                        <div class="template-grand-total" style="font-weight: bold; margin: 0 0 5px 0; padding: 5px; background-color: #5aa1e39e; border: 1px solid #fff; border-radius: 3px; box-shadow: 0px 0px 3px -1px;">
                            Grand Total: €77,484
                        </div>
                        <div class="template-values" style="display: flex; justify-content: space-between; align-items: center; padding: 5px 5px;">
                            <p class="template-gratuity-10" style="margin: 0;">Suggested gratuity (10%):</p>
                            <p class="template-gratuity-result" style="margin: 0;">€8,334.00</p>
                        </div>
                        <div class="template-values" style="display: flex; justify-content: space-between; align-items: center; padding: 5px 5px;">
                            <p class="template-gratuity-15" style="margin: 0;">Suggested gratuity (15%):</p>
                            <p class="template-gratuity-result" style="margin: 0;">€12,501.00</p>
                        </div>
                    </div>
                </div>
                <!-- Contenedor de bloques de información detallada del yate -->
                <div class="template-info-blocks" id="infoBlocks3" style="display: flex; flex-wrap: wrap; gap: 30px; box-sizing: border-box; flex: 1;">
                     <!-- Bloque de costos del charter -->
                    <div class="template-charter-cost-container" style="width: calc(33.333% - 20px); border-radius: 4px; font-size: 12px; box-sizing: border-box; min-width: 250px;">
                        <div class="template-charter-cost" style="font-weight: bold; margin: 0 0 5px 0; padding: 5px; background-color: #5aa1e39e; border-radius: 3px; box-shadow: 0px -4px 6px -5px; border-top: 1px solid #fff; border-right: 1px solid #fff; border-left: 1px solid #fff;">
                            5 nights, 5 Guests: €83,334
                        </div>
                        <div class="template-charter-discount" style="font-weight: bold; margin: 0 0 5px 0; padding: 5px; background-color: #5aa1e39e; border-radius: 3px; box-shadow: 0px 4px 6px -5px; border-bottom: 1px solid #fff; border-right: 1px solid #fff; border-left: 1px solid #fff;">
                            Discount Rate - 50% = €41,667
                        </div>
                        <div class="template-values" style="display: flex; justify-content: space-between; align-items: center; padding: 5px 5px;">
                            <p class="template-vat" style="margin: 0;">VAT (35.5%):</p>
                            <p class="template-vat-result" style="margin: 0;">€14,791.79</p>
                        </div>
                        <div class="template-values" style="display: flex; justify-content: space-between; align-items: center; padding: 5px 5px;">
                            <p class="template-apa" style="margin: 0;">APA (25.5%):</p>
                            <p class="template-apa-result" style="margin: 0;">€10,625.09</p>
                        </div>
                        <div class="template-values" style="display: flex; justify-content: space-between; align-items: center; padding: 5px 5px;">
                            <p class="template-relocation-fee" style="margin: 0;">Relocation fee:</p>
                            <p class="template-relocation-result" style="margin: 0;">€2,000.00</p>
                        </div>
                        <div class="template-values" style="display: flex; justify-content: space-between; align-items: center; padding: 5px 5px;">
                            <p class="template-security-deposit" style="margin: 0;">Security deposit:</p>
                            <p class="template-security-result" style="margin: 0;">€3,000.00</p>
                        </div>
                        <div class="template-subtotal" style="font-weight: bold; margin: 0 0 5px 0; padding: 5px; background-color: #5aa1e39e; border: 1px solid #fff; border-radius: 3px; box-shadow: 0px 0px 3px -1px;">
                            Subtotal for charter: €72,084
                        </div>
                        <div class="template-extras-title" style="font-weight: bold; margin: 0 0 5px 0; padding: 5px; background-color: #5aa1e39e; border: 1px solid #fff; border-radius: 3px; box-shadow: 0px 0px 3px -1px;">
                            Extras:
                        </div>
                        <div class="template-values" style="display: flex; justify-content: space-between; align-items: center; padding: 5px 5px;">
                            <p class="template-extra-x" style="margin: 0;">Extra X:</p>
                            <p class="template-extra-result" style="margin: 0;">€400.00</p>
                        </div>
                        <div class="template-values" style="display: flex; justify-content: space-between; align-items: center; padding: 5px 5px;">
                            <p class="template-extra-y" style="margin: 0;">Extra Y:</p>
                            <p class="template-extra-result" style="margin: 0;">€5,000.00</p>
                        </div>
                        <div class="template-grand-total" style="font-weight: bold; margin: 0 0 5px 0; padding: 5px; background-color: #5aa1e39e; border: 1px solid #fff; border-radius: 3px; box-shadow: 0px 0px 3px -1px;">
                            Grand Total: €77,484
                        </div>
                        <div class="template-values" style="display: flex; justify-content: space-between; align-items: center; padding: 5px 5px;">
                            <p class="template-gratuity-10" style="margin: 0;">Suggested gratuity (10%):</p>
                            <p class="template-gratuity-result" style="margin: 0;">€8,334.00</p>
                        </div>
                        <div class="template-values" style="display: flex; justify-content: space-between; align-items: center; padding: 5px 5px;">
                            <p class="template-gratuity-15" style="margin: 0;">Suggested gratuity (15%):</p>
                            <p class="template-gratuity-result" style="margin: 0;">€12,501.00</p>
                        </div>
                    </div>
                </div>
            </div>
    </div>