// -------------------------
// Common helpers for AJAX, error handling, modals
// -------------------------
function showError(message) {
    console.error(message);
    alert(message); // Simple alert, can be upgraded to banner
}

function ajaxPost(url, data, success, error) {
    console.log(data);
    $.ajax({
        url: url,
        type: 'POST',
        data: JSON.stringify(data),
        contentType: 'application/json',
        dataType: 'json',
        success: success,
        error: function(xhr, status, err) {
            if (error) error(xhr, status, err);
            showError('AJAX error: ' + err);
        }
    });
}

function ajaxGet(url, params, success, error) {
    $.ajax({
        url: url,
        type: 'GET',
        data: params,
        dataType: 'json',
        success: success,
        error: function(xhr, status, err) {
            if (error) error(xhr, status, err);
     showError('AJAX GET error: ' + err + ' | ' + xhr.responseText);

        }
    });
}

// -------------------------
// Modal helper
// -------------------------
function openModal(modalId) {
    var el = document.getElementById(modalId);
    if (window.bootstrap && bootstrap.Modal) {
        bootstrap.Modal.getOrCreateInstance(el).show();
    }
}

function closeModal(modalId) {
    var el = document.getElementById(modalId);
    if (window.bootstrap && bootstrap.Modal) {
        var inst = bootstrap.Modal.getInstance(el);
        if (inst) inst.hide();
    }
}
