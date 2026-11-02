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
    <button type="submit" class="btn">Xác nhận đặt phòng</button>
    <a href="index.php?action=dashboard" class="btn btn-danger">Hủy</a>
</form>
<script>
    (() => {
        const checkIn = document.getElementById('check_in');
        const checkOut = document.getElementById('check_out');
        const estimate = document.getElementById('booking-estimate');
        const nightlyRate = <?= (int) $room['nightly_rate'] ?>;
        const formatter = new Intl.NumberFormat('vi-VN');

        const updateEstimate = () => {
            if (!checkIn.value || !checkOut.value) {
                estimate.textContent = 'Chọn ngày để xem tổng tiền dự kiến.';
                return;
            }

            const start = Date.parse(`${checkIn.value}T00:00:00Z`);
            const end = Date.parse(`${checkOut.value}T00:00:00Z`);
            const nights = (end - start) / 86400000;
            estimate.textContent = nights > 0
                ? `Tổng dự kiến: ${formatter.format(nightlyRate * nights)} ₫ (${nights} đêm)`
                : 'Ngày trả phòng phải sau ngày nhận phòng.';
        };

        checkIn.addEventListener('change', updateEstimate);
        checkOut.addEventListener('change', updateEstimate);
    })();
</script>