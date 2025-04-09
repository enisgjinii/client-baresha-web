<?php
session_set_cookie_params(3600);
session_start();

$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    include 'connection.php';
    $perdoruesi = $conn->real_escape_string($_POST['perdoruesi']);
    $password = $_POST['password'];
    $sql = "SELECT * FROM klientet WHERE perdoruesi = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("s", $perdoruesi);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (md5($password) === $user['fjalkalimi']) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_perdoruesi'] = $user['perdoruesi'];
                $_SESSION['login_time'] = time();
                header("Location: dashboard.php");
                exit();
            } else {
                $error_message = "Invalid username or password";
            }
        } else {
            $error_message = "Invalid username or password";
        }
        $stmt->close();
    } else {
        $error_message = "System error, please try again later";
        error_log("Error preparing statement: " . $conn->error);
    }
    $conn->close();
}

if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time'] > 3600)) {
    session_unset();
    session_destroy();
    header("Location: " . $_SERVER["PHP_SELF"]);
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRM Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <style>
        body {
            background: #fff;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow-x: hidden;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        }

        .login-container {
            background-color: #fff;
            border-radius: 8px;
            border: 1px solid #ddd;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            max-width: 1000px;
            width: 95%;
        }

        .login-form {
            padding: 3rem;
        }

        .login-image {
            background: #f8f9fa;
            border-radius: 0 8px 8px 0;
            border-left: 1px solid #ddd;
            position: relative;
            overflow: hidden;
        }

        .form-control {
            padding: 1rem;
            border-radius: 12px;
            border: 2px solid #E2E8F0;
            font-size: 1rem;
            background: rgba(255, 255, 255, 0.9);
            transition: all 0.3s ease;
        }

        .form-control:focus {
            box-shadow: 0 0 0 3px rgba(107, 70, 193, 0.2);
            border-color: #6B46C1;
        }

        .btn-primary {
            background: linear-gradient(135deg, #6B46C1, #3B82F6);
            border: none;
            padding: 1rem;
            border-radius: 12px;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(107, 70, 193, 0.3);
        }

        .input-group-text {
            border-radius: 12px;
            border: 2px solid #E2E8F0;
            background: white;
        }

        .floating-shapes {
            position: absolute;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 0;
        }

        .shape {
            position: absolute;
            background: rgba(255, 255, 255, 0.1);
            animation: floatingShapes 20s linear infinite;
        }

        @keyframes containerFadeIn {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes floatingShapes {
            0% {
                transform: translateY(0) rotate(0deg);
            }

            100% {
                transform: translateY(-100vh) rotate(360deg);
            }
        }

        .remember-me {
            gap: 0.5rem;
        }

        .form-check-input:checked {
            background-color: #6B46C1;
            border-color: #6B46C1;
        }

        .alert {
            border: none;
            border-radius: 12px;
            padding: 1rem;
            background: rgba(254, 226, 226, 0.5);
            backdrop-filter: blur(4px);
        }

        @media (max-width: 768px) {
            .login-container {
                width: 90%;
                margin: 1rem;
            }

            .login-form {
                padding: 2rem;
            }
        }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="row g-0">
            <div class="col-lg-6 login-form">
                <div class="mb-4">
                    <h2 class="fw-bold" style="color: #2D3748;">Welcome Back</h2>
                    <p class="text-muted">Sign in to access your CRM dashboard</p>
                </div>
                <?php if (!empty($error_message)): ?>
                    <div class="alert alert-danger d-flex align-items-center" role="alert">
                        <i class="bi bi-exclamation-circle me-2"></i>
                        <?php echo $error_message; ?>
                    </div>
                <?php endif; ?>
                <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <div class="mb-4">
                        <label for="perdoruesi" class="form-label text-muted mb-2">Username</label>
                        <div class="input-group">
                            <span class="input-group-text border-end-0">
                                <i class="bi bi-person"></i>
                            </span>
                            <input type="text" class="form-control border-start-0" id="perdoruesi" name="perdoruesi"
                                value="<?php echo isset($_POST['perdoruesi']) ? htmlspecialchars($_POST['perdoruesi']) : ''; ?>" required>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label for="password" class="form-label text-muted mb-2">Password</label>
                        <div class="input-group">
                            <span class="input-group-text border-end-0">
                                <i class="bi bi-lock"></i>
                            </span>
                            <input type="password" class="form-control border-start-0" id="password" name="password" required>
                            <span class="input-group-text border-start-0" id="togglePassword">
                                <i class="bi bi-eye-slash"></i>
                            </span>
                        </div>
                    </div>
                    <div class="mb-4 d-flex justify-content-between align-items-center">
                        <div class="remember-me d-flex align-items-center">
                            <input type="checkbox" id="remember" name="remember" class="form-check-input">
                            <label for="remember" class="form-check-label text-muted">Remember me</label>
                        </div>
                        <a href="recover_password.php" class="text-decoration-none" style="color: #6B46C1;">Forgot password?</a>
                    </div>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">Sign In</button>
                    </div>
                </form>
            </div>
            <div class="col-lg-6 login-image d-none d-lg-block">
                <div class="floating-shapes">
                    <div class="shape"></div>
                </div>
                <div class="h-100 d-flex align-items-center justify-content-center">
                    <img src="assets/login-illustration.svg" alt="CRM Illustration" class="img-fluid p-5" style="max-width: 100%; z-index: 1;">
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordField = document.getElementById('password');
            const icon = this.querySelector('i');
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            } else {
                passwordField.type = 'password';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            }
        });
    </script>
</body>

</html>