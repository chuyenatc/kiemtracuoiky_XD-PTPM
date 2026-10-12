<?php
declare(strict_types=1);

session_start();

require_once __DIR__ . '/model.php';
$model = new Model();

$csrfToken = $_SESSION['csrf_token'] ?? null;
if (!is_string($csrfToken) || $csrfToken === '') {
    $csrfToken = bin2hex(random_bytes(32));
    $_SESSION['csrf_token'] = $csrfToken;
}

$requestedAction = $_GET['action'] ?? 'dashboard';
$action = is_string($requestedAction) ? $requestedAction : 'dashboard';
$view = 'khachhang.php';
$error = $_SESSION['flash_error'] ?? '';
$success = $_SESSION['flash_success'] ?? '';
unset($_SESSION['flash_error']);
$roomName = '';
$rooms = [];
$room = null;
$booking = null;
$admins = [];

function isValidDate(string $date): bool
{
    $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $date);
    return $parsed !== false && $parsed->format('Y-m-d') === $date;
}

function bookingReturnTo(mixed $value): ?string
{
    if (!is_string($value)
        || !preg_match('/\Aindex\.php\?action=book&id=([1-9][0-9]*)\z/', $value, $matches)) {
        return null;
    }
    return 'index.php?action=book&id=' . $matches[1];
}

function redirectToDashboard(): void
{
    $dashboard = ($_SESSION['role'] ?? '') === 'admin' ? 'admin' : 'dashboard';
    header('Location: index.php?action=' . $dashboard);
    exit;
}

function parseVndAmount(mixed $value): ?int
{
    if (!is_string($value) || !ctype_digit($value)) {
        return null;
    }

    $amount = filter_var($value, FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 1, 'max_range' => 999999999],
    ]);
    return $amount === false ? null : $amount;
}

function passwordResetMailConfigured(): bool
{
    $appUrl = rtrim((string) getenv('HOTEL_APP_URL'), '/');
    $from = (string) getenv('HOTEL_MAIL_FROM');
    $urlParts = parse_url($appUrl);

    return $appUrl !== ''
        && filter_var($appUrl, FILTER_VALIDATE_URL) !== false
        && is_array($urlParts)
        && in_array($urlParts['scheme'] ?? '', ['http', 'https'], true)
        && isset($urlParts['host'])
        && filter_var($from, FILTER_VALIDATE_EMAIL) !== false;
}

function sendPasswordResetEmail(string $email, string $token): void
{
    $appUrl = rtrim((string) getenv('HOTEL_APP_URL'), '/');
    $from = (string) getenv('HOTEL_MAIL_FROM');
    if (!passwordResetMailConfigured()) {
        error_log('Password reset email is not configured; set HOTEL_APP_URL and HOTEL_MAIL_FROM.');
        return;
    }

    $resetUrl = $appUrl . '/index.php?action=reset_password&token=' . rawurlencode($token);
    $message = "Chúng tôi nhận được yêu cầu đặt lại mật khẩu cho tài khoản của bạn.\n\n"
        . "Mở liên kết sau trong vòng 30 phút để đặt mật khẩu mới:\n"
        . $resetUrl . "\n\n"
        . "Nếu bạn không yêu cầu, hãy bỏ qua email này.";
    $headers = "From: {$from}\r\n"
        . "Content-Type: text/plain; charset=UTF-8\r\n";

    if (!mail($email, 'Dat lai mat khau tai khoan', $message, $headers)) {
        error_log('PHP mail() failed while sending a password reset email.');
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submittedToken = $_POST['csrf_token'] ?? null;
    if (!is_string($submittedToken) || !hash_equals($csrfToken, $submittedToken)) {
        $_SESSION['flash_error'] = 'Phiên làm việc không hợp lệ. Vui lòng thử lại.';
        redirectToDashboard();
    }
}

$adminActions = ['admin', 'admin_rooms', 'room_add', 'room_price', 'room_delete', 'admin_accounts', 'admin_add'];
if (in_array($action, $adminActions, true) && ($_SESSION['role'] ?? '') !== 'admin') {
    $_SESSION['flash_error'] = 'Bạn cần đăng nhập bằng tài khoản admin để sử dụng chức năng này.';
    header('Location: index.php?action=login');
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
                $email = $_POST['email'] ?? '';
                $password = $_POST['password'] ?? '';
                $confirmPassword = $_POST['confirm_password'] ?? '';
                if (!is_string($username) || trim($username) === ''
                    || strlen(trim($username)) > 50
                    || !is_string($email) || strlen(trim($email)) > 254
                    || filter_var(trim($email), FILTER_VALIDATE_EMAIL) === false
                    || !is_string($password) || strlen($password) < 6
                    || !is_string($confirmPassword) || $password !== $confirmPassword) {
                    $error = 'Vui lòng nhập tài khoản, email hợp lệ, mật khẩu từ 6 ký tự và xác nhận mật khẩu chính xác.';
                    break;
                }

                $username = trim($username);
                $email = trim($email);
                if (!$model->dangky($username, $password, $email)) {
                    $error = 'Tài khoản hoặc email này đã được sử dụng.';
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

        case 'forgot_password':
            $view = 'quenmatkhau.php';
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $email = $_POST['email'] ?? '';
                if (!is_string($email) || filter_var(trim($email), FILTER_VALIDATE_EMAIL) === false) {
                    $error = 'Vui lòng nhập địa chỉ email hợp lệ.';
                    break;
                }

                $email = trim($email);
                if (!passwordResetMailConfigured()) {
                    $error = 'Chức năng email chưa được cấu hình. Vui lòng liên hệ quản trị viên.';
                    break;
                }

                $token = $model->taoYeuCauDatLaiMatKhau($email);
                if ($token !== null) {
                    sendPasswordResetEmail($email, $token);
                }
                $success = 'Nếu email này đã đăng ký, hệ thống sẽ gửi liên kết còn hiệu lực trong 30 phút. Nếu không nhận được email, vui lòng liên hệ quản trị viên.';
            }
            break;

        case 'reset_password':
            $view = 'datlaimatkhau.php';
            $resetToken = $_SERVER['REQUEST_METHOD'] === 'POST'
                ? ($_POST['token'] ?? '')
                : ($_GET['token'] ?? '');
            if (!is_string($resetToken) || !preg_match('/\A[a-f0-9]{64}\z/', $resetToken)) {
                $error = 'Liên kết đặt lại mật khẩu không hợp lệ hoặc đã hết hạn.';
                $view = 'dangnhap.php';
                break;
            }

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $password = $_POST['password'] ?? '';
                $confirmPassword = $_POST['confirm_password'] ?? '';
                if (!is_string($password) || strlen($password) < 6
                    || !is_string($confirmPassword) || $password !== $confirmPassword) {
                    $error = 'Mật khẩu phải có ít nhất 6 ký tự và phần xác nhận phải khớp.';
                    break;
                }

                if (!$model->datLaiMatKhau($resetToken, $password)) {
                    $error = 'Liên kết đặt lại mật khẩu không hợp lệ hoặc đã hết hạn.';
                    break;
                }

                $_SESSION['flash_success'] = 'Mật khẩu đã được cập nhật. Vui lòng đăng nhập.';
                header('Location: index.php?action=login');
                exit;
            }
            break;

        case 'admin':
            $view = 'quanlyadmin.php';
            $rooms = $model->quanly();
            break;

        case 'admin_rooms':
            $view = 'adminphong.php';
            $rooms = $model->quanly();
            break;

        case 'room_add':
            $view = 'adminphong.php';
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                header('Location: index.php?action=admin_rooms');
                exit;
            }
            $submittedRoomName = $_POST['room_name'] ?? '';
            $nightlyRate = parseVndAmount($_POST['nightly_rate'] ?? null);
            if (!is_string($submittedRoomName)
                || trim($submittedRoomName) === ''
                || strlen(trim($submittedRoomName)) > 20
                || $nightlyRate === null) {
                $error = 'Tên phòng phải có từ 1 đến 20 ký tự và giá thuê mỗi đêm phải lớn hơn 0.';
                $roomName = is_string($submittedRoomName) ? trim($submittedRoomName) : '';
            } elseif (!$model->themPhong(trim($submittedRoomName), $nightlyRate)) {
                $error = 'Tên phòng đã tồn tại. Vui lòng chọn tên khác.';
                $roomName = trim($submittedRoomName);
            } else {
                $_SESSION['flash_success'] = 'Đã thêm phòng mới.';
                header('Location: index.php?action=admin_rooms');
                exit;
            }
            $rooms = $model->quanly();
            break;

        case 'room_price':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                header('Location: index.php?action=admin_rooms');
                exit;
            }
            $roomId = filter_input(INPUT_POST, 'room_id', FILTER_VALIDATE_INT);
            $nightlyRate = parseVndAmount($_POST['nightly_rate'] ?? null);
            if (!$roomId || $nightlyRate === null || !$model->capNhatGiaPhong($roomId, $nightlyRate)) {
                $error = 'Không thể cập nhật giá. Hãy kiểm tra phòng và nhập giá lớn hơn 0.';
            } else {
                $_SESSION['flash_success'] = 'Đã cập nhật giá thuê phòng.';
                header('Location: index.php?action=admin_rooms');
                exit;
            }
            $view = 'adminphong.php';
            $rooms = $model->quanly();
            break;

        case 'room_delete':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                header('Location: index.php?action=admin_rooms');
                exit;
            }
            $roomId = filter_input(INPUT_POST, 'room_id', FILTER_VALIDATE_INT);
            if (!$roomId || !$model->xoaPhong($roomId)) {
                $error = 'Không thể xóa phòng không tồn tại, đang được thuê hoặc đã có lịch sử đặt.';
            } else {
                $_SESSION['flash_success'] = 'Đã xóa phòng.';
                header('Location: index.php?action=admin_rooms');
                exit;
            }
            $view = 'adminphong.php';
            $rooms = $model->quanly();
            break;

        case 'admin_accounts':
            $view = 'admintaikhoan.php';
            $admins = $model->danhSachAdmin();
            break;

        case 'admin_add':
            $view = 'admintaikhoan.php';
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                header('Location: index.php?action=admin_accounts');
                exit;
            }
            $username = $_POST['username'] ?? '';
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            if (!is_string($username) || trim($username) === ''
                || strlen(trim($username)) > 50
                || !is_string($email) || strlen(trim($email)) > 254
                || filter_var(trim($email), FILTER_VALIDATE_EMAIL) === false
                || !is_string($password) || strlen($password) < 6
                || !is_string($confirmPassword) || $password !== $confirmPassword) {
                $error = 'Nhập tên tài khoản, email hợp lệ, mật khẩu ít nhất 6 ký tự và xác nhận chính xác.';
            } elseif (!$model->taoTaiKhoanAdmin(trim($username), $password, trim($email))) {
                $error = 'Tên tài khoản hoặc email đã được sử dụng.';
            } else {
                $_SESSION['flash_success'] = 'Đã tạo tài khoản admin.';
                header('Location: index.php?action=admin_accounts');
                exit;
            }
            $admins = $model->danhSachAdmin();
            break;

        case 'customer':
            $view = 'khachhang.php';
            $rooms = $model->danhSachPhongKhach();
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

            $view = 'datphongadmin.php';
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
            $view = 'quanlyadmin.php';
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

            $view = 'giahanadmin.php';
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
            if (($_SESSION['role'] ?? '') === 'admin') {
                $view = 'quanlyadmin.php';
                $rooms = $model->quanly();
            } else {
                $view = 'khachhang.php';
                $rooms = $model->danhSachPhongKhach();
            }
            break;
    }
} catch (InvalidArgumentException $exception) {
    $error = $exception->getMessage();
    if (($_SESSION['role'] ?? '') === 'admin') {
        $rooms = $model->quanly();
        $view = 'quanlyadmin.php';
    } else {
        $rooms = $model->danhSachPhongKhach();
        $view = 'khachhang.php';
    }
}

require __DIR__ . '/views/mainadmin.php';
