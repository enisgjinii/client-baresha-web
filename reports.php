<?php
include 'connection.php';
include 'header.php';
include 'sidebar.php';

$user_id = $_SESSION['user_id'];

$sql = "SELECT payments.*, invoices.invoice_number, invoices.total_amount
        FROM payments
        INNER JOIN invoices ON payments.invoice_id = invoices.id
        WHERE invoices.customer_id = ?
        ORDER BY payments.payment_date DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$pagesa = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Prepare data for ApexChart - Payment distribution by type
$paymentTypeCounts = [];
foreach ($pagesa as $payment) {
    $paymentType = $payment['type_of_pay'];
    if (isset($paymentTypeCounts[$paymentType])) {
        $paymentTypeCounts[$paymentType]++;
    } else {
        $paymentTypeCounts[$paymentType] = 1;
    }
}

$paymentTypeLabels = array_keys($paymentTypeCounts);
$paymentTypeSeries = array_values($paymentTypeCounts);

?>

<style>
    /* Re-using invoice table styles for consistency */
    #paymentsTable th,
    #paymentsTable td {
        text-align: left !important;
        vertical-align: middle;
        padding: 0.6rem;
    }

    #paymentsTable th {
        font-size: 1rem;
        font-weight: bold;
        color: #333;
        border-bottom: 2px solid #eee;
    }

    #paymentsTable td {
        font-size: 0.9rem;
    }

    /* Informative spans - similar to invoices but adapted for payments */
    .invoice-number-span {
        font-weight: 500;
        color: #007bff;
    }

    .payment-date-span {
        font-style: italic;
        color: #6c757d;
    }

    .payment-amount-span {
        font-weight: bold;
        color: #28a745;
        /* Green to indicate payment */
    }

    .payment-amount-span::before {
        content: '+';
        /* Indicate it's an addition of payment */
        margin-right: 2px;
    }


    .bank-info-span {
        color: #666;
        /* Muted color for bank info */
        font-size: 0.85rem;
        /* Slightly smaller font */
    }

    .payment-type-span {
        font-weight: 600;
        color: #17a2b8;
        /* Info color for payment type */
    }

    .description-span {
        color: #555;
        /* Slightly darker muted color for description */
    }


    /* General layout and animations - re-using invoice styles */
    .table-responsive {
        overflow-x: auto;
    }

    .card-body {
        padding: 1.25rem;
    }

    .card-header {
        padding: 0.75rem 1.25rem;
    }

    .main-content {
        padding: 1.5rem;
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
            transform: translateY(20px);
            opacity: 0;
        }

        100% {
            transform: translateY(0);
            opacity: 1;
        }
    }

    /* ApexChart container style */
    #paymentChart {
        width: 100%;
        margin-top: 20px;
        /* Add some space between table and chart */
    }
</style>

<div class="col-md-10 main-content">
    <div class="d-flex justify-content-between align-items-center mb-4 fade-in">
        <div>
            <h3 class="fw-bold text-primary">Raporti i Pagesave</h3>
            <p class="text-muted">Rishikoni të gjitha detajet e pagesave tuaja</p>
        </div>
    </div>
    <div class="card shadow-lg border-0 rounded-lg slide-up">
        <div class="card-header bg-light py-2">
            <h5 class="mb-0">Të gjitha pagesat</h5>
        </div>
        <div class="card-body p-3">
            <?php if (!empty($pagesa)): ?>
                <div class="table-responsive">
                    <table class="table table-hover animate-table" id="paymentsTable">
                        <thead class="table-light">
                            <tr>
                                <th><span class="header-span">Fatura #</span></th>
                                <th><span class="header-span">Data e Pagesës</span></th>
                                <th><span class="header-span">Shuma</span></th>
                                <th><span class="header-span">Lloji i Pagesës</span></th>
                                <th><span class="header-span">Përshkrimi</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pagesa as $p): ?>
                                <tr class="table-row-hover">
                                    <td><span class="invoice-number-span"><?php echo htmlspecialchars($p['invoice_number']); ?></span></td>
                                    <td><span class="payment-date-span"><?php echo htmlspecialchars(date('d M Y', strtotime($p['payment_date']))); ?></span></td>
                                    <td><span class="payment-amount-span"><?php echo htmlspecialchars(number_format($p['payment_amount'], 2)); ?></span></td>
                                    <td><span class="payment-type-span"><?php echo htmlspecialchars($p['type_of_pay']); ?></span></td>
                                    <td><span class="description-span"><?php echo htmlspecialchars($p['description']); ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-5 empty-state">
                    <h5>Nuk u gjetën pagesa</h5>
                    <p class="text-muted">Asnjë pagesë nuk është regjistruar për faturat tuaja ende.</p>
                </div>
            <?php endif; ?>

            <div id="paymentChart"></div>

        </div>
    </div>
</div>
<?php include 'footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>


<script>
    $(document).ready(function() {
        $('#paymentsTable').DataTable({
            responsive: true,
            dom: `
    <'container-fluid'
        <'row mb-3'
            <'col-12 col-md-4 col-lg-3'
                <'d-flex align-items-center'
                    <'me-3'l>
                >
            >
            <'col-12 col-md-4 col-lg-6 my-2 my-md-0'
                <'d-flex justify-content-center justify-content-md-center'B>
            >
            <'col-12 col-md-4 col-lg-3'
                <'d-flex justify-content-end'f>
            >
        >
        <'row'
            <'col-12'
                <'table-responsive'tr>
            >
        >
        <'row mt-3'
            <'col-12 col-md-6'i>
            <'col-12 col-md-6'
                <'d-flex justify-content-md-end'p>
            >
        >
    >`,
            buttons: [
                'csv', 'excel', 'pdf', 'print'
            ],
            initComplete: function() {
                $(".dt-buttons").removeClass("dt-buttons btn-group");
                $(".buttons-csv").addClass("btn btn-light btn-sm me-1");
                $(".buttons-excel").addClass("btn btn-light btn-sm me-1");
                $(".buttons-pdf").addClass("btn btn-light btn-sm me-1");
                $(".buttons-print").addClass("btn btn-light btn-sm");

                $("div.dataTables_length select").addClass("form-select").css({
                    width: 'auto',
                    margin: '0 8px',
                    padding: '0.375rem 1.75rem 0.375rem 0.75rem',
                    lineHeight: '1.5',
                    border: '1px solid #ced4da',
                    borderRadius: '0.25rem'
                });
            },
        });

        // ApexChart Configuration - Pie Chart for Payment Types
        var options = {
            chart: {
                type: 'pie',
                height: 350,
            },
            series: <?php echo json_encode($paymentTypeSeries); ?>, // Use PHP array for series data
            labels: <?php echo json_encode($paymentTypeLabels); ?>, // Use PHP array for labels
            legend: {
                position: 'bottom'
            },
            responsive: [{
                breakpoint: 480,
                options: {
                    chart: {
                        width: 200
                    },
                    legend: {
                        position: 'bottom'
                    }
                }
            }]
        };

        var chart = new ApexCharts(document.querySelector("#paymentChart"), options);
        chart.render();
    });
</script>