<?php
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
$currentDir = basename(dirname($_SERVER['PHP_SELF']));
?>
<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <img src="<?= BASE_URL ?>assets/img/logo.png" alt="SIG" class="logo-img">
        <h4 class="mb-0">SIG</h4>
        <small>Sistema de Inventario</small>
    </div>
    
    <nav class="sidebar-nav">
        <a href="<?= BASE_URL ?>dashboard" class="nav-link <?= $currentDir == 'dashboard' ? 'active' : '' ?>">
            <i class="fas fa-tachometer-alt"></i>
            <span>Dashboard</span>
        </a>
        
        <div class="nav-section">Inventario</div>
        <a href="<?= BASE_URL ?>inventario/panel" class="nav-link <?= $currentDir == 'inventario' && $currentPage == 'panel' ? 'active' : '' ?>">
            <i class="fas fa-clipboard-list"></i>
            <span>Panel de Control</span>
        </a>
        <a href="<?= BASE_URL ?>inventario/conteo" class="nav-link <?= $currentPage == 'conteo' ? 'active' : '' ?>">
            <i class="fas fa-barcode"></i>
            <span>Scanner / Conteo</span>
        </a>
        <a href="<?= BASE_URL ?>inventario/historial" class="nav-link <?= $currentPage == 'historial' ? 'active' : '' ?>">
            <i class="fas fa-history"></i>
            <span>Historial</span>
        </a>
        
        <div class="nav-section">Productos</div>
        <a href="<?= BASE_URL ?>productos" class="nav-link <?= $currentDir == 'productos' ? 'active' : '' ?>">
            <i class="fas fa-boxes"></i>
            <span>Gestión de Productos</span>
        </a>
        
        <?php if ($_SESSION['user_role'] == 'admin'): ?>
        <div class="nav-section">Administración</div>
        <a href="<?= BASE_URL ?>api/reportes.php?action=diferencias" class="nav-link">
            <i class="fas fa-file-alt"></i>
            <span>Reportes</span>
        </a>
        <?php endif; ?>
    </nav>
    
    <div class="sidebar-footer">
        <div class="user-info">
            <i class="fas fa-user-circle"></i>
            <div>
                <small><?= $_SESSION['user_name'] ?></small>
                <small class="text-muted"><?= ucfirst($_SESSION['user_role']) ?></small>
            </div>
        </div>
        <button onclick="app.logout()" class="btn btn-outline-danger btn-sm w-100 mt-2">
            <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
        </button>
    </div>
</div>