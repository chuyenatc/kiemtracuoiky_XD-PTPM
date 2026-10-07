<?php
session_start();

require_once __DIR__ . '/model.php';
$model = new Model();

$requestedAction = $_GET['action'] ?? 'dashboard';
$action = is_string($requestedAction) ? $requestedAction : 'dashboard';
$view = 'quanly.php';
$error = $_SESSION['flash_error'] ?? '';
unset($_SESSION['flash_error']);
$rooms = [];
$room = null;
$booking = null;

function isValidDate(string $date): bool
{
    $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $date);
    return $parsed !== false && $parsed->format('Y-m-d') === $date;
}

function bookingReturnTo($value): ?string
{
    if (!is_string($value)
        || !preg_match('/\Aindex\.php\?action=book&id=([1-9][0-9]*)\z/', $value, $matches)) {
        return null;
    }
    return 'index.php?action=book&id=' . $matches[1];
}

function redirectToDashboard(): void
{
    header('Location: index.php?action=dashboard');
    exit;
}

if ($action === 'book' && !isset($_SESSION['user'])) {
    $roomId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if ($roomId) {
        $returnTo = 'index.php?action=book&id=' . $roomId;
        header('Location: index.php?' . http_build_query([
            'action' => 'login',
            'return_to' => $returnTo,
        ]));
        exit;
    }
    redirectToDashboard();
}

if (in_array($action, ['checkout', 'extend'], true)
    && ($_SESSION['role'] ?? '') !== 'admin') {
    $_SESSION['flash_error'] = 'Bạn không có quyền thực hiện thao tác quản lý này.';
    redirectToDashboard();
}

try {
    switch ($action) {
        case 'login':
            $view = 'dangnhap.php';
            $returnTo = bookingReturnTo($_SERVER['REQUEST_METHOD'] === 'POST'
                ? ($_POST['return_to'] ?? null)
                : ($_GET['return_to'] ?? null));
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $username = $_POST['username'] ?? '';
                $password = $_POST['password'] ?? '';
                if (!is_string($username) || !is_string($password)) {
                    $error = 'Thông tin đăng nhập không hợp lệ.';
                    break;
                }

                $user = $model->dangnhap($username, $password);
                if ($user) {
                    session_regenerate_id(true);
                    $_SESSION['user'] = $user['username'];
                    $_SESSION['role'] = $user['role'];
                    header('Location: ' . ($returnTo ?? 'index.php?action=dashboard'));
                    exit;
                }
                $error = 'Sai tài khoản hoặc mật khẩu!';
            }
            break;

        case 'register':
            $view = 'dangky.php';
            $returnTo = bookingReturnTo($_SERVER['REQUEST_METHOD'] === 'POST'
                ? ($_POST['return_to'] ?? null)
                : ($_GET['return_to'] ?? null));
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $username = $_POST['username'] ?? '';
                $password = $_POST['password'] ?? '';
                $confirmPassword = $_POST['confirm_password'] ?? '';
                if (!is_string($username) || trim($username) === ''
                    || strlen(trim($username)) > 50
                    || !is_string($password) || strlen($password) < 6
                    || !is_string($confirmPassword) || $password !== $confirmPassword) {
                    $error = 'Vui lòng nhập tài khoản hợp lệ, mật khẩu từ 6 ký tự và xác nhận mật khẩu chính xác.';
                    break;
                }

                $username = trim($username);
                if (!$model->dangky($username, $password)) {
                    $error = 'Tài khoản này đã tồn tại. Vui lòng chọn tên tài khoản khác.';
                    break;
                }

                $user = $model->dangnhap($username, $password);
                if (!$user) {
                    throw new RuntimeException('Không thể đăng nhập tự động sau khi đăng ký.');
                }
                session_regenerate_id(true);
                $_SESSION['user'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                header('Location: ' . ($returnTo ?? 'index.php?action=dashboard'));
                exit;
            }
            break;

        case 'logout':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $_SESSION = [];
                session_destroy();
                redirectToDashboard();
            }
            redirectToDashboard();
            break;

        case 'book':
            $roomId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
            if (!$roomId) {
                throw new InvalidArgumentException('Phòng không hợp lệ.');
            }

            $room = $model->layPhong($roomId);
            if (!$room || $room['status'] !== 'Trống') {
                throw new InvalidArgumentException('Phòng không tồn tại hoặc đã được thuê.');
            }

            $view = 'datphong.php';
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $customerName = $_POST['customer_name'] ?? '';
                $checkIn = $_POST['check_in'] ?? '';
                $checkOut = $_POST['check_out'] ?? '';
                if (!is_string($customerName) || trim($customerName) === ''
                    || !is_string($checkIn) || !isValidDate($checkIn)
                    || !is_string($checkOut) || !isValidDate($checkOut)
                    || $checkOut <= $checkIn) {
                    $error = 'Vui lòng nhập tên khách và ngày trả phòng sau ngày nhận phòng.';
                    break;
                }

                if (!$model->datphong($roomId, trim($customerName), $checkIn, $checkOut)) {
                    $error = 'Phòng vừa được đặt bởi người khác. Vui lòng chọn phòng khác.';
                    break;
                }
                redirectToDashboard();
            }
            break;

        case 'checkout':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                redirectToDashboard();
            }
            $roomId = filter_input(INPUT_POST, 'room_id', FILTER_VALIDATE_INT);
            $bookingId = filter_input(INPUT_POST, 'booking_id', FILTER_VALIDATE_INT);
            if (!$roomId || !$bookingId || !$model->traphong($roomId, $bookingId)) {
                $error = 'Thông tin trả phòng không hợp lệ hoặc phòng không còn được thuê.';
            }
            $rooms = $model->quanly();
            break;

        case 'extend':
            $bookingId = $_SERVER['REQUEST_METHOD'] === 'POST'
                ? filter_input(INPUT_POST, 'booking_id', FILTER_VALIDATE_INT)
                : filter_input(INPUT_GET, 'booking_id', FILTER_VALIDATE_INT);
            if (!$bookingId) {
                throw new InvalidArgumentException('Thông tin đặt phòng không hợp lệ.');
            }

            $booking = $model->layLuotThue($bookingId);
            if (!$booking) {
                throw new InvalidArgumentException('Không tìm thấy lượt thuê đang hoạt động.');
            }

            $view = 'giahan.php';
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $newCheckout = $_POST['new_checkout'] ?? '';
                if (!is_string($newCheckout) || !isValidDate($newCheckout)
                    || $newCheckout <= $booking['check_out']) {
                    $error = 'Ngày trả phòng mới phải sau ngày trả phòng hiện tại.';
                    break;
                }

                if (!$model->giahan($bookingId, $newCheckout)) {
                    $error = 'Không thể gia hạn lượt thuê này.';
                    break;
                }
                redirectToDashboard();
            }
            break;

        case 'dashboard':
        default:
            $view = 'quanly.php';
            $rooms = $model->quanly();
            break;
    }
} catch (InvalidArgumentException $exception) {
    $error = $exception->getMessage();
    $rooms = $model->quanly();
    $view = 'quanly.php';
}

require __DIR__ . '/views/main.php';
