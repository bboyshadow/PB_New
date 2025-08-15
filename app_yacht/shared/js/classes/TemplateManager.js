// ARCHIVO shared/js/classes/TemplateManager.js

/**
 * Clase TemplateManager
 * Encapsula la lógica de gestión de plantillas
 * Implementa un patrón de diseño orientado a objetos para mejorar la mantenibilidad
 */
class TemplateManager {
    /**
     * Constructor de la clase TemplateManager
     * @param {Object} config - Configuración inicial
     * @param {string} config.ajaxUrl - URL para peticiones AJAX
     * @param {string} config.nonce - Nonce de seguridad para peticiones AJAX
     * @param {Function} config.onTemplateCreated - Callback cuando se crea una plantilla
     * @param {Function} config.onTemplateLoaded - Callback cuando se carga una plantilla
     * @param {Function} config.onError - Callback cuando hay un error
     */
    constructor(config = {}) {
        // Configuración por defecto
        this.config = {
            ajaxUrl: '',
            nonce: '',
            onTemplateCreated: null,
            onTemplateLoaded: null,
            onError: null,
            ...config
        };
        
        // Estado interno
        this.isCreating = false;
        this.currentTemplate = null;
        
        // Vincular métodos al contexto actual
        this.createTemplate = this.createTemplate.bind(this);
        this.loadTemplate = this.loadTemplate.bind(this);
        this.collectFormData = this.collectFormData.bind(this);
        this.toggleOneDayCharter = this.toggleOneDayCharter.bind(this);
        
        // Inicializar sistema de eventos si está disponible
        if (typeof window.eventBus !== 'undefined') {
            this.eventBus = window.eventBus;
        }
    }
    
    /**
     * Recolecta los datos del formulario para crear una plantilla
     * @returns {FormData} - Objeto FormData con los datos recolectados
     */
    collectFormData() {
        const formData = new FormData();
        formData.append('action', 'createTemplate');
        formData.append('nonce', this.config?.nonce || '');
        
        // Recolectar URL del yate
        const yachtUrl = document.getElementById('yacht-url')?.value.trim() || '';
        formData.append('yachtUrl', yachtUrl);
        
        // Recolectar tipo de plantilla
        const templateSelector = document.getElementById('templateSelector');
        const selectedTemplate = templateSelector ? templateSelector.value : '';
        const finalTemplate = selectedTemplate || 'default-template';
        formData.append('template', finalTemplate);
        
        // Textos de temporada
        const lowSeasonText = document.getElementById('lowmixedResult')?.textContent || '';
        const highSeasonText = document.getElementById('highmixedResult')?.textContent || '';
        formData.append('lowSeasonText', lowSeasonText);
        formData.append('highSeasonText', highSeasonText);
        
        // Currency
        const currency = document.getElementById('currency')?.value || '';
        formData.append('currency', currency);
        
        // Actualizar símbolos de moneda si hay un selector de moneda
        if (document.getElementById('currency') && typeof updateCurrencySymbols === 'function') {
            updateCurrencySymbols();
        }
        
        // VAT, APA, etc.
        if (document.getElementById('vatCheck')?.checked) {
            formData.append('vatRate', document.getElementById('vatRate')?.value || '');
        }
        if (document.getElementById('apaCheck')?.checked) {
            formData.append('apaAmount', document.getElementById('apaAmount')?.value || '');
        }
        if (document.getElementById('apaPercentageCheck')?.checked) {
            formData.append('apaPercentage', document.getElementById('apaPercentage')?.value || '');
        }
        if (document.getElementById('relocationCheck')?.checked) {
            formData.append('relocationFee', document.getElementById('relocationFee')?.value || '');
        }
        if (document.getElementById('securityCheck')?.checked) {
            formData.append('securityFee', document.getElementById('securityFee')?.value || '');
        }
        
        // One Day Charter
        const enableOneDayCharter = document.getElementById('enableOneDayCharter');
        const isOneDayActive = (enableOneDayCharter && enableOneDayCharter.checked) ? '1' : '0';
        formData.append('enableOneDayCharter', isOneDayActive);
        
        // Add + Expenses to Base Charter Rate
        const enableExpenses = document.getElementById('enableExpenses');
        const isExpensesActive = (enableExpenses && enableExpenses.checked) ? '1' : '0';
        formData.append('enableExpenses', isExpensesActive);
        
        // Mixed Seasons
        const enableMixedSeasons = document.getElementById('enableMixedSeasons');
        const isMixedActive = (enableMixedSeasons && enableMixedSeasons.checked) ? '1' : '0';
        formData.append('enableMixedSeasons', isMixedActive);
        
        // Hide Elements
        formData.append('hideVAT', document.getElementById('hideVAT')?.checked ? '1' : '0');
        formData.append('hideAPA', document.getElementById('hideAPA')?.checked ? '1' : '0');
        formData.append('hideRelocation', document.getElementById('hideRelocation')?.checked ? '1' : '0');
        formData.append('hideSecurity', document.getElementById('hideSecurity')?.checked ? '1' : '0');
        formData.append('hideExtras', document.getElementById('hideExtras')?.checked ? '1' : '0');
        formData.append('hideGratuity', document.getElementById('hideGratuity')?.checked ? '1' : '0');
        
        // Charter Rates
        const charterRateGroups = document.querySelectorAll('.charter-rate-group');
        if (charterRateGroups.length > 0) {
            charterRateGroups.forEach((group, i) => {
                const guests = group.querySelector('input[name="guests"]')?.value || '';
                let nights = '';
                let hours = '';
                
                if (isOneDayActive === '1') {
                    hours = group.querySelector('input[name="hours"]')?.value || '';
                } else {
                    nights = group.querySelector('input[name="nights"]')?.value || '';
                }
                
                const baseRate = group.querySelector('input[name="baseRate"]')?.value || '';
                
                const discountContainer = group.querySelector('.discount-container');
                const discountActive = discountContainer && discountContainer.style.display !== 'none';
                let discountType = '';
                let discountAmount = '';
                if (discountActive) {
                    discountType = group.querySelector('select[name="discountType"]')?.value || '';
                    discountAmount = group.querySelector('input[name="discountAmount"]')?.value || '';
                }
                
                formData.append(`charterRates[${i}][guests]`, guests);
                formData.append(`charterRates[${i}][nights]`, nights);
                formData.append(`charterRates[${i}][hours]`, hours);
                formData.append(`charterRates[${i}][baseRate]`, baseRate);
                formData.append(`charterRates[${i}][discountType]`, discountType);
                formData.append(`charterRates[${i}][discountAmount]`, discountAmount);
                formData.append(`charterRates[${i}][discountActive]`, discountActive ? '1' : '0');

                // Promotion handling
                const promotionContainer = group.querySelector('.promotion-container');
                const promotionActive = promotionContainer && promotionContainer.style.display !== 'none';
                let promotionNights = '';
                if (promotionActive) {
                    promotionNights = group.querySelector('input[name="promotionNights"]')?.value || '';
                }
                formData.append(`charterRates[${i}][promotionNights]`, promotionNights);
                formData.append(`charterRates[${i}][promotionActive]`, promotionActive ? '1' : '0');
            });
        }
        
        // Expandir
        const extrasContainer = document.getElementById('extrasContainer');
        if (extrasContainer) {
            const extraGroups = extrasContainer.querySelectorAll('.extra-group');
            extraGroups.forEach((extra, i) => {
                const extraName = extra.querySelector('input[name="extraName"]')?.value || '';
                const extraCost = extra.querySelector('input[name="extraCost"]')?.value || '';
                formData.append(`extras[${i}][extraName]`, extraName);
                formData.append(`extras[${i}][extraCost]`, extraCost);
            });
            
            // Guest Fee
            const extraPerPersonGroups = extrasContainer.querySelectorAll('.extra-per-person-group');
            extraPerPersonGroups.forEach((extra, i) => {
                const extraName = extra.querySelector('input[name="extraPerPersonName"]')?.value || '';
                const extraTotal = extra.querySelector('input[name="extraPerPersonTotal"]')?.value || '';
                // Añadimos el guest fee como un extra normal para la plantilla
                formData.append(`extras[${extraGroups.length + i}][extraName]`, extraName);
                formData.append(`extras[${extraGroups.length + i}][extraCost]`, extraTotal);
            });
        }
        
        // VAT Mix
        const vatRateMixCheckbox = document.getElementById('vatRateMix');
        const vatRateMixEnabled = vatRateMixCheckbox && vatRateMixCheckbox.checked ? '1' : '0';
        formData.append('vatRateMix', vatRateMixEnabled);
        if (vatRateMixEnabled === '1') {
            const vatMixItems = document.querySelectorAll('.country-vat-item-wrapper');
            vatMixItems.forEach((item) => {
                const country = item.querySelector('input[name="vatCountryName[]"]')?.value || '';
                const rate = item.querySelector('input[name="vatRate[]"]')?.value || '';
                const nights = item.querySelector('input[name="vatNights[]"]')?.value || '';
                formData.append('vatCountryName[]', country);
                formData.append('vatRate[]', rate);
                formData.append('vatNights[]', nights);
            });
        }

        return formData;
    }
    
    /**
     * Crea una nueva plantilla usando async/await
     * @returns {Promise<Object>} - Promesa que resuelve con los datos de la plantilla creada
     */
    async createTemplate() {
        try {
            // Validación
            if (typeof validateFields === 'function') {
                const isValid = validateFields();
                try { typeof validateFieldsWithWarnings === 'function' && validateFieldsWithWarnings(); } catch (e) {}
                if (!isValid) return; // Detener si la validación estricta falla
            }
            
            // Indicar que estamos creando una plantilla
            this.isCreating = true;
            
            // Usar UI helper si está disponible para indicar estado de carga
            try { window.AppYacht?.ui?.setLoading?.(true); } catch (e) {}
            
            // Controlar estado del botón Create Template
            const createBtn = document.getElementById('createTemplateButton');
            if (createBtn) {
                createBtn.dataset.prevText = createBtn.textContent || 'Create Template';
                createBtn.textContent = 'Creating...';
                createBtn.disabled = true;
            }
            
            // Notificar inicio si hay callback
            if (typeof this.config.onTemplateCreated === 'function') {
                this.config.onTemplateCreated();
            }
            
            // Publicar evento si hay eventBus
            if (this.eventBus) {
                this.eventBus.publish('template:creating', {});
            }
            
            // Recolectar datos del formulario
            const formData = this.collectFormData();
            
            // Verificar que tenemos los datos mínimos necesarios
            const yachtUrl = formData.get('yachtUrl');
            if (!yachtUrl) {
                throw new Error('Yacht URL is required');
            }
            
            // Verificar que tenemos un nonce válido
            const nonce = formData.get('nonce');
            if (!nonce) {
                throw new Error('Security error: nonce not available');
            }
            
            // Enviar petición usando fetch con async/await
            const response = await fetch(this.config.ajaxUrl, {
                method: 'POST',
                body: formData
            });
            
            if (!response.ok) {
                throw new Error(`Server response error: ${response.status}`);
            }
            
            const result = await response.json();
            
            if (!result.success) {
                throw new Error(result.data || 'Error creating template');
            }
            
            // Actualizar la UI si hay un contenedor de resultado
            const resultDiv = document.getElementById('result');
            if (resultDiv && result.data) {
                resultDiv.innerHTML = result.data;
            }
            
            // Habilitar el botón de copiar si existe
            const copyBtn = document.getElementById('copyTemplateButton');
            if (copyBtn) {
                copyBtn.disabled = false;
                copyBtn.style.display = 'block';
            }
            
            // Guardar la plantilla actual
            this.currentTemplate = result.data;
            
            // Usar UI helper si está disponible para notificar éxito
            try { window.AppYacht?.ui?.notifySuccess?.('Template created successfully'); } catch (e) {}
            
            // Notificar éxito si hay callback
            if (typeof this.config.onTemplateCreated === 'function') {
                this.config.onTemplateCreated(result.data);
            }
            
            // Publicar evento si hay eventBus
            if (this.eventBus) {
                this.eventBus.publish('template:created', result.data);
            }
            
            return result.data;
        } catch (error) {
            // Evitar ruido en consola para errores de validación previsibles
            if (!error.message || !error.message.includes('Validation failed')) {
                (window.AppYacht?.error || console.error)('Error creating template:', error);
            }
            
            // Usar UI helper si está disponible para notificar error, fallback a #errorMessage
            try { 
                window.AppYacht?.ui?.notifyError?.('Error creating template: ' + error.message); 
            } catch (e) {
                // Fallback a #errorMessage
                const errorEl = document.getElementById('errorMessage');
                if (errorEl) {
                    errorEl.textContent = 'Error creating template: ' + error.message;
                    errorEl.style.display = 'block';
                    errorEl.classList.add('text-danger');
                }
            }
            
            // Notificar error si hay callback
            if (typeof this.config.onError === 'function') {
                this.config.onError(error);
            }
            
            // Publicar evento si hay eventBus
            if (this.eventBus) {
                this.eventBus.publish('template:error', { error: error.message });
            }
            
            throw error;
        } finally {
            this.isCreating = false;
            
            // Desactivar estado de carga
            try { window.AppYacht?.ui?.setLoading?.(false); } catch (e) {}
            
            // Restaurar estado del botón Create Template
            const createBtn = document.getElementById('createTemplateButton');
            if (createBtn) {
                createBtn.textContent = createBtn.dataset.prevText || 'Create Template';
                createBtn.disabled = false;
            }
        }
    }
    
    /**
     * Carga una plantilla existente usando async/await
     * @param {string} templateId - ID de la plantilla a cargar
     * @returns {Promise<Object>} - Promesa que resuelve con los datos de la plantilla cargada
     */
    async loadTemplate(templateId) {
        try {
            // Usar UI helper si está disponible para indicar estado de carga
            try { window.AppYacht?.ui?.setLoading?.(true); } catch (e) {}
            
            // Controlar estado del selector de templates
            const templateSelector = document.getElementById('templateSelector');
            const saveTemplateSelector = document.getElementById('saveTemplateSelector');
            if (templateSelector) templateSelector.disabled = true;
            if (saveTemplateSelector) saveTemplateSelector.disabled = true;
            
            // Notificar inicio si hay callback
            if (typeof this.config.onTemplateLoaded === 'function') {
                this.config.onTemplateLoaded();
            }
            
            // Publicar evento si hay eventBus
            if (this.eventBus) {
                this.eventBus.publish('template:loading', { templateId });
            }
            
            // Crear FormData para la petición
            const formData = new FormData();
            formData.append('action', 'load_template');
            formData.append('nonce', this.config?.nonce || '');
            formData.append('templateId', templateId);
            
            // Enviar petición usando fetch con async/await
            const response = await fetch(this.config.ajaxUrl, {
                method: 'POST',
                body: formData
            });
            
            if (!response.ok) {
                throw new Error(`Server response error: ${response.status}`);
            }
            
            const result = await response.json();
            
            if (!result.success) {
                throw new Error(result.data || 'Error loading template');
            }
            
            // Guardar la plantilla actual
            this.currentTemplate = result.data;
            
            // Usar UI helper si está disponible para notificar éxito
            try { window.AppYacht?.ui?.notifySuccess?.('Template loaded successfully'); } catch (e) {}
            
            // Notificar éxito si hay callback
            if (typeof this.config.onTemplateLoaded === 'function') {
                this.config.onTemplateLoaded(result.data);
            }
            
            // Publicar evento si hay eventBus
            if (this.eventBus) {
                this.eventBus.publish('template:loaded', result.data);
            }
            
            return result.data;
        } catch (error) {
            (window.AppYacht?.error || console.error)('Error loading template:', error);
            
            // Usar UI helper si está disponible para notificar error, fallback a #errorMessage
            try { 
                window.AppYacht?.ui?.notifyError?.('Error loading template: ' + error.message); 
            } catch (e) {
                // Fallback a #errorMessage
                const errorEl = document.getElementById('errorMessage');
                if (errorEl) {
                    errorEl.textContent = 'Error loading template: ' + error.message;
                    errorEl.style.display = 'block';
                    errorEl.classList.add('text-danger');
                }
            }
            
            // Notificar error si hay callback
            if (typeof this.config.onError === 'function') {
                this.config.onError(error);
            }
            if (this.eventBus) {
                this.eventBus.publish('template:error', { error: error.message });
            }
            throw error;
        } finally {
            // Desactivar estado de carga
            try { window.AppYacht?.ui?.setLoading?.(false); } catch (e) {}
            
            // Restaurar estado de los selectores
            const templateSelector = document.getElementById('templateSelector');
            const saveTemplateSelector = document.getElementById('saveTemplateSelector');
            if (templateSelector) templateSelector.disabled = false;
            if (saveTemplateSelector) saveTemplateSelector.disabled = false;
        }
    }
    
    /**
     * Alterna la visibilidad de los campos de One Day Charter
     * @param {boolean} isEnabled - Indica si One Day Charter está habilitado
     */
    toggleOneDayCharter(isEnabled) {
        const oneDayFields = document.querySelectorAll('.one-day-charter-field');
        oneDayFields.forEach(field => {
            field.style.display = isEnabled ? 'block' : 'none';
        });
        
        // Hide promotion UI when one-day is enabled
        const charterRateGroups = document.querySelectorAll('.charter-rate-group');
        charterRateGroups.forEach(group => {
            const promotionContainer = group.querySelector('.promotion-container');
            const promotionBtn = group.querySelector('.toggle-promotion-btn');
            if (isEnabled) {
                if (promotionContainer) {
                    promotionContainer.style.display = 'none';
                    const promotionNights = promotionContainer.querySelector('input[name="promotionNights"]');
                    if (promotionNights) promotionNights.value = '';
                }
                if (promotionBtn) promotionBtn.style.display = 'none';
            } else {
                if (promotionBtn) promotionBtn.style.display = '';
            }
        });
        
        // Publicar evento si hay eventBus
        if (this.eventBus) {
            this.eventBus.publish('template:oneDayCharter', { isEnabled });
        }
    }
}

// Exponer la clase globalmente en lugar de exportarla como módulo
window.TemplateManager = TemplateManager;