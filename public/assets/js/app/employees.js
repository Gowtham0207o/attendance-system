// employees.js
console.log("✅ employees.js loaded");

// -------------------------
// Render employee list
// -------------------------
function renderEmployees() {
    console.log("🎯 renderEmployees() running");

    $.ajax({
        url: 'api/employee/list.php',
        type: 'GET',
        dataType: 'json',
        success: function (res) {
            const container = $('#employeeList');
            container.empty();

            if (!res.success || !res.data || res.data.length === 0) {
                container.html("<div class='text-muted text-center py-3'>No employees found.</div>");
                return;
            }

            res.data.forEach(function (emp) {
                const card = $(`
                    <div class='card p-2 mb-2 glass employee-card'>
                        <div class='d-flex justify-content-between align-items-center'>
                            <div>
                                <div class='fw-bold'>${emp.name}</div>
                                <small class='text-muted'>${emp.role}</small><br>
                                <small class='text-warning'>₹${parseFloat(emp.salary || 0).toLocaleString()}</small>
                            </div>
                         <div>
    <div class='d-flex flex-wrap gap-2'>

        <div class='d-flex flex-wrap gap-2'>
            <div class='btn-group btn-group-sm' role='group'>
                <button class='btn btn-outline-light markPresentEmp' data-id='${emp.id}'>Present</button>
                <button class='btn btn-outline-light markAbsentEmp' data-id='${emp.id}'>Absent</button>

                <select class='form-select form-select-sm btn-outline-light markOthersEmp' data-id='${emp.id}' style="width:auto;">
                    <option value="">Others…</option>
                    <option value="Leave">Leave</option>
                    <option value="Half-day">Half-day</option>
                    <option value="WFH">WFH</option>
                    <option value="Holiday">Holiday</option>
                    <option value="Permission">Permission</option>
                </select>
            </div>
        </div>

        <button class='btn btn-sm btn-outline-light empUpdate' data-id='${emp.id}'>Update</button>

    </div>
</div>

                        </div>
                    </div>
                `);
                container.append(card);
            });
        },
        error: function (xhr, status, error) {
            console.error("❌ Error loading employees:", error);
        }
    });
}

// -------------------------
// Add employee form
// -------------------------
$(document).on('submit', '#addEmpForm', function (e) {
    e.preventDefault();

    const $form = $(this);

    // DEBUG: ensure form exists and show markup snapshot
    console.log('--- addEmpForm submit handler fired ---');
    console.log('Form element:', $form.length ? $form[0] : 'MISSING');
    console.log('Form html snippet:', $form.html().slice(0, 400));

    // Find inputs explicitly (safer than serializeArray when DOM oddities occur)
    const nameInput = $form.find('input[name="name"]');
    const roleInput = $form.find('input[name="role"]');
    const salaryInput = $form.find('input[name="salary"]');

    // Debug each
    console.log('name exists:', nameInput.length, 'value:', nameInput.val());
    console.log('role exists:', roleInput.length, 'value:', roleInput.val());
    console.log('salary exists:', salaryInput.length, 'value:', salaryInput.val());

    // Basic validation
    const name = (nameInput.val() || '').trim();
    const role = (roleInput.val() || '').trim();
    const salaryRaw = (salaryInput.val() || '').toString().trim();
    const salary = salaryRaw === '' ? null : Number(salaryRaw);

    if (!name) { alert('Please enter name'); nameInput.focus(); return; }
    if (!role) { alert('Please enter role'); roleInput.focus(); return; }
    if (salary === null || Number.isNaN(salary)) { alert('Please enter a valid salary'); salaryInput.focus(); return; }

    // Prevent duplicate submits
    const $submitBtn = $form.find('button[type="submit"]');
    $submitBtn.prop('disabled', true);

    // Prepare payload
    const payload = {
        name: name,
        role: role,
        salary: salary,
username: (name + role).replace(/\s+/g, '')

    };

    console.log('Posting payload:', payload);

    $.ajax({
        url: 'api/employee/add.php',
        method: 'POST',
        data: payload,
        dataType: 'json',
        success: function (res) {
            console.log('Server response:', res);
            if (res && res.success) {
                // close modal safely
                const modalEl = document.getElementById('addEmpModal');
                const modalInstance = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                modalInstance.hide();

                // refresh employee list
                if (typeof renderEmployees === 'function') renderEmployees();
                else if (typeof loadEmployees === 'function') loadEmployees();
            } else {
                alert(res && res.message ? res.message : 'Failed to add employee');
            }
        },
        error: function (xhr, status, err) {
            console.error('AJAX error:', status, err, xhr.responseText);
            alert('Server error while adding employee');
        },
        complete: function () {
            $submitBtn.prop('disabled', false);
        }
    });
});

// -------------------------
// Event handlers for marking attendance
// -------------------------
// $(document).on('click', '.markPresent, .markAbsent, .markLeave', function () {
//     const id = $(this).data('id');
//     console.log(id);
//     const status = $(this).hasClass('markPresent')
//         ? 'Present'
//         : $(this).hasClass('markAbsent')
//         ? 'Absent' 
//         : $(this).hasClass('markOthers')
//         ? 'here i want the value'
//         : 'Leave';

//     // Disable the buttons while processing
//     const btnGroup = $(this).closest('.btn-group');
//     btnGroup.find('button').prop('disabled', true);

//     ajaxPost(
//         'api/attendance/mark.php',
//         {
//             labour_id: id,
//             project_id: projectId,
//             status: status,
//             shift: 'Day',
//         },
//         function (res) {
//             if (res.success) {
//                 // Smooth fade-out on success
//                 const row = $(`.labour-row[data-id='${id}']`);
//                 row.fadeOut(300, function () {
//                     row.remove();
//                 });
//             } else {
//                 alert('Failed to mark attendance: ' + (res.message || 'Unknown error'));
//                 btnGroup.find('button').prop('disabled', false);
//             }
//         }
//     );
// });

// -------------------------
// Update employee
// -------------------------
// -------------------------
// Common function to send attendance
// -------------------------
function sendAttendance(id, status, btnGroup) {
    console.log(btnGroup);
    btnGroup.find('button, select').prop('disabled', true);
    console.log(id+status);
    ajaxPost(
        'api/employee/mark.php',
        {
            employee_id: id,        // or employee_id if this is for employees
            status: status,
            shift: 'Day',
        },
        function (res) {
            if (res.success) {
                console.log(res);
                              const card = $(`.employee-card:has([data-id='${id}'])`);
                
                // Fade-out effect
                card.fadeOut(300, function () {
                    $(this).remove();
                });
            } else {
                alert('Failed to mark attendance: ' + (res.message || 'Unknown error'));
                btnGroup.find('button, select').prop('disabled', false);
            }
        }
    );
}

// -------------------------
// Button handlers (Present / Absent)
// -------------------------
$(document).on('click', '.markPresentEmp, .markAbsentEmp', function () {
    const id = $(this).data('id');
    const btnGroup = $(this).closest('.btn-group');

    const status = $(this).hasClass('markPresent')
        ? 'Present'
        : 'Absent';

    sendAttendance(id, status, btnGroup);
});

// -------------------------
// Dropdown handler (Others)
// -------------------------
$(document).on('change', '.markOthersEmp', function () {
    const id = $(this).data('id');
    const status = $(this).val();      // <-- here you get the selected value
    if (!status) return;               // ignore if "Others…" placeholder

    const btnGroup = $(this).closest('.btn-group');

    sendAttendance(id, status, btnGroup);

    // Optional: reset back to placeholder after marking
    $(this).val('');
});

// -------------------------
// Global Loader
// -------------------------
window.loadEmployees = function () {
    console.log("🔁 loadEmployees() called");
    renderEmployees();
};

// -------------------------
// Initial auto-load
// -------------------------
$(document).ready(function () {
    renderEmployees();
});
