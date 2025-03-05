<?php
include 'connection.php';
include 'header.php';
include 'sidebar.php';
class Invoice
{
  public $invoice_number;
  public $created_date;
  public $total_amount;
  public $paid_amount;
  public $status;
  public function __construct($data)
  {
    $this->invoice_number = $data['invoice_number'];
    $this->created_date   = $data['created_date'];
    $this->total_amount   = $data['total_amount'];
    $this->paid_amount    = $data['paid_amount'];
    $this->status         = $data['status'];
  }
}
class InvoiceRepository
{
  private $conn;
  public function __construct($conn)
  {
    $this->conn = $conn;
  }
  public function getInvoicesByUserId($user_id)
  {
    $sql = "SELECT invoices.*, klientet.emri AS emri_klientit
                FROM invoices
                INNER JOIN klientet ON invoices.customer_id = klientet.id
                WHERE klientet.id = ?
                ORDER BY invoices.created_date DESC";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $invoices = [];
    while ($row = $result->fetch_assoc()) {
      $invoices[] = new Invoice($row);
    }
    $stmt->close();
    return $invoices;
  }
}

$user_id = $_SESSION['user_id'];
$invoiceRepo = new InvoiceRepository($conn);
$invoices = $invoiceRepo->getInvoicesByUserId($user_id);

$invoiceStatusCounts = [];
foreach ($invoices as $invoice) {
  $status = ucfirst(strtolower($invoice->status)); 
  if (isset($invoiceStatusCounts[$status])) {
    $invoiceStatusCounts[$status]++;
  } else {
    $invoiceStatusCounts[$status] = 1;
  }
}

$invoiceStatusLabels = array_keys($invoiceStatusCounts);
$invoiceStatusSeries = array_values($invoiceStatusCounts);


?>

<style>
  #invoicesTable th,
  #invoicesTable td {
    text-align: left !important;
    vertical-align: middle;
    padding: 0.6rem;
  }

  #invoicesTable th {
    font-size: 1rem;
    font-weight: bold;
    color: #333;
    border-bottom: 2px solid #eee;
  }

  #invoicesTable td {
    font-size: 0.9rem;
  }

  .invoice-number-span {
    font-weight: 500;
    color: #007bff;
  }

  .date-span {
    font-style: italic;
    color: #6c757d;
  }

  .total-amount-span,
  .paid-amount-span {
    font-weight: bold;
  }

  .total-amount-span::before {
    content: '+';
    margin-right: 2px;
    color: #28a745;
  }

  .paid-amount-span::before {
    content: '-';
    margin-right: 2px;
    color: #17a2b8;
  }


  .status-span {
    display: inline-block;
    padding: 0.25rem 0.5rem;
    border-radius: 0.2rem;
    font-weight: 600;
    text-align: center;
    min-width: 70px;
  }

  .status-paid {
    background-color: #d4edda;
    color: #155724;
  }

  .status-pending {
    background-color: #fff3cd;
    color: #856404;
  }

  .status-due {
    background-color: #f8d7da;
    color: #721c24;
  }

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

  #invoiceChart {
    width: 100%;
    margin-top: 20px;
   
  }
</style>

<div class="col-md-10 main-content">
  <div class="d-flex justify-content-between align-items-center mb-3 fade-in">
    <div>
      <h3 class="fw-bold text-primary">Fatura</h3>
      <p class="text-muted mb-0">Menaxhoni dhe rishikoni faturat tuaja</p>
    </div>
  </div>
  <div class="card shadow-sm border-0 rounded-lg slide-up">
    <div class="card-header bg-light py-2">
      <h5 class="mb-0">Të gjitha faturat</h5>
    </div>
    <div class="card-body p-3">
      <?php if (!empty($invoices)): ?>
        <div class="table-responsive">
          <table class="table table-hover animate-table" id="invoicesTable">
            <thead class="table-light">
              <tr>
                <th><span class="header-span">Fatura #</span></th>
                <th><span class="header-span">Data</span></th>
                <th><span class="header-span">Pagesa</span></th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($invoices as $invoice): ?>
                <tr class="table-row-hover">
                  <td><span class="invoice-number-span"><?php echo htmlspecialchars($invoice->invoice_number); ?></span></td>
                  <td><span class="date-span"><?php echo htmlspecialchars(date('d M Y', strtotime($invoice->created_date))); ?></span></td>
                  <td><span class="paid-amount-span"><?php echo htmlspecialchars(number_format($invoice->paid_amount, 2)); ?></span></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php else: ?>
        <div class="text-center py-4 empty-state">
          <h5>Nuk u gjetën fatura</h5>
          <p class="text-muted">Nuk keni krijuar asnjë faturë ende.</p>
        </div>
      <?php endif; ?>

      <div id="invoiceChart"></div>

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
    $('#invoicesTable').DataTable({
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
    var options = {
      chart: {
        type: 'pie',
        height: 350,
      },
      series: <?php echo json_encode($invoiceStatusSeries); ?>,
      labels: <?php echo json_encode($invoiceStatusLabels); ?>,
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

    var chart = new ApexCharts(document.querySelector("#invoiceChart"), options);
    chart.render();
  });
</script>