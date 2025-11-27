// -------------------------
// Render labour list (with project filter)
// -------------------------
function renderLabours(filter = '') {
    const projectId = $('#projectSelect').val() || 0;
    
    ajaxGet('api/labour/list.php', { project_id: projectId, filter }, function (res) {
        const container = $('#labourList');
        container.empty();

        if (!res.success || res.data.length === 0) {
            container.html("<div class='text-muted text-center py-3'>No labours available for this project.</div>");
            return;
        }

        res.data.forEach(function (labour) {
            const item = $(`
                <div class='timeline-item glass text-light labour-row' data-id='${labour.id}'>
                    <div class='d-flex justify-content-between align-items-center'>
                        <div>
                            <div class='fw-bold'>${labour.name}</div>
                            <small class='text-muted'>${labour.role || labour.skill || ''}</small>
                        </div>
                        <div>
                            <div class='btn-group btn-group-sm' role='group'>
                                <button class='btn btn-outline-light  markPresent' data-id='${labour.id}'>Present</button>
                                <button class='btn btn-outline-light  markAbsent' data-id='${labour.id}'>Absent</button>
                                <button class='btn btn-outline-light  markLeave' data-id='${labour.id}'>Leave</button>
                            </div>
                        </div>
                    </div>
                </div>
            `);
            container.append(item);
        });
    });
}

// =========================
// Add Labour - client side
// =========================
$(document).on('submit', '#addLabourForm', function (e) {
    e.preventDefault();
    console.log("Form called");
    const $form = $(this);
    const nameInput = $form.find('input[name="name"]');
    const RemarkOrAddressInput = $form.find('input[name="address"]');
    const skillInput = $form.find('input[name="skill"]');
    const phoneInput = $form.find('input[name="phone"]');

    const name = (nameInput.val() || '').trim();
    const role = (RemarkOrAddressInput.val() || '').trim();
    const skill = (skillInput.val() || '').trim();
    const phone = (phoneInput.val() || '').trim();

    // Basic validation (consistent with employee)
    if (!name) { alert('Please enter labour name'); nameInput.focus(); return; }


    const payload = {
        name: name,
        role: role,
        skill: skill,
        phone: phone,
        username: (name + role).replace(/\s+/g, '')
 // simple username pattern like employee
    };

    const $btn = $form.find('button[type="submit"]').prop('disabled', true);

    function handleResponse(res) {
        $btn.prop('disabled', false);

        if (!res) {
            alert('No response from server');
            return;
        }
        if (res.success) {
            // close modal
            const modalEl = document.getElementById('addLabourModal');
            if (modalEl) {
                const m = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                m.hide();
            }

            // reset form
            $form[0].reset();

            // If backend returned new_labour, append immediately
            if (res.new_labour && $('#labourList').length) {
                const l = res.new_labour;
                const item = $(`
                    <div class='timeline-item glass text-light labour-row' data-id='${l.id}'>
                        <div class='d-flex justify-content-between align-items-center'>
                            <div>
                                <div class='fw-bold'>${escapeHtml(l.name)}</div>
                                <small class='text-muted'>${escapeHtml(l.role || l.skill || '')}</small>
                            </div>
                            <div>
                                <div class='btn-group btn-group-sm' role='group'>
                                    <button class='btn btn-outline-light markPresent' data-id='${l.id}'>Present</button>
                                    <button class='btn btn-outline-light markAbsent' data-id='${l.id}'>Absent</button>
                                    <button class='btn btn-outline-light markExtraShift' data-id='${l.id}'>ExtraShift</button>
                                </div>
                            </div>
                        </div>
                    </div>
                `);
                $('#labourList').prepend(item);
            } else {
                // fallback to re-render for consistency
                if (typeof renderLabours === 'function') renderLabours($('#labourFilter').val() || '');
            }

            showToast('Labour added successfully');
        } else {
            alert(res.message || (res.errors ? res.errors.join(', ') : 'Failed to add labour'));
        }
    }

    // Use ajaxPost helper if present (keeps existing convention)
    if (typeof ajaxPost === 'function') {
        ajaxPost('api/labour/add.php', payload, handleResponse);
    } else {
        $.post('api/labour/add.php', payload, handleResponse, 'json')
         .fail(function (xhr) {
             console.error('Labour add error', xhr.responseText);
             alert('Server error while adding labour');
             $btn.prop('disabled', false);
         });
    }
});

// Small helpers used above
function showToast(message, timeout = 1600) {
    const id = 'small-toast';
    $('#' + id).remove();
    const $t = $(`<div id="${id}" class="toast position-fixed bottom-0 end-0 m-3 p-2 bg-success text-white rounded shadow">${message}</div>`);
    $('body').append($t);
    setTimeout(() => $t.fadeOut(300, () => $t.remove()), timeout);
}

function escapeHtml(s) {
    return String(s || '').replace(/[&<>"'`=\/]/g, function(ch){
        return {
            '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":"&#39;",'/':'&#x2F;','`':'&#96;','=':'&#61;'
        }[ch];
    });
}


// -------------------------
// Event handlers for marking attendance
// -------------------------
$(document).on('click', '.markPresent, .markAbsent, .markExtraShift', function () {
    const id = $(this).data('id');
    const projectId = $('#projectSelect').val() || 0;
    console.log(id);
    const status = $(this).hasClass('markPresent')
        ? 'Present'
        : $(this).hasClass('markExtraShift')
        ? 'ExtraShift'
        : 'Absent';

    // Disable the buttons while processing
    const btnGroup = $(this).closest('.btn-group');
    btnGroup.find('button').prop('disabled', true);

    ajaxPost(
        'api/attendance/mark.php',
        {
            labour_id: id,
            project_id: projectId,
            status: status,
            shift: (status == ExtraShift) ? 'night' : 'day',
        },
        function (res) {
            if (res.success) {
                // Smooth fade-out on success
                const row = $(`.labour-row[data-id='${id}']`);
                row.fadeOut(300, function () {
                    row.remove();
                });
            } else {
                alert('Failed to mark attendance: ' + (res.message || 'Unknown error'));
                btnGroup.find('button').prop('disabled', false);
            }
        }
    );
});
let filterTimeout = null;
// -------------------------
// Filter input
// -------------------------
$('#labourFilter').on('input', function () {
     clearTimeout(filterTimeout);
    const filter = $(this).val();
    filterTimeout = setTimeout(() => renderLabours(filter), 300);
});

// -------------------------
// Load labours on project change
// -------------------------
$('#projectSelect').on('change', function () {
    renderLabours($('#labourFilter').val());
});

// -------------------------
// Initial load
// -------------------------
$(document).ready(function () {
    renderLabours();
});
