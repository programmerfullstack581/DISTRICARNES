// Hacer disponible globalmente para el callback de Google
window.decodeJwtResponse = function(token) {
    try {
        var base64Url = token.split('.')[1];
        if (!base64Url) return {};
        var base64 = base64Url.replace(/-/g, '+').replace(/_/g, '/');
        var jsonPayload = decodeURIComponent(atob(base64).split('').map(function(c) {
            return '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2);
        }).join(''));
        return JSON.parse(jsonPayload);
    } catch (e) {
        return {};
    }
};

window.handleCredentialResponse = async function(response) {
    const responsePayload = decodeJwtResponse(response.credential);
    console.log('GSI callback recibido', responsePayload && responsePayload.email ? responsePayload.email : '(sin email)');
    try {
        const res = await fetch('../backend/php/auth/google_login.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'credential=' + response.credential
        });
        let result;
        const contentType = res.headers.get('content-type');
        if (contentType && contentType.includes('application/json')) {
            result = await res.json();
        } else {
            const text = await res.text();
            console.error('Respuesta no JSON del servidor Google Login:', text);
            Swal.fire({
                icon: 'error',
                title: 'Error de autenticación',
                text: (text && text.trim().length > 0) ? text.slice(0, 300) : 'El servidor devolvió una respuesta inesperada.',
                confirmButtonColor: '#e50914',
                background: '#000000',
                color: '#ffffff'
            });
            return;
        }
        if (result.success) {
            const userData = {
                isLoggedIn: true,
                user: result.user,
                loginTime: new Date().toISOString()
            };
            localStorage.setItem('userData', JSON.stringify(userData));
            sessionStorage.setItem('currentSession', JSON.stringify(userData));
            window.dispatchEvent(new CustomEvent('auth:loggedIn'));
            const redirect = (() => {
                if (result.redirect_url) {
                    return result.redirect_url.startsWith('/') ? 'https://districarnes-83qm.onrender.com' + result.redirect_url : result.redirect_url;
                }
                return 'https://districarnes-83qm.onrender.com/admin/admin_dashboard.html';
            })();
            const displayName = result.user.nombre || result.user.nombres_completos || result.user.email || 'Usuario';
            const displayEmail = result.user.email || result.user.correo_electronico || '';
            Swal.fire({
                icon: 'success',
                title: `¡Bienvenido ${displayName}!`,
                text: displayEmail ? `Sesión iniciada como ${displayEmail}. Redirigiendo...` : 'Inicio de sesión exitoso. Redirigiendo...',
                timer: 2000,
                timerProgressBar: true,
                showConfirmButton: false,
                confirmButtonColor: '#e50914',
                background: '#000000',
                color: '#ffffff'
            });
            setTimeout(() => {
                window.location.href = redirect;
            }, 2000);
        } else {
            console.error('Google Login fallo:', result && result.message ? result.message : '(sin mensaje)');
            Swal.fire({
                icon: 'error',
                title: 'Error de inicio de sesión con Google',
                text: result.message || 'No se pudo iniciar sesión con Google.',
                confirmButtonColor: '#e50914',
                background: '#000000',
                color: '#ffffff'
            });
        }
    } catch (error) {
        console.error('Error durante el inicio de sesión con Google:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error inesperado',
            text: error && error.message ? error.message : 'Ocurrió un error inesperado al intentar iniciar sesión con Google. Por favor, inténtalo más tarde.',
            confirmButtonColor: '#e50914',
            background: '#000000',
            color: '#ffffff'
        });
    }
};

document.addEventListener('DOMContentLoaded', function() {
    // Obtener elementos del DOM (con verificación de existencia)
    const loginForm = document.getElementById('loginForm');
    const passwordError = document.getElementById('passwordError');
    const togglePassword = document.getElementById('togglePassword1');
    const passwordInput = document.getElementById('password');
    const emailInput = document.getElementById('email');
    const loginButton = document.getElementById('loginButton');

    // Si no existe el formulario, salir silenciosamente
    if (!loginForm) return;

    try {
        const params = new URLSearchParams(window.location.search || '');
        const verified = params.get('verified');
        if (verified === '1') {
            Swal.fire({
                icon: 'success',
                title: 'Correo verificado',
                text: 'Tu cuenta ya está verificada. Ya puedes iniciar sesión.',
                confirmButtonColor: '#e50914',
                background: '#000000',
                color: '#000000'
            });
        } else if (verified === '0') {
            const reason = params.get('reason') || '';
            let msg = 'No se pudo verificar el correo. Solicita un nuevo enlace iniciando sesión.';
            if (reason === 'token_expired') msg = 'El enlace de verificación expiró. Inicia sesión para recibir uno nuevo.';
            Swal.fire({
                icon: 'error',
                title: 'Verificación fallida',
                text: msg,
                confirmButtonColor: '#dc3545',
                background: '#ffffff',
                color: '#000000'
            });
        }
    } catch (e) { }

    // Mostrar/ocultar contraseña (solo si existen los elementos)
    if (togglePassword && passwordInput) {
        togglePassword.addEventListener('click', function() {
            const isPassword = passwordInput.type === 'password';
            passwordInput.type = isPassword ? 'text' : 'password';
            this.textContent = isPassword ? '🔒' : '👁️';
        });
    }

    // Manejar el envío del formulario
    loginForm.addEventListener('submit', async function(e) {
        e.preventDefault();

        // Validar que los campos existan y tengan valor
        const email = emailInput ? emailInput.value.trim() : '';
        const password = passwordInput ? passwordInput.value : '';

        if (!email) {
            Swal.fire({
                icon: 'warning',
                title: 'Campo requerido',
                text: 'Por favor ingresa tu correo, nombre o cédula.',
                confirmButtonColor: '#dc3545',
                background: '#ffffff',
                color: '#000000'
            });
            return;
        }
        if (!password) {
            Swal.fire({
                icon: 'warning',
                title: 'Campo requerido',
                text: 'Por favor ingresa tu contraseña.',
                confirmButtonColor: '#dc3545',
                background: '#ffffff',
                color: '#000000'
            });
            return;
        }

        // Guardar texto original del botón
        const originalText = loginButton ? loginButton.innerHTML : 'Iniciar sesión';

        // Estado de carga
        if (loginButton) {
            loginButton.disabled = true;
            loginButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Iniciando sesión...';
        }

        try {
            // Ruta corregida desde el directorio login
            const response = await fetch('../backend/php/auth/login_verify.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({ email, password })
            });

            // Verificar si la respuesta es JSON válido
            let result;
            const contentType = response.headers.get('content-type');
            if (contentType && contentType.includes('application/json')) {
                result = await response.json();
            } else {
                // Si no es JSON, probablemente es un error 404 o 500
                throw new Error('El servidor no respondió correctamente.');
            }

            // Procesar respuesta
            if (result.success) {
                // Guardar datos completos del usuario
                const userData = {
                    isLoggedIn: true,
                    user: result.user,
                    loginTime: new Date().toISOString()
                };
                
                // Guardar en localStorage (persiste entre sesiones)
                localStorage.setItem('userData', JSON.stringify(userData));
                
                // Guardar en sessionStorage (solo para la sesión actual)
                sessionStorage.setItem('currentSession', JSON.stringify(userData));

                // Dispatch global logged-in event
                window.dispatchEvent(new CustomEvent('auth:loggedIn'));
                
                // Mostrar mensaje de éxito con SweetAlert2
                Swal.fire({
                    icon: 'success',
                    title: `¡Bienvenido ${result.user.nombre}!`,
                    text: result.message || 'Inicio de sesión exitoso. Redirigiendo...',
                    timer: 2000,
                    timerProgressBar: true,
                    showConfirmButton: false,
                    confirmButtonColor: '#dc3545',
                    background: '#ffffff',
                    color: '#000000'
                });
                
                // Mostrar estado en el botón
                if (loginButton) {
                    loginButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> ¡Éxito! Redirigiendo...';
                }
                
                // Redirección usando la URL proporcionada por el servidor
                setTimeout(() => {
                    if (result.redirect_url) {
                        // Usar la URL de redirección del servidor
                        if (result.redirect_url.startsWith('/')) {
                            // URL relativa, agregar ../ para salir del directorio login
                            window.location.href = '..' + result.redirect_url;
                        } else {
                            window.location.href = result.redirect_url;
                        }
                    } else {
                        // Fallback: redirigir siempre al panel de administración
                        window.location.href = 'https://districarnes-83qm.onrender.com/admin/admin_dashboard.html';
                    }
                }, 2000);
            } else {
                if (result && result.needs_verification) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Verifica tu correo',
                        text: result.message || 'Debes verificar tu correo para poder iniciar sesión.',
                        confirmButtonColor: '#dc3545',
                        background: '#ffffff',
                        color: '#000000'
                    });
                } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error de autenticación',
                    text: result.message || 'Credenciales incorrectas. Verifica tu email y contraseña.',
                    confirmButtonColor: '#dc3545',
                    background: '#ffffff',
                    color: '#000000'
                });
                }
            }

        } catch (error) {
            console.error('Error durante el inicio de sesión:', error);
            // Distinguir entre error de red y otro tipo
            if (error.message.includes('fetch') || error.message.includes('Failed to fetch')) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error de conexión',
                    text: 'No se pudo conectar al servidor. Verifica tu conexión a internet.',
                    confirmButtonColor: '#dc3545',
                    background: '#ffffff',
                    color: '#000000'
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error inesperado',
                    text: 'Ocurrió un error inesperado. Por favor, inténtalo más tarde.',
                    confirmButtonColor: '#dc3545',
                    background: '#ffffff',
                    color: '#000000'
                });
            }
        } finally {
            // Restaurar botón
            if (loginButton) {
                loginButton.disabled = false;
                loginButton.innerHTML = originalText;
            }
        }
    });

    // Función para mostrar errores (mantenida para compatibilidad)
    function showError(message) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: message,
            confirmButtonColor: '#dc3545',
            background: '#ffffff',
            color: '#000000'
        });
    }

    // Facebook SDK Initialization
    window.fbAsyncInit = function() {
        FB.init({
            appId      : '809276405052275', // Your App ID
            cookie     : true,  // Enable cookies to allow the server to access the session
            xfbml      : true,  // Parse social plugins on this page
            version    : 'v19.0' // Use a recent Graph API version
        });

        // Render Facebook social plugins (like the login button)
        FB.XFBML.parse();
    };

    // This function is called by the Facebook SDK when the user logs in
    window.checkLoginState = function(response) {
        if (response.authResponse) {
            const accessToken = response.authResponse.accessToken;
            sendFacebookTokenToBackend(accessToken);
        } else {
            Swal.fire({
                icon: 'warning',
                title: 'Inicio de sesión cancelado',
                text: 'No se pudo iniciar sesión con Facebook.',
                confirmButtonColor: '#dc3545',
                background: '#ffffff',
                color: '#000000'
            });
        }
    };

    // Function to send Facebook Access Token to backend
    async function sendFacebookTokenToBackend(accessToken) {
        try {
                        const response = await fetch('../backend/php/auth/facebook_login.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: new URLSearchParams({ accessToken: accessToken })
                        });
            
                        const result = await response.json();
            
                        if (result.success) {
                            const userData = {
                                isLoggedIn: true,
                                user: result.user,
                                loginTime: new Date().toISOString()
                            };
                            localStorage.setItem('userData', JSON.stringify(userData));
                            sessionStorage.setItem('currentSession', JSON.stringify(userData));
            
                            // Dispatch global logged-in event
                            window.dispatchEvent(new CustomEvent('auth:loggedIn'));
            
                            Swal.fire({
                                icon: 'success',
                                title: `¡Bienvenido ${result.user.nombre}!`, 
                                text: result.message || 'Inicio de sesión exitoso. Redirigiendo...', 
                                timer: 2000,
                                timerProgressBar: true,
                                showConfirmButton: false,
                                confirmButtonColor: '#dc3545',
                                background: '#ffffff',
                                color: '#000000'
                            });
                setTimeout(() => {
                    if (result.redirect_url) {
                        window.location.href = 'https://districarnes-83qm.onrender.com' + result.redirect_url;
                    } else {
                        window.location.href = 'https://districarnes-83qm.onrender.com/admin/admin_dashboard.html';
                    }
                }, 2000);

            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error de autenticación con Facebook',
                    text: result.message || 'No se pudo iniciar sesión con Facebook.',
                    confirmButtonColor: '#dc3545',
                    background: '#ffffff',
                    color: '#000000'
                });
            }

        } catch (error) {
            console.error('Error durante el inicio de sesión con Facebook:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error inesperado',
                text: 'Ocurrió un error inesperado al intentar iniciar sesión con Facebook. Por favor, inténtalo más tarde.',
                confirmButtonColor: '#dc3545',
                background: '#ffffff',
                color: '#000000'
            });
        }
    }

    // Google Sign-In Functions (moved to global scope arriba)

    

    // --- INICIO: LÓGICA PARA INICIO DE SESIÓN CON TELÉFONO (FLUJO CON SWEETALERT2) ---
    const phoneLoginBtn = document.getElementById('phoneLoginBtn');

    if (phoneLoginBtn) {
        phoneLoginBtn.addEventListener('click', () => {
            Swal.fire({
                title: 'Iniciar sesión con teléfono',
                text: 'Ingresa tu número de teléfono para enviarte un código de verificación.',
                input: 'tel',
                inputPlaceholder: 'Ej: 3101234567',
                inputAttributes: {
                    autocapitalize: 'off',
                    autocorrect: 'off'
                },
                showCancelButton: true,
                confirmButtonText: 'Enviar Código',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#dc3545',
                showLoaderOnConfirm: true,
                preConfirm: (phone) => {
                    if (!phone) {
                        Swal.showValidationMessage('Por favor, ingresa un número de teléfono');
                        return false;
                    }
                    return fetch('../backend/api/send_verification_code.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: new URLSearchParams({ phone: phone })
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(response.statusText);
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (!data.success) {
                            throw new Error(data.message || 'Error desconocido');
                        }
                        // Pasar el teléfono y el código simulado al siguiente paso
                        return { phone: phone, code: data.code };
                    })
                    .catch(error => {
                        Swal.showValidationMessage(`Error: ${error.message}`);
                    });
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                if (result.isConfirmed) {
                    const { phone, code } = result.value;

                    // Notificación Toast de éxito
                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 10000,
                        timerProgressBar: true,
                        didOpen: (toast) => {
                            toast.addEventListener('mouseenter', Swal.stopTimer);
                            toast.addEventListener('mouseleave', Swal.resumeTimer);
                        }
                    });

                    Toast.fire({
                        icon: 'success',
                        title: `Código enviado a ${phone}`,
                        text: `Código de prueba: ${code}` // SIMULACIÓN: Mostrar el código
                    });

                    // Segundo popup para verificar el código
                    return Swal.fire({
                        title: 'Verifica tu identidad',
                        text: `Ingresa el código de 6 dígitos que "enviamos" a ${phone}`,
                        input: 'text',
                        inputPlaceholder: '123456',
                        inputAttributes: {
                            maxlength: 6,
                            autocapitalize: 'off',
                            autocorrect: 'off'
                        },
                        showCancelButton: true,
                        confirmButtonText: 'Verificar e Iniciar Sesión',
                        confirmButtonColor: '#007bff',
                        showLoaderOnConfirm: true,
                        preConfirm: (verificationCode) => {
                            return fetch('../backend/api/verify_code.php', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                                body: new URLSearchParams({ code: verificationCode, phone: phone })
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (!data.success) {
                                    throw new Error(data.message);
                                }
                                return data;
                            })
                            .catch(error => {
                                Swal.showValidationMessage(`Error: ${error.message}`);
                            });
                        },
                        allowOutsideClick: () => !Swal.isLoading()
                    });
                }
            }).then((result) => {
                if (result && result.isConfirmed) {
                    const data = result.value;
                    // Lógica de inicio de sesión exitoso
                    const userData = {
                        isLoggedIn: true,
                        user: data.user,
                        loginTime: new Date().toISOString()
                    };
                    localStorage.setItem('userData', JSON.stringify(userData));
                    sessionStorage.setItem('currentSession', JSON.stringify(userData));
                    window.dispatchEvent(new CustomEvent('auth:loggedIn'));

                    const Toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 3000, timerProgressBar: true });
                    Toast.fire({
                        icon: 'success',
                        title: `¡Bienvenido, ${data.user.nombre}!`
                    });

                    setTimeout(() => {
                        window.location.href = 'https://districarnes-83qm.onrender.com/admin/admin_dashboard.html';
                    }, 3000);
                }
            });
        });
    }
    // --- FIN: LÓGICA PARA INICIO DE SESIÓN CON TELÉFONO ---
});
