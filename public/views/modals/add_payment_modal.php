<!-- Add Team Payment Modal -->
<div class="modal fade" id="addPaymentModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content bg-dark text-light glass">
      
      <div class="modal-header border-0">
        <h5 class="modal-title">Add Team Payment</h5>
        <button type="button"
                class="btn-close btn-close-white"
                data-bs-dismiss="modal"></button>
      </div>
     <?php
                              // Fetch projects (formerly sites)
                              $stmtp = db()->prepare("SELECT id, name FROM teams ORDER BY name ASC");
                              $stmtp->execute();
                              $teams = $stmtp->fetchAll(PDO::FETCH_ASSOC);
                              ?>
      <div class="modal-body">
        <form id="addPaymentForm">

          <!-- Project -->
          <div class="mb-2">
            <select class="form-select form-select-sm"
                    name="project_id"
                    id="paymentProjectSelect"
                    required>
            <?php foreach($projects as $project): ?>
                                    <option value="<?= $project['id'] ?>"><?= htmlspecialchars($project['name'])?></option>
                                    <?php endforeach; ?>
              <!-- populate dynamically -->
            </select>
          </div>

          <!-- Team -->
          <div class="mb-2">
            <select class="form-select form-select-sm"
                    name="team_id"
                    id="paymentTeamSelect"
                    required>
                                     <?php foreach($teams as $team): ?>
                                    <option value="<?= $team['id'] ?>"><?= htmlspecialchars($team['name'])?></option>
                                    <?php endforeach; ?>
              <!-- populate dynamically -->
            </select>
          </div>

          <!-- Payment Date -->
          <div class="mb-2">
            <input type="date"
                   class="form-control form-control-sm"
                   name="payment_date"
                   required>
          </div>
      <div class="mb-2">
            <label class="small text-muted">Pending Amount (₹)</label>
            <input type="text"
                   class="form-control form-control-sm bg-dark text-warning"
                   id="pendingAmountInput"
                   value="₹ 0.00"
                   disabled>
          </div>
          <!-- Amount -->
          <div class="mb-2">
            <input type="number"
                   class="form-control form-control-sm"
                   name="amount_paid"
                   placeholder="Amount Paid (₹)"
                   required>
          </div>

          <!-- Payment Mode -->
          <div class="mb-2">
            <select class="form-select form-select-sm"
                    name="payment_mode">
              <option value="cash">Cash</option>
              <option value="upi">UPI</option>
              <option value="bank">Bank</option>
              <option value="cheque">Cheque</option>
            </select>
          </div>

          <!-- Reference -->
          <div class="mb-2">
            <input type="text"
                   class="form-control form-control-sm"
                   name="reference_no"
                   placeholder="Reference No (optional)">
          </div>

          <!-- Remarks -->
          <div class="mb-2">
            <textarea class="form-control form-control-sm"
                      name="remarks"
                      placeholder="Remarks (optional)"
                      rows="2"></textarea>
          </div>

          <!-- Action -->
          <div class="d-flex justify-content-end">
            <button class="btn btn-sm btn-primary" type="submit">
              Save Payment
            </button>
          </div>

        </form>
      </div>

    </div>
  </div>
</div>
