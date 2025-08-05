<?php
use PHPUnit\Framework\TestCase;

class RenderEngineTest extends TestCase {
    private $config;

    protected function setUp(): void {
        $this->config = [
            'templates_path' => __DIR__ . '/templates/',
            'available_templates' => [ 'valid-template' ],
            'cache_enabled' => false,
            'cache_duration' => 3600,
            'default_template' => 'valid-template',
        ];
    }

    public function testRenderValidTemplate() {
        $engine = new RenderEngine( $this->config );
        $output = $engine->render( 'valid-template', [], 'html' );
        $this->assertNotInstanceOf( WP_Error::class, $output );
        $this->assertSame( 'VALID', $output );
    }

    public function testRenderInvalidTemplate() {
        $engine = new RenderEngine( $this->config );
        $output = $engine->render( 'invalid-template', [], 'html' );
        $this->assertInstanceOf( WP_Error::class, $output );
        $this->assertSame( 'invalid_template', $output->get_error_code() );
    }

    public function testLoadTemplatePreviewValidTemplate() {
        $engine = new RenderEngine( $this->config );
        $result = $engine->loadTemplatePreview( [ 'template' => 'valid-template', 'currency' => '€' ] );
        $this->assertNotInstanceOf( WP_Error::class, $result );
        $this->assertTrue( $result['success'] );
        $this->assertSame( 'valid-template', $result['template'] );
    }

    public function testLoadTemplatePreviewInvalidTemplate() {
        $engine = new RenderEngine( $this->config );
        $result = $engine->loadTemplatePreview( [ 'template' => 'invalid-template' ] );
        $this->assertInstanceOf( WP_Error::class, $result );
        $this->assertSame( 'invalid_template', $result->get_error_code() );
    }
}
