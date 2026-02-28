<?php
$extraScripts = '<script src="' . BASE_URL . 'assets/js/inventario.js"></script>';
require_once ROOT_PATH . 'views/layouts/header.php';
require_once ROOT_PATH . 'views/layouts/sidebar.php';
?>

<div class="main-content">
    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold mb-0"><i class="fas fa-barcode text-danger me-2"></i>Scanner de Inventario</h2>
                <p class="text-muted mb-0">Sesión: <span id="sesionNombre" class="fw-bold"><?= $sesion['nombre'] ?></span></p>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-dark" onclick="inventario.toggleSound()">
                    <i class="fas fa-volume-up" id="soundIcon"></i>
                </button>
                <a href="<?= BASE_URL ?>inventario/panel" class="btn btn-outline-danger">
                    <i class="fas fa-arrow-left me-2"></i>Volver al Panel
                </a>
            </div>
        </div>

        <div class="row">
            <!-- Scanner Principal -->
            <div class="col-lg-5 mb-4">
                <div class="card border-0 shadow-lg" style="border-top: 4px solid #dc2626 !important;">
                    <div class="card-header bg-black text-white">
                        <h5 class="mb-0"><i class="fas fa-qrcode me-2"></i>Entrada de Código</h5>
                    </div>
                    <div class="card-body p-4">
                        <form id="scanForm">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Código de Barra / Producto</label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text bg-danger text-white border-danger">
                                        <i class="fas fa-barcode"></i>
                                    </span>
                                    <input type="text" 
                                           class="form-control form-control-lg border-danger" 
                                           id="codigoInput" 
                                           placeholder="Escanea o escribe el código..."
                                           autocomplete="off"
                                           autofocus>
                                    <button class="btn btn-danger" type="submit">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                                <small class="text-muted">Presiona ENTER o el botón + para agregar</small>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">Cantidad</label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text bg-dark text-white">
                                        <i class="fas fa-sort-numeric-up"></i>
                                    </span>
                                    <input type="number" 
                                           class="form-control form-control-lg" 
                                           id="cantidadInput" 
                                           value="1" 
                                           min="0.01" 
                                           step="0.01">
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-dark btn-lg w-100 py-3">
                                <i class="fas fa-check-circle me-2"></i>REGISTRAR CONTEO
                            </button>
                        </form>
                        
                        <!-- Último producto escaneado -->
                        <div id="lastProduct" class="mt-4 p-3 bg-light rounded d-none">
                            <h6 class="text-muted mb-2">Último Producto:</h6>
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                        <i class="fas fa-check fa-lg"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="mb-0" id="lastProductName">-</h5>
                                    <small class="text-muted" id="lastProductCode">-</small>
                                </div>
                                <div class="text-end">
                                    <h4 class="mb-0 text-success" id="lastProductQty">+0</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Estadísticas Rápidas -->
                <div class="card border-0 shadow mt-4 bg-dark text-white">
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-4">
                                <h3 class="mb-0 text-danger" id="statProductos">0</h3>
                                <small>Productos</small>
                            </div>
                            <div class="col-4 border-start border-secondary">
                                <h3 class="mb-0 text-white" id="statUnidades">0</h3>
                                <small>Unidades</small>
                            </div>
                            <div class="col-4 border-start border-secondary">
                                <h3 class="mb-0 text-warning" id="statContadores">0</h3>
                                <small>Contadores</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Lista en Tiempo Real -->
            <div class="col-lg-7">
                <div class="card border-0 shadow-lg h-100">
                    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-list-ol text-danger me-2"></i>Conteos en Tiempo Real</h5>
                        <span class="badge bg-danger" id="liveIndicator">
                            <i class="fas fa-circle fa-xs me-1 animate-pulse"></i>EN VIVO
                        </span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                            <table class="table table-hover mb-0" id="tablaConteos">
                                <thead class="table-dark sticky-top">
                                    <tr>
                                        <th>Hora</th>
                                        <th>Código</th>
                                        <th>Producto</th>
                                        <th class="text-center">Cant.</th>
                                        <th>Contador</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody id="listaConteos">
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-muted">
                                            <i class="fas fa-spinner fa-spin fa-2x mb-3 d-block"></i>
                                            Esperando conteos...
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .animate-pulse {
        animation: pulse 2s infinite;
    }
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.5; }
    }
    .sticky-top {
        position: sticky;
        top: 0;
        z-index: 10;
    }
</style>

<?php require_once ROOT_PATH . 'views/layouts/footer.php'; ?>