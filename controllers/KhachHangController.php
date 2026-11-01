<?php
declare(strict_types=1);

require_once __DIR__ . '/ControllerChung.php';

class KhachHangController extends ControllerChung
{
    public function handle(string $action = 'dashboard'): void
    {
        $this->render('khachhang.php', ['rooms' => $this->model->danhSachPhongKhach()]);
    }
}
