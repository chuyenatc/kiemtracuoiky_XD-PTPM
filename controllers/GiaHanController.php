<?php
declare(strict_types=1);

require_once __DIR__ . '/ControllerChung.php';

class GiaHanController extends ControllerChung
{
    public function handle(string $action = 'extend'): void
    {
        $bookingId = $this->isPost()
            ? filter_input(INPUT_POST, 'booking_id', FILTER_VALIDATE_INT)
            : filter_input(INPUT_GET, 'booking_id', FILTER_VALIDATE_INT);
        if (!$bookingId) {
            throw new InvalidArgumentException('Thông tin đặt phòng không hợp lệ.');
        }

        $booking = $this->model->layLuotThue($bookingId);
        if (!$booking) {
            throw new InvalidArgumentException('Không tìm thấy lượt thuê đang hoạt động.');
        }

        if ($this->isPost()) {
            $newCheckout = $_POST['new_checkout'] ?? '';
            if (!is_string($newCheckout) || !$this->validDate($newCheckout)
                || $newCheckout <= $booking['check_out']) {
                $this->error = 'Ngày trả phòng mới phải sau ngày trả phòng hiện tại.';
            } elseif (!$this->model->giahan($bookingId, $newCheckout)) {
                $this->error = 'Không thể gia hạn lượt thuê này.';
            } else {
                $this->redirectToDashboard();
            }
        }

        $this->render('giahanadmin.php', ['booking' => $booking]);
    }
}
