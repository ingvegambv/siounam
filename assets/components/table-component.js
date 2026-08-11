  // assets/components/table-component.js
class TableComponent extends HTMLElement {
    constructor() {
        super();
        this.attachShadow({ mode: 'open' });
        this.data = [];
        this.columns = [];
        this.actions = [];
        this.pageSize = 10;
        this.currentPage = 1;
        this.searchTerm = '';
        this.filteredData = [];
    }

    connectedCallback() {
        this.render();
        this.setupEventListeners();
    }

    render() {
        this.shadowRoot.innerHTML = `
            <style>
                @import url('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css');
                
                :host {
                    display: block;
                }

                .table-container {
                    background: #fff;
                    border-radius: 15px;
                    box-shadow: 0 2px 15px rgba(0,0,0,0.05);
                    overflow: hidden;
                    border: 1px solid rgba(0,0,0,0.03);
                }

                .table-toolbar {
                    padding: 15px 20px;
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    border-bottom: 1px solid #edf2f7;
                    flex-wrap: wrap;
                    gap: 10px;
                    background: #fafbfc;
                }

                .table-toolbar .search-box {
                    display: flex;
                    align-items: center;
                    gap: 10px;
                }

                .table-toolbar .search-box input {
                    padding: 8px 15px;
                    border: 1px solid #d1d5db;
                    border-radius: 10px;
                    font-size: 14px;
                    outline: none;
                    transition: all 0.3s ease;
                    min-width: 250px;
                }

                .table-toolbar .search-box input:focus {
                    border-color: #667eea;
                    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
                }

                .table-toolbar .search-box input::placeholder {
                    color: #b0b8c4;
                }

                .table-toolbar .btn {
                    padding: 8px 18px;
                    border: none;
                    border-radius: 10px;
                    cursor: pointer;
                    font-size: 14px;
                    font-weight: 500;
                    transition: all 0.3s ease;
                    display: inline-flex;
                    align-items: center;
                    gap: 8px;
                }

                .btn-primary {
                    background: linear-gradient(135deg, #667eea, #764ba2);
                    color: #fff;
                }

                .btn-primary:hover {
                    transform: translateY(-2px);
                    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
                }

                .btn-sm {
                    padding: 5px 10px;
                    font-size: 12px;
                    border-radius: 8px;
                }

                .btn-warning {
                    background: #f39c12;
                    color: #fff;
                }

                .btn-warning:hover {
                    background: #e67e22;
                    transform: scale(1.05);
                }

                .btn-danger {
                    background: #e74c3c;
                    color: #fff;
                }

                .btn-danger:hover {
                    background: #c0392b;
                    transform: scale(1.05);
                }

                .btn-success {
                    background: #2ecc71;
                    color: #fff;
                }

                .btn-success:hover {
                    background: #27ae60;
                    transform: scale(1.05);
                }

                .btn-info {
                    background: #3498db;
                    color: #fff;
                }

                .btn-info:hover {
                    background: #2980b9;
                    transform: scale(1.05);
                }

                .table-responsive {
                    overflow-x: auto;
                }

                table {
                    width: 100%;
                    border-collapse: collapse;
                    font-size: 14px;
                }

                thead {
                    background: #f8f9fa;
                }

                thead th {
                    padding: 12px 18px;
                    text-align: left;
                    font-weight: 600;
                    color: #495057;
                    white-space: nowrap;
                    font-size: 13px;
                    text-transform: uppercase;
                    letter-spacing: 0.5px;
                }

                tbody tr {
                    border-bottom: 1px solid #edf2f7;
                    transition: background 0.2s ease;
                }

                tbody tr:last-child {
                    border-bottom: none;
                }

                tbody tr:hover {
                    background: #f8f9fa;
                }

                tbody td {
                    padding: 12px 18px;
                    color: #2c3e50;
                    font-size: 13px;
                }

                .badge {
                    display: inline-block;
                    padding: 4px 12px;
                    border-radius: 20px;
                    font-size: 11px;
                    font-weight: 600;
                }

                .badge-success {
                    background: #d4edda;
                    color: #155724;
                }

                .badge-danger {
                    background: #f8d7da;
                    color: #721c24;
                }

                .badge-warning {
                    background: #fff3cd;
                    color: #856404;
                }

                .badge-info {
                    background: #d1ecf1;
                    color: #0c5460;
                }

                .badge-primary {
                    background: #cce5ff;
                    color: #004085;
                }

                .action-buttons {
                    display: flex;
                    gap: 5px;
                    flex-wrap: wrap;
                }

                .action-buttons button {
                    padding: 4px 8px;
                    border: none;
                    border-radius: 6px;
                    cursor: pointer;
                    font-size: 12px;
                    transition: all 0.2s ease;
                    background: #f1f3f5;
                    color: #495057;
                }

                .action-buttons button:hover {
                    transform: scale(1.1);
                }

                .action-buttons .btn-warning {
                    background: #fff3cd;
                    color: #856404;
                }

                .action-buttons .btn-danger {
                    background: #f8d7da;
                    color: #721c24;
                }

                .action-buttons .btn-info {
                    background: #d1ecf1;
                    color: #0c5460;
                }

                .pagination {
                    padding: 15px 20px;
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    border-top: 1px solid #edf2f7;
                    background: #fafbfc;
                }

                .pagination .info {
                    color: #7f8c8d;
                    font-size: 13px;
                }

                .pagination .controls {
                    display: flex;
                    gap: 5px;
                }

                .pagination .controls button {
                    padding: 6px 14px;
                    border: 1px solid #d1d5db;
                    background: #fff;
                    border-radius: 8px;
                    cursor: pointer;
                    transition: all 0.3s ease;
                    font-size: 13px;
                    color: #495057;
                }

                .pagination .controls button:hover:not(:disabled) {
                    background: #f8f9fa;
                    border-color: #667eea;
                    color: #667eea;
                }

                .pagination .controls button.active {
                    background: linear-gradient(135deg, #667eea, #764ba2);
                    color: #fff;
                    border-color: #667eea;
                }

                .pagination .controls button:disabled {
                    opacity: 0.4;
                    cursor: not-allowed;
                }

                .empty-state {
                    padding: 60px 40px;
                    text-align: center;
                    color: #7f8c8d;
                }

                .empty-state i {
                    font-size: 48px;
                    margin-bottom: 15px;
                    color: #d1d5db;
                }

                .empty-state p {
                    font-size: 16px;
                    margin: 0;
                }

                /* Row selection */
                .table-row-selected {
                    background: rgba(102, 126, 234, 0.05) !important;
                }

                @media (max-width: 768px) {
                    .table-toolbar {
                        flex-direction: column;
                        align-items: stretch;
                    }

                    .table-toolbar .search-box {
                        flex-direction: column;
                        width: 100%;
                    }

                    .table-toolbar .search-box input {
                        min-width: auto;
                        width: 100%;
                    }

                    .pagination {
                        flex-direction: column;
                        gap: 10px;
                        text-align: center;
                    }
                }
            </style>

            <div class="table-container">
                <div class="table-toolbar">
                    <div class="search-box">
                        <input type="text" id="searchInput" placeholder="Buscar...">
                        <button class="btn btn-primary" id="searchBtn">
                            <i class="fas fa-search"></i> Buscar
                        </button>
                    </div>
                    <div>
                        <slot name="toolbar-actions"></slot>
                    </div>
                </div>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr id="tableHeader"></tr>
                        </thead>
                        <tbody id="tableBody"></tbody>
                    </table>
                </div>
                <div class="pagination">
                    <span class="info" id="paginationInfo">Mostrando 0 - 0 de 0</span>
                    <div class="controls" id="paginationControls"></div>
                </div>
            </div>
        `;
    }

    setupEventListeners() {
        const searchInput = this.shadowRoot.getElementById('searchInput');
        const searchBtn = this.shadowRoot.getElementById('searchBtn');

        if (searchInput) {
            searchInput.addEventListener('keyup', (e) => {
                if (e.key === 'Enter') {
                    this.searchTerm = searchInput.value;
                    this.currentPage = 1;
                    this.filterData();
                }
            });
        }

        if (searchBtn) {
            searchBtn.addEventListener('click', () => {
                this.searchTerm = searchInput.value;
                this.currentPage = 1;
                this.filterData();
            });
        }
    }

    filterData() {
        if (!this.searchTerm) {
            this.filteredData = [...this.data];
        } else {
            const term = this.searchTerm.toLowerCase();
            this.filteredData = this.data.filter(row => {
                return this.columns.some(col => {
                    const value = String(row[col.key] || '').toLowerCase();
                    return value.includes(term);
                });
            });
        }
        this.renderTable();
    }

    renderTable() {
        const startIndex = (this.currentPage - 1) * this.pageSize;
        const endIndex = Math.min(startIndex + this.pageSize, this.filteredData.length);
        const pageData = this.filteredData.slice(startIndex, endIndex);

        this.renderHeader();
        this.renderBody(pageData);
        this.renderPagination();
    }

    renderHeader() {
        const header = this.shadowRoot.getElementById('tableHeader');
        if (!header) return;

        let html = '';
        this.columns.forEach(col => {
            html += `<th>${col.label}</th>`;
        });
        if (this.actions.length > 0) {
            html += '<th style="text-align: center;">Acciones</th>';
        }
        header.innerHTML = html;
    }

    renderBody(data) {
        const body = this.shadowRoot.getElementById('tableBody');
        if (!body) return;

        if (data.length === 0) {
            const colspan = this.columns.length + (this.actions.length > 0 ? 1 : 0);
            body.innerHTML = `
                <tr>
                    <td colspan="${colspan}">
                        <div class="empty-state">
                            <i class="fas fa-inbox"></i>
                            <p>No hay datos disponibles</p>
                            ${this.searchTerm ? `<p style="font-size: 13px; margin-top: 5px;">No se encontraron resultados para "${this.searchTerm}"</p>` : ''}
                        </div>
                    </td>
                </tr>
            `;
            return;
        }

        let html = '';
        data.forEach((row, index) => {
            html += '<tr>';
            this.columns.forEach(col => {
                let value = row[col.key] !== undefined && row[col.key] !== null ? row[col.key] : '-';
                
                if (col.type === 'badge') {
                    const badgeClass = this.getBadgeClass(value, col.badgeMap);
                    value = `<span class="badge ${badgeClass}">${value}</span>`;
                }
                if (col.type === 'date') {
                    value = new Date(value).toLocaleDateString('es-MX', {
                        year: 'numeric',
                        month: 'short',
                        day: 'numeric'
                    });
                }
                if (col.type === 'datetime') {
                    value = new Date(value).toLocaleString('es-MX');
                }
                if (col.type === 'currency') {
                    value = `$${Number(value).toFixed(2)}`;
                }
                if (col.type === 'percentage') {
                    value = `${Number(value).toFixed(1)}%`;
                }
                html += `<td>${value}</td>`;
            });
            if (this.actions.length > 0) {
                html += '<td style="text-align: center;"><div class="action-buttons">';
                this.actions.forEach(action => {
                    const icon = action.icon || 'fa-edit';
                    const color = action.color || 'primary';
                    const label = action.label || action.key;
                    html += `
                        <button class="btn-${color} action-btn" 
                                data-action="${action.key}" 
                                data-id="${row.id || row.id_usuario || index}"
                                title="${label}">
                            <i class="fas ${icon}"></i>
                        </button>
                    `;
                });
                html += '</div></td>';
            }
            html += '</tr>';
        });
        body.innerHTML = html;

        // Event listeners for action buttons
        body.querySelectorAll('.action-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                const action = btn.dataset.action;
                const id = btn.dataset.id;
                const rowData = this.data.find(row => (row.id || row.id_usuario) == id);
                this.dispatchEvent(new CustomEvent('action', {
                    detail: { 
                        action, 
                        id, 
                        data: rowData || {},
                        event: e
                    }
                }));
            });
        });

        // Row click event
        body.querySelectorAll('tr').forEach(row => {
            row.addEventListener('click', function() {
                this.classList.toggle('table-row-selected');
            });
        });
    }

    renderPagination() {
        const totalPages = Math.ceil(this.filteredData.length / this.pageSize);
        const info = this.shadowRoot.getElementById('paginationInfo');
        const controls = this.shadowRoot.getElementById('paginationControls');

        if (info) {
            if (this.filteredData.length === 0) {
                info.textContent = 'Mostrando 0 - 0 de 0';
            } else {
                const start = (this.currentPage - 1) * this.pageSize + 1;
                const end = Math.min(this.currentPage * this.pageSize, this.filteredData.length);
                info.textContent = `Mostrando ${start} - ${end} de ${this.filteredData.length}`;
            }
        }

        if (controls) {
            if (totalPages <= 1) {
                controls.innerHTML = '';
                return;
            }

            let html = `
                <button onclick="this.getRootNode().host.prevPage()" ${this.currentPage === 1 ? 'disabled' : ''}>
                    <i class="fas fa-chevron-left"></i>
                </button>
            `;

            // Pagination logic with ellipsis
            const maxVisible = 5;
            let startPage = Math.max(1, this.currentPage - Math.floor(maxVisible / 2));
            let endPage = Math.min(totalPages, startPage + maxVisible - 1);
            if (endPage - startPage < maxVisible - 1) {
                startPage = Math.max(1, endPage - maxVisible + 1);
            }

            if (startPage > 1) {
                html += `<button onclick="this.getRootNode().host.goToPage(1)">1</button>`;
                if (startPage > 2) {
                    html += `<button disabled>...</button>`;
                }
            }

            for (let i = startPage; i <= endPage; i++) {
                html += `
                    <button onclick="this.getRootNode().host.goToPage(${i})" 
                            class="${i === this.currentPage ? 'active' : ''}">
                        ${i}
                    </button>
                `;
            }

            if (endPage < totalPages) {
                if (endPage < totalPages - 1) {
                    html += `<button disabled>...</button>`;
                }
                html += `<button onclick="this.getRootNode().host.goToPage(${totalPages})">${totalPages}</button>`;
            }

            html += `
                <button onclick="this.getRootNode().host.nextPage()" 
                        ${this.currentPage === totalPages ? 'disabled' : ''}>
                    <i class="fas fa-chevron-right"></i>
                </button>
            `;

            controls.innerHTML = html;
        }
    }

    getBadgeClass(value, badgeMap) {
        if (!badgeMap) return 'badge-info';
        return badgeMap[value] || 'badge-info';
    }

    setData(data) {
        this.data = data || [];
        this.filteredData = [...this.data];
        this.currentPage = 1;
        this.searchTerm = '';
        const searchInput = this.shadowRoot.getElementById('searchInput');
        if (searchInput) {
            searchInput.value = '';
        }
        this.renderTable();
    }

    setColumns(columns) {
        this.columns = columns || [];
        this.renderTable();
    }

    setActions(actions) {
        this.actions = actions || [];
        this.renderTable();
    }

    setPageSize(size) {
        this.pageSize = size || 10;
        this.currentPage = 1;
        this.renderTable();
    }

    prevPage() {
        if (this.currentPage > 1) {
            this.currentPage--;
            this.renderTable();
        }
    }

    nextPage() {
        const totalPages = Math.ceil(this.filteredData.length / this.pageSize);
        if (this.currentPage < totalPages) {
            this.currentPage++;
            this.renderTable();
        }
    }

    goToPage(page) {
        const totalPages = Math.ceil(this.filteredData.length / this.pageSize);
        if (page >= 1 && page <= totalPages) {
            this.currentPage = page;
            this.renderTable();
        }
    }

    refresh() {
        this.filterData();
    }

    getSelectedRows() {
        const rows = this.shadowRoot.querySelectorAll('.table-row-selected');
        const selectedIds = [];
        rows.forEach(row => {
            const id = row.querySelector('.action-btn')?.dataset.id;
            if (id) selectedIds.push(id);
        });
        return selectedIds;
    }
}

customElements.define('table-component', TableComponent);