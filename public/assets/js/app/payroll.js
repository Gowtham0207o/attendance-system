// -------------------------
// Create payroll
// -------------------------
$('#createPayrun').on('click', function(){
    var start = prompt('Enter start date (YYYY-MM-DD):', new Date().toISOString().slice(0,10));
    var end = prompt('Enter end date (YYYY-MM-DD):', new Date().toISOString().slice(0,10));
    if(!start || !end) return;

    ajaxPost('api/payroll/create.php', {start, end}, function(res){
        if(res.success){
            alert('Payroll created with ID ' + res.payroll_id);
            loadPayrolls();
        }
    });
});

// -------------------------
// Load payroll list
// -------------------------
function loadPayrolls() {
    ajaxGet('api/payroll/list.php', {}, function(res){
        var tbody = $('#payrollTable tbody');
        tbody.empty();
        res.data.forEach(function(p){
            tbody.append(`
                <tr>
                    <td>PayRun #${p.id}</td>
                    <td>${p.payroll_period_start} - ${p.payroll_period_end}</td>
                    <td>₹${parseFloat(p.total_amount).toLocaleString()}</td>
                    <td><button class="btn btn-sm btn-outline-light viewPayroll" data-id="${p.id}">View</button></td>
                </tr>
            `);
        });
    });
}

// -------------------------
// View payroll details
// -------------------------
$(document).on('click', '.viewPayroll', function(){
    var id = $(this).data('id');
    ajaxGet('api/payroll/view.php', {id}, function(res){
        var msg = res.data.map(d => `${d.labour_name} (${d.project_name}): ₹${d.total_amount}`).join('\n');
        alert('Payroll Details:\n' + msg);
    });
});

// Initial load
loadPayrolls();
