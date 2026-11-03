<?php
declare(strict_types=1);

require_once __DIR__ . '/ControllerChung.php';

class DangKyController extends ControllerChung
{
    public function handle(string $action = 'register'): void
    {
        $returnTo = $this->bookingReturnTo($this->isPost()
            ? ($_POST['return_to'] ?? null)
            : ($_GET['return_to'] ?? null));

        if ($this->isPost()) {
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
                $this->error = 'Vui lòng nhập tài khoản, email hợp lệ, mật khẩu từ 6 ký tự và xác nhận mật khẩu chính xác.';
            } else {
                $username = trim($username);
                $email = trim($email);
                if (!$this->model->dangky($username, $password, $email)) {
                    $this->error = 'Tài khoản hoặc email này đã được sử dụng.';
                } else {
                    $user = $this->model->dangnhap($username, $password);
                    if (!$user) {
                        throw new RuntimeException('Không thể đăng nhập tự động sau khi đăng ký.');
                    }
                    session_regenerate_id(true);
                    $_SESSION['user'] = $user['username'];
                    $_SESSION['user_id'] = (int) $user['id'];
                    $_SESSION['role'] = $user['role'];
                    header('Location: ' . ($returnTo ?? 'index.php?action=dashboard'));
                    exit;
                }
            }
        }

        $this->render('dangky.php', ['returnTo' => $returnTo]);
    }
}
