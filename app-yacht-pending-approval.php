<?php
/*
Template Name: App Yacht Pending Approval
*/

/**
 * page-pending-approval.php
 *
 * Plantilla para mostrar una página de espera de aprobación en App_Yacht.
 * Utiliza Bootstrap y Font Awesome para un diseño consistente con la aplicación.
 *
 * @package App_Yacht\Core
 * @since 1.0.0
 */

get_header();
?>
<div class="container my-5">
    <div class="card bg-dark text-light border-0 shadow-lg" style="border-radius: 8px;">
        <div class="card-body text-center p-5">
            <i class="fas fa-hourglass-half fa-3x fa-spin mb-4" style="color: #00bcd4;"></i>
            <h1 class="card-title mb-3"><?php _e('Awaiting Approval', 'app_yacht'); ?></h1>
            <p class="card-text lead mb-4">
                <?php _e('Your request is currently pending approval. You will be notified once it has been reviewed.', 'app_yacht'); ?>
            </p>
            <a href="<?php echo esc_url(home_url('/')); ?>" class="btn btn-primary btn-lg">
                <i class="fas fa-arrow-left me-2"></i><?php _e('Back to Home', 'app_yacht'); ?>
            </a>
        </div>
    </div>
</div>
<?php
get_footer();
?>