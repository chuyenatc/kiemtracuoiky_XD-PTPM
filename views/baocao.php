<?php /** @var string $month */ ?>
<?php /** @var array $bookings */ ?>
<?php /** @var array $summary */ ?>
<h3>Báo cáo lượt thuê và doanh thu tiền phòng</h3>
<form method="GET" action="index.php" class="panel" style="display: flex; align-items: end; gap: 12px; flex-wrap: wrap;">
    <input type="hidden" name="action" value="monthly_report">
    <div class="form-group" style="margin: 0; min-width: 190px;">
        <label for="month">Chọn tháng</label>
        <input type="month" id="month" name="month" value="<?= htmlspecialchars($month, ENT_QUOTES, 'UTF-8') ?>" required>
    </div>
    <button type="submit" class="btn">Xem báo cáo</button>
</form>

<div class="summary-grid">
    <section class="summary-card">
        <span>Lượt thuê có ngày ở trong tháng</span>
        <strong><?= number_format((int) $summary['booking_count'], 0, ',', '.') ?></strong>
    </section>
    <section class="summary-card">
        <span>Số đêm tính trong tháng</span>
        <strong><?= number_format((int) $summary['nights'], 0, ',', '.') ?></strong>
    </section>
    <section class="summary-card">
        <span>Doanh thu tiền phòng dự kiến</span>
        <strong><?= number_format((int) $summary['revenue'], 0, ',', '.') ?> ₫</strong>
    </section>
</div>

<h4>Trạng thái thanh toán trong tháng</h4>
<div class="summary-grid">
    <?php foreach ($summary['payment_statuses'] as $status => $count): ?>
        <section class="summary-card">
            <span><?= htmlspecialchars($status, ENT_QUOTES, 'UTF-8') ?></span>
            <strong><?= number_format((int) $count, 0, ',', '.') ?> lượt</strong>
        </section>
    <?php endforeach; ?>
</div>

<p class="notice">
    Báo cáo tính các lượt thuê có ngày lưu trú giao với tháng đã chọn; tiền phòng được phân bổ theo số đêm nằm trong tháng.
    Đây là doanh thu tiền phòng dự kiến, chưa trừ chi phí, thuế hoặc xác nhận thanh toán — không phải lợi nhuận ròng.
</p>

<h4>Chi tiết lượt thuê trong tháng <?= htmlspecialchars($month, ENT_QUOTES, 'UTF-8') ?></h4>
<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>Phòng</th>
                <th>Khách hàng</th>
                <th>Nhận phòng</th>
                <th>Trả phòng</th>
                <th>Trạng thái lượt thuê</th>
                <th>Trạng thái thanh toán</th>
                <th>Cọc yêu cầu</th>
                <th>Đã thu</th>
                <th>Còn phải thu</th>
                <th>Đêm trong tháng</th>
                <th>Đơn giá/đêm</th>
                <th>Doanh thu trong tháng</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($bookings as $booking): ?>
                <?php
                $paymentStatusClass = match ($booking['payment_status']) {
                    'Đã thanh toán' => 'paid',
                    'Đã đặt cọc', 'Đã thu một phần' => 'partial',
                    default => '',
                };
                ?>
                <tr>
                    <td><?= htmlspecialchars($booking['room_name'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars((string) $booking['customer_name'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($booking['check_in'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($booking['check_out'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= $booking['status'] === 'Active' ? 'Đang thuê' : 'Đã trả phòng' ?></td>
                    <td>
                        <span class="payment-status <?= htmlspecialchars($paymentStatusClass, ENT_QUOTES, 'UTF-8') ?>">
                            <?= htmlspecialchars((string) $booking['payment_status'], ENT_QUOTES, 'UTF-8') ?>
                        </span>
                    </td>
                    <td><?= number_format((int) $booking['deposit_required'], 0, ',', '.') ?> ₫</td>
                    <td><?= number_format((int) $booking['paid_amount'], 0, ',', '.') ?> ₫</td>
                    <td><?= $booking['balance_due'] === null
                        ? 'Chưa đối soát'
                        : number_format((int) $booking['balance_due'], 0, ',', '.') . ' ₫' ?></td>
                    <td><?= number_format((int) $booking['nights_in_month'], 0, ',', '.') ?></td>
                    <td><?= number_format((int) $booking['nightly_rate'], 0, ',', '.') ?> ₫</td>
                    <td><strong><?= number_format((int) $booking['revenue_in_month'], 0, ',', '.') ?> ₫</strong></td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$bookings): ?>
                <tr><td colspan="12">Không có lượt thuê nào trong tháng đã chọn.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
