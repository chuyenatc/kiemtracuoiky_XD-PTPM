<?php
declare(strict_types=1);

require_once __DIR__ . '/ControllerChung.php';

class QuanLyAdminController extends ControllerChung
{
    public function handle(string $action = 'admin'): void
    {
        if ($action === 'checkout') {
            if (!$this->isPost()) {
                $this->redirectToDashboard();
            }

            $roomId = filter_input(INPUT_POST, 'room_id', FILTER_VALIDATE_INT);
            $bookingId = filter_input(INPUT_POST, 'booking_id', FILTER_VALIDATE_INT);
            if (!$roomId || !$bookingId || !$this->model->traphong($roomId, $bookingId)) {
                $this->error = 'Thông tin trả phòng không hợp lệ hoặc phòng không còn được thuê.';
            }
        }

        $this->render('quanlyadmin.php', ['rooms' => $this->model->quanly()]);
    }
}
