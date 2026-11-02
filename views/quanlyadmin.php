<?php /** @var array $rooms */ ?>
<?php /** @var string $csrfToken */ ?>
<h3>Danh sách phòng</h3>
<div class="grid">
    <?php foreach ($rooms as $r): ?>
        <div class="card <?= $r['status'] === 'Trống' ? 'empty' : 'rented' ?>">
            <h2>Phòng <?= htmlspecialchars($r['room_name'], ENT_QUOTES, 'UTF-8') ?></h2>
            <p>Trạng thái: <strong><?= htmlspecialchars($r['status'], ENT_QUOTES, 'UTF-8') ?></strong></p>
            
            <?php if ($r['status'] === 'Trống'): ?>
                <a href="index.php?action=book&amp;id=<?= (int) $r['id'] ?>" class="btn">Đặt phòng</a>
            <?php else: ?>
                <p><small>Khách: <?= htmlspecialchars((string) $r['customer_name'], ENT_QUOTES, 'UTF-8') ?></small></p>
                <p><small>Trả phòng: <?= htmlspecialchars((string) $r['check_out'], ENT_QUOTES, 'UTF-8') ?></small></p>
                <p><small>Giá: <?= number_format((int) $r['booking_nightly_rate'], 0, ',', '.') ?> ₫/đêm</small></p>
                <p><strong>Tổng tiền dự kiến: <?= number_format((int) $r['booking_total'], 0, ',', '.') ?> ₫</strong></p>
                <p>Cọc yêu cầu: <strong><?= number_format((int) $r['deposit_required'], 0, ',', '.') ?> ₫</strong></p>
                <p>Đã thu: <strong><?= number_format((int) $r['paid_amount'], 0, ',', '.') ?> ₫</strong></p>
                <p>Còn phải thu: <strong><?= $r['balance_due'] === null ? 'Chưa đối soát' : number_format((int) $r['balance_due'], 0, ',', '.') . ' ₫' ?></strong></p>
                <p>Phương thức:
                    <strong><?= $r['payment_method'] === 'online' ? 'Chuyển khoản / QR' : ($r['payment_method'] === 'direct' ? 'Trực tiếp' : 'Chưa ghi nhận') ?></strong>
                </p>
                <p>Thanh toán:
                    <span class="payment-status <?= $r['payment_status'] === 'Đã thanh toán' ? 'paid' : ($r['paid_amount'] > 0 ? 'partial' : '') ?>">
                        <?= htmlspecialchars((string) $r['payment_status'], ENT_QUOTES, 'UTF-8') ?>
                    </span>
                </p>
                <a href="index.php?action=extend&amp;booking_id=<?= (int) $r['booking_id'] ?>" class="btn btn-warning">Gia hạn</a>
                <form method="POST" action="index.php?action=checkout" style="display: inline;">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="room_id" value="<?= (int) $r['id'] ?>">
                    <input type="hidden" name="booking_id" value="<?= (int) $r['booking_id'] ?>">
                    <button type="submit" class="btn btn-danger">Trả phòng</button>
                </form>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>