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
                console.log(projectId);
                console.log("loaded o=frf");
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
