<?php
declare(strict_types=1);

require_once __DIR__ . '/ControllerChung.php';

class DatLaiMatKhauController extends ControllerChung
{
    public function handle(string $action = 'reset_password'): void
    {
        $resetToken = $this->isPost()
            ? ($_POST['token'] ?? '')
            : ($_GET['token'] ?? '');
        if (!is_string($resetToken) || !preg_match('/\A[a-f0-9]{64}\z/', $resetToken)) {
            $this->error = 'Liên kết đặt lại mật khẩu không hợp lệ hoặc đã hết hạn.';
            $this->render('dangnhap.php');
            return;
        }

        if ($this->isPost()) {
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            if (!is_string($password) || strlen($password) < 6
                || !is_string($confirmPassword) || $password !== $confirmPassword) {
                $this->error = 'Mật khẩu phải có ít nhất 6 ký tự và phần xác nhận phải khớp.';
            } elseif (!$this->model->datLaiMatKhau($resetToken, $password)) {
                $this->error = 'Liên kết đặt lại mật khẩu không hợp lệ hoặc đã hết hạn.';
            } else {
                $_SESSION['flash_success'] = 'Mật khẩu đã được cập nhật. Vui lòng đăng nhập.';
                header('Location: index.php?action=login');
                exit;
            }
        }

        $this->render('datlaimatkhau.php', ['resetToken' => $resetToken]);
    }
}
