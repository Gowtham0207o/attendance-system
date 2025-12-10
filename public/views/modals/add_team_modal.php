<div class="modal fade" id="addTeamModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content bg-dark text-light glass">
      <div class="modal-header border-0">
        <h5 class="modal-title">Add Team</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="addTeamForm">
          <div class="mb-2">
            <input type="text" name="name" class="form-control form-control-sm" placeholder="Team Name (e.g. Afroz)" required>
          </div>
          <div class="mb-2">
            <input type="text" name="description" class="form-control form-control-sm" placeholder="Description (optional)">
          </div>
          <div class="d-flex justify-content-end">
            <button class="btn btn-sm btn-warning" type="submit">Save Team</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
