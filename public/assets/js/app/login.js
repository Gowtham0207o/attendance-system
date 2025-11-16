$('#loginForm').on('submit', function(e){
    e.preventDefault();
    $.post('api/auth/login.php', $(this).serialize(), function(resp){
        if(resp.success){
            window.location.href = 'index.php';
        } else {
            $('#loginError').text(resp.error);
        }
    }, 'json');
});
