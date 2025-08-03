// ARCHIVO shared/js/classes/MailComposer.js

/**
 * Clase MailComposer
 * Encapsula la lógica de composición de correos
 * Implementa un patrón de diseño orientado a objetos para mejorar la mantenibilidad
 */
class MailComposer {
    /**
     * Constructor de la clase MailComposer
     * @param {Object} config - Configuración inicial
     * @param {string} config.editorId - ID del elemento editor
     * @param {Function} config.onContentChange - Callback cuando cambia el contenido
     * @param {Function} config.onError - Callback cuando hay un error
     */
    constructor(config = {}) {
        // Configuración por defecto
        this.config = {
            editorId: 'contenido',
            onContentChange: null,
            onError: null,
            ...config
        };
        
        // Estado interno
        this.editor = document.getElementById(this.config.editorId);
        this.fonts = [];
        this.fontsLoaded = false;
        
        // Vincular métodos al contexto actual
        this.applyCommand = this.applyCommand.bind(this);
        // this.loadGoogleFonts = this.loadGoogleFonts.bind(this); // Eliminado
        this.updateFontSelector = this.updateFontSelector.bind(this);
        this.restoreSelectedFont = this.restoreSelectedFont.bind(this);
        this.initializeEditor = this.initializeEditor.bind(this);
        
        // Inicializar sistema de eventos si está disponible
        if (typeof window.eventBus !== 'undefined') {
            this.eventBus = window.eventBus;
            console.log('EventBus conectado a MailComposer');
        }
        
        console.log('MailComposer inicializado');
    }
    
    /**
     * Inicializa el editor y los controles
     */
    initializeEditor() {
        if (!this.editor) {
            console.error(`Editor con ID ${this.config.editorId} no encontrado`);
            return;
        }
        
        // Inicializar botones de formato básico
        document.getElementById('boldBtn')?.addEventListener('click', () => this.applyCommand('bold'));
        document.getElementById('italicBtn')?.addEventListener('click', () => this.applyCommand('italic'));
        document.getElementById('underlineBtn')?.addEventListener('click', () => this.applyCommand('underline'));
        
        // Botones de alineación
        document.getElementById('alignLeftBtn')?.addEventListener('click', () => this.applyCommand('justifyLeft'));
        document.getElementById('alignCenterBtn')?.addEventListener('click', () => this.applyCommand('justifyCenter'));
        document.getElementById('alignRightBtn')?.addEventListener('click', () => this.applyCommand('justifyRight'));
        
        // Botones de listas
        document.getElementById('bulletListBtn')?.addEventListener('click', () => this.applyCommand('insertUnorderedList'));
        document.getElementById('numberedListBtn')?.addEventListener('click', () => this.applyCommand('insertOrderedList'));
        
        // Botones de indentación
        document.getElementById('indentBtn')?.addEventListener('click', () => this.applyCommand('indent'));
        document.getElementById('outdentBtn')?.addEventListener('click', () => this.applyCommand('outdent'));
        
        // Selección de fuente
        const fontSelect = document.getElementById('fontSelect');
        if (fontSelect) {
            fontSelect.addEventListener('change', (e) => {
                const selectedFont = e.target.value;
                this.applyCommand('fontName', selectedFont);
                
                // Guardar la fuente seleccionada usando el sistema centralizado de almacenamiento
                saveFormData('mail_settings', {
                    fields: ['selected_font'],
                    beforeSave: () => {
                        // Preparar los datos antes de guardar
                        document.getElementById('selected_font').value = selectedFont;
                    }
                });
                console.log('Fuente seleccionada guardada:', selectedFont);
                
                // Aplicar la fuente al selector para previsualización
                e.target.style.fontFamily = selectedFont;
            });
        }
        
        // Selección de tamaño
        const fontSizeSelect = document.getElementById('fontSizeSelect');
        if (fontSizeSelect) {
            fontSizeSelect.addEventListener('change', (e) => {
                this.applyCommand('fontSize', e.target.value);
            });
        }
        
        // Botones de color
        const textColorBtn = document.getElementById('textColorBtn');
        if (textColorBtn) {
            textColorBtn.addEventListener('change', (e) => {
                this.applyCommand('foreColor', e.target.value);
            });
        }
        
        const bgColorBtn = document.getElementById('bgColorBtn');
        if (bgColorBtn) {
            bgColorBtn.addEventListener('change', (e) => {
                this.applyCommand('hiliteColor', e.target.value);
            });
        }
        
        // Actualizar selector de fuentes y restaurar configuración (sin Google Fonts)
        this.updateFontSelector([]); 
        this.restoreSelectedFont();
        
        console.log('Editor inicializado correctamente');
    }
    
    /**
     * Aplica un comando de formato al editor
     * @param {string} command - Comando a ejecutar
     * @param {string} value - Valor opcional para el comando
     * @param {object} [execOptions={}] - Opciones adicionales para la ejecución del comando.
     * @param {boolean} [execOptions.preserveFocusAndScroll=false] - Si es true, esta función no gestionará el foco ni el scroll; se asume que el llamador lo hace.
     */
    applyCommand(command, value = null, execOptions = {}) {
        const manageFocusAndScroll = !(execOptions && execOptions.preserveFocusAndScroll);
        let savedWindowScrollY, savedEditorScrollTop;

        if (manageFocusAndScroll) {
            savedWindowScrollY = window.scrollY;
            // Es importante enfocar el editor para que execCommand funcione correctamente.
            // Si el llamador quiere un control de foco más fino (ej. con preventScroll),
            // debe hacerlo antes de llamar a applyCommand con preserveFocusAndScroll = true.
            this.editor.focus(); 
            savedEditorScrollTop = this.editor.scrollTop;
        } else {
            // Si preserveFocusAndScroll es true, se asume que el editor ya tiene el foco
            // y que el scroll será manejado por el llamador.
            // document.execCommand debería funcionar si el editor ya está enfocado.
        }
        
        document.execCommand(command, false, value);
        
        if (manageFocusAndScroll) {
            this.editor.scrollTop = savedEditorScrollTop;
            window.scrollTo(0, savedWindowScrollY);
            
            // Intento adicional para estabilizar el scroll, especialmente si execCommand causa un scroll asíncrono.
            setTimeout(() => {
                window.scrollTo(0, savedWindowScrollY);
            }, 0);
        }
        
        if (typeof this.config.onContentChange === 'function') {
            this.config.onContentChange(this.getContent());
        }
        
        if (this.eventBus) {
            this.eventBus.publish('mail:contentChanged', { content: this.getContent() });
        }
    }

    // loadGoogleFonts() eliminado ya que no son fiables en email

    /**
     * Actualiza el selector de fuentes con estilos de previsualización (solo fuentes seguras)
     * @param {Array<string>} fontsList - Lista de fuentes (ignorada, siempre usa seguras)
     */
    updateFontSelector(fontsList) { // fontsList ya no se usa pero se mantiene la firma por si acaso
        const fontSelect = document.getElementById('fontSelect');
        if (!fontSelect) return;
        
        const currentValue = fontSelect.value || 'Arial';
        
        // Fuentes seguras para email (siempre disponibles)
        const emailSafeFonts = [
            'Arial',
            'Times New Roman',
            'Courier New',
            'Verdana',
            'Georgia',
            'Tahoma',
            'Helvetica'
        ];
        
        // Limpiar selector actual
        fontSelect.innerHTML = '';
        
        // Añadir grupo de fuentes seguras para email
        const systemGroup = document.createElement('optgroup');
        systemGroup.label = 'Fuentes seguras para email';
        
        emailSafeFonts.forEach(font => {
            const option = document.createElement('option');
            option.value = font;
            option.textContent = font;
            option.style.fontFamily = font;
            systemGroup.appendChild(option);
        });
        
        // Añadir grupo al selector
        fontSelect.appendChild(systemGroup);

        // Ya no se añaden fuentes de Google

        // Intentar obtener la fuente guardada directamente de localStorage
        const savedFont = localStorage.getItem('pb_mail_selected_font'); 
        
        // Verificar si la fuente guardada existe en las opciones disponibles
        let fontExists = false;
        if (savedFont) {
            fontExists = Array.from(fontSelect.options).some(option => option.value === savedFont);
        }
        
        // Establecer la fuente si existe, o usar Arial como fallback
        fontSelect.value = (fontExists && savedFont) || currentValue || 'Arial';
        
        // Aplicar estilos para mejorar la visualización del selector
        fontSelect.style.maxWidth = '200px';
        fontSelect.style.fontSize = '14px';
        
        // Aplicar la fuente seleccionada al elemento option correspondiente
        Array.from(fontSelect.options).forEach(option => {
            option.style.fontFamily = option.value;
        });
        
        // Aplicar la fuente seleccionada al selector para previsualización
        fontSelect.style.fontFamily = fontSelect.value;
    }
    
    /**
     * Restaura la fuente seleccionada al cargar la página (usa localStorage directamente)
     */
    restoreSelectedFont() {
        const fontSelect = document.getElementById('fontSelect');
        if (!fontSelect) return;

        const savedFont = localStorage.getItem('pb_mail_selected_font');
        if (savedFont) {
             // Asegurarse de que la fuente guardada exista como opción antes de seleccionarla
             if (Array.from(fontSelect.options).some(option => option.value === savedFont)) {
                fontSelect.value = savedFont;
                fontSelect.style.fontFamily = savedFont;
                console.log('Fuente restaurada desde localStorage:', savedFont);
            } else {
                 console.warn('Fuente guardada "' + savedFont + '" no encontrada en el selector.');
            }
        }
    }
    
    /**
     * Obtiene el contenido actual del editor
     * @returns {string} - Contenido HTML del editor
     */
    getContent() {
        return this.editor?.innerHTML || '';
    }
    
    /**
     * Establece el contenido del editor
     * @param {string} html - Contenido HTML a establecer
     */
    setContent(html) {
        if (this.editor) {
            // Guardar posición de desplazamiento de la ventana
            const windowScrollY = window.scrollY;
            
            this.editor.innerHTML = html;
            
            // Restaurar posición de desplazamiento de la ventana
            window.scrollTo(0, windowScrollY);
            
            // Prevenir comportamiento por defecto que podría causar desplazamiento
            setTimeout(() => {
                window.scrollTo(0, windowScrollY);
            }, 0);
            
            // Notificar cambio si hay callback
            if (typeof this.config.onContentChange === 'function') {
                this.config.onContentChange(html);
            }
            
            // Publicar evento si hay eventBus
            if (this.eventBus) {
                this.eventBus.publish('mail:contentChanged', { content: html });
            }
        }
    }
}

// Exponer la clase globalmente en lugar de exportarla como módulo
window.MailComposer = MailComposer;
