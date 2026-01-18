<!-- Team Export Modal -->
<div class="modal fade" id="exportTeamModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content bg-dark text-light glass">
      <div class="modal-header border-0">
        <h5 class="modal-title">Export Team Attendance (Excel)</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="exportTeamForm">
          <div class="mb-3">
            <label class="form-label small text-muted">From</label>
            <input type="date" id="teamExportFrom" name="from" class="form-control form-control-sm" required>
          </div>
          <div class="mb-3">
            <label class="form-label small text-muted">To</label>
            <input type="date" id="teamExportTo" name="to" class="form-control form-control-sm" required>
          </div>

          <div class="mb-3">
            <label class="form-label small text-muted">Shift</label>
            <select id="teamExportShift" name="shift" class="form-select form-select-sm">
              <option value="all">All</option>
              <option value="day">Day</option>
              <option value="night">Night</option>
            </select>
          </div>

          <div class="small text-muted mb-2">
            Export will include per-date team skill counts, totals per skill, and estimated salary/pay for the range (where labour salary is available).
          </div>

          <div class="d-flex justify-content-end">
            <button type="submit" class="btn btn-sm btn-warning">Download Excel</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
