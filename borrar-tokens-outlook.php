<?php
/**
 * Script para borrar manualmente los tokens de Outlook
 * 
 * Este script elimina los tokens de Outlook almacenados en la base de datos
 * para el usuario actual o para un usuario específico (si se proporciona un ID).
 */

// Cargar WordPress
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/wp-load.php');

// Verificar que el usuario está autenticado
if (!is_user_logged_in()) {
    die('Debes iniciar sesión para usar esta herramienta.');
}

// Verificar que el usuario es administrador
if (!current_user_can('administrator')) {
    die('Necesitas permisos de administrador para usar esta herramienta.');
}

// Cargar las funciones de desconexión de Outlook
require_once get_template_directory() . '/app_yacht/modules/mail/outlook/outlook-disconnect.php';

// Obtener el ID de usuario a procesar (por defecto, el usuario actual)
$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : get_current_user_id();

// Verificar si se ha enviado el formulario
$action_taken = false;
$result_message = '';

if (isset($_POST['action']) && $_POST['action'] === 'delete_tokens') {
    // Verificar nonce para seguridad
    if (!isset($_POST['security']) || !wp_verify_nonce($_POST['security'], 'delete_outlook_tokens_nonce')) {
        $result_message = '<div class="error">Error de seguridad. Por favor, recarga la página e intenta de nuevo.</div>';
    } else {
        // Intentar eliminar los tokens
        $user_id_to_process = isset($_POST['user_id']) ? intval($_POST['user_id']) : get_current_user_id();
        
        // Verificar si el usuario tiene tokens
        if (pb_outlook_is_connected($user_id_to_process)) {
            // Eliminar tokens usando la función existente
            $success = pb_outlook_disconnect_user($user_id_to_process);
            
            if ($success) {
                $action_taken = true;
                $result_message = '<div class="success">Los tokens de Outlook han sido eliminados correctamente.</div>';
            } else {
                $result_message = '<div class="error">Error al eliminar los tokens. Verifica los logs para más información.</div>';
            }
        } else {
            $result_message = '<div class="notice">El usuario no tiene tokens de Outlook para eliminar.</div>';
        }
    }
}

// Obtener información del usuario
$user = get_user_by('id', $user_id);
$is_connected = pb_outlook_is_connected($user_id);
$outlook_email = get_user_meta($user_id, 'outlook_email', true);

// Estilo CSS básico
?>
<!DOCTYPE html>
<html>
<head>
    <title>Borrar Tokens de Outlook</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; padding: 20px; max-width: 800px; margin: 0 auto; }
        h1 { color: #333; }
        .container { border: 1px solid #ddd; padding: 20px; border-radius: 5px; }
        .success { background-color: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin-bottom: 15px; }
        .error { background-color: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin-bottom: 15px; }
        .notice { background-color: #fff3cd; color: #856404; padding: 10px; border-radius: 5px; margin-bottom: 15px; }
        .info { background-color: #e2f0fb; padding: 10px; border-radius: 5px; margin-bottom: 15px; }
        form { margin-top: 20px; }
        button { background-color: #dc3545; color: white; border: none; padding: 10px 15px; cursor: pointer; border-radius: 5px; }
        button:hover { background-color: #c82333; }
        .back-link { display: inline-block; margin-top: 20px; color: #007bff; text-decoration: none; }
        .back-link:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <h1>Herramienta para Borrar Tokens de Outlook</h1>
    
    <div class="container">
        <?php if ($action_taken): ?>
            <?php echo $result_message; ?>
            <p><a href="<?php echo esc_url(admin_url('admin.php?page=app-yacht')); ?>" class="back-link">Volver al panel</a></p>
        <?php else: ?>
            <?php echo $result_message; ?>
            
            <div class="info">
                <h3>Información del usuario</h3>
                <p><strong>Usuario:</strong> <?php echo esc_html($user ? $user->display_name : 'Usuario no encontrado'); ?> (ID: <?php echo esc_html($user_id); ?>)</p>
                <p><strong>Estado de conexión:</strong> <?php echo $is_connected ? 'Conectado' : 'No conectado'; ?></p>
                <?php if ($is_connected && !empty($outlook_email)): ?>
                    <p><strong>Correo de Outlook:</strong> <?php echo esc_html($outlook_email); ?></p>
                <?php endif; ?>
            </div>
            
            <?php if ($is_connected): ?>
                <form method="post" action="">
                    <input type="hidden" name="action" value="delete_tokens">
                    <input type="hidden" name="user_id" value="<?php echo esc_attr($user_id); ?>">
                    <?php wp_nonce_field('delete_outlook_tokens_nonce', 'security'); ?>
                    
                    <p>Esta acción eliminará los siguientes tokens de Outlook para el usuario:</p>
                    <ul>
                        <li>outlook_access_token</li>
                        <li>outlook_refresh_token</li>
                        <li>outlook_expires_at</li>
                        <li>outlook_email</li>
                    </ul>
                    
                    <p><strong>¿Estás seguro de que deseas eliminar estos tokens?</strong></p>
                    <button type="submit">Eliminar Tokens de Outlook</button>
                </form>
            <?php else: ?>
                <p>El usuario no tiene tokens de Outlook para eliminar.</p>
            <?php endif; ?>
            
            <p><a href="<?php echo esc_url(admin_url('admin.php?page=app-yacht')); ?>" class="back-link">Volver al panel</a></p>
        <?php endif; ?>
    </div>
</body>
</html>