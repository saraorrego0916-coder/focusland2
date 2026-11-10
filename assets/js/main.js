/**
 * FocusLand - Lógica para el menú de navegación móvil
 */
document.addEventListener('DOMContentLoaded', () => {
    const menuToggle = document.getElementById('menuToggle');
    const navMenu = document.querySelector('.nav-menu') || document.querySelector('.navbar nav') || document.querySelector('.nav-links');

    if (menuToggle && navMenu) {
        menuToggle.addEventListener('click', () => {
            // Alterna la clase active para abrir o cerrar el menú móvil
            navMenu.classList.toggle('active');

            // Actualiza accesibilidad
            const isExpanded = navMenu.classList.contains('active');
            menuToggle.setAttribute('aria-expanded', isExpanded);
        });

        // Opcional: Cerrar el menú al hacer clic en un enlace cuando la pantalla sea móvil
        const navLinks = navMenu.querySelectorAll('a');
        navLinks.forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth <= 850) {
                    navMenu.classList.remove('active');
                    menuToggle.setAttribute('aria-expanded', 'false');
                }
            });
        });
    }

    // =========================================================================
    // 2. Validación rápida en Frontend para Formularios de Autenticación
    //    (login.php y registrar.php)
    // =========================================================================
    const authForms = document.querySelectorAll('.auth-form');

    authForms.forEach(form => {
        form.addEventListener('submit', (e) => {
            const inputs = form.querySelectorAll('input[required]');
            let hasEmpty = false;
            let firstEmptyInput = null;

            // Limpiar errores visuales previos
            inputs.forEach(input => input.classList.remove('input-field-error'));

            // Validar si algún campo requerido está vacío
            inputs.forEach(input => {
                if (!input.value.trim()) {
                    hasEmpty = true;
                    input.classList.add('input-field-error');
                    if (!firstEmptyInput) {
                        firstEmptyInput = input;
                    }
                }
            });

            if (hasEmpty) {
                e.preventDefault();

                // Buscar o crear contenedor de alertas
                let alertBox = form.parentElement.querySelector('#authAlertBox') || form.parentElement.querySelector('.alert-container');
                if (!alertBox) {
                    alertBox = document.createElement('div');
                    alertBox.id = 'authAlertBox';
                    alertBox.className = 'alert-container';
                    form.parentElement.insertBefore(alertBox, form);
                }

                // Desplegar inmediatamente la alerta .alert-error
                alertBox.innerHTML = `
                    <div class="alert alert-error auth-alert error" role="alert">
                        <span class="alert-icon">✕</span>
                        <span class="alert-text">✕ Por favor, completa todos los campos requeridos.</span>
                    </div>
                `;

                // Enfocar el primer input vacío
                if (firstEmptyInput) {
                    firstEmptyInput.focus();
                }

                // Desplazar suavemente a la alerta si es necesario
                alertBox.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        });

        // Limpiar estado de error cuando el usuario empiece a escribir
        const requiredInputs = form.querySelectorAll('input[required]');
        requiredInputs.forEach(input => {
            input.addEventListener('input', () => {
                input.classList.remove('input-field-error');
            });
        });
    });

    // =========================================================================
    // 3. Menú Dinámico con Sesión Activa (FocusLand)
    //    Renderiza saludo, botón "Agendar Cita" y "Cerrar Sesión" si hay sesión
    // =========================================================================
    function getCookie(name) {
        const value = `; ${document.cookie}`;
        const parts = value.split(`; ${name}=`);
        if (parts.length === 2) return decodeURIComponent(parts.pop().split(';').shift());
        return null;
    }

    function renderActiveSessionUI(nombre) {
        const navLinks = document.querySelector('.nav-links') || document.querySelector('.navbar ul');
        if (!navLinks) return;

        // Si ya está renderizado el grupo de sesión por PHP, no duplicar
        if (navLinks.querySelector('.nav-auth-group')) return;

        // Remover botón Ingresar y cualquier botón Agendar Cita huérfano previo
        const loginBtn = navLinks.querySelector('.btn-ingresar')?.closest('li');
        const agendarBtn = navLinks.querySelector('.btn-agendar')?.closest('li');

        if (loginBtn) loginBtn.remove();
        if (agendarBtn) agendarBtn.remove();

        // Crear elemento de sesión activa
        const authLi = document.createElement('li');
        authLi.className = 'nav-auth-group';
        authLi.innerHTML = `
            <div class="user-profile-badge">
                <i class="fa-solid fa-circle-user user-icon"></i>
                <span class="user-name">Hola, ${nombre}</span>
            </div>
            <a href="cita.php" class="btn-agendar">Agendar Cita</a>
            <a href="logout.php" class="btn-logout" title="Cerrar sesión">
                <i class="fa-solid fa-arrow-right-from-bracket"></i> Cerrar Sesión
            </a>
        `;
        navLinks.appendChild(authLi);
    }

    // 1) Renderizado instantáneo si la cookie existe
    const cookieUser = getCookie('focusland_user');
    if (cookieUser) {
        renderActiveSessionUI(cookieUser);
    }

    // 2) Sincronizar con el estado de sesión de PHP
    fetch('includes/session_status.php')
        .then(res => res.json())
        .then(data => {
            if (data && data.logged_in) {
                renderActiveSessionUI(data.nombre || 'Usuario');
            } else if (cookieUser && (!data || !data.logged_in)) {
                // Si la sesión expiró o se cerró, limpiar cookie y restaurar botón "Ingresar"
                document.cookie = "focusland_user=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
                const authGroup = document.querySelector('.nav-auth-group');
                if (authGroup) {
                    const navUl = authGroup.closest('ul');
                    authGroup.remove();
                    if (navUl && !navUl.querySelector('.btn-ingresar')) {
                        const li = document.createElement('li');
                        li.innerHTML = '<a href="login.php" class="btn-ingresar">Ingresar</a>';
                        navUl.appendChild(li);
                    }
                }
            }
        })
        .catch(() => {
            // Silencioso si no se encuentra disponible el endpoint
        });
});
