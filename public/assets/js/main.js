// Load all modules safely
$(document).ready(function(){
    // You can initialize tabs, modals, etc. here


});

// public/assets/js/main.js
$(document).ready(function () {
    console.log("Main initialized");

    // When user switches tabs
    $('button[data-bs-toggle="tab"]').on("shown.bs.tab", function (e) {
        const target = $(e.target).attr("data-bs-target");

        switch (target) {
            case "#labour":
                const projectId = $("#projectSelect").val();
                if (projectId) renderLabourByProject(projectId);
                break;

            case "#employee":
                loadEmployees();
                break;

            case "#payroll":
                loadPayrolls();
                break;
        }
    });
});
$('#exportCsv').on('click', function () {
    // Simple GET that triggers a file download
    window.location.href = 'api/attendance/export_employee.php';
});
// Assuming you already have a selected date in `selectedDate` (YYYY-MM-DD)
$('#exportLabourDayAttendance').on('click', function () {
    const selectedDate = $('#selectedDateInput').val() || new Date().toISOString().split('T')[0];
    window.location.href = 'api/attendance/export_labour_attendance.php?date=' + encodeURIComponent(selectedDate);
});

// When Export Team Attendance button is clicked → open modal
$(document).on('click', '#exportTeamAttendanceBtn', function () {
    // Set default values
    $('#teamExportFrom').val(new Date().toISOString().split('T')[0]);
    $('#teamExportTo').val(new Date().toISOString().split('T')[0]);

    const modal = new bootstrap.Modal(document.getElementById('exportTeamModal'));
    modal.show();
});

// When user submits the export form inside modal
$(document).on('submit', '#exportTeamForm', function (e) {
    e.preventDefault();

    const from  = $('#teamExportFrom').val();
    const to    = $('#teamExportTo').val();
    const shift = $('#teamExportShift').val() || 'all';
    const teamId = $('#teamSelect').val() || '';  // optional filter

    if (!from || !to) {
        alert("Please select both FROM and TO dates.");
        return;
    }

    if (from > to) {
        alert("FROM date cannot be greater than TO date.");
        return;
    }

    // Close modal
    const modalEl = document.getElementById('exportTeamModal');
    const modal = bootstrap.Modal.getInstance(modalEl);
    modal.hide();

    // Build query parameters
    const params = $.param({
        from: from,
        to: to,
        shift: shift,
        team_id: teamId
    });

    // Trigger the download (same as your labour export method)
    window.location.href = 'api/team/export.php?' + params;
});
