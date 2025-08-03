<div class="container mt-5">
    <div class="card shadow-lg">
        
        <div class="card-body">
            
            <div class="text-center">
                <div id="qr
<nav class="navbar navbar-dark bg-primary mb-4">
    <div class="container">
    <span class="navbar-brand mb-0 h1">
            <i class="fas fa-qrcode me-2"></i>Control de Tareas
        </span>
    </div>
</nav>

<div class="container">
    <div class="card shadow-lg">
        <div class="card-header bg-white border-bottom-0">
            <h2 class="h4 mb-0 text-primary"><i class="fas fa-user-hard-hat me-2"></i>Bienvenido, Empleado Demo</h2>
        </div>
        
        <div class="card-body">
            <div class="alert alert-info mb-4">
                <i class="fas fa-info-circle me-2"></i>Escanea el código QR del edificio para iniciar tu tarea diaria.
            </div>
            
            <div class="text-center">
                <div id="qr-reader" class="position-relative" style="max-width: 600px; margin: 0 auto;">
                    <div class="scanner-overlay"></div>
                    <div class="spinner-border text-primary scanner-loading" role="status" style="display: none;">
                        <span class="visually-hidden">Inicializando escáner...</span>
                    </div>
                </div>
            </div>
            
            <div id="task-status" class="mt-4"></div>
        </div>
    </div>
</div>

<script>
    // Datos iniciales para JavaScript
    const edificioQR = "Edificio 1";
</script>