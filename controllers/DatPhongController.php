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
            $userId = filter_var($_SESSION['user_id'] ?? null, FILTER_VALIDATE_INT);
            $customerName = $_SESSION['user'] ?? '';
            $checkIn = $_POST['check_in'] ?? '';
            $checkOut = $_POST['check_out'] ?? '';
            $paymentMethod = $_POST['payment_method'] ?? '';
            if (!$userId
                || !is_string($customerName) || trim($customerName) === ''
                || !is_string($checkIn) || !$this->validDate($checkIn)
                || !is_string($checkOut) || !$this->validDate($checkOut)
                || $checkOut <= $checkIn
                || !is_string($paymentMethod)
                || !in_array($paymentMethod, ['direct', 'online'], true)) {
                $this->error = 'Thông tin tài khoản, ngày thuê hoặc phương thức thanh toán không hợp lệ.';
            } elseif (!$this->model->datphong(
                $roomId,
                (int) $userId,
                trim($customerName),
                $checkIn,
                $checkOut,
                $paymentMethod
            )) {
                $this->error = 'Phòng vừa được đặt bởi người khác. Vui lòng chọn phòng khác.';
            } else {
                $this->redirectToDashboard();
            }
        }

        $this->render('datphongadmin.php', [
            'room' => $room,
            'customerName' => (string) ($_SESSION['user'] ?? ''),
        ]);
    }
}
