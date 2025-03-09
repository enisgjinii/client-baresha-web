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
                    $articles = [
                        [
                            'title_eng' => 'ARTICLE 1 – DEFINITIONS',
                            'title_alb' => 'NENI 1 – DEFINICIONET',
                            'content' => [
                                [
                                    'text' => '<strong>1.1. Artist - Copyright Owner</strong> – refers to a natural or legal person that represents himself or a group or a band, that authorizes Baresha Music SH.P.K.',
                                ],
                                [
                                    'lang' => 'alb',
                                    'text' => '<strong>1.1. Artisti - Pronari i të Drejtave</strong> - përfaqëson një person fizik ose juridik që përfaqëson veten, një grup ose një bandë, që autorizon Baresha Music SH.P.K.',
                                ],
                                [
                                    'text' => '<strong>1.2. Baresha Music SH.P.K</strong> - Copyright User - refers to the company that holds exclusive rights to distribute, sell, and publish audio and video masters on YouTube platforms and digital stores under the terms of this contract.',
                                ],
                                [
                                    'lang' => 'alb',
                                    'text' => '<strong>1.2. Baresha Music SH.P.K</strong> - Përdoruesi i të Drejtave - përfaqëson kompaninë që mbart të drejta ekskluzive për shpërndarjen, shitjen dhe publikimin e masterit audio dhe video në platformat e YouTube dhe dyqanet dixhitale nën këtë kontratë siç cekët në nenin 1.3.',
                                ],
                                [
                                    'text' => '<strong>1.3. Digital Stores – Shitore Dixhitale</strong>',
                                    'list' => ['Spotify', 'Apple Music', 'YouTube Music', 'Deezer', 'Amazon Music', 'Facebook', 'Instagram', 'Tik Tok', 'Etc – Etj']
                                ],
                            ]
                        ],
                        [
                            'title_eng' => 'ARTICLE 2 - OBJECT OF THE CONTRACT',
                            'title_alb' => 'NENI 2 – OBJEKTI I KONTRATES',
                            'content' => [
                                [
                                    'text' => '<strong>2.1.</strong> The copyright owner hereby GRANTS, namely awards EXCLUSIVE RIGHTS to Baresha Music SH.P.K (The Copyright User), for the distribution, sale and publication of audio materials and video masters on the artist\'s channel on YouTube and digital stores, as well as for the use of the artist\'s name, logo, photographs and biography on the artist\'s channel on YouTube and in all digital stores.',
                                ],
                                [
                                    'lang' => 'alb',
                                    'text' => '<strong>2.1.</strong> Pronari i të drejtave këtu AUTORIZON, që do të thotë i jep të drejtat ekskluzive për Baresha Music SH.P.K (Përdoruesi i të Drejtave), për shpërndarjen, shitjen dhe publikimin e materialeve dhe masterit audio dhe video në kanalin e Artistit në YouTube dhe dyqanet dixhitale, si dhe për përdorimin e emrit, logos, fotografive dhe biografisë së Artistit në kanalin e Artistit në YouTube dhe në të gjitha dyqanet dixhitale.',
                                ],
                            ]
                        ],
                        [
                            'title_eng' => 'ARTICLE 3 – THE RIGHT OF USE',
                            'title_alb' => 'NENI 3 – TË DREJTAT E PERDORIMIT',
                            'content' => [
                                [
                                    'text' => '<strong>3.1.</strong> <strong>The Artist (Copyright Owner)</strong> will grant authorization for their <strong> previous and future works </strong> potentially uploaded on their YouTube channel to <strong> Baresha Music SH.P.K (Copyright User) </strong>, under a special contract. This contract will provide the Copyright User with the right to use the works without any fixed duration. In the event of the termination of the Cooperation Contract, <strong>Baresha Music SH.P.K (Copyright User)</strong> shall return all rights to the Artist (Copyright Owner) within 30 days of the termination of the Cooperation Contract.',
                                ],
                                [
                                    'lang' => 'alb',
                                    'text' => '<strong>3.1.</strong> Artisti (Pronari i të Drejtave) do të autorizojë <strong>veprat e tij të mëparshme dhe të ardhshme</strong>, të ngarkuara potencialisht në kanalin e tij në YouTube, në dispozicion të <strong>Baresha Music SH.P.K (Përdoruesi i të Drejtave)</strong>, nën një kontratë të veçantë. Kontrata e të Drejtave do t\'i japë Përdoruesit të Copyright-it të drejtën për të përdorur veprat pa një kohëzgjatje të caktuar. Në rast të ndërprerjes së Kontratës së Bashkëpunimit, <strong>Baresha Music SH.P.K (Përdoruesi i Copyright-it)</strong> do t\'i kthejë të gjitha të drejtat Artistit (Pronarit të Drejtave) brenda 30 ditëve nga ndërprerja e Kontratës së Bashkëpunimit.',
                                ],
                                [
                                    'text' => '<strong>3.2.</strong> Through the execution of this contract, the Artist hereby grants authorization to Baresha Music SH.P.K to utilize all of their channel videos for promotional purposes pertaining to other clients of Baresha Music SH.P.K through the use of "End Screens" and "Cards".',
                                ],
                                [
                                    'lang' => 'alb',
                                    'text' => '<strong>3.2.</strong> Nëpërmjet nënshkrimit të kësaj kontrate, Artisti autorizon Baresha Music SH.P.K për të përdorur të gjitha videot në kanalin e tij për promovimin e klientëve të tjerë në Baresha Music SH.P.K duke përdorur "End Screens" dhe "Cards".',
                                ],
                                [
                                    'text' => '<strong>3.3.</strong> By signing this contract, the Artist acknowledges that they have read and understood these rules and accepts responsibility for complying with this article and the contract as a whole. The Artist may only use purchased instrumentals that come with a license. For each publication, the Artist is required to provide the license if the beat is sourced from YouTube or the Internet. The Artist may also use melody lines, instrumentals, videos and lyrics that have been authorized by their respective authors, producers and songwriters. The use of any material that is not licensed or authorized is strictly prohibited on the Artist’s channel:',
                                    'list' => [
                                        'It is not allowed and it is strictly forbidden to publish songs that contain YouTube “Free” beats or “Free” beats anywhere on the internet.',
                                        'It is strictly forbidden and not permitted to use the Instrumental: Beats, Melodies, or Lyrics which are from the Internet or YouTube, but that the Artist does not have the authorship/authorization of the authors, producers or songwriters.',
                                        '“Covers” or “Remix” are strictly prohibited to be published if the Artist doesn’t have the direct authorization from the (Artist, Artists, Authors, Producers) of the song recording and composition.',
                                        'Music projects that the Artist owns and/or are located on his channel, in which they include either “Free” beat or beat without a license, then the Artist must delete projects or create a New Channel if he want to collaborate with Baresha Music SH.P.K.'
                                    ]
                                ],
                                [
                                    'lang' => 'alb',
                                    'text' => '<strong>3.3.</strong> Me nënshkrimin e kësaj kontrate, Artisti pranon se i ka lexuar dhe kuptuar këto rregulla dhe pranon përgjegjësinë për respektimin e këtij neni dhe kontratës në tërësi. Artisti mund të përdorë vetëm instrumente të blera që vijnë me licencë. Për çdo publikim, Artistit i kërkohet të japë licencën nëse beat e ka burimin nga YouTube ose interneti. Artisti mund të përdorë gjithashtu vija melodike, instrumente, video dhe tekste që janë autorizuar nga autorët, producentët dhe kompozitorët e tyre përkatës. Përdorimi i çdo materiali që nuk është i licencuar ose i autorizuar është rreptësisht i ndaluar në kanalin e Artistit:',
                                    'list' => [
                                        'Nuk lejohet dhe ndalohet rreptësisht publikimi i këngëve që përmbajnë beat “Free” ose “Free” të YouTube apo kudo në internet.',
                                        'Ndalohet rreptësisht dhe nuk lejohet përdorimi i instrumenteve: Beats, Melodi, apo Tekste që janë nga Interneti apo YouTube, por që Artisti nuk ka autorësinë/autorizimin e artistit, producentëve apo, tekst shkruesëve.',
                                        'Projektet muzikore që Artisti zotëron dhe/ose ndodhen në kanalin e tij, në të cilat përfshijnë beat "Falas" ose beat pa licencë, atëherë Artisti duhet të fshijë projektet ose të krijojë një Kanal të Ri nëse dëshiron të bashkëpunojë me Baresha Music SH.P.K.',
                                    ]
                                ],
                                [
                                    'text' => '<strong>3.4.</strong> The artist hereby affirms that they have carefully reviewed the fundamental regulations and guidelines presented herein, and by affixing their signature to this agreement, they confirm their acknowledgement and agreement to abide by these terms:',
                                    'list' => [
                                        '<strong>3.4.1</strong> Will not alter or manipulate any aspect related to YouTube, and explicitly declare that I will refrain from modifying, editing or creating content for the "Tags" section, "Metadata," "Description," "Hashtags," "Channel Tags," or "Thumbnail" of any material. The use of names belonging to other artists or trademarks without the written authorization of the respective owner is strictly prohibited. Non-compliance with this rule by the ARTIST may result in legal liability for any potential damages incurred, including financial compensation.',
                                        '<strong>3.4.2</strong> The ARTIST shall refrain from uploading any content to YouTube that includes any material, including but not limited to audio, video, instrumental, melody, text, photo, image, person, logo, or trademark that is not their own and for which they lack written authorization from the respective owner. Failure to comply with this rule may result in legal liability for any potential damages incurred, including financial compensation.',
                                        '<strong>3.4.3</strong> The ARTIST is prohibited from removing any Baresha Music representative from their designated role as "Owner," "Manager," or "Editor" without executing a contract. In the event of such an occurrence, the ARTIST must restore the removed individual(s) within 48 hours. Failure to do so will result in a daily penalty of 20 Euros for each day beyond the two-day limit until the roles of "Owner," "Manager," or "Editor" are returned to the Baresha Music representatives.'
                                    ]
                                ],
                                [
                                    'lang' => 'alb',
                                    'text' => '<strong>3.4.</strong> Artisti me këtë deklaron se ka shqyrtuar me kujdes rregulloret dhe udhëzimet themelore të paraqitura në këtë marrëveshje, dhe duke vendosur nënshkrimin e tyre në këtë marrëveshje, ata konfirmojnë pranimin dhe pajtimin e tyre për të respektuar kushtet e saj siq jan me poshtë.',
                                    'list' => [
                                        '<strong>3.4.1</strong> Nuk do të ndryshoj ose manipuloj asnjë aspekt të lidhur me YouTube-n, dhe deklaron me qartësi se do të ndaloj të modifikoj, redaktoj ose krijoj përmbajtje për "Tags", "Metadata", "Description", "Hashtags", "Channel Tags" ose "Thumbnail" të çdo materiali. Përdorimi i emrave që i takojnë artistëve tjerë ose markave të regjistruara pa autorizimin me shkrim të pronarit të tyre është kategorikisht i ndaluar. Mosrespektimi i kësaj rregulle nga ARTISTI mund të çojë në përgjegjësi ligjore për çdo dëm potencial që mund të shkaktohet, duke përfshirë dëmshpërblim financiar.',
                                        '<strong>3.4.2</strong> ARTISTI duhet të ndalojë ngarkimin e çdo lloj përmbajtjeje në YouTube që përfshin çdo material, duke përfshirë por jo duke u kufizuar me audio, video, instrumentale, melodi, tekst, foto, imazh, person, logo ose markë tregtare që nuk i takojnë atij dhe për të cilët ai nuk ka autorizim me shkrim nga pronari i tyre. Mosrespektimi i kësaj rregulle mund të çojë në përgjegjësi ligjore për çdo dëm potencial që mund të shkaktohet, duke përfshirë dëmshpërblim financiar.',
                                        '<strong>3.4.3</strong> ARTISTI është i ndaluar të heqë nga roli i caktuar si "Owner", "Manager" ose "Editor" i caktuar nga Baresha Music pa nënshkrimin e një kontrate. Në rast se ndodh një ngjarje e tillë, ARTISTI duhet të rikthejë personat e hequr brenda 48 orëve. Mosrespektimi i kësaj rregulle do të rezultojë në një gjobë ditore prej 20 Euro për çdo ditë pas limitit të dy ditëve deri në momentin që rolet e "Owner", "Manager" ose "Editor" kthehen te përfaqësuesit e Baresha Music.',
                                    ],
                                ],
                                [
                                    'text' => '<strong>3.5.</strong> Baresha Music SH.P.K further reserves the right to terminate the contract at any time if the ARTIST\'s actions on their YouTube channel endanger Baresha Music\'s operations, such as receiving unresolved Copyright Strikes or engaging in any activity that violates YouTube\'s rules, terms, and conditions. In the event of such termination, Baresha Music SH.P.K is obligated to liquidate any outstanding payments and release all clients\' audio-visual materials from the use of Baresha Music SH.P.K within a period of four months.',
                                ],
                                [
                                    'lang' => 'alb',
                                    'text' => '<strong>3.5.</strong> Baresha Music SH.P.K gjithashtu rezervon të drejtën për të ndërprerë kontratën në çdo kohë nëse veprimet e ARTIST\'s në kanalin e tyre në YouTube vështirësojnë veprimtarinë e Baresha Music (për shembull duke marrë shkelje të drejtave të autorit pa u zgjidhur, apo duke bërë çdo veprim që shkel rregullat, kushtet dhe kushtetutat e YouTube). Në rast se ky rast ndodh, Baresha Music SH.P.K është i detyruar që në një periudhë prej katër muajsh të likuidojë çdo pagesë që nuk është realizuar dhe të lirojë të gjithë materialet audiovizive të klientëve nga përdorimi i Baresha Music SH.P.K.',
                                ],
                            ]
                        ],
                        [
                            'title_eng' => 'ARTICLE 4 – RIGHTS AND OBLIGATIONS OF THE COPYRIGHT USER – BARESHA MUSIC SH.P.K',
                            'title_alb' => 'ARTICLE 4 – TË DREJTAT DHE OBLIGIMET E PËRDORUESIT E TË DREJTAVE AUTORIALE – BARESHA MUSIC SH.P.K',
                            'content' => [
                                [
                                    'text' => '<strong>4.1.</strong> Baresha Music SH.P.K shall hold the exclusive right to distribute the audio and video content of the Artist on the Artist\'s YouTube channel as well as on all digital platforms.',
                                ],
                                [
                                    'lang' => 'alb',
                                    'text' => '<strong>4.1.</strong> Baresha Music SH.P.K do të mbajë të drejtën ekskluzive për të shpërndarë përmbajtjen audio dhe video të Artistit në kanalin e tij të YouTube-s si dhe në të gjitha platformat dixhitale.',
                                ],
                                [
                                    'text' => '<strong>4.2.</strong> Baresha Music SH.P.K is authorized to distribute the materials provided by the artist on YouTube and other digital platforms, in accordance with the terms and conditions outlined in this contractual agreement.',
                                ],
                                [
                                    'lang' => 'alb',
                                    'text' => '<strong>4.2.</strong> Baresha Music SH.P.K do të shpërndajë materialet e dërguara nga artisti në YouTube dhe dyqanet dixhitale, siç është specifikuar në këtë kontratë.',
                                ],
                                [
                                    'text' => '<strong>4.3.</strong> With the objective of advancing the promotion of the Artist\'s materials, Baresha Music SH.P.K. shall employ various strategies including "Cross-Promotion", "Tag Promotion", "Thumbnail Optimization", and any other available means at the company\'s disposal to achieve this objective.',
                                ],
                                [
                                    'lang' => 'alb',
                                    'text' => '<strong>4.3.</strong> Me qëllim të promovimit të materialeve të artistit, Baresha Music SH.P.K do të përdorë strategjitë e ndryshme si "Cross-Promotion", "Tag Promotion", "Thumbnail Optimization" dhe çdo formë tjetër në dispozicion të kompanisë Baresha Music SH.P.K. për të arritur këtë qëllim.',
                                ],
                                [
                                    'text' => '<strong>4.4.</strong> Baresha Music SH.P.K will provide a maximum of one (1) video per "Instagram Story" and one (1) video for the "Instagram Feed" for each artist or performer featured in a song. These provisions are subject to the regulations outlined in Article Sections 6.1 of this contract.',
                                ],
                                [
                                    'lang' => 'alb',
                                    'text' => '<strong>4.4.</strong> Baresha Music SH.P.K do t\'i dërgojë Artistit 1 (një) video për "Instagram Story" maksimumi dhe 1 (një) video për "Instagram Feed" për secilin Artist në një këngë, me rregullat e lart cekur të nenit 3.3.',
                                ],
                                [
                                    'text' => '<strong>4.5.1.</strong> For YouTube - To promote their song, Baresha Music SH.P.K may display advertisements in the form of banners or advertising clips on or before Copyright Owner\'s works (or albums) on YouTube.',
                                ],
                                [
                                    'lang' => 'alb',
                                    'text' => '<strong>4.5.1.</strong> Për YouTube - Që të promovojnë këngën e tyre, Baresha Music SH.P.K mund të shfaq reklama në formën e banerave ose klipave reklamuese në ose para materialit (ose albumeve) të Pronarit të të Drejtave të Autorit në YouTube.',
                                ],
                                [
                                    'text' => '<strong>4.5.2.</strong> Baresha Music SH.P.K will conduct a thorough review process to detect and block any videos of works uploaded by unauthorized third parties. Alternatively, the company may choose to allow these works and monetize them accordingly.',
                                ],
                                [
                                    'lang' => 'alb',
                                    'text' => '<strong>4.5.2.</strong> Baresha Music SH.P.K do të kryejë një proces të hollësishëm vlerësuese për të zbuluar dhe bllokuar çdo video të materialit të ngarkuara nga palë të treta pa autorizim. Në rast se këto punë lejohen, kompania mund të vendosë t\'i monetizojë ato.',
                                ],
                                [
                                    'text' => '<strong>4.5.3.</strong> Baresha Music SH.P.K is committed to promptly removing any videos of works that are uploaded by unauthorized third parties.',
                                ],
                                [
                                    'lang' => 'alb',
                                    'text' => '<strong>4.5.3.</strong> Baresha Music SH.P.K është e vendosur që të heqë menjëherë çdo video të materialit që ngarkohen nga palë të treta pa autorizim.',
                                ],
                                [
                                    'text' => '<strong>4.5.4.</strong> Baresha Music SH.P.K is committed to safeguarding the reputation of its artists. To this end, the company will remove any links from Google search results that redirect visitors to sites containing false information or that could potentially harm the image of the artist in question.',
                                ],
                                [
                                    'lang' => 'alb',
                                    'text' => '<strong>4.5.4.</strong> Baresha Music SH.P.K është e vendosur që të mbrojë reputacionin e artistëve të saj. Me këtë qëllim, kompania do të heqë çdo lloj linku nga rezultatet e kërkimit në Google që ridrejtojnë vizitorët në faqe të internetit që përmbajnë informacione të rreme ose që mund të dëmtojnë imazhin e artistit në fjalë.',
                                ],
                                [
                                    'text' => '<strong>4.5.5.</strong> Baresha Music SH.P.K is committed to maintaining the integrity of its artists\' image and reputation. To this end, the company will remove any illegal materials published in connection with the artist on YouTube and other "User Generated Content" platforms such as SoundCloud, DailyMotion, etc.',
                                ],
                                [
                                    'lang' => 'alb',
                                    'text' => '<strong>4.5.5.</strong> Baresha Music SH.P.K është e vendosur të mbajë integritetin e imazhit dhe reputacionit të artistëve të saj. Me këtë qëllim, kompania do të heqë çdo material të paligjshëm që publikohet në lidhje me artistin në YouTube dhe platforma të tjera të "Përmbajtjes së Krijuar nga Përdoruesit" si SoundCloud, DailyMotion etj.',
                                ],
                                [
                                    'text' => '<strong>4.6.</strong> Baresha Music SH.P.K is hereby granted authorization by the Artist, who is the Copyright Owner, to exclusively exercise the right to profit from the following:',
                                ],
                                [
                                    'lang' => 'alb',
                                    'text' => '<strong>4.6.</strong> Baresha Music SH.P.K merr autorizimin e nevojshëm nga Artisti, i cili është pronari i të drejtave të autorit, për të ushtruar ekskluzivisht të drejtën për të përfituar nga të ardhurat e mëposhtme:',
                                ],
                                [
                                    'text' => '<strong>4.6.1.</strong> During the term of this contractual agreement, the company shall be authorized to engage in the distribution, publication, and sale of the audio and video content of the Artist on the Artist\'s YouTube channel, as well as on all digital stores.',
                                ],
                                [
                                    'lang' => 'alb',
                                    'text' => '<strong>4.6.1.</strong> Gjatë kohëzgjatjes së kësaj marrëveshje kontraktuale, kompania do të jetë e autorizuar për të angazhuar veten në shpërndarjen, publikimin dhe shitjen e përmbajtjes audio dhe video të Artistit në kanalin YouTube të Artistit, si dhe në të gjitha dyqanet dixhitale.',
                                ],
                                [
                                    'text' => '<strong>4.6.2.</strong> The placement of advertisements, including banners or clips, prior to or during the display of the Artist\'s works or albums, as well as all other materials submitted by the Artist that generate profits, as specified in this agreement, shall be authorized.',
                                ],
                                [
                                    'lang' => 'alb',
                                    'text' => '<strong>4.6.2.</strong> Vendosja e reklamave, duke përfshirë flamuj ose klipet, para ose gjatë shfaqjes së punëve ose albumeve të Artistit, si dhe të gjitha materialet e tjera të paraqitura nga Artisti që gjenerojnë fitime, ashtu siç është specifikuar në këtë marrëveshje, do të autorizohet.',
                                ],
                                [
                                    'text' => '<strong>4.7.</strong> Baresha Music SH.P.K shall pay the artist with a sum of ' . htmlspecialchars($contract['tvsh']) . '.00 % of gross income for YouTube and ' . htmlspecialchars('100' - $percentage_of_platforms) . '.00 % for Digital Store sales.',
                                ],
                                [
                                    'lang' => 'alb',
                                    'text' => '<strong>4.7.</strong> Baresha Music SH.P.K do të paguajë artistin me një shumë prej ' . htmlspecialchars($contract['tvsh']) . '.00 % të të ardhurave bruto për shitjet në YouTube dhe ' . htmlspecialchars(100 - $percentage_of_platforms) . '.00 % për shitjet në shitoret dixhitale.',
                                ],
                                [
                                    'text' => '<strong>4.8.</strong> Baresha Music SH.P.K is obligated to provide timely notification to the Artist every four months, as outlined in the terms of this contractual agreement, for all payments exceeding 100 Euro made on Digital Stores. If this minimum threshold has not been met, the payment shall be deferred to the next payment period until the threshold has been achieved. This same protocol applies for YouTube payments, with payments are made on a monthly basis.',
                                ],
                                [
                                    'lang' => 'alb',
                                    'text' => '<strong>4.8.</strong> Baresha Music SH.P.K është e detyruar të njoftojë Artistin në kohë çdo katër muaj, siç është përcaktuar në kushtet e kësaj marrëveshje kontraktuale, për të gjitha pagesat që tejkalojnë 100 euro dhe janë bërë në Dyqanet Dixhitale. Nëse kjo shumë minimale nuk është arritur, pagesa do të shtyhet për në periudhën e pagesave të ardhshme, derisa kufiri minimal të arrihet. Ky protokoll i njëjtë aplikohet edhe për pagesat e YouTube, ku pagesat bëhen çdo muaj.',
                                ],
                                [
                                    'text' => '<strong>4.9.</strong> In the event that the artist decides to sell their channel to another artist or company, Baresha Music SH.P.K reserves the right to retain 100% of the earnings for that particular month. Furthermore, we request that a contract outlining the terms and conditions of the sale be submitted to us for our review and approval. This is to ensure that the new owner of the channel is aware of their obligations to Baresha Music SH.P.K and that any future earnings are directed to the appropriate party.',
                                ],
                                [
                                    'lang' => 'alb',
                                    'text' => '<strong>4.9.</strong> Në rast se artisti vendos të shesë kanalin e tij një artist tjetër ose një kompanie tjetër, Baresha Music SH.P.K rezervon të drejtën për të mbajtur 100% të ardhurave për atë muaj të caktuar. Në të njëjtën kohë, kërkojmë që një kontratë që përmban kushtet dhe parimet e shitjes të paraqitet për shqyrtim dhe aprovim tek ne. Kjo është për të siguruar që pronari i ri i kanalit është i vetëdijshëm për obligimet e tij ndaj Baresha Music SH.P.K dhe që të ardhurat e ardhshme drejtohen tek pala e duhur.',
                                ],
                                [
                                    'text' => '<strong>4.10.</strong> In the event that the artist does not release any new content within a year, Baresha Music SH.P.K reserves the right to terminate this contract as well as any other existing contracts with the artist. In this case, the channel will also lose monetization.',
                                ],
                                [
                                    'lang' => 'alb',
                                    'text' => '<strong>4.10.</strong> Në rast se artisti nuk publikon asnjë lloj përmbajtjeje të re brenda një viti, Baresha Music SH.P.K rezervon të drejtën për të shfuqizuar këtë kontratë dhe çdo kontratë tjetër me artistin. Në këtë rast, kanali do të humbasë monetizimin.',
                                ],
                            ]
                        ],
                        [
                            'title_eng' => 'ARTICLE 5 – RIGHTS AND OBLIGATIONS OF THE ARTIST',
                            'title_alb' => 'NENI 5 – RIGHTS AND OBLIGATIONS OF THE ARTIST',
                            'content' => [
                                [
                                    'text' => '<strong>5.1.</strong> It is stipulated that during the term of this contract, the artist is prohibited from entering into contractual agreements with any other distribution companies that operate through YouTube and digital shops. This restriction only applies to the artist\'s own YouTube channel, which is identified by its YouTube ID as specified in both this contract and another agreement. In the event that the artist knowingly enters into a new agreement with Baresha Music SH.P.K, and he has a running contract with other distribution companies, any resulting consequences will be the sole responsibility of the artist.',
                                ],
                                [
                                    'lang' => 'alb',
                                    'text' => '<strong>5.1.</strong> Është parashikuar që gjatë kohës së kësaj kontrate, artisti është i ndaluar nga hyrja në marrëveshje kontraktuale me çdo kompani tjetër të shpërndarjes që operon përmes YouTube dhe dyqaneve dixhitale. Kufizimi i kësaj parashtruesje zbatohet vetëm në kanalin e YouTube të artistit, i cili identifikohet me identifikuesin e tij të YouTube siç është specifikuar në këtë kontratë dhe në një marrëveshje tjetër. Në rast se artisti me dije nënshkruan një marrëveshje të re me Baresha Music SH.P.K dhe ka një kontratë aktive me kompani të tjera të shpërndarjes, atëherë çdo pasojë që rezulton është e përgjegjësi e artistit.',
                                ],
                                [
                                    'text' => '<strong>5.2.</strong> During the validity of this contract, the artist is obliged to grant Baresha Music SH.P.K access to his/her YouTube channel. In the event that the artist decides to assume control over his/her YouTube channel and/or opts to revoke Baresha Music SH.P.K\'s access, any potential damage incurred to the YouTube channel or video content shall be borne by the artist. It is important to note that the channel is under joint management with Baresha Music, therefore the artist is expected to comply with the rules outlined in Article 3 of this agreement. Failure to comply may result in the artist being held accountable for any and all resulting consequences.',
                                ],
                                [
                                    'lang' => 'alb',
                                    'text' => '<strong>5.2.</strong> Gjatë vlefshmërisë së kësaj kontrate, artisti është i detyruar të japë akses Baresha Music SH.P.K në kanalin e tij/saj të YouTube. Në rast se artisti vendos të marrë kontrollin e kanalit të tij/saj të YouTube dhe/ose zgjedh të tërheqë aksesin e Baresha Music SH.P.K, çdo dëm potencial që mund të ndodhë në kanalin e YouTube ose përmbajtjen e videove do të mbulohet nga artisti. Është e rëndësishme të theksohet se kanali është në menaxhim të përbashkët me Baresha Music, prandaj pritet që artisti të përmbahet nga rregullat e përmendura në Nenin 3 të kësaj marrëveshje. Në rast se artisti nuk i ndjek këto rregulla, ai/ajo do të jetë i/e përgjegjshëm për çdo pasojë që ndodhin si pasojë e kësaj.',
                                ],
                                [
                                    'text' => '<strong>5.3.</strong> As per the terms of this agreement, Baresha Music SH.P.K is entitled to receive proceeds from the distribution and sale of audio and/or video masters by Baresha Music SH.P.K on YouTube and all other digital platforms.',
                                ],
                                [
                                    'lang' => 'alb',
                                    'text' => '<strong>5.3.</strong> Sipas kushteve të kësaj marrëveshje, artisti ka të drejtë të marrë të ardhurat nga shpërndarja dhe shitja e master audio dhe / ose video nga Baresha Music SH.P.K në YouTube dhe në të gjitha dyqanet dixhitale.',
                                ],
                                [
                                    'text' => '<strong>5.4.</strong> The artist shall be responsible for the publication, distribution, and sale of their audio and video master recordings, exclusively through Baresha Music Sh.P.K, both on their YouTube channel and in various digital stores for the contractual period.',
                                ],
                                [
                                    'lang' => 'alb',
                                    'text' => '<strong>5.4.</strong> Artisti do të publikojë, distribuojë dhe shesë master audio dhe video në kanalin e tij të YouTube dhe në dyqanet dixhitale vetëm përmes Baresha Music Sh.P.K për kohën e kontratës.',
                                ],
                                [
                                    'text' => '<strong>5.5.</strong> The owner of the rights, herein referred to as "the artist," hereby grants full authorization to Baresha Music to post any audio, video, or photographic content on Instagram, Facebook, and any other relevant internet platform. Baresha Music is also authorized to generate profits from the artist\'s content, subject to the profit-sharing agreement outlined in Article Sections 6.1 of this contract. Should the artist terminate this contract, they may request the removal of any previously uploaded audio, video, or photographic content from Baresha Music\'s platform. It is hereby declared that the artist shall not authorize any other company or individual to submit a copyright strike or any similar actions against Baresha Music personally. Any such actions that harm or damage Baresha Music will result in the artist being solely responsible for all damages, including any additional losses incurred.',
                                ],
                                [
                                    'lang' => 'alb',
                                    'text' => '<strong>5.5.</strong> Pronari i të drejtave, në vijim i quajtur "artisti", i jep plotësisht autorizimin Baresha Music për të postuar cilindo lloj përmbajtjeje audio, video ose fotografike në Instagram, Facebook dhe çdo platformë tjetër në internet. Baresha Music gjithashtu është autorizuar për të generuar fitime nga përmbajtja e artistit, në përputhje me marrëveshjen e ndarjes së fitimit të përcaktuar në Seksionet 6.1 të kësaj kontrate. Nëse artisti ndërpren këtë kontratë, atëherë ata mund të kërkojnë largimin e çdo përmbajtjeje audio, video ose fotografike që ka qenë e ngarkuar më parë nga Baresha Music. Deklarohet se artisti nuk do të autorizojë ndonjë kompani ose individ për të dërguar një "Copyright Strike" ose ndonjë veprim të ngjashëm kundër Baresha Music personalisht. Çdo veprim i tillë që dëmton ose dëmton Baresha Music do të rezultojë në faktin që artisti do të jetë i vetmi përgjegjës për të gjitha dëmet, duke përfshirë humbjet shtesë.',
                                ],
                                [
                                    'text' => '<strong>5.6.</strong> The artist hereby declares and warrants that for the duration of this contract, they shall refrain from opening any new YouTube channels or digital stores (including but not limited to Spotify, Apple Music, etc.). The artist further declares that they shall not do so either as an individual or through another company or individual. Furthermore, the artist declares and warrants that they shall not publish any song(s) on another YouTube channel or any other channel on a digital store, whether through another company or individual or themselves, for the duration of this contract. The artist affirms that they shall not enter into any agreement with another company or individual, nor shall they do so themselves, for the purpose of publishing on YouTube or digital stores while this contract is in effect.',
                                ],
                                [
                                    'lang' => 'alb',
                                    'text' => '<strong>5.6.</strong> Artisti deklaron dhe garanton se gjatë kohës që kjo kontratë është në fuqi, ai nuk do të hapë një kanal të ri në YouTube ose dyqane dixhitale (si Spotify, Apple Music, etj.). Artisti deklaron se nuk do ta bëjë këtë as si person dhe as përmes një kompanie tjetër ose një individi tjetër. Në mënyrë të ngjashme, artisti deklaron dhe garanton se nuk do të publikojë një ose më shumë këngë nga një kanal tjetër i YouTube ose ndonjë kanal tjetër në dyqanin dixhital, nëpërmjet një kompanie tjetër ose individi tjetër ose vetë as gjatë kohës që kjo kontratë është në fuqi. Artisti deklaron se nuk do të hyjë në një marrëveshje me një kompani tjetër ose individ për publikime në YouTube dhe në dyqanin dixhital, ndërsa kjo kontratë është në fuqi.',
                                ],
                                [
                                    'text' => '<strong>5.7.</strong> It is mandatory for the artist to provide Baresha Music with their material/video/audio at least 24 hours prior to the intended release date. This will enable Baresha Music to complete the necessary marketing processes to ensure a successful content/release launch.',
                                ],
                                [
                                    'lang' => 'alb',
                                    'text' => '<strong>5.7.</strong> Është detyrë e artistit të dërgojë materialet e tij / video / audio tek Baresha Music, 24 orë para datës së planifikuar të lansimit. Kjo do t\'i japë kohë Baresha Music për të finalizuar procesin e marketingut të përmbajtjes / për të siguruar një lansim të suksesshëm.',
                                ],
                                [
                                    'text' => '<strong>5.8.</strong> It is mandatory for the artist to ensure that their channel does not contain any content that is not their own, and to promptly remove such content to comply with YouTube\'s rules on "Reused Content".',
                                ],
                                [
                                    'lang' => 'alb',
                                    'text' => '<strong>5.8.</strong> Artisti është i detyruar të kontrollojë nëse kanali i tij përmban materiale që nuk janë të tij dhe t\'i largojë ato nga kanali në mënyrë që të përputhen me rregullat e YouTube për "Reused Content".',
                                ],
                            ]
                        ],
                        [
                            'title_eng' => 'ARTICLE 6 – EARNINGS, EXPENDITURES, AND COMISSIONS',
                            'title_alb' => 'NENI 6 – TË HYRAT SHPENZIMET DHE PROVIZIONET',
                            'content' => [
                                [
                                    'text' => 'Revenues generated by the distribution, publication and sale of the Artist’s audio and video master on his YouTube channel and digital stores shall be transferred to the bank account designated by Baresha Music SH.PK',
                                ],
                                [
                                    'lang' => 'alb',
                                    'text' => 'Te ardhurat e gjeneruara nga shpërndarja, publikimi dhe shitja e masterit audio dhe video të artistit në kanalin e tij të YouTube dhe dyqanet dixhitale do të transferohen në llogarinë bankare të caktuar nga Baresha Music SH.P.K.',
                                ],
                                [
                                    'text' => '<strong>6.1.</strong> Baresha Music SH.P.K shall pay the client with a sum of ' . htmlspecialchars($contract['tvsh']) . '.00 % of gross income for YouTube and ' . htmlspecialchars('100' - $percentage_of_platforms) . '.00 % for Digital Store sales.',
                                ],
                                [
                                    'lang' => 'alb',
                                    'text' => '<strong>6.1.</strong> Baresha Music SH.P.K do të paguajë klientin me një shumë prej ' . htmlspecialchars($contract['tvsh']) . '.00 % të të ardhurave bruto për shitjet në YouTube dhe ' . htmlspecialchars(100 - $percentage_of_platforms) . '.00 % për shitjet në shitoret dixhitale.',
                                ],
                                [
                                    'text' => '<strong>6.2.</strong> Expenses / Shpenzimet',
                                ],
                                [
                                    'text' => '<strong>6.2.1.</strong> The costs associated with the publication, distribution, marketing, optimization of the Artist’s content, and upkeep of their account on YouTube and digital stores, totaling 100 EURO/annum, shall be the responsibility of the Artist. Administrative costs for the first year must be paid in advance.',
                                ],
                                [
                                    'lang' => 'alb',
                                    'text' => '<strong>6.2.1.</strong> Artisti duhet të bëjë pages prejë 100 EURO/vit për botim, shpërndarje, zhvillim të marketingut, rritjen e suksesit të këngës, shitjen dhe mirëmbajtjen e llogarisë së artistit në YouTube dhe dyqanet dixhitale. Pagesa për koston administrative për vitin e parë duhet të bëhet paraprakisht.',
                                ],
                                [
                                    'text' => '<strong>6.2.2.</strong> The artist shall bear the responsibility of covering the banking commissions associated with the transfer of funds.',
                                ],
                                [
                                    'lang' => 'alb',
                                    'text' => '<strong>6.2.2.</strong> Komisionet bankare për transferimin e fondeve do të paguhen nga artisti.',
                                ],
                            ]
                        ],
                        [
                            'title_eng' => 'ARTICLE 7 – PARTIES’ COVENANTS AND OWNERSHIP',
                            'title_alb' => 'NENI 7 – GARANICTË E PALËVE DHE PRONËSIA',
                            'content' => [
                                [
                                    'text' => '<strong>7.1.</strong> Each party to the contract is responsible and liable for paying taxes on the income earned under this contract. In accordance with Law No. 06/L-105 on Corporate Income Tax, Article 31.2 stipulates that "each taxpayer who pays interest or royalties to residents or non-residents shall withhold tax at a rate of ten percent (10%) at the time of payment or credit."',
                                ],
                                [
                                    'lang' => 'alb',
                                    'text' => '<strong>7.1.</strong> Secila palë kontraktuese është përgjegjëse dhe e detyruar të paguajë detyrimet tatimore që dalin nga të ardhurat që fitohen nën këtë kontratë. Bazuar në Ligjin nr. 06/L-105 për Tatimin mbi të Ardhurat, në përputhje me Nenin 31.2 "Tatimpaguesit, që paguajn të drejta punësore (Royalties) për personat rezidentë ose jo-rezidentë, duhet të mbajë një taksë prej dhjetë për qind (10%) në kohën e pagesës ose kreditit".',
                                ],
                            ]
                        ],
                        [
                            'title_eng' => 'ARTICLE 8 – PARTIES’ COVENANTS AND OWNERSHIP',
                            'title_alb' => 'NENI 8 – GARANICTË E PALËVE DHE PRONËSIA',
                            'content' => [
                                [
                                    'text' => '<strong>8.1.</strong> Upon execution of this contract, the Artist, who is the Copyright Owner, hereby affirms that they possess all the necessary rights to utilize, publish, distribute, and sell any audio and/or video masters that they intend to distribute and publish on their YouTube channel and Digital Stores. The Artist warrants that all requisite arrangements, whether verbal or written, with third parties have been duly completed, and that such parties have granted the Artist the necessary permission to authorize Baresha Music SH.P.K to distribute and publish any audio and video masters provided by the Artist on their YouTube channel and Digital Stores.',
                                ],
                                [
                                    'lang' => 'alb',
                                    'text' => '<strong>8.1.</strong> Duke nënshkruar këtë kontratë, Artisti - Pronari i të Drejtave dëshmon se ai posedon të gjitha të drejtat për të përdorur, publikuar, distribuar, shitur si dhe të drejtat, për çdo material audio dhe / ose video që Artisti do të distribuojë dhe publikojë në kanalin e tij në YouTube dhe në Dyqanet Dixhitale, për të cilat Artisti ka bërë të gjitha marrëveshjet e nevojshme, verbale ose të shkruara me palë të treta, dhe që të gjithë këto palë kanë lejuar me vlerë të plotë Artistin për të autorizuar Baresha Music SH.P.K për të distribuar / publikuar çdo material audio dhe video që artisti jep për ta publikuar në kanalin e tij në YouTube dhe në Dyqanet Dixhitale.',
                                ],
                                [
                                    'text' => '<strong>8.2.</strong> By signing this contract, the Artist – The Copyright Owner, CERTIFIES that:',
                                ],
                                [
                                    'lang' => 'alb',
                                    'text' => '<strong>8.2.</strong> Artisti – Pronari i të Drejtave, me nënshkrimin e kësaj kontrate VËRTETON se:',
                                ],
                                [
                                    'text' => '<strong>8.2.1.</strong> The Artist affirms that they bear no financial liability towards third parties and have fulfilled all financial obligations owed to such parties. Any future financial obligations that may arise shall be the Artist\'s sole responsibility, and they will be held personally liable for any such potential obligations.',
                                ],
                                [
                                    'lang' => 'alb',
                                    'text' => '<strong>8.2.1.</strong> Artisti konfirmon se nuk ka asnjë përgjegjësi financiare ndaj palëve të treta dhe se Artisti ka kryer të gjitha obligimet financiare ndaj palëve të treta. Në rast se ndonjë obligim financiar i tillë do të lindë në të ardhmen, Artisti do të jetë i përgjegjshëm personalisht për çdo detyrim potencial të tillë.',
                                ],
                                [
                                    'text' => '<strong>8.2.2.</strong> The Artist warrants that they do not have any existing contractual agreements with any other YouTube or audio distribution companies. In the event that the Artist has such an agreement and still chooses to execute this contract with Baresha Music SH.P.K, they will assume full liability for any potential damages, whether financial or otherwise, that may arise.',
                                ],
                                [
                                    'lang' => 'alb',
                                    'text' => '<strong>8.2.2.</strong> Artisti garanton se nuk ka asnjë marrëveshje kontraktuale të tjera me asnjë kompani distributive të tjera në YouTube dhe audio dhe nëse ai / ajo ka një marrëveshje të tillë dhe ende nënshkruan këtë kontratë me Baresha Music SH.P.K, atëherë Artisti është i përgjegjshëm për çdo dëm potencial (financiar dhe çdo lloj dëmi që mund të lindë).',
                                ],
                                [
                                    'text' => '<strong>8.2.3.</strong> During the term of this contract, the Artist is prohibited from entering into any agreements with other distribution or publication companies for the release of audio and/or video masters on their YouTube channel and digital stores.',
                                ],
                                [
                                    'lang' => 'alb',
                                    'text' => '<strong>8.2.3.</strong> Gjatë afatit të kësaj kontrate, Artisti është i ndaluar të hyjë në marrëveshje të tjera me kompani të tjera distributive ose botuese për publikimin e materialeve audio dhe / ose video në kanalin e tyre në YouTube dhe dyqanet e tyre digjitale.',
                                ],
                            ]
                        ],
                        [
                            'title_eng' => 'ARTICLE 9 – DURATION OF THE CONTRACT',
                            'title_alb' => 'NENI 9 – KOHËZGJATJA E KONTRATËS',
                            'content' => [
                                [
                                    "text" => "<strong>9.1.</strong> The Cooperation Agreement shall be valid for a period of " . htmlspecialchars($contract['kohezgjatja']) . " months from the day the contract is signed and shall be automatically renewed for subsequent " . htmlspecialchars($contract['kohezgjatja']) . "-month periods unless otherwise terminated. Either the Artist (Copyright Owner) or Baresha Music SH.P.K (Copyright User) may terminate this Cooperation Agreement by providing written notice of termination at least 90 days prior to the end of each term. In the event of termination, the Agreement shall be deemed to have ended on the date on which it would have naturally expired. This Agreement may not be terminated prior to the end of any term, except in cases where the Artist provides written notice of termination with a valid reason via email, at least 3 months in advance.",
                                ],
                                [
                                    'lang' => 'alb',
                                    'text' => '<strong>9.1.</strong> Marrëveshja për bashkëpunim do të jetë e vlefshme për një periudhë prej ' . htmlspecialchars($contract['kohezgjatja']) . ' muajsh nga data e nënshkrimit dhe do të rinovohet automatikisht për periudha të mëtejshme prej ' . htmlspecialchars($contract['kohezgjatja']) . ' muajsh, përveç rasteve të ndërprerjes. As artisti (pronari i të drejtave të kopjimit) dhe as Baresha Music SH.P.K (përdoruesi i të drejtave të kopjimit) mund të ndërprejnë këtë marrëveshje për bashkëpunim duke siguruar njoftim me shkrim të ndërprerjes së saj të paktën 90 ditë para përfundimit të secilës periudhë. Në rast të ndërprerjes, marrëveshja do të konsiderohet se ka përfunduar në datën në të cilën ajo do të kishte përfunduar natyrshëm. Kjo marrëveshje nuk mund të ndërpritet përpara përfundimit të çdo periudhe, përveç rasteve kur artisti jep njoftim me shkrim të ndërprerjes së saj me arsyetim të vlefshëm nëpërmjet email-it, të paktën 3 muaj përpara përfundimit të marrëveshjes.',
                                ],
                                [
                                    'text' => 'The undersigned parties hereby declare that they are entering into this contract voluntarily, without any form of coercion, misrepresentation or deceit. By affixing their signatures to this document, the parties affirm that they have thoroughly read and understood the contents of this contract, and have no objections to the terms and conditions stated herein.',
                                ],
                                [
                                    'lang' => 'alb',
                                    'text' => 'Palët nënshkruese dëshmojnë se po nënshkruajnë këtë kontratë me vullnet të lirë, pa ndonjë formë prekjeje, mashtrimi ose gënjeshtrë. Duke nënshkruar këtë kontratë, palët dëshmojnë se kanë lexuar me kujdes kontratën dhe nuk kanë asnjë objeksion nën kushtet dhe parimet e në të shkruar.',
                                ],
                            ]
                        ],
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
                        <button type="submit" class="btn btn-primary me-2" id="submitSignature">
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