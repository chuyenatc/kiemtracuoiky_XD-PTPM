<?php
declare(strict_types=1);

require_once __DIR__ . '/model.php';
require_once __DIR__ . '/controllers/ControllerChung.php';

session_start();

$csrfToken = $_SESSION['csrf_token'] ?? null;
if (!is_string($csrfToken) || $csrfToken === '') {
    $csrfToken = bin2hex(random_bytes(32));
    $_SESSION['csrf_token'] = $csrfToken;
}

$requestedAction = $_GET['action'] ?? 'dashboard';
$action = is_string($requestedAction) ? $requestedAction : 'dashboard';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $submittedToken = $_POST['csrf_token'] ?? null;
    if (!is_string($submittedToken) || !hash_equals($csrfToken, $submittedToken)) {
        $_SESSION['flash_error'] = 'Phiên làm việc không hợp lệ. Vui lòng thử lại.';
        header('Location: index.php?action=' . (($_SESSION['role'] ?? '') === 'admin' ? 'admin' : 'dashboard'));
        exit;
    }
}

$error = $_SESSION['flash_error'] ?? '';
$success = $_SESSION['flash_success'] ?? '';
unset($_SESSION['flash_error']);
$model = new Model();
$controllerArguments = [$model, $csrfToken, $error, $success];

$adminActions = [
    'admin', 'admin_rooms', 'room_add', 'room_price', 'room_delete',
    'room_photo', 'room_image_delete', 'room_description', 'admin_accounts', 'admin_add', 'monthly_report',
];
if (in_array($action, $adminActions, true) && ($_SESSION['role'] ?? '') !== 'admin') {
    $_SESSION['flash_error'] = 'Bạn cần đăng nhập bằng tài khoản admin để sử dụng chức năng này.';
    header('Location: index.php?action=login');
    exit;
}

if (in_array($action, ['checkout', 'extend'], true) && ($_SESSION['role'] ?? '') !== 'admin') {
    $_SESSION['flash_error'] = 'Bạn không có quyền thực hiện thao tác quản lý này.';
    header('Location: index.php?action=' . (($_SESSION['role'] ?? '') === 'admin' ? 'admin' : 'dashboard'));
    exit;
}

if ($action === 'book' && !isset($_SESSION['user'])) {
    $roomId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if ($roomId) {
        header('Location: index.php?' . http_build_query([
            'action' => 'login',
            'return_to' => 'index.php?action=book&id=' . $roomId,
        ]));
        exit;
    }
    header('Location: index.php?action=dashboard');
    exit;
}

if ($action === 'logout') {
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
        header('Location: index.php?action=' . (($_SESSION['role'] ?? '') === 'admin' ? 'admin' : 'dashboard'));
        exit;
    }
    $_SESSION = [];
    session_destroy();
    header('Location: index.php?action=dashboard');
    exit;
}

$controllers = [
    'login' => ['DangNhapController.php', 'DangNhapController'],
    'register' => ['DangKyController.php', 'DangKyController'],
    'forgot_password' => ['QuenMatKhauController.php', 'QuenMatKhauController'],
    'reset_password' => ['DatLaiMatKhauController.php', 'DatLaiMatKhauController'],
    'admin' => ['QuanLyAdminController.php', 'QuanLyAdminController'],
    'checkout' => ['QuanLyAdminController.php', 'QuanLyAdminController'],
    'admin_rooms' => ['AdminPhongController.php', 'AdminPhongController'],
    'room_add' => ['AdminPhongController.php', 'AdminPhongController'],
    'room_price' => ['AdminPhongController.php', 'AdminPhongController'],
    'room_delete' => ['AdminPhongController.php', 'AdminPhongController'],
    'room_photo' => ['AdminPhongController.php', 'AdminPhongController'],
    'room_image_delete' => ['AdminPhongController.php', 'AdminPhongController'],
    'room_description' => ['AdminPhongController.php', 'AdminPhongController'],
    'admin_accounts' => ['AdminTaiKhoanController.php', 'AdminTaiKhoanController'],
    'admin_add' => ['AdminTaiKhoanController.php', 'AdminTaiKhoanController'],
    'monthly_report' => ['BaoCaoController.php', 'BaoCaoController'],
    'customer' => ['KhachHangController.php', 'KhachHangController'],
    'room_detail' => ['KhachHangController.php', 'KhachHangController'],
    'dashboard' => [($_SESSION['role'] ?? '') === 'admin' ? 'QuanLyAdminController.php' : 'KhachHangController.php',
        ($_SESSION['role'] ?? '') === 'admin' ? 'QuanLyAdminController' : 'KhachHangController'],
    'book' => ['DatPhongController.php', 'DatPhongController'],
    'extend' => ['GiaHanController.php', 'GiaHanController'],
];

[$controllerFile, $controllerClass] = $controllers[$action] ?? $controllers['dashboard'];
require_once __DIR__ . '/controllers/' . $controllerFile;
$controller = new $controllerClass(...$controllerArguments);

try {
    $controller->handle($action);
} catch (InvalidArgumentException $exception) {
    $fallbackClass = ($_SESSION['role'] ?? '') === 'admin'
        ? 'QuanLyAdminController'
        : 'KhachHangController';
    if ($fallbackClass === 'QuanLyAdminController') {
        require_once __DIR__ . '/controllers/QuanLyAdminController.php';
    } else {
        require_once __DIR__ . '/controllers/KhachHangController.php';
    }
    $fallback = new $fallbackClass($model, $csrfToken, $exception->getMessage(), $success);
    $fallback->handle('dashboard');
}
