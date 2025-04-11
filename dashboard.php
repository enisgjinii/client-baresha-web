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
    body {
        background-color: #f3f7fa;
        font-family: 'Nunito', sans-serif;
    }
    .main-content {
        padding: 2rem 1rem; /* Adjusted padding for mobile */
        transition: padding 0.3s ease;
    }
    .dashboard-card {
        background-color: #fff;
        border-radius: 12px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
        margin-bottom: 1.5rem;
        overflow: hidden;
        border: 1px solid #eef0f2;
        height: 100%; /* Equal height cards */
        display: flex;
        flex-direction: column;
    }
    .dashboard-card:hover {
        box-shadow: 0 7px 25px rgba(0, 0, 0, 0.07);
        transform: translateY(-2px);
        transition: all 0.3s ease;
    }
    .dashboard-card-header {
        padding: 1.5rem 1.5rem;
        background-color: #ffffff;
        border-bottom: 1px solid #eef0f2;
    }
    .dashboard-card-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: #3a4750;
    }
    .dashboard-card-body {
        padding: 1.5rem;
        flex: 1;
        display: flex;
        flex-direction: column;
    }
    .dashboard-summary-item {
        margin-bottom: 1.2rem;
        color: #6e7a82;
    }
    .dashboard-summary-value {
        font-size: 2rem;
        font-weight: 800;
        color: #07a081;
        display: block;
        margin-top: 0.3rem;
    }
    .dashboard-list {
        list-style: none;
        padding-left: 0;
    }
    .dashboard-list-item {
        padding: 1rem 0;
        border-bottom: 1px solid #edf2f7;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .dashboard-list-item:last-child {
        border-bottom: none;
    }
    .dashboard-event-date {
        font-style: italic;
        color: #a0aab4;
        margin-left: 1.2rem;
    }
    .dashboard-payment-info {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
    }
    .dashboard-payment-amount {
        font-weight: 700;
        color: #49b382;
        font-size: 1.1rem;
    }
    .dashboard-invoice-link {
        color: #5482f4;
        font-weight: 500;
    }
    #invoiceStatusChart,
    #paymentTypeChart {
        width: 100%;
        height: 350px;
        margin-top: 1rem;
    }
    .chart-card .dashboard-card-body {
        padding: 1rem;
    }
    .fade-in {
        animation: fadeIn ease 0.5s;
    }
    .slide-up {
        animation: slideUp ease 0.5s;
    }
    @keyframes fadeIn {
        0% {
            opacity: 0;
        }
        100% {
            opacity: 1;
        }
    }
    @keyframes slideUp {
        0% {
            transform: translateY(30px);
            opacity: 0;
        }
        100% {
            transform: translateY(0);
            opacity: 1;
        }
    }
    /* Adding responsive media queries */
    @media (max-width: 768px) {
        .main-content {
            padding: 1.5rem 0.75rem;
        }
        .dashboard-card-header {
            padding: 1.25rem;
        }
        .dashboard-card-body {
            padding: 1.25rem;
        }
        .dashboard-summary-value {
            font-size: 1.5rem;
        }
        .chart-container {
            height: 300px;
        }
    }
    @media (max-width: 576px) {
        .main-content {
            padding: 1rem 0.5rem;
        }
        .dashboard-card-title {
            font-size: 1.1rem;
        }
        .dashboard-summary-value {
            font-size: 1.3rem;
        }
        .dashboard-list-item {
            flex-direction: column;
            align-items: flex-start;
        }
        .dashboard-payment-info {
            align-items: flex-start;
            margin-top: 0.5rem;
        }
        .chart-container {
            height: 250px;
        }
    }
</style>
<div class="col-md-10 col-sm-12 main-content">
    <div class="d-flex justify-content-between align-items-center mb-4 fade-in">
        <div>
            <h2 class="fw-bold text-dark mb-2">Paneli Kryesor</h2>
            <p class="text-muted mb-0">Mirë se vini, <?php echo htmlspecialchars($user_info['emriart'] ?: $user_info['emri'] ?: 'Klient'); ?>. Analiza e të dhënave të biznesit tuaj.</p>
        </div>
        <div class="d-none d-md-block">
            <p class="text-muted mb-0"><?php echo date("d F Y"); ?></p>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-4 col-md-6 col-sm-12 mb-4">
            <div class="dashboard-card fade-in slide-up h-100">
                <div class="dashboard-card-header">
                    <h5 class="dashboard-card-title">Përmbledhja e Faturave</h5>
                </div>
                <div class="dashboard-card-body">
                    <div class="dashboard-summary-item">
                        Fatura Totale
                        <span class="dashboard-summary-value"><?php echo htmlspecialchars($invoice_summary['total_invoices'] ?: '0'); ?></span>
                    </div>
                    <div class="dashboard-summary-item">
                        Fatura të Papaguara
                        <span class="dashboard-summary-value"><?php echo htmlspecialchars($invoice_summary['unpaid_invoices'] ?: '0'); ?></span>
                    </div>
                    <div class="dashboard-summary-item">
                        Vlera Totale e Papaguar
                        <span class="dashboard-summary-value"><?php echo htmlspecialchars(number_format($invoice_summary['total_unpaid_amount'] ?: 0, 2)); ?></span>
                    </div>
                    <div class="dashboard-summary-item">
                        Vlera Totale e Paguar
                        <span class="dashboard-summary-value"><?php echo htmlspecialchars(number_format($invoice_summary['total_paid_amount'] ?: 0, 2)); ?></span>
                    </div>
                    <div class="dashboard-summary-item">
                        Mesatarja e Faturës
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
                                    <div>
                                        <i class="fas fa-file-invoice-dollar me-2 text-secondary"></i>
                                        <a href="invoices.php" class="dashboard-invoice-link">Fatura #<?php echo htmlspecialchars($row['invoice_number']); ?></a>
                                        <span class="dashboard-event-date">(<?php echo htmlspecialchars(date('d M', strtotime($row['payment_date']))); ?>)</span>
                                        <div class="text-muted"><small>Statusi: <?php echo htmlspecialchars($row['invoice_status']); ?></small></div>
                                    </div>
                                    <div class="dashboard-payment-info">
                                        <span class="dashboard-payment-amount">+<?php echo htmlspecialchars(number_format($row['payment_amount'], 2)); ?></span>
                                        <div class="text-muted"><small> / Totali: <?php echo htmlspecialchars(number_format($row['invoice_total'], 2)); ?></small></div>
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
                                    <div>
                                        <i class="fas fa-music me-2 text-info"></i>
                                        <span class="fw-bold"><?php echo htmlspecialchars($row['vepra']); ?></span>
                                        <div class="text-muted"><small>Klienti: <?php echo htmlspecialchars($row['klient_emriart'] ?: 'I panjohur'); ?></small></div>
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