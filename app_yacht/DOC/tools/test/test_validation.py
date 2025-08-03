#!/usr/bin/env python3
# -*- coding: utf-8 -*-

"""
Pruebas para el validador de documentación técnica.

Este módulo contiene pruebas unitarias para validar el correcto funcionamiento
del validador de documentación técnica (doc-validator.py).
"""

import os
import sys
import unittest
import tempfile
import shutil
from pathlib import Path

# Añadir el directorio de Python al path para poder importar el validador
sys.path.insert(0, str(Path(__file__).parent.parent / "py"))
# Use importlib to import module with hyphen in name
import importlib.util
spec = importlib.util.spec_from_file_location("doc_validator", str(Path(__file__).parent.parent / "doc-validator.py"))
doc_validator = importlib.util.module_from_spec(spec)
spec.loader.exec_module(doc_validator)
DocumentValidator = doc_validator.DocumentValidator


class TestDocumentValidator(unittest.TestCase):
    """Caso de prueba para el validador de documentación."""

    def setUp(self):
        """Configuración común para todas las pruebas."""
        # Crear un directorio temporal para las pruebas
        self.test_dir = Path(tempfile.mkdtemp(prefix="doc_test_"))
        self.doc_dir = self.test_dir / "DOC"
        self.doc_dir.mkdir()
        
        # Crear estructura de directorios de prueba
        self.example_dir = self.doc_dir / "core" / "example-file"
        self.example_dir.mkdir(parents=True)
        
    def tearDown(self):
        """Limpieza después de cada prueba."""
        # Eliminar el directorio temporal
        if self.test_dir.exists():
            shutil.rmtree(self.test_dir)
    
    def create_test_file(self, filename, content):
        """Crear un archivo de prueba con el contenido dado."""
        filepath = self.example_dir / filename
        filepath.parent.mkdir(parents=True, exist_ok=True)
        with open(filepath, 'w', encoding='utf-8') as f:
            f.write(content)
        return filepath
    
    def test_validate_complete_documentation(self):
        """Validar documentación completa y correcta."""
        # Crear archivos de documentación de ejemplo
        self.create_test_file("01_index.md", """# Documentación Técnica: core/example-file - Índice

**IMPRESCINDIBLE LEER...**

- **Tipo**: Backend (PHP)
- **Versión del Documento**: 1.0
- **Fecha de Creación**: 2025-06-26
- **Última Actualización**: 2025-06-26
- **Autor**: Equipo de Desarrollo
- **Estado de Auditoría**: ✅ Completado
- **Enlace al Índice Superior**: ../index.md

## Secciones de Documentación

1. [Propósito General](02_propósito-general.md)""")
        
        # Crear más archivos de documentación...
        
        # Validar la documentación
        validator = DocumentValidator(str(self.doc_dir))
        result = validator.run_validation()
        
        # Verificar que la validación fue exitosa
        self.assertTrue(result, "La validación de documentación completa falló")
    
    def test_missing_required_file(self):
        """Validar que se detecta un archivo requerido faltante."""
        # Crear solo el archivo índice
        self.create_test_file("01_index.md", "...")
        
        # Validar la documentación
        validator = DocumentValidator(str(self.doc_dir))
        result = validator.run_validation()
        
        # Verificar que la validación falló
        self.assertFalse(result, "La validación debería fallar por archivos faltantes")
    
    def test_invalid_metadata(self):
        """Validar detección de metadatos inválidos."""
        # Crear archivo con metadatos inválidos
        self.create_test_file("01_index.md", "# Documentación Técnica")
        
        # Validar la documentación
        validator = DocumentValidator(str(self.doc_dir))
        result = validator.run_validation()
        
        # Verificar que la validación falló
        self.assertFalse(result, "La validación debería fallar por metadatos inválidos")


if __name__ == "__main__":
    unittest.main()
