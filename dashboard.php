<?php
include 'connection.php';
include 'header.php';
include 'sidebar.php';
// Verify session exists and user_id is available
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if no session exists
    header("Location: login.php");
    exit;
}
$user_id = $_SESSION['user_id'];
// Set default timezone to ensure consistency
date_default_timezone_set('Europe/Tirane');
// Check database connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
// Get user information
$sql_user_info = "SELECT emri, emriart FROM klientet WHERE id = ?";
$stmt_user_info = $conn->prepare($sql_user_info);
$stmt_user_info->bind_param("i", $user_id);
$stmt_user_info->execute();
$user_info_result = $stmt_user_info->get_result();
$user_info = $user_info_result->fetch_assoc();
// Continue with existing queries
$sql_invoice_summary = "SELECT
    (SELECT COUNT(*) FROM invoices WHERE customer_id IN (SELECT id FROM klientet WHERE id = ?)) AS total_invoices,
    (SELECT COUNT(*) FROM invoices WHERE status = 'I papaguar' AND customer_id IN (SELECT id FROM klientet WHERE id = ?)) AS unpaid_invoices,
    (SELECT SUM(total_amount) FROM invoices WHERE status = 'I papaguar' AND customer_id IN (SELECT id FROM klientet WHERE id = ?)) AS total_unpaid_amount,
    (SELECT SUM(paid_amount) FROM invoices WHERE customer_id IN (SELECT id FROM klientet WHERE id = ?)) AS total_paid_amount,
    (SELECT AVG(total_amount) FROM invoices WHERE customer_id IN (SELECT id FROM klientet WHERE id = ?) ) AS average_invoice_amount
";
$stmt_invoice_summary = $conn->prepare($sql_invoice_summary);
$stmt_invoice_summary->bind_param("iiiii", $user_id, $user_id, $user_id, $user_id, $user_id);
$stmt_invoice_summary->execute();
$invoice_summary_result = $stmt_invoice_summary->get_result();
$invoice_summary = $invoice_summary_result->fetch_assoc();
$sql_recent_payments = "SELECT p.payment_id, p.payment_amount, p.payment_date, i.invoice_number, i.total_amount AS invoice_total, i.status AS invoice_status
                           FROM payments p
                           JOIN invoices i ON p.invoice_id = i.id
                           WHERE i.customer_id IN (SELECT id FROM klientet WHERE id = ?)
                           ORDER BY p.payment_date DESC
                           LIMIT 5";
$stmt_recent_payments = $conn->prepare($sql_recent_payments);
$stmt_recent_payments->bind_param("i", $user_id);
$stmt_recent_payments->execute();
$recent_payments_result = $stmt_recent_payments->get_result();
$sql_recent_song_contracts = "SELECT knt.id, knt.vepra, k.emriart AS klient_emriart, knt.data AS contract_date
                            FROM kontrata knt
                            LEFT JOIN klientet k ON knt.klienti = k.emri
                            WHERE k.id = ?
                            ORDER BY knt.data DESC
                            LIMIT 5";
$stmt_recent_song_contracts = $conn->prepare($sql_recent_song_contracts);
$stmt_recent_song_contracts->bind_param("i", $user_id);
$stmt_recent_song_contracts->execute();
$recent_song_contracts_result = $stmt_recent_song_contracts->get_result();
$sql_invoice_status_chart = "SELECT status, COUNT(*) as count
                             FROM invoices
                             WHERE customer_id IN (SELECT id FROM klientet WHERE id = ?)
                             GROUP BY status";
$stmt_invoice_status_chart = $conn->prepare($sql_invoice_status_chart);
$stmt_invoice_status_chart->bind_param("i", $user_id);
$stmt_invoice_status_chart->execute();
$invoice_status_chart_result = $stmt_invoice_status_chart->get_result();
$invoice_status_labels = [];
$invoice_status_series = [];
while ($row = $invoice_status_chart_result->fetch_assoc()) {
    $invoice_status_labels[] = $row['status'];
    $invoice_status_series[] = $row['count'];
}
$sql_payment_type_chart = "SELECT type_of_pay, COUNT(*) as count
                            FROM payments p
                            JOIN invoices i ON p.invoice_id = i.id
                            WHERE i.customer_id IN (SELECT id FROM klientet WHERE id = ?)
                            GROUP BY type_of_pay";
$stmt_payment_type_chart = $conn->prepare($sql_payment_type_chart);
$stmt_payment_type_chart->bind_param("i", $user_id);
$stmt_payment_type_chart->execute();
$payment_type_chart_result = $stmt_payment_type_chart->get_result();
$payment_type_labels = [];
$payment_type_series = [];
while ($row = $payment_type_chart_result->fetch_assoc()) {
    $payment_type_labels[] = $row['type_of_pay'];
    $payment_type_series[] = $row['count'];
}
?>
<style>
    :root {
        --primary-color: #6B46C1; /* Purple */
        --secondary-color: #3B82F6; /* Blue */
        --success-color: #10B981; /* Green */
        --warning-color: #F59E0B; /* Amber */
        --danger-color: #EF4444; /* Red */
        --info-color: #3B82F6; /* Blue */

        --text-primary: #1A202C; /* Very Dark Gray */
        --text-secondary: #4A5568; /* Dark Gray */
        --text-muted: #718096; /* Gray */

        --bg-body: #F7FAFC; /* Very Light Gray */
        --bg-card: #FFFFFF; /* White */
        --border-color: #E2E8F0; /* Light Gray */

        --card-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        --card-shadow-hover: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        --card-border-radius: 12px;
    }

    /* Apply dark mode variables if needed */
    .dark-mode {
        --text-primary: #EDF2F7;
        --text-secondary: #A0AEC0;
        --text-muted: #718096;
        --bg-body: #1A202C;
        --bg-card: #2D3748;
        --border-color: #4A5568;
    }

    body {
        background-color: var(--bg-body);
        font-family: 'Inter', 'Nunito', sans-serif; /* Added Inter for a cleaner look */
        color: var(--text-secondary);
        transition: background-color 0.3s ease, color 0.3s ease;
    }

    .main-content {
        padding: 2rem 1.5rem; /* More horizontal padding */
        transition: padding 0.3s ease;
    }

    /* Welcome Header */
    .welcome-header {
        margin-bottom: 2.5rem;
    }
    .welcome-header h2 {
        font-weight: 700;
        color: var(--text-primary);
        font-size: 1.8rem; /* Slightly larger */
    }
    .welcome-header p {
        color: var(--text-muted);
        font-size: 1rem;
    }
    .welcome-header .date-display {
        font-size: 0.9rem;
        color: var(--text-muted);
        text-align: right;
    }

    /* Dashboard Card Base */
    .dashboard-card {
        background-color: var(--bg-card);
        border-radius: var(--card-border-radius);
        box-shadow: var(--card-shadow);
        margin-bottom: 1.5rem;
        border: 1px solid var(--border-color);
        height: 100%;
        display: flex;
        flex-direction: column;
        transition: all 0.3s ease;
        overflow: hidden; /* Ensure content respects border radius */
    }

    .dashboard-card:hover {
        box-shadow: var(--card-shadow-hover);
        transform: translateY(-3px);
    }

    .dashboard-card-header {
        padding: 1.25rem 1.5rem;
        background-color: transparent; /* Cleaner look */
        border-bottom: 1px solid var(--border-color);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .dashboard-card-title {
        font-size: 1.1rem; /* Adjusted size */
        font-weight: 600; /* Slightly less bold */
        color: var(--text-primary);
        margin-bottom: 0;
    }

    .dashboard-card-body {
        padding: 1.5rem;
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    /* Summary Card Specifics */
    .dashboard-summary-item {
        display: flex; /* Align icon, text, and value */
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
        padding-bottom: 1rem;
        border-bottom: 1px dashed var(--border-color); /* Dashed separator */
        color: var(--text-secondary);
        font-size: 0.9rem;
    }
    .dashboard-summary-item:last-child {
        margin-bottom: 0;
        padding-bottom: 0;
        border-bottom: none;
    }
    .dashboard-summary-item .summary-label {
        display: flex;
        align-items: center;
        gap: 0.5rem; /* Space between icon and text */
    }
    .dashboard-summary-item .summary-label i {
        color: var(--primary-color); /* Use primary color for icons */
        font-size: 1.1rem;
        width: 20px; /* Fixed width for alignment */
        text-align: center;
    }
    .dashboard-summary-value {
        font-size: 1.3rem; /* Adjusted size */
        font-weight: 700;
        color: var(--text-primary);
        text-align: right;
    }
    /* Specific colors for key metrics */
    .summary-unpaid .dashboard-summary-value { color: var(--warning-color); }
    .summary-paid .dashboard-summary-value { color: var(--success-color); }
    .summary-unpaid-amount .dashboard-summary-value { color: var(--danger-color); }


    /* List Card Specifics (Payments, Contracts) */
    .dashboard-list {
        list-style: none;
        padding-left: 0;
        margin-bottom: 1rem; /* Space before button */
        flex-grow: 1; /* Allow list to take available space */
    }
    .dashboard-list-item {
        padding: 0.8rem 0.5rem; /* Slightly more padding */
        border-bottom: 1px solid var(--border-color);
        display: flex;
        justify-content: space-between;
        align-items: center;
        transition: background-color 0.2s ease;
        border-radius: 6px; /* Rounded corners on hover */
        margin-bottom: 0.3rem; /* Small gap between items */
    }
    .dashboard-list-item:hover {
        background-color: rgba(0, 0, 0, 0.02); /* Subtle hover */
    }
    .dark-mode .dashboard-list-item:hover {
        background-color: rgba(255, 255, 255, 0.04);
    }
    .dashboard-list-item:last-child {
        border-bottom: none;
    }
    .dashboard-list-item .item-main-info {
        display: flex;
        align-items: center;
        gap: 0.75rem; /* Space between icon and text */
    }
    .dashboard-list-item .item-main-info i {
        font-size: 1.2rem;
        width: 24px;
        text-align: center;
        color: var(--text-muted);
    }
    .dashboard-list-item .item-details {
        font-size: 0.9rem;
        color: var(--text-primary);
    }
    .dashboard-list-item .item-sub-details {
        font-size: 0.8rem;
        color: var(--text-muted);
    }
    .dashboard-event-date {
        font-size: 0.85rem;
        color: var(--text-muted);
        white-space: nowrap;
        margin-left: 1rem;
    }
    .dashboard-payment-info {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        text-align: right;
    }
    .dashboard-payment-amount {
        font-weight: 600;
        color: var(--success-color);
        font-size: 1rem;
    }
    .dashboard-invoice-link {
        color: var(--primary-color);
        font-weight: 500;
        text-decoration: none;
        transition: color 0.2s ease;
    }
    .dashboard-invoice-link:hover {
        color: var(--secondary-color);
        text-decoration: underline;
    }

    /* Chart Cards */
    .chart-card .dashboard-card-body {
        padding: 1rem; /* Less padding for charts */
    }
    .chart-container { /* Use class for sizing */
        width: 100%;
        height: 350px; /* Default height */
        margin-top: 0.5rem;
    }

    /* Buttons */
    .btn-outline-primary {
        border-color: var(--primary-color);
        color: var(--primary-color);
        font-weight: 500;
        border-radius: 8px;
        padding: 0.5rem 1rem;
        transition: all 0.2s ease;
    }
    .btn-outline-primary:hover {
        background-color: var(--primary-color);
        color: white;
    }
    /* Add similar styles for other outline buttons if needed */
    .btn-outline-secondary { /* Example */
        border-color: var(--text-muted);
        color: var(--text-muted);
        font-weight: 500;
        border-radius: 8px;
        padding: 0.5rem 1rem;
        transition: all 0.2s ease;
    }
    .btn-outline-secondary:hover {
        background-color: var(--text-muted);
        color: var(--bg-card);
    }
     .btn-outline-info { /* Example */
        border-color: var(--info-color);
        color: var(--info-color);
        font-weight: 500;
        border-radius: 8px;
        padding: 0.5rem 1rem;
        transition: all 0.2s ease;
    }
    .btn-outline-info:hover {
        background-color: var(--info-color);
        color: white;
    }

    .btn-sm {
        padding: 0.4rem 0.8rem;
        font-size: 0.8rem;
    }
    .mt-auto {
        margin-top: auto !important; /* Ensure button stays at bottom */
    }

    /* Animations */
    .fade-in {
        animation: fadeIn ease 0.6s forwards;
    }
    .slide-up {
        animation: slideUp ease 0.6s forwards;
        opacity: 0; /* Start hidden for animation */
    }
    /* Add delay for staggered effect */
    .row > div:nth-child(1) .slide-up { animation-delay: 0.1s; }
    .row > div:nth-child(2) .slide-up { animation-delay: 0.2s; }
    .row > div:nth-child(3) .slide-up { animation-delay: 0.3s; }
    .row:nth-of-type(2) > div:nth-child(1) .slide-up { animation-delay: 0.4s; }
    .row:nth-of-type(2) > div:nth-child(2) .slide-up { animation-delay: 0.5s; }


    @keyframes fadeIn {
        0% { opacity: 0; }
        100% { opacity: 1; }
    }
    @keyframes slideUp {
        0% { transform: translateY(25px); opacity: 0; }
        100% { transform: translateY(0); opacity: 1; }
    }

    /* Responsive Adjustments */
    @media (max-width: 992px) {
        .chart-container { height: 320px; }
    }
    @media (max-width: 768px) {
        .main-content { padding: 1.5rem 1rem; }
        .welcome-header h2 { font-size: 1.6rem; }
        .dashboard-card-header { padding: 1rem 1.25rem; }
        .dashboard-card-body { padding: 1.25rem; }
        .dashboard-summary-value { font-size: 1.2rem; }
        .chart-container { height: 300px; }
        .welcome-header .date-display { text-align: left; margin-top: 0.5rem; }
    }
    @media (max-width: 576px) {
        .main-content { padding: 1rem 0.75rem; }
        .welcome-header h2 { font-size: 1.4rem; }
        .dashboard-card-title { font-size: 1rem; }
        .dashboard-summary-item { flex-direction: column; align-items: flex-start; gap: 0.3rem; }
        .dashboard-summary-value { font-size: 1.1rem; text-align: left; margin-top: 0.2rem; }
        .dashboard-list-item { flex-direction: column; align-items: flex-start; gap: 0.5rem; padding: 0.8rem; }
        .dashboard-payment-info { align-items: flex-start; margin-top: 0.5rem; }
        .dashboard-event-date { margin-left: 0; margin-top: 0.3rem; }
        .chart-container { height: 280px; }
        .btn-sm { padding: 0.5rem 1rem; font-size: 0.9rem; } /* Make buttons slightly larger on mobile */
    }
</style>
<div class="col-md-10 col-sm-12 main-content">
    <div class="welcome-header fade-in">
        <h2>Paneli Kryesor</h2>
        <p>Mirë se vini, <?php echo htmlspecialchars($user_info['emriart'] ?: $user_info['emri'] ?: 'Klient'); ?>. Analiza e të dhënave të biznesit tuaj.</p>
        <p class="date-display"><?php echo date("d F Y"); ?></p>
    </div>
    <div class="row">
        <div class="col-lg-4 col-md-6 col-sm-12 mb-4">
            <div class="dashboard-card fade-in slide-up h-100">
                <div class="dashboard-card-header">
                    <h5 class="dashboard-card-title">Përmbledhja e Faturave</h5>
                </div>
                <div class="dashboard-card-body">
                    <div class="dashboard-summary-item">
                        <div class="summary-label"><i class="fas fa-file-invoice"></i> Fatura Totale</div>
                        <span class="dashboard-summary-value"><?php echo htmlspecialchars($invoice_summary['total_invoices'] ?: '0'); ?></span>
                    </div>
                    <div class="dashboard-summary-item summary-unpaid">
                        <div class="summary-label"><i class="fas fa-file-invoice-dollar"></i> Fatura të Papaguara</div>
                        <span class="dashboard-summary-value"><?php echo htmlspecialchars($invoice_summary['unpaid_invoices'] ?: '0'); ?></span>
                    </div>
                    <div class="dashboard-summary-item summary-unpaid-amount">
                        <div class="summary-label"><i class="fas fa-dollar-sign"></i> Vlera Totale e Papaguar</div>
                        <span class="dashboard-summary-value"><?php echo htmlspecialchars(number_format($invoice_summary['total_unpaid_amount'] ?: 0, 2)); ?></span>
                    </div>
                    <div class="dashboard-summary-item summary-paid">
                        <div class="summary-label"><i class="fas fa-check-circle"></i> Vlera Totale e Paguar</div>
                        <span class="dashboard-summary-value"><?php echo htmlspecialchars(number_format($invoice_summary['total_paid_amount'] ?: 0, 2)); ?></span>
                    </div>
                    <div class="dashboard-summary-item">
                        <div class="summary-label"><i class="fas fa-chart-line"></i> Mesatarja e Faturës</div>
                        <span class="dashboard-summary-value"><?php echo htmlspecialchars(number_format($invoice_summary['average_invoice_amount'] ?: 0, 2)); ?></span>
                    </div>
                    <a href="invoices.php" class="btn btn-outline-primary btn-sm mt-auto">Detajet e Faturave <i class="fas fa-arrow-right ms-2"></i></a>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6 col-sm-12 mb-4">
            <div class="dashboard-card fade-in slide-up h-100">
                <div class="dashboard-card-header">
                    <h5 class="dashboard-card-title">Pagesat e Fundit</h5>
                </div>
                <div class="dashboard-card-body">
                    <ul class="dashboard-list">
                        <?php if ($recent_payments_result->num_rows > 0): ?>
                            <?php while ($row = $recent_payments_result->fetch_assoc()): ?>
                                <li class="dashboard-list-item">
                                    <div class="item-main-info">
                                        <i class="fas fa-file-invoice-dollar me-2 text-secondary"></i>
                                        <a href="invoices.php" class="dashboard-invoice-link">Fatura #<?php echo htmlspecialchars($row['invoice_number']); ?></a>
                                    </div>
                                    <div class="dashboard-event-date">
                                        <?php echo htmlspecialchars(date('d M', strtotime($row['payment_date']))); ?>
                                    </div>
                                    <div class="dashboard-payment-info">
                                        <span class="dashboard-payment-amount">+<?php echo htmlspecialchars(number_format($row['payment_amount'], 2)); ?></span>
                                        <div class="item-details"><small> / Totali: <?php echo htmlspecialchars(number_format($row['invoice_total'], 2)); ?></small></div>
                                    </div>
                                </li>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <li>Nuk ka pagesa të fundit.</li>
                        <?php endif; ?>
                    </ul>
                    <a href="payments.php" class="btn btn-outline-secondary btn-sm mt-auto">Shiko Pagesat <i class="fas fa-arrow-right ms-2"></i></a>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-12 col-sm-12 mb-4">
            <div class="dashboard-card fade-in slide-up h-100">
                <div class="dashboard-card-header">
                    <h5 class="dashboard-card-title">Kontratat e Fundit të Këngëve</h5>
                </div>
                <div class="dashboard-card-body">
                    <ul class="dashboard-list">
                        <?php if ($recent_song_contracts_result->num_rows > 0): ?>
                            <?php while ($row = $recent_song_contracts_result->fetch_assoc()): ?>
                                <li class="dashboard-list-item">
                                    <div class="item-main-info">
                                        <i class="fas fa-music me-2 text-info"></i>
                                        <span class="fw-bold"><?php echo htmlspecialchars($row['vepra']); ?></span>
                                        <div class="item-details"><small>Klienti: <?php echo htmlspecialchars($row['klient_emriart'] ?: 'I panjohur'); ?></small></div>
                                    </div>
                                    <span class="dashboard-event-date">(<?php echo htmlspecialchars(date('d M', strtotime($row['contract_date']))); ?>)</span>
                                </li>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <li>Nuk ka kontrata të fundit të këngëve.</li>
                        <?php endif; ?>
                    </ul>
                    <a href="song-contract.php" class="btn btn-outline-info btn-sm mt-auto">Shiko Kontratat <i class="fas fa-arrow-right ms-2"></i></a>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-6 col-md-12 mb-4">
            <div class="dashboard-card chart-card fade-in slide-up h-100">
                <div class="dashboard-card-header">
                    <h5 class="dashboard-card-title">Statusi i Faturave</h5>
                </div>
                <div class="dashboard-card-body">
                    <div id="invoiceStatusChart" class="chart-container"></div>
                </div>
            </div>
        </div>
        <div class="col-lg-6 col-md-12 mb-4">
            <div class="dashboard-card chart-card fade-in slide-up h-100">
                <div class="dashboard-card-header">
                    <h5 class="dashboard-card-title">Llojet e Pagesave</h5>
                </div>
                <div class="dashboard-card-body">
                    <div id="paymentTypeChart" class="chart-container"></div>
                </div>
            </div>
        </div>
    </div>
    <!-- Error handling section that only appears when there's an error -->
    <div id="errorContainer" class="alert alert-danger d-none" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <span id="errorMessage"></span>
    </div>
</div>
<?php include 'footer.php'; ?>
<script src="https://kit.fontawesome.com/de9f35a91c.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    // Function to handle errors
    function showError(message) {
        const errorContainer = document.getElementById('errorContainer');
        const errorMessage = document.getElementById('errorMessage');
        errorMessage.textContent = message;
        errorContainer.classList.remove('d-none');
        setTimeout(() => {
            errorContainer.classList.add('d-none');
        }, 5000);
    }
    // Function to handle chart responsiveness
    function updateChartDimensions() {
        if (invoiceStatusChart && paymentTypeChart) {
            invoiceStatusChart.render();
            paymentTypeChart.render();
        }
    }
    // Listen for window resize events
    window.addEventListener('resize', updateChartDimensions);
    // Chart options with improved responsiveness
    var invoiceStatusOptions = {
        chart: {
            type: 'pie',
            height: '100%',
            toolbar: {
                show: false
            },
            redrawOnWindowResize: true,
            redrawOnParentResize: true
        },
        series: <?php echo json_encode($invoice_status_series ?: []); ?>,
        labels: <?php echo json_encode($invoice_status_labels ?: []); ?>,
        legend: {
            position: 'bottom',
            fontFamily: 'Nunito',
            fontSize: '14px',
            itemMargin: {
                vertical: 8
            },
            horizontalAlign: 'center'
        },
        colors: ['#5482f4', '#49b382', '#ffab00', '#f4516c', '#07a081'],
        tooltip: {
            enabled: true,
            y: {
                formatter: function(value) {
                    return value + " fatura"
                }
            },
            style: {
                fontFamily: 'Nunito',
            }
        },
        plotOptions: {
            pie: {
                expandOnClick: true,
                donut: {
                    size: '65%',
                    labels: {
                        show: true,
                        name: {
                            fontSize: '15px',
                            fontFamily: 'Nunito',
                            color: undefined,
                            offsetY: -10
                        },
                        value: {
                            fontSize: '22px',
                            fontFamily: 'Nunito',
                            color: undefined,
                            offsetY: 16,
                            formatter: function(val) {
                                return val
                            }
                        },
                        total: {
                            show: true,
                            showAlways: true,
                            label: 'Total',
                            fontSize: '18px',
                            fontFamily: 'Nunito',
                            color: '#373d3f',
                        }
                    }
                }
            }
        },
        responsive: [{
            breakpoint: 992,
            options: {
                chart: {
                    height: 380
                },
                legend: {
                    position: 'bottom',
                    horizontalAlign: 'center'
                }
            }
        },
        {
            breakpoint: 576,
            options: {
                chart: {
                    height: 320
                },
                plotOptions: {
                    pie: {
                        donut: {
                            labels: {
                                name: {
                                    fontSize: '13px'
                                },
                                value: {
                                    fontSize: '18px'
                                },
                                total: {
                                    fontSize: '15px'
                                }
                            }
                        }
                    }
                },
                legend: {
                    fontSize: '12px',
                    itemMargin: {
                        vertical: 5,
                        horizontal: 2
                    }
                }
            }
        }]
    };
    // Create charts with error handling
    try {
        var invoiceStatusChart = new ApexCharts(document.querySelector("#invoiceStatusChart"), invoiceStatusOptions);
        invoiceStatusChart.render();
    } catch (error) {
        console.error("Error rendering invoice status chart:", error);
        showError("Pati një problem në shfaqjen e grafikut të statusit të faturave.");
    }
    var paymentTypeOptions = {
        // Similar responsive updates to the payment type chart
        chart: {
            type: 'pie',
            height: '100%',
            toolbar: {
                show: false
            },
            redrawOnWindowResize: true,
            redrawOnParentResize: true
        },
        series: <?php echo json_encode($payment_type_series ?: []); ?>,
        labels: <?php echo json_encode($payment_type_labels ?: []); ?>,
        legend: {
            position: 'bottom',
            fontFamily: 'Nunito',
            fontSize: '14px',
            itemMargin: {
                vertical: 8
            },
            horizontalAlign: 'center'
        },
        colors: ['#ffab00', '#a370f7', '#49b382', '#5482f4', '#f4516c'],
        tooltip: {
            enabled: true,
            y: {
                formatter: function(value) {
                    return value + " pagesa"
                }
            },
            style: {
                fontFamily: 'Nunito',
            }
        },
        plotOptions: {
            pie: {
                expandOnClick: true,
                donut: {
                    size: '65%',
                    labels: {
                        show: true,
                        name: {
                            fontSize: '15px',
                            fontFamily: 'Nunito',
                            color: undefined,
                            offsetY: -10
                        },
                        value: {
                            fontSize: '22px',
                            fontFamily: 'Nunito',
                            color: undefined,
                            offsetY: 16,
                            formatter: function(val) {
                                return val
                            }
                        },
                        total: {
                            show: true,
                            showAlways: true,
                            label: 'Total',
                            fontSize: '18px',
                            fontFamily: 'Nunito',
                            color: '#373d3f',
                        }
                    }
                }
            }
        },
        responsive: [{
            breakpoint: 992,
            options: {
                chart: {
                    height: 380
                },
                legend: {
                    position: 'bottom',
                    horizontalAlign: 'center'
                }
            }
        },
        {
            breakpoint: 576,
            options: {
                chart: {
                    height: 320
                },
                plotOptions: {
                    pie: {
                        donut: {
                            labels: {
                                name: {
                                    fontSize: '13px'
                                },
                                value: {
                                    fontSize: '18px'
                                },
                                total: {
                                    fontSize: '15px'
                                }
                            }
                        }
                    }
                },
                legend: {
                    fontSize: '12px',
                    itemMargin: {
                        vertical: 5,
                        horizontal: 2
                    }
                }
            }
        }]
    };
    try {
        var paymentTypeChart = new ApexCharts(document.querySelector("#paymentTypeChart"), paymentTypeOptions);
        paymentTypeChart.render();
    } catch (error) {
        console.error("Error rendering payment type chart:", error);
        showError("Pati një problem në shfaqjen e grafikut të llojeve të pagesave.");
    }
    // Initial chart dimensions update
    document.addEventListener("DOMContentLoaded", function() {
        updateChartDimensions();
    });
</script>