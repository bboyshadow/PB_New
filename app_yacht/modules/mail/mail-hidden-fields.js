// ARCHIVO: modules/mail/mail-hidden-fields.js

/**
 * Este script crea campos ocultos necesarios para el funcionamiento
 * del sistema centralizado de almacenamiento (storage.js)
 */
(function() {
    document.addEventListener('DOMContentLoaded', function() {
        // Verificar si el contenedor de correo existe
        const mailContainer = document.querySelector('.mail-container, #mail-form-container');
        if (!mailContainer) return;
        
        // Crear un contenedor para los campos ocultos
        const hiddenContainer = document.createElement('div');
        hiddenContainer.id = 'mail-hidden-fields';
        hiddenContainer.style.display = 'none';
        
        // Definir los campos ocultos necesarios
        const hiddenFields = [
            'mail_content',
            'mail_to',
            'mail_cc',
            'mail_bcc',
            'mail_subject',
            'selected_font'
        ];
        
        // Crear los campos ocultos
        hiddenFields.forEach(fieldId => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.id = fieldId;
            input.name = fieldId;
            hiddenContainer.appendChild(input);
        });
        
        // Añadir el contenedor al DOM
        mailContainer.appendChild(hiddenContainer);
        console.log('Campos ocultos para storage.js creados');
    });
})();