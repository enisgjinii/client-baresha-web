<?php
session_start(); // Start the session
session_destroy(); // Destroy all session data
?>
<!DOCTYPE html>
<html>

<head>
    <title>YouTube Disconnected</title>
</head>

<body>
    <script>
        localStorage.removeItem('youtube_access_token'); // Optionally clear localStorage tokens too, if you used them previously
        localStorage.removeItem('youtube_refresh_token');
        alert('YouTube channel disconnected.');
        window.location.href = 'link-youtube.php'; // Redirect back to link-youtube.php
    </script>
</body>

</html>