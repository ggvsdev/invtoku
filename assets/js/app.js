/**
 * Sistema de Inventario General - App Principal
 */

const app = {
    baseUrl: document.querySelector('body')?.dataset.baseUrl || '/sig/',
    apiUrl: document.querySelector('body')?.dataset.baseUrl + 'api/' || '/sig/api/',
    
    init: function() {
        this.setupAjax();
        this.setupCSRF();
    },
    
    setupAjax: function() {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            error: function(xhr, status, error) {
                if (xhr.status === 401) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Sesión expirada',
                        text: 'Por favor, inicie sesión nuevamente',
                        confirmButtonColor: '#dc2626'
                    }).then(() => {
                        window.location.href = app.baseUrl;
                    });
                }
            }
        });
    },
    
    setupCSRF: function() {
        // Actualizar token CSRF en cada petición POST/PUT/DELETE
        $(document).ajaxComplete(function(event, xhr, settings) {
            if (settings.type !== 'GET') {
                const newToken = xhr.getResponseHeader('X-CSRF-TOKEN');
                if (newToken) {
                    $('meta[name="csrf-token"]').attr('content', newToken);
                }
            }
        });
    },
    
    logout: function() {
        Swal.fire({
            title: '¿Cerrar sesión?',
            text: "¿Está seguro que desea salir del sistema?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Sí, salir',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post(app.apiUrl + 'auth/logout', function() {
                    window.location.href = app.baseUrl;
                });
            }
        });
    },
    
    // Utilidades
    formatNumber: function(num) {
        return new Intl.NumberFormat('es-AR').format(num);
    },
    
    formatDate: function(date) {
        return new Intl.DateTimeFormat('es-AR', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        }).format(new Date(date));
    },
    
    playSound: function(type = 'success') {
        const audio = new Audio();
        audio.src = type === 'success' 
            ? 'https://assets.mixkit.co/active_storage/sfx/2000/2000-preview.mp3'
            : 'https://assets.mixkit.co/active_storage/sfx/2003/2003-preview.mp3';
        audio.volume = 0.3;
        audio.play().catch(e => console.log('Audio play failed'));
    }
};

// Inicializar al cargar
$(document).ready(function() {
    app.init();
});