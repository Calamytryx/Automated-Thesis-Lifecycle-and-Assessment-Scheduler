class RubricBuilder {
    constructor() {
        this.columns = ['Criteria', 'Level 1', 'Level 2', 'Level 3', 'Level 4'];
        this.selectedCells = [];
        this.isSelecting = false;
        this.lastSelectedCell = null;
    }

    renderBuilder() {
        return `
            <div class="rubric-builder mb-4">
                <div class="builder-controls mb-3">
                    <button type="button" class="btn btn-sm btn-primary me-2" id="addLevel">Add Level</button>
                    <button type="button" class="btn btn-sm btn-primary me-2" id="addCriterion">Add Criterion</button>
                    <button type="button" class="btn btn-sm btn-danger me-2" id="removeLevel">Remove Level</button>
                    <button type="button" class="btn btn-sm btn-danger me-2" id="removeCriterion">Remove Criterion</button>
                    <button type="button" class="btn btn-sm btn-secondary me-2" id="mergeCells">Merge Cells</button>
                    <button type="button" class="btn btn-sm btn-secondary" id="splitCell">Split Cell</button>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered" id="rubricTable">
                        <thead>
                            <tr>
                                ${this.columns.map(col => `<th contenteditable="true">${col}</th>`).join('')}
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                ${this.columns.map(() => `<td contenteditable="true"></td>`).join('')}
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        `;
    }

    renderEmptyRow() {
        return `<tr>${this.columns.map(() => `<td contenteditable="true"></td>`).join('')}</tr>`;
    }

    attachEventListeners() {
        document.getElementById('addLevel').addEventListener('click', () => this.addLevel());
        document.getElementById('addCriterion').addEventListener('click', () => this.addCriterion());
        document.getElementById('removeLevel').addEventListener('click', () => this.removeLevel());
        document.getElementById('removeCriterion').addEventListener('click', () => this.removeCriterion());
        document.getElementById('mergeCells').addEventListener('click', () => this.mergeCells());
        document.getElementById('splitCell').addEventListener('click', () => this.splitCell());

        const table = document.getElementById('rubricTable');
        table.addEventListener('click', (e) => this.handleClick(e));
        table.addEventListener('mousedown', (e) => this.handleMouseDown(e));
        table.addEventListener('mouseover', (e) => this.handleMouseOver(e));
        document.addEventListener('mouseup', () => this.handleMouseUp());
    }

    handleClick(e) {
        if (e.target.tagName !== 'TD') return;

        if (e.ctrlKey || e.metaKey) {
            // Control/Command click for multiple selection
            const index = this.selectedCells.indexOf(e.target);
            if (index === -1) {
                this.selectedCells.push(e.target);
            } else {
                this.selectedCells.splice(index, 1);
            }
        } else if (e.shiftKey && this.lastSelectedCell) {
            // Shift click for range selection
            const cells = Array.from(document.querySelectorAll('#rubricTable td'));
            const start = cells.indexOf(this.lastSelectedCell);
            const end = cells.indexOf(e.target);
            const range = cells.slice(
                Math.min(start, end),
                Math.max(start, end) + 1
            );
            this.selectedCells = range;
        } else {
            // Normal click
            this.selectedCells = [e.target];
            this.lastSelectedCell = e.target;
        }

        this.updateSelectedCellsStyle();
    }

    handleMouseDown(e) {
        if (e.target.tagName === 'TD' && !e.ctrlKey && !e.shiftKey) {
            this.isSelecting = true;
            this.selectedCells = [e.target];
            this.lastSelectedCell = e.target;
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
        // Reset all cell styles
        const allCells = document.querySelectorAll('#rubricTable td');
        allCells.forEach(cell => {
            cell.style.backgroundColor = '';
            cell.style.color = '';
        });

        // Highlight selected cells
        this.selectedCells.forEach(cell => {
            cell.style.backgroundColor = '#007bff';
            cell.style.color = 'white';
        });
    }

    mergeCells() {
        if (this.selectedCells.length < 2) {
            alert('Please select at least 2 cells to merge');
            return;
        }

        // Check if cells are adjacent
        const firstCell = this.selectedCells[0];
        const rowSpan = firstCell.rowSpan || 1;
        const colSpan = firstCell.colSpan || 1;

        // Combine content
        const combinedContent = this.selectedCells.map(cell => cell.textContent).join(' ');
        firstCell.textContent = combinedContent;
        firstCell.rowSpan = rowSpan;
        firstCell.colSpan = this.selectedCells.length;

        // Remove other cells
        this.selectedCells.slice(1).forEach(cell => cell.remove());
        
        // Clear selection
        this.selectedCells = [];
        this.updateSelectedCellsStyle();
    }

    splitCell() {
        if (this.selectedCells.length !== 1) {
            alert('Please select one merged cell to split');
            return;
        }

        const cell = this.selectedCells[0];
        if (!cell.colSpan || cell.colSpan === 1) {
            alert('Selected cell is not merged');
            return;
        }

        const row = cell.parentElement;
        const content = cell.textContent;
        const colSpan = cell.colSpan;

        // Reset the first cell
        cell.colSpan = 1;
        cell.textContent = content;

        // Add new cells
        for (let i = 1; i < colSpan; i++) {
            const newCell = document.createElement('td');
            newCell.contentEditable = true;
            row.insertBefore(newCell, cell.nextSibling);
        }

        // Clear selection
        this.selectedCells = [];
        this.updateSelectedCellsStyle();
    }

    addLevel() {
        const table = document.getElementById('rubricTable');
        const headerRow = table.querySelector('thead tr');
        const newLevelNum = headerRow.children.length;
        
        // Add header
        const newHeader = document.createElement('th');
        newHeader.contentEditable = true;
        newHeader.textContent = `Level ${newLevelNum}`;
        headerRow.appendChild(newHeader);
        
        // Add column to each row
        const rows = table.querySelectorAll('tbody tr');
        rows.forEach(row => {
            const newCell = document.createElement('td');
            newCell.contentEditable = true;
            row.appendChild(newCell);
        });
        
        this.columns.push(`Level ${newLevelNum}`);
    }

    addCriterion() {
        const tbody = document.getElementById('rubricTable').querySelector('tbody');
        tbody.insertAdjacentHTML('beforeend', this.renderEmptyRow());
    }

    removeLevel() {
        const table = document.getElementById('rubricTable');
        if (table.rows[0].cells.length > 2) {
            Array.from(table.rows).forEach(row => row.deleteCell(-1));
            this.columns.pop();
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