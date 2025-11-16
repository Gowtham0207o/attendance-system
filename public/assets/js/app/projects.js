$(document).ready(function () {

    // Log to confirm file actually loaded
    console.log('✅ projects.js loaded');

    // Use delegated binding to catch submit from dynamic modals
    $(document).on('submit', '#addProjectForm', function (e) {
        e.preventDefault(); // stop normal form submission
        console.log('🚀 Add project form submitted via AJAX');

        const $form = $(this);
        const formData = $form.serialize();
  console.log(formData);

        // Optional: disable submit button to prevent duplicates
        const $btn = $form.find('button[type="submit"]').prop('disabled', true);

        $.ajax({
            url: 'api/projects/add.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function (res) {
                console.log('✅ Server response:', res);
                if (res.success) {
                    alert('Project added successfully!');
                    // Hide modal
                    const modalEl = document.getElementById('addProjectModal');
                    const modal = bootstrap.Modal.getInstance(modalEl);
                    modal.hide();
                    $form[0].reset();
                   
                   console.log()
                    if (res.new_project) {
                    const { id, name } = res.new_project;
                    const dropdown = $('#projectSelect');

                    // Append new project as an option
                    dropdown.append(`<option value="${id}">${name}</option>`);

                    // Select it immediately
                    dropdown.val(id).trigger('change');
                        console.log('🎉 Project added and dropdown updated');
                }
                } else {
                    alert(res.message || 'Failed to add project.');
                }
            },
            error: function (xhr) {
                console.error('❌ AJAX Error:', xhr.responseText);
                alert('Server error while adding project.');
            },
            complete: function () {
                $btn.prop('disabled', false);
            }
        });
    });

});
// Define global loader
function loadProjects() {
    $.ajax({
        url: 'api/project/list.php',
        type: 'GET',
        dataType: 'json',
        success: function (res) {
            console.log('🔁 Reloading projects:', res);
            if (res.success) {
                const dropdown = $('#projectSelect');
                dropdown.empty();
                if (res.data.length === 0) {
                    dropdown.append('<option disabled>No projects found</option>');
                    return;
                }
                res.data.forEach(p => {
                    dropdown.append(`<option value="${p.id}">${p.name}</option>`);
                });
            } else {
                console.error('❌ Server responded with success:false', res.message);
            }
        },
        error: function (xhr) {
            console.error('⚠️ Failed to load project list:', xhr.responseText);
        }
    });
}
