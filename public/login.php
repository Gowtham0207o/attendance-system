<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Attendance System</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <style>
        /* ==== Aesthetic 3D Spline + Glass Form ==== */
        html, body {
            margin: 0;
            padding: 0;
            height: 100%;
            overflow: hidden;
            font-family: "Poppins", sans-serif;
            background-color: #0a0c1b;
            color: #fff;
        }

        /* Background Spline Scene */
        .spline-bg {
            position: fixed;
            top: 0; left: 0;
            width: 100vw; height: 100vh;
            z-index: 0;
            overflow: hidden;
        }

        /* Login Card */
        .login-container {
            position: relative;
            z-index: 5;
            width: 380px;
            margin: 10vh auto;
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(20px);
            border-radius: 18px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.6);
            padding: 40px;
            text-align: center;
            animation: floatUp 0.8s ease-out;
        }

        @keyframes floatUp {
            0% { opacity: 0; transform: translateY(40px); }
            100% { opacity: 1; transform: translateY(0); }
        }

        .login-container h2 {
            margin-bottom: 25px;
            font-weight: 700;
            color: #f0c94d;
        }

        .form-control {
            background: rgba(255,255,255,0.1);
            color: #fff;
            border: none;
            border-radius: 8px;
        }

        .form-control::placeholder {
            color: #bbb;
        }

        .btn-warning {
            width: 100%;
            border-radius: 8px;
            font-weight: 600;
            box-shadow: 0 0 15px rgba(255, 193, 7, 0.4);
            transition: all 0.3s ease;
        }

        .btn-warning:hover {
            background-color: #ffc107;
            transform: translateY(-2px);
            box-shadow: 0 0 25px rgba(255, 193, 7, 0.7);
        }

        .login-footer {
            font-size: 0.85rem;
            margin-top: 15px;
            color: #aaa;
        }

        .error-msg {
            color: #ff7070;
            font-size: 0.9rem;
            margin-top: 10px;
        }

        /* Responsive */
        @media (max-width: 480px) {
            .login-container {
                width: 90%;
                padding: 25px;
            }
        }
    </style>
</head>
<body>

    <!-- === 3D Spline Background === -->
    <div class="spline-bg">
        <!-- Example 3D scene (You can replace with your own Spline link) -->
        <iframe 
            src="https://my.spline.design/abstractbackground-4d214a997a4c8eae73c1a00e9d9c5f7b/"
            frameborder="0"
            width="100%"
            height="100%"
            style="filter: brightness(0.9) contrast(1.1);"
            allow="autoplay; fullscreen"
        ></iframe>
    </div>

    <!-- === Login Form === -->
    <div class="login-container shadow-lg">
        <h2>Attendance System</h2>
        <form id="loginForm" autocomplete="off">
            <div class="mb-3">
                <input type="text" class="form-control form-control-lg" name="username" placeholder="Username" required>
            </div>
            <div class="mb-3">
                <input type="password" class="form-control form-control-lg" name="password" placeholder="Password" required>
            </div>
            <button type="submit" class="btn btn-warning btn-lg">Sign In</button>
            <div class="error-msg" id="loginError"></div>
        </form>
        <div class="login-footer">
            &copy; <?= date('Y'); ?> SelfMade Technology. All rights reserved.
        </div>
    </div>

    <!-- === JS === -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        $(function() {
            $('#loginForm').on('submit', function(e) {
                e.preventDefault();
                $('#loginError').text('');
                const data = $(this).serialize();

                $.ajax({
                    url: 'api/auth/login.php',
                    method: 'POST',
                    data: data,
                    dataType: 'json',
                    success: function(res) {
                        if(res.success) {
                            window.location.href = 'index.php';
                        } else {
                            $('#loginError').text(res.error || 'Invalid username or password');
                        }
                    },
                    error: function() {
                        $('#loginError').text('Server error. Please try again.');
                    }
                });
            });
        });
    </script>
</body>
</html>
