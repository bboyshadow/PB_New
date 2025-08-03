#!/usr/bin/env python3
# -*- coding: utf-8 -*-

"""
Validador de Documentación Técnica para Proyectos

Valida la estructura y reciprocidad de la documentación técnica en la carpeta DOC/,
alineado con la plantilla doc_template_structure_updated.md. Se enfoca en verificar
que la documentación refleje el estado actual del código sin sugerir mejoras.
"""

import os
import re
import sys
import json
import codecs
import logging
import argparse
from pathlib import Path
from typing import Dict, List, Set, Tuple, Optional

# Constantes para los nombres de sección
SECTION_OUTGOING = "Dependencias Internas (Salientes)"
SECTION_INCOMING = "Dependencias Internas (Entrantes)"
SECTION_IMPLICIT = "Conexiones Implícitas"
SECTION_LOGIC = "Lógica de Negocio"
SECTION_ERRORS = "Manejo de Errores"
SECTION_TESTS = "Pruebas de Integración"

# Estructura obligatoria de los documentos
REQUIRED_FILES = [
    "propósito-general.md",
    "estructura-código.md",
    "dependencias.md",
    "lógica-negocio.md",
    "puntos-entrada.md",
    "flujo-datos.md",
    "manejo-errores.md",
    "pruebas-integración.md",
    "referencias.md",
    "historial.md",
]

class DocumentValidator:
    """Clase principal para validar la documentación técnica."""

    def __init__(self, doc_root: str = "DOC", strict: bool = True):
        """Inicializa el validador con la ruta raíz de documentación."""
        # Crear directorio de logs primero
        self.logs_dir = Path('DOC/tools/test/logs')
        self.logs_dir.mkdir(exist_ok=True)
        self.logger = self.setup_logging()

        self.logger.info("=== Inicializando validador ===")
        self.logger.info(f"Ruta de documentación proporcionada: {doc_root}")

        self.doc_root = Path(doc_root).resolve()
        self.logger.info(f"Ruta absoluta resuelta: {self.doc_root}")
        self.strict = strict

        if not self.doc_root.exists() or not self.doc_root.is_dir():
            self.logger.error(f"El directorio de documentación no existe o no es un directorio: {self.doc_root}")
            # Salir si el directorio base no es válido
            sys.exit(1)

        self.logger.debug("Contenido del directorio:")
        for i, item in enumerate(self.doc_root.iterdir(), 1):
            self.logger.debug(f"  {i}. {item.name} (directorio: {item.is_dir()})")

        self.md_files: Dict[str, Path] = {}
        self.validation_errors: Dict[str, List[str]] = {}
        self.validation_warnings: Dict[str, List[str]] = {}
        self.folder_errors: List[str] = []
        self.documented_outgoing: Set[Tuple[str, str]] = set()
        self.documented_incoming: Set[Tuple[str, str]] = set()
        self.logger.info("=== Fin de inicialización ===")

    def setup_logging(self) -> logging.Logger:
        """Configura el sistema de logging para la instancia."""
        logger = logging.getLogger(f'doc_validator_{id(self)}')
        logger.setLevel(logging.DEBUG)
        logger.propagate = False

        # Evitar añadir manejadores si ya existen
        if logger.hasHandlers():
            logger.handlers.clear()

        log_format = logging.Formatter('%(asctime)s - %(levelname)s - %(message)s')

        # Manejador de archivo de depuración
        log_file = self.logs_dir / 'doc_validator.log'
        file_handler = logging.FileHandler(log_file, mode='w', encoding='utf-8')
        file_handler.setLevel(logging.DEBUG)
        file_handler.setFormatter(log_format)
        logger.addHandler(file_handler)

        # Manejador de consola
        console_handler = logging.StreamHandler()
        console_handler.setLevel(logging.INFO)
        console_handler.setFormatter(log_format)
        logger.addHandler(console_handler)
        
        # Manejador de archivo de errores
        error_log_file = self.logs_dir / 'doc_validator_errors.log'
        error_handler = logging.FileHandler(error_log_file, mode='w', encoding='utf-8')
        error_handler.setLevel(logging.ERROR)
        error_handler.setFormatter(log_format)
        logger.addHandler(error_handler)
        
        logger.info("Sistema de registro configurado correctamente.")
        return logger

    def find_md_files(self) -> Dict[str, Path]:
        """Encuentra todos los archivos .md en el directorio de documentación."""
        md_files = {}
        self.logger.info(f"Buscando archivos .md en: {self.doc_root}")
        
        for md_path in self.doc_root.rglob('*.md'):
            self.logger.debug(f"Procesando archivo: {md_path}")
            if md_path.name.startswith('.'):
                self.logger.debug(f"  - Ignorando archivo oculto: {md_path}")
                continue
            
            try:
                rel_path = md_path.relative_to(self.doc_root)
                code_path = str(rel_path.with_suffix('')).replace('\\', '/')
                md_files[code_path] = md_path
                self.logger.debug(f"  - Archivo añadido con clave: {code_path}")
            except ValueError as e:
                self.logger.warning(f"No se pudo determinar la ruta relativa para {md_path}: {e}")

        self.logger.info(f"Total de archivos .md encontrados: {len(md_files)}")
        return md_files

    def _extract_metadata(self, content: str, file_path: str) -> Optional[Dict[str, str]]:
        """
        Extrae y valida metadatos del encabezado del documento de manera robusta.
        Combina la extracción y validación en un solo paso para mayor eficiencia.

        Args:
            content: Contenido del archivo Markdown.
            file_path: Ruta del archivo (para mensajes de error).

        Returns:
            Diccionario con los metadatos si son válidos, o None si hay un error.
        """
        self.logger.debug(f"Iniciando extracción de metadatos para: {file_path}")

        if not content or not content.strip():
            self.logger.error(f"El contenido del archivo está vacío: {file_path}")
            return None

        content_norm = content.replace('\r\n', '\n')
        
        bullet = r"(?:^\s*[-*]\s*)?"
        patterns = {
            'tipo': bullet + r'\*\*Tipo\*\*:\s*(.*?)(?:\n|$)',
            'version': bullet + r'\*\*Versión(?: del Documento)?\*\*:\s*(.*?)(?:\n|$)',
            'fecha_creacion': bullet + r'\*\*Fecha(?: de)? Creación\*\*:\s*(\d{4}-\d{2}-\d{2})',
            'autor': bullet + r'\*\*Autor(?:\s*\(es\))?\*\*:\s*(.*?)(?:\n|$)',
            'estado': bullet + r'\*\*(?:Estado(?: de Auditoría)?|Auditoría)\*\*:\s*(.*?)(?=\n|$)',
            'ultima_actualizacion': bullet + r'\*\*(?:Última )?Actualización\*\*:\s*(\d{4}-\d{2}-\d{2})'
        }

        metadata = {field: (match.group(1).strip() if (match := re.search(pattern, content_norm, re.IGNORECASE | re.MULTILINE)) else None) for field, pattern in patterns.items()}
        metadata = {k: v for k, v in metadata.items() if v is not None}

        # --- Validación ---
        errors = []
        required_fields = ['tipo', 'version', 'fecha_creacion', 'autor', 'estado']
        missing_fields = [field for field in required_fields if not metadata.get(field)]
        if missing_fields:
            errors.append(f"Faltan campos de metadato obligatorios o están vacíos: {', '.join(missing_fields)}")

        for date_field in ['fecha_creacion', 'ultima_actualizacion']:
            if date_field in metadata and not re.match(r'^\d{4}-\d{2}-\d{2}$', metadata[date_field]):
                errors.append(f"Formato de fecha inválido en '{date_field}'. Use YYYY-MM-DD.")

        if 'version' in metadata and not re.match(r'^\d+\.\d+(?:\.\d+)?$', metadata['version']):
            errors.append("Formato de versión inválido. Use X.Y o X.Y.Z.")
        
        valid_states = ['✅ Completado', '🔍 En Progreso', '⚠️ Requiere Revisión', 'Pendiente']
        if 'estado' in metadata and metadata.get('estado') not in valid_states:
             self.logger.warning(f"En archivo '{file_path}', estado de auditoría no estándar: '{metadata['estado']}'")

        if errors:
            error_details = "\n".join([f"  - {e}" for e in errors])
            self.logger.error(f"Errores de metadatos en '{file_path}':\n{error_details}")
            return None

        self.logger.debug(f"Metadatos extraídos y validados para: {file_path}")
        return metadata

    def validate_structure(self, content: str, file_path: str) -> List[str]:
        """Valida la estructura de un archivo Markdown."""
        errors = []

        if not re.search(r'^#\s+Documentación Técnica', content, re.MULTILINE):
            errors.append("Falta el encabezado principal con formato '# Documentación Técnica'")

        metadata = self._extract_metadata(content, file_path)
        if metadata is None:
            errors.append(
                "Bloque de metadatos ausente, incompleto o con formato incorrecto. Revise los logs de error para más detalles."
            )

        return errors
    
    def get_documented_relations(self, content: str, section_heading: str) -> List[str]:
        """Extrae rutas de archivo de una sección específica."""
        section_match = re.search(
            rf"^##\s*{re.escape(section_heading)}([\s\S]*?)(?=(?:^##|\Z))", 
            content, re.MULTILINE | re.IGNORECASE
        )
        if section_match:
            return self._extract_paths_from_section(section_match.group(1))
        return []

    def _extract_paths_from_section(self, section_content: str) -> List[str]:
        """Extrae rutas de archivo de una sección de Markdown."""
        paths = []
        items = re.findall(r"^\s*[\*\-]\s*.*?`([^`]+)`.*", section_content, re.MULTILINE)
        for path in items:
            normalized_path = str(Path(path).as_posix())
            if normalized_path.startswith('DOC/'):
                normalized_path = normalized_path[4:]
            if normalized_path.endswith('.md'):
                normalized_path = normalized_path[:-3]
            paths.append(normalized_path)
        return paths

    def validate_file_paths(self, paths: List[str], source_file: str) -> List[str]:
        """Valida que las rutas referenciadas existan."""
        missing = []
        for path in paths:
            if not any((self.doc_root / f"{path}.md").exists() for p in [path]):
                missing.append(path)
        return missing

    def validate_logic_section(self, content: str, file_path: str) -> List[str]:
        """Valida la sección de lógica de negocio."""
        errors = []
        section_match = re.search(
            rf"^##\s*{re.escape(SECTION_LOGIC)}([\s\S]*?)(?=(?:^##|\Z))",
            content, re.MULTILINE | re.IGNORECASE
        )
        if not section_match:
            errors.append(f"Falta la sección '{SECTION_LOGIC}'")
            return errors

        function_matches = re.finditer(
            r'^###\s*Función/Clase:\s*`([^`]+)`([\s\S]*?)(?=(?:^###|\Z))',
            section_match.group(1), re.MULTILINE
        )
        for match in function_matches:
            func_name = match.group(1)
            func_content = match.group(2)
            if not re.search(r'####\s*Uso\s*\n\s*[\*\-]', func_content, re.MULTILINE):
                errors.append(f"Subsección 'Uso' vacía o ausente para la función/clase '{func_name}'")
            if not re.search(r'####\s*Variables Globales/Estado', func_content, re.MULTILINE):
                errors.append(f"Falta subsección 'Variables Globales/Estado' para la función/clase '{func_name}'")
        return errors

    def validate_document(self, code_path: str, md_path: Path) -> bool:
        """Valida un documento individual."""
        try:
            with md_path.open('r', encoding='utf-8') as f:
                content = f.read()
        except IOError as e:
            self.validation_errors[code_path] = [f"No se pudo leer el archivo: {e}"]
            return False

        errors = self.validate_structure(content, str(md_path))
        if md_path.name == "lógica-negocio.md":
            errors.extend(self.validate_logic_section(content, str(md_path)))

        outgoing = self.get_documented_relations(content, SECTION_OUTGOING)
        incoming = self.get_documented_relations(content, SECTION_INCOMING)
        
        warnings = []
        for path in self.validate_file_paths(outgoing, code_path):
            warnings.append(f"Dependencia saliente no encontrada: '{path}'")
        for path in self.validate_file_paths(incoming, code_path):
            warnings.append(f"Dependencia entrante no encontrada: '{path}'")
        
        if errors:
            self.validation_errors[code_path] = errors
        if warnings:
            self.validation_warnings[code_path] = warnings

        for target in outgoing:
            self.documented_outgoing.add((code_path, target))
        for source in incoming:
            self.documented_incoming.add((source, code_path))

        return not bool(errors)

    def validate_folder_structure(self) -> List[str]:
        """Valida que cada subcarpeta con un 'index.md' tenga los archivos requeridos."""
        errors = []
        folders_to_check = {md_path.parent for code_path, md_path in self.md_files.items() if md_path.name == "index.md"}
        
        for folder in folders_to_check:
            for required_file in REQUIRED_FILES:
                if not (folder / required_file).exists():
                    errors.append(f"Falta el archivo requerido '{required_file}' en la carpeta '{folder.relative_to(self.doc_root)}'")
        return errors

    def validate_reciprocity(self) -> List[Tuple[str, str]]:
        """Valida la reciprocidad de las dependencias."""
        # Una dependencia A -> B (saliente de A) debe corresponder a una dependencia A -> B (entrante en B)
        return sorted(list(self.documented_outgoing - self.documented_incoming))

    def generate_report(self) -> str:
        """Genera un informe detallado de validación."""
        report = ["=" * 80, "INFORME DE VALIDACIÓN DE DOCUMENTACIÓN TÉCNICA", "=" * 80]
        
        folder_errors = self.folder_errors
        if self.strict and folder_errors:
            report.append("\n❌ ERRORES DE ESTRUCTURA DE CARPETA:")
            report.extend([f"  - {error}" for error in folder_errors])

        if self.validation_errors:
            report.append("\n❌ ERRORES DE ESTRUCTURA DE ARCHIVO:")
            for file, errors in self.validation_errors.items():
                report.append(f"\nArchivo: {file}.md")
                report.extend([f"  - {error}" for error in errors])

        if self.validation_warnings:
            report.append("\n⚠️ ADVERTENCIAS:")
            for file, warnings in self.validation_warnings.items():
                report.append(f"\nArchivo: {file}.md")
                report.extend([f"  - {warning}" for warning in warnings])

        reciprocity_errors = self.validate_reciprocity()
        if reciprocity_errors:
            report.append("\n❌ ERRORES DE RECIPROCIDAD:")
            for source, target in reciprocity_errors:
                report.append(f"  - La dependencia de '{source}' → '{target}' no está documentada como entrante en '{target}'.")

        total_errors = len(folder_errors) + sum(len(e) for e in self.validation_errors.values()) + len(reciprocity_errors)
        report.extend(["\n" + "-" * 80, f"Validación completada. Total de errores: {total_errors}", "=" * 80])
        return "\n".join(report)

    def run_validation(self) -> bool:
        """Ejecuta la validación completa."""
        self.md_files = self.find_md_files()
        if not self.md_files:
            self.logger.warning("No se encontraron archivos Markdown para validar.")
            return True # No hay archivos, no hay errores.

        self.logger.info(f"Se encontraron {len(self.md_files)} archivos. Iniciando validación...")
        for code_path, md_path in self.md_files.items():
            self.validate_document(code_path, md_path)

        self.folder_errors = self.validate_folder_structure() if self.strict else []

        report = self.generate_report()
        print(report)

        # La validación es exitosa si no hay errores de ningún tipo
        has_errors = (
            bool(self.validation_errors)
            or bool(self.validate_reciprocity())
            or bool(self.folder_errors)
        ) if self.strict else (
            bool(self.validation_errors) or bool(self.validate_reciprocity())
        )
        return not has_errors

def main():
    """Función principal que ejecuta el validador."""
    parser = argparse.ArgumentParser(description='Validador de Documentación Técnica')
    parser.add_argument('doc_root', nargs='?', default='DOC',
                       help='Directorio raíz de documentación (por defecto: DOC)')
    parser.add_argument('--lenient', action='store_true', help='Desactivar comprobaciones estrictas')
    args = parser.parse_args()

    # Configurar codificación UTF-8 para la salida estándar
    if sys.stdout.encoding != 'utf-8':
        sys.stdout = codecs.getwriter('utf-8')(sys.stdout.buffer, 'strict')

    try:
        validator = DocumentValidator(args.doc_root, strict=not args.lenient)
        success = validator.run_validation()
        
        if success:
            validator.logger.info("La validación de la documentación ha finalizado con éxito.")
        else:
            validator.logger.error("La validación de la documentación ha encontrado errores. Revise el informe y los logs.")

        sys.exit(0 if success else 1)

    except Exception as e:
        # Captura errores críticos que puedan ocurrir antes de que el logger se configure completamente
        logging.basicConfig(level=logging.ERROR, format='%(asctime)s - %(levelname)s - %(message)s')
        logging.error(f"Error crítico en la ejecución del validador: {str(e)}", exc_info=True)
        sys.exit(1)

if __name__ == "__main__":
    main()
