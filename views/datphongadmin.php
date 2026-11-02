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
            QR bên dưới chỉ dùng minh họa giao diện, không tạo giao dịch hoặc nhận tiền thật.
        </p>
    </fieldset>
    <section id="qr-payment-preview" class="qr-preview" aria-live="polite" hidden>
        <h4>Quét mã QR thanh toán</h4>
        <p id="qr-payment-amount">Chọn ngày để xem số tiền cần thanh toán.</p>
        <div id="qr-mock" class="qr-mock" role="img" aria-label="Mã QR minh họa, không thể dùng để thanh toán"></div>
        <p class="qr-preview-warning">QR minh họa — không dùng để chuyển tiền. Khoản thanh toán chỉ được xác nhận sau khi admin đối soát.</p>
    </section>
    <button type="submit" class="btn">Xác nhận đặt phòng</button>
    <a href="index.php?action=dashboard" class="btn btn-danger">Hủy</a>
</form>
<script>
    (() => {
        const checkIn = document.getElementById('check_in');
        const checkOut = document.getElementById('check_out');
        const estimate = document.getElementById('booking-estimate');
        const depositEstimate = document.getElementById('deposit-estimate');
        const qrPreview = document.getElementById('qr-payment-preview');
        const qrAmount = document.getElementById('qr-payment-amount');
        const qrMock = document.getElementById('qr-mock');
        const paymentMethods = document.querySelectorAll('input[name="payment_method"]');
        const nightlyRate = <?= (int) $room['nightly_rate'] ?>;
        const formatter = new Intl.NumberFormat('vi-VN');
        const today = new Date();
        const todayValue = `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, '0')}-${String(today.getDate()).padStart(2, '0')}`;

        const drawMockQr = () => {
            let seed = 20261103;
            const nextBit = () => {
                seed = (seed * 1664525 + 1013904223) >>> 0;
                return (seed >>> 31) === 1;
            };
            const inFinder = (row, column, top, left) =>
                row >= top && row < top + 7 && column >= left && column < left + 7;
            const finderBit = (row, column, top, left) => {
                const y = row - top;
                const x = column - left;
                return y === 0 || y === 6 || x === 0 || x === 6
                    || (y >= 2 && y <= 4 && x >= 2 && x <= 4);
            };

            for (let row = 0; row < 21; row++) {
                for (let column = 0; column < 21; column++) {
                    let dark = nextBit();
                    for (const [top, left] of [[0, 0], [0, 14], [14, 0]]) {
                        if (inFinder(row, column, top, left)) {
                            dark = finderBit(row, column, top, left);
                        }
                    }
                    const module = document.createElement('span');
                    if (dark) module.classList.add('is-dark');
                    qrMock.append(module);
                }
            }
        };
        drawMockQr();

        const updateEstimate = () => {
            const isOnline = document.querySelector('input[name="payment_method"]:checked')?.value === 'online';
            qrPreview.hidden = !isOnline;

            if (!checkIn.value || !checkOut.value) {
                estimate.textContent = 'Chọn ngày để xem tổng tiền dự kiến.';
                depositEstimate.textContent = 'Lượt nhận phòng từ ngày mai trở đi cần đặt cọc 20% tổng tiền dự kiến.';
                qrAmount.textContent = 'Chọn ngày hợp lệ để xem số tiền thanh toán.';
                return;
            }

            const start = Date.parse(`${checkIn.value}T00:00:00Z`);
            const end = Date.parse(`${checkOut.value}T00:00:00Z`);
            const nights = (end - start) / 86400000;
            if (nights <= 0) {
                estimate.textContent = 'Ngày trả phòng phải sau ngày nhận phòng.';
                depositEstimate.textContent = 'Vui lòng chọn ngày hợp lệ để tính tiền cọc.';
                qrAmount.textContent = 'Ngày trả phòng phải sau ngày nhận phòng.';
                return;
            }

            const total = nightlyRate * nights;
            estimate.textContent = `Tổng dự kiến: ${formatter.format(total)} ₫ (${nights} đêm)`;
            const deposit = checkIn.value > todayValue ? Math.ceil(total * 0.2) : 0;
            depositEstimate.textContent = deposit > 0
                ? `Cọc bắt buộc 20%: ${formatter.format(deposit)} ₫. Còn lại: ${formatter.format(total - deposit)} ₫.`
                : 'Nhận phòng hôm nay: không yêu cầu đặt cọc trước.';
            qrAmount.textContent = deposit > 0
                ? `Số tiền cọc minh họa: ${formatter.format(deposit)} ₫`
                : `Tổng tiền minh họa: ${formatter.format(total)} ₫`;
        };

        checkIn.addEventListener('change', updateEstimate);
        checkOut.addEventListener('change', updateEstimate);
        paymentMethods.forEach((method) => method.addEventListener('change', updateEstimate));
    })();
</script>