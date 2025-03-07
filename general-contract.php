<?php
include 'connection.php';
include 'header.php';
include 'sidebar.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
$user_id = $_SESSION['user_id'];

$sql = "SELECT kg.*, k.youtube AS klient_youtube
        FROM kontrata_gjenerale kg
        LEFT JOIN klientet k ON kg.youtube_id = k.youtube
        WHERE k.id = ?";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    die("Database error: " . $conn->error);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Kontratat e Përgjithshme</title>
    <style>
        #contractsTable th,
        #contractsTable td {
            text-align: left !important;
            vertical-align: middle;
            padding: 0.6rem;
        }

        #contractsTable th {
            font-size: 1rem;
            font-weight: bold;
            color: #333;
            border-bottom: 2px solid #eee;
        }

        #contractsTable td {
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
</head>

<body>
    <div class="col-md-10 main-content">
        <div class="d-flex justify-content-between align-items-center mb-3 fade-in">
            <div>
                <h3 class="fw-bold text-primary">Kontratat e Përgjithshme</h3>
                <p class="text-muted mb-0">Menaxho dhe shqyrto kontratat e përgjithshme</p>
            </div>
        </div>
        <div class="card shadow-sm border-0 rounded-lg slide-up">
            <div class="card-header bg-light py-2">
                <h5 class="mb-0">Të Gjitha Kontratat e Përgjithshme për Përdoruesin</h5>
            </div>
            <div class="card-body p-3">
                <?php if ($result && $result->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover animate-table" id="contractsTable">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Emri</th>
                                    <th>Mbiemri</th>
                                    <th>Data e Krijimit</th>
                                    <th>Youtube Klienti</th>
                                    <th>Artisti</th>
                                    <th>Email</th>
                                    <th>Nënshkrimi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <tr class="table-row-hover">
                                        <td>
                                            <a href="view_contract.php?id=<?php echo htmlspecialchars($row['id']); ?>" target="_blank">
                                                <span class="contract-id-span"><?php echo htmlspecialchars($row['id']); ?></span></a>
                                        </td>
                                        <td><?php echo htmlspecialchars($row['emri']); ?></td>
                                        <td><?php echo htmlspecialchars($row['mbiemri']); ?></td>
                                        <td><span class="date-span"><?php echo htmlspecialchars(date('d M Y', strtotime($row['data_e_krijimit']))); ?></span></td>
                                        <td><?php echo htmlspecialchars($row['klient_youtube']); ?></td>
                                        <td>
                                            <?php
                                            $artist_data_json = $row['artisti'];
                                            $artist_data = json_decode($artist_data_json, true);
                                            echo (is_array($artist_data) && isset($artist_data['emriart'])) ? htmlspecialchars($artist_data['emriart']) : "Informacioni i Artistit";
                                            ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                                        <td>
                                            <?php if ($row['nenshkrimi']): ?>
                                                <a href="view_signature.php?id=<?php echo htmlspecialchars($row['id']); ?>" target="_blank">Shiko</a>
                                            <?php else: ?>
                                                Pa Nënshkrim
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4 empty-state">
                        <h5>Nuk u Gjetën Kontrata të Përgjithshme për Përdoruesin</h5>
                        <p class="text-muted">Kontrata të përgjithshme nuk janë shtuar ende për këtë përdorues.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php include 'footer.php'; ?>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
            $('#contractsTable').DataTable({
                responsive: true,
                dom: `
                <'container-fluid'
                    <'row mb-3'
                        <'col-12 col-md-4 col-lg-3 d-flex align-items-center'<'me-3'l>>
                        <'col-12 col-md-4 col-lg-6 my-2 my-md-0 d-flex justify-content-center justify-content-md-center'B>
                        <'col-12 col-md-4 col-lg-3 d-flex justify-content-end'f>
                    >
                    <'row'
                        <'col-12'<'table-responsive'tr>>
                    >
                    <'row mt-3'
                        <'col-12 col-md-6'i>
                        <'col-12 col-md-6 d-flex justify-content-md-end'p>
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
    <?php $conn->close(); ?>
</body>

</html>