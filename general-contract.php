<?php
include 'connection.php';
include 'header.php';
include 'sidebar.php';
// Fetch data from kontrata_gjenerale table
$sql = "SELECT * FROM kontrata_gjenerale";
$result = $conn->query($sql);
?>
<div class="col-md-10 main-content">
    <h2>General Contracts Management</h2>
    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Emri</th>
                    <th>Mbiemri</th>
                    <th>ID Kontrates</th>
                    <th>Data e Krijimit</th>
                    <th>Youtube ID</th>
                    <th>Artisti</th>
                    <th>TVSH</th>
                    <th>Pronari Xhirollogarise</th>
                    <th>Numri Xhirollogarise</th>
                    <th>Kodi Swift</th>
                    <th>IBAN</th>
                    <th>Emri Bankes</th>
                    <th>Adresa Bankes</th>
                    <th>Numri Tel</th>
                    <th>Numri Personal</th>
                    <th>Email</th>
                    <th>Nenshkrimi</th>
                    <th>Shenim</th>
                    <th>Shteti</th>
                    <th>Kohezgjatja</th>
                    <th>Lloji Dokumentit</th>
                    <th>Document Path</th>
                    <th>ID Regjistruesit</th>
                    <th>Emri Regjistruesit</th>
                    <th>Data e Nenshkrimit</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['emri']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['mbiemri']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['id_kontrates']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['data_e_krijimit']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['youtube_id']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['artisti']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['tvsh']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['pronari_xhirollogarise']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['numri_xhirollogarise']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['kodi_swift']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['iban']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['emri_bankes']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['adresa_bankes']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['numri_tel']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['numri_personal']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                        // Nenshkrimi (Signature) - Display as link if path available, or "No Signature"
                        echo "<td>";
                        if ($row['nenshkrimi']) {
                            echo "<a href='view_signature.php?id=" . htmlspecialchars($row['id']) . "' target='_blank'>View Signature</a>";
                        } else {
                            echo "No Signature";
                        }
                        echo "</td>";
                        echo "<td>" . htmlspecialchars($row['shenim']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['shteti']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['kohezgjatja']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['lloji_dokumentit']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['document_path']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['id_regjistruesit']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['emri_regjistruesit']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['data_e_nenshkrimit']) . "</td>";
                        echo "<td>
                                <button class='btn btn-primary btn-sm'>Edit</button>
                                <button class='btn btn-danger btn-sm'>Delete</button>
                              </td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='27'>No general contracts found</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
    <h3>Add New General Contract</h3>
    <form action="insert_general_contract.php" method="post" enctype="multipart/form-data">
        <div class="row mb-3">
            <div class="col-md-4">
                <label for="emri" class="form-label">Emri</label>
                <input type="text" class="form-control" id="emri" name="emri" required>
            </div>
            <div class="col-md-4">
                <label for="mbiemri" class="form-label">Mbiemri</label>
                <input type="text" class="form-control" id="mbiemri" name="mbiemri" required>
            </div>
            <div class="col-md-4">
                <label for="id_kontrates" class="form-label">ID Kontrates</label>
                <input type="text" class="form-control" id="id_kontrates" name="id_kontrates" required>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-4">
                <label for="data_e_krijimit" class="form-label">Data e Krijimit</label>
                <input type="date" class="form-control" id="data_e_krijimit" name="data_e_krijimit" required>
            </div>
            <div class="col-md-4">
                <label for="youtube_id" class="form-label">Youtube ID</label>
                <input type="text" class="form-control" id="youtube_id" name="youtube_id">
            </div>
            <div class="col-md-4">
                <label for="artisti" class="form-label">Artisti</label>
                <input type="text" class="form-control" id="artisti" name="artisti">
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-4">
                <label for="tvsh" class="form-label">TVSH</label>
                <input type="number" class="form-control" id="tvsh" name="tvsh">
            </div>
            <div class="col-md-4">
                <label for="pronari_xhirollogarise" class="form-label">Pronari Xhirollogarise</label>
                <input type="text" class="form-control" id="pronari_xhirollogarise" name="pronari_xhirollogarise">
            </div>
            <div class="col-md-4">
                <label for="numri_xhirollogarise" class="form-label">Numri Xhirollogarise</label>
                <input type="text" class="form-control" id="numri_xhirollogarise" name="numri_xhirollogarise">
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-4">
                <label for="kodi_swift" class="form-label">Kodi Swift</label>
                <input type="text" class="form-control" id="kodi_swift" name="kodi_swift">
            </div>
            <div class="col-md-4">
                <label for="iban" class="form-label">IBAN</label>
                <input type="text" class="form-control" id="iban" name="iban">
            </div>
            <div class="col-md-4">
                <label for="emri_bankes" class="form-label">Emri Bankes</label>
                <input type="text" class="form-control" id="emri_bankes" name="emri_bankes">
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-4">
                <label for="adresa_bankes" class="form-label">Adresa Bankes</label>
                <input type="text" class="form-control" id="adresa_bankes" name="adresa_bankes">
            </div>
            <div class="col-md-4">
                <label for="numri_tel" class="form-label">Numri Tel</label>
                <input type="text" class="form-control" id="numri_tel" name="numri_tel">
            </div>
            <div class="col-md-4">
                <label for="numri_personal" class="form-label">Numri Personal</label>
                <input type="text" class="form-control" id="numri_personal" name="numri_personal">
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-4">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email">
            </div>
            <div class="col-md-4">
                <label for="nenshkrimi" class="form-label">Nenshkrimi (Signature)</label>
                <input type="file" class="form-control" id="nenshkrimi" name="nenshkrimi">
            </div>
            <div class="col-md-4">
                <label for="shenim" class="form-label">Shenim</label>
                <input type="text" class="form-control" id="shenim" name="shenim">
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-4">
                <label for="shteti" class="form-label">Shteti</label>
                <input type="text" class="form-control" id="shteti" name="shteti">
            </div>
            <div class="col-md-4">
                <label for="kohezgjatja" class="form-label">Kohezgjatja (in months)</label>
                <input type="number" class="form-control" id="kohezgjatja" name="kohezgjatja">
            </div>
            <div class="col-md-4">
                <label for="lloji_dokumentit" class="form-label">Lloji Dokumentit</label>
                <input type="text" class="form-control" id="lloji_dokumentit" name="lloji_dokumentit" required>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-4">
                <label for="document_path" class="form-label">Document Path</label>
                <input type="text" class="form-control" id="document_path" name="document_path">
            </div>
            <div class="col-md-4">
                <label for="id_regjistruesit" class="form-label">ID Regjistruesit</label>
                <input type="text" class="form-control" id="id_regjistruesit" name="id_regjistruesit">
            </div>
            <div class="col-md-4">
                <label for="emri_regjistruesit" class="form-label">Emri Regjistruesit</label>
                <input type="text" class="form-control" id="emri_regjistruesit" name="emri_regjistruesit">
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-4">
                <label for="data_e_nenshkrimit" class="form-label">Data e Nenshkrimit</label>
                <input type="text" class="form-control" id="data_e_nenshkrimit" name="data_e_nenshkrimit">
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Add Contract</button>
    </form>
</div>
<?php include 'footer.php'; ?>
<?php
$conn->close();
?>