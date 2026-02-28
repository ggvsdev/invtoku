<?php
require_once ROOT_PATH . 'views/layouts/header.php';
require_once ROOT_PATH . 'views/layouts/sidebar.php';
?>

<div class="main-content">
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold mb-0">Dashboard</h2>
            <span class="text-muted"><?= date('d/m/Y H:i') ?></span>
        </div>
        
        <!-- Bienvenida -->
        <div class="card border-0 shadow-lg mb-4 bg-gradient-dark text-white" style="background: linear-gradient(135deg, #0a0a0a 0%, #dc2626 100%);">
            <div class="card-body p-5">
                <h3 class="fw-bold">¡Bienvenido, <?= $_SESSION['user_name'] ?>!</h3>
                <p class="mb-0 opacity-75">Sistema de Inventario General - Panel de Control</p>
            </div>
        </div>
        
        <!-- Estado del Inventario -->
        <div class="row">
            <div class="col-md-8">
                <div class="card border-0 shadow h-100">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0">Estado del Inventario</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($stats['sesion_activa']): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i>
                            Sesión activa: <strong><?= $stats['sesion_activa']['nombre'] ?></strong>
                        </div>
                        <canvas id="chartConteos" height="200"></canvas>
                        <?php else: ?>
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-box-open fa-4x mb-3"></i>
                            <p>No hay sesión de inventario activa</p>
                            <a href="<?= BASE_URL ?>inventario/panel" class="btn btn-danger">Iniciar Sesión</a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card border-0 shadow h-100">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Acceso Rápido</h5>
                    </div>
                    <div class="list-group list-group-flush">
                        <a href="<?= BASE_URL ?>inventario/conteo" class="list-group-item list-group-item-action py-3">
                            <i class="fas fa-barcode text-danger me-3"></i>
                            <div>
                                <h6 class="mb-0">Scanner</h6>
                                <small class="text-muted">Conteo rápido</small>
                            </div>
                        </a>
                        <a href="<?= BASE_URL ?>productos" class="list-group-item list-group-item-action py-3">
                            <i class="fas fa-boxes text-primary me-3"></i>
                            <div>
                                <h6 class="mb-0">Productos</h6>
                                <small class="text-muted">Gestión de catálogo</small>
                            </div>
                        </a>
                        <a href="<?= BASE_URL ?>inventario/historial" class="list-group-item list-group-item-action py-3">
                            <i class="fas fa-history text-success me-3"></i>
                            <div>
                                <h6 class="mb-0">Historial</h6>
                                <small class="text-muted">Sesiones anteriores</small>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($stats['sesion_activa']): ?>
<script>
const ctx = document.getElementById('chartConteos').getContext('2d');
new Chart(ctx, {
    type: 'doughnut',
    data: {
        labels: ['Productos Contados', 'Pendientes'],
        datasets: [{
            data: [<?= $stats['conteos']['productos_distintos'] ?? 0 ?>, 100],
            backgroundColor: ['#dc2626', '#e5e7eb'],
            borderWidth: 0
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});
</script>
<?php endif; ?>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>