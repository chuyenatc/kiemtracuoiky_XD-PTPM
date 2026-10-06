<?php /** @var array $rooms */ ?>
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
                <a href="index.php?action=extend&amp;booking_id=<?= (int) $r['booking_id'] ?>" class="btn btn-warning">Gia hạn</a>
                <form method="POST" action="index.php?action=checkout" style="display: inline;">
                    <input type="hidden" name="room_id" value="<?= (int) $r['id'] ?>">
                    <input type="hidden" name="booking_id" value="<?= (int) $r['booking_id'] ?>">
                    <button type="submit" class="btn btn-danger">Trả phòng</button>
                </form>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>