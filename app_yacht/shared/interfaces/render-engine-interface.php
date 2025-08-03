<?php
/**
 * Interface para el motor de renderizado
 * Define el contrato para generación de plantillas y contenido
 *
 * @package AppYacht\Interfaces
 * @version 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Interface para motores de renderizado
 */
interface RenderEngineInterface {

	/**
	 * Renderiza una plantilla con datos
	 *
	 * @param string $template Nombre de la plantilla
	 * @param array  $data Datos para la plantilla
	 * @param string $format Formato de salida (html|text)
	 * @return string Contenido renderizado
	 */
	public function render( $template, array $data, $format = 'html');

	/**
	 * Carga una vista previa de plantilla
	 *
	 * @param array $data Datos del request
	 * @return array Resultado con vista previa
	 */
	public function loadTemplatePreview( array $data);

	/**
	 * Crea una nueva plantilla
	 *
	 * @param array      $formData Datos del formulario
	 * @param array|null $yachtData Datos del yate (opcional)
	 * @return array Resultado de la creación
	 */
	public function createTemplate( array $formData, $yachtData = null);

	/**
	 * Obtiene lista de plantillas disponibles
	 *
	 * @return array Lista de plantillas
	 */
	public function getAvailableTemplates();

	/**
	 * Verifica si una plantilla existe
	 *
	 * @param string $template Nombre de la plantilla
	 * @return bool
	 */
	public function templateExists( $template);

	/**
	 * Obtiene el contenido de una plantilla
	 *
	 * @param string $template Nombre de la plantilla
	 * @return string|false Contenido de la plantilla o false
	 */
	public function getTemplateContent( $template);

	/**
	 * Procesa variables en el contenido de la plantilla
	 *
	 * @param string $content Contenido con variables
	 * @param array  $variables Variables a reemplazar
	 * @return string Contenido procesado
	 */
	public function processVariables( $content, array $variables);
}
