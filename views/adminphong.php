<?php /** @var array $rooms */ ?>
<?php /** @var string $roomName */ ?>
<h3>Quản lý phòng</h3>
<form method="POST" action="index.php?action=room_add" style="max-width: 420px;">
    <div class="form-group">
        <label for="room_name">Tên phòng</label>
        <input type="text" id="room_name" name="room_name" maxlength="20"
               value="<?= htmlspecialchars($roomName, ENT_QUOTES, 'UTF-8') ?>" required>
    </div>
    <button type="submit" class="btn">Thêm phòng</button>
</form>

<h4>Danh sách phòng</h4>
<div class="grid">
    <?php foreach ($rooms as $r): ?>
        <div class="card <?= $r['status'] === 'Trống' ? 'empty' : 'rented' ?>">
            <h3>Phòng <?= htmlspecialchars($r['room_name'], ENT_QUOTES, 'UTF-8') ?></h3>
            <p>Trạng thái: <strong><?= htmlspecialchars($r['status'], ENT_QUOTES, 'UTF-8') ?></strong></p>
            <?php if ($r['can_delete'] && $r['status'] === 'Trống'): ?>
                <form method="POST" action="index.php?action=room_delete"
                      onsubmit="return confirm('Bạn có chắc muốn xóa phòng này?');">
                    <input type="hidden" name="room_id" value="<?= (int) $r['id'] ?>">
                    <button type="submit" class="btn btn-danger">Xóa phòng</button>
                </form>
            <?php else: ?>
                <p><small>Không thể xóa phòng đang thuê hoặc đã có lịch sử đặt.</small></p>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
    <?php if (!$rooms): ?>
        <p>Chưa có phòng. Hãy thêm phòng mới ở biểu mẫu phía trên.</p>
    <?php endif; ?>
</div>
