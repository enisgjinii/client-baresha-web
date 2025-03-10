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

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
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

/**
 * Helper function to perform a cURL request.
 */
function curlRequest($url)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    // Optional: disable SSL verification if needed (not recommended for production)
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $output = curl_exec($ch);
    if (curl_errno($ch)) {
        curl_close($ch);
        return false;
    }
    curl_close($ch);
    return $output;
}

/**
 * Fetch videos from a YouTube channel using the YouTube Data API.
 */
function fetchVideos($channel_id, $youtube_api_key)
{
    $max_results = 50;
    $events = [];
    $pageToken = '';
    do {
        $api_url = "https://www.googleapis.com/youtube/v3/search?part=snippet&maxResults={$max_results}&order=date&channelId={$channel_id}&type=video&key={$youtube_api_key}" . ($pageToken ? "&pageToken={$pageToken}" : "");
        $api_response_json = curlRequest($api_url);
        if ($api_response_json === false) {
            return ['error' => "Failed to fetch data for Channel ID: " . htmlspecialchars($channel_id)];
        }
        $api_response = json_decode($api_response_json, true);
        if ($api_response && isset($api_response['items'])) {
            $video_ids_array = array_map(function ($item) {
                return $item['id']['videoId'];
            }, $api_response['items']);
            $video_ids_string = implode(',', $video_ids_array);
            $video_details_url = "https://www.googleapis.com/youtube/v3/videos?part=snippet,statistics&id={$video_ids_string}&key={$youtube_api_key}";
            $video_details_json = curlRequest($video_details_url);
            if ($video_details_json === false) {
                return ['error' => "Failed to fetch detailed video data for Channel ID: " . htmlspecialchars($channel_id)];
            }
            $video_details_response = json_decode($video_details_json, true);
            $video_details_items = $video_details_response['items'];
            $video_details_map = [];
            foreach ($video_details_items as $video_detail_item) {
                $video_details_map[$video_detail_item['id']] = $video_detail_item;
            }
            foreach ($api_response['items'] as $item) {
                $video_id = $item['id']['videoId'];
                $video_detail = isset($video_details_map[$video_id]) ? $video_details_map[$video_id] : [];
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
                    'date' => $published_date, // used for sorting
                    'videoId' => $video_id,
                    'thumbnail' => $video_thumbnail,
                    'description' => $video_description,
                    'viewCount' => $view_count,
                    'likeCount' => $like_count,
                    'commentCount' => $comment_count,
                    'publishedAtFormatted' => $published_at_formatted
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

// Sort events by published date (newest first)
usort($events, function ($a, $b) {
    return strtotime($b['date']) - strtotime($a['date']);
});
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>YouTube Timeline</title>
    <!-- Bootstrap CSS for styling -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">
    <style>
        /* Timeline styling */
        .timeline {
            position: relative;
            max-width: 800px;
            margin: 20px auto;
            padding: 10px 0;
        }

        .timeline::after {
            content: '';
            position: absolute;
            width: 6px;
            background-color: #007bff;
            top: 0;
            bottom: 0;
            left: 50%;
            margin-left: -3px;
        }

        .timeline-item {
            padding: 20px 30px;
            position: relative;
            background-color: inherit;
            width: 50%;
        }

        .timeline-item.left {
            left: 0;
        }

        .timeline-item.right {
            left: 50%;
        }

        .timeline-item::after {
            content: "";
            position: absolute;
            width: 25px;
            height: 25px;
            right: -13px;
            background-color: white;
            border: 4px solid #007bff;
            top: 15px;
            border-radius: 50%;
            z-index: 1;
        }

        .timeline-item.right::after {
            left: -13px;
        }

        .timeline-item .timeline-content {
            padding: 20px;
            background-color: #f8f9fa;
            position: relative;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .timeline-item .timeline-content:hover {
            background-color: #e2e6ea;
        }

        .timeline-item .timeline-content h5 {
            margin-top: 0;
            font-size: 1.2em;
        }

        .timeline-item .timeline-content p {
            margin-bottom: 5px;
        }

        .timeline-item .timeline-content .timeline-date {
            font-size: 0.9em;
            color: #777;
        }

        .timeline-item img {
            max-width: 100%;
            border-radius: 4px;
            margin-bottom: 10px;
        }

        @media screen and (max-width: 600px) {
            .timeline-item {
                width: 100%;
                left: 0 !important;
            }

            .timeline-item.right::after,
            .timeline-item.left::after {
                left: 50%;
            }
        }
    </style>
</head>

<body>
    <div class="col-md-10 main-content">
        <h2 class="mb-3 text-primary">YouTube Timeline</h2>
        <p class="text-muted">Explore YouTube video releases in a timeline view.</p>
        <div class="card shadow-sm border-0 rounded-lg mb-3 p-3">
            <form method="post" action="timeline.php">
                <div class="mb-3">
                    <label for="youtube_api_key" class="form-label">YouTube API Key:</label>
                    <input type="text" class="form-control" id="youtube_api_key" name="youtube_api_key" value="<?php echo htmlspecialchars($youtube_api_key); ?>" placeholder="Enter your YouTube Data API v3 Key" required>
                    <div class="form-text">To fetch video data, a YouTube Data API v3 key is needed. <a href="https://console.cloud.google.com/apis/credentials" target="_blank">Get API Key</a></div>
                </div>
                <button type="submit" class="btn btn-primary">Save API Key &amp; Fetch Videos</button>
            </form>
        </div>
        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success" role="alert"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>
        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>
        <?php if (empty($youtube_channel_id) && empty($error_message)): ?>
            <div class="alert alert-warning" role="alert">Please update your profile with your YouTube Channel ID to view your videos in the timeline. Go to <a href="settings.php">Settings</a> to update your profile.</div>
        <?php endif; ?>

        <?php if (!empty($events)): ?>
            <div class="timeline">
                <?php
                $counter = 0;
                foreach ($events as $event):
                    $side = ($counter % 2 == 0) ? 'left' : 'right';
                ?>
                    <div class="timeline-item <?php echo $side; ?>" data-video='<?php echo json_encode($event); ?>'>
                        <div class="timeline-content">
                            <img src="<?php echo htmlspecialchars($event['thumbnail']); ?>" alt="Video Thumbnail">
                            <h5><?php echo htmlspecialchars($event['title']); ?></h5>
                            <p class="timeline-date"><?php echo htmlspecialchars($event['publishedAtFormatted']); ?></p>
                            <p>Views: <?php echo htmlspecialchars($event['viewCount']); ?> | Likes: <?php echo htmlspecialchars($event['likeCount']); ?> | Comments: <?php echo htmlspecialchars($event['commentCount']); ?></p>
                        </div>
                    </div>
                <?php $counter++;
                endforeach; ?>
            </div>
        <?php else: ?>
            <p class="text-muted">No videos found.</p>
        <?php endif; ?>
    </div>

    <!-- Video Preview Modal -->
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

    <!-- jQuery and Bootstrap scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Handle timeline item click to show video preview modal
        $(document).ready(function() {
            $('.timeline-item').on('click', function() {
                var eventData = $(this).data('video');
                var videoId = eventData.videoId;
                var videoUrl = 'https://www.youtube.com/embed/' + videoId;
                $('#videoPreviewModalLabel').text(eventData.title);
                $('#videoPreviewContainer').html('<iframe src="' + videoUrl + '" frameborder="0" allowfullscreen></iframe>');
                $('#modal-published-date').text(eventData.publishedAtFormatted);
                $('#modal-view-count').text(eventData.viewCount);
                $('#modal-like-count').text(eventData.likeCount);
                $('#modal-comment-count').text(eventData.commentCount);
                $('#modal-description').text(eventData.description);
                var videoPreviewModal = new bootstrap.Modal(document.getElementById('videoPreviewModal'));
                videoPreviewModal.show();
            });

            $('#videoPreviewModal').on('hidden.bs.modal', function() {
                $('#videoPreviewContainer').html('');
            });
        });
    </script>
</body>

</html>
<?php include 'footer.php'; ?>