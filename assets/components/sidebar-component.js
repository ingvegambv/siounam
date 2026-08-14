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
        // Si estamos en /pages/xxx.php -> base es ../
        if (path.includes('/pages/')) {
            return '../';
        }
        // Si estamos en la raíz -> base es vacío
        return '';
    }

    connectedCallback() {
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
            1: [
                { icon: 'fa-th-large', label: 'Dashboard', path: basePath + 'admin/dashboard.php' },
                { icon: 'fa-users-cog', label: 'Gestión de Usuarios', path: basePath + 'admin/pages/gestion_usuarios.php' },
                { icon: 'fa-chalkboard-teacher', label: 'Asignar Materias', path: basePath + 'admin/pages/asignar_materias.php' },
                { icon: 'fa-database', label: 'Gestión BD', path: basePath + 'admin/pages/gestion_bd.php' },
                { icon: 'fa-chart-bar', label: 'Estadísticas', path: basePath + 'admin/pages/estadisticas.php' },
                { icon: 'fa-file-alt', label: 'Boletas', path: basePath + 'admin/pages/boletas.php' },
                { icon: 'fa-user-graduate', label: 'Gestión Alumnos', path: basePath + 'admin/pages/gestion_alumnos.php' }
            ],
            2: [
                { icon: 'fa-th-large', label: 'Dashboard', path: basePath + 'coordinator/dashboard.php' },
                { icon: 'fa-chalkboard-teacher', label: 'Asignar Materias', path: basePath + 'coordinator/pages/asignar_materias.php' },
                { icon: 'fa-user-graduate', label: 'Gestión Alumnos', path: basePath + 'coordinator/pages/gestion_alumnos.php' },
                { icon: 'fa-file-alt', label: 'Boletas', path: basePath + 'coordinator/pages/boletas.php' },
                { icon: 'fa-users', label: 'Maestros', path: basePath + 'coordinator/pages/gestion_maestros.php' },
            ],
            3: [
                { icon: 'fa-th-large', label: 'Dashboard', path: basePath + 'teacher/dashboard.php' },
                { icon: 'fa-users', label: 'Mis Alumnos', path: basePath + 'teacher/pages/mis_alumnos.php' },
                { icon: 'fa-book', label: 'Calificaciones', path: basePath + 'teacher/pages/calificaciones.php' }
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
                    background: linear-gradient(180deg, #1a1a2e 0%, #2c3e50 100%);
                    color: #fff;
                    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                    display: flex;
                    flex-direction: column;
                    overflow: hidden;
                    box-shadow: 2px 0 20px rgba(0,0,0,0.3);
                }

                .sidebar.collapsed {
                    width: 70px;
                }

                .sidebar.collapsed .user-info,
                .sidebar.collapsed .menu-item span,
                .sidebar.collapsed .logout-btn span,
                .sidebar.collapsed .role-badge,
                .sidebar.collapsed .sidebar-title {
                    display: none;
                }

                .sidebar.collapsed .menu-item {
                    justify-content: center;
                    padding: 12px 0;
                }

                .sidebar.collapsed .avatar-circle {
                    width: 40px;
                    height: 40px;
                    font-size: 14px;
                }

                .sidebar.collapsed .menu-item i {
                    font-size: 20px;
                    margin-right: 0;
                }

                .sidebar-header {
                    padding: 20px 20px 10px 20px;
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    border-bottom: 1px solid rgba(255,255,255,0.08);
                }

                .sidebar-title {
                    font-size: 18px;
                    font-weight: 700;
                    color: #fff;
                    letter-spacing: 1px;
                }

                .sidebar-title span {
                    color: #667eea;
                }

                .btn-toggle {
                    background: rgba(255,255,255,0.05);
                    border: 1px solid rgba(255,255,255,0.1);
                    color: #fff;
                    font-size: 16px;
                    cursor: pointer;
                    padding: 8px 12px;
                    border-radius: 8px;
                    transition: all 0.3s ease;
                }

                .btn-toggle:hover {
                    background: rgba(255,255,255,0.1);
                    color: #667eea;
                }

                .user-profile {
                    padding: 20px;
                    border-bottom: 1px solid rgba(255,255,255,0.08);
                }

                .avatar-container {
                    display: flex;
                    align-items: center;
                    gap: 15px;
                }

                .avatar-circle {
                    width: 55px;
                    height: 55px;
                    border-radius: 50%;
                    background: linear-gradient(135deg, #667eea, #764ba2);
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-size: 22px;
                    font-weight: 600;
                    color: #fff;
                    flex-shrink: 0;
                    position: relative;
                }

                .avatar-circle .online-status {
                    position: absolute;
                    bottom: 2px;
                    right: 2px;
                    width: 14px;
                    height: 14px;
                    background: #2ecc71;
                    border-radius: 50%;
                    border: 2px solid #1a1a2e;
                }

                .user-info {
                    text-align: left;
                    flex: 1;
                    overflow: hidden;
                }

                .user-info strong {
                    display: block;
                    font-size: 14px;
                    color: #fff;
                    white-space: nowrap;
                    overflow: hidden;
                    text-overflow: ellipsis;
                }

                .user-info small {
                    display: block;
                    font-size: 11px;
                    color: #a8b5c4;
                    white-space: nowrap;
                    overflow: hidden;
                    text-overflow: ellipsis;
                }

                .role-badge {
                    display: inline-block;
                    background: rgba(102, 126, 234, 0.2);
                    color: #667eea;
                    padding: 2px 10px;
                    border-radius: 12px;
                    font-size: 10px;
                    margin-top: 3px;
                    border: 1px solid rgba(102, 126, 234, 0.2);
                }

                .sidebar-nav {
                    flex: 1;
                    padding: 15px 0;
                    overflow-y: auto;
                }

                .sidebar-nav::-webkit-scrollbar {
                    width: 4px;
                }

                .sidebar-nav::-webkit-scrollbar-track {
                    background: rgba(255,255,255,0.05);
                }

                .sidebar-nav::-webkit-scrollbar-thumb {
                    background: #667eea;
                    border-radius: 2px;
                }

                .menu-list {
                    list-style: none;
                    padding: 0;
                    margin: 0;
                }

                .menu-item {
                    margin: 2px 12px;
                    border-radius: 10px;
                    transition: all 0.3s ease;
                }

                .menu-item a {
                    display: flex;
                    align-items: center;
                    gap: 15px;
                    padding: 11px 15px;
                    color: rgba(255,255,255,0.6);
                    text-decoration: none;
                    border-radius: 10px;
                    transition: all 0.3s ease;
                    position: relative;
                }

                .menu-item a i {
                    width: 20px;
                    font-size: 16px;
                    transition: all 0.3s ease;
                    text-align: center;
                }

                .menu-item a span {
                    font-size: 13px;
                    font-weight: 500;
                }

                .menu-item:hover a {
                    background: rgba(255,255,255,0.05);
                    color: #fff;
                }

                .menu-item.active a {
                    background: linear-gradient(135deg, rgba(102, 126, 234, 0.2), rgba(118, 75, 162, 0.2));
                    color: #fff;
                    border: 1px solid rgba(102, 126, 234, 0.2);
                }

                .menu-item.active a::before {
                    content: '';
                    position: absolute;
                    left: -2px;
                    top: 50%;
                    transform: translateY(-50%);
                    width: 3px;
                    height: 20px;
                    background: linear-gradient(180deg, #667eea, #764ba2);
                    border-radius: 0 3px 3px 0;
                }

                .sidebar-footer {
                    padding: 15px 20px;
                    border-top: 1px solid rgba(255,255,255,0.08);
                }

                .logout-btn {
                    display: flex;
                    align-items: center;
                    gap: 15px;
                    padding: 10px 15px;
                    color: rgba(255, 107, 107, 0.7);
                    text-decoration: none;
                    border-radius: 10px;
                    transition: all 0.3s ease;
                    cursor: pointer;
                    border: 1px solid transparent;
                    background: transparent;
                    width: 100%;
                    font-size: 14px;
                }

                .logout-btn:hover {
                    background: rgba(255, 107, 107, 0.1);
                    color: #ff6b6b;
                    border-color: rgba(255, 107, 107, 0.2);
                }

                .logout-btn i {
                    width: 20px;
                    font-size: 16px;
                    text-align: center;
                }

                /* Responsive */
                @media (max-width: 768px) {
                    .sidebar {
                        width: 70px;
                    }
                    
                    .sidebar .user-info,
                    .sidebar .menu-item span,
                    .sidebar .logout-btn span,
                    .sidebar .role-badge,
                    .sidebar .sidebar-title {
                        display: none;
                    }
                    
                    .sidebar .menu-item {
                        justify-content: center;
                    }
                    
                    .sidebar .avatar-circle {
                        width: 40px;
                        height: 40px;
                        font-size: 14px;
                    }

                    .sidebar .menu-item a {
                        justify-content: center;
                        padding: 11px 0;
                    }

                    .sidebar .menu-item a i {
                        margin-right: 0;
                    }
                }

                @keyframes fadeIn {
                    from {
                        opacity: 0;
                        transform: translateX(-20px);
                    }
                    to {
                        opacity: 1;
                        transform: translateX(0);
                    }
                }

                .sidebar {
                    animation: fadeIn 0.4s ease;
                }
            </style>

            <div class="sidebar ${this.collapsed ? 'collapsed' : ''}" id="sidebar">
                <div class="sidebar-header">
                    <div class="sidebar-title">SIO<span>UNAM</span></div>
                    <button class="btn-toggle" id="toggleBtn" title="Toggle sidebar">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                </div>

                <div class="user-profile">
                    <div class="avatar-container">
                        <div class="avatar-circle">
                            <span id="userInitials">U</span>
                            <span class="online-status"></span>
                        </div>
                        <div class="user-info">
                            <strong id="userName">Usuario</strong>
                            <small id="userUsername">@usuario</small>
                            <span class="role-badge" id="userRole">Rol</span>
                        </div>
                    </div>
                </div>

                <nav class="sidebar-nav">
                    <ul class="menu-list" id="menuList">
                        <!-- Items will be rendered here -->
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
        
        let html = '';
        this.menuItems.forEach(item => {
            // Verificar si la ruta actual coincide con la del menú
            const isActive = currentPath.includes(item.path.replace('.php', '').split('/').pop()) || 
                           (item.path.includes('dashboard') && currentPath.includes('dashboard'));
            
            html += `
                <li class="menu-item ${isActive ? 'active' : ''}">
                    <a href="${item.path}" data-tooltip="${item.label}">
                        <i class="fas ${item.icon}"></i>
                        <span>${item.label}</span>
                    </a>
                </li>
            `;
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
    }

    updateUserInfo() {
        if (!this.user) {
            const nameElem = this.shadowRoot.getElementById('userInitials');
            const userName = this.shadowRoot.getElementById('userName');
            const userUsername = this.shadowRoot.getElementById('userUsername');
            const userRole = this.shadowRoot.getElementById('userRole');

            if (nameElem) nameElem.textContent = 'U';
            if (userName) userName.textContent = 'Usuario';
            if (userUsername) userUsername.textContent = '@usuario';
            if (userRole) userRole.textContent = 'Rol';
            return;
        }

        const initials = this.user.nombre && this.user.apellido_paterno 
            ? `${this.user.nombre.charAt(0)}${this.user.apellido_paterno.charAt(0)}`.toUpperCase()
            : 'U';
        
        const nameElem = this.shadowRoot.getElementById('userInitials');
        const userName = this.shadowRoot.getElementById('userName');
        const userUsername = this.shadowRoot.getElementById('userUsername');
        const userRole = this.shadowRoot.getElementById('userRole');

        if (nameElem) nameElem.textContent = initials;
        if (userName) userName.textContent = `${this.user.nombre || ''} ${this.user.apellido_paterno || ''}`.trim() || 'Usuario';
        if (userUsername) userUsername.textContent = `@${this.user.usuario || 'usuario'}`;
        
        const roles = ['', 'Administrador', 'Coordinador', 'Maestro'];
        if (userRole) userRole.textContent = roles[this.user.id_rol] || 'Rol';
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

    connectedCallback() {
        const savedState = localStorage.getItem('sidebarCollapsed');
        if (savedState !== null) {
            this.collapsed = savedState === 'true';
        }
        this.loadUserData();
        this.render();
        this.setupEventListeners();
        this.loadMenuItems();
    }
}

customElements.define('sidebar-component', SidebarComponent);