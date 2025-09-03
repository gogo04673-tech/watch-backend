<?php
$token = "eyJhbGciOiJIUzI1NiJ9.eyJhdWQiOiI4ZGRlY2E1MTNkNDYwYmMyMjhjYjJlMWY4MzU1NDcxNiIsIm5iZiI6MTc1Njc0NTM1MC4zNDIwMDAyLCJzdWIiOiI2OGI1Y2U4NjI1ZTEwM2I4OWYzOTgzOTciLCJzY29wZXMiOlsiYXBpX3JlYWQiXSwidmVyc2lvbiI6MX0._UMRFHywtIK-S-i9on1UnVdtP29tKu22dVqLQoqDwtQ"; // ضع التوكن الخاص بك

$url = "https://api.themoviedb.org/3/movie/now_playing";

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

echo json_encode([
    "status" => "success",
    "message" => "Now playing movies fetched successfully",
    "data" => $data['results']
]);
