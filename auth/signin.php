<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Access-Control-Allow-Origin: *');
header("Content-Type: application/json; charset=UTF-8");

include "../connect.php";

// استقبال البيانات من POST أو JSON
$input = file_get_contents('php://input');
$data = json_decode($input, true) ?: $_POST;

$email = isset($data['users_email']) ? $data['users_email'] : '';
$password = isset($data['users_password']) ? $data['users_password'] : '';

// التحقق من القيم المطلوبة
if (empty($email) || empty($password)) {
    echo json_encode(["status" => "failed", "message" => "All fields are required"]);
    exit();
}

// التحقق من صحة البريد الإلكتروني
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["status" => "failed", "message" => "Invalid email format"]);
    exit();
}

try {
    // البحث عن المستخدم بالبريد فقط
    $stmt = $connect->prepare('SELECT * FROM `users` WHERE `users_email` = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['users_password'])) {
        // تسجيل الدخول ناجح
        echo json_encode([
            "status" => "success",
            "message" => "Account Sign In successfully",
            "data" => $user
        ]);
    } else {
        // البريد موجود لكن كلمة المرور خاطئة أو المستخدم غير موجود
        echo json_encode(["status" => "failed", "message" => "Invalid email or password"]);
    }
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
