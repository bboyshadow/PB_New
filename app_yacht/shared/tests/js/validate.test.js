// ARCHIVO shared/tests/js/validate.test.js

/**
 * Pruebas unitarias para las funciones de validación
 * Implementadas usando Jest
 */

// Importar las funciones a probar
import { validateFields, validateEmail, validateNumber } from '../../js/validate.js';

// Mock del DOM para pruebas
function setupDOM() {
    document.body.innerHTML = `
        <form id="charterForm">
            <input id="field1" required>
            <input id="email" type="email">
            <input id="number" type="number">
            <div id="errorMessage" style="display:none;"></div>
        </form>
    `;
}

describe('Validation Functions', () => {
    beforeEach(() => {
        // Configurar el DOM antes de cada prueba
        setupDOM();
    });

    test('validateFields should return false for empty required fields', () => {
        // Setup
        const field1 = document.getElementById('field1');
        field1.value = '';
        
        // Execute
        const result = validateFields();
        
        // Assert
        expect(result).toBe(false);
        expect(document.getElementById('errorMessage').style.display).toBe('block');
    });

    test('validateFields should return true for valid required fields', () => {
        // Setup
        const field1 = document.getElementById('field1');
        field1.value = 'test value';
        
        // Execute
        const result = validateFields();
        
        // Assert
        expect(result).toBe(true);
        expect(document.getElementById('errorMessage').style.display).toBe('none');
    });

    test('validateEmail should return false for invalid email', () => {
        // Setup
        const emailInput = document.getElementById('email');
        emailInput.value = 'invalid-email';
        
        // Execute
        const result = validateEmail(emailInput.value);
        
        // Assert
        expect(result).toBe(false);
    });

    test('validateEmail should return true for valid email', () => {
        // Setup
        const emailInput = document.getElementById('email');
        emailInput.value = 'valid@example.com';
        
        // Execute
        const result = validateEmail(emailInput.value);
        
        // Assert
        expect(result).toBe(true);
    });

    test('validateNumber should return false for non-numeric input', () => {
        // Setup
        const numberInput = document.getElementById('number');
        numberInput.value = 'abc';
        
        // Execute
        const result = validateNumber(numberInput.value);
        
        // Assert
        expect(result).toBe(false);
    });

    test('validateNumber should return true for numeric input', () => {
        // Setup
        const numberInput = document.getElementById('number');
        numberInput.value = '123';
        
        // Execute
        const result = validateNumber(numberInput.value);
        
        // Assert
        expect(result).toBe(true);
    });

    test('validateNumber should handle decimal numbers', () => {
        // Setup
        const numberInput = document.getElementById('number');
        numberInput.value = '123.45';
        
        // Execute
        const result = validateNumber(numberInput.value);
        
        // Assert
        expect(result).toBe(true);
    });
});