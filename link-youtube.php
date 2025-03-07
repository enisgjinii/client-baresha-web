<?php
include 'connection.php';
include 'header.php';
include 'sidebar.php';
$youtube_client_id = '889125415131-qfi8c11iibraqj82aal4fc8q4oic2m5u.apps.googleusercontent.com';
$youtube_client_secret = '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';
$redirect_uri = 'http://localhost/client-baresha-web/link-youtube.php';
$authorization_code = $_GET['code'] ?? '';
$access_token = $_SESSION['youtube_access_token'] ?? '';
$refresh_token = $_SESSION['youtube_refresh_token'] ?? '';
$channel_name = '';
$channel_thumbnail = '';
$view_count = 'N/A';
$estimated_revenue_total = 'N/A';
$revenue_data_daily = [];
$youtube_error_message = '';
$start_date = !empty($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-30 days'));
$end_date = !empty($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
function refreshYoutubeAccessToken($client_id, $client_secret, $refresh_token, $redirect_uri)
{
    $token_url = 'https://oauth2.googleapis.com/token';
    $params = [
        'client_id' => $client_id,
        'client_secret' => $client_secret,
        'refresh_token' => $refresh_token,
        'grant_type' => 'refresh_token',
        'redirect_uri' => $redirect_uri
    ];
    $ch = curl_init($token_url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    $curl_error = curl_error($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($curl_error) {
        return ['error' => 'cURL error: ' . $curl_error];
    }
    if ($http_code != 200) {
        return ['error' => 'HTTP error: ' . $http_code . ' - ' . $response];
    }
    return json_decode($response, true);
}
function fetchYoutubeApiData($api_url, $access_token, $client_id = null, $client_secret = null, $refresh_token = null, $redirect_uri = null, $retry = false)
{
    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $access_token,
        'Accept: application/json'
    ]);
    $response_json = curl_exec($ch);
    $curl_error = curl_error($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($curl_error) {
        return ['error' => 'cURL error: ' . $curl_error];
    }
    if ($http_code == 401 && !$retry && !empty($client_id) && !empty($client_secret) && !empty($refresh_token) && !empty($redirect_uri)) {
        $newTokenResponse = refreshYoutubeAccessToken($client_id, $client_secret, $refresh_token, $redirect_uri);
        if (!empty($newTokenResponse['access_token'])) {
            $_SESSION['youtube_access_token'] = $newTokenResponse['access_token'];
            $access_token = $newTokenResponse['access_token'];
            if (!empty($newTokenResponse['refresh_token'])) {
                $_SESSION['youtube_refresh_token'] = $newTokenResponse['refresh_token'];
            }
            return fetchYoutubeApiData($api_url, $access_token, $client_id, $client_secret, $refresh_token, $redirect_uri, true);
        } else {
            return ['error' => 'Failed to refresh access token.'];
        }
    }
    if ($http_code != 200) {
        return ['error' => 'HTTP error: ' . $http_code . ' - ' . $response_json];
    }
    return json_decode($response_json, true);
}
if ($authorization_code) {
    $token_url = 'https://oauth2.googleapis.com/token';
    $token_params = [
        'code' => $authorization_code,
        'client_id' => $youtube_client_id,
        'client_secret' => $youtube_client_secret,
        'redirect_uri' => $redirect_uri,
        'grant_type' => 'authorization_code'
    ];
    $ch = curl_init($token_url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($token_params));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $token_response_json = curl_exec($ch);
    $curl_error = curl_error($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($curl_error) {
        $youtube_error_message = 'Token exchange cURL error: ' . $curl_error;
    } elseif ($http_code != 200) {
        $youtube_error_message = 'Token exchange HTTP error: ' . $http_code . ' - ' . $token_response_json;
    }
    if (empty($youtube_error_message)) {
        $token_response = json_decode($token_response_json, true);
        if (!empty($token_response['access_token'])) {
            $access_token = $token_response['access_token'];
            $refresh_token = $token_response['refresh_token'] ?? '';
            $_SESSION['youtube_access_token'] = $access_token;
            $_SESSION['youtube_refresh_token'] = $refresh_token;
            $channel_info_url = 'https://www.googleapis.com/youtube/v3/channels?part=snippet,statistics&mine=true';
            $channel_data = fetchYoutubeApiData($channel_info_url, $access_token, $youtube_client_id, $youtube_client_secret, $refresh_token, $redirect_uri);
            if (!empty($channel_data['error'])) {
                $youtube_error_message .= ' Channel info error: ' . $channel_data['error'];
            } elseif (!empty($channel_data['items'][0])) {
                $channel_name = $channel_data['items'][0]['snippet']['title'];
                $channel_thumbnail = $channel_data['items'][0]['snippet']['thumbnails']['default']['url'];
                $view_count = number_format($channel_data['items'][0]['statistics']['viewCount']);
            } else {
                $channel_name = 'Error fetching channel name';
            }
        } else {
            $youtube_error_message = 'Failed to retrieve access token: ' . $token_response_json;
        }
    }
}
if ($access_token) {
    $analytics_url = 'https://youtubeanalytics.googleapis.com/v2/reports';
    $params_for_chart = [
        'ids' => 'channel==MINE',
        'startDate' => $start_date,
        'endDate' => $end_date,
        'metrics' => 'estimatedRevenue',
        'dimensions' => 'day',
        'sort' => 'day',
        'currency' => 'EUR'
    ];
    $chart_query = $analytics_url . '?' . http_build_query($params_for_chart);
    $analytics_data = fetchYoutubeApiData($chart_query, $access_token, $youtube_client_id, $youtube_client_secret, $refresh_token, $redirect_uri);
    if (!empty($analytics_data['error'])) {
        $youtube_error_message .= ' Revenue data error: ' . $analytics_data['error'];
    } elseif (!empty($analytics_data['rows'])) {
        $revenue_sum = 0;
        foreach ($analytics_data['rows'] as $row) {
            $date = $row[0];
            $revenue = $row[1];
            $revenue_data_daily[] = ['date' => $date, 'revenue' => $revenue];
            $revenue_sum += $revenue;
        }
        $estimated_revenue_total = number_format($revenue_sum, 2);
    } else {
        $estimated_revenue_total = 'No revenue data available';
    }
}
$oauth_url = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query([
    'client_id' => $youtube_client_id,
    'redirect_uri' => $redirect_uri,
    'scope' => 'https://www.googleapis.com/auth/youtube.readonly https://www.googleapis.com/auth/youtube.channel-memberships.creator https://www.googleapis.com/auth/yt-analytics.readonly https://www.googleapis.com/auth/yt-analytics-monetary.readonly',
    'response_type' => 'code',
    'access_type' => 'offline',
    'prompt' => 'consent'
]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>YouTube Analytics Dashboard</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <style>
        body {
            background-color: #f4f6f9
        }
        .link-youtube-card {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 8px 20px rgba(70, 70, 70, 0.08);
            margin-bottom: 2.5rem;
            overflow: hidden;
            border: 1px solid #e0e0e0
        }
        .link-youtube-card:hover {
            box-shadow: 0 12px 28px rgba(70, 70, 70, 0.12);
            transform: translateY(-3px);
            transition: all 0.35s ease-in-out
        }
        .link-youtube-card-header {
            padding: 1.75rem 2rem;
            background-color: #f9f9f9;
            border-bottom: 1px solid #e0e0e0
        }
        .link-youtube-card-title {
            font-size: 1.6rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 0
        }
        .link-youtube-card-body {
            padding: 2.5rem;
            text-align: center
        }
        .youtube-connect-btn {
            background-color: #e31b23;
            color: #fff;
            border: none;
            border-radius: 10px;
            padding: 1.1rem 2.2rem;
            font-size: 1.15rem;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.3s ease-in-out, transform 0.2s ease-in-out;
            margin-bottom: 1.2rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.08)
        }
        .youtube-connect-btn:hover {
            background-color: #c4171d;
            transform: scale(1.03);
            box-shadow: 0 5px 12px rgba(0, 0, 0, 0.12)
        }
        .youtube-connect-btn:active {
            transform: scale(1);
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.08)
        }
        .youtube-icon {
            width: 25px;
            height: 25px;
            margin-right: 12px;
            filter: brightness(0) invert(1)
        }
        .channel-info {
            margin-top: 2.5rem;
            padding: 2rem;
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            background-color: #fff;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05)
        }
        .channel-thumbnail {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 1.2rem;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            border: 3px solid #fff
        }
        .channel-name {
            font-size: 1.5rem;
            font-weight: 600;
            color: #3a4750;
            margin-bottom: 0.7rem
        }
        .channel-stats {
            font-size: 1.05rem;
            color: #777;
            margin-bottom: 0.5rem
        }
        .revenue-stats {
            margin-top: 2rem;
            padding: 2rem;
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            background-color: #fff;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05)
        }
        .revenue-stat-title {
            font-size: 1.4rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 1.5rem;
            text-align: left
        }
        .revenue-stat-item {
            margin-bottom: 1.2rem
        }
        .revenue-stat-label {
            font-weight: 500;
            color: #555;
            display: block;
            margin-bottom: 0.3rem;
            font-size: 1.05rem;
            text-align: left
        }
        .revenue-stat-value {
            font-size: 1.3rem;
            color: #008060;
            font-weight: 600;
            text-align: left
        }
        #revenueChart {
            width: 100%;
            height: 320px;
            margin-top: 2rem
        }
        #calendar {
            max-width: 900px;
            margin: 2rem auto
        }
        .fc-toolbar-title {
            font-size: 1.5rem;
            font-weight: 600
        }
        .fc-daygrid-day-number {
            font-size: 1rem;
            color: #333
        }
        .fc-event {
            font-size: 0.85rem
        }
        .main-content {
            padding: 3rem
        }
        .fade-in {
            animation: fadeIn ease 0.6s
        }
        .slide-up {
            animation: slideUp ease 0.6s
        }
        @keyframes fadeIn {
            0% {
                opacity: 0
            }
            100% {
                opacity: 1
            }
        }
        @keyframes slideUp {
            0% {
                transform: translateY(40px);
                opacity: 0
            }
            100% {
                transform: translateY(0);
                opacity: 1
            }
        }
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.85);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            backdrop-filter: blur(4px)
        }
        .loading-spinner {
            border: 6px solid #f3f3f3;
            border-top: 6px solid #3498db;
            border-radius: 50%;
            width: 60px;
            height: 60px;
            animation: spin 1.8s linear infinite
        }
        @keyframes spin {
            0% {
                transform: rotate(0deg)
            }
            100% {
                transform: rotate(360deg)
            }
        }
        .token-display {
            margin-top: 2.5rem;
            padding: 1.2rem;
            border: 1px dashed #ccc;
            border-radius: 8px;
            background-color: #fff;
            font-size: 0.9rem;
            word-wrap: break-word;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.06)
        }
        .token-title {
            font-weight: 600;
            margin-bottom: 0.7rem;
            color: #555
        }
        .token-display textarea.form-control {
            font-size: 0.85rem;
            background-color: #f0f0f0;
            border: 1px solid #ddd;
            border-radius: 6px;
            padding: 0.75rem;
            margin-bottom: 1rem;
            color: #444
        }
        .text-muted.mt-3,
        .text-muted.mt-2 {
            color: #777 !important;
            font-size: 0.9rem
        }
        .text-success {
            color: #28a745 !important;
            font-weight: 500
        }
        .alert-danger {
            border-radius: 6px;
            padding: 0.9rem 1.4rem;
            margin-bottom: 1.2rem;
            font-size: 0.9rem
        }
        .table th,
        .table td {
            vertical-align: middle;
            padding: 0.7rem 0.6rem;
            font-size: 0.95rem
        }
        .table-light thead th {
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6
        }
        .date-filter-form {
            max-width: none;
            display: flex;
            flex-wrap: wrap;
            gap: 0.8rem;
            align-items: center;
            margin-bottom: 1.5rem
        }
        .date-filter-form>div {
            flex-grow: 1;
            min-width: 140px
        }
        .date-filter-form button {
            height: auto;
            margin-top: 0
        }
        @media (max-width:768px) {
            .main-content {
                padding: 2rem
            }
            .link-youtube-card-header {
                padding: 1.2rem 1.5rem
            }
            .link-youtube-card-body {
                padding: 1.5rem
            }
            .channel-info,
            .revenue-stats {
                padding: 1.5rem
            }
            .token-display {
                margin-top: 2rem;
                padding: 1rem
            }
            .date-filter-form {
                flex-direction: column;
                align-items: stretch
            }
            .date-filter-form button {
                margin-top: 0.5rem
            }
        }
    </style>
</head>
<body>
    <div class="col-md-10 main-content">
        <div class="fade-in">
            <h2 class="fw-bold text-dark mb-3">YouTube Analytics Dashboard</h2>
            <p class="text-muted mb-4">Connect your YouTube channel to view EUR-based revenue insights, explore detailed charts, and see your revenue events on a calendar.</p>
            <form method="GET" class="date-filter-form">
                <?php if (!empty($authorization_code)): ?>
                    <input type="hidden" name="code" value="<?php echo htmlspecialchars($authorization_code); ?>">
                <?php endif; ?>
                <div>
                    <label for="start_date">Start Date</label>
                    <input type="date" id="start_date" name="start_date" class="form-control" value="<?php echo htmlspecialchars($start_date); ?>">
                </div>
                <div>
                    <label for="end_date">End Date</label>
                    <input type="date" id="end_date" name="end_date" class="form-control" value="<?php echo htmlspecialchars($end_date); ?>">
                </div>
                <div>
                    <button type="submit" class="btn btn-primary">Filter</button>
                </div>
            </form>
            <div class="link-youtube-card slide-up">
                <div class="link-youtube-card-header">
                    <h5 class="link-youtube-card-title">Connect Your Channel</h5>
                </div>
                <div class="link-youtube-card-body">
                    <?php if (!empty($youtube_error_message)): ?>
                        <script>
                            Toastify({
                                text: "<?php echo htmlspecialchars($youtube_error_message); ?>",
                                duration: 6000,
                                gravity: "top",
                                position: "right",
                                backgroundColor: "#d9534f",
                                close: true,
                                stopOnFocus: true
                            }).showToast();
                        </script>
                    <?php endif; ?>
                    <?php if (!$access_token): ?>
                        <p>To get started, link your YouTube channel.</p>
                        <a href="<?php echo htmlspecialchars($oauth_url); ?>" class="youtube-connect-btn">
                            <img src="https://upload.wikimedia.org/wikipedia/commons/7/75/YouTube_social_white_squircle_%282017%29.svg" alt="YouTube Icon" class="youtube-icon">
                            Connect with YouTube
                        </a>
                        <p class="text-muted mt-3"><i class="fas fa-info-circle me-1"></i> By connecting, you grant permission to securely view your YouTube channel data for analytics and insights within this dashboard.</p>
                    <?php else: ?>
                        <div class="channel-info">
                            <?php if ($channel_thumbnail): ?>
                                <img src="<?php echo htmlspecialchars($channel_thumbnail); ?>" alt="Channel Thumbnail" class="channel-thumbnail">
                            <?php endif; ?>
                            <?php if ($channel_name): ?>
                                <h4 class="channel-name"><?php echo htmlspecialchars($channel_name); ?></h4>
                            <?php endif; ?>
                            <?php if ($view_count != 'N/A'): ?>
                                <p class="channel-stats"><i class="fas fa-eye me-1"></i> Total Views: <?php echo htmlspecialchars($view_count); ?></p>
                            <?php endif; ?>
                            <p class="text-success"><i class="fas fa-check-circle me-1"></i> Channel successfully linked!</p>
                            <button class="youtube-connect-btn" onclick="disconnectYouTube()" style="background-color:#4285F4;">
                                <i class="fas fa-unlink youtube-icon"></i> Disconnect YouTube
                            </button>
                        </div>
                        <div class="revenue-stats">
                            <h4 class="revenue-stat-title"><i class="fas fa-chart-line me-2"></i> Revenue Overview</h4>
                            <div class="revenue-stat-item">
                                <span class="revenue-stat-label">Estimated Revenue (<?php echo htmlspecialchars($start_date . ' to ' . $end_date); ?>, EUR)</span>
                                <span class="revenue-stat-value">€<?php echo htmlspecialchars($estimated_revenue_total); ?></span>
                            </div>
                            <div id="revenueChart"></div>
                        </div>
                        <div id="calendar"></div>
                        <div class="token-display">
                            <p class="token-title">Access Token:</p>
                            <textarea rows="3" class="form-control" readonly><?php echo htmlspecialchars($access_token); ?></textarea>
                            <p class="token-title">Refresh Token:</p>
                            <textarea rows="3" class="form-control" readonly><?php echo htmlspecialchars($refresh_token); ?></textarea>
                            <p class="text-muted mt-2"><i class="fas fa-info-circle me-1"></i> These tokens are sensitive. Handle with care and do not expose them in production.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div id="loadingOverlay" class="loading-overlay" style="display: none;">
            <div class="loading-spinner"></div>
        </div>
    </div>
    <script>
        function disconnectYouTube() {
            window.location.href = 'logout-youtube.php'
        }
        function showLoading() {
            document.getElementById('loadingOverlay').style.display = 'flex'
        }
        function hideLoading() {
            document.getElementById('loadingOverlay').style.display = 'none'
        }
        document.addEventListener('DOMContentLoaded', function() {
            var revenueData = <?php echo json_encode($revenue_data_daily); ?>;
            if (revenueData && revenueData.length) {
                var revenueChartOptions = {
                    chart: {
                        type: 'line',
                        height: 300,
                        toolbar: {
                            show: true
                        },
                        animations: {
                            enabled: true,
                            easing: 'easeinout',
                            speed: 800
                        },
                        dropShadow: {
                            enabled: true,
                            top: 3,
                            left: 2,
                            blur: 4,
                            opacity: 0.2
                        }
                    },
                    markers: {
                        size: 4
                    },
                    fill: {
                        type: 'gradient',
                        gradient: {
                            shadeIntensity: 1,
                            inverseColors: false,
                            opacityFrom: 0.5,
                            opacityTo: 0.1,
                            stops: [0, 90, 100]
                        }
                    },
                    series: [{
                        name: 'Estimated Revenue (€)',
                        data: revenueData.map(item => [item.date, parseFloat(item.revenue || 0)])
                    }],
                    xaxis: {
                        type: 'datetime',
                        labels: {
                            datetimeUTC: false,
                            format: 'MMM dd'
                        }
                    },
                    yaxis: {
                        labels: {
                            formatter: function(value) {
                                return "€" + value.toFixed(2)
                            }
                        },
                        title: {
                            text: 'EUR'
                        }
                    },
                    tooltip: {
                        x: {
                            format: 'yyyy-MM-dd'
                        },
                        y: {
                            formatter: function(value) {
                                return "€" + value.toFixed(2)
                            }
                        }
                    },
                    grid: {
                        borderColor: '#e0e0e0'
                    }
                };
                var revenueChart = new ApexCharts(document.querySelector("#revenueChart"), revenueChartOptions);
                revenueChart.render();
            }
            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                height: 500,
                eventColor: '#008060',
                eventTextColor: '#fff',
                events: revenueData.map(function(item) {
                    return {
                        title: "€" + parseFloat(item.revenue).toFixed(2),
                        start: item.date,
                        allDay: true
                    }
                }),
                eventDidMount: function(info) {
                    info.el.style.fontSize = '0.85rem';
                    info.el.style.fontWeight = '500'
                },
                eventDisplay: 'block'
            });
            calendar.render();
            hideLoading();
        });
        <?php if ($authorization_code && empty($youtube_error_message)): ?> showLoading();
        <?php endif; ?>
    </script>
    <?php include 'footer.php'; ?>
</body>
</html>