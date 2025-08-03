// Archivo modules\mail\mail.js

// La clase MailComposer se carga globalmente desde el archivo MailComposer.js
// No es necesario importarla como un módulo ES6

// Variable global para la instancia de MailComposer
let mailComposer;
let savedScrollTopWindow;
let savedScrollTopEditor;

// Definir aquí para que esté disponible globalmente dentro del IIFE
function saveFormContent() {
    // Guardar contenido del editor y campos del formulario usando storage.js
    const fieldsToSave = [
        'mail_content', 'mail_to', 'mail_cc', 'mail_bcc', 'mail_subject',
        'fontSelect', 'fontSizeSelect', 'textColorBtn', 'bgColorBtn'
    ];
    const selectors = {
        'mail_content': '#contenido', // Guardará innerHTML
        'mail_to': '#correo-destino',
        'mail_cc': '#correo-cc',
        'mail_bcc': '#correo-bcc',
        'mail_subject': '#asunto',
        'fontSelect': '#fontSelect',
        'fontSizeSelect': '#fontSizeSelect',
        'textColorBtn': '#textColorBtn',
        'bgColorBtn': '#bgColorBtn'
    };

    saveFormData('mail_form', {
        fields: fieldsToSave,
        selectors: selectors
        // No necesitamos beforeSave si guardamos los elementos visibles directamente
    });
    // console.log('Contenido del formulario guardado usando storage.js'); // Opcional: mantener para debug
}

function restoreFormContent() {
    // Restaurar contenido del editor y campos del formulario usando storage.js
     const fieldsToRestore = [
        'mail_content', 'mail_to', 'mail_cc', 'mail_bcc', 'mail_subject',
        'fontSelect', 'fontSizeSelect', 'textColorBtn', 'bgColorBtn'
    ];
    const selectors = {
        'mail_content': '#contenido', // Se restaura como innerHTML
        'mail_to': '#correo-destino',
        'mail_cc': '#correo-cc',
        'mail_bcc': '#correo-bcc',
        'mail_subject': '#asunto',
        'fontSelect': '#fontSelect',
        'fontSizeSelect': '#fontSizeSelect',
        'textColorBtn': '#textColorBtn',
        'bgColorBtn': '#bgColorBtn'
    };

    const savedData = restoreFormData('mail_form', {
        fields: fieldsToRestore,
        selectors: selectors
    });
    
    if (savedData) {
         // Aplicar estilo de fuente restaurado al selector
         const fontSelectEl = document.querySelector(selectors.fontSelect);
         if (fontSelectEl && savedData.fontSelect) {
             fontSelectEl.style.fontFamily = savedData.fontSelect;
         }
        // console.log('Contenido del formulario restaurado usando storage.js'); // Opcional: mantener para debug
    }
}


(function($){
    $(document).ready(function() {
        console.log("Módulo Mail cargado correctamente.");
        
        // Inicializar el compositor de correo
        mailComposer = new MailComposer({
            editorId: 'contenido',
            onContentChange: handleContentChange,
            onError: handleMailError
        });
        
        // Inicializar el editor y sus controles
        mailComposer.initializeEditor(); // Esto llama a updateFontSelector internamente
        
        // Callbacks para los eventos de MailComposer
        function handleContentChange(content) {
            // console.log('Contenido del correo actualizado'); // Opcional
            // Guardar automáticamente al cambiar contenido del editor
             saveFormContent();
        }
        
        function handleMailError(error) {
            console.error('Error en el módulo de correo:', error);
            // Aquí puedes añadir lógica para manejar errores
        }
        
        // Conectar con el sistema de eventos si está disponible
        if (typeof window.eventBus !== 'undefined') {
            window.eventBus.subscribe('template:created', (data) => {
                console.log('Plantilla creada, actualizando editor de correo:', data);
                // Aquí puedes añadir lógica para actualizar el editor con datos de la plantilla
            });
        }
                
        // Función para aplicar comandos de formato al editor
        function applyCommand(command, value = null) {
            // Usar el método de la instancia de MailComposer
            mailComposer.applyCommand(command, value);
            // El guardado ahora se hace en el listener 'input' del editor o 'change' de los controles
        }
        
        // --- Event Listeners para Controles de Formato ---

        // Función reutilizable para insertar enlace (MODIFICADA para usar modal y contexto)
        function insertLinkHandler(fromContextMenu = false) {
            console.log(`insertLinkHandler called. From context menu: ${fromContextMenu}`); // Log entry point
            const editor = mailComposer.editor;
            const selection = window.getSelection();
            savedRangeForLink = null; // Resetear antes de guardar

            if (fromContextMenu && rangeFromContextMenu) {
                // If called from context menu and we successfully saved a range earlier
                savedRangeForLink = rangeFromContextMenu.cloneRange();
                console.log("Using pre-saved range from context menu:", savedRangeForLink); // Debug
                rangeFromContextMenu = null; // Clear the temporary range
            } else if (!fromContextMenu) {
                // Original logic for toolbar button or if context menu failed to save range
                console.log("Attempting to get current selection for toolbar/fallback."); // Debug
                if (selection.rangeCount > 0) {
                    const range = selection.getRangeAt(0);
                    const isInsideEditor = editor.contains(range.commonAncestorContainer);
                    console.log("Is selection inside editor?", isInsideEditor, "Container:", range.commonAncestorContainer); // Log container check
                    if (isInsideEditor) {
                        savedRangeForLink = range.cloneRange();
                        console.log("Selection saved for link modal (toolbar/fallback):", savedRangeForLink); // Debug
                    } else {
                        console.log("Selection is outside editor, not saving range (toolbar/fallback)."); // Log if outside
                    }
                } else {
                    console.log("No selection range found (toolbar/fallback)."); // Log if no range
                }
            } else {
                 console.log("Called from context menu, but no pre-saved range available."); // Debug
                 // Optionally, could try to get current selection as a last resort, but it's likely wrong.
            }

            // Guardar la posición de desplazamiento actual
            if (editor) { 
                savedScrollTopWindow = window.pageYOffset || document.documentElement.scrollTop;
                savedScrollTopEditor = editor.scrollTop;
                console.log(`Scroll positions saved: window=${savedScrollTopWindow}, editor=${savedScrollTopEditor}`); // Debug
            } else {
                console.warn("Editor not found in insertLinkHandler, cannot save scroll positions.");
            }

            // 2. Mostrar el modal personalizado (common part)
            showLinkModal();

            // La lógica de aplicar el enlace ahora está en handleLinkModalOk
        }

        // Función reutilizable para insertar imagen
        // Función reutilizable para insertar imagen
        function insertImageHandler() {
            const editor = mailComposer.editor;
            let currentSavedScrollTopWindow;
            let currentSavedScrollTopEditor;

            if (editor) {
                currentSavedScrollTopWindow = window.pageYOffset || document.documentElement.scrollTop;
                currentSavedScrollTopEditor = editor.scrollTop;
                console.log(`Scroll positions saved before image prompt: window=${currentSavedScrollTopWindow}, editor=${currentSavedScrollTopEditor}`);
            }

            const url = prompt("Introduce la URL de la imagen:", "http://");
            if (url) {
                // Ensure editor has focus after prompt, as prompt can take focus away
                if (editor) {
                    editor.focus();
                }

                if (rangeFromContextMenu) {
                    const range = rangeFromContextMenu.cloneRange(); // Usar el rango guardado
                    rangeFromContextMenu = null; // Limpiar inmediatamente

                    console.log("Attempting to insert image via context menu. Range details:",
                        "StartNode:", range.startContainer, "StartOffset:", range.startOffset,
                        "EndNode:", range.endContainer, "EndOffset:", range.endOffset,
                        "Collapsed:", range.collapsed);

                    try {
                        const img = document.createElement('img');
                        img.src = url;
                        // Opcional: añadir estilos o atributos a la imagen aquí si es necesario
                        // img.style.maxWidth = '100%';

                        range.deleteContents(); // Eliminar cualquier contenido seleccionado en el rango
                        range.insertNode(img); // Insertar la imagen en el rango

                        // Mover el cursor al final de la imagen insertada
                        range.setStartAfter(img);
                        range.setEndAfter(img);
                        range.collapse(false); // Colapsar el rango al final

                        const selection = window.getSelection();
                        if (selection) {
                            selection.removeAllRanges();
                            selection.addRange(range);
                        }
                        console.log("Image inserted manually via context menu range.");
                        handleContentChange(mailComposer.editor.innerHTML); // Guardar contenido
                    } catch (e) {
                        console.error("Error inserting image manually with range:", e);
                        // Fallback a la inserción normal si la manipulación del DOM falla
                        applyCommand("insertImage", url);
                        saveFormContent();
                    }
                } else {
                    // Inserción normal (sin menú contextual)
                    console.log("Image insertion using current editor selection/caret (no context menu range).");
                    applyCommand("insertImage", url);
                    saveFormContent();
                }

                // Restaurar la posición de desplazamiento
                if (editor && typeof currentSavedScrollTopWindow !== 'undefined' && typeof currentSavedScrollTopEditor !== 'undefined') {
                    requestAnimationFrame(() => {
                        window.scrollTo(0, currentSavedScrollTopWindow);
                        editor.scrollTop = currentSavedScrollTopEditor;
                        console.log(`Scroll positions restored after image insertion: window=${currentSavedScrollTopWindow}, editor=${currentSavedScrollTopEditor}`);
                    });
                }
            } else {
                // Si el usuario cancela el prompt, restaurar el foco al editor si es posible,
                // y también restaurar la posición de desplazamiento original.
                if (editor) {
                    editor.focus(); // Restore focus to editor
                    if (typeof currentSavedScrollTopWindow !== 'undefined' && typeof currentSavedScrollTopEditor !== 'undefined') {
                         requestAnimationFrame(() => {
                            window.scrollTo(0, currentSavedScrollTopWindow);
                            editor.scrollTop = currentSavedScrollTopEditor;
                            console.log(`Scroll positions restored after image prompt cancellation: window=${currentSavedScrollTopWindow}, editor=${currentSavedScrollTopEditor}`);
                        });
                    }
                }
            }
        }

        // Selección de fuente 
        $("#fontSelect").change(debounce(function() {
            const selectedFont = $(this).val();
            applyCommand("fontName", selectedFont);
            saveFormContent(); // Guardar al cambiar fuente
            $(this).css('font-family', selectedFont); // Aplicar previsualización
        }, 300)); 
        
        // Selección de tamaño
        $("#fontSizeSelect").change(function() {
            applyCommand("fontSize", $(this).val());
            saveFormContent(); // Guardar al cambiar tamaño
        });
        
        // Botones de color
        $("#textColorBtn").change(function() {
            applyCommand("foreColor", $(this).val());
            saveFormContent(); // Guardar al cambiar color texto
        });
        
        $("#bgColorBtn").change(function() {
            applyCommand("hiliteColor", $(this).val());
            saveFormContent(); // Guardar al cambiar color fondo
        });
        
        // Botón de insertar enlace
        $("#linkBtn").on('mousedown', function(e) {
            // Prevenir que el botón tome el foco y se pierda la selección del editor
            e.preventDefault();

            // Guardar la selección actual ANTES de mostrar el modal
            const editor = mailComposer.editor;
            const selection = window.getSelection();
            savedRangeForLink = null; // Resetear
            if (selection.rangeCount > 0) {
                const range = selection.getRangeAt(0);
                if (editor.contains(range.commonAncestorContainer)) {
                    savedRangeForLink = range.cloneRange();
                    console.log("Selection saved on mousedown (toolbar):", savedRangeForLink);
                } else {
                    console.log("Selection is outside editor on mousedown (toolbar).");
                }
            } else {
                console.log("No selection range found on mousedown (toolbar).");
            }
        }).on('click', function() {
             // Llamar al handler que ahora solo mostrará el modal
             // El rango ya debería estar guardado desde 'mousedown'
             insertLinkHandler(false); // false indica que viene de la toolbar
        });
        
        // Botón de insertar imagen
        $("#imageBtn").click(insertImageHandler); // Usar la función handler

         // Botones de formato básico (Bold, Italic, Underline, etc.)
         // Guardar después de aplicar estos comandos
         $('#boldBtn, #italicBtn, #underlineBtn, #alignLeftBtn, #alignCenterBtn, #alignRightBtn, #bulletListBtn, #numberedListBtn, #indentBtn, #outdentBtn').on('click', function() {
             // El comando se aplica en MailComposer, solo necesitamos guardar
             // Usamos un pequeño delay para asegurar que el comando se aplique antes de guardar
             setTimeout(saveFormContent, 50); 
         });
        
        // Insertar un "template"
        $("#insertTemplate").click(function() {
            const resultDiv = $("#result");
            if (resultDiv.length > 0) {
                // Usar setContent para asegurar que el evento onContentChange se dispare
                const currentContent = mailComposer.getContent();
                mailComposer.setContent(currentContent + "<br><br>" + resultDiv.html() + "<br><br>");
                focusCaretEnd($("#contenido")[0]);
                // saveFormContent se llamará automáticamente por el onContentChange
            } else {
                console.error("No se encontró el div con id 'result'");
            }
        });

        // Función para mover el cursor al final del contenido
        function focusCaretEnd(el) {
            el.focus();
            if (typeof window.getSelection !== "undefined" && typeof document.createRange !== "undefined") {
                const range = document.createRange();
                range.selectNodeContents(el);
                range.collapse(false);
                const sel = window.getSelection();
                sel.removeAllRanges();
                sel.addRange(range);
            }
        }
        
        // --- Menú contextual ---
        let contextMenu = null;
        let contextMenuTrigger = null; 
        
        function createContextMenu() {
            if (contextMenu) return;
            const menuDiv = createElement('div', { className: 'context-menu', role: 'menu', tabindex: '-1' });

            // Helper function to restore selection and apply command
            const applyCommandWithSavedRange = (command, value = null) => {
                const editor = mailComposer.editor;
                // Guardar la posición de desplazamiento actual
                const scrollPos = {
                    x: window.scrollX || window.pageXOffset,
                    y: window.scrollY || window.pageYOffset
                };
                
                // Asegurar el foco ANTES de cualquier operación
                editor.focus();

                let targetRange = null;

                if (rangeFromContextMenu) {
                    const selection = window.getSelection();
                    selection.removeAllRanges();
                    selection.addRange(rangeFromContextMenu);
                    console.log(`Restored range for context menu command: ${command}`, rangeFromContextMenu); // Debug
                    targetRange = rangeFromContextMenu.cloneRange(); // Guardar el rango restaurado
                    rangeFromContextMenu = null; // Clear after use
                } else {
                    console.warn(`No saved range for context menu command: ${command}. Applying to current selection/caret.`);
                    // El foco ya está asegurado arriba
                    // Intentar obtener el rango actual si no se guardó uno
                    const selection = window.getSelection();
                    if (selection.rangeCount > 0) {
                        targetRange = selection.getRangeAt(0).cloneRange();
                    }
                }

                // --- Modificación para Pegar ---
                if (command === 'paste') {
                    if (navigator.clipboard && navigator.clipboard.readText) {
                        navigator.clipboard.readText()
                            .then(text => {
                                if (text) {
                                    // Si teníamos un rango (guardado o actual), úsalo
                                    if (targetRange) {
                                        // Borra el contenido seleccionado (si lo hay) antes de pegar
                                        targetRange.deleteContents();
                                        // Crea un nodo de texto y lo inserta
                                        const textNode = document.createTextNode(text);
                                        targetRange.insertNode(textNode);
                                        // Mueve el cursor al final del texto insertado
                                        targetRange.setStartAfter(textNode);
                                        targetRange.collapse(true);
                                        const selection = window.getSelection();
                                        selection.removeAllRanges();
                                        selection.addRange(targetRange);
                                    } else {
                                        // Fallback: si no hay rango, intenta insertar con execCommand (menos fiable)
                                        console.warn('Paste fallback: No target range found, attempting execCommand.');
                                        document.execCommand('insertText', false, text);
                                    }
                                    // Guardar contenido después de pegar
                                    saveFormContent();
                                }
                            })
                            .catch(err => {
                                console.error('Error al leer el portapapeles:', err);
                                // Opcional: Informar al usuario que el pegado falló o requiere permisos
                                alert('No se pudo pegar desde el portapapeles. Asegúrate de haber otorgado permisos.');
                                // Como fallback, intentar el método antiguo (puede no funcionar)
                                mailComposer.applyCommand(command, value);
                                saveFormContent();
                            });
                    } else {
                        console.warn('La API del portapapeles no está disponible. Usando execCommand("paste").');
                        // Fallback si la API no está soportada
                        mailComposer.applyCommand(command, value);
                        saveFormContent();
                    }
                } else {
                    // Para otros comandos, usar la lógica original
                    mailComposer.applyCommand(command, value);
                    // Save content after applying command
                    saveFormContent();
                }
                
                // Restaurar la posición de desplazamiento después de aplicar el comando y un breve retardo
                setTimeout(() => {
                    window.scrollTo(scrollPos.x, scrollPos.y);
                }, 0); // Un retardo de 0 ms es suficiente para permitir que el navegador procese otros eventos.
                // --------------------------------
            };

            const menuItems = [
                { text: 'Cortar', action: () => applyCommandWithSavedRange('cut') }, // Use helper
                { text: 'Copiar', action: () => applyCommandWithSavedRange('copy') }, // Use helper
                { text: 'Pegar', action: () => applyCommandWithSavedRange('paste') }, // La lógica ahora está dentro de applyCommandWithSavedRange
                { separator: true },
                { text: 'Negrita', action: () => applyCommandWithSavedRange('bold') }, // Use helper
                { text: 'Cursiva', action: () => applyCommandWithSavedRange('italic') }, // Use helper
                { text: 'Subrayado', action: () => applyCommandWithSavedRange('underline') }, // Use helper
                { separator: true },
                { text: 'Insertar enlace', action: () => {
                    console.log("Context menu 'Insertar enlace' clicked. Using range saved from contextmenu event if available.");
                    // insertLinkHandler already uses rangeFromContextMenu internally
                    insertLinkHandler(true); 
                } },
                { text: 'Insertar imagen', action: () => { 
                    // Image insertion uses prompt, focus handled internally
                    insertImageHandler(); 
                } },
                { separator: true },
                { text: 'Lista con viñetas', action: () => applyCommandWithSavedRange('insertUnorderedList') }, // Use helper
                { text: 'Lista numerada', action: () => applyCommandWithSavedRange('insertOrderedList') }, // Use helper
                { separator: true },
                { text: 'Seleccionar todo', action: () => applyCommandWithSavedRange('selectAll') }, // Use helper
                { separator: true }, 
                { text: 'Limpiar Contenido', action: () => {
                    if (confirm('¿Estás seguro de que deseas limpiar todo el contenido del correo?')) {
                        mailComposer.setContent(''); 
                        // saveFormContent called by setContent's onContentChange
                    }
                }}
            ];
            
            createWithFragment(fragment => {
                menuItems.forEach(item => {
                    if (item.separator) {
                         const separator = document.createElement('div');
                         separator.className = 'context-menu-separator';
                         fragment.appendChild(separator);
                    } else {
                        const menuItem = createElement('div', { className: 'context-menu-item', role: 'menuitem', tabindex: '0' }); 
                        menuItem.textContent = item.text;
                         menuItem.addEventListener('click', (e) => {
                            e.stopPropagation(); // Prevent click from propagating to document
                            // Call the action (which now handles range restoration)
                            item.action();
 
                            // Hide the menu AFTER the action is performed
                            hideContextMenu();
                         });
                        menuItem.addEventListener('keydown', (e) => {
                            if (e.key === 'Enter' || e.key === ' ') {
                                e.preventDefault();
                                item.action();
                                hideContextMenu();
                            }
                        });
                        fragment.appendChild(menuItem);
                    }
                });
            }, menuDiv, 'replace');

            menuDiv.addEventListener('keydown', (e) => {
                 if (e.key === 'Escape') { hideContextMenu(); } 
                 else if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
                     e.preventDefault(); 
                     const items = Array.from(menuDiv.querySelectorAll('[role="menuitem"]'));
                     const currentIndex = items.indexOf(document.activeElement);
                     let nextIndex;
                     if (e.key === 'ArrowDown') { nextIndex = (currentIndex + 1) % items.length; } 
                     else { nextIndex = (currentIndex - 1 + items.length) % items.length; }
                     if (items[nextIndex]) { items[nextIndex].focus(); }
                 }
            });
            document.body.appendChild(menuDiv);
            contextMenu = $(menuDiv); 
        }
        
        function showContextMenu(e) {
            contextMenuTrigger = e.target;
            rangeFromContextMenu = null; // Reset before trying to save

            // Check if the right-click happened inside the editor
            const editor = mailComposer.editor;
            if (editor && editor.contains(e.target)) {
                const selection = window.getSelection();
                if (selection.rangeCount > 0) {
                    const range = selection.getRangeAt(0);
                    // Ensure the range is actually within the editor content
                    if (editor.contains(range.commonAncestorContainer)) {
                        rangeFromContextMenu = range.cloneRange();
                        console.log(
                            "Range saved from contextmenu. StartNode:", rangeFromContextMenu.startContainer,
                            "StartOffset:", rangeFromContextMenu.startOffset,
                            "EndNode:", rangeFromContextMenu.endContainer,
                            "EndOffset:", rangeFromContextMenu.endOffset,
                            "Collapsed:", rangeFromContextMenu.collapsed,
                            "CommonAncestor:", rangeFromContextMenu.commonAncestorContainer
                        ); // Debug
                    } else {
                         console.log("Contextmenu range ancestor not in editor."); // Debug
                    }
                } else {
                     console.log("No selection range on contextmenu event."); // Debug
                }
            } else {
                 console.log("Contextmenu event not inside editor."); // Debug
            }


            if (!contextMenu) createContextMenu();
            contextMenu.css({ display: 'block', left: e.pageX + 'px', top: e.pageY + 'px' });
            requestAnimationFrame(() => {
                 const firstItem = contextMenu.find('[role="menuitem"]').first();
                 if (firstItem) { firstItem.focus(); }
            });
            e.preventDefault();
        }
        
        function hideContextMenu() {
            if (contextMenu) contextMenu.css('display', 'none');
            if (contextMenuTrigger) {
                contextMenuTrigger.focus();
                contextMenuTrigger = null; 
            }
        }
        
        $('#contenido').on('contextmenu', showContextMenu);
        // Modificado: No ocultar el menú si se hace clic dentro del modal
        $(document).on('click', function(event) {
            // Si el clic NO fue dentro del menú contextual Y NO fue dentro del modal de enlace
            if (contextMenu && !contextMenu.is(event.target) && contextMenu.has(event.target).length === 0 &&
                linkModal && !linkModal.contains(event.target)) {
                hideContextMenu();
            }
            // Si el clic fue fuera del modal de enlace (y no en el menú), ocultar modal
            // (Esto es opcional, el botón cancelar/escape ya lo hacen)
            // if (linkModal && linkModal.style.display === 'block' && !linkModal.contains(event.target) && event.target.id !== 'linkBtn') {
            //     hideLinkModal();
            // }
        });
        $('#contenido').on('contextmenu', function(e) { e.preventDefault(); });
        
        // --- Fin Menú contextual ---

        // Restaurar contenido al cargar la página
        restoreFormContent(); // Esto ahora restaura todo, incluida la fuente
        
        // Guardar contenido automáticamente cuando se modifica el editor
        // El listener 'input' ahora se maneja a través del callback onContentChange de MailComposer
        // $('#contenido').on('input', function() {
        //     saveFormContent(); 
        // }); 
        
        // Guardar contenido cuando se modifican los campos del formulario
        $('#correo-destino, #correo-cc, #correo-bcc, #asunto').on('input', debounce(saveFormContent, 300)); // Usar debounce aquí también

    });
})(jQuery);


// --- Variables y funciones para el Modal de Enlace ---
let rangeFromContextMenu = null; // Variable para guardar el rango del menú contextual
let linkModal = null;
let savedRangeForLink = null; // Variable específica para guardar el rango del enlace

// Función para crear el modal de enlace si no existe
function createLinkModal() {
    if (linkModal) return;

    linkModal = document.createElement('div');
    linkModal.id = 'link-modal';
    linkModal.className = 'mail-modal'; // Clase genérica para modales si se crean más
    linkModal.style.display = 'none'; // Oculto por defecto
    linkModal.setAttribute('role', 'dialog');
    linkModal.setAttribute('aria-modal', 'true');
    linkModal.setAttribute('aria-labelledby', 'link-modal-title');

    linkModal.innerHTML = `
        <div class="modal-content">
            <h3 id="link-modal-title">Insertar Enlace</h3>
            <label for="link-url-input">URL:</label>
            <input type="url" id="link-url-input" placeholder="https://ejemplo.com" required>
            <div class="modal-actions">
                <button type="button" id="link-ok-btn" class="button button-primary">Aceptar</button>
                <button type="button" id="link-cancel-btn" class="button">Cancelar</button>
            </div>
        </div>
    `;

    document.body.appendChild(linkModal);

    // Event listeners para los botones
    document.getElementById('link-ok-btn').addEventListener('click', handleLinkModalOk);
    document.getElementById('link-cancel-btn').addEventListener('click', hideLinkModal);

    // Cerrar modal al presionar Escape
    linkModal.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            hideLinkModal();
        }
    });
    // Opcional: Cerrar al hacer clic fuera (requiere un overlay)
}

// Función para mostrar el modal de enlace
function showLinkModal() {
    if (!linkModal) createLinkModal();

    const editor = mailComposer.editor;
    let targetRect;

    // Intentar obtener la posición de la selección guardada
    if (savedRangeForLink) {
        targetRect = savedRangeForLink.getBoundingClientRect();
        // Si el rango está colapsado, getBoundingClientRect puede devolver un rect con altura 0.
        // En ese caso, intentar obtener el rect del nodo padre.
        if (targetRect.height === 0 && savedRangeForLink.startContainer) {
             let node = savedRangeForLink.startContainer;
             targetRect = (node.nodeType === Node.TEXT_NODE ? node.parentNode : node).getBoundingClientRect();
        }
    } else if (editor) {
        // Fallback: si no hay rango guardado, intentar usar la posición actual del cursor en el editor
        const selection = window.getSelection();
        if (selection.rangeCount > 0 && editor.contains(selection.anchorNode)) {
             const range = selection.getRangeAt(0);
             targetRect = range.getBoundingClientRect();
             if (targetRect.height === 0 && range.startContainer) {
                 let node = range.startContainer;
                 targetRect = (node.nodeType === Node.TEXT_NODE ? node.parentNode : node).getBoundingClientRect();
             }
        } else {
             // Último fallback: usar el rect del editor mismo
             targetRect = editor.getBoundingClientRect();
        }
    }

    // Mostrar el modal temporalmente fuera de la vista para obtener dimensiones
    linkModal.style.visibility = 'hidden';
    linkModal.style.display = 'block';
    linkModal.style.position = 'absolute'; // Cambiado a absolute
    linkModal.style.transform = 'none'; // Resetear transform

    // Usar requestAnimationFrame puede causar problemas si el cálculo depende del layout que aún no se ha renderizado.
    // Calcular directamente.
    const modalWidth = linkModal.offsetWidth;
    const modalHeight = linkModal.offsetHeight;

    let top, left;

    if (targetRect) {
        // Posicionar debajo del borde inferior del targetRect, considerando el scroll de la página
        top = targetRect.bottom + window.scrollY + 5; // 5px de espacio
        left = targetRect.left + window.scrollX;

        // Ajustar si se sale de la pantalla (viewport + scroll)
        const viewportWidth = window.innerWidth + window.scrollX;
        const viewportHeight = window.innerHeight + window.scrollY;

        if (left + modalWidth > viewportWidth) {
            left = viewportWidth - modalWidth - 10;
        }
        if (top + modalHeight > viewportHeight) {
            // Si no cabe debajo, intentar ponerlo encima
            top = targetRect.top + window.scrollY - modalHeight - 5;
        }
        // Asegurar que no se salga por arriba o izquierda del documento visible
        if (top < window.scrollY) top = window.scrollY + 5;
        if (left < window.scrollX) left = window.scrollX + 5;

    } else {
        // Fallback si no se pudo obtener ningún rect: centrar en la vista actual
        top = window.scrollY + (window.innerHeight - modalHeight) / 2;
        left = window.scrollX + (window.innerWidth - modalWidth) / 2;
    }

    linkModal.style.top = `${top}px`;
    linkModal.style.left = `${left}px`;
    linkModal.style.visibility = 'visible'; // Hacer visible en la posición correcta

    // Añadir foco y selección al campo de URL
    const urlInput = document.getElementById('link-url-input');
    if (urlInput) {
        urlInput.focus();
        urlInput.select(); // Selecciona el contenido actual (ej. "https://")
    }

    // Restaurar el rango guardado si existe
    if (rangeFromContextMenu) {
        const selection = window.getSelection();
        selection.removeAllRanges();
        selection.addRange(rangeFromContextMenu);
    }
}

// Función para ocultar el modal de enlace
function hideLinkModal() {
    if (linkModal) {
        linkModal.style.display = 'none';
    }
    // Devolver el foco al editor después de cerrar
    if (mailComposer && mailComposer.editor) {
        // mailComposer.editor.focus(); // Comentado: el foco se maneja en handleLinkModalOk para evitar saltos de scroll
        // NO restaurar selección ni limpiar savedRangeForLink aquí
        // La restauración/limpieza se maneja en handleLinkModalOk o si se necesita en un cancel explícito
    }
}

// Manejador para el botón OK del modal
function handleLinkModalOk(event) {
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }
    const urlInput = document.getElementById('link-url-input');
    let url = urlInput.value.trim(); // Use let since it might be reassigned
    console.log("handleLinkModalOk called. URL:", url); // Log entry

    // Añadir https:// si no tiene protocolo
    if (url && !url.match(/^([a-z]+:)?\/\//i)) {
        url = 'https://' + url;
    }

    if (url && url !== 'https://') {
        hideLinkModal(); // Oculta el modal primero

        // Restaurar foco y selección ANTES de ejecutar el comando
        if (mailComposer && mailComposer.editor) {
            // Usar setTimeout para permitir que el DOM se actualice y el foco se maneje correctamente
            setTimeout(() => {
                let rangeToRestore = savedRangeForLink; // Copiar a variable local por si acaso
                savedRangeForLink = null; // Limpiar la variable global inmediatamente después de copiarla

                try {
                    if (mailComposer && mailComposer.editor) { // Doble chequeo por si acaso
                        // 1. Ensure editor has focus, without causing a scroll
                        mailComposer.editor.focus({preventScroll: true}); 
                        console.log("Editor explicitly focused with preventScroll. Active element:", document.activeElement);

                        // 2. Restore selection if available
                        if (rangeToRestore) {
                            const selection = window.getSelection();
                            if (mailComposer.editor.contains(rangeToRestore.commonAncestorContainer)) {
                                selection.removeAllRanges();
                                selection.addRange(rangeToRestore);
                                console.log("Selection restored. Selection:", selection.toString());
                            } else {
                                console.warn("Saved range is no longer valid or outside editor. Editor remains focused.");
                            }
                        } else {
                            console.log("No saved range to restore. Editor remains focused.");
                        }

                        // 3. Aplicar el comando
                        const selectionBeforeCommand = window.getSelection();
                        console.log("Selection *just before* applyCommand:", selectionBeforeCommand.toString(), "Range count:", selectionBeforeCommand.rangeCount);
                        
                        mailComposer.applyCommand('createLink', url);
                        console.log("Applied createLink command with URL:", url);

                        // 4. Restaurar scrolls DESPUÉS de aplicar el comando
                        if (typeof savedScrollTopEditor !== 'undefined' && mailComposer && mailComposer.editor) {
                            mailComposer.editor.scrollTop = savedScrollTopEditor;
                            console.log(`Editor scroll restored AFTER command to: ${savedScrollTopEditor}`);
                        }
                        if (typeof savedScrollTopWindow !== 'undefined') {
                            window.scrollTo(0, savedScrollTopWindow);
                            console.log(`Window scroll restored AFTER command to: ${savedScrollTopWindow}`);
                        }

                        // 5. Clean up scroll variables 
                        savedScrollTopWindow = undefined;
                        savedScrollTopEditor = undefined;
                        console.log("Scroll variables cleaned up after command.");
                    }
                } catch (error) {
                     console.error("Error during link application or scroll restoration in setTimeout:", error);
                     // Asegurar la limpieza de variables de scroll incluso en caso de error
                     savedScrollTopWindow = undefined;
                     savedScrollTopEditor = undefined;
                }
            }, 0); // Un retraso de 0ms a menudo funciona

        } else {
            console.error("MailComposer o editor no disponible para aplicar enlace.");
            // Limpiar el rango si no se pudo usar
            savedRangeForLink = null;
        }

    } else {
        // Opcional: Mostrar un mensaje si la URL no es válida o está vacía
        alert('Por favor, introduce una URL válida.');
        urlInput.focus();
    }
}

// --- Fin Variables y funciones para el Modal de Enlace ---
