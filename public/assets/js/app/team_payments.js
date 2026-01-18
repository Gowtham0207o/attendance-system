// ===============================
// Team Payments JS
// ===============================

(function () {

    // ---------------------------------
    // Load pending amount (already used)
    // ---------------------------------
    function loadPendingAmount() {
        const projectId = $('#paymentProjectSelect').val();
        const teamId    = $('#paymentTeamSelect').val();

        if (!projectId || !teamId) {
            $('#pendingAmountInput').val('₹ 0.00');
            return;
        }

        $.getJSON('api/team/pending_amount.php', {
            project_id: projectId,
            team_id: teamId
        }, function (res) {
            if (!res || !res.success) {
                $('#pendingAmountInput').val('₹ 0.00');
                return;
            }

            $('#pendingAmountInput').val(
                '₹ ' + Number(res.pending).toLocaleString('en-IN', {
                    minimumFractionDigits: 2
                })
            );
        });
    }

    // Reload pending amount when project/team changes
    $(document).on('change', '#paymentProjectSelect, #paymentTeamSelect', function () {
        loadPendingAmount();
    });

    // ---------------------------------
    // Save Team Payment
    // ---------------------------------
    $(document).on('submit', '#addPaymentForm', function (e) {
        e.preventDefault();

        const $form = $(this);

        const payload = {
            project_id:   $form.find('[name="project_id"]').val(),
            team_id:      $form.find('[name="team_id"]').val(),
            payment_date: $form.find('[name="payment_date"]').val(),
            amount_paid:  $form.find('[name="amount_paid"]').val(),
            payment_mode: $form.find('[name="payment_mode"]').val(),
            reference_no: $form.find('[name="reference_no"]').val(),
            remarks:      $form.find('[name="remarks"]').val()
        };

        // Basic validation
        if (!payload.project_id || !payload.team_id) {
            alert('Please select project and team');
            return;
        }
        if (!payload.payment_date) {
            alert('Payment date is required');
            return;
        }
        if (!payload.amount_paid || payload.amount_paid <= 0) {
            alert('Enter valid amount');
            return;
        }

        const $btn = $form.find('button[type="submit"]')
                          .prop('disabled', true)
                          .text('Saving…');

        $.ajax({
            url: 'api/team/save_payment.php',
            type: 'POST',
            data: JSON.stringify(payload),
            contentType: 'application/json',
            dataType: 'json',
            success: function (res) {
                $btn.prop('disabled', false).text('Save Payment');

                if (!res || !res.success) {
                    alert(res?.message || 'Failed to save payment');
                    return;
                }

                // Close modal
                const modalEl = document.getElementById('addPaymentModal');
                if (modalEl) {
                    const m = bootstrap.Modal.getInstance(modalEl) ||
                              new bootstrap.Modal(modalEl);
                    m.hide();
                }

                // Reset form
                $form[0].reset();

                // Refresh pending amount
                loadPendingAmount();

                // Toast
                showToast('Payment recorded successfully');
            },
            error: function (xhr) {
                $btn.prop('disabled', false).text('Save Payment');
                alert('Server error while saving payment');
                console.error(xhr.responseText);
            }
        });
    });

})();
