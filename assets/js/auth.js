/**
 * Módulo de Autenticación
 */

const auth = {
    login: function(username, password) {
        return $.ajax({
            url: app.apiUrl + 'auth/login',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                username: username,
                password: password,
                csrf_token: $('meta[name="csrf-token"]').attr('content')
            })
        });
    },
    
    checkSession: function() {
        // Verificar sesión cada 5 minutos
        setInterval(() => {
            $.get(app.apiUrl + 'auth/check')
                .fail((xhr) => {
                    if (xhr.status === 401) {
                        window.location.reload();
                    }
                });
        }, 300000);
    }
};