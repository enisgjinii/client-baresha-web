<?php
// Enable error reporting for development (Disable in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include the database connection file
include 'connection.php';

// Function to safely retrieve and sanitize GET parameters
function get_safe_param($conn, $param)
{
    return isset($_GET[$param]) ? $conn->real_escape_string(trim($_GET[$param])) : '';
}

// Retrieve the Invoice Number securely
$invoiceNumber = get_safe_param($conn, 'id');

// Validate the Invoice Number
if (empty($invoiceNumber)) {
    echo "<div class='text-center mt-5'>Numri i faturës është i pavlefshëm.</div>";
    exit;
}

// Prepare and execute the SQL query for invoice details using prepared statements
$stmt = $conn->prepare("SELECT * FROM invoices WHERE invoice_number = ?");
if (!$stmt) {
    echo "<div class='text-center mt-5'>Gabim në përgatitjen e pyetjes së faturës.</div>";
    exit;
}
$stmt->bind_param("s", $invoiceNumber);
if (!$stmt->execute()) {
    echo "<div class='text-center mt-5'>Gabim në ekzekutimin e pyetjes së faturës.</div>";
    exit;
}
$result = $stmt->get_result();

// Check if the invoice exists
if ($result->num_rows > 0) {
    // Fetch invoice data
    $invoiceRow = $result->fetch_assoc();
    $customerID = $invoiceRow['customer_id'] ?? null;
    $invoiceID = $invoiceRow['id'] ?? null;
    // Handle potential null for subaccount_name
    $subaccountName = !empty($invoiceRow['subaccount_name']) ? htmlspecialchars($invoiceRow['subaccount_name'], ENT_QUOTES, 'UTF-8') : '';

    if (!$customerID || !$invoiceID) {
        echo "<div class='text-center mt-5'>Të dhënat e faturës janë të paplota.</div>";
        exit;
    }

    // Get customer details securely
    $stmt2 = $conn->prepare("SELECT * FROM klientet WHERE id = ?");
    if (!$stmt2) {
        echo "<div class='text-center mt-5'>Gabim në përgatitjen e pyetjes së klientit.</div>";
        exit;
    }
    $stmt2->bind_param("s", $customerID);
    if (!$stmt2->execute()) {
        echo "<div class='text-center mt-5'>Gabim në ekzekutimin e pyetjes së klientit.</div>";
        exit;
    }
    $result2 = $stmt2->get_result();

    // Initialize customer variables
    $customerName = $customerEmail = $customerAddress = $customerPhone = "";
    $customerPercentage = 0;

    // Check if customer exists
    if ($result2->num_rows > 0) {
        $clientRow = $result2->fetch_assoc();
        $customerName = !empty($clientRow['emri']) ? htmlspecialchars($clientRow['emri'], ENT_QUOTES, 'UTF-8') : "";
        $customerEmail = !empty($clientRow['emailadd']) ? htmlspecialchars($clientRow['emailadd'], ENT_QUOTES, 'UTF-8') : "";
        $customerAddress = !empty($clientRow['adresa']) ? htmlspecialchars($clientRow['adresa'], ENT_QUOTES, 'UTF-8') : "";
        $customerPhone = !empty($clientRow['nrtel']) ? htmlspecialchars($clientRow['nrtel'], ENT_QUOTES, 'UTF-8') : "";
        $customerPercentage = isset($clientRow['perqindja']) ? floatval($clientRow['perqindja']) : 0;
    }

    // Get all payments for this invoice
    $stmtPayments = $conn->prepare("SELECT * FROM payments WHERE invoice_id = ? ORDER BY payment_date DESC");
    if (!$stmtPayments) {
        echo "<div class='text-center mt-5'>Gabim në përgatitjen e pyetjes së pagesave.</div>";
        exit;
    }
    $stmtPayments->bind_param("i", $invoiceID);
    if (!$stmtPayments->execute()) {
        echo "<div class='text-center mt-5'>Gabim në ekzekutimin e pyetjes së pagesave.</div>";
        exit;
    }
    $resultForPayments = $stmtPayments->get_result();
?>

    <!-- Invoice Details Markup -->
    <div class="container">
        <!-- Header Section -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="d-flex align-items-center">
                <div class="logo me-2">
                    <img src="images/brand-icon.png" alt="Company Logo" width="60">
                </div>
                <div>
                    <h5 class="mb-0">Baresha Network</h5>
                    <small class="text-muted">
                        8RVC+762, R118, Shiroke, Suhareke<br>
                        info@bareshamusic.com | +383 (049) 605 655
                    </small>
                </div>
            </div>
            <div class="text-end">
                <h6 class="mb-1">Fatura #: <?= htmlspecialchars($invoiceRow['invoice_number'] ?? '', ENT_QUOTES, 'UTF-8'); ?></h6>
                <span class="badge <?= ($invoiceRow['type'] ?? '') == 'grupor' ? 'bg-success' : 'bg-primary'; ?>">
                    <?= ($invoiceRow['type'] ?? '') == 'grupor' ? 'Fatura e ndarë' : 'Fatura individuale'; ?>
                </span>
                <?php if (($invoiceRow['type'] ?? '') == 'grupor' && !empty($subaccountName)) : ?>
                    <span class="badge bg-info text-dark ms-1"><?= $subaccountName; ?></span>
                <?php endif; ?>
            </div>
        </div>
        <hr>
        <!-- Customer & Invoice Details -->
        <div class="row mb-3">
            <div class="col-6">
                <?php if (!empty($customerName)) : ?>
                    <p class="mb-1"><strong>Faturuar për:</strong> <?= $customerName; ?></p>
                <?php endif; ?>
                <?php if (!empty($customerAddress)) : ?>
                    <p class="mb-1"><strong>Adresa:</strong> <?= $customerAddress; ?></p>
                <?php endif; ?>
                <?php if (!empty($customerEmail)) : ?>
                    <p class="mb-1"><strong>Email-i:</strong> <?= $customerEmail; ?></p>
                <?php endif; ?>
                <?php if (!empty($customerPhone)) : ?>
                    <p class="mb-1"><strong>Numri i telefonit:</strong> <?= $customerPhone; ?></p>
                <?php endif; ?>
            </div>
            <div class="col-6 text-end">
                <p class="mb-1"><strong>Data e faturës:</strong> <?= htmlspecialchars($invoiceRow['created_date'] ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
                <p class="mb-1"><strong>Statusi:</strong> <?= htmlspecialchars($invoiceRow['status'] ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
            </div>
        </div>
        <hr>
        <!-- Invoice Items -->
        <h6>Detajet e Fatures</h6>
        <div class="table-responsive">
            <table class="table table-sm table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Emërtimi</th>
                        <th>Çmimi (EUR)</th>
                        <th>Çmimi pas %<?= $customerPercentage; ?></th>
                        <th>Statusi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><?= htmlspecialchars($invoiceRow['id'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?= htmlspecialchars($invoiceRow['item'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?= number_format(floor(($invoiceRow['total_amount_in_eur'] ?? 0)), 2) . " €"; ?></td>
                        <td><?= number_format(floor(($invoiceRow['total_amount_in_eur_after_percentage'] ?? 0)), 2) . " €"; ?></td>
                        <td><?= htmlspecialchars($invoiceRow['status'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <p class="mb-3"><strong>Totali i Faturës:</strong> <?= number_format(floor(($invoiceRow['total_amount_in_eur_after_percentage'] ?? 0)), 2) . " €"; ?></p>
        <!-- Payment History -->
        <h6>Historia e Pagesave</h6>
        <?php if ($resultForPayments->num_rows > 0) : ?>
            <div class="table-responsive">
                <table class="table table-sm table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>ID e Pagesës</th>
                            <th>Data e Pagesës</th>
                            <th>Shuma (EUR)</th>
                            <th>Informacion Bankar</th>
                            <th>Metoda</th>
                            <th>Shënime</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $totalPayments = 0;
                        while ($payment = $resultForPayments->fetch_assoc()) {
                            $paymentAmount = floor(($payment['payment_amount'] ?? 0));
                            $totalPayments += $paymentAmount;
                            echo "<tr>
                          <td>" . htmlspecialchars($payment['payment_id'] ?? '', ENT_QUOTES, 'UTF-8') . "</td>
                          <td>" . htmlspecialchars($payment['payment_date'] ?? '', ENT_QUOTES, 'UTF-8') . "</td>
                          <td>" . number_format($paymentAmount, 2) . " €</td>
                          <td>" . htmlspecialchars($payment['bank_info'] ?? '', ENT_QUOTES, 'UTF-8') . "</td>
                          <td>" . htmlspecialchars($payment['type_of_pay'] ?? '', ENT_QUOTES, 'UTF-8') . "</td>
                          <td>" . htmlspecialchars($payment['description'] ?? '', ENT_QUOTES, 'UTF-8') . "</td>
                        </tr>";
                        }
                        ?>
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <th colspan="2">Totali</th>
                            <th><?= number_format($totalPayments, 2) . " €"; ?></th>
                            <th colspan="3"></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        <?php else : ?>
            <p>Nuk ka pagesa të regjistruara për këtë faturë.</p>
        <?php endif; ?>
        <!-- Payment Details -->
        <div class="mt-3">
            <p><strong>Shuma e Paguar:</strong> <?= number_format(floor(($invoiceRow['paid_amount'] ?? 0)), 2) . " €"; ?></p>
            <p><strong>Mbetja:</strong> <?= number_format(floor((($invoiceRow['total_amount_in_eur_after_percentage'] ?? 0) - ($invoiceRow['paid_amount'] ?? 0))), 2) . " €"; ?></p>
        </div>
    </div>

<?php
    // Close payment statement
    $stmtPayments->close();
} else {
    echo "<div class='text-center mt-5'>Fatura nuk u gjet.</div>";
}
$stmt->close();
$conn->close();
?>