<?php
declare(strict_types=1);

require_once __DIR__ . '/ControllerChung.php';

class DangNhapController extends ControllerChung
{
    public function handle(string $action = 'login'): void
    {
        $returnTo = $this->bookingReturnTo($this->isPost()
            ? ($_POST['return_to'] ?? null)
            : ($_GET['return_to'] ?? null));

        if ($this->isPost()) {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            if (!is_string($username) || !is_string($password)) {
                $this->error = 'Thông tin đăng nhập không hợp lệ.';
            } else {
                $user = $this->model->dangnhap($username, $password);
                if ($user) {
                    session_regenerate_id(true);
                    $_SESSION['user'] = $user['username'];
                    $_SESSION['role'] = $user['role'];
                    header('Location: ' . ($returnTo ?? 'index.php?action=dashboard'));
                    exit;
                }
                $this->error = 'Sai tài khoản hoặc mật khẩu!';
            }
        }

        $this->render('dangnhap.php', ['returnTo' => $returnTo]);
    }
}
