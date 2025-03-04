<?php
// Start the session
session_start();

// Initialize variables
$error_message = "";

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Include database connection
    include 'connection.php';

    // Get user input (perdoruesi instead of email)
    $perdoruesi = $conn->real_escape_string($_POST['perdoruesi']);
    $password = $_POST['password'];

    // Query to check user credentials using 'perdoruesi'
    $sql = "SELECT * FROM klientet WHERE perdoruesi = ?";

    // Prepare statement
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        // Bind parameters
        $stmt->bind_param("s", $perdoruesi);

        // Execute query
        $stmt->execute();

        // Get results
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // User found
            $user = $result->fetch_assoc();

            // Verify password (MD5)
            if (md5($password) === $user['fjalkalimi']) {
                // Password is correct, create session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_perdoruesi'] = $user['perdoruesi'];

                // Redirect to dashboard or home page
                header("Location: dashboard.php");
                exit();
            } else {
                // Wrong password
                $error_message = "Invalid username or password";
            }
        } else {
            // User not found
            $error_message = "Invalid username or password";
        }

        // Close statement
        $stmt->close();
    } else {
        // Error in preparing statement
        $error_message = "System error, please try again later";
        error_log("Error preparing statement: " . $conn->error);
    }

    // Close connection
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRM Login</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #4e73df;
            height: 100vh;
        }

        .login-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            overflow: hidden;
        }

        .login-form {
            padding: 3rem;
        }

        .login-image {
            background-color: #4e73df;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .form-control {
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
        }

        .btn-primary {
            background-color: #4e73df;
            border-color: #4e73df;
            padding: 0.75rem;
            border-radius: 0.5rem;
        }

        .btn-outline-secondary {
            border-color: #d1d3e2;
            color: #6c757d;
            padding: 0.75rem;
            border-radius: 0.5rem;
        }

        .social-login {
            display: flex;
            justify-content: center;
            margin-top: 1.5rem;
        }

        .social-login a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: 1px solid #d1d3e2;
            margin: 0 0.5rem;
            color: #6c757d;
            text-decoration: none;
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
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="row justify-content-center align-items-center vh-100">
            <div class="col-lg-10">
                <div class="row login-container g-0">
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
                                    <span class="input-group-text bg-transparent border-start-0">
                                        <i class="bi bi-lock"></i>
                                    </span>
                                </div>
                            </div>

                            <div class="mb-4 d-flex justify-content-between">
                                <div class="remember-me">
                                    <input type="checkbox" id="remember" name="remember" class="form-check-input">
                                    <label for="remember" class="form-check-label">Remember me</label>
                                </div>
                                <a href="recover_password.php" class="text-decoration-none text-primary">Recover password</a>
                            </div>

                            <div class="d-grid gap-2 mb-3">
                                <button type="submit" class="btn btn-primary">Sign In</button>
                            </div>

                            <div class="d-grid gap-2">
                                <a href="register.php" class="btn btn-outline-secondary">Sign Up</a>
                            </div>
                        </form>

                        <div class="mt-4 text-center">
                            <p class="text-muted">Or sign in with</p>
                            <div class="social-login">
                                <a href="#"><i class="bi bi-twitter"></i></a>
                                <a href="#"><i class="bi bi-google"></i></a>
                                <a href="#"><i class="bi bi-facebook"></i></a>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 login-image d-none d-md-flex">
                        <img src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.8.1/svgfile.svg" alt="CRM Illustration" class="img-fluid" style="max-width: 80%;">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>