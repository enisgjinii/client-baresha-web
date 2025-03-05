<?php
include 'connection.php';
include 'header.php';
include 'sidebar.php';

$user_id = $_SESSION['user_id']; // Get user ID from session

// Modified SQL query to JOIN tables and filter by user ID - Assuming 'reg_user_id' in klientet
$sql = "SELECT knt.*, k.emri AS klient_emri, k.emriart AS klient_emriart, k.youtube AS klient_youtube
        FROM kontrata knt
        LEFT JOIN klientet k ON knt.klienti = k.emri -- Assuming 'kontrata.klienti' links to 'klientet.emri'
        WHERE k.id = ?"; // Filter by user ID - adjust if needed

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

?>
<style>
    #songContractsTable th,
    #songContractsTable td {
        text-align: left !important;
        vertical-align: middle;
        padding: 0.6rem;
    }

    #songContractsTable th {
        font-size: 1rem;
        font-weight: bold;
        color: #333;
        border-bottom: 2px solid #eee;
    }

    #songContractsTable td {
        font-size: 0.9rem;
    }

    .contract-id-span {
        font-weight: 500;
        color: #007bff;
    }

    .date-span {
        font-style: italic;
        color: #6c757d;
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
</style>
<div class="col-md-10 main-content">
    <div class="d-flex justify-content-between align-items-center mb-3 fade-in">
        <div>
            <h3 class="fw-bold text-primary">Kontratat e Këngëve</h3>
            <p class="text-muted mb-0">Menaxho dhe shqyrto kontratat e këngëve</p>
        </div>
    </div>
    <div class="card shadow-sm border-0 rounded-lg slide-up">
        <div class="card-header bg-light py-2">
            <h5 class="mb-0">Të Gjitha Kontratat e Këngëve për Përdoruesin</h5>
        </div>
        <div class="card-body p-3">
            <?php if ($result->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover animate-table" id="songContractsTable">
                        <thead class="table-light">
                            <tr>
                                <th><span class="header-span">ID</span></th>
                                <th><span class="header-span">Emri</span></th>
                                <th><span class="header-span">Mbiemri</span></th>
                                <th><span class="header-span">Numri i Telefonit</span></th>
                                <th><span class="header-span">Numri Personal</span></th>
                                <th><span class="header-span">Vepra</span></th>
                                <th><span class="header-span">Data</span></th>
                                <th><span class="header-span">Shënim</span></th>
                                <th><span class="header-span">Nënshkrimi</span></th>
                                <th><span class="header-span">Kontrata PDF</span></th>
                                <th><span class="header-span">Përqindja</span></th>
                                <th><span class="header-span">Klienti Emri</span></th>
                                <th><span class="header-span">Klienti Emri Artistik</span></th>
                                <th><span class="header-span">Klienti Youtube</span></th>
                                <th><span class="header-span">PDF File</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    echo "<tr class='table-row-hover'>";
                                    echo "<td><span class='contract-id-span'>" . htmlspecialchars($row['id']) . "</span></td>";
                                    echo "<td><span>" . htmlspecialchars($row['emri']) . "</span></td>";
                                    echo "<td><span>" . htmlspecialchars($row['mbiemri']) . "</span></td>";
                                    echo "<td><span>" . htmlspecialchars($row['numri_i_telefonit']) . "</span></td>";
                                    echo "<td><span>" . htmlspecialchars($row['numri_personal']) . "</span></td>";
                                    echo "<td><span>" . htmlspecialchars($row['vepra']) . "</span></td>";
                                    echo "<td><span class='date-span'>" . htmlspecialchars($row['data']) . "</span></td>";
                                    echo "<td><span>" . htmlspecialchars($row['shenim']) . "</span></td>";
                                    echo "<td>";
                                    if ($row['nenshkrimi']) {
                                        echo "<a href='view_signature_song.php?id=" . htmlspecialchars($row['id']) . "' target='_blank'>Shiko Nënshkrimin</a>";
                                    } else {
                                        echo "Pa Nënshkrim";
                                    }
                                    echo "</td>";
                                    echo "<td>";
                                    if ($row['kontrata_PDF']) {
                                        echo "<a href='view_pdf_song.php?id=" . htmlspecialchars($row['id']) . "' target='_blank'>Shiko PDF</a>";
                                    } else {
                                        echo "Pa PDF";
                                    }
                                    echo "</td>";
                                    echo "<td><span>" . htmlspecialchars($row['perqindja']) . "</span></td>";
                                    echo "<td><span>" . htmlspecialchars($row['klient_emri']) . "</span></td>";
                                    echo "<td><span>" . htmlspecialchars($row['klient_emriart']) . "</span></td>";
                                    echo "<td><span>" . htmlspecialchars($row['klient_youtube']) . "</span></td>";
                                    echo "<td><span>" . htmlspecialchars($row['pdf_file']) . "</span></td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='15'>Nuk u Gjetën Kontrata të Këngëve për Përdoruesin</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-4 empty-state">
                    <h5>Nuk u Gjetën Kontrata të Këngëve për Përdoruesin</h5>
                    <p class="text-muted">Kontrata të këngëve nuk janë shtuar ende për këtë përdorues.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php include 'footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
<script>
    $(document).ready(function() {
        $('#songContractsTable').DataTable({
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
    });
</script>
<?php
$conn->close();
?>