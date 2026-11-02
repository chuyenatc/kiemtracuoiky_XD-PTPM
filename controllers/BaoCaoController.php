<?php
declare(strict_types=1);

require_once __DIR__ . '/ControllerChung.php';

class BaoCaoController extends ControllerChung
{
    public function handle(string $action = 'monthly_report'): void
    {
        $requestedMonth = $_GET['month'] ?? date('Y-m');
        if (!is_string($requestedMonth)
            || !preg_match('/\A\d{4}-(0[1-9]|1[0-2])\z/', $requestedMonth)) {
            throw new InvalidArgumentException('Tháng báo cáo không hợp lệ.');
        }

        $month = DateTimeImmutable::createFromFormat('!Y-m', $requestedMonth);
        if (!$month || $month->format('Y-m') !== $requestedMonth) {
            throw new InvalidArgumentException('Tháng báo cáo không hợp lệ.');
        }

        $monthStart = $month->format('Y-m-01');
        $nextMonthStart = $month->modify('+1 month')->format('Y-m-01');
        $bookings = $this->model->baoCaoLuotThueTheoThang($monthStart, $nextMonthStart);
        $summary = [
            'booking_count' => count($bookings),
            'nights' => 0,
            'revenue' => 0,
            'payment_statuses' => [
                'Chưa đối soát' => 0,
                'Chưa thanh toán' => 0,
                'Đã đặt cọc' => 0,
                'Đã thu một phần' => 0,
                'Đã thanh toán' => 0,
            ],
        ];

        foreach ($bookings as &$booking) {
            $booking['nights_in_month'] = (int) $booking['nights_in_month'];
            $booking['revenue_in_month'] = $booking['nights_in_month'] * (int) $booking['nightly_rate'];
            $summary['nights'] += $booking['nights_in_month'];
            $summary['revenue'] += $booking['revenue_in_month'];
            $summary['payment_statuses'][$booking['payment_status']]++;
        }
        unset($booking);

        $this->render('baocao.php', [
            'month' => $requestedMonth,
            'bookings' => $bookings,
            'summary' => $summary,
        ]);
    }
}
