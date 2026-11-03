<?php
declare(strict_types=1);

require_once __DIR__ . '/ControllerChung.php';

class KhachHangController extends ControllerChung
{
    public function handle(string $action = 'dashboard'): void
    {
        if ($action === 'room_detail') {
            $roomId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
            $room = $roomId ? $this->model->layPhong($roomId) : null;
            if (!$room) {
                throw new InvalidArgumentException('Không tìm thấy phòng cần xem.');
            }

            $this->render('thongtinphong.php', ['room' => $room]);
            return;
        }

        $userId = filter_var($_SESSION['user_id'] ?? null, FILTER_VALIDATE_INT);
        $bookings = $userId && ($_SESSION['role'] ?? '') === 'customer'
            ? $this->model->danhSachLuotThueKhach((int) $userId)
            : [];
        $this->render('khachhang.php', [
            'rooms' => $this->model->danhSachPhongKhach(),
            'bookings' => $bookings,
        ]);
    }
}
