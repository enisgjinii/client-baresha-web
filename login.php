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
            background: linear-gradient(135deg, #4e73df, #224abe);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow-x: hidden;
        }

        .login-container {
            background-color: white;
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            max-width: 900px;
            width: 100%;
            animation: fadeIn 0.8s ease-in-out;
        }

        .login-form {
            padding: 2rem;
        }

        .login-image {
            background: linear-gradient(45deg, #4e73df, #6a89cc);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .form-control {
            padding: 0.75rem;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            box-shadow: 0 0 10px rgba(78, 115, 223, 0.5);
            border-color: #4e73df;
        }

        .btn-primary {
            background-color: #4e73df;
            border-color: #4e73df;
            padding: 0.75rem;
            border-radius: 8px;
            transition: transform 0.2s ease, background-color 0.3s ease;
        }

        .btn-primary:hover {
            transform: scale(1.05);
            background-color: #4263cc;
        }

        .remember-me {
            display: flex;
            align-items: center;
        }

        .remember-me input {
            margin-right: 0.5rem;
        }

        .alert {
            padding: 0.75rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            animation: slideIn 0.5s ease-out;
        }

        .input-group-text {
            cursor: pointer;
            background-color: transparent;
            border-radius: 8px;
            transition: color 0.3s ease;
        }

        .input-group-text:hover {
            color: #4e73df;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @media (max-width: 768px) {
            .login-container {
                margin: 1rem;
            }

            .login-form {
                padding: 1.5rem;
            }

            .login-image {
                display: none;
            }
        }

        @media (max-width: 576px) {
            h2 {
                font-size: 1.5rem;
            }

            h4 {
                font-size: 1.25rem;
            }

            .btn-primary {
                padding: 0.65rem;
            }
        }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="row g-0">
            <div class="col-md-6 login-form">
                <h2 class="fw-bold mb-2">Welcome to our CRM.</h2>
                <h4 class="text-dark mb-4">Sign In to see latest updates.</h4>
                <p class="text-muted mb-4">Enter your details to proceed further</p>
                <?php if (!empty($error_message)): ?>
                    <div class="alert alert-danger" role="alert">
                        <?php echo $error_message; ?>
                    </div>
                <?php endif; ?>
                <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <div class="mb-4">
                        <label for="perdoruesi" class="form-label text-muted">Perdoruesi</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="perdoruesi" name="perdoruesi" value="<?php echo isset($_POST['perdoruesi']) ? htmlspecialchars($_POST['perdoruesi']) : ''; ?>" required>
                            <span class="input-group-text bg-transparent border-start-0">
                                <i class="bi bi-person"></i>
                            </span>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label for="password" class="form-label text-muted">Password</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="password" name="password" placeholder="Start typing..." required>
                            <span class="input-group-text" id="togglePassword">
                                <i class="bi bi-eye-slash"></i>
                            </span>
                        </div>
                    </div>
                    <div class="mb-4 d-flex justify-content-between flex-wrap">
                        <div class="remember-me">
                            <input type="checkbox" id="remember" name="remember" class="form-check-input">
                            <label for="remember" class="form-check-label">Remember me</label>
                        </div>
                        <a href="recover_password.php" class="text-decoration-none text-primary">Recover password</a>
                    </div>
                    <div class="d-grid gap-2 mb-3">
                        <button type="submit" class="btn btn-primary">Sign In</button>
                    </div>
                </form>
            </div>
            <div class="col-md-6 login-image d-none d-md-flex">
                <img src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.8.1/svgfile.svg" alt="CRM Illustration" class="img-fluid" style="max-width: 80%; animation: float 3s ease-in-out infinite;">
            </div>
        </div>
    </div>
    <style>
        @keyframes float {
            0% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-15px);
            }

            100% {
                transform: translateY(0);
            }
        }
    </style>
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