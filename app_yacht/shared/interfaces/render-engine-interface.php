<?php


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


interface RenderEngineInterface {

	
	public function render( $template, array $data, $format = 'html');

	
	public function loadTemplatePreview( array $data);

	
	public function createTemplate( array $formData, $yachtData = null);

	
	public function getAvailableTemplates();

	
	public function templateExists( $template);

	
	public function getTemplateContent( $template);

	
	public function processVariables( $content, array $variables);
}
