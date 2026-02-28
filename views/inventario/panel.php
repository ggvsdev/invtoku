<?php
require_once ROOT_PATH . 'views/layouts/header.php';
require_once ROOT_PATH . 'views/layouts/sidebar.php';
?>

<div class="main-content">
    <div class="container-fluid py-4">
        <h2 class="fw-bold mb-4"><i class="fas fa-clipboard-list text-danger me-2"></i>Panel de Inventario</h2>
        
        <?php if (!$sesion): ?>
        <!-- Crear Nueva Sesión -->
        <div class="card border-0 shadow-lg">
            <div class="card-body p-5 text-center">
                <div class="mb-4">
                    <i class="fas fa-play-circle fa-5x text-danger"></i>
                </div>
                <h3>Iniciar Nueva Sesión de Inventario</h3>
                <p class="text-muted mb-4">No hay sesiones activas actualmente</p>
                
                <form id="nuevaSesionForm" class="max-w-500 mx-auto">
                    <div class="mb-3">
                        <input type="text" class="form-control form-control-lg" id="nombreSesion" placeholder="Nombre de la sesión (ej: Inventario Enero 2024)" required>
                    </div>
                    <div class="mb-3">
                        <textarea class="form-control" id="descSesion" rows="3" placeholder="Descripción opcional..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-danger btn-lg px-5">
                        <i class="fas fa-rocket me-2"></i>Iniciar Sesión
                    </button>
                </form>
            </div>
        </div>
        
        <?php else: ?>
        <!-- Sesión Activa -->
        <div class="alert alert-success d-flex align-items-center mb-4">
            <i class="fas fa-check-circle fa-2x me-3"></i>
            <div>
                <h5 class="alert-heading mb-1">Sesión Activa: <?= $sesion['nombre'] ?></h5>
                <p class="mb-0">Iniciada el <?= date('d/m/Y H:i', strtotime($sesion['fecha_inicio'])) ?> por <?= $sesion['creador'] ?></p>
            </div>
            <div class="ms-auto">
                <a href="<?= BASE_URL ?>inventario/conteo" class="btn btn-dark btn-lg">
                    <i class="fas fa-barcode me-2"></i>Ir a Scanner
                </a>
            </div>
        </div>
        
        <!-- Estadísticas -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow bg-dark text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="text-muted">Productos Contados</h6>
                                <h3 class="mb-0"><?= $stats['productos_distintos'] ?? 0 ?></h3>
                            </div>
                            <i class="fas fa-boxes fa-2x text-danger"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="text-muted">Total Unidades</h6>
                                <h3 class="mb-0"><?= number_format($stats['total_unidades'] ?? 0) ?></h3>
                            </div>
                            <i class="fas fa-sort-amount-up fa-2x text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="text-muted">Contadores</h6>
                                <h3 class="mb-0"><?= $stats['total_contadores'] ?? 0 ?></h3>
                            </div>
                            <i class="fas fa-users fa-2x text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="text-muted">Último Conteo</h6>
                                <h5 class="mb-0"><?= $stats['ultimo_conteo'] ? date('H:i:s', strtotime($stats['ultimo_conteo'])) : '-' ?></h5>
                            </div>
                            <i class="fas fa-clock fa-2x text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Acciones -->
        <div class="card border-0 shadow">
            <div class="card-header bg-white">
                <h5 class="mb-0">Acciones de la Sesión</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <a href="<?= BASE_URL ?>inventario/conteo" class="btn btn-outline-dark w-100 py-3">
                            <i class="fas fa-barcode fa-2x mb-2 d-block"></i>
                            Continuar Conteo
                        </a>
                    </div>
                    <div class="col-md-4">
                        <button onclick="inventario.pausarSesion(<?= $sesion['id'] ?>)" class="btn btn-outline-warning w-100 py-3">
                            <i class="fas fa-pause fa-2x mb-2 d-block"></i>
                            Pausar Sesión
                        </button>
                    </div>
                    <div class="col-md-4">
                        <button onclick="inventario.cerrarSesion(<?= $sesion['id'] ?>)" class="btn btn-outline-danger w-100 py-3">
                            <i class="fas fa-flag-checkered fa-2x mb-2 d-block"></i>
                            Cerrar Sesión
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#nuevaSesionForm').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: '<?= API_URL ?>inventario/sesion',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                nombre: $('#nombreSesion').val(),
                descripcion: $('#descSesion').val(),
                csrf_token: $('meta[name="csrf-token"]').attr('content')
            }),
            success: function(response) {
                if (response.success) {
                    Swal.fire('Éxito', response.message, 'success').then(() => {
                        location.reload();
                    });
                }
            },
            error: function(xhr) {
                Swal.fire('Error', xhr.responseJSON?.message || 'Error al crear sesión', 'error');
            }
        });
    });
});
</script>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>