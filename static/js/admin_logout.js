function logout() {
    Swal.fire({
        title: '¿Cerrar Sesión?',
        text: '¿Estás seguro de que deseas cerrar sesión?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#ff0000',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, cerrar sesión',
        cancelButtonText: 'Cancelar',
        background: '#1a1a1a',
        color: '#ffffff',
        customClass: {
            popup: 'swal-dark-theme'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // Limpiar datos de sesión
            localStorage.removeItem('userData');
            sessionStorage.clear();
            
            // Reemplazar estado del historial para que no puedan volver atrás
            window.history.replaceState(null, null, 'https://districarnes-83qm.onrender.com/index.php');
            
            // Redireccionar usando replace para no dejar rastro en el historial
            window.location.replace('https://districarnes-83qm.onrender.com/index.php');
        }
    });
}
