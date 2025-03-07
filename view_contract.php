<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include_once 'connection.php';

function sanitizeInput($data)
{
    if ($data === null) {
        return '';
    }
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

function fetchContract($conn, $id)
{
    $stmt = $conn->prepare("SELECT * FROM kontrata_gjenerale WHERE id = ?");
    if (!$stmt) {
        throw new Exception("Prepare statement failed: " . $conn->error);
    }
    $stmt->bind_param("i", $id);
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    $result = $stmt->get_result();
    $contract = $result->fetch_assoc();
    $stmt->close();
    return $contract;
}

function updateSignature($conn, $id, $signatureData)
{
    if (!file_exists('signatures/')) {
        if (!mkdir('signatures/', 0755, true)) {
            throw new Exception("Failed to create signatures directory.");
        }
    }
    $decodedData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $signatureData));
    if ($decodedData === false) {
        throw new Exception("Invalid signature data.");
    }
    $fileName = 'signatures/signature_' . $id . '_' . time() . '.png';
    if (file_put_contents($fileName, $decodedData) === false) {
        throw new Exception("Failed to save signature image.");
    }
    $stmt = $conn->prepare("UPDATE kontrata_gjenerale SET nenshkrimi = ? WHERE id = ?");
    if (!$stmt) {
        throw new Exception("Prepare statement failed: " . $conn->error);
    }
    $stmt->bind_param("si", $fileName, $id);
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    $stmt->close();
    return true;
}

$id = isset($_GET['id']) ? intval($_GET['id']) : null;
$contract = null;
$errors = [];
$successMessage = "";
try {
    if (!$id) {
        throw new Exception("ID nuk është caktuar!");
    }
    $contract = fetchContract($conn, $id);
    if (!$contract) {
        throw new Exception("Nuk u gjet asnjë rresht me këtë ID!");
    }
    $artisti = json_decode($contract['artisti'], true);
    if (!is_array($artisti)) {
        $artisti = [];
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['signatureData'])) {
        $signatureData = sanitizeInput($_POST['signatureData']);
        if ($signatureData) {
            updateSignature($conn, $id, $signatureData);
            $successMessage = "Nënshkrimi u azhurnua me sukses!";
            $contract = fetchContract($conn, $id);
            $artisti = json_decode($contract['artisti'], true);
            if (!is_array($artisti)) {
                $artisti = [];
            }
        } else {
            throw new Exception("Nënshkrimi është bosh!");
        }
    }
} catch (Exception $e) {
    $errors[] = $e->getMessage();
}
?>
<!doctype html>
<html lang="en">

<head>
    <title>Kontrata Gjenerale për Klient</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/6.3.0/mdb.min.css" rel="stylesheet">
    <link rel="shortcut icon" href="images/favicon.png" />
    <style>
        * {
            font-family: 'Georgia', serif;
            font-size: 13px;
        }

        .modal-backdrop.show {
            background-color: rgba(0, 0, 0, 0.5);
            -webkit-backdrop-filter: blur(25px);
            backdrop-filter: blur(25px);
        }

        .modal-backdrop {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }

        .contract-container {
            background: #ffffff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
            border-radius: 10px;
            border: 1px solid #dcdcdc;
            max-width: 800px;
            margin: auto;
        }

        .contract-header {
            text-align: center;
            margin-bottom: 15px;
        }

        .contract-header img {
            width: 80px;
            margin-bottom: 5px;
        }

        .contract-title {
            font-size: 20px;
            font-weight: bold;
            color: #2c3e50;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 5px;
        }

        .contract-section {
            margin-bottom: 15px;
        }

        .contract-section p {
            line-height: 1.5;
            color: #34495e;
            margin-bottom: 8px;
        }

        .contract-section p strong {
            color: #2c3e50;
        }

        .signature-section img {
            width: 150px;
            height: auto;
        }

        .form-section {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 8px;
            box-shadow: inset 0 0 10px rgba(0, 0, 0, 0.05);
            margin-top: 15px;
        }

        .form-section h5 {
            margin-bottom: 15px;
            color: #2c3e50;
            font-weight: bold;
            font-size: 16px;
        }

        .alert-success,
        .alert-danger,
        .alert-info {
            max-width: 500px;
            margin: 10px auto;
            font-size: 13px;
        }

        .signature-section.row {
            margin-top: 20px;
        }

        .signature-section .col-md-6 {
            margin-bottom: 15px;
        }

        .signature-section .border-bottom {
            border-bottom: 1px solid #ccc;
            padding-bottom: 3px;
        }

        .btn-download {
            display: inline-block;
            padding: 6px 12px;
            font-size: 13px;
            border-radius: 4px;
            background-color: #2c3e50;
            color: #ffffff;
            text-decoration: none;
        }

        .btn-download:hover {
            background-color: #1a252f;
        }

        .print-overlay {
            display: none;
        }

        @media print {
            .no-print {
                display: none !important;
            }

            .print-overlay {
                display: block;
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                z-index: -1;
            }

            .print-overlay img {
                width: 100%;
                height: 40%;
                object-fit: cover;
                opacity: 0.1;
                position: absolute;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%) rotate(45deg);
                z-index: -1;
            }

            .contract-container {
                box-shadow: none;
                border: none;
                padding: 15px;
                max-width: 100%;
                background: transparent;
            }

            body {
                font-size: 14px;
            }
        }

        #blurOverlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1040;
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }
    </style>
</head>

<body>
    <?php if (empty($contract['nenshkrimi'])): ?>
        <div id="blurOverlay"></div>
        <div class="print-overlay">
            <img src="background-overlay.png" alt="Background Overlay">
        </div>
        <?php if ($successMessage): ?>
            <div class="alert alert-success text-center no-print" role="alert"><?php echo $successMessage; ?></div>
        <?php endif; ?>
        <?php if (!empty($errors)): ?>
            <div class="container my-3">
                <?php foreach ($errors as $error): ?>
                    <div class="alert alert-danger no-print" role="alert"><?php echo $error; ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <?php if ($contract): ?>
            <div class="contract-container">
                <?php
                $youtube_id = sanitizeInput($contract['youtube_id'] ?? '');
                $sql  = "SELECT lloji_klientit, numri_unik_i_biznesit, emribiz FROM klientet WHERE youtube = ?";
                $stmt = mysqli_prepare($conn, $sql);
                if ($stmt) {
                    mysqli_stmt_bind_param($stmt, "s", $youtube_id);
                    mysqli_stmt_execute($stmt);
                    mysqli_stmt_bind_result($stmt, $lloji_klientit, $numri_unik_i_biznesit, $emribiz);
                    mysqli_stmt_fetch($stmt);
                    mysqli_stmt_close($stmt);
                } else {
                    $lloji_klientit = null;
                    $numri_unik_i_biznesit = null;
                    $emribiz = null;
                }
                $labelArtistEn = "ARTIST";
                $labelArtistSq = "ARTISTI";
                $labelCitizenEn = "a citizen of";
                $labelCitizenSq = "qytetar i";
                $labelIDEn = "with personal identification number";
                $labelIDSq = "me numër personal identifikimi";
                if (strtolower($lloji_klientit) === 'biznes') {
                    $labelArtistEn = "CLIENT";
                    $labelArtistSq = "KLIENTI";
                    $labelCitizenEn = "Business entity from";
                    $labelCitizenSq = "Biznesi";
                    $labelIDEn = "with unique business number";
                    $labelIDSq = "me numër unik të biznesit";
                }
                $idValue = $contract['numri_personal'] ?? '';
                if (strtolower($lloji_klientit) === 'biznes') {
                    $idValue = $numri_unik_i_biznesit ?? '';
                    $emribiz = $emribiz ?? '';
                }
                if (empty($lloji_klientit)) {
                    $lloji_klientit = 'Unknown';
                }
                ?>
                <div class="contract-header">
                    <img src="images/brand-icon.png" alt="Brand Icon" class="img-fluid">
                    <div class="contract-title">CONTRACT ON COOPERATION / KONTRATË BASHKPUNIMI</div>
                    <span class="badge bg-<?php echo strtolower($lloji_klientit) == 'personal' ? 'primary' : (strtolower($lloji_klientit) == 'biznes' ? 'success' : 'secondary'); ?>">
                        <?php echo htmlspecialchars($lloji_klientit); ?>
                    </span>
                </div>
                <div class="contract-section">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p class="fw-bold">No. Nr.: <?php echo sanitizeInput($contract['id_kontrates']); ?></p>
                        </div>
                    </div>
                    <hr style="border-top: 1px solid red;">
                    <div class="row mb-3">
                        <div class="col">
                            <p>
                                This document specifies the terms and conditions of the agreement between
                                <strong>Baresha Music SH.P.K</strong>, located at Rr. Brigada 123 nr. 23 in Suharekë,
                                represented by <strong>AFRIM KOLGECI, CEO-FOUNDER of Baresha Music</strong>, and
                                <strong><?php echo $labelArtistEn; ?>: <?php echo sanitizeInput($contract['emri'] ?? ''); ?>, Business Name: <?php echo sanitizeInput($emribiz); ?></strong>,
                                <?php echo $labelCitizenEn; ?> <strong><?php echo sanitizeInput($contract['shteti'] ?? ''); ?></strong>,
                                <?php echo $labelIDEn; ?> <strong><?php echo sanitizeInput($idValue); ?></strong>.
                                <?php echo sanitizeInput($artisti['emriart'] ?? ''); ?> will represent themselves via their YouTube channel identified by YouTube ID: <strong><?php echo sanitizeInput($contract['youtube_id'] ?? ''); ?></strong>.
                            </p>
                            <p>The terms and conditions outlined in this contract pertain to the contractual relationship as a whole between the two parties.</p>
                        </div>
                        <div class="col">
                            <p><strong>Shqip. :</strong></p>
                            <p>
                                Ky dokument specifikon kushtet dhe kushtëzimet e marrëveshjes midis
                                <strong>Baresha Music SH.P.K</strong>, me adresë Rr. Brigada 123 nr. 23 në Suharekë,
                                e përfaqësuar nga <strong>AFRIM KOLGECI, CEO-FOUNDER i Baresha Music</strong>, dhe
                                <strong><?php echo $labelArtistSq; ?>: <?php echo sanitizeInput($contract['emri'] ?? ''); ?>, Emri i biznesit: <?php echo sanitizeInput($emribiz); ?></strong>,
                                <?php echo $labelCitizenSq; ?> <strong><?php echo sanitizeInput($contract['shteti'] ?? ''); ?></strong>,
                                <?php echo $labelIDSq; ?> <strong><?php echo sanitizeInput($idValue); ?></strong>.
                                <?php echo sanitizeInput($artisti['emriart'] ?? ''); ?> do të përfaqësohet nga ana e tyre përmes kanalit të tyre në YouTube me YouTube ID: <strong><?php echo sanitizeInput($contract['youtube_id'] ?? ''); ?></strong>.
                            </p>
                            <p>Kushtet dhe kushtëzimet e përcaktuara në këtë kontratë lidhen me marrëdhënien kontraktuale në tërësi midis dy palëve.</p>
                        </div>
                    </div>
                </div>
                <?php $showModal = empty($contract['nenshkrimi']) ? 'true' : 'false'; ?>
                <div class="contract-section">
                    <?php
                    $youtube_id = sanitizeInput($contract['youtube_id'] ?? '');
                    echo "<p><strong>YouTube ID:</strong> $youtube_id</p>";
                    $sql_fetch_platforms_precentage = "SELECT perqindja2 as percentage_of_platforms FROM klientet WHERE youtube = '$youtube_id'";
                    $result = $conn->query($sql_fetch_platforms_precentage);
                    while ($row = $result->fetch_assoc()) {
                        $percentage_of_platforms = $row['percentage_of_platforms'];
                    }
                    // Example articles array – customize as needed.
                    $articles = [
                        [
                            'title_eng' => 'ARTICLE 1 – DEFINITIONS',
                            'title_alb' => 'NENI 1 – DEFINICIONET',
                            'content' => [
                                ['text' => '<strong>1.1. Artist - Copyright Owner</strong> – refers to a person that authorizes Baresha Music SH.P.K.'],
                                ['lang' => 'alb', 'text' => '<strong>1.1. Artisti - Pronari i të Drejtave</strong> - përfaqëson një person që autorizon Baresha Music SH.P.K.']
                            ]
                        ],
                        // Add further articles as needed...
                    ];
                    foreach ($articles as $article) {
                        echo "<h5>{$article['title_eng']} / {$article['title_alb']}</h5>";
                        foreach ($article['content'] as $content) {
                            echo "<p>{$content['text']}</p>";
                            if (isset($content['list']) && is_array($content['list'])) {
                                echo "<ul>";
                                foreach ($content['list'] as $item) {
                                    echo "<li>" . sanitizeInput($item) . "</li>";
                                }
                                echo "</ul>";
                            }
                        }
                        echo "<hr style='border-top: 1px solid red;'>";
                    }
                    ?>
                </div>
                <div class="signature-section row mt-5">
                    <div class="col-md-4 text-center">
                        <p><strong>Baresha Music SH.P.K.</strong></p>
                        <p><strong>Nënshkrimi:</strong></p>
                        <p class="border-bottom">
                            <?php
                            if (isset($contract['id_regjistruesit']) && $contract['id_regjistruesit'] == '107320225270391798116') {
                                $bm_signature = 'MINIRE-Signature.png';
                                if (file_exists($bm_signature)) {
                                    echo '<img src="' . htmlspecialchars($bm_signature) . '" alt="Baresha Signature" style="max-width: 200px;">';
                                }
                            } else {
                                echo '<br>';
                            }
                            ?>
                        </p>
                    </div>
                    <div class="col-md-4 text-center d-flex flex-column justify-content-center align-items-center">
                        <p></p>
                        <p>
                            <?php
                            $company_stamp = 'images/vula.png';
                            if (file_exists($company_stamp)) {
                                echo '<img src="' . sanitizeInput($company_stamp) . '" alt="Company Stamp" style="max-width: 150px;">';
                            } else {
                                echo '<br>';
                            }
                            ?>
                        </p>
                    </div>
                    <div class="col-md-4 text-center">
                        <p><strong>ARTISTI/Artisti</strong></p>
                        <p><strong>Nënshkrimi:</strong></p>
                        <p class="border-bottom">
                            <?php
                            $artistSignature = sanitizeInput($contract['nenshkrimi']);
                            if ($artistSignature && file_exists($artistSignature)) {
                                echo '<img src="' . $artistSignature . '" alt="Artisti Signature" style="max-width: 200px;">';
                            } else {
                                echo '<br>';
                            }
                            ?>
                        </p>
                    </div>
                </div>
                <div class="contract-section mt-4">
                    <p><strong>Data e nënshkrimit të marrëveshjes / Date of Signing:</strong> <?php echo $contract['data_e_nenshkrimit'] ?? date('m/d/Y');  ?></p>
                    <?php if (!empty(trim($contract['shenim'] ?? ''))): ?>
                        <div class="my-5 border rounded py-3 bg-light">
                            <h5>Shënime / Notes</h5>
                            <p><?php echo nl2br(sanitizeInput($contract['shenim'])); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="form-section mt-4 no-print">
                    <h5>Vendosni Nënshkrimin / Add Your Signature</h5>
                    <form method="POST" enctype="multipart/form-data" id="signatureForm">
                        <div class="mb-3">
                            <canvas id="signature" width="400" height="200" class="border rounded"></canvas>
                            <input type="hidden" name="signatureData" id="signatureData">
                        </div>
                        <button type="submit" class="btn btn-primary me-2" id="submitSignature" disabled>
                            <i class="fas fa-paper-plane"></i> Dërgo / Submit
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="clearSignaturePad()">
                            <i class="fas fa-sync-alt"></i> Fshij / Clear
                        </button>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <div class="container my-5">
                <div class="card p-5 text-center">
                    <img src="images/icons8-query-94.png" alt="Error" width="120">
                    <h3 class="mt-3">Nuk u gjet kontrata!</h3>
                    <p>Ju lutem kontrolloni ID-në ose kontaktoni administratën për më shumë informacion.</p>
                </div>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <div class="alert alert-info text-center mt-4 no-print" role="alert">
            Kontrata është tashmë nënshkruar / The contract has already been signed.
        </div>
    <?php endif; ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/signature_pad/1.5.3/signature_pad.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var canvas = document.getElementById('signature');
            var signaturePad = new SignaturePad(canvas);
            const verifyButton = document.getElementById('verifyButton');
            const numriPersonalInput = document.getElementById('numriPersonalInput');
            const errorMessage = document.getElementById('errorMessage');
            const signatureForm = document.getElementById('signatureForm');
            const submitSignature = document.getElementById('submitSignature');
            const numriPersonal = "<?php echo sanitizeInput($contract['numri_personal'] ?? ''); ?>";
            const showModal = <?php echo empty($contract['nenshkrimi']) ? 'true' : 'false'; ?>;
            const verifyModal = new bootstrap.Modal(document.getElementById('verifyModal'), {
                backdrop: 'static',
                keyboard: false
            });
            const blurOverlay = document.getElementById('blurOverlay');
            const modalElement = document.getElementById('verifyModal');
            modalElement.addEventListener('show.bs.modal', function() {
                blurOverlay.style.display = 'block';
            });
            modalElement.addEventListener('hide.bs.modal', function() {
                blurOverlay.style.display = 'none';
            });

            function getContractIdFromUrl() {
                const urlParams = new URLSearchParams(window.location.search);
                return urlParams.get('id');
            }
            const currentContractId = getContractIdFromUrl();

            function isUserVerified() {
                const verificationTimestamp = localStorage.getItem('verificationTimestamp');
                const verifiedContractId = localStorage.getItem('verifiedContractId');
                if (!verifiedContractId || verifiedContractId !== currentContractId) {
                    return false;
                }
                if (verificationTimestamp) {
                    const currentTime = Date.now();
                    const timeDifference = currentTime - verificationTimestamp;
                    if (timeDifference < 3600000) {
                        return true;
                    } else {
                        localStorage.removeItem('verificationTimestamp');
                        localStorage.removeItem('verifiedContractId');
                        return false;
                    }
                }
                return false;
            }
            if (showModal && !isUserVerified()) {
                verifyModal.show();
            } else {
                submitSignature.disabled = false;
            }
            verifyButton.addEventListener('click', function() {
                const enteredNumri = numriPersonalInput.value.trim();
                if (enteredNumri === numriPersonal) {
                    errorMessage.style.display = 'none';
                    verifyModal.hide();
                    submitSignature.disabled = false;
                    document.getElementById('verificationForm').querySelectorAll('input, button').forEach(elem => {
                        elem.disabled = true;
                    });
                    localStorage.setItem('verificationTimestamp', Date.now());
                    localStorage.setItem('verifiedContractId', currentContractId);
                } else {
                    errorMessage.style.display = 'block';
                }
            });
            signatureForm.addEventListener('submit', function(event) {
                if (signaturePad.isEmpty()) {
                    alert("Ju lutem nënshkruani kontratën para se ta dërgoni / Please add your signature before submitting.");
                    event.preventDefault();
                } else {
                    const signatureData = signaturePad.toDataURL('image/png');
                    document.getElementById('signatureData').value = signatureData;
                }
            });
        });

        function clearSignaturePad() {
            var canvas = document.getElementById('signature');
            var signaturePad = new SignaturePad(canvas);
            signaturePad.clear();
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/6.3.0/mdb.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
</body>

</html>