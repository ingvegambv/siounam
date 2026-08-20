class SidebarComponent extends HTMLElement {
    constructor() {
        super();
        this.attachShadow({ mode: 'open' });
        this.collapsed = false;
        this.user = null;
        this.role = 0;
        this.menuItems = [];
        // Detectar automáticamente la base de la URL
        this.basePath = this.getBasePath();
    }

    // Detectar la ruta base automáticamente
    getBasePath() {
        const path = window.location.pathname;
        
        // Si estamos en /admin/pages/xxx.php -> base es ../../
        if (path.includes('/admin/pages/')) {
            return '../../';
        }
        // Si estamos en /admin/xxx.php -> base es ../
        if (path.includes('/admin/')) {
            return '../';
        }
        // Si estamos en /coordinator/pages/xxx.php -> base es ../../
        if (path.includes('/coordinator/pages/')) {
            return '../../';
        }
        // Si estamos en /coordinator/xxx.php -> base es ../
        if (path.includes('/coordinator/')) {
            return '../';
        }
        // Si estamos en /teacher/pages/xxx.php -> base es ../../
        if (path.includes('/teacher/pages/')) {
            return '../../';
        }
        // Si estamos en /teacher/xxx.php -> base es ../
        if (path.includes('/teacher/')) {
            return '../';
        }
        // Si estamos en /pages/xxx.php -> base es ../
        if (path.includes('/pages/')) {
            return '../';
        }
        // Si estamos en la raíz -> base es vacío
        return '';
    }

    connectedCallback() {
        const savedState = localStorage.getItem('sidebarCollapsed');
        if (savedState !== null) {
            this.collapsed = savedState === 'true';
        }
        this.loadUserData();
        this.render();
        this.setupEventListeners();
        this.loadMenuItems();
        
        window.addEventListener('storage', (e) => {
            if (e.key === 'userData') {
                this.loadUserData();
                this.updateUserInfo();
                this.loadMenuItems();
            }
        });
    }

    loadUserData() {
        try {
            const userData = sessionStorage.getItem('userData');
            if (userData) {
                this.user = JSON.parse(userData);
                this.role = this.user.id_rol || 0;
            } else {
                this.fetchUserData();
            }
        } catch (e) {
            console.error('Error al cargar datos del usuario:', e);
            this.user = null;
            this.role = 0;
        }
    }

    fetchUserData() {
        // Usar ruta absoluta para get_user.php
        const baseUrl = this.getBasePath();
        $.ajax({
            url: baseUrl + 'ajax/get_user.php',
            type: 'POST',
            dataType: 'json',
            success: (user) => {
                if (user && user.id_usuario) {
                    this.user = user;
                    this.role = user.id_rol || 0;
                    sessionStorage.setItem('userData', JSON.stringify(user));
                    this.updateUserInfo();
                    this.loadMenuItems();
                }
            },
            error: () => {
                this.user = null;
                this.role = 0;
            }
        });
    }

    getMenuItems() {
        const basePath = this.getBasePath();
        
        const menus = {
            1: [ // Administrador
                { icon: 'fa-home', label: 'Dashboard', path: basePath + 'admin/dashboard.php' },
                { icon: 'fa-users', label: 'Gestión de Usuarios', path: basePath + 'admin/pages/gestion_usuarios.php' },
                { icon: 'fa-book-open', label: 'Asignar Materias', path: basePath + 'admin/pages/asignar_materias.php' },
                { icon: 'fa-database', label: 'Gestión BD', path: basePath + 'admin/pages/gestion_bd.php' },
                { icon: 'fa-chart-bar', label: 'Estadísticas', path: basePath + 'admin/pages/estadisticas.php' },
                { icon: 'fa-file-alt', label: 'Boletas', path: basePath + 'admin/pages/boletas.php' },
                { icon: 'fa-user-graduate', label: 'Gestión Alumnos', path: basePath + 'admin/pages/gestion_alumnos.php' }
            ],
            2: [ // Coordinador
                { icon: 'fa-home', label: 'Dashboard', path: basePath + 'coordinator/dashboard.php' },
                { icon: 'fa-book-open', label: 'Asignar Materias', path: basePath + 'coordinator/pages/asignar_materias.php' },
                { icon: 'fa-user-graduate', label: 'Gestión Alumnos', path: basePath + 'coordinator/pages/gestion_alumnos.php' },
                { icon: 'fa-file-alt', label: 'Boletas', path: basePath + 'coordinator/pages/boletas.php' },
                { icon: 'fa-users', label: 'Maestros', path: basePath + 'coordinator/pages/gestion_maestros.php' }
            ],
            3: [ // Maestro
                { icon: 'fa-book-open', label: 'Mis Materias', path: basePath + 'teacher/pages/mis_materias.php' },
                { icon: 'fa-file-alt', label: 'Boletas', path: basePath + 'teacher/pages/boletas.php' }
            ]
        };

        return menus[this.role] || [];
    }

    loadMenuItems() {
        this.menuItems = this.getMenuItems();
        this.updateMenu();
    }

    render() {
        this.shadowRoot.innerHTML = `
            <style>
                @import url('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css');
                
                :host {
                    display: block;
                    height: 100vh;
                    position: fixed;
                    left: 0;
                    top: 0;
                    z-index: 1000;
                }

                .sidebar {
                    width: 280px;
                    height: 100%;
                    background: #ffffff;
                    color: #4a5568;
                    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                    display: flex;
                    flex-direction: column;
                    overflow: hidden;
                    box-shadow: 2px 0 20px rgba(0, 0, 0, 0.06);
                    font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', sans-serif;
                    border-right: 1px solid #eef2f6;
                }

                .sidebar.collapsed {
                    width: 90px;
                }

                .sidebar.collapsed .user-info,
                .sidebar.collapsed .menu-item span,
                .sidebar.collapsed .logout-btn span,
                .sidebar.collapsed .role-badge,
                .sidebar.collapsed .brand-text,
                .sidebar.collapsed .menu-label,
                .sidebar.collapsed .menu-divider,
                .sidebar.collapsed .item-arrow {
                    display: none;
                }

                .sidebar.collapsed .menu-item a {
                    justify-content: center;
                    padding: 12px 0;
                }

                .sidebar.collapsed .avatar-circle {
                    width: 40px;
                    height: 40px;
                    font-size: 14px;
                }

                .sidebar.collapsed .menu-item i {
                    font-size: 18px;
                    margin-right: 0;
                }

                .sidebar.collapsed .sidebar-header {
                    padding: 16px 16px;
                }

                .sidebar.collapsed .user-profile {
                    padding: 12px 16px;
                    justify-content: center;
                }

                .sidebar.collapsed .brand-logo {
                    width: 40px;
                    height: 40px;
                }

                .sidebar.collapsed .btn-toggle {
                    margin-left: auto;
                }

                .sidebar.collapsed .profile-card {
                    justify-content: center;
                    padding: 10px 0;
                }

                .sidebar.collapsed .avatar-container {
                    margin: 0 auto;
                }

                /* Header / Branding */
                .sidebar-header {
                    padding: 24px 20px 16px 20px;
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    position: relative;
                }

                .brand-container {
                    display: flex;
                    align-items: center;
                    gap: 12px;
                }

                .brand-logo {
                    width: 50px;
                    height: 42px;
                    border: 2px solid #2563eb;
                    border-radius: 10px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    background: #2563eb;
                    color: #ffffff;
                    font-size: 20px;
                    font-weight: bold;
                    flex-shrink: 0;
                    overflow: hidden;
                    padding: 2px;
                }

                .brand-logo img {
                    width: 100%;
                    height: 100%;
                    object-fit: contain;
                }

                .brand-text {
                    display: flex;
                    flex-direction: column;
                }

                .brand-title {
                    font-size: 20px;
                    font-weight: 700;
                    letter-spacing: -0.5px;
                    line-height: 1;
                }

                .brand-title .sio {
                    color: #1a202c;
                }

                .brand-title .unam {
                    color: #2563eb;
                }

                .brand-subtitle {
                    font-size: 9px;
                    font-weight: 600;
                    color: #718096;
                    letter-spacing: 1px;
                    margin-top: 4px;
                    text-transform: uppercase;
                }

                .btn-toggle {
                    background: transparent;
                    border: 1px solid #e2e8f0;
                    color: #a0aec0;
                    font-size: 14px;
                    cursor: pointer;
                    padding: 6px 10px;
                    border-radius: 6px;
                    transition: all 0.2s ease;
                    flex-shrink: 0;
                }

                .btn-toggle:hover {
                    background: #f7fafc;
                    color: #2563eb;
                    border-color: #2563eb;
                }

                /* Header line divider */
                .header-divider {
                    height: 1px;
                    background: #eef2f6;
                    margin: 0 20px 16px 20px;
                }

                /* Perfil de Usuario - Sin interactividad */
                .user-profile {
                    padding: 0 16px;
                    margin-bottom: 20px;
                }

                .profile-card {
                    display: flex;
                    align-items: center;
                    gap: 12px;
                    padding: 10px 12px;
                    border-radius: 10px;
                    background: #f7fafc;
                    border: 1px solid #eef2f6;
                    cursor: default;
                }

                .avatar-container {
                    position: relative;
                    flex-shrink: 0;
                }

                .avatar-circle {
                    width: 46px;
                    height: 46px;
                    border-radius: 10px;
                    background: #2563eb;
                    border: 2px solid #3b82f6;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-size: 16px;
                    font-weight: 700;
                    color: #ffffff;
                    overflow: hidden;
                }

                .avatar-circle img {
                    width: 100%;
                    height: 100%;
                    object-fit: cover;
                }

                .badge-star {
                    position: absolute;
                    bottom: -2px;
                    right: -2px;
                    width: 18px;
                    height: 18px;
                    background: #fbbf24;
                    border-radius: 50%;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    color: #ffffff;
                    font-size: 9px;
                    border: 2px solid #ffffff;
                }

                .user-info {
                    flex: 1;
                    overflow: hidden;
                }

                .user-info strong {
                    display: block;
                    font-size: 14px;
                    font-weight: 600;
                    color: #1a202c;
                    white-space: nowrap;
                    overflow: hidden;
                    text-overflow: ellipsis;
                    text-transform: uppercase;
                }

                .role-badge {
                    display: block;
                    font-size: 12px;
                    color: #2563eb;
                    margin-top: 2px;
                    white-space: nowrap;
                    overflow: hidden;
                    text-overflow: ellipsis;
                }

                /* Navegación */
                .sidebar-nav {
                    flex: 1;
                    padding: 0;
                    overflow-y: auto;
                }

                .sidebar-nav::-webkit-scrollbar {
                    width: 4px;
                }

                .sidebar-nav::-webkit-scrollbar-thumb {
                    background: #e2e8f0;
                    border-radius: 4px;
                }

                .sidebar-nav::-webkit-scrollbar-track {
                    background: transparent;
                }

                .menu-list {
                    list-style: none;
                    padding: 0;
                    margin: 0;
                }

                .menu-label {
                    padding: 16px 20px 8px 20px;
                    font-size: 11px;
                    font-weight: 600;
                    letter-spacing: 0.8px;
                    color: #a0aec0;
                    text-transform: uppercase;
                }

                .menu-divider {
                    height: 1px;
                    margin: 8px 20px;
                    background: #eef2f6;
                }

                .menu-item {
                    margin: 1px 0;
                }

                .menu-item a {
                    display: flex;
                    align-items: center;
                    padding: 10px 20px;
                    color: #4a5568;
                    text-decoration: none;
                    transition: all 0.2s ease;
                    position: relative;
                    font-size: 14px;
                    font-weight: 500;
                }

                .menu-item a i.menu-icon {
                    width: 24px;
                    font-size: 16px;
                    margin-right: 12px;
                    text-align: center;
                    color: #a0aec0;
                    transition: all 0.2s ease;
                }

                .menu-item a span {
                    flex: 1;
                }

                .menu-item a .item-arrow {
                    font-size: 11px;
                    color: #cbd5e0;
                    transition: transform 0.2s ease;
                }

                .menu-item:hover a {
                    color: #1a202c;
                    background: #f7fafc;
                }

                .menu-item:hover a i.menu-icon {
                    color: #2563eb;
                }

                .menu-item:hover a .item-arrow {
                    color: #2563eb;
                }

                /* Estado Activo */
                .menu-item.active a {
                    background: #eff6ff;
                    color: #1a202c;
                    font-weight: 600;
                    border-left: 4px solid #2563eb;
                    padding-left: 16px;
                    border-radius: 0 8px 8px 0;
                    margin-right: 12px;
                }

                .menu-item.active a i.menu-icon {
                    color: #2563eb;
                }

                .menu-item.active a .item-arrow {
                    display: none;
                }

                /* Footer / Logout */
                .sidebar-footer {
                    padding: 16px 20px 24px 20px;
                    border-top: 1px solid #eef2f6;
                }

                .logout-btn {
                    display: flex;
                    align-items: center;
                    gap: 12px;
                    padding: 10px 12px;
                    color: #4a5568;
                    text-decoration: none;
                    border: none;
                    background: transparent;
                    width: 100%;
                    font-size: 14px;
                    font-weight: 500;
                    cursor: pointer;
                    transition: all 0.2s;
                    border-radius: 8px;
                }

                .logout-btn:hover {
                    background: #fef2f2;
                    color: #dc2626;
                }

                .logout-btn:hover i {
                    color: #dc2626;
                }

                .logout-btn i {
                    font-size: 16px;
                    width: 24px;
                    text-align: center;
                    color: #a0aec0;
                    transition: all 0.2s ease;
                }

                /* Responsive */
                @media (max-width: 768px) {
                    .sidebar {
                        width: 90px;
                    }

                    .sidebar .user-info,
                    .sidebar .menu-item span,
                    .sidebar .logout-btn span,
                    .sidebar .role-badge,
                    .sidebar .brand-text,
                    .sidebar .menu-label,
                    .sidebar .menu-divider,
                    .sidebar .item-arrow {
                        display: none;
                    }

                    .sidebar .menu-item a {
                        justify-content: center;
                        padding: 12px 0;
                    }

                    .sidebar .avatar-circle {
                        width: 40px;
                        height: 40px;
                        font-size: 14px;
                        border-radius: 8px;
                    }

                    .sidebar .menu-item i {
                        margin-right: 0;
                    }

                    .sidebar .sidebar-header {
                        padding: 16px 16px;
                        justify-content: center;
                    }

                    .sidebar .brand-logo {
                        width: 40px;
                        height: 40px;
                    }

                    .sidebar .badge-star {
                        width: 14px;
                        height: 14px;
                        font-size: 7px;
                    }

                    .sidebar .profile-card {
                        justify-content: center;
                        padding: 10px 0;
                    }

                    .sidebar .avatar-container {
                        margin: 0 auto;
                    }
                }
            </style>

            <div class="sidebar ${this.collapsed ? 'collapsed' : ''}" id="sidebar">
                <div class="sidebar-header">
                    <div class="brand-container">
                        <div class="brand-logo">
                            <img src="${this.getBasePath()}assets/img/logo.png" alt="Logo" id="logoImg">
                        </div>
                        <div class="brand-text">
                            <span class="brand-title">
                                <span class="sio">SIO</span><span class="unam">UNAM</span>
                            </span>
                            
                        </div>
                    </div>
                    <button class="btn-toggle" id="toggleBtn" title="Toggle sidebar">
                        <i class="fas ${this.collapsed ? 'fa-chevron-right' : 'fa-chevron-left'}"></i>
                    </button>
                </div>

                <div class="header-divider"></div>

                <div class="user-profile">
                    <div class="profile-card">
                        <div class="avatar-container">
                            <div class="avatar-circle" id="avatarContainer">
                                <img src="${this.getBasePath()}assets/img/logo50.jpeg" alt="Avatar" id="userAvatar">
                            </div>
                            <div class="badge-star">
                                <i class="fas fa-star"></i>
                            </div>
                        </div>
                        <div class="user-info">
                            <strong id="userName">DANA MENDEZ</strong>
                            <span class="role-badge" id="userRole">Administrador</span>
                        </div>
                    </div>
                </div>

                <nav class="sidebar-nav">
                    <ul class="menu-list" id="menuList">
                        <!-- Items dynamically populated -->
                    </ul>
                </nav>

                <div class="sidebar-footer">
                    <button class="logout-btn" id="logoutBtn">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Cerrar sesión</span>
                    </button>
                </div>
            </div>
        `;
    }

    updateMenu() {
        const menuList = this.shadowRoot.getElementById('menuList');
        if (!menuList) return;

        const currentPath = window.location.pathname;

        const categoryMap = {
            'Gestión de Usuarios': 'GESTIÓN ACADÉMICA',
            'Asignar Materias': 'GESTIÓN ACADÉMICA',
            'Gestión BD': 'GESTIÓN ACADÉMICA',
            'Estadísticas': 'GESTIÓN ACADÉMICA',
            'Boletas': 'GESTIÓN ACADÉMICA',
            'Gestión Alumnos': 'GESTIÓN ACADÉMICA',
            'Maestros': 'GESTIÓN ACADÉMICA',
            'Mis Materias': 'GESTIÓN ACADÉMICA'
        };

        let html = '';
        let currentCategory = '';

        this.menuItems.forEach((item) => {
            const isActive = currentPath.includes(item.path.replace('.php', '').split('/').pop()) || 
                           (item.path.includes('dashboard') && currentPath.includes('dashboard'));

            if (item.label === 'Dashboard') {
                html += `
                    <li class="menu-item ${isActive ? 'active' : ''}">
                        <a href="${item.path}">
                            <i class="fas ${item.icon} menu-icon"></i>
                            <span>${item.label}</span>
                            <i class="fas fa-chevron-right item-arrow"></i>
                        </a>
                    </li>
                `;
            } else {
                const category = categoryMap[item.label] || 'GESTIÓN ACADÉMICA';

                if (category !== currentCategory) {
                    if (currentCategory !== '') {
                        html += `<li class="menu-divider"></li>`;
                    }
                    html += `<li class="menu-label">${category}</li>`;
                    currentCategory = category;
                }

                html += `
                    <li class="menu-item ${isActive ? 'active' : ''}">
                        <a href="${item.path}">
                            <i class="fas ${item.icon} menu-icon"></i>
                            <span>${item.label}</span>
                            <i class="fas fa-chevron-right item-arrow"></i>
                        </a>
                    </li>
                `;
            }
        });

        menuList.innerHTML = html;
    }

    setupEventListeners() {
        const toggleBtn = this.shadowRoot.getElementById('toggleBtn');
        if (toggleBtn) {
            toggleBtn.addEventListener('click', () => {
                this.toggleSidebar();
            });
        }

        const logoutBtn = this.shadowRoot.getElementById('logoutBtn');
        if (logoutBtn) {
            logoutBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.logout();
            });
        }

        this.updateUserInfo();

        // Manejar error de carga de imagen del logo
        const logoImg = this.shadowRoot.getElementById('logoImg');
        if (logoImg) {
            logoImg.addEventListener('error', () => {
                logoImg.style.display = 'none';
                const parent = logoImg.parentElement;
                parent.textContent = 'U';
                parent.style.display = 'flex';
                parent.style.alignItems = 'center';
                parent.style.justifyContent = 'center';
                parent.style.fontSize = '20px';
                parent.style.fontWeight = 'bold';
                parent.style.color = '#ffffff';
            });
        }

        // Manejar error de carga de la imagen del avatar
        const userAvatar = this.shadowRoot.getElementById('userAvatar');
        if (userAvatar) {
            userAvatar.addEventListener('error', () => {
                const container = this.shadowRoot.getElementById('avatarContainer');
                const initials = this.user ? 
                    `${this.user.nombre?.charAt(0) || ''}${this.user.apellido_paterno?.charAt(0) || ''}`.toUpperCase() : 
                    'U';
                container.innerHTML = `<span style="font-size:16px;font-weight:700;color:#ffffff;">${initials || 'U'}</span>`;
                container.style.background = '#2563eb';
                container.style.display = 'flex';
                container.style.alignItems = 'center';
                container.style.justifyContent = 'center';
            });
        }
    }

    updateUserInfo() {
        if (!this.user) {
            const nameElem = this.shadowRoot.getElementById('userName');
            const userRole = this.shadowRoot.getElementById('userRole');
            const avatarContainer = this.shadowRoot.getElementById('avatarContainer');

            if (nameElem) nameElem.textContent = 'USUARIO';
            if (userRole) userRole.textContent = 'Rol';
            if (avatarContainer) {
                avatarContainer.innerHTML = `<span style="font-size:16px;font-weight:700;color:#ffffff;">U</span>`;
                avatarContainer.style.background = '#2563eb';
                avatarContainer.style.display = 'flex';
                avatarContainer.style.alignItems = 'center';
                avatarContainer.style.justifyContent = 'center';
            }
            return;
        }

        // Actualizar nombre en mayúsculas
        const userName = this.shadowRoot.getElementById('userName');
        if (userName) {
            const fullName = `${this.user.nombre || ''} ${this.user.apellido_paterno || ''}`.trim();
            userName.textContent = fullName.toUpperCase() || 'USUARIO';
        }
        
        // Actualizar rol
        const userRole = this.shadowRoot.getElementById('userRole');
        const roles = ['', 'Administrador', 'Coordinador', 'Maestro'];
        if (userRole) userRole.textContent = roles[this.user.id_rol] || 'Rol';

        // Actualizar avatar - imagen fija logo50.png
        const avatarContainer = this.shadowRoot.getElementById('avatarContainer');
        if (avatarContainer) {
            // Si el avatar tiene la imagen, la mantenemos, pero aseguramos que la imagen esté
            const existingImg = avatarContainer.querySelector('img');
            if (!existingImg) {
                avatarContainer.innerHTML = `<img src="${this.getBasePath()}assets/img/logo50.jpeg" alt="Avatar" id="userAvatar">`;
                // Manejar error de carga de la nueva imagen
                const newImg = avatarContainer.querySelector('img');
                if (newImg) {
                    newImg.addEventListener('error', () => {
                        const initials = `${this.user.nombre?.charAt(0) || ''}${this.user.apellido_paterno?.charAt(0) || ''}`.toUpperCase();
                        avatarContainer.innerHTML = `<span style="font-size:16px;font-weight:700;color:#ffffff;">${initials || 'U'}</span>`;
                        avatarContainer.style.background = '#2563eb';
                        avatarContainer.style.display = 'flex';
                        avatarContainer.style.alignItems = 'center';
                        avatarContainer.style.justifyContent = 'center';
                    });
                }
            } else {
                // Asegurar que la imagen tenga la ruta correcta
                existingImg.src = `${this.getBasePath()}assets/img/logo50.jpeg`;
            }
        }
    }

    toggleSidebar() {
        this.collapsed = !this.collapsed;
        const sidebar = this.shadowRoot.getElementById('sidebar');
        if (sidebar) {
            sidebar.classList.toggle('collapsed');
        }
        
        const icon = this.shadowRoot.querySelector('.btn-toggle i');
        if (icon) {
            icon.className = this.collapsed ? 'fas fa-chevron-right' : 'fas fa-chevron-left';
        }
        
        this.dispatchEvent(new CustomEvent('sidebarToggle', {
            detail: { collapsed: this.collapsed }
        }));

        localStorage.setItem('sidebarCollapsed', this.collapsed);
    }

    logout() {
        if (!confirm('¿Estás seguro de que deseas cerrar sesión?')) {
            return;
        }

        const logoutBtn = this.shadowRoot.getElementById('logoutBtn');
        if (logoutBtn) {
            logoutBtn.disabled = true;
            logoutBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Cerrando...';
        }

        const basePath = this.getBasePath();
        
        $.ajax({
            url: basePath + 'ajax/logout.php',
            type: 'POST',
            dataType: 'json',
            success: (response) => {
                sessionStorage.removeItem('userData');
                localStorage.removeItem('sidebarCollapsed');
                window.location.href = basePath + 'pages/login.php';
            },
            error: () => {
                sessionStorage.removeItem('userData');
                localStorage.removeItem('sidebarCollapsed');
                window.location.href = basePath + 'pages/login.php';
            }
        });
    }

    setUserData(userData) {
        this.user = userData;
        this.role = userData.id_rol || 0;
        sessionStorage.setItem('userData', JSON.stringify(userData));
        this.loadMenuItems();
        this.updateUserInfo();
    }

    refreshMenu() {
        this.loadMenuItems();
    }
}

customElements.define('sidebar-component', SidebarComponent);