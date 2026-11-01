<?php
declare(strict_types=1);

require_once __DIR__ . '/../model.php';

abstract class ControllerChung
{
    protected Model $model;
    protected string $csrfToken;
    protected string $error;
    protected string $success;

    public function __construct(Model $model, string $csrfToken, string $error = '', string $success = '')
    {
        $this->model = $model;
        $this->csrfToken = $csrfToken;
        $this->error = $error;
        $this->success = $success;
    }

    protected function render(string $view, array $data = []): void
    {
        $action = $_GET['action'] ?? 'dashboard';
        $csrfToken = $this->csrfToken;
        $error = $this->error;
        $success = $this->success;
        extract($data, EXTR_SKIP);
        require __DIR__ . '/../views/mainadmin.php';
    }

    protected function redirect(string $action): void
    {
        header('Location: index.php?action=' . rawurlencode($action));
        exit;
    }

    protected function redirectToDashboard(): void
    {
        $this->redirect(($_SESSION['role'] ?? '') === 'admin' ? 'admin' : 'dashboard');
    }

    protected function isPost(): bool
    {
        return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
    }

    protected function validDate(string $date): bool
    {
        $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $date);
        return $parsed !== false && $parsed->format('Y-m-d') === $date;
    }

    protected function bookingReturnTo(mixed $value): ?string
    {
        if (!is_string($value)
            || !preg_match('/\Aindex\.php\?action=book&id=([1-9][0-9]*)\z/', $value, $matches)) {
            return null;
        }

        return 'index.php?action=book&id=' . $matches[1];
    }

    protected function parseVndAmount(mixed $value): ?int
    {
        if (!is_string($value) || !ctype_digit($value)) {
            return null;
        }

        $amount = filter_var($value, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1, 'max_range' => 999999999],
        ]);
        return $amount === false ? null : $amount;
    }

    protected function mailConfigured(): bool
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

    protected function sendPasswordResetEmail(string $email, string $token): void
    {
        $appUrl = rtrim((string) getenv('HOTEL_APP_URL'), '/');
        $from = (string) getenv('HOTEL_MAIL_FROM');
        if (!$this->mailConfigured()) {
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
}
