<?php
require_once 'vendor/autoload.php';
use Firebase\JWT\JWT;

// Set secure session parameters
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_samesite', 'Strict');
session_set_cookie_params(3600, '/', null, true, true);
session_start();

// Initialize variables
$error_message = "";
$max_attempts = 5;
$lockout_time = 900; // 15 minutes
$ip_address = $_SERVER['REMOTE_ADDR'];

// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Check if IP is locked out
$attempts_file = sys_get_temp_dir() . '/login_attempts.json';
if (file_exists($attempts_file)) {
    $attempts = json_decode(file_get_contents($attempts_file), true);
} else {
    $attempts = [];
}

if (isset($attempts[$ip_address])) {
    if ($attempts[$ip_address]['count'] >= $max_attempts && 
        time() - $attempts[$ip_address]['time'] < $lockout_time) {
        $error_message = "Too many login attempts. Please try again later.";
        header("Refresh: 3");
        exit();
    } elseif (time() - $attempts[$ip_address]['time'] >= $lockout_time) {
        unset($attempts[$ip_address]);
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error_message = "Invalid request";
        $log_entry = date('Y-m-d H:i:s') . " - CSRF Attack detected from {$ip_address}\n";
        file_put_contents('login_activity.log', $log_entry, FILE_APPEND);
        header("Location: login.php");
        exit();
    }
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
            // Validate password strength
if (strlen($password) < 8 || 
    !preg_match("/[A-Z]/", $password) || 
    !preg_match("/[a-z]/", $password) || 
    !preg_match("/[0-9]/", $password)) {
    $error_message = "Password must be at least 8 characters and contain uppercase, lowercase, and numbers";
} elseif (md5($password, $user['fjalkalimi']) || md5($password) === $user['fjalkalimi']) {
                // Store the new secure hash for future use
                $new_hash = password_hash($password, PASSWORD_DEFAULT);
                $update_sql = "UPDATE klientet SET fjalkalimi = ? WHERE id = ?";
                $update_stmt = $conn->prepare($update_sql);
                if ($update_stmt) {
                    $update_stmt->bind_param("si", $new_hash, $user['id']);
                    $update_stmt->execute();
                    $update_stmt->close();
                }
                // Generate JWT token
$secret_key = "your_secret_key_here";
$issued_at = time();
$expiration = $issued_at + 3600; // Token expires in 1 hour

$token_payload = [
    'iat' => $issued_at,
    'exp' => $expiration,
    'user_id' => $user['id'],
    'username' => $user['perdoruesi']
];

$jwt = JWT::encode($token_payload, $secret_key, 'HS256');

// Set secure session variables
$_SESSION['user_id'] = $user['id'];
$_SESSION['user_perdoruesi'] = $user['perdoruesi'];
$_SESSION['login_time'] = time();
$_SESSION['jwt'] = $jwt;

// Log successful login
$log_entry = date('Y-m-d H:i:s') . " - Successful login: {$user['perdoruesi']} from {$ip_address}\n";
file_put_contents('login_activity.log', $log_entry, FILE_APPEND);

// Clear login attempts for this IP
if (isset($attempts[$ip_address])) {
    unset($attempts[$ip_address]);
    file_put_contents($attempts_file, json_encode($attempts));
}

header("Location: dashboard.php");
exit();
            } else {
    // Log failed attempt
    if (!isset($attempts[$ip_address])) {
        $attempts[$ip_address] = [
            'count' => 1,
            'time' => time()
        ];
    } else {
        $attempts[$ip_address]['count']++;
        $attempts[$ip_address]['time'] = time();
    }
    file_put_contents($attempts_file, json_encode($attempts));
    
    $remaining_attempts = $max_attempts - $attempts[$ip_address]['count'];
    $error_message = "Invalid username or password. {$remaining_attempts} attempts remaining.";
    
    // Log failed login attempt
    $log_entry = date('Y-m-d H:i:s') . " - Failed login attempt for user: {$perdoruesi} from {$ip_address}\n";
    file_put_contents('login_activity.log', $log_entry, FILE_APPEND);
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
            0% { transform: translateY(0) rotate(0deg); }
            100% { transform: translateY(-100vh) rotate(360deg); }
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
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
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