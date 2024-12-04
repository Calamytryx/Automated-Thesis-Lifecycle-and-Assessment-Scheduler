class RubricBuilder {
    constructor() {
        this.columns = ['Criteria', 'Level 1', 'Level 2', 'Level 3', 'Level 4'];
        this.rows = [];
        this.maxScore = 4;
        this.selectedCells = [];
        this.isSelecting = false;
    }

    renderBuilder() {
        return `
            <div class="rubric-builder mb-4">
                <div class="builder-controls mb-3">
                    <button type="button" class="btn btn-sm btn-primary me-2" id="addLevel">Add Level</button>
                    <button type="button" class="btn btn-sm btn-primary me-2" id="addCriterion">Add Criterion</button>
                    <button type="button" class="btn btn-sm btn-danger me-2" id="removeLevel">Remove Level</button>
                    <button type="button" class="btn btn-sm btn-danger me-2" id="removeCriterion">Remove Criterion</button>
                    <button type="button" class="btn btn-sm btn-success me-2" id="mergeCells">Merge Cells</button>
                    <button type="button" class="btn btn-sm btn-warning" id="splitCell">Split Cell</button>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered" id="rubricTable">
                        <thead>
                            <tr>
                                ${this.columns.map(col => `<th contenteditable="true">${col}</th>`).join('')}
                            </tr>
                        </thead>
                        <tbody>
                            ${this.renderEmptyRow()}
                        </tbody>
                    </table>
                </div>
            </div>
        `;
    }

    renderEmptyRow() {
        return `
            <tr>
                ${this.columns.map(col => `<td contenteditable="true"></td>`).join('')}
            </tr>
        `;
    }

    attachEventListeners() {
        document.getElementById('addLevel').addEventListener('click', () => this.addLevel());
        document.getElementById('addCriterion').addEventListener('click', () => this.addCriterion());
        document.getElementById('removeLevel').addEventListener('click', () => this.removeLevel());
        document.getElementById('removeCriterion').addEventListener('click', () => this.removeCriterion());
        document.getElementById('mergeCells').addEventListener('click', () => this.mergeCells());
        document.getElementById('splitCell').addEventListener('click', () => this.splitCell());

        const table = document.getElementById('rubricTable');
        table.addEventListener('mousedown', (e) => this.handleMouseDown(e));
        table.addEventListener('mouseover', (e) => this.handleMouseOver(e));
        document.addEventListener('mouseup', () => this.handleMouseUp());
    }

    handleMouseDown(e) {
        if (e.target.tagName === 'TD') {
            this.isSelecting = true;
            this.selectedCells = [e.target];
            this.updateSelectedCellsStyle();
        }
    }

    handleMouseOver(e) {
        if (this.isSelecting && e.target.tagName === 'TD') {
            if (!this.selectedCells.includes(e.target)) {
                this.selectedCells.push(e.target);
                this.updateSelectedCellsStyle();
            }
        }
    }

    handleMouseUp() {
        this.isSelecting = false;
    }

    updateSelectedCellsStyle() {
        const table = document.getElementById('rubricTable');
        table.querySelectorAll('td').forEach(cell => {
            cell.classList.remove('selected-cell');
        });
        this.selectedCells.forEach(cell => {
            cell.classList.add('selected-cell');
        });
    }

    mergeCells() {
        if (this.selectedCells.length < 2) return;
        
        const firstCell = this.selectedCells[0];
        const rowSpan = this.selectedCells.length;
        const content = this.selectedCells.map(cell => cell.textContent).join(' ');
        
        firstCell.textContent = content;
        firstCell.rowSpan = rowSpan;
        
        this.selectedCells.slice(1).forEach(cell => {
            cell.remove();
        });
        
        this.selectedCells = [];
        this.updateSelectedCellsStyle();
    }

    splitCell() {
        if (this.selectedCells.length !== 1) return;
        
        const cell = this.selectedCells[0];
        if (cell.rowSpan <= 1) return;
        
        const content = cell.textContent;
        const row = cell.parentElement;
        const cellIndex = Array.from(row.cells).indexOf(cell);
        
        cell.rowSpan = 1;
        
        for (let i = 1; i < cell.rowSpan; i++) {
            const nextRow = row.nextElementSibling;
            const newCell = document.createElement('td');
            newCell.textContent = content;
            newCell.contentEditable = true;
            nextRow.insertCell(cellIndex);
        }
        
        this.selectedCells = [];
        this.updateSelectedCellsStyle();
    }

    addLevel() {
        const levelNum = this.columns.length;
        this.columns.push(`Level ${levelNum}`);
        this.refreshTable();
    }

    addCriterion() {
        const tbody = document.getElementById('rubricTable').querySelector('tbody');
        tbody.insertAdjacentHTML('beforeend', this.renderEmptyRow());
    }

    removeLevel() {
        if (this.columns.length > 2) {
            this.columns.pop();
            this.refreshTable();
        }
    }

    removeCriterion() {
        const tbody = document.getElementById('rubricTable').querySelector('tbody');
        if (tbody.children.length > 1) {
            tbody.removeChild(tbody.lastChild);
        }
    }

    refreshTable() {
        const table = document.getElementById('rubricTable');
        table.querySelector('thead tr').innerHTML = 
            this.columns.map(col => `<th contenteditable="true">${col}</th>`).join('');
        
        const rows = table.querySelectorAll('tbody tr');
        rows.forEach(row => {
            while (row.cells.length < this.columns.length) {
                row.insertCell().setAttribute('contenteditable', 'true');
            }
            while (row.cells.length > this.columns.length) {
                row.deleteCell(-1);
            }
        });
    }

    getStructure() {
        const table = document.getElementById('rubricTable');
        if (!table) {
            console.error('Rubric table not found');
            return JSON.stringify({ levels: [], criteria: [] });
        }

        const headers = Array.from(table.querySelectorAll('thead th')).map(th => th.textContent.trim());
        const rows = Array.from(table.querySelectorAll('tbody tr')).map(row => 
            Array.from(row.cells).map(cell => cell.textContent.trim())
        );

        const structure = {
            levels: headers.slice(1),
            criteria: rows.filter(row => row[0].trim() !== '').map(row => ({
                criterion: row[0],
                levels: row.slice(1)
            }))
        };

        console.log('Generated structure:', structure);
        return JSON.stringify(structure);
    }

    loadExistingStructure(structure) {
        if (!structure || !structure.levels || !structure.criteria) return;
        
        this.columns = ['Criteria', ...structure.levels];
        
        const table = document.getElementById('rubricTable');
        table.querySelector('thead tr').innerHTML = 
            this.columns.map(col => `<th contenteditable="true">${col}</th>`).join('');
        
        const tbody = table.querySelector('tbody');
        tbody.innerHTML = '';
        
        structure.criteria.forEach(criterion => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td contenteditable="true">${criterion.criterion}</td>
                ${criterion.levels.map(level => `<td contenteditable="true">${level}</td>`).join('')}
            `;
            tbody.appendChild(row);
        });
    }
}