<?php
include 'connection.php';
include 'header.php';
include 'sidebar.php';

// Create a class for CSV data
class CSVData {
    public $id;
    public $client_id;
    public $client_name;
    public $username;
    public $youtube_id;
    public $month;
    public $year;
    public $store;
    public $label;
    public $organization_name;
    public $artist;
    public $title;
    public $release;
    public $mix;
    public $upc_number;
    public $isrc_number;
    public $country;
    public $type;
    public $items;
    public $currency;
    public $total_eur;
    public $total_due_to_pay_eur;
    public $date_added;
      public function __construct($data) {
        // Safely assign properties with default values if keys don't exist
        $this->id = isset($data['id']) ? $data['id'] : null;
        $this->client_id = isset($data['client_id']) ? $data['client_id'] : null;
        $this->client_name = isset($data['client_name']) ? $data['client_name'] : '';
        $this->username = isset($data['username']) ? $data['username'] : '';
        $this->youtube_id = isset($data['youtube_id']) ? $data['youtube_id'] : '';
        $this->month = isset($data['month']) ? $data['month'] : '';
        $this->year = isset($data['year']) ? $data['year'] : '';
        $this->store = isset($data['store']) ? $data['store'] : '';
        $this->label = isset($data['label']) ? $data['label'] : '';
        $this->organization_name = isset($data['organization_name']) ? $data['organization_name'] : '';
        $this->artist = isset($data['artist']) ? $data['artist'] : '';
        $this->title = isset($data['title']) ? $data['title'] : '';
        $this->release = isset($data['release']) ? $data['release'] : '';
        $this->mix = isset($data['mix']) ? $data['mix'] : '';
        $this->upc_number = isset($data['upc_number']) ? $data['upc_number'] : '';
        $this->isrc_number = isset($data['isrc_number']) ? $data['isrc_number'] : '';
        $this->country = isset($data['country']) ? $data['country'] : '';
        $this->type = isset($data['type']) ? $data['type'] : '';
        $this->items = isset($data['items']) ? $data['items'] : 0;
        $this->currency = isset($data['currency']) ? $data['currency'] : 'EUR';
        $this->total_eur = isset($data['total_eur']) ? $data['total_eur'] : 0.00;
        $this->total_due_to_pay_eur = isset($data['total_due_to_pay_eur']) ? $data['total_due_to_pay_eur'] : 0.00;
        $this->date_added = isset($data['date_added']) ? $data['date_added'] : date('Y-m-d H:i:s');
    }
    
    // Get country code for flag display (ISO 3166-1 alpha-2)
    public function getCountryCode() {
        $countryMap = [
            'United States' => 'us',
            'United Kingdom' => 'gb',
            'Germany' => 'de',
            'France' => 'fr',
            'Italy' => 'it',
            'Spain' => 'es',
            'Japan' => 'jp',
            'Canada' => 'ca',
            'Australia' => 'au',
            'Brazil' => 'br',
            'Mexico' => 'mx',
            'Russia' => 'ru',
            'China' => 'cn',
            'India' => 'in',
            'South Korea' => 'kr',
            'Netherlands' => 'nl',
            'Sweden' => 'se',
            'Norway' => 'no',
            'Denmark' => 'dk',
            'Poland' => 'pl',
            'Belgium' => 'be',
            'Switzerland' => 'ch',
            'Austria' => 'at',
            // Add more as needed
        ];
        
        return isset($countryMap[$this->country]) ? $countryMap[$this->country] : 'globe';
    }
}

// Repository class to handle database operations
class CSVDataRepository {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    public function getCSVDataByUserId($user_id, $limit = null, $offset = null) {
        $sql = "SELECT csv_data.*
                FROM csv_data
                WHERE csv_data.client_id = ?
                ORDER BY csv_data.date_added DESC";
                
        if ($limit !== null) {
            $sql .= " LIMIT ?";
            if ($offset !== null) {
                $sql .= " OFFSET ?";
            }
        }
        
        $stmt = $this->conn->prepare($sql);
        
        if ($limit !== null && $offset !== null) {
            $stmt->bind_param("iii", $user_id, $limit, $offset);
        } elseif ($limit !== null) {
            $stmt->bind_param("ii", $user_id, $limit);
        } else {
            $stmt->bind_param("i", $user_id);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        $csvData = [];
        while ($row = $result->fetch_assoc()) {
            $csvData[] = new CSVData($row);
        }
        
        $stmt->close();
        return $csvData;
    }
    
    public function getCSVDataSummary($user_id) {
        $sql = "SELECT 
                    SUM(total_eur) as total_income,
                    SUM(total_due_to_pay_eur) as total_due,
                    COUNT(id) as total_entries,
                    COUNT(DISTINCT store) as unique_stores,
                    COUNT(DISTINCT country) as unique_countries
                FROM csv_data
                WHERE client_id = ?";
                
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $summary = $result->fetch_assoc();
        $stmt->close();
        
        return $summary;
    }
    
    public function getTopPerformingCountries($user_id, $limit = 5) {
        $sql = "SELECT 
                    country, 
                    SUM(total_eur) as total_income,
                    COUNT(*) as entry_count
                FROM csv_data
                WHERE client_id = ?
                GROUP BY country
                ORDER BY total_income DESC
                LIMIT ?";
                
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $user_id, $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $countries = [];
        while ($row = $result->fetch_assoc()) {
            $countries[] = $row;
        }
        
        $stmt->close();
        return $countries;
    }
    
    public function getDataForServerSideDatatables($user_id, $start, $length, $search, $order_column, $order_dir) {
        // Column mapping for ordering
        $columns = [
            0 => 'month', 
            1 => 'year',
            2 => 'store', 
            3 => 'artist', 
            4 => 'title', 
            5 => 'country', 
            6 => 'items', 
            7 => 'total_eur', 
            8 => 'total_due_to_pay_eur'
        ];
        
        // Base query
        $sql = "SELECT SQL_CALC_FOUND_ROWS *
                FROM csv_data
                WHERE client_id = ?";
        
        // Add search condition if provided
        if (!empty($search)) {
            $search_term = "%$search%";
            $sql .= " AND (
                        month LIKE ? OR
                        year LIKE ? OR
                        store LIKE ? OR
                        artist LIKE ? OR
                        title LIKE ? OR
                        country LIKE ?
                    )";
        }
        
        // Add ordering
        if (isset($columns[$order_column])) {
            $sql .= " ORDER BY " . $columns[$order_column] . " " . $order_dir;
        } else {
            $sql .= " ORDER BY date_added DESC";
        }
        
        // Add pagination
        $sql .= " LIMIT ? OFFSET ?";
        
        $stmt = $this->conn->prepare($sql);
        
        // Bind parameters
        if (!empty($search)) {
            $search_term = "%$search%";
            $stmt->bind_param("issssssii", $user_id, $search_term, $search_term, $search_term, $search_term, $search_term, $search_term, $length, $start);
        } else {
            $stmt->bind_param("iii", $user_id, $length, $start);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        // Get total records (without filtering)
        $total_query = "SELECT COUNT(*) as count FROM csv_data WHERE client_id = ?";
        $total_stmt = $this->conn->prepare($total_query);
        $total_stmt->bind_param("i", $user_id);
        $total_stmt->execute();
        $total_result = $total_stmt->get_result()->fetch_assoc();
        $recordsTotal = $total_result['count'];
        
        // Get total filtered records
        $filtered_query = "SELECT FOUND_ROWS() as count";
        $filtered_result = $this->conn->query($filtered_query)->fetch_assoc();
        $recordsFiltered = $filtered_result['count'];
        
        // Build data array
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $csvData = new CSVData($row);
            $data[] = $csvData;
        }
        
        $stmt->close();
        $total_stmt->close();
        
        return [
            'data' => $data,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered
        ];
    }
}

// Get current user's ID from session
$user_id = $_SESSION['user_id'];

// Create repository instance and get data
$csvDataRepo = new CSVDataRepository($conn);
// Get only limited data for initial display to improve performance
$initialCsvData = $csvDataRepo->getCSVDataByUserId($user_id, 500);
$summary = $csvDataRepo->getCSVDataSummary($user_id);
$topCountries = $csvDataRepo->getTopPerformingCountries($user_id, 10);

// Get monthly data for chart
$monthlyData = [];
$storeData = [];
$yearlyData = [];
$currentYear = date('Y');
$previousYear = $currentYear - 1;

// Initialize monthlyData for all months of current and previous year
$allMonths = [
    'January', 'February', 'March', 'April', 'May', 'June', 
    'July', 'August', 'September', 'October', 'November', 'December'
];

foreach ($allMonths as $month) {
    $monthlyData[$currentYear][$month] = 0;
    $monthlyData[$previousYear][$month] = 0;
}

foreach ($initialCsvData as $data) {
    $period = $data->month . ' ' . $data->year;
    $year = $data->year;
    $month = $data->month;
    
    // Populate monthly data by year
    if (!isset($monthlyData[$year][$month])) {
        $monthlyData[$year][$month] = 0;
    }
    $monthlyData[$year][$month] += $data->total_eur;
    
    // Populate store data
    if (!isset($storeData[$data->store])) {
        $storeData[$data->store] = 0;
    }
    $storeData[$data->store] += $data->total_eur;
    
    // Aggregate yearly data
    if (!isset($yearlyData[$year])) {
        $yearlyData[$year] = 0;
    }
    $yearlyData[$year] += $data->total_eur;
}

// Sort yearly data by year
ksort($yearlyData);

// Prepare monthly series data for ApexCharts
$currentYearValues = [];
$previousYearValues = [];
foreach ($allMonths as $month) {
    $currentYearValues[] = isset($monthlyData[$currentYear][$month]) ? round($monthlyData[$currentYear][$month], 2) : 0;
    $previousYearValues[] = isset($monthlyData[$previousYear][$month]) ? round($monthlyData[$previousYear][$month], 2) : 0;
}

// Prepare top 5 stores for donut chart
arsort($storeData);
$topStores = array_slice($storeData, 0, 5);
$otherStores = array_sum(array_slice($storeData, 5));
if ($otherStores > 0) {
    $topStores['Others'] = $otherStores;
}

$storeLabels = json_encode(array_keys($topStores));
$storeValues = json_encode(array_values($topStores));

// Prepare yearly growth data
$yearLabels = json_encode(array_keys($yearlyData));
$yearValues = json_encode(array_values($yearlyData));

// Prepare country data for map chart
$countryData = [];
foreach ($topCountries as $countryInfo) {
    $countryData[] = [
        'country' => $countryInfo['country'],
        'value' => round($countryInfo['total_income'], 2)
    ];
}
$countryChartData = json_encode($countryData);
?>

<style>
    #csvDataTable th,
    #csvDataTable td {
        text-align: left !important;
        vertical-align: middle;
        padding: 0.6rem;
    }

    #csvDataTable th {
        font-size: 1rem;
        font-weight: 600;
        color: #333;
        border-bottom: 2px solid #eee;
        white-space: nowrap;
    }

    #csvDataTable td {
        font-size: 0.9rem;
    }

    .money-amount {
        font-weight: bold;
        color: #28a745;
        white-space: nowrap;
    }

    .money-amount::before {
        content: '€';
        margin-right: 2px;
    }    .store-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.25rem 0.75rem;
        border-radius: 50rem;
        font-weight: 600;
        text-align: center;
        white-space: nowrap;
        font-size: 0.8125rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        transition: all 0.2s;
    }
    
    .store-badge:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    
    .store-badge::before {
        content: '';
        display: inline-block;
        width: 14px;
        height: 14px;
        border-radius: 50%;
        margin-right: 6px;
    }

    .fitvids {
        background-color: #FFE0E3;
        color: #E01E5A;
    }
    
    .fitvids::before {
        background-color: #E01E5A;
    }
    
    .itunes {
        background-color: #EDF5FF;
        color: #007AFF;
    }
    
    .itunes::before {
        background-color: #007AFF;
    }
    
    .spotify {
        background-color: #E8F8EF;
        color: #1DB954;
    }
    
    .spotify::before {
        background-color: #1DB954;
    }
    
    .amazon {
        background-color: #FFF8E0;
        color: #FF9900;
    }
    
    .amazon::before {
        background-color: #FF9900;
    }
    
    .youtube {
        background-color: #FFEEEE;
        color: #FF0000;
    }
    
    .youtube::before {
        background-color: #FF0000;
    }
    
    .tiktok {
        background-color: #F0F2F5;
        color: #000000;
    }
    
    .tiktok::before {
        background-color: #000000;
    }
    
    .deezer {
        background-color: #EEFAFF;
        color: #00C7F2;
    }
    
    .deezer::before {
        background-color: #00C7F2;
    }
    
    .google {
        background-color: #F1F3F4;
        color: #4285F4;
    }
    
    .google::before {
        background-color: #4285F4;
    }
    
    .pandora {
        background-color: #EBF9FF;
        color: #00A0EE;
    }
    
    .pandora::before {
        background-color: #00A0EE;
    }
    
    .soundcloud {
        background-color: #FFF1E6;
        color: #FF5500;
    }
    
    .soundcloud::before {
        background-color: #FF5500;
    }
    
    .tidal {
        background-color: #E6F0FF;
        color: #00FFFF;
    }
    
    .tidal::before {
        background-color: #00FFFF;
    }
    
    .facebook {
        background-color: #E7F3FF;
        color: #1877F2;
    }
    
    .facebook::before {
        background-color: #1877F2;
    }
    
    .instagram {
        background-color: #F8E7FF;
        color: #C13584;
    }
    
    .instagram::before {
        background-color: #C13584;
    }

    .table-responsive {
        overflow-x: auto;
    }

    .card {
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        border: none;
        border-radius: 0.5rem;
        overflow: hidden;
        margin-bottom: 1.5rem;
        transition: transform 0.3s, box-shadow 0.3s;
    }

    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }

    .card-body {
        padding: 1.25rem;
    }

    .card-header {
        padding: 1rem 1.25rem;
        font-weight: 600;
        background-color: rgba(0, 0, 0, 0.03);
        border-bottom: 1px solid rgba(0, 0, 0, 0.125);
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

    .chart-container {
        position: relative;
        height: 350px;
        width: 100%;
        margin-bottom: 20px;
    }
    
    .mini-chart {
        height: 200px;
    }

    .summary-card {
        transition: all 0.3s;
        border-left: 4px solid #007bff;
    }

    .summary-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }

    .summary-icon {
        font-size: 2.5rem;
        opacity: 0.7;
    }
    
    .country-flag {
        width: 24px;
        height: 16px;
        margin-right: 5px;
        border-radius: 2px;
        box-shadow: 0 0 3px rgba(0,0,0,0.2);
    }
    
    /* Loading indicator for DataTables */
    .dataTables_processing {
        background-color: rgba(255, 255, 255, 0.9) !important;
        border-radius: 5px !important;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1) !important;
        z-index: 1000 !important;
    }
    
    /* Custom table striping */
    #csvDataTable tbody tr:nth-of-type(odd) {
        background-color: rgba(0, 0, 0, 0.02);
    }
    
    /* Improved filters */
    .dataTables_filter input {
        border: 1px solid #ddd;
        border-radius: 4px;
        padding: 6px 12px;
        margin-left: 8px;
    }
    
    .dataTables_length select {
        border: 1px solid #ddd;
        border-radius: 4px;
        padding: 6px 12px;
        margin: 0 5px;
    }
    
    /* Animation for new data */
    @keyframes highlightRow {
        0% {
            background-color: rgba(92, 184, 92, 0.2);
        }
        100% {
            background-color: transparent;
        }
    }
    
    .highlight-new {
        animation: highlightRow 2s ease;
    }
    
    .apexcharts-tooltip {
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.15) !important;
        border: none !important;
    }
    
    .cell-truncate {
        max-width: 150px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
</style>

<div class="col-md-10 main-content">
    <div class="d-flex justify-content-between align-items-center mb-3 fade-in">
        <div>
            <h3 class="fw-bold text-primary">CSV Income</h3>
            <p class="text-muted mb-0">Menaxhoni dhe rishikoni të dhënat e të ardhurave CSV</p>
        </div>
        <div>
            <button type="button" class="btn btn-sm btn-outline-secondary" id="refreshData">
                <i class="bi bi-arrow-clockwise"></i> Refresh Data
            </button>
            <div class="btn-group ms-2">
                <button type="button" class="btn btn-sm btn-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-download"></i> Export
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="#" id="exportCSV"><i class="bi bi-file-earmark-spreadsheet"></i> CSV</a></li>
                    <li><a class="dropdown-item" href="#" id="exportExcel"><i class="bi bi-file-earmark-excel"></i> Excel</a></li>
                    <li><a class="dropdown-item" href="#" id="exportPDF"><i class="bi bi-file-earmark-pdf"></i> PDF</a></li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm border-0 rounded-lg slide-up summary-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="text-muted mb-1">Total Income</h6>
                            <h4 class="mb-0">€<?= number_format($summary['total_income'] ?? 0, 2) ?></h4>
                        </div>
                        <div class="summary-icon text-primary">
                            <i class="bi bi-cash-stack"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 rounded-lg slide-up summary-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="text-muted mb-1">Total Due</h6>
                            <h4 class="mb-0">€<?= number_format($summary['total_due'] ?? 0, 2) ?></h4>
                        </div>
                        <div class="summary-icon text-success">
                            <i class="bi bi-wallet2"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 rounded-lg slide-up summary-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="text-muted mb-1">Entries</h6>
                            <h4 class="mb-0"><?= number_format($summary['total_entries'] ?? 0) ?></h4>
                        </div>
                        <div class="summary-icon text-info">
                            <i class="bi bi-file-earmark-text"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 rounded-lg slide-up summary-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="text-muted mb-1">Unique Stores</h6>
                            <h4 class="mb-0"><?= number_format($summary['unique_stores'] ?? 0) ?></h4>
                            <div class="small text-muted mt-1"><?= number_format($summary['unique_countries'] ?? 0) ?> countries</div>
                        </div>
                        <div class="summary-icon text-warning">
                            <i class="bi bi-shop"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 rounded-lg slide-up">
                <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Monthly Income Trends</h5>
                    <div class="btn-group btn-group-sm">
                        <button type="button" class="btn btn-outline-secondary active" data-view="month">Month</button>
                        <button type="button" class="btn btn-outline-secondary" data-view="year">Year</button>
                    </div>
                </div>
                <div class="card-body">
                    <div id="monthlyIncomeChart" class="chart-container"></div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 rounded-lg slide-up">
                <div class="card-header bg-light py-2">
                    <h5 class="mb-0">Income by Store</h5>
                </div>
                <div class="card-body">
                    <div id="storeIncomeChart" class="chart-container"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Charts Row -->
    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 rounded-lg slide-up">
                <div class="card-header bg-light py-2">
                    <h5 class="mb-0">Global Income Distribution</h5>
                </div>
                <div class="card-body">
                    <div id="worldMap" class="chart-container"></div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 rounded-lg slide-up">
                <div class="card-header bg-light py-2">
                    <h5 class="mb-0">Top Performing Countries</h5>
                </div>
                <div class="card-body">
                    <div id="countryBarChart" class="chart-container"></div>
                    <div class="mt-3">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Country</th>
                                    <th>Income</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($topCountries as $country): ?>
                                <tr>
                                    <td>
                                        <?php $countryCode = (new CSVData(['country' => $country['country']]))->getCountryCode(); ?>
                                        <img src="https://flagcdn.com/24x18/<?= $countryCode ?>.png" 
                                             class="country-flag" 
                                             alt="<?= htmlspecialchars($country['country']) ?>"
                                             onerror="this.onerror=null; this.src='https://flagcdn.com/24x18/globe.png';">
                                        <?= htmlspecialchars($country['country']) ?>
                                    </td>
                                    <td class="money-amount"><?= number_format($country['total_income'], 2) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Table Card -->
    <div class="card shadow-sm border-0 rounded-lg slide-up">
        <div class="card-header bg-light py-2">
            <h5 class="mb-0">All CSV Income Data</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="csvDataTable" class="table table-striped table-hover w-100">
                    <thead>
                        <tr>
                            <th>Month</th>
                            <th>Year</th>
                            <th>Store</th>
                            <th>Artist</th>
                            <th>Title</th>
                            <th>Country</th>
                            <th>Items</th>
                            <th>Total (EUR)</th>
                            <th>Due to Pay (EUR)</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>

<!-- DataTables JS and extensions -->
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.colVis.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.bootstrap5.min.css">

<!-- ApexCharts -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<!-- Country flags -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/flag-icon-css/3.5.0/css/flag-icon.min.css"></script>

<script>
    $(document).ready(function() {        // Store badge color mapping with enhanced styling
        const storeBadgeClasses = {
            'Spotify': 'spotify',
            'iTunes': 'itunes',
            'Apple Music': 'itunes',
            'YouTube': 'youtube',
            'YouTube Music': 'youtube',
            'Amazon': 'amazon',
            'Amazon Music': 'amazon',
            'TikTok': 'tiktok',
            'FitVids': 'fitvids',
            'Deezer': 'deezer',
            'Google Play': 'google',
            'Apple': 'itunes',
            'Pandora': 'pandora',
            'SoundCloud': 'soundcloud',
            'Tidal': 'tidal',
            'Facebook': 'facebook',
            'Instagram': 'instagram'
        };
        
        // Get store CSS class
        function getStoreBadgeClass(store) {
            return storeBadgeClasses[store] || '';
        }
        
        // Format money amount
        function formatMoney(amount) {
            return new Intl.NumberFormat('de-DE', { 
                style: 'currency', 
                currency: 'EUR',
                minimumFractionDigits: 2
            }).format(amount);
        }
        
        // Initialize DataTable with server-side processing
        const table = $('#csvDataTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: 'get_csv_data.php',
                type: 'POST',
                data: function(d) {
                    d.action = 'get_csv_data';
                    return d;
                },
                error: function (xhr, error, thrown) {
                    console.error('DataTables ERROR:', error, thrown);
                    if (xhr.responseText) {
                        console.error('Server Response:', xhr.responseText);
                    }
                }
            },
            pageLength: 25,
            deferRender: true,
            columns: [
                { data: 'month' },
                { data: 'year' },
                { 
                    data: 'store',
                    render: function(data, type, row) {
                        if (type === 'display') {
                            const badgeClass = getStoreBadgeClass(data);
                            return `<span class="store-badge ${badgeClass}">${data}</span>`;
                        }
                        return data;
                    }
                },
                { 
                    data: 'artist',
                    render: function(data, type, row) {
                        if (type === 'display' && data && data.length > 20) {
                            return `<span class="cell-truncate" title="${data}">${data.substring(0, 20)}...</span>`;
                        }
                        return data;
                    }
                },
                { 
                    data: 'title',
                    render: function(data, type, row) {
                        if (type === 'display' && data && data.length > 20) {
                            return `<span class="cell-truncate" title="${data}">${data.substring(0, 20)}...</span>`;
                        }
                        return data;
                    }
                },                { 
                    data: 'country',
                    render: function(data, type, row) {
                        if (type === 'display') {
                            if (!data) return '<span class="text-muted">Unknown</span>';
                            
                            const countryObj = new CSVData({ country: data });
                            const countryCode = countryObj.getCountryCode();
                            return `<div class="d-flex align-items-center">
                                      <img src="https://flagcdn.com/w20/${countryCode}.png" 
                                           class="country-flag me-2" 
                                           alt="${data}"
                                           loading="lazy"
                                           onerror="this.onerror=null; this.src='https://flagcdn.com/w20/globe.png';">
                                      <span>${data}</span>
                                   </div>`;
                        }
                        return data;
                    }
                },
                { 
                    data: 'items',
                    render: function(data, type, row) {
                        if (type === 'display') {
                            return new Intl.NumberFormat().format(data);
                        }
                        return data;
                    }
                },
                { 
                    data: 'total_eur',
                    render: function(data, type, row) {
                        if (type === 'display') {
                            return `<span class="money-amount">${formatMoney(data)}</span>`;
                        }
                        return data;
                    }
                },
                { 
                    data: 'total_due_to_pay_eur',
                    render: function(data, type, row) {
                        if (type === 'display') {
                            return `<span class="money-amount">${formatMoney(data)}</span>`;
                        }
                        return data;
                    }
                }
            ],
            order: [[0, 'desc'], [1, 'desc']],
            pageLength: 25,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
            deferRender: true,
            language: {
                search: "Kërko:",
                lengthMenu: "Shfaq _MENU_ rreshta",
                zeroRecords: "Nuk u gjetën të dhëna",
                info: "Duke shfaqur faqen _PAGE_ nga _PAGES_",
                infoEmpty: "Nuk ka të dhëna në dispozicion",
                infoFiltered: "(filtruar nga _MAX_ total rreshta)",
                processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Duke u ngarkuar...</span></div>',
                paginate: {
                    first: "E para",
                    last: "E fundit",
                    next: "Tjetra",
                    previous: "E mëparshme"
                }
            },
            dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
                 "<'row'<'col-sm-12'tr>>" +
                 "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
            buttons: [
                { extend: 'csv', className: 'btn-sm' },
                { extend: 'excel', className: 'btn-sm' },
                { extend: 'pdf', className: 'btn-sm' },
                { extend: 'print', className: 'btn-sm' }
            ]
        });
        
        // Export buttons functionality
        $('#exportCSV').on('click', function() {
            table.button('.buttons-csv').trigger();
        });
        
        $('#exportExcel').on('click', function() {
            table.button('.buttons-excel').trigger();
        });
        
        $('#exportPDF').on('click', function() {
            table.button('.buttons-pdf').trigger();
        });
        
        // Refresh data
        $('#refreshData').on('click', function() {
            table.ajax.reload();
        });
        
        // Monthly Income Apex Chart
        const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
        const currentYear = new Date().getFullYear();
        const previousYear = currentYear - 1;
        
        const monthlyChart = new ApexCharts(document.querySelector("#monthlyIncomeChart"), {
            chart: {
                height: 350,
                type: 'area',
                fontFamily: 'inherit',
                toolbar: {
                    show: true,
                    tools: {
                        download: true,
                        selection: true,
                        zoom: true,
                        zoomin: true,
                        zoomout: true,
                        pan: true
                    }
                },
                animations: {
                    enabled: true,
                    easing: 'easeinout',
                    speed: 800
                }
            },
            dataLabels: {
                enabled: false
            },
            stroke: {
                width: [3, 3],
                curve: 'smooth'
            },
            series: [{
                name: currentYear,
                data: <?= json_encode($currentYearValues) ?>
            }, {
                name: previousYear,
                data: <?= json_encode($previousYearValues) ?>
            }],
            xaxis: {
                categories: monthNames,
                labels: {
                    rotate: 0
                }
            },
            yaxis: {
                labels: {
                    formatter: function(val) {
                        return '€' + val.toFixed(0);
                    }
                }
            },
            tooltip: {
                y: {
                    formatter: function(value) {
                        return '€' + value.toFixed(2);
                    }
                }
            },
            legend: {
                position: 'top'
            },
            colors: ['#3498db', '#7f8c8d'],
            fill: {
                type: 'gradient',
                gradient: {
                    shade: 'light',
                    type: "vertical",
                    shadeIntensity: 0.25,
                    gradientToColors: ['#2c3e50', '#bdc3c7'],
                    inverseColors: false,
                    opacityFrom: 0.7,
                    opacityTo: 0.3
                }
            }
        });
        
        monthlyChart.render();
        
        // Store Income Donut Chart
        const donutChart = new ApexCharts(document.querySelector("#storeIncomeChart"), {
            chart: {
                type: 'donut',
                height: 350,
                fontFamily: 'inherit',
                animations: {
                    enabled: true,
                    easing: 'easeinout',
                    speed: 800,
                    animateGradually: {
                        enabled: true,
                        delay: 150
                    },
                    dynamicAnimation: {
                        enabled: true,
                        speed: 350
                    }
                }
            },
            series: <?= $storeValues ?>,
            labels: <?= $storeLabels ?>,
            colors: ['#2ecc71', '#3498db', '#9b59b6', '#e74c3c', '#f39c12', '#1abc9c', '#34495e'],
            legend: {
                position: 'bottom',
                horizontalAlign: 'center',
                offsetX: 0,
                offsetY: 0
            },
            plotOptions: {
                pie: {
                    donut: {
                        size: '50%',
                        labels: {
                            show: true,
                            name: {
                                show: true
                            },
                            value: {
                                show: true,
                                formatter: function(val) {
                                    return '€' + parseFloat(val).toFixed(2);
                                }
                            },
                            total: {
                                show: true,
                                formatter: function(w) {
                                    const total = w.globals.seriesTotals.reduce((a, b) => a + b, 0);
                                    return '€' + parseFloat(total).toFixed(2);
                                }
                            }
                        }
                    }
                }
            },
            dataLabels: {
                enabled: false
            },
            tooltip: {
                y: {
                    formatter: function(value) {
                        return '€' + value.toFixed(2);
                    }
                }
            },
            responsive: [{
                breakpoint: 480,
                options: {
                    chart: {
                        height: 250
                    },
                    legend: {
                        position: 'bottom'
                    }
                }
            }]
        });
        
        donutChart.render();
        
        // World Map Chart
        const mapChart = new ApexCharts(document.querySelector("#worldMap"), {
            chart: {
                height: 350,
                type: 'treemap',
                fontFamily: 'inherit'
            },
            legend: {
                show: false
            },
            title: {
                text: 'Income Distribution by Country',
                align: 'center'
            },
            dataLabels: {
                enabled: true,
                formatter: function(text, op) {
                    return [text, '€' + op.value.toFixed(2)];
                },
                style: {
                    fontSize: '12px'
                }
            },
            series: [
                {
                    data: <?= $countryChartData ?>
                }
            ],
            colors: ['#3498db'],
            plotOptions: {
                treemap: {
                    enableShades: true,
                    shadeIntensity: 0.5,
                    reverseNegativeShade: true,
                    distributed: false,
                    useFillColorAsStroke: false
                }
            },
            tooltip: {
                y: {
                    formatter: function(value) {
                        return '€' + value.toFixed(2);
                    }
                }
            }
        });
        
        mapChart.render();
        
        // Country Bar Chart
        const countryBarChart = new ApexCharts(document.querySelector("#countryBarChart"), {
            chart: {
                height: 200,
                type: 'bar',
                fontFamily: 'inherit',
                toolbar: {
                    show: false
                }
            },
            plotOptions: {
                bar: {
                    horizontal: true,
                    dataLabels: {
                        position: 'top',
                    },
                }
            },
            colors: ['#3498db'],
            dataLabels: {
                enabled: true,
                offsetX: 30,
                style: {
                    fontSize: '12px',
                    colors: ['#304758']
                },
                formatter: function(val) {
                    return '€' + parseFloat(val).toFixed(0);
                }
            },
            series: [{
                data: <?= json_encode(array_column($topCountries, 'total_income')) ?>
            }],
            xaxis: {
                categories: <?= json_encode(array_column($topCountries, 'country')) ?>,
                labels: {
                    formatter: function(val) {
                        return '€' + parseFloat(val).toFixed(0);
                    }
                }
            }
        });
        
        countryBarChart.render();
        
        // Toggle between monthly and yearly view
        $('.btn-group[data-view]').on('click', 'button', function() {
            const $this = $(this);
            const view = $this.data('view');
            
            // Toggle active state
            $this.addClass('active').siblings().removeClass('active');
            
            // Update chart based on selected view
            if (view === 'year') {
                monthlyChart.updateOptions({
                    chart: {
                        type: 'bar'
                    },
                    stroke: {
                        width: [0, 0],
                        curve: 'smooth'
                    },
                    xaxis: {
                        categories: <?= $yearLabels ?>,
                    },
                    series: [{
                        name: 'Yearly Income',
                        data: <?= $yearValues ?>
                    }]
                });
            } else {
                monthlyChart.updateOptions({
                    chart: {
                        type: 'area'
                    },
                    stroke: {
                        width: [3, 3],
                        curve: 'smooth'
                    },
                    xaxis: {
                        categories: monthNames,
                    },
                    series: [{
                        name: currentYear,
                        data: <?= json_encode($currentYearValues) ?>
                    }, {
                        name: previousYear,
                        data: <?= json_encode($previousYearValues) ?>
                    }]
                });
            }
        });
    });
      // Get country code utility function for DataTables
    function CSVData(data) {
        this.country = data.country || '';
        
        this.getCountryCode = function() {
            const countryMap = {
                'United States': 'us',
                'United Kingdom': 'gb',
                'Germany': 'de',
                'France': 'fr',
                'Italy': 'it',
                'Spain': 'es',
                'Japan': 'jp',
                'Canada': 'ca',
                'Australia': 'au',
                'Brazil': 'br',
                'Mexico': 'mx',
                'Russia': 'ru',
                'China': 'cn',
                'India': 'in',
                'South Korea': 'kr',
                'Netherlands': 'nl',
                'Sweden': 'se',
                'Norway': 'no',
                'Denmark': 'dk',
                'Poland': 'pl',
                'Belgium': 'be',
                'Switzerland': 'ch',
                'Austria': 'at',
                // Add 2-letter country codes
                'US': 'us',
                'GB': 'gb',
                'DE': 'de',
                'FR': 'fr',
                'IT': 'it',
                'ES': 'es',
                'JP': 'jp',
                'CA': 'ca',
                'AU': 'au',
                'BR': 'br',
                'MX': 'mx',
                'RU': 'ru',
                'CN': 'cn',
                'IN': 'in',
                'KR': 'kr',
                'NL': 'nl',
                'SE': 'se',
                'NO': 'no',
                'DK': 'dk',
                'PL': 'pl',
                'BE': 'be',
                'CH': 'ch',
                'AT': 'at',
                'XK': 'xk'
            };
            
            return countryMap[this.country] || 'globe';
        };
    }
</script>
