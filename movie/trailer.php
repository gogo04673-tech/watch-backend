<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Access-Control-Allow-Origin: *');
header("Content-Type: application/json; charset=UTF-8");

$token = "eyJhbGciOiJIUzI1NiJ9.eyJhdWQiOiI4ZGRlY2E1MTNkNDYwYmMyMjhjYjJlMWY4MzU1NDcxNiIsIm5iZiI6MTc1Njc0NTM1MC4zNDIwMDAyLCJzdWIiOiI2OGI1Y2U4NjI1ZTEwM2I4OWYzOTgzOTciLCJzY29wZXMiOlsiYXBpX3JlYWQiXSwidmVyc2lvbiI6MX0._UMRFHywtIK-S-i9on1UnVdtP29tKu22dVqLQoqDwtQ"; // ضع التوكن الخاص بك

$input = file_get_contents('php://input');
$data = json_decode($input, true) ?: $_POST;

$movieId = isset($data['movieId']) ? intval($data['movieId']) : 0;


if ($movieId == 0) {
    echo json_encode([
        "status" => "failure",
        "message" => "Movie id is required."
    ]);
    exit();
}

$url = "https://api.themoviedb.org/3/movie/$movieId/videos";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $token",
    "Content-Type: application/json;charset=utf-8"
]);

$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);

// فلترة: Trailer + YouTube
$trailers = array_filter($data['results'] ?? [], function ($video) {
    return $video['site'] === 'YouTube' && $video['type'] === 'Trailer';
});

// ترتيب حسب التاريخ (الأحدث أولاً)
usort($trailers, function ($a, $b) {
    return strtotime($b['published_at']) - strtotime($a['published_at']);
});

if (!empty($trailers)) {
    $latestTrailer = $trailers[0];

    echo json_encode([
        "status" => "success",
        "message" => "Latest trailer fetched successfully",
        "data" => $latestTrailer
    ]);
} else {
    echo json_encode([
        "status" => "failure",
        "message" => "No trailer found"
    ]);
}
