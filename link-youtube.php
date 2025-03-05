<?php
include 'connection.php';
include 'header.php';
include 'sidebar.php';

$youtube_client_id = '889125415131-qfi8c11iibraqj82aal4fc8q4oic2m5u.apps.googleusercontent.com'; // Replace with your YouTube API Client ID
$youtube_client_secret = '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'; // Replace with your YouTube API Client Secret
$redirect_uri = 'http://localhost/client-baresha-web/link-youtube.php'; // Replace with your redirect URI

$authorization_code = $_GET['code'] ?? '';
$access_token = $_SESSION['youtube_access_token'] ?? ''; // Get from session
$refresh_token = $_SESSION['youtube_refresh_token'] ?? ''; // Get from session
$channel_name = '';
$channel_thumbnail = '';
$view_count = 'N/A';
$estimated_revenue_total = 'N/A';
$revenue_data_daily = [];
$youtube_error_message = '';

// Function to safely fetch data from YouTube API (same as before)
function fetchYoutubeApiData($api_url, $access_token)
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
        if (isset($token_response['access_token'])) {
            $access_token = $token_response['access_token'];
            $refresh_token = $token_response['refresh_token'] ?? '';

            // Store tokens in session - Now using server-side sessions!
            $_SESSION['youtube_access_token'] = $access_token;
            $_SESSION['youtube_refresh_token'] = $refresh_token;
            echo "<script>console.log('Access and Refresh tokens saved to PHP session (Server-side storage)')</script>";


            // Fetch Channel Information
            $channel_info_url = 'https://www.googleapis.com/youtube/v3/channels?part=snippet,statistics&mine=true';
            $channel_data = fetchYoutubeApiData($channel_info_url, $access_token);
            if (isset($channel_data['error'])) {
                $youtube_error_message .= 'Channel info error: ' . $channel_data['error'];
            } elseif (isset($channel_data['items'][0])) {
                $channel_name = $channel_data['items'][0]['snippet']['title'];
                $channel_thumbnail = $channel_data['items'][0]['snippet']['thumbnails']['default']['url'];
                $view_count = number_format($channel_data['items'][0]['statistics']['viewCount']);
            } else {
                $channel_name = 'Error fetching channel name';
            }

            // Fetch Estimated Revenue Data
            $analytics_url = 'https://youtubeanalytics.googleapis.com/v2/reports';
            $today = date('Y-m-d');
            $thirty_days_ago = date('Y-m-d', strtotime('-30 days'));

            $analytics_params = [
                'ids' => 'channel==MINE',
                'startDate' => $thirty_days_ago,
                'endDate' => $today,
                'metrics' => 'estimatedRevenue',
                'dimensions' => 'date',
                'sort' => 'date'
            ];

            $analytics_url .= '?' . http_build_query($analytics_params);
            $analytics_data = fetchYoutubeApiData($analytics_url, $access_token);

            if (isset($analytics_data['error'])) {
                $youtube_error_message .= 'Revenue data error: ' . $analytics_data['error'];
            } elseif (isset($analytics_data['rows'])) {
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
        } else {
            $youtube_error_message = 'Failed to retrieve access token: ' . $token_response_json;
        }
    }
}


$oauth_url = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query([
    'client_id' => $youtube_client_id,
    'redirect_uri' => $redirect_uri,
    'scope' => 'https://www.googleapis.com/auth/youtube.readonly https://www.googleapis.com/auth/youtube.channel-memberships.creator https://www.googleapis.com/auth/yt-analytics.readonly',
    'response_type' => 'code',
    'access_type' => 'offline',
    'prompt' => 'consent',
]);

?>
<style>
    /* --- General Layout and Card Styles --- */
    .link-youtube-card {
        background-color: #fff;
        border-radius: 10px;
        /* Slightly less rounded */
        box-shadow: 0 8px 20px rgba(70, 70, 70, 0.08);
        /* Softer shadow, darker color */
        margin-bottom: 2.5rem;
        /* Increased bottom margin for spacing */
        overflow: hidden;
        border: 1px solid #e0e0e0;
        /* Lighter border color */
    }

    .link-youtube-card:hover {
        box-shadow: 0 12px 28px rgba(70, 70, 70, 0.12);
        /* More pronounced hover shadow */
        transform: translateY(-3px);
        /* Slightly more translateY on hover */
        transition: all 0.35s ease-in-out;
        /* Smoother transition with ease-in-out */
    }

    .link-youtube-card-header {
        padding: 1.75rem 2rem;
        background-color: #f9f9f9;
        /* Slightly lighter header background */
        border-bottom: 1px solid #e0e0e0;
        /* Lighter header border */
    }

    .link-youtube-card-title {
        font-size: 1.6rem;
        /* Slightly larger title font */
        font-weight: 600;
        /* Less bold, slightly lighter weight */
        color: #333;
        /* Darker text color */
        margin-bottom: 0;
        /* Remove default bottom margin for cleaner layout */
    }

    .link-youtube-card-body {
        padding: 2.5rem;
        /* Increased padding in body */
        text-align: center;
    }

    /* --- YouTube Connect Button --- */
    .youtube-connect-btn {
        background-color: #e31b23;
        /* YouTube Red - slightly adjusted */
        color: #fff;
        border: none;
        border-radius: 10px;
        /* Slightly more rounded button */
        padding: 1.1rem 2.2rem;
        /* Slightly adjusted padding */
        font-size: 1.15rem;
        /* Slightly larger font size */
        font-weight: 500;
        /* Lighter font weight */
        cursor: pointer;
        transition: background-color 0.3s ease-in-out, transform 0.2s ease-in-out;
        /* Transition for transform */
        margin-bottom: 1.2rem;
        /* Adjusted margin */
        display: inline-flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 3px 8px rgba(0, 0, 0, 0.08);
        /* Button shadow */
    }

    .youtube-connect-btn:hover {
        background-color: #c4171d;
        /* Darker red on hover */
        transform: scale(1.03);
        /* Scale up slightly on hover */
        box-shadow: 0 5px 12px rgba(0, 0, 0, 0.12);
        /* Increased shadow on hover */
    }

    .youtube-connect-btn:active {
        transform: scale(1.0);
        /* Reset scale when active (click) */
        box-shadow: 0 3px 8px rgba(0, 0, 0, 0.08);
        /* Reset shadow when active */
    }

    .youtube-icon {
        width: 25px;
        /* Slightly larger icon */
        height: 25px;
        margin-right: 12px;
        /* Adjusted icon margin */
        filter: brightness(0) invert(1);
        /* Ensure icon is white */
    }

    /* --- Channel Info Section --- */
    .channel-info {
        margin-top: 2.5rem;
        /* Increased margin top */
        padding: 2rem;
        border: 1px solid #e0e0e0;
        /* Lighter border */
        border-radius: 10px;
        /* Rounded corners */
        background-color: #f9f9f9;
        /* Lighter background */
        box-shadow: inset 0 2px 5px rgba(0, 0, 0, 0.03);
        /* Subtle inner shadow */
    }

    .channel-thumbnail {
        width: 120px;
        /* Larger thumbnail */
        height: 120px;
        border-radius: 50%;
        object-fit: cover;
        margin-bottom: 1.2rem;
        /* Adjusted margin */
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        /* Thumbnail shadow */
        border: 3px solid #fff;
        /* White border around thumbnail */
    }

    .channel-name {
        font-size: 1.5rem;
        /* Slightly larger channel name */
        font-weight: 600;
        /* Slightly lighter weight */
        color: #3a4750;
        margin-bottom: 0.7rem;
        /* Adjusted margin */
    }

    .channel-stats {
        font-size: 1.05rem;
        /* Slightly larger stats font */
        color: #777;
        /* Muted stats color */
        margin-bottom: 0.5rem;
        /* Adjusted margin */
    }

    /* --- Revenue Stats Section --- */
    .revenue-stats {
        margin-top: 2rem;
        padding: 2rem;
        border: 1px solid #e0e0e0;
        /* Lighter border */
        border-radius: 10px;
        /* Rounded corners */
        background-color: #f9f9f9;
        /* Lighter background */
        box-shadow: inset 0 2px 5px rgba(0, 0, 0, 0.03);
        /* Subtle inner shadow */
    }

    .revenue-stat-title {
        font-size: 1.4rem;
        /* Adjusted title font size */
        font-weight: 600;
        color: #333;
        margin-bottom: 1.5rem;
        /* Increased margin */
        text-align: left;
        /* Align revenue stats title to left */
    }

    .revenue-stat-item {
        margin-bottom: 1.2rem;
        /* Adjusted margin */
    }

    .revenue-stat-label {
        font-weight: 500;
        /* Lighter label weight */
        color: #555;
        /* Darker label color */
        display: block;
        margin-bottom: 0.3rem;
        /* Adjusted margin */
        font-size: 1.05rem;
        /* Slightly larger label font */
        text-align: left;
        /* Align label to left */
    }

    .revenue-stat-value {
        font-size: 1.3rem;
        /* Slightly larger value font */
        color: #008060;
        /* Green color, slightly adjusted */
        font-weight: 600;
        /* Slightly heavier value weight */
        text-align: left;
        /* Align value to left */
    }

    #revenueChart {
        width: 100%;
        height: 320px;
        /* Slightly taller chart area */
        margin-top: 2rem;
    }

    /* --- Main Content and Animations --- */
    .main-content {
        padding: 3rem;
        /* Increased main content padding */
    }

    .fade-in {
        animation: fadeIn ease 0.6s;
        /* Slightly slower fade-in */
    }

    .slide-up {
        animation: slideUp ease 0.6s;
        /* Slightly slower slide-up */
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
            transform: translateY(40px);
            /* Increased translateY for more noticeable slide-up */
            opacity: 0;
        }

        100% {
            transform: translateY(0);
            opacity: 1;
        }
    }

    /* --- Loading Overlay --- */
    .loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 0.85);
        /* Slightly less transparent overlay */
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 1000;
    }

    .loading-spinner {
        border: 6px solid #f3f3f3;
        /* Light grey border */
        border-top: 6px solid #3498db;
        /* Blue loading color */
        border-radius: 50%;
        width: 60px;
        /* Slightly larger spinner */
        height: 60px;
        animation: spin 1.8s linear infinite;
        /* Slightly faster spin */
    }

    @keyframes spin {
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(360deg);
        }
    }

    /* --- Token Display Area --- */
    .token-display {
        margin-top: 3rem;
        /* Increased margin top */
        padding: 1.5rem;
        /* Increased padding */
        border: 1px dashed #ccc;
        /* Dashed border to indicate development purpose */
        border-radius: 10px;
        /* Rounded corners */
        background-color: #fefefe;
        /* Very light background */
        font-size: 0.95rem;
        /* Slightly larger token font */
        word-wrap: break-word;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
        /* Subtle shadow */
    }

    .token-title {
        font-weight: 600;
        /* Slightly lighter token title weight */
        margin-bottom: 0.7rem;
        /* Adjusted margin */
        color: #555;
    }

    .token-display textarea.form-control {
        font-size: 0.85rem;
        /* Slightly smaller textarea font */
        background-color: #f0f0f0;
        /* Lighter textarea background */
        border: 1px solid #ddd;
        /* Lighter textarea border */
        border-radius: 6px;
        /* Rounded textarea corners */
        padding: 0.75rem;
        /* Adjusted textarea padding */
        margin-bottom: 1rem;
        /* Adjusted textarea margin */
        color: #444;
        /* Darker textarea text color */
    }

    .text-muted.mt-3,
    .text-muted.mt-2 {
        color: #888 !important;
        /* Muted text color adjustments */
        font-size: 0.95rem;
    }

    .text-success {
        color: #28a745 !important;
        /* Success text color adjustment */
        font-weight: 500;
    }

    .alert-danger {
        border-radius: 8px;
        padding: 1rem 1.5rem;
        margin-bottom: 1.5rem;
        font-size: 0.95rem;
    }
</style>

<div class="col-md-10 main-content">
    <div class="fade-in">
        <h2 class="fw-bold text-dark mb-3">Link Your YouTube Channel</h2>
        <p class="text-muted mb-4">Connect your YouTube channel to unlock insights and manage your content.</p>

        <div class="link-youtube-card slide-up">
            <div class="link-youtube-card-header">
                <h5 class="link-youtube-card-title">Connect Your Channel</h5>
            </div>
            <div class="link-youtube-card-body">
                <?php if (!empty($youtube_error_message)): ?>
                    <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($youtube_error_message); ?></div>
                <?php endif; ?>

                <?php if (!$access_token): ?>
                    <p>To get started, link your YouTube channel.</p>
                    <a href="<?php echo htmlspecialchars($oauth_url); ?>" class="youtube-connect-btn">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/7/75/YouTube_social_white_squircle_%282017%29.svg" alt="YouTube Icon" class="youtube-icon">
                        Connect with YouTube
                    </a>
                    <p class="text-muted mt-3">By connecting, you grant permission to view your YouTube channel data.</p>
                <?php else: ?>
                    <div class="channel-info">
                        <?php if ($channel_thumbnail): ?>
                            <img src="<?php echo htmlspecialchars($channel_thumbnail); ?>" alt="Channel Thumbnail" class="channel-thumbnail">
                        <?php endif; ?>
                        <?php if ($channel_name): ?>
                            <h4 class="channel-name"><?php echo htmlspecialchars($channel_name); ?></h4>
                        <?php endif; ?>
                        <?php if ($view_count != 'N/A'): ?>
                            <p class="channel-stats">Total Views: <?php echo htmlspecialchars($view_count); ?></p>
                        <?php endif; ?>
                        <p class="text-success">Channel successfully linked!</p>
                        <button class="youtube-connect-btn" onclick="disconnectYouTube()" style="background-color:#4285F4;">
                            <i class="fas fa-unlink youtube-icon"></i> Disconnect YouTube
                        </button>
                    </div>

                    <div class="revenue-stats">
                        <h4 class="revenue-stat-title mb-3">Revenue Overview (Last 30 Days)</h4>
                        <div class="revenue-stat-item">
                            <span class="revenue-stat-label">Estimated Revenue (Total)</span>
                            <span class="revenue-stat-value">$<?php echo htmlspecialchars($estimated_revenue_total); ?></span>
                        </div>
                        <div id="revenueChart"></div>
                    </div>

                    <div class="token-display">
                        <p class="token-title">For Development/Verification Only (Tokens are Session-Based - Not Persistent for a Day in this Example)</p>
                        <p class="token-title">Access Token:</p>
                        <textarea rows="3" class="form-control" readonly><?php echo htmlspecialchars($access_token); ?></textarea>
                        <p class="token-title">Refresh Token:</p>
                        <textarea rows="3" class="form-control" readonly><?php echo htmlspecialchars($refresh_token); ?></textarea>
                        <p class="text-muted mt-2"><b>Important:</b> These tokens are displayed for development verification only and are stored in a session. Do not expose tokens in a production UI. For production, implement secure server-side token storage and refresh token handling.</p>
                    </div>

                <?php endif; ?>
            </div>
        </div>
    </div>
    <div id="loadingOverlay" class="loading-overlay" style="display: none;">
        <div class="loading-spinner"></div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    function disconnectYouTube() {
        // Clear session tokens by redirecting to a logout action (you'd need to create logout.php to destroy session)
        window.location.href = 'logout-youtube.php';
    }

    document.addEventListener('DOMContentLoaded', function() {


        // Revenue Chart
        var revenueData = <?php echo json_encode($revenue_data_daily); ?>;
        var chartOptions = {
            chart: {
                type: 'line',
                height: 300,
                toolbar: {
                    show: false
                }
            },
            series: [{
                name: 'Estimated Revenue',
                data: revenueData.map(item => [item.date, parseFloat(item.revenue || 0)]) // Handle potentially missing revenue, convert to float
            }],
            xaxis: {
                type: 'datetime',
                labels: {
                    datetimeUTC: false,
                    format: 'MMM dd',
                }
            },
            yaxis: {
                labels: {
                    formatter: function(value) {
                        return "$" + value.toFixed(2);
                    }
                },
                title: {
                    text: 'USD',
                },
            },
            tooltip: {
                x: {
                    format: 'yyyy-MM-dd'
                },
                y: {
                    formatter: function(value) {
                        return "$" + value.toFixed(2);
                    }
                }
            },
            colors: ['#49b382'], // Example color
        };

        var chart = new ApexCharts(document.querySelector("#revenueChart"), chartOptions);
        chart.render();

        // Hide loading overlay after chart render (or after API calls complete if you manage loading state differently)
        hideLoading();
    });

    function showLoading() {
        document.getElementById('loadingOverlay').style.display = 'flex';
    }

    function hideLoading() {
        document.getElementById('loadingOverlay').style.display = 'none';
    }

    <?php if ($authorization_code && empty($youtube_error_message)): ?>
        showLoading(); // Show loading only when we expect data to load after auth code exchange
    <?php endif; ?>
</script>

<?php include 'footer.php'; ?>