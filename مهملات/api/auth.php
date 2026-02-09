<?php
define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/config/config.php';
require_once BASE_PATH . '/classes/helpers/functions.php';

header('Content-Type: application/json; charset=utf-8');

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? $_POST['action'] ?? '';
$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$action = $action ?: ($input['action'] ?? '');

try {
    switch ($action) {
        case 'login':
            if ($method !== 'POST') jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);

            $email = Sanitizer::email($input['email'] ?? '');
            $password = $input['password'] ?? '';

            $validator = new Validator();
            $validator->validate(['email' => $email, 'password' => $password], [
                'email' => 'required|email',
                'password' => 'required|min:6',
            ]);

            if ($validator->getErrors()) {
                jsonResponse(['success' => false, 'message' => $validator->getFirstError()], 422);
            }

            $result = Auth::login($email, $password);
            if ($result['success']) {
                jsonResponse(['success' => true, 'data' => ['user' => Auth::getCurrentUser()]]);
            } else {
                jsonResponse(['success' => false, 'message' => $result['error']], 401);
            }
            break;

        case 'register':
            if ($method !== 'POST') jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);

            $data = Sanitizer::cleanArray($input);
            $validator = new Validator();
            $validator->validate($data, [
                'full_name' => 'required|min:3',
                'email' => 'required|email',
                'password' => 'required|min:8',
            ]);

            if ($validator->getErrors()) {
                jsonResponse(['success' => false, 'message' => $validator->getFirstError()], 422);
            }

            $result = Auth::register($data['email'], $input['password'], $data['full_name'], $data['phone'] ?? '');
            if ($result['success']) {
                jsonResponse(['success' => true, 'data' => ['user_id' => $result['user_id']]]);
            } else {
                jsonResponse(['success' => false, 'message' => $result['error']], 422);
            }
            break;

        case 'logout':
            Auth::logout();
            jsonResponse(['success' => true, 'message' => 'تم تسجيل الخروج']);
            break;

        case 'check':
            jsonResponse([
                'success' => true,
                'data' => [
                    'authenticated' => Auth::isLoggedIn(),
                    'user' => Auth::isLoggedIn() ? Auth::getCurrentUser() : null,
                ]
            ]);
            break;

        default:
            jsonResponse(['success' => false, 'message' => 'الإجراء غير معروف'], 400);
    }
} catch (\Throwable $e) {
    error_log("API Auth Error: " . $e->getMessage());
    jsonResponse(['success' => false, 'message' => 'حدث خطأ في الخادم'], 500);
}
