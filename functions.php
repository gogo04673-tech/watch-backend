<?php


require 'phpmailer/PHPMailer.php';
require 'phpmailer/SMTP.php';
require 'phpmailer/Exception.php';


require __DIR__ . '/vendor/autoload.php';
require_once "config.php";

use Google\Client;



// استيراد الـ namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function send_verification_code($toEmail, $userName, $verificationCode)
{
	$mail = new PHPMailer(true);

	try {
		// إعدادات SMTP
		$mail->isSMTP();
		$mail->Host       = 'smtp.gmail.com';
		$mail->SMTPAuth   = true;
		$mail->Username   = 'eljihadmohammed84@gmail.com'; // بريدك
		$mail->Password   = 'rkrg umbw xlem hjsd'; // كلمة مرور تطبيق Gmail (وليس كلمة السر العادية)
		$mail->SMTPSecure = 'tls';
		$mail->Port       = 587;

		// المرسل والمستلم
		$mail->setFrom('youremail@gmail.com', 'Your App');
		$mail->addAddress($toEmail, $userName);

		// المحتوى
		$mail->isHTML(true);
		$mail->Subject = 'Email Verification Code';
		$mail->Body    = "
            <h2>Hello, $userName</h2>
            <p>Your verification code is:</p>
            <h3>$verificationCode</h3>
        ";

		$mail->send();
		return true;
	} catch (Exception $e) {
		echo "Mailer Error: " . $mail->ErrorInfo;
		return false;
	}
}


if (!function_exists('filterRequest')) {
	function filterRequest($requestName)
	{
		return htmlspecialchars(strip_tags($_POST[$requestName]));
	}
}

function filterRequest($requestName)
{
	return htmlspecialchars(strip_tags($_POST[$requestName]));
}


function checkAuthenticate()
{
	if (isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])) {
		if ($_SERVER['PHP_AUTH_USER'] != "mohamed" ||  $_SERVER['PHP_AUTH_PW'] != "mohamed1234") {
			header('WWW-Authenticate: Basic realm="My Realm"');
			header('HTTP/1.0 401 Unauthorized');
			echo 'Page Not Found';
			exit;
		}
	} else {
		exit;
	}
}


function deleteFile(string $dir, string $fileName): bool
{
	// 1. التحقق من صحة المدخلات
	if (empty($dir) || empty($fileName)) {
		throw new InvalidArgumentException("Directory and file name must be provided");
	}

	// 2. تطبيع المسارات وتجنب هجمات Directory Traversal
	$safeDir = realpath($dir);
	$safeFileName = basename($fileName);

	if (!$safeDir) {
		throw new RuntimeException("Directory does not exist or is inaccessible");
	}

	// 3. بناء المسار الآمن
	$filePath = $safeDir . DIRECTORY_SEPARATOR . $safeFileName;

	// 4. التحقق من وجود الملف
	if (!file_exists($filePath)) {
		return false; // الملف غير موجود
	}

	// 5. التحقق من أنه ملف وليس مجلد
	if (!is_file($filePath)) {
		throw new RuntimeException("Path is not a file: " . $filePath);
	}

	// 6. التحقق من الصلاحيات
	if (!is_writable($filePath)) {
		throw new RuntimeException("File is not writable: " . $filePath);
	}

	// 7. محاولة الحذف
	if (@unlink($filePath)) {
		return true;
	}

	// 8. معالجة الأخطاء
	$error = error_get_last();
	throw new RuntimeException("Failed to delete file: " . ($error['message'] ?? 'Unknown error'));
}

/// * =============================== Upload file or image ====================================///
function secureFileUpload($requestFile, $dir)
{
	$targetDir = '/opt/lampp/htdocs/shop-lay-PHP-main/upload/' . $dir;
	$errors = [];

	if (!is_dir($targetDir)) return ["success" => false, "errors" => ["folder_not_found"]];
	if (!is_writable($targetDir)) return ["success" => false, "errors" => ["folder_not_writable"]];
	if (!isset($_FILES[$requestFile]) || $_FILES[$requestFile]['error'] !== UPLOAD_ERR_OK)
		return ["success" => false, "errors" => ["upload_failed"]];

	$file = $_FILES[$requestFile];
	$maxSize = 2 * 1024 * 1024; // 2MB

	if ($file['size'] > $maxSize) $errors[] = "size_exceeded";

	$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
	$allowedExt = ["jpg", "jpeg", "png", "gif", "webp"];
	if (!in_array($ext, $allowedExt)) $errors[] = "invalid_extension";

	$finfo = finfo_open(FILEINFO_MIME_TYPE);
	$mime = finfo_file($finfo, $file['tmp_name']);
	finfo_close($finfo);
	$allowedMimes = ["image/jpeg", "image/png", "image/gif", "image/webp"];
	if (!in_array($mime, $allowedMimes)) $errors[] = "invalid_mime";

	if (!empty($errors)) return ["success" => false, "errors" => $errors];

	$newName = bin2hex(random_bytes(8)) . '.' . $ext;
	$targetPath = rtrim($targetDir, '/') . '/' . $newName;

	if (move_uploaded_file($file['tmp_name'], $targetPath)) {
		return ["success" => true, "filename" => $newName];
	} else {
		return ["success" => false, "errors" => ["move_failed"]];
	}
}

/// * =============================== Upload file or image ====================================///

// * get all  data Function 
function getAllData($table, $where = null, $json = true)
{
	include __DIR__ . "/connect.php";

	//global $connect;

	try {
		$allowedTables = ['categories', 'users', 'items', 'items_view', 'items_top_seller', 'favorite_items', 'cart', 'items_cart', 'address', 'orders', 'orders_view', 'notifications', 'orders_details_view', 'contact_us'];
		if (!in_array($table, $allowedTables)) {
			throw new Exception("Invalid table name");
		}

		$sql = "SELECT * FROM `$table`";
		if (!empty($where)) {
			$sql .= " WHERE $where";
		}

		$stmt = $connect->prepare($sql);
		$stmt->execute();
		$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

		if ($json) {
			echo json_encode([
				"status" => count($data) > 0 ? "success" : "failed",
				"message" => count($data) > 0 ? "Data retrieved successfully" : "No data found",
				"data" => $data
			]);
		} else {
			return $data;
		}
	} catch (PDOException $e) {
		echo json_encode([
			"status" => "failed",
			"message" => "Database error: " . $e->getMessage()
		]);
	} catch (Exception $e) {
		echo json_encode([
			"status" => "failed",
			"message" => $e->getMessage()
		]);
	}
}

// * get data Function 
function getData($table, $where = null, $json = true)
{
	include __DIR__ . "/connect.php";

	try {
		$allowedTables = ['categories', 'users', 'items', 'items_view', 'favorite_items', 'cart', 'items_cart', 'address', 'coupon'];
		if (!in_array($table, $allowedTables)) {
			throw new Exception("Invalid table name");
		}

		$sql = "SELECT * FROM `$table`";
		$params = [];

		if (!empty($where) && is_array($where)) {
			$conditions = [];
			foreach ($where as $field => $value) {
				$conditions[] = "$field = :$field";
				$params[":$field"] = $value;
			}
			$sql .= " WHERE " . implode(" AND ", $conditions);
		}

		$stmt = $connect->prepare($sql);
		$stmt->execute($params);
		$data = $stmt->fetch(PDO::FETCH_ASSOC);

		if ($json) {
			echo json_encode([
				"status"  => !empty($data) ? "success" : "failed",
				"message" => !empty($data) ? "Data retrieved successfully" : "No data found",
				"data"    => $data
			]);
		} else {
			return $stmt->rowCount();
		}
	} catch (PDOException $e) {
		echo json_encode([
			"status"  => "failed",
			"message" => "Database error: " . $e->getMessage()
		]);
	} catch (Exception $e) {
		echo json_encode([
			"status"  => "failed",
			"message" => $e->getMessage()
		]);
	}
}


// * insert data function
function insertData($table, $data, $json = true)
{
	include __DIR__ . "/connect.php";

	// تحقق من اسم الجدول
	$allowedTables = ['categories', 'users', 'items', 'items_view', 'favorite_items', 'cart', 'address', 'orders', 'contact_us'];
	if (!in_array($table, $allowedTables)) {
		throw new Exception("Invalid table name");
	}

	// بناء الاستعلام
	$ins = [];
	foreach ($data as $field => $value) {
		$ins[] = ":" . $field;
	}
	$ins = implode(',', $ins);
	$fields = implode(',', array_keys($data));

	$sql = "INSERT INTO $table ($fields) VALUES ($ins)";

	try {
		$stmt = $connect->prepare($sql);
		foreach ($data as $f => $v) {
			$stmt->bindValue(':' . $f, $v);
		}
		$stmt->execute();
		$count = $stmt->rowCount();

		if ($json) {
			echo json_encode(["status" => $count > 0 ? "success" : "failure"]);
		} else {
			return $count;
		}
	} catch (PDOException $e) {
		if ($json) {
			echo json_encode(["status" => "error", "message" => $e->getMessage()]);
		} else {
			throw $e;
		}
	}
}

// * update data 
function updateData($table, $data, $where, $json = true)
{
	include __DIR__ . "/connect.php";

	// تحقق من اسم الجدول
	$allowedTables = ['categories', 'users', 'items', 'items_view', 'favorite_items', 'cart', 'address', 'orders'];
	if (!in_array($table, $allowedTables)) {
		throw new Exception("Invalid table name");
	}

	// بناء الاستعلام
	$setParts = [];
	foreach ($data as $field => $value) {
		$setParts[] = "`$field` = :$field";
	}
	$setQuery = implode(', ', $setParts);

	$whereParts = [];
	foreach ($where as $field => $value) {
		$whereParts[] = "`$field` = :where_$field";
	}
	$whereQuery = implode(' AND ', $whereParts);

	$sql = "UPDATE `$table` SET $setQuery WHERE $whereQuery";

	try {
		$stmt = $connect->prepare($sql);

		// ربط قيم التحديث
		foreach ($data as $f => $v) {
			$stmt->bindValue(':' . $f, $v);
		}

		// ربط قيم الشرط
		foreach ($where as $f => $v) {
			$stmt->bindValue(':where_' . $f, $v);
		}

		$stmt->execute();
		$count = $stmt->rowCount();

		if ($json) {
			echo json_encode(["status" => $count > 0 ? "success" : "failure"]);
		} else {
			return $count;
		}
	} catch (PDOException $e) {
		if ($json) {
			echo json_encode(["status" => "error", "message" => $e->getMessage()]);
		} else {
			throw $e;
		}
	}
}



// * Notification
function sendGCM($title, $message, $topic, $pageid, $pagename)
{
	$projectId = "shop-lay"; // ضع هنا project_id من ملف JSON
	$url = "https://fcm.googleapis.com/v1/projects/$projectId/messages:send";

	$accessToken = getAccessTokenFromServiceAccount();

	$fields = [
		"message" => [
			"topic" => $topic,
			"notification" => [
				"title" => $title,
				"body"  => $message,
			],
			"data" => [
				"pageid" => $pageid,
				"pagename" => $pagename,
			]
		]
	];

	$headers = [
		"Authorization: Bearer $accessToken",
		"Content-Type: application/json; UTF-8"
	];

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));

	$result = curl_exec($ch);
	curl_close($ch);

	return $result;
}


// getAccessTokenFromServiceAccount
function getAccessTokenFromServiceAccount()
{

	// قراءة JSON من المتغير
	$firebaseConfig = json_decode(FIREBASE_JSON, true);
	$json = $firebaseConfig['project_id'];

	if (!$json) {
		throw new Exception("لم يتم تعريف متغير البيئة FIREBASE_JSON");
	}

	// إنشاء ملف JSON مؤقت على السيرفر
	$tmpPath = sys_get_temp_dir() . '/service-account.json';
	if (file_put_contents($tmpPath, $json) === false) {
		throw new Exception("فشل في إنشاء ملف JSON المؤقت: $tmpPath");
	}

	// تهيئة عميل Google API
	$client = new Google\Client();
	$client->setAuthConfig($tmpPath);
	$client->addScope('https://www.googleapis.com/auth/firebase.messaging');

	// جلب Access Token
	$token = $client->fetchAccessTokenWithAssertion();

	if (isset($token['access_token'])) {
		return $token['access_token'];
	} else {
		throw new Exception("فشل في جلب Access Token: " . json_encode($token));
	}
}

// notifications
function insertNotify($title, $body, $userId,  $topic, $pageId, $pageName)
{
	include __DIR__ . "/connect.php";

	$stmt = $connect->prepare('INSERT INTO `notifications`(`notifications_users_id`, `notifications_title`, `notifications_body`) VALUES (?, ?, ?)');
	$stmt->execute([$userId, $title, $body]);

	sendGCM($title, $body, $topic, $pageId, $pageName);

	return $stmt->rowCount();
}



/// ==========================>
// function uploadImage($file, $uploadDir = "uploads/")
// {
// 	// تحقق أن المجلد موجود وإذا لم يكن أنشئه
// 	if (!is_dir($uploadDir)) {
// 		mkdir($uploadDir, 0777, true);
// 	}

// 	// تحقق أن الملف تم رفعه بنجاح
// 	if (isset($file) && $file['error'] === UPLOAD_ERR_OK) {
// 		$fileTmpPath = $file['tmp_name'];
// 		$fileName = basename($file['name']);
// 		$targetFilePath = $uploadDir . '/' . $fileName;

// 		// انقل الملف إلى المجلد
// 		if (move_uploaded_file($fileTmpPath, $targetFilePath)) {
// 			return [
// 				"status" => "success",
// 				"path"   => $targetFilePath
// 			];
// 		} else {
// 			return [
// 				"status" => "error",
// 				"message" => "خطأ أثناء نقل الملف."
// 			];
// 		}
// 	} else {
// 		return [
// 			"status" => "error",
// 			"message" => "لم يتم رفع أي ملف."
// 		];
// 	}
// }



function uploadImage($file, $uploadDir = "uploads/", $maxSizeMB = 2)
{
	// المجلد موجود؟ إذا لا، أنشئه
	if (!is_dir($uploadDir)) {
		mkdir($uploadDir, 0777, true);
	}

	// تحقق من وجود الملف ونجاح رفعه
	if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
		return ["status" => "error", "message" => "لم يتم رفع أي ملف."];
	}

	// تحقق من حجم الملف
	$maxSizeBytes = $maxSizeMB * 1024 * 1024;
	if ($file['size'] > $maxSizeBytes) {
		return ["status" => "error", "message" => "حجم الملف أكبر من $maxSizeMB ميغابايت."];
	}

	// تحقق من امتداد الملف
	$allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
	$fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
	if (!in_array($fileExt, $allowedExtensions)) {
		return ["status" => "error", "message" => "نوع الملف غير مدعوم."];
	}

	// تحقق أن الملف فعليًا صورة
	$check = getimagesize($file['tmp_name']);
	if ($check === false) {
		return ["status" => "error", "message" => "الملف ليس صورة صحيحة."];
	}

	// توليد اسم عشوائي جديد
	$newFileName = bin2hex(random_bytes(16)) . "." . $fileExt;
	$targetFilePath = $uploadDir . '/' . $newFileName;

	// نقل الملف
	if (move_uploaded_file($file['tmp_name'], $targetFilePath)) {
		return ["status" => "success", "path" => $targetFilePath, "name" => $newFileName];
	} else {
		return ["status" => "error", "message" => "خطأ أثناء نقل الملف."];
	}
}
