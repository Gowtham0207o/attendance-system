$('#signupForm').on('submit', function(e){
    e.preventDefault();
    $.post('api/auth/signup.php', $(this).serialize(), function(resp){
        if(resp.success){
            $('#signupSuccess').text('Admin created successfully');
            $('#signupError').text('');
            $('#signupForm')[0].reset();
        } else {
            $('#signupError').text(resp.error);
            $('#signupSuccess').text('');
        }
    }, 'json');
});
