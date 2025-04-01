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
    
    // Verify required fields are present
    if (!isset($_POST['perdoruesi']) || !isset($_POST['password']) || 
        empty(trim($_POST['perdoruesi'])) || empty(trim($_POST['password']))) {
        $error_message = "Username and password are required";
    } else {
        include 'connection.php';
        $perdoruesi = $conn->real_escape_string(trim($_POST['perdoruesi']));
        $password = trim($_POST['password']);

        // Check for minimum username length
        if (strlen($perdoruesi) < 3) {
            $error_message = "Username must be at least 3 characters";
        } 
        // Validate password format
        elseif (strlen($password) < 8 || 
            !preg_match("/[A-Z]/", $password) || 
            !preg_match("/[a-z]/", $password) || 
            !preg_match("/[0-9]/", $password)) {
            $error_message = "Password must be at least 8 characters and contain uppercase, lowercase, and numbers";
        } else {
            $sql = "SELECT * FROM klientet WHERE perdoruesi = ?";
            $stmt = $conn->prepare($sql);
            if ($stmt) {
                $stmt->bind_param("s", $perdoruesi);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result->num_rows > 0) {
                    $user = $result->fetch_assoc();
                    
                    // Check if account is active
                    if (isset($user['status']) && $user['status'] !== 'active') {
                        $error_message = "This account has been deactivated. Please contact support.";
                        $log_entry = date('Y-m-d H:i:s') . " - Inactive account login attempt: {$perdoruesi} from {$ip_address}\n";
                        file_put_contents('login_activity.log', $log_entry, FILE_APPEND);
                    } 
                    // Check password - support both legacy md5 and new secure hashes
                    elseif (password_verify($password, $user['fjalkalimi']) || 
                            md5($password) === $user['fjalkalimi'] || 
                            md5($password) === md5($user['fjalkalimi'])) {
                        
                        // If using old hash format, update to new secure hash
                        if (!password_verify($password, $user['fjalkalimi'])) {
                            $new_hash = password_hash($password, PASSWORD_DEFAULT);
                            $update_sql = "UPDATE klientet SET fjalkalimi = ? WHERE id = ?";
                            $update_stmt = $conn->prepare($update_sql);
                            if ($update_stmt) {
                                $update_stmt->bind_param("si", $new_hash, $user['id']);
                                $update_stmt->execute();
                                $update_stmt->close();
                            }
                        }
                        // Generate JWT token
                        $secret_key = "your_secret_key_here";
                        $issued_at = time();
                        $expiration = $issued_at + 3600; // Token expires in 1 hour

                        $token_payload = [
                            'iat' => $issued_at,
                            'exp' => $expiration,
                            'user_id' => $user['id'],
                            'username' => $user['perdoruesi'],
                            'ip' => $ip_address
                        ];

                        $jwt = JWT::encode($token_payload, $secret_key, 'HS256');

                        // Set secure session variables
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['user_perdoruesi'] = $user['perdoruesi'];
                        $_SESSION['user_email'] = $user['email'] ?? '';
                        $_SESSION['login_time'] = time();
                        $_SESSION['last_activity'] = time();
                        $_SESSION['last_ip'] = $ip_address;
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
                    // User doesn't exist, but still increment failed attempts to prevent username enumeration
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
                    
                    // Log attempted login with non-existent user
                    $log_entry = date('Y-m-d H:i:s') . " - Login attempt with non-existent user: {$perdoruesi} from {$ip_address}\n";
                    file_put_contents('login_activity.log', $log_entry, FILE_APPEND);
                }
                $stmt->close();
            } else {
                $error_message = "System error, please try again later";
                error_log("Error preparing statement: " . $conn->error);
            }
            $conn->close();
        }
    }
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
        :root {
            --primary-color: #6B46C1;
            --secondary-color: #3B82F6;
            --error-color: #dc3545;
            --success-color: #28a745;
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e9f2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        }

        .login-container {
            background-color: #fff;
            border-radius: 16px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            width: 100%;
            max-width: 1000px;
            animation: containerFadeIn 0.6s ease-out;
        }

        .login-form {
            padding: 3rem;
        }

        .form-control {
            height: 50px;
            padding: 0.75rem 1.2rem;
            border-radius: 12px;
            border: 2px solid #E2E8F0;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(107, 70, 193, 0.1);
        }

        .input-group-text {
            border-radius: 12px;
            border: 2px solid #E2E8F0;
            background: white;
            padding: 0.75rem 1.2rem;
        }

        .btn-primary {
            height: 50px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border: none;
            font-weight: 600;
            padding: 0 2rem;
            border-radius: 12px;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(107, 70, 193, 0.3);
        }

        .password-strength {
            margin-top: 0.5rem;
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .strength-meter {
            height: 4px;
            flex: 1;
            background: #e9ecef;
            border-radius: 2px;
            overflow: hidden;
        }

        .strength-meter div {
            height: 100%;
            width: 0;
            transition: width 0.3s ease;
        }

        .weak { background-color: var(--error-color); }
        .medium { background-color: #ffc107; }
        .strong { background-color: var(--success-color); }

        .invalid-feedback {
            display: block;
            margin-top: 0.5rem;
            font-size: 0.875rem;
            color: var(--error-color);
        }

        .login-image {
            background: linear-gradient(135deg, #EDF2F7, #F7FAFC);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            position: relative;
        }

        @keyframes containerFadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .alert {
            border: none;
            border-radius: 12px;
            padding: 1rem 1.5rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            animation: alertSlideDown 0.3s ease-out;
        }

        @keyframes alertSlideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .has-validation .form-control {
            margin-bottom: 0;
        }

        .validation-icon {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            z-index: 4;
            display: none;
        }

        .is-valid ~ .validation-icon.valid-icon {
            display: block;
            color: var(--success-color);
        }

        .is-invalid ~ .validation-icon.invalid-icon {
            display: block;
            color: var(--error-color);
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="row g-0">
            <div class="col-lg-6 login-form">
                <div class="mb-4">
                    <h2 class="fw-bold mb-2">Welcome Back</h2>
                    <p class="text-muted mb-4">Sign in to access your dashboard</p>
                </div>
                <?php if (!empty($error_message)): ?>
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-circle-fill"></i>
                        <span><?php echo $error_message; ?></span>
                    </div>
                <?php endif; ?>
                <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" id="loginForm" novalidate>
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <div class="mb-4">
                        <label for="perdoruesi" class="form-label">Username</label>
                        <div class="input-group has-validation">
                            <span class="input-group-text">
                                <i class="bi bi-person"></i>
                            </span>
                            <input type="text" class="form-control" id="perdoruesi" name="perdoruesi" 
                                value="<?php echo isset($_POST['perdoruesi']) ? htmlspecialchars($_POST['perdoruesi']) : ''; ?>" 
                                required minlength="3">
                            <div class="validation-icon valid-icon">
                                <i class="bi bi-check-circle-fill"></i>
                            </div>
                            <div class="validation-icon invalid-icon">
                                <i class="bi bi-x-circle-fill"></i>
                            </div>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group has-validation">
                            <span class="input-group-text">
                                <i class="bi bi-lock"></i>
                            </span>
                            <input type="password" class="form-control" id="password" name="password" required>
                            <span class="input-group-text" id="togglePassword" style="cursor: pointer;">
                                <i class="bi bi-eye-slash"></i>
                            </span>
                            <div class="validation-icon valid-icon">
                                <i class="bi bi-check-circle-fill"></i>
                            </div>
                            <div class="validation-icon invalid-icon">
                                <i class="bi bi-x-circle-fill"></i>
                            </div>
                        </div>
                        <div class="password-strength d-none">
                            <span class="strength-text"></span>
                            <div class="strength-meter">
                                <div></div>
                            </div>
                        </div>
                    </div>
                    <div class="mb-4 text-end">
                        <a href="recover_password.php" class="text-decoration-none" style="color: var(--primary-color);">
                            Forgot password?
                        </a>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Sign In</button>
                    </div>
                </form>
            </div>
            <div class="col-lg-6 login-image d-none d-lg-block">
                <img src="assets/login-illustration.svg" alt="Login" class="img-fluid" style="max-width: 80%;">
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('loginForm');
            const username = document.getElementById('perdoruesi');
            const password = document.getElementById('password');
            const togglePassword = document.getElementById('togglePassword');
            const strengthMeter = document.querySelector('.password-strength');
            const strengthText = document.querySelector('.strength-text');
            const strengthBar = document.querySelector('.strength-meter div');
            
            function validateUsername(input) {
                const isValid = input.value.trim().length >= 3;
                input.classList.toggle('is-valid', isValid);
                input.classList.toggle('is-invalid', !isValid);
                return isValid;
            }

            function checkPasswordStrength(password) {
                let score = 0;
                if (password.length >= 8) score++;
                if (password.length >= 12) score++;
                if (/[A-Z]/.test(password)) score++;
                if (/[a-z]/.test(password)) score++;
                if (/[0-9]/.test(password)) score++;
                if (/[^A-Za-z0-9]/.test(password)) score += 2;
                
                const strengthClass = score < 3 ? 'weak' : score < 5 ? 'medium' : 'strong';
                const strengthText = score < 3 ? 'Weak' : score < 5 ? 'Medium' : 'Strong';
                const percentage = (score / 7) * 100;
                
                return { strengthClass, strengthText, percentage };
            }

            function validatePassword(input) {
                const password = input.value;
                const isValid = password.length >= 8 && 
                               /[A-Z]/.test(password) && 
                               /[a-z]/.test(password) && 
                               /[0-9]/.test(password);
                               
                input.classList.toggle('is-valid', isValid);
                input.classList.toggle('is-invalid', !isValid);
                
                if (password.length > 0) {
                    strengthMeter.classList.remove('d-none');
                    const strength = checkPasswordStrength(password);
                    strengthText.textContent = strength.strengthText;
                    strengthBar.className = strength.strengthClass;
                    strengthBar.style.width = strength.percentage + '%';
                } else {
                    strengthMeter.classList.add('d-none');
                }
                
                return isValid;
            }

            username.addEventListener('input', () => validateUsername(username));
            password.addEventListener('input', () => validatePassword(password));
            
            togglePassword.addEventListener('click', function() {
                const type = password.type === 'password' ? 'text' : 'password';
                password.type = type;
                this.querySelector('i').classList.toggle('bi-eye');
                this.querySelector('i').classList.toggle('bi-eye-slash');
            });

            form.addEventListener('submit', function(e) {
                const isUsernameValid = validateUsername(username);
                const isPasswordValid = validatePassword(password);
                
                if (!isUsernameValid || !isPasswordValid) {
                    e.preventDefault();
                    const existingAlert = document.querySelector('.alert-danger');
                    if (existingAlert) existingAlert.remove();
                    
                    const alert = document.createElement('div');
                    alert.className = 'alert alert-danger';
                    alert.innerHTML = `
                        <i class="bi bi-exclamation-circle-fill"></i>
                        <span>Please fix the validation errors before submitting.</span>
                    `;
                    form.insertBefore(alert, form.firstChild);
                }
            });
        });
    </script>
</body>
</html>