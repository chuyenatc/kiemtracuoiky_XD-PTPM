<?php /** @var array $booking */ ?>
<h3>Gia hạn Phòng <?= htmlspecialchars($booking['room_name'], ENT_QUOTES, 'UTF-8') ?></h3>
<form method="POST" style="max-width: 400px;">
    <input type="hidden" name="booking_id" value="<?= (int) $booking['id'] ?>">
    <div class="form-group">
        <label>Chọn ngày trả phòng mới</label>
        <input type="date" name="new_checkout" min="<?= htmlspecialchars($booking['check_out'], ENT_QUOTES, 'UTF-8') ?>" required>
    </div>
    <button type="submit" class="btn btn-warning">Xác nhận gia hạn</button>
    <a href="index.php?action=dashboard" class="btn btn-danger">Hủy</a>
</form>