# Plantilla de Prompt para Validar Documentación y Crear PR

### Objetivo del Prompt

Esta plantilla explica cómo pedir a la IA que verifique la documentación con `doc-validator.py` en modo **lenient** y luego cree un Pull Request. Sirve para evitar que errores menores bloqueen la sincronización con GitHub.

---

### Prompt para Copiar y Usar

Hola, necesito validar la documentación y enviar un Pull Request.

1. Abre una terminal en la raíz del proyecto.
2. Ejecuta el validador en modo lenient:
   ```bash
   python DOC/tools/doc-validator.py DOC/tools/test --lenient
   ```
3. Si la validación termina sin errores, ejecuta las pruebas unitarias:
   ```bash
   python3 -m pytest DOC/tools/test/test_validation.py -q
   ```
4. Crea un commit con los cambios validados.
5. Genera un Pull Request describiendo brevemente las modificaciones realizadas.

Si al enviar el PR aparecen conflictos de fusión, sigue estos pasos:

6. Obtén los últimos cambios de la rama remota y fusiona:
   ```bash
   git fetch origin
   git merge origin/main  # o la rama que corresponda
   ```
7. Resuelve los conflictos en tu editor, añade los archivos modificados y crea
   un nuevo commit:
   ```bash
   git add <archivos-resueltos>
   git commit -m "Resolver conflictos"
   ```
8. Vuelve a ejecutar el validador y las pruebas para asegurarte de que todo
   funciona.
9. Empuja la rama actualizada y genera el Pull Request.

No detengas el proceso por advertencias. Solo si aparecen errores debes corregir la documentación antes de continuar.
