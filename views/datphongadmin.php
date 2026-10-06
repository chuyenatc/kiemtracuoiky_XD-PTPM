<?php /** @var array $room */ ?>
<h3>Đặt phòng <?= htmlspecialchars($room['room_name'], ENT_QUOTES, 'UTF-8') ?></h3>
<form method="POST" style="max-width: 400px;">
    <div class="form-group">
        <label>Tên khách hàng</label>
        <input type="text" name="customer_name" required>
    </div>
    <div class="form-group">
        <label>Ngày nhận phòng</label>
        <input type="date" name="check_in" required>
    </div>
    <div class="form-group">
        <label>Ngày trả phòng</label>
        <input type="date" name="check_out" required>
    </div>
    <button type="submit" class="btn">Xác nhận đặt phòng</button>
    <a href="index.php?action=dashboard" class="btn btn-danger">Hủy</a>
</form>