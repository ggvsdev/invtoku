/**
 * Módulo de Inventario - Conteo en Tiempo Real
 */

const inventario = {
    sesionId: null,
    pollingInterval: null,
    soundEnabled: true,
    
    init: function() {
        this.setupScanner();
        this.startRealtimeUpdates();
        this.loadInitialData();
    },
    
    setupScanner: function() {
        const self = this;
        
        $('#scanForm').on('submit', function(e) {
            e.preventDefault();
            self.registrarConteo();
        });
        
        // Auto-focus en el input de código
        $('#codigoInput').focus();
        
        // Mantener focus después de cualquier click
        $(document).on('click', function(e) {
            if (!$(e.target).is('input, button, a')) {
                $('#codigoInput').focus();
            }
        });
        
        // Atajo de teclado: F9 para cantidad
        $(document).on('keydown', function(e) {
            if (e.key === 'F9') {
                e.preventDefault();
                $('#cantidadInput').select().focus();
            }
            if (e.key === 'F10') {
                e.preventDefault();
                $('#codigoInput').focus();
            }
        });
    },
    
    registrarConteo: function() {
        const codigo = $('#codigoInput').val().trim();
        const cantidad = parseFloat($('#cantidadInput').val()) || 1;
        
        if (!codigo) {
            this.showError('Ingrese un código');
            return;
        }
        
        if (cantidad <= 0) {
            this.showError('La cantidad debe ser mayor a cero');
            return;
        }
        
        // Efecto visual de escaneo
        this.animateScan();
        
        $.ajax({
            url: app.apiUrl + 'inventario/conteo',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                codigo: codigo,
                cantidad: cantidad,
                csrf_token: $('meta[name="csrf-token"]').attr('content')
            }),
            success: (response) => {
                if (response.success) {
                    this.handleSuccess(response.data);
                } else {
                    this.showError(response.message);
                }
            },
            error: (xhr) => {
                const response = xhr.responseJSON || {};
                this.showError(response.message || 'Error al registrar conteo');
            }
        });
    },
    
    handleSuccess: function(data) {
        // Sonido de éxito
        if (this.soundEnabled) {
            app.playSound('success');
        }
        
        // Mostrar último producto
        $('#lastProduct').removeClass('d-none').addClass('fade-in');
        $('#lastProductName').text(data.producto.nombre);
        $('#lastProductCode').text(data.producto.codigo);
        $('#lastProductQty').text('+' + data.conteo.cantidad);
        
        // Limpiar y preparar siguiente
        $('#codigoInput').val('').focus();
        $('#cantidadInput').val('1');
        
        // Actualizar lista inmediatamente
        this.updateStats();
        this.loadConteos();
        
        // Toast de éxito
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'success',
            title: `${data.producto.nombre} (+${data.conteo.cantidad})`,
            showConfirmButton: false,
            timer: 2000,
            timerProgressBar: true
        });
    },
    
    showError: function(message) {
        if (this.soundEnabled) {
            app.playSound('error');
        }
        
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'error',
            title: message,
            showConfirmButton: false,
            timer: 3000
        });
        
        $('#codigoInput').select().focus();
    },
    
    animateScan: function() {
        $('#codigoInput').addClass('is-valid');
        setTimeout(() => $('#codigoInput').removeClass('is-valid'), 300);
    },
    
    startRealtimeUpdates: function() {
        // Actualizar cada 3 segundos
        this.pollingInterval = setInterval(() => {
            this.updateStats();
            this.loadConteos();
        }, 3000);
    },
    
    loadInitialData: function() {
        this.updateStats();
        this.loadConteos();
    },
    
    updateStats: function() {
        $.get(app.apiUrl + 'inventario/conteos')
            .done((response) => {
                if (response.success) {
                    const stats = response.data.stats;
                    $('#statProductos').text(stats.productos_distintos || 0);
                    $('#statUnidades').text(app.formatNumber(stats.total_unidades || 0));
                    $('#statContadores').text(stats.total_contadores || 0);
                }
            });
    },
    
    loadConteos: function() {
        $.get(app.apiUrl + 'inventario/conteos')
            .done((response) => {
                if (response.success && response.data.ultimos) {
                    this.renderConteos(response.data.ultimos);
                }
            });
    },
    
    renderConteos: function(conteos) {
        const tbody = $('#listaConteos');
        tbody.empty();
        
        if (conteos.length === 0) {
            tbody.html(`
                <tr>
                    <td colspan="6" class="text-center py-5 text-muted">
                        <i class="fas fa-box-open fa-2x mb-3 d-block"></i>
                        No hay conteos registrados
                    </td>
                </tr>
            `);
            return;
        }
        
        conteos.forEach((conteo, index) => {
            const hora = new Date(conteo.created_at).toLocaleTimeString('es-AR');
            const row = `
                <tr class="${index === 0 ? 'table-success' : ''}">
                    <td class="font-monospace">${hora}</td>
                    <td><code>${conteo.codigo_barra || conteo.codigo_producto}</code></td>
                    <td class="fw-bold">${conteo.producto_nombre}</td>
                    <td class="text-center">
                        <span class="badge bg-dark">+${conteo.cantidad}</span>
                    </td>
                    <td>${conteo.contador_nombre}</td>
                    <td>
                        ${index === 0 ? '<span class="badge bg-success"><i class="fas fa-check me-1"></i>Último</span>' : '<span class="badge bg-secondary">OK</span>'}
                    </td>
                </tr>
            `;
            tbody.append(row);
        });
    },
    
    toggleSound: function() {
        this.soundEnabled = !this.soundEnabled;
        $('#soundIcon').toggleClass('fa-volume-up fa-volume-mute');
        localStorage.setItem('sig_sound', this.soundEnabled);
    },
    
    pausarSesion: function(id) {
        Swal.fire({
            title: '¿Pausar sesión?',
            text: "Los conteos se detendrán temporalmente",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#f59e0b',
            confirmButtonText: 'Pausar'
        }).then((result) => {
            if (result.isConfirmed) {
                // Implementar pausa
                location.reload();
            }
        });
    },
    
    cerrarSesion: function(id) {
        Swal.fire({
            title: '¿Cerrar sesión de inventario?',
            text: "Esta acción no se puede deshacer. Se generará el reporte final.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            confirmButtonText: 'Sí, cerrar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: app.apiUrl + 'inventario/cerrar',
                    method: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify({ sesion_id: id }),
                    success: () => {
                        Swal.fire('Cerrada', 'La sesión ha sido cerrada correctamente', 'success')
                            .then(() => location.reload());
                    }
                });
            }
        });
    }
};

// Inicializar si estamos en la página de conteo
if ($('#scanForm').length) {
    $(document).ready(() => inventario.init());
}