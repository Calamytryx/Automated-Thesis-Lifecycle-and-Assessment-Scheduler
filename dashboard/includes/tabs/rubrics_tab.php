<!-- Rubrics Tab -->
<div class="tab-pane fade" id="rubrics" role="tabpanel" aria-labelledby="rubrics-tab">
    <div class="d-flex justify-content-between align-items-center mb-3 my-3">
        <button class="btn btn-primary btn-sm add-btn feature-btn" data-table="rubrics">Add Rubric</button>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered table-hover table-sm db-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <!-- Data loaded via AJAX -->
            </tbody>
        </table>
    </div>
    <nav aria-label="Page navigation">
        <ul class="pagination justify-content-center">
            <!-- Pagination loaded via AJAX -->
        </ul>
    </nav>

    <script src="js/rubricBuilder.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const loadRubrics = (page = 1) => {
                fetch(`includes/tabs/get_table.php?table=rubrics&page=${page}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.error) {
                            console.error(data.error);
                            return;
                        }
                        const tbody = document.querySelector('#rubrics .db-table tbody');
                        tbody.innerHTML = '';
                        data.data.forEach(rubric => {
                            tbody.innerHTML += `
                                <tr>
                                    <td>${rubric.name}</td>
                                    <td>${rubric.description}</td>
                                    <td class="text-center align-middle">
                                        <button class="btn btn-primary btn-sm edit-btn" data-table="rubrics" data-id="${rubric.id}">Edit</button>
                                        <button class="btn btn-danger btn-sm delete-btn" data-table="rubrics" data-id="${rubric.id}">Delete</button>
                                    </td>
                                </tr>
                            `;
                        });
                    });
            };

            // Call loadRubrics on page load
            loadRubrics();

            // Handle Pagination Clicks
            document.querySelector('#rubrics .pagination').addEventListener('click', function (e) {
                e.preventDefault();
                if (e.target.tagName === 'A') {
                    const page = parseInt(e.target.getAttribute('data-page'));
                    if (!isNaN(page)) {
                        loadRubrics(page);
                    }
                }
            });
        });
    </script>
</div>
