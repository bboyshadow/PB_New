// ARCHIVO shared/tests/js/ui.test.js

/**
 * Pruebas unitarias para las funciones de interfaz de usuario
 * Implementadas usando Jest
 */

// Importar las funciones a probar
import { createWithFragment, createBatch, batchStyles } from '../../js/utils/dom.js';
import { debounce, throttle } from '../../js/utils/debounce.js';

// Mock del DOM para pruebas
function setupDOM() {
    document.body.innerHTML = `
        <div id="container"></div>
    `;
}

describe('DOM Manipulation Functions', () => {
    beforeEach(() => {
        // Configurar el DOM antes de cada prueba
        setupDOM();
        
        // Limpiar todos los timers
        jest.useFakeTimers();
    });
    
    afterEach(() => {
        // Restaurar timers reales
        jest.useRealTimers();
    });
    
    test('createWithFragment should create elements in a fragment and append to container', () => {
        // Setup
        const container = document.getElementById('container');
        
        // Execute
        createWithFragment(fragment => {
            for (let i = 0; i < 3; i++) {
                const div = document.createElement('div');
                div.className = 'test-item';
                div.textContent = `Item ${i}`;
                fragment.appendChild(div);
            }
        }, container);
        
        // Assert
        const items = container.querySelectorAll('.test-item');
        expect(items.length).toBe(3);
        expect(items[0].textContent).toBe('Item 0');
        expect(items[1].textContent).toBe('Item 1');
        expect(items[2].textContent).toBe('Item 2');
    });
    
    test('createBatch should create multiple elements from data array', () => {
        // Setup
        const container = document.getElementById('container');
        const items = [
            { id: 1, name: 'Item 1' },
            { id: 2, name: 'Item 2' },
            { id: 3, name: 'Item 3' }
        ];
        
        // Execute
        createBatch(items, item => {
            const div = document.createElement('div');
            div.className = 'batch-item';
            div.dataset.id = item.id;
            div.textContent = item.name;
            return div;
        }, container);
        
        // Assert
        const batchItems = container.querySelectorAll('.batch-item');
        expect(batchItems.length).toBe(3);
        expect(batchItems[0].dataset.id).toBe('1');
        expect(batchItems[1].textContent).toBe('Item 2');
    });

    test('batchStyles should apply and restore styles correctly', () => {
        const container = document.getElementById('container');
        const div = document.createElement('div');
        div.style.width = '100px';
        container.appendChild(div);

        const original = batchStyles(div, { width: '200px', height: '50px' });

        expect(div.style.width).toBe('200px');
        expect(div.style.height).toBe('50px');
        expect(original).toEqual({ width: '100px', height: '' });

        // Restore original styles
        Object.keys(original).forEach(prop => {
            div.style[prop] = original[prop];
        });

        expect(div.style.width).toBe('100px');
        expect(div.style.height).toBe('');
    });
});

describe('Performance Optimization Functions', () => {
    beforeEach(() => {
        jest.useFakeTimers();
    });
    
    afterEach(() => {
        jest.useRealTimers();
    });
    
    test('debounce should only execute after the wait time', () => {
        // Setup
        const mockFn = jest.fn();
        const debouncedFn = debounce(mockFn, 1000);
        
        // Execute
        debouncedFn();
        debouncedFn();
        debouncedFn();
        
        // Assert - function should not have been called yet
        expect(mockFn).not.toHaveBeenCalled();
        
        // Fast-forward time
        jest.advanceTimersByTime(1000);
        
        // Assert - function should have been called once
        expect(mockFn).toHaveBeenCalledTimes(1);
    });
    
    test('throttle should limit execution frequency', () => {
        // Setup
        const mockFn = jest.fn();
        const throttledFn = throttle(mockFn, 1000);
        
        // Execute
        throttledFn(); // Should execute immediately
        throttledFn(); // Should be ignored
        throttledFn(); // Should be ignored
        
        // Assert - function should have been called once
        expect(mockFn).toHaveBeenCalledTimes(1);
        
        // Fast-forward time
        jest.advanceTimersByTime(1000);
        
        // Call again after the limit
        throttledFn();
        
        // Assert - function should have been called twice
        expect(mockFn).toHaveBeenCalledTimes(2);
    });
});