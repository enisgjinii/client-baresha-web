<?php
include 'connection.php';
include 'header.php';
include 'sidebar.php';
$user_id = $_SESSION['user_id'];
$toastMessage = "";
$toastColor = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $perdoruesi = isset($_POST['perdoruesi']) ? trim($_POST['perdoruesi']) : "";
    $fjalkalimi = isset($_POST['fjalkalimi']) ? trim($_POST['fjalkalimi']) : "";
    
    if (empty($perdoruesi)) {
        $toastMessage = "Përdoruesi është i domosdoshëm.";
        $toastColor = "#dc3545";
    } else {
        if (empty($fjalkalimi)) {
            $sqlUpdate = "UPDATE klientet SET perdoruesi=? WHERE id=?";
            $stmt = $conn->prepare($sqlUpdate);
            if (!$stmt) {
                $toastMessage = "Gabim gjatë përgatitjes së përditësimit.";
                $toastColor = "#dc3545";
            } else {
                $stmt->bind_param("si", $perdoruesi, $user_id);
                if ($stmt->execute()) {
                    $toastMessage = "Përdoruesi u përditësua me sukses.";
                    $toastColor = "#28a745";
                } else {
                    $toastMessage = "Gabim gjatë përditësimit të përdoruesit.";
                    $toastColor = "#dc3545";
                }
                $stmt->close();
            }
        } else {
            // Validate password strength
            if (strlen($fjalkalimi) < 8 || 
                !preg_match("/[A-Z]/", $fjalkalimi) || 
                !preg_match("/[a-z]/", $fjalkalimi) || 
                !preg_match("/[0-9]/", $fjalkalimi)) {
                $toastMessage = "Fjalëkalimi duhet të jetë të paktën 8 karaktere dhe të përmbajë shkronja të mëdha, të vogla dhe numra.";
                $toastColor = "#dc3545";
            } else {
                $hashed = password_hash($fjalkalimi, PASSWORD_DEFAULT);
                $sqlUpdate = "UPDATE klientet SET perdoruesi=?, fjalkalimi=? WHERE id=?";
                $stmt = $conn->prepare($sqlUpdate);
                if (!$stmt) {
                    $toastMessage = "Gabim gjatë përgatitjes së përditësimit.";
                    $toastColor = "#dc3545";
                } else {
                    $stmt->bind_param("ssi", $perdoruesi, $hashed, $user_id);
                    if ($stmt->execute()) {
                        $toastMessage = "Përdoruesi dhe fjalëkalimi u përditësuan me sukses.";
                        $toastColor = "#28a745";
                    } else {
                        $toastMessage = "Gabim gjatë përditësimit të të dhënave.";
                        $toastColor = "#dc3545";
                    }
                    $stmt->close();
                }
            }
        }
    }
}
$sql = "SELECT * FROM klientet WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$client = $result->fetch_assoc();
$stmt->close();
?>
<html>

<head>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>

<body>
    <div class="col-md-10 main-content">
        <div class="mb-4 fade-in">
            <h3 class="fw-bold text-primary"><i class="fas fa-cog me-2"></i> Cilësimet e Llogarisë</h3>
            <p class="text-muted">Përditësoni përdoruesin dhe fjalëkalimin tuaj. Të dhënat tjera janë vetëm për shikim.</p>
        </div>
        <div class="card shadow-sm rounded slide-up">
            <div class="card-body">
                <?php if ($client): ?>
                    <form method="post" action="settings.php" class="mb-4">
                        <input type="hidden" name="id" value="<?php echo $client['id']; ?>">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="perdoruesi" class="form-label"><i class="fas fa-user me-1"></i> Përdoruesi</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-user-edit"></i></span>
                                    <input type="text" name="perdoruesi" id="perdoruesi" class="form-control" value="<?php echo htmlspecialchars($client['perdoruesi']); ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="fjalkalimi" class="form-label"><i class="fas fa-key me-1"></i> Fjalëkalimi</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="password" name="fjalkalimi" id="fjalkalimi" class="form-control" placeholder="Lini bosh për të ruajtur aktualin">
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i> Përditëso Llogarinë</button>
                    </form>
                    <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                        <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i> Informacione të Tjera</h6>
                        <button id="copyButton" class="btn btn-sm btn-outline-secondary"><i class="fas fa-copy me-1"></i> Kopjo Informacionin</button>
                    </div>
                    <div id="readOnlyInfo" class="row">
                        <?php
                        $fields = [
                            'emri' => 'Emri',
                            'monetizuar' => 'Monetizuar',
                            'dk' => 'DK',
                            'dks' => 'DKS',
                            'youtube' => 'YouTube',
                            'info' => 'Informacion',
                            'perqindja' => 'Përqindja',
                            'kontrata' => 'Kontrata',
                            'ads' => 'Reklama',
                            'fb' => 'Facebook',
                            'ig' => 'Instagram',
                            'adresa' => 'Adresa',
                            'kategoria' => 'Kategoria',
                            'nrtel' => 'Numri i Telefonit',
                            'emailadd' => 'Email',
                            'emailp' => 'Email Sekondar',
                            'infoprw' => 'Informacion PRW',
                            'emriart' => 'Emri Art',
                            'nrllog' => 'Nr. Log',
                            'np' => 'NP',
                            'glist' => 'Lista',
                            'emails' => 'Emails',
                            'perqindja2' => 'Përqindja 2',
                            'blocked' => 'Bllokuar',
                            'subscribers' => 'Abonues',
                            'parregullt' => 'Parregullt',
                            'perqindja_check' => 'Kontroll Përqindje',
                            'perqindja_platformave_check' => 'Kontroll Platformash',
                            'bank_name' => 'Emri i Bankës',
                            'lloji_klientit' => 'Lloji i Klientit',
                            'aktiv' => 'Aktiv',
                            'email_kontablist' => 'Email i Kontabilitetit',
                            'shtetsia' => 'Shtetësia',
                            'statusi_i_kontrates' => 'Statusi i Kontratës',
                            'shtetsiaKontabiliteti' => 'Shtetësia e Kontabilitetit',
                            'agent' => 'Agjenti',
                            'emribiz' => 'Emri i Biznesit',
                            'numri_unik_i_biznesit' => 'Numri Unik i Biznesit',
                            'kontrata_size' => 'Madhësia e Kontratës'
                        ];
                        foreach ($fields as $key => $label) {
                            if (!empty($client[$key])) {
                                echo '<div class="col-md-6 mb-2"><strong class="text-info"><i class="fas fa-tag me-1"></i> ' . $label . ':</strong> <span class="ms-1">' . htmlspecialchars($client[$key]) . '</span></div>';
                            }
                        }
                        ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <h5>Nuk u gjetën të dhëna për klientin.</h5>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="card shadow-sm rounded slide-up mt-4">
            <div class="card-body">
                <h6 class="mb-3"><i class="fas fa-shield-alt me-2"></i> Session Information</h6>
                <div class="row">
                    <div class="col-md-6 mb-2">
                        <strong class="text-info"><i class="fas fa-fingerprint me-1"></i> Session ID:</strong>
                        <span class="ms-1"><?php echo session_id(); ?></span>
                    </div>
                    <div class="col-md-6 mb-2">
                        <strong class="text-info"><i class="fas fa-clock me-1"></i> Login Time:</strong>
                        <span class="ms-1"><?php echo date('Y-m-d H:i:s', $_SESSION['login_time']); ?></span>
                    </div>
                    <div class="col-md-12 mb-2">
                        <strong class="text-info"><i class="fas fa-key me-1"></i> JWT Token:</strong>
                        <div class="input-group">
                            <input type="text" class="form-control" value="<?php echo $_SESSION['jwt']; ?>" readonly>
                            <button class="btn btn-outline-secondary" type="button" onclick="copyJWT()"><i class="fas fa-copy"></i></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('copyButton').addEventListener('click', function() {
            var infoText = document.getElementById('readOnlyInfo').innerText;
            navigator.clipboard.writeText(infoText).then(function() {
                Toastify({
                    text: "Informacioni u kopjua!",
                    duration: 3000,
                    gravity: "top",
                    position: "right",
                    backgroundColor: "#28a745"
                }).showToast();
            }, function(err) {
                Toastify({
                    text: "Kopjimi dështoi!",
                    duration: 3000,
                    gravity: "top",
                    position: "right",
                    backgroundColor: "#dc3545"
                }).showToast();
            });
        });
        <?php if (!empty($toastMessage)): ?>
            Toastify({
                text: "<?php echo $toastMessage; ?>",
                duration: 3000,
                gravity: "top",
                position: "right",
                backgroundColor: "<?php echo $toastColor; ?>"
            }).showToast();
        <?php endif; ?>
    </script>
    <style>
        .main-content {
            padding: 2rem;
            /* Increased padding for more spacious feel */
        }

        .fade-in {
            animation: fadeIn 0.8s ease-in-out;
        }

        .slide-up {
            animation: slideUp 0.6s ease-out;
        }

        .card {
            border-radius: 10px;
            /* More rounded corners for card */
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: 1px solid #e9ecef;
            /* Light border for definition */
        }

        .card:hover {
            transform: translateY(-3px);
            /* Slightly more lift on hover */
            box-shadow: 0 7px 20px rgba(0, 0, 0, 0.12);
            /* Enhanced shadow on hover */
        }

        .card-body {
            padding: 1.5rem;
            /* Increased padding in card body */
        }

        .form-label {
            font-weight: 500;
            /* Stronger font weight for labels */
            color: #495057;
            /* Darker label text */
            margin-bottom: 0.75rem;
            /* Increased margin bottom for labels */
        }

        .form-control {
            border-radius: 7px;
            /* Rounded corners for form inputs */
            border: 1px solid #ced4da;
            padding: 0.75rem;
            /* Comfortable padding in form inputs */
        }

        .form-control:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }

        .btn-primary {
            border-radius: 7px;
            /* Rounded corners for primary button */
            padding: 0.8rem 1.5rem;
            /* Comfortable padding for button */
            background-color: #007bff;
            border-color: #007bff;
            transition: background-color 0.3s ease, border-color 0.3s ease;
        }

        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #0056b3;
        }

        .btn-outline-secondary {
            border-radius: 7px;
        }

        .input-group-text {
            background-color: #e9ecef;
            border: 1px solid #ced4da;
            border-radius: 7px 0 0 7px;
            /* Rounded corners on the left side */
        }

        #readOnlyInfo {
            margin-top: 20px;
        }

        #readOnlyInfo .col-md-6 {
            margin-bottom: 10px;
            /* Increased spacing between info items */
        }

        #readOnlyInfo strong {
            font-weight: 600;
            /* Even stronger font for info labels */
        }


        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</body>

</html>

<script>
    function copyJWT() {
        const jwtInput = document.querySelector('input[value="<?php echo $_SESSION["jwt"]; ?>"]');
        jwtInput.select();
        document.execCommand('copy');
        Toastify({
            text: "JWT Token copied!",
            duration: 3000,
            gravity: "top",
            position: "right",
            backgroundColor: "#28a745"
        }).showToast();
    }
</script>