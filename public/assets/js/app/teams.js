// assets/js/app/teams.js
// Handles team-wise skill attendance, e.g. "Afroz: 3 Mason, 2 Helper, 4 Carpenter"

(function () {

    // Use your global ajaxGet/ajaxPost if available, else fallback to jQuery
    function _ajaxGet(url, data, cb) {
        if (typeof ajaxGet === 'function') return ajaxGet(url, data, cb);
        $.getJSON(url, data).done(cb).fail(function () {
            cb && cb({ success: false, message: 'AJAX GET failed' });
        });
    }

    function _ajaxPost(url, data, cb) {
        if (typeof ajaxPost === 'function') return ajaxPost(url, data, cb);
        $.post(url, data, cb, 'json').fail(function () {
            cb && cb({ success: false, message: 'AJAX POST failed' });
        });
    }

    function escapeHtml(s) {
        return String(s || '').replace(/[&<>"'`=\/]/g, function (ch) {
            return {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;',
                '/': '&#x2F;',
                '`': '&#96;',
                '=': '&#61;'
            }[ch];
        });
    }

    function showToast(message, timeout = 1600) {
        $('#small-toast').remove();
        const $t = $(`<div id="small-toast" class="toast position-fixed bottom-0 end-0 m-3 p-2 bg-success text-white rounded shadow">${message}</div>`);
        $('body').append($t);
        setTimeout(() => $t.fadeOut(300, () => $t.remove()), timeout);
    }

    // -------------------------
    // Load list of teams into dropdown
    // -------------------------
    function loadTeams() {
        const $sel = $('#teamSelect');
        if ($sel.length === 0) return;

        $sel.prop('disabled', true).html('<option>Loading…</option>');

        _ajaxGet('api/team/list.php', {}, function (res) {
            $sel.prop('disabled', false).empty();
            if (!res || !res.success || !res.data || res.data.length === 0) {
                $sel.append('<option value="">No teams found</option>');
                return;
            }
            $sel.append('<option value="">-- Select team --</option>');
            res.data.forEach(t => {
                $sel.append(`<option value="${t.id}">${escapeHtml(t.name)}</option>`);
            });
        });
    }

    // -------------------------
    // Load team skill summary (from team_skill_attendance)
    // -------------------------
    function loadTeamSummary() {
        const $container = $('#teamSkillAttendance');
        const teamId = $('#teamSelect').val();
        const date = $('#teamDate').val();
        const shift = $('#teamShift').val();
         const projectId = $('#projectSelect').val();

        if ($container.length === 0) return;

        if (!teamId) {
            $container.html('<div class="text-muted p-3">Select a team to view attendance.</div>');
            return;
        }

        $container.html('<div class="text-muted p-3">Loading team skills…</div>');

        _ajaxGet('api/team/summary.php', {
            team_id: teamId,
            date: date,
            shift: shift
        }, function (res) {
            if (!res || !res.success) {
                $container.html('<div class="text-danger p-3">Failed to load team summary.</div>');
                return;
            }

            const rows = res.data || [];
            if (rows.length === 0) {
                $container.html('<div class="text-muted p-3">No labour skill templates added for this team yet.</div>');
                return;
            }

            let html = `
                <div class="table-responsive">
                  <table class="table table-sm table-dark align-middle mb-0">
                    <thead>
                      <tr>
                        <th>Skill / Category</th>
                        <th class="text-center">Working Today</th>
                      </tr>
                    </thead>
                    <tbody>
            `;

            rows.forEach(r => {
                const working = r.working_count || 0;
                html += `
                    <tr data-skill="${escapeHtml(r.skill)}">
                      <td>${escapeHtml(r.skill)}</td>
                      <td class="text-center">
                        <input type="number"
                               class="form-control form-control-sm teamWorkingInput"
                               min="0"
                               value="${working}"
                               data-skill="${escapeHtml(r.skill)}">
                      </td>
                    </tr>
                `;
            });

            html += `
                    </tbody>
                  </table>
                </div>
            `;

            $container.html(html);
        });
    }
    let selectedProjectId = null;
$(document).on('change', '#projectSelect', function () {
    selectedProjectId = $(this).val();
    console.log('📌 Project changed:', selectedProjectId);
});

    // -------------------------
    // Save team attendance (writes to team_skill_attendance)
    // -------------------------
function saveTeamAttendance() {
    const teamId    = $('#teamSelect').val();
    const date      = $('#teamDate').val();
    const shift     = $('#teamShift').val();
    const projectId = selectedProjectId || $('#projectSelect').val();
    const $inputs   = $('#teamSkillAttendance .teamWorkingInput');

    if (!projectId) {
        alert('Please select a project.');
        return;
    }

    if (!teamId) {
        alert('Please select a team.');
        return;
    }

    if (!date) {
        alert('Please select date.');
        return;
    }

    const items = [];
    $inputs.each(function () {
        const skill = $(this).data('skill');
        let val = parseInt($(this).val(), 10);
        if (isNaN(val) || val < 0) val = 0;

        items.push({
            skill: skill,
            working_count: val
        });
    });

    if (items.length === 0) {
        alert('No skills found to save.');
        return;
    }

    const payload = {
        project_id: projectId,
        team_id: teamId,
        date: date,
        shift: shift,
        items: items
    };

    console.log('📦 Saving team attendance payload:', payload);

    $('#saveTeamAttendance').prop('disabled', true).text('Saving…');

    _ajaxPost('api/team/mark_attendance.php', payload, function (res) {
        $('#saveTeamAttendance').prop('disabled', false).text('Save Team Attendance');

        if (!res || !res.success) {
            alert(res && res.message ? res.message : 'Failed to save team attendance.');
            return;
        }

        showToast('Team attendance saved');
        loadTeamSummary();
    });
}

// function loadPaymentTeams() {
//     const $sel = $('#paymentTeamSelect');
//     $sel.html('<option>Loading…</option>');

//     $.getJSON('api/team/list.php', function (res) {
//         $sel.empty().append('<option value="">Select Team</option>');
//         if (res.success && res.data.length) {
//             res.data.forEach(t => {
//                 $sel.append(`<option value="${t.id}">${t.name}</option>`);
//             });
//         }
//     });
// }


    // =========================
    // Add Team functionality
    // =========================

    // When Add Team modal opens, you could reset the form if desired
    $('#addTeamModal').on('show.bs.modal', function () {
        const $form = $('#addTeamForm');
        if ($form.length) {
            $form[0].reset();
        }
    });

    // Handle Add Team submit
    $(document).on('submit', '#addTeamForm', function (e) {
        e.preventDefault();
    const $form = $(this);
    const nameInput = $form.find('input[name="name"]');
    const descriptionInput = $form.find('input[name="description"]');
    const name = (nameInput.val() || '').trim();
    const description = (descriptionInput.val() || '').trim();

    const payload = {
        name: name,
        description: description
 // simple username pattern like employee
    };
    console.log(payload);

        const $btn = $form.find('button[type="submit"]').prop('disabled', true);

        _ajaxPost('api/team/add.php', payload, function (res) {
            $btn.prop('disabled', false);

            if (!res || !res.success) {
                alert(res && res.message ? res.message : 'Failed to add team');
                return;
            }

            // Close modal
            const modalEl = document.getElementById('addTeamModal');
            if (modalEl) {
                const m = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                m.hide();
            }

            // Reset form
            if ($form[0]) $form[0].reset();

            showToast('Team added successfully');
        });
    });
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
        if (!res.success) {
            $('#pendingAmountInput').val('₹ 0.00');
            return;
        }

        $('#pendingAmountInput').val(
            '₹ ' + res.pending.toLocaleString('en-IN', {
                minimumFractionDigits: 2
            })
        );
    });
}
$(document).on('change', '#paymentProjectSelect, #paymentTeamSelect', function () {
    loadPendingAmount();
});

    // -------------------------
    // Wire events
    // -------------------------
    $(document).ready(function () {
        // Only init if Teams tab exists
        if ($('#teams').length === 0) return;

        // Default date = today
        $('#teamDate').val(new Date().toISOString().slice(0, 10));

        loadTeams();

        $('#teamSelect, #teamDate, #teamShift').on('change', function () {
            loadTeamSummary();
        });

        $('#reloadTeamSummary').on('click', function () {
            loadTeamSummary();
        });

        $('#saveTeamAttendance').on('click', function () {
            saveTeamAttendance();
        });

        // When Teams tab is shown, refresh
        $('button[data-bs-target="#teams"]').on('shown.bs.tab', function () {
            if (!$('#teamSelect').val()) loadTeams();
            loadTeamSummary();
        });
    });


})();
