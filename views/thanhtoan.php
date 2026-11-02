<?php /** @var array $bookings */ ?>
<?php /** @var array $summary */ ?>
<?php /** @var string $csrfToken */ ?>
<h3>Quản lý thanh toán</h3>
<p class="notice">
    Lựa chọn thanh toán online hiện chỉ lưu phương thức dự kiến, chưa tích hợp cổng thanh toán.
    Admin cần đối soát giao dịch thực tế rồi mới ghi nhận tiền đã thu. Các lượt thuê có từ trước
    khi bật chức năng này được đánh dấu “Chưa đối soát”.
</p>

<div class="summary-grid">
    <div class="summary-card">
        <span>Tổng lượt thuê</span>
        <strong><?= number_format((int) $summary['booking_count'], 0, ',', '.') ?></strong>
    </div>
    <div class="summary-card">
        <span>Tiền đã ghi nhận</span>
        <strong><?= number_format((int) $summary['paid_amount'], 0, ',', '.') ?> ₫</strong>
    </div>
    <div class="summary-card">
        <span>Cọc còn thiếu (đã đối soát)</span>
        <strong><?= number_format((int) $summary['deposit_pending'], 0, ',', '.') ?> ₫</strong>
    </div>
    <div class="summary-card">
        <span>Số dư còn phải thu (đã đối soát)</span>
        <strong><?= number_format((int) $summary['balance_due'], 0, ',', '.') ?> ₫</strong>
    </div>
    <div class="summary-card">
        <span>Lượt thuê cần đối soát</span>
        <strong><?= number_format((int) $summary['unreconciled_count'], 0, ',', '.') ?></strong>
    </div>
</div>

<?php if (!$bookings): ?>
    <p>Chưa có lượt thuê nào.</p>
<?php else: ?>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Phòng / khách</th>
                    <th>Thời gian</th>
                    <th>Phương thức</th>
                    <th>Tổng dự kiến</th>
                    <th>Cọc yêu cầu</th>
                    <th>Đã thu</th>
                    <th>Còn phải thu</th>
                    <th>Trạng thái</th>
                    <th>Ghi nhận / đối soát</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($bookings as $booking): ?>
                    <?php
                    $unreconciled = $booking['payment_method'] === 'unknown';
                    $balanceDue = $booking['balance_due'];
                    $canRecord = $unreconciled || (int) $balanceDue > 0;
                    $methodLabel = match ($booking['payment_method']) {
                        'direct' => 'Trực tiếp',
                        'online' => 'Chuyển khoản / QR',
                        default => 'Chưa ghi nhận',
                    };
                    $statusClass = match ($booking['payment_status']) {
                        'Đã thanh toán' => 'paid',
                        'Đã đặt cọc', 'Đã thu một phần' => 'partial',
                        default => '',
                    };
                    ?>
                    <tr>
                        <td>
                            <strong>Phòng <?= htmlspecialchars((string) $booking['room_name'], ENT_QUOTES, 'UTF-8') ?></strong><br>
                            <?= htmlspecialchars((string) $booking['customer_name'], ENT_QUOTES, 'UTF-8') ?>
                            <br><small><?= $booking['status'] === 'Active' ? 'Đang thuê' : 'Đã trả phòng' ?></small>
                        </td>
                        <td>
                            <?= htmlspecialchars((string) $booking['check_in'], ENT_QUOTES, 'UTF-8') ?><br>
                            đến <?= htmlspecialchars((string) $booking['check_out'], ENT_QUOTES, 'UTF-8') ?>
                        </td>
                        <td><?= htmlspecialchars($methodLabel, ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= number_format((int) $booking['total_amount'], 0, ',', '.') ?> ₫</td>
                        <td><?= number_format((int) $booking['deposit_required'], 0, ',', '.') ?> ₫</td>
                        <td><?= number_format((int) $booking['paid_amount'], 0, ',', '.') ?> ₫</td>
                        <td>
                            <?= $balanceDue === null
                                ? 'Chưa đối soát'
                                : number_format((int) $balanceDue, 0, ',', '.') . ' ₫' ?>
                        </td>
                        <td>
                            <span class="payment-status <?= htmlspecialchars($statusClass, ENT_QUOTES, 'UTF-8') ?>">
                                <?= htmlspecialchars((string) $booking['payment_status'], ENT_QUOTES, 'UTF-8') ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($canRecord): ?>
                                <form class="inline-payment-form" method="POST" action="index.php?action=payment_record">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                                    <input type="hidden" name="booking_id" value="<?= (int) $booking['id'] ?>">
                                    <?php if ($unreconciled): ?>
                                        <label>
                                            Phương thức thực tế
                                            <select name="payment_method" required>
                                                <option value="">Chọn phương thức</option>
                                                <option value="direct">Thanh toán trực tiếp</option>
                                                <option value="online">Chuyển khoản / QR</option>
                                            </select>
                                        </label>
                                    <?php endif; ?>
                                    <label>
                                        <?= $unreconciled ? 'Số đã thu để đối soát' : 'Số tiền thu thêm' ?>
                                        <input type="number" name="amount"
                                               min="<?= $unreconciled ? 0 : 1 ?>"
                                               max="999999999" step="1" required
                                               placeholder="Nhập số tiền (₫)">
                                    </label>
                                    <?php if ($unreconciled): ?>
                                        <small class="form-hint">Nhập 0 nếu chỉ cập nhật phương thức và chưa xác nhận khoản thu.</small>
                                    <?php endif; ?>
                                    <button type="submit" class="btn">
                                        <?= $unreconciled ? 'Đối soát' : 'Ghi nhận tiền' ?>
                                    </button>
                                </form>
                            <?php else: ?>
                                <span>Đã thu đủ</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
