<?php
declare(strict_types=1);

require_once __DIR__ . '/ControllerChung.php';

class AdminTaiKhoanController extends ControllerChung
{
    public function handle(string $action = 'admin_accounts'): void
    {
        if ($action === 'admin_add') {
            if (!$this->isPost()) {
                $this->redirect('admin_accounts');
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
                $this->error = 'Nhập tên tài khoản, email hợp lệ, mật khẩu ít nhất 6 ký tự và xác nhận chính xác.';
            } elseif (!$this->model->taoTaiKhoanAdmin(trim($username), $password, trim($email))) {
                $this->error = 'Tên tài khoản hoặc email đã được sử dụng.';
            } else {
                $_SESSION['flash_success'] = 'Đã tạo tài khoản admin.';
                $this->redirect('admin_accounts');
            }
        }

        $this->render('admintaikhoan.php', ['admins' => $this->model->danhSachAdmin()]);
    }
}
