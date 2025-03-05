<style>
    #calendar {
        margin: 20px;
        background-color: #fff;
        border-radius: 8px;
        box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        padding: 15px;
    }

    .fc-header-toolbar {
        margin-bottom: 1em;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .fc-toolbar-title {
        font-size: 1.5em;
        color: #333;
    }

    .fc-button {
        background-color: #007bff;
        color: white;
        border: none;
        border-radius: 5px;
        padding: 0.5em 1em;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    .fc-button:hover,
    .fc-button:focus {
        background-color: #0056b3;
        opacity: 0.9;
    }

    .fc-button-group>.fc-button {
        margin: 0 2px;
    }

    .youtube-event {
        background-color: #e0f7fa;
        color: #0b7285;
        border: 1px solid #b2ebf2;
        cursor: pointer;
        border-radius: 4px;
        padding: 2px;
        margin-bottom: 2px;
    }

    .fc-event-title {
        font-weight: 500;
        font-size: 0.9em;
    }

    .video-tooltip {
        position: absolute;
        z-index: 1000;
        background: #fff;
        border: 1px solid #ccc;
        padding: 10px;
        border-radius: 5px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        pointer-events: none;
        opacity: 0;
        transition: opacity 0.2s;
        text-align: left;
        width: 250px;
    }

    .video-tooltip.visible {
        opacity: 1;
    }

    .tooltip-thumbnail {
        width: 100%;
        max-height: 150px;
        object-fit: cover;
        border-radius: 4px;
        margin-bottom: 8px;
    }

    .tooltip-title {
        font-weight: bold;
        margin-bottom: 5px;
        color: #333;
        font-size: 1em;
    }

    .tooltip-date {
        font-size: 0.9em;
        color: #777;
    }

    #videoPreviewModal .modal-content {
        border-radius: 8px;
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.2);
    }

    #videoPreviewModal .modal-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
        padding-top: 15px;
        padding-bottom: 10px;
        align-items: center;
    }

    #videoPreviewModal .modal-title {
        color: #007bff;
        font-weight: bold;
        margin-right: auto;
    }

    #videoPreviewModal .btn-close {
        opacity: 0.7;
    }

    #videoPreviewModal .btn-close:hover {
        opacity: 1;
    }

    #videoPreviewModal .modal-body {
        text-align: left;
        padding: 20px;
    }

    #videoPreviewModal iframe {
        width: 100%;
        height: 450px;
        border: none;
        border-radius: 6px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        margin-bottom: 15px;
    }

    #videoDetails {
        margin-top: 15px;
        padding-left: 0;
    }

    #videoDetails li {
        margin-bottom: 8px;
        list-style: none;
        padding-left: 0;
    }

    #videoDetails strong {
        font-weight: bold;
        margin-right: 5px;
        color: #555;
    }

    .card-body form .mb-3,
    .alert {
        margin-bottom: 1rem !important;
    }

    .form-label {
        margin-bottom: 0.2rem !important;
        font-size: 0.95rem;
    }

    .form-control,
    .btn {
        font-size: 0.95rem;
        padding: 0.5rem 0.75rem;
    }

    .card .card-body {
        padding: 1rem;
    }

    .card {
        margin-bottom: 1.5rem !important;
    }

    .main-content {
        padding-top: 1rem;
    }

    .form-text {
        font-size: 0.85rem;
    }
</style>
<?php
include 'connection.php';
include 'header.php';
include 'sidebar.php';

$youtube_api_key = '';
$youtube_channel_id = '';
$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['youtube_api_key'])) {
    $_SESSION['youtube_api_key'] = $_POST['youtube_api_key'];
    $youtube_api_key = $_SESSION['youtube_api_key'];
    $success_message = "YouTube API Key saved successfully!";
} elseif (isset($_SESSION['youtube_api_key'])) {
    $youtube_api_key = $_SESSION['youtube_api_key'];
}

$user_id = $_SESSION['user_id'];
if ($user_id) {
    $stmt = $conn->prepare("SELECT youtube FROM klientet WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $user_data = $result->fetch_assoc();
        $youtube_channel_id = $user_data['youtube'];
    } else {
        $error_message = "User data not found.";
    }
    $stmt->close();
} else {
    $error_message = "User not logged in.";
}

$static_channel_id = 'UCV6ZBT0ZUfNbtZMbsy-L3CQ';
$events = [];

function fetchVideos($channel_id, $youtube_api_key)
{
    $max_results = 50;
    $events = [];
    $pageToken = '';
    do {
        $api_url = "https://www.googleapis.com/youtube/v3/search?part=snippet&maxResults={$max_results}&order=date&channelId={$channel_id}&type=video&key={$youtube_api_key}" . ($pageToken ? "&pageToken={$pageToken}" : "");
        $api_response_json = @file_get_contents($api_url);
        if ($api_response_json === false) return ['error' => "Failed to fetch data for Channel ID: " . htmlspecialchars($channel_id)];
        $api_response = json_decode($api_response_json, true);
        if ($api_response && isset($api_response['items'])) {
            $video_ids_array = array_map(function ($item) {
                return $item['id']['videoId'];
            }, $api_response['items']);
            $video_ids_string = implode(',', $video_ids_array);
            $video_details_url = "https://www.googleapis.com/youtube/v3/videos?part=snippet,statistics&id={$video_ids_string}&key={$youtube_api_key}";
            $video_details_json = @file_get_contents($video_details_url);
            if ($video_details_json === false) return ['error' => "Failed to fetch detailed video data for Channel ID: " . htmlspecialchars($channel_id)];
            $video_details_response = json_decode($video_details_json, true);
            $video_details_items = $video_details_response['items'];
            $video_details_map = [];
            foreach ($video_details_items as $video_detail_item) {
                $video_details_map[$video_detail_item['id']] = $video_detail_item;
            }
            foreach ($api_response['items'] as $item) {
                $video_id = $item['id']['videoId'];
                $video_detail = $video_details_map[$video_id];
                $video_title = $item['snippet']['title'];
                $published_at_raw = $item['snippet']['publishedAt'];
                $published_date = date('Y-m-d', strtotime($published_at_raw));
                $video_thumbnail = $item['snippet']['thumbnails']['medium']['url'];
                $video_description = isset($video_detail['snippet']) ? $video_detail['snippet']['description'] : '';
                $view_count = isset($video_detail['statistics']['viewCount']) ? number_format($video_detail['statistics']['viewCount']) : 'N/A';
                $like_count = isset($video_detail['statistics']['likeCount']) ? number_format($video_detail['statistics']['likeCount']) : 'N/A';
                $comment_count = isset($video_detail['statistics']['commentCount']) ? number_format($video_detail['statistics']['commentCount']) : 'N/A';
                $published_at_formatted = date('F j, Y, g:i a', strtotime($published_at_raw));
                $events[] = [
                    'title' => $video_title,
                    'start' => $published_date,
                    'allDay' => true,
                    'url' => 'https://www.youtube.com/watch?v=' . $video_id,
                    'extendedProps' => [
                        'videoId' => $video_id,
                        'thumbnail' => $video_thumbnail,
                        'description' => $video_description,
                        'viewCount' => $view_count,
                        'likeCount' => $like_count,
                        'commentCount' => $comment_count,
                        'publishedAtFormatted' => $published_at_formatted
                    ]
                ];
            }
        } else {
            $api_error_detail = isset($api_response['error']['message']) ? " API Error: " . $api_response['error']['message'] : '';
            return ['error' => "No videos found or invalid API response for Channel ID: " . htmlspecialchars($channel_id) . $api_error_detail];
        }
        $pageToken = isset($api_response['nextPageToken']) ? $api_response['nextPageToken'] : '';
    } while ($pageToken);
    return $events;
}

if ($youtube_api_key) {
    if ($youtube_channel_id) {
        $result_dynamic = fetchVideos($youtube_channel_id, $youtube_api_key);
        if (isset($result_dynamic['error'])) {
            $error_message .= $result_dynamic['error'];
        } else {
            $events = array_merge($events, $result_dynamic);
        }
    } else {
        $error_message .= "Please update your profile with your YouTube Channel ID.";
    }
    $result_static = fetchVideos($static_channel_id, $youtube_api_key);
    if (isset($result_static['error'])) {
        $error_message .= " " . $result_static['error'];
    } else {
        $events = array_merge($events, $result_static);
    }
} else {
    $error_message .= "API key is required.";
}
?>

<div class="col-md-10 main-content">
    <div class="fade-in">
        <h4 class="fw-bold text-primary">Calendar</h4>
        <p class="text-muted">Explore YouTube song releases on the calendar</p>
        <div class="card shadow-sm border-0 rounded-lg mb-3">
            <div class="card-body">
                <form method="post" action="calendar.php">
                    <div class="mb-3">
                        <label for="youtube_api_key" class="form-label">YouTube API Key:</label>
                        <input type="text" class="form-control" id="youtube_api_key" name="youtube_api_key" value="<?php echo htmlspecialchars($youtube_api_key); ?>" placeholder="Enter your YouTube Data API v3 Key" required>
                        <div class="form-text">To fetch video data, a YouTube Data API v3 key is needed. <a href="https://console.cloud.google.com/apis/credentials" target="_blank">Get API Key</a></div>
                    </div>
                    <button type="submit" class="btn btn-primary">Save API Key & Fetch Videos</button>
                </form>
            </div>
        </div>
        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success" role="alert"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>
        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>
        <?php if (empty($youtube_channel_id) && empty($error_message)): ?>
            <div class="alert alert-warning" role="alert">Please update your profile with your YouTube Channel ID to view your videos on the calendar. Go to <a href="settings.php">Settings</a> to update your profile.</div>
        <?php endif; ?>
        <div id="calendar" class="shadow-sm border-0 rounded-lg"></div>
    </div>
</div>
<div class="modal fade" id="videoPreviewModal" tabindex="-1" aria-labelledby="videoPreviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="videoPreviewModalLabel">Video Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="videoPreviewContainer"></div>
                <ul id="videoDetails" class="list-unstyled">
                    <li><strong>Published Date:</strong> <span id="modal-published-date"></span></li>
                    <li><strong>Views:</strong> <span id="modal-view-count"></span></li>
                    <li><strong>Likes:</strong> <span id="modal-like-count"></span></li>
                    <li><strong>Comments:</strong> <span id="modal-comment-count"></span></li>
                    <li><strong>Description:</strong> <span id="modal-description"></span></li>
                </ul>
            </div>
        </div>
    </div>
</div>
<div id="videoTooltip" class="video-tooltip">
    <img src="" alt="Video Thumbnail" class="tooltip-thumbnail">
    <h6 class="tooltip-title"></h6>
    <p class="tooltip-date"></p>
</div>
<?php include 'footer.php'; ?>
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js'></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        var videoTooltipEl = document.getElementById('videoTooltip');
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
            },
            eventMouseEnter: function(info) {
                var tooltip = videoTooltipEl;
                var event = info.event;
                var extendedProps = event.extendedProps;
                tooltip.querySelector('.tooltip-thumbnail').src = extendedProps.thumbnail;
                tooltip.querySelector('.tooltip-title').textContent = event.title;
                tooltip.querySelector('.tooltip-date').textContent = new Date(event.start).toLocaleDateString();
                tooltip.classList.add('visible');
                tooltip.style.top = (info.jsEvent.clientY + 15) + 'px';
                tooltip.style.left = (info.jsEvent.clientX + 15) + 'px';
            },
            eventMouseLeave: function(info) {
                videoTooltipEl.classList.remove('visible');
            },
            eventContent: function(arg) {
                let event = arg.event;
                let thumbnail = event.extendedProps.thumbnail;
                let title = event.title;
                let eventHtml = `<div class="youtube-event-content"><img src="${thumbnail}" style="width:100%; border-radius:4px; margin-bottom: 4px;"><span class="fc-event-title">${title}</span></div>`;
                return {
                    html: eventHtml
                };
            },
            events: <?php echo json_encode($events); ?>,
            eventColor: '#378006',
            eventClassNames: ['youtube-event'],
            eventClick: function(info) {
                info.jsEvent.preventDefault();
                var videoId = info.event.extendedProps.videoId;
                var videoUrl = 'https://www.youtube.com/embed/' + videoId;
                var videoTitle = info.event.title;
                var publishedDate = info.event.extendedProps.publishedAtFormatted;
                var viewCount = info.event.extendedProps.viewCount;
                var likeCount = info.event.extendedProps.likeCount;
                var commentCount = info.event.extendedProps.commentCount;
                var description = info.event.extendedProps.description;
                $('#videoPreviewModalLabel').text(videoTitle);
                $('#videoPreviewContainer').html('<iframe src="' + videoUrl + '" frameborder="0" allowfullscreen></iframe>');
                $('#modal-published-date').text(publishedDate);
                $('#modal-view-count').text(viewCount);
                $('#modal-like-count').text(likeCount);
                $('#modal-comment-count').text(commentCount);
                $('#modal-description').text(description);
                var videoPreviewModal = new bootstrap.Modal(document.getElementById('videoPreviewModal'));
                videoPreviewModal.show();
            }
        });
        calendar.render();
    });
    $(document).ready(function() {
        $('#videoPreviewModal').on('hidden.bs.modal', function() {
            $('#videoPreviewContainer').html('');
        });
    });
</script>