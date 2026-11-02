<?php /** @var string $csrfToken */ ?>
<?php /** @var array $room */ ?>
<h3>Đặt phòng <?= htmlspecialchars($room['room_name'], ENT_QUOTES, 'UTF-8') ?></h3>
<?php if (!empty($room['images'])): ?>
    <div class="room-gallery" data-room-gallery aria-label="Ảnh phòng <?= htmlspecialchars($room['room_name'], ENT_QUOTES, 'UTF-8') ?>" style="max-width: 760px;">
        <?php foreach ($room['images'] as $imageIndex => $image): ?>
            <div class="room-photo-item <?= $imageIndex === 0 ? 'active' : '' ?>" data-gallery-slide aria-hidden="<?= $imageIndex === 0 ? 'false' : 'true' ?>">
                <img class="room-image"
                     src="<?= htmlspecialchars($image['image_path'], ENT_QUOTES, 'UTF-8') ?>"
                     alt="Ảnh mô tả phòng <?= htmlspecialchars($room['room_name'], ENT_QUOTES, 'UTF-8') ?>"
                     loading="lazy">
            </div>
        <?php endforeach; ?>
        <?php if (count($room['images']) > 1): ?>
            <div class="gallery-controls">
                <button type="button" data-gallery-previous aria-label="Ảnh trước">‹</button>
                <span class="gallery-counter" data-gallery-counter aria-live="polite">1 / <?= count($room['images']) ?></span>
                <button type="button" data-gallery-next aria-label="Ảnh tiếp theo">›</button>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>
<p>Giá thuê: <strong><?= number_format((int) $room['nightly_rate'], 0, ',', '.') ?> ₫/đêm</strong></p>
<p id="booking-estimate" role="status">Chọn ngày để xem tổng tiền dự kiến.</p>
<p id="deposit-estimate" class="notice" role="status">
    Lượt nhận phòng từ ngày mai trở đi cần đặt cọc 20% tổng tiền dự kiến.
</p>
<form method="POST" style="max-width: 400px;">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
    <div class="form-group">
        <label>Tên khách hàng</label>
        <input type="text" name="customer_name" required>
    </div>
    <div class="form-group">
        <label>Ngày nhận phòng</label>
        <input type="date" id="check_in" name="check_in" required>
    </div>
    <div class="form-group">
        <label>Ngày trả phòng</label>
        <input type="date" id="check_out" name="check_out" required>
    </div>
    <fieldset class="form-group payment-options">
        <legend>Phương thức thanh toán</legend>
        <label class="payment-option">
            <input type="radio" name="payment_method" value="direct" required>
            Thanh toán trực tiếp tại khách sạn
        </label>
        <label class="payment-option">
            <input type="radio" name="payment_method" value="online" required>
            Chuyển khoản / QR online
        </label>
        <p class="form-hint">
            Thanh toán online hiện được ghi nhận là phương thức đã chọn; admin xác nhận tiền sau khi đối soát giao dịch.
        </p>
    </fieldset>
    <button type="submit" class="btn">Xác nhận đặt phòng</button>
    <a href="index.php?action=dashboard" class="btn btn-danger">Hủy</a>
</form>
<script>
    (() => {
        const checkIn = document.getElementById('check_in');
        const checkOut = document.getElementById('check_out');
        const estimate = document.getElementById('booking-estimate');
        const depositEstimate = document.getElementById('deposit-estimate');
        const nightlyRate = <?= (int) $room['nightly_rate'] ?>;
        const formatter = new Intl.NumberFormat('vi-VN');
        const today = new Date();
        const todayValue = `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, '0')}-${String(today.getDate()).padStart(2, '0')}`;

        const updateEstimate = () => {
            if (!checkIn.value || !checkOut.value) {
                estimate.textContent = 'Chọn ngày để xem tổng tiền dự kiến.';
                depositEstimate.textContent = 'Lượt nhận phòng từ ngày mai trở đi cần đặt cọc 20% tổng tiền dự kiến.';
                return;
            }

            const start = Date.parse(`${checkIn.value}T00:00:00Z`);
            const end = Date.parse(`${checkOut.value}T00:00:00Z`);
            const nights = (end - start) / 86400000;
            if (nights <= 0) {
                estimate.textContent = 'Ngày trả phòng phải sau ngày nhận phòng.';
                depositEstimate.textContent = 'Vui lòng chọn ngày hợp lệ để tính tiền cọc.';
                return;
            }

            const total = nightlyRate * nights;
            estimate.textContent = `Tổng dự kiến: ${formatter.format(total)} ₫ (${nights} đêm)`;
            const deposit = checkIn.value > todayValue ? Math.ceil(total * 0.2) : 0;
            depositEstimate.textContent = deposit > 0
                ? `Cọc bắt buộc 20%: ${formatter.format(deposit)} ₫. Còn lại: ${formatter.format(total - deposit)} ₫.`
                : 'Nhận phòng hôm nay: không yêu cầu đặt cọc trước.';
        };

        checkIn.addEventListener('change', updateEstimate);
        checkOut.addEventListener('change', updateEstimate);
    })();
</script>