<?php
declare(strict_types=1);

require_once __DIR__ . '/ControllerChung.php';

class ThanhToanController extends ControllerChung
{
    public function handle(string $action = 'payments'): void
    {
        if ($action === 'payment_record') {
            if (!$this->isPost()) {
                $this->redirect('payments');
            }

            $bookingId = filter_input(INPUT_POST, 'booking_id', FILTER_VALIDATE_INT);
            $amount = $this->parseVndAmount($_POST['amount'] ?? null, true);
            $reconciledMethod = $_POST['payment_method'] ?? null;
            if ($reconciledMethod === '') {
                $reconciledMethod = null;
            }
            if (!$bookingId
                || $amount === null
                || ($reconciledMethod !== null && !is_string($reconciledMethod))
                || !$this->model->ghiNhanThanhToan(
                    $bookingId,
                    $amount,
                    is_string($reconciledMethod) ? $reconciledMethod : null
                )) {
                $this->error = 'Không thể ghi nhận khoản thu. Hãy kiểm tra lượt thuê, số tiền và số dư còn lại.';
            } else {
                $_SESSION['flash_success'] = 'Đã ghi nhận khoản thanh toán.';
                $this->redirect('payments');
            }
        }

        $bookings = $this->model->danhSachThanhToan();
        $summary = [
            'booking_count' => count($bookings),
            'deposit_pending' => 0,
            'paid_amount' => 0,
            'balance_due' => 0,
            'unreconciled_count' => 0,
        ];
        foreach ($bookings as $booking) {
            if ($booking['payment_method'] === 'unknown') {
                $summary['unreconciled_count']++;
                continue;
            }
            $summary['deposit_pending'] += max(
                (int) $booking['deposit_required'] - (int) $booking['paid_amount'],
                0
            );
            $summary['paid_amount'] += (int) $booking['paid_amount'];
            $summary['balance_due'] += (int) $booking['balance_due'];
        }

        $this->render('thanhtoan.php', [
            'bookings' => $bookings,
            'summary' => $summary,
        ]);
    }
}
