<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

// Extensión opcional para funcionalidades adicionales
if (!class_exists('CoreDatabase')) {
    class CoreDatabase extends Database {}
}