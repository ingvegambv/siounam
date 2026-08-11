// assets/components/header-component.js
class HeaderComponent extends HTMLElement {
    constructor() {
        super();
        this.attachShadow({ mode: 'open' });
    }

    connectedCallback() {
        this.render();
        this.updateDateTime();
        setInterval(() => this.updateDateTime(), 60000);
        this.setupEventListeners();
    }

    render() {
        const title = this.getAttribute('title') || 'Dashboard';
        
        this.shadowRoot.innerHTML = `
            <style>
                @import url('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css');
                
                :host {
                    display: block;
                }

                .header {
                    background: #fff;
                    padding: 18px 25px;
                    box-shadow: 0 2px 15px rgba(0,0,0,0.05);
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    border-radius: 15px;
                    margin-bottom: 25px;
                    border: 1px solid rgba(0,0,0,0.03);
                }

                .header-left {
                    display: flex;
                    align-items: center;
                    gap: 15px;
                }

                .header-left h1 {
                    font-size: 22px;
                    font-weight: 700;
                    color: #1a1a2e;
                    margin: 0;
                    letter-spacing: -0.5px;
                }

                .header-left h1 i {
                    color: #667eea;
                    margin-right: 10px;
                }

                .header-left .breadcrumb {
                    font-size: 13px;
                    color: #95a5a6;
                    padding-left: 15px;
                    border-left: 2px solid #e9ecef;
                }

                .header-left .breadcrumb span {
                    color: #667eea;
                }

                .header-right {
                    display: flex;
                    align-items: center;
                    gap: 15px;
                }

                .header-right .datetime {
                    font-size: 13px;
                    color: #7f8c8d;
                    background: #f8f9fa;
                    padding: 8px 15px;
                    border-radius: 10px;
                    border: 1px solid #e9ecef;
                }

                .header-right .btn-refresh {
                    background: transparent;
                    border: 1px solid #e9ecef;
                    border-radius: 10px;
                    padding: 8px 12px;
                    color: #2c3e50;
                    cursor: pointer;
                    transition: all 0.3s ease;
                    font-size: 14px;
                }

                .header-right .btn-refresh:hover {
                    background: #f8f9fa;
                    border-color: #667eea;
                    color: #667eea;
                    transform: rotate(180deg);
                }

                .header-right .btn-refresh i {
                    transition: transform 0.3s ease;
                }

                .header-right .btn-refresh:hover i {
                    transform: rotate(180deg);
                }

                .slot-actions {
                    display: flex;
                    gap: 10px;
                    align-items: center;
                }

                @media (max-width: 768px) {
                    .header {
                        flex-direction: column;
                        align-items: stretch;
                        gap: 15px;
                        padding: 15px;
                    }

                    .header-left {
                        flex-wrap: wrap;
                    }

                    .header-left .breadcrumb {
                        padding-left: 0;
                        border-left: none;
                        width: 100%;
                    }

                    .header-right {
                        flex-wrap: wrap;
                        justify-content: space-between;
                    }

                    .slot-actions {
                        width: 100%;
                        justify-content: flex-end;
                    }
                }
            </style>

            <div class="header">
                <div class="header-left">
                    <h1>
                        <i class="fas fa-${this.getAttribute('icon') || 'th-large'}"></i>
                        ${title}
                    </h1>
                    <div class="breadcrumb">
                        <span id="breadcrumb">Inicio</span>
                    </div>
                </div>
                <div class="header-right">
                    <span class="datetime" id="datetime"></span>
                    <button class="btn-refresh" id="refreshBtn" title="Actualizar página">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                    <div class="slot-actions">
                        <slot name="actions"></slot>
                    </div>
                </div>
            </div>
        `;
    }

    setupEventListeners() {
        const refreshBtn = this.shadowRoot.getElementById('refreshBtn');
        if (refreshBtn) {
            refreshBtn.addEventListener('click', () => {
                this.dispatchEvent(new CustomEvent('refresh'));
                window.location.reload();
            });
        }
    }

    updateDateTime() {
        const now = new Date();
        const options = { 
            weekday: 'long', 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        };
        const datetime = this.shadowRoot.getElementById('datetime');
        if (datetime) {
            datetime.textContent = now.toLocaleDateString('es-MX', options);
        }
    }

    setBreadcrumb(items) {
        const breadcrumb = this.shadowRoot.getElementById('breadcrumb');
        if (breadcrumb) {
            breadcrumb.innerHTML = items.map(item => `<span>${item}</span>`).join(' / ');
        }
    }

    setTitle(title) {
        const h1 = this.shadowRoot.querySelector('h1');
        if (h1) {
            h1.innerHTML = `<i class="fas fa-${this.getAttribute('icon') || 'th-large'}"></i> ${title}`;
        }
    }
}

customElements.define('header-component', HeaderComponent);