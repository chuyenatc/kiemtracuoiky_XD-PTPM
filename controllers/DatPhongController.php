<?php
declare(strict_types=1);

require_once __DIR__ . '/ControllerChung.php';

class DatPhongController extends ControllerChung
{
    public function handle(string $action = 'book'): void
    {
        $roomId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if (!$roomId) {
            throw new InvalidArgumentException('Phòng không hợp lệ.');
        }

        $room = $this->model->layPhong($roomId);
        if (!$room || $room['status'] !== 'Trống') {
            throw new InvalidArgumentException('Phòng không tồn tại hoặc đã được thuê.');
        }

        if ($this->isPost()) {
            $customerName = $_POST['customer_name'] ?? '';
            $checkIn = $_POST['check_in'] ?? '';
            $checkOut = $_POST['check_out'] ?? '';
            if (!is_string($customerName) || trim($customerName) === ''
                || !is_string($checkIn) || !$this->validDate($checkIn)
                || !is_string($checkOut) || !$this->validDate($checkOut)
                || $checkOut <= $checkIn) {
                $this->error = 'Vui lòng nhập tên khách và ngày trả phòng sau ngày nhận phòng.';
            } elseif (!$this->model->datphong($roomId, trim($customerName), $checkIn, $checkOut)) {
                $this->error = 'Phòng vừa được đặt bởi người khác. Vui lòng chọn phòng khác.';
            } else {
                $this->redirectToDashboard();
            }
        }

        $this->render('datphongadmin.php', ['room' => $room]);
    }
}
