
<!-- Modal -->
<div class="modal fade" id="addProjectModal" tabindex="-1" aria-labelledby="addProjectLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content bg-dark text-light glass">
      <div class="modal-header border-0">
        <h5 class="modal-title" id="addProjectLabel">Add New Project</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="addProjectForm">
          <div class="mb-3">
            <input type="text" class="form-control form-control-sm" name="name" placeholder="Project Name" required>
          </div>
          <div class="mb-3">
            <input type="text" class="form-control form-control-sm" name="location" placeholder="Location">
          </div>
          <div class="mb-3">
            <input type="date" class="form-control form-control-sm" name="start_date" required>
          </div>
          <div class="d-flex justify-content-end">
            <button type="submit" class="btn btn-warning btn-sm">Save Project</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>