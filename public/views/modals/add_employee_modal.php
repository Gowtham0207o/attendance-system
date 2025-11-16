<div class="modal fade" id="addEmpModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content bg-dark text-light glass">
      <div class="modal-header border-0">
        <h5 class="modal-title">Add Employee</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="addEmpForm">
          <div class="mb-2">
            <input class="form-control form-control-sm" name="name" placeholder="Name" required>
          </div>
          <div class="mb-2">
            <input class="form-control form-control-sm" name="role" placeholder="Role / Designation" required>
          </div>
          <div class="mb-2">
            <input class="form-control form-control-sm" name="salary" type="number" placeholder="Monthly Salary (INR)" required>
          </div>
          <div class="d-flex justify-content-end">
            <button class="btn btn-sm btn-primary" type="submit">Save</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
