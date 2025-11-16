<div class="modal fade" id="addLabourModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content bg-dark text-light glass">
      
      <!-- Header -->
      <div class="modal-header border-0">
        <h5 class="modal-title">Add Labour</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>

      <!-- Body -->
      <div class="modal-body">
        <form id="addLabourForm">

          <!-- Labour Name -->
          <div class="mb-2">
            <input 
              class="form-control form-control-sm" 
              name="name" 
              placeholder="Labour Name" 
              required>
          </div>

          <!-- Skill / Role -->
          <div class="mb-2">
            <input 
              class="form-control form-control-sm" 
              name="skill" 
              placeholder="Skill / Role (e.g., Mason, Carpenter)" 
              required>
          </div>

          <!-- Phone Number -->
          <div class="mb-2">
            <input 
              class="form-control form-control-sm" 
              name="phone" 
              type="tel" 
              placeholder="Phone Number" 
              pattern="[0-9]{10}" 
              maxlength="10" 
              required>
          </div>

          <!-- Address / Remarks -->
          <div class="mb-2">
            <textarea 
              class="form-control form-control-sm" 
              name="address" 
              placeholder="Address / Remarks" 
              rows="2" 
              required></textarea>
          </div>

          <!-- Save Button -->
          <div class="d-flex justify-content-end">
            <button class="btn btn-sm btn-primary" type="submit">
              Save Labour
            </button>
          </div>

        </form>
      </div>

    </div>
  </div>
</div>
