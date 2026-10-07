<?php /** @var array $rooms */ ?>
<h3>Phòng dành cho khách hàng</h3>
<p>Chọn phòng còn trống để xem thông tin và gửi yêu cầu đặt phòng.</p>
<div class="grid">
    <?php foreach ($rooms as $r): ?>
        <div class="card <?= $r['status'] === 'Trống' ? 'empty' : 'rented' ?>">
            <h2>Phòng <?= htmlspecialchars($r['room_name'], ENT_QUOTES, 'UTF-8') ?></h2>
            <p>Trạng thái: <strong><?= htmlspecialchars($r['status'], ENT_QUOTES, 'UTF-8') ?></strong></p>
            <?php if ($r['status'] === 'Trống'): ?>
                <a href="index.php?action=book&amp;id=<?= (int) $r['id'] ?>" class="btn">Đặt phòng</a>
            <?php else: ?>
                <p>Phòng hiện không còn trống.</p>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
    <?php if (!$rooms): ?>
        <p>Hiện chưa có phòng nào được mở bán.</p>
    <?php endif; ?>
</div>
