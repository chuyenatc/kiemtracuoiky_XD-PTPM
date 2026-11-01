<?php
declare(strict_types=1);

require_once __DIR__ . '/ControllerChung.php';

class QuenMatKhauController extends ControllerChung
{
    public function handle(string $action = 'forgot_password'): void
    {
        if ($this->isPost()) {
            $email = $_POST['email'] ?? '';
            if (!is_string($email) || filter_var(trim($email), FILTER_VALIDATE_EMAIL) === false) {
                $this->error = 'Vui lòng nhập địa chỉ email hợp lệ.';
            } elseif (!$this->mailConfigured()) {
                $this->error = 'Chức năng email chưa được cấu hình. Vui lòng liên hệ quản trị viên.';
            } else {
                $email = trim($email);
                $token = $this->model->taoYeuCauDatLaiMatKhau($email);
                if ($token !== null) {
                    $this->sendPasswordResetEmail($email, $token);
                }
                $this->success = 'Nếu email này đã đăng ký, hệ thống sẽ gửi liên kết còn hiệu lực trong 30 phút. Nếu không nhận được email, vui lòng liên hệ quản trị viên.';
            }
        }

        $this->render('quenmatkhau.php');
    }
}
