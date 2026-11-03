<?php
declare(strict_types=1);

require_once __DIR__ . '/ControllerChung.php';

class GiaHanController extends ControllerChung
{
    public function handle(string $action = 'extend'): void
    {
        $isAdmin = ($_SESSION['role'] ?? '') === 'admin';
        $ownerId = $isAdmin ? null : filter_var($_SESSION['user_id'] ?? null, FILTER_VALIDATE_INT);
        if (!$isAdmin && (!$ownerId || ($_SESSION['role'] ?? '') !== 'customer')) {
            $this->redirectToDashboard();
        }

        $bookingId = $this->isPost()
            ? filter_input(INPUT_POST, 'booking_id', FILTER_VALIDATE_INT)
            : filter_input(INPUT_GET, 'booking_id', FILTER_VALIDATE_INT);
        if (!$bookingId) {
            throw new InvalidArgumentException('Thông tin đặt phòng không hợp lệ.');
        }

        $booking = $this->model->layLuotThue($bookingId, $ownerId ? (int) $ownerId : null);
        if (!$booking) {
            throw new InvalidArgumentException('Không tìm thấy lượt thuê đang hoạt động.');
        }

        if ($this->isPost()) {
            $newCheckout = $_POST['new_checkout'] ?? '';
            if (!is_string($newCheckout) || !$this->validDate($newCheckout)
                || $newCheckout <= $booking['check_out']) {
                $this->error = 'Ngày trả phòng mới phải sau ngày trả phòng hiện tại.';
            } elseif (!$this->model->giahan($bookingId, $newCheckout, $ownerId ? (int) $ownerId : null)) {
                $this->error = 'Không thể gia hạn lượt thuê này.';
            } else {
                $this->redirectToDashboard();
            }
        }

        $this->render('giahanadmin.php', ['booking' => $booking]);
    }
}
