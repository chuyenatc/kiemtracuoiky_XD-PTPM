<?php /** @var array $rooms */ ?>
<?php /** @var array $bookings */ ?>
<h3>Phòng dành cho khách hàng</h3>
<p>Xem hình ảnh, trạng thái và giá phòng trước khi gửi yêu cầu đặt phòng.</p>
<?php if (($_SESSION['role'] ?? '') === 'customer' && isset($_SESSION['user'])): ?>
    <section class="panel">
        <h3>Lượt thuê của tôi</h3>
        <?php if (!$bookings): ?>
            <p>Bạn hiện không có lượt thuê nào đang hoạt động.</p>
        <?php else: ?>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Phòng</th>
                            <th>Thời gian thuê</th>
                            <th>Tổng dự kiến</th>
                            <th>Thanh toán</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bookings as $booking): ?>
                            <tr>
                                <td><?= htmlspecialchars((string) $booking['room_name'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td>
                                    <?= htmlspecialchars((string) $booking['check_in'], ENT_QUOTES, 'UTF-8') ?>
                                    đến <?= htmlspecialchars((string) $booking['check_out'], ENT_QUOTES, 'UTF-8') ?>
                                </td>
                                <td><?= number_format((int) $booking['booking_total'], 0, ',', '.') ?> ₫</td>
                                <td>
                                    <?= htmlspecialchars((string) $booking['payment_status'], ENT_QUOTES, 'UTF-8') ?><br>
                                    Đã thu: <?= number_format((int) $booking['paid_amount'], 0, ',', '.') ?> ₫<br>
                                    Còn lại:
                                    <?= $booking['balance_due'] === null
                                        ? 'Chưa đối soát'
                                        : number_format((int) $booking['balance_due'], 0, ',', '.') . ' ₫' ?>
                                </td>
                                <td>
                                    <a class="btn btn-warning"
                                       href="index.php?action=extend&amp;booking_id=<?= (int) $booking['id'] ?>">
                                        Gia hạn
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
<?php endif; ?>
<div class="grid">
    <?php foreach ($rooms as $r): ?>
        <article class="card room-card <?= $r['status'] === 'Trống' ? 'empty' : 'rented' ?>">
            <?php if (!empty($r['images'])): ?>
                <div class="room-gallery" data-room-gallery aria-label="Ảnh phòng <?= htmlspecialchars($r['room_name'], ENT_QUOTES, 'UTF-8') ?>">
                    <?php foreach ($r['images'] as $imageIndex => $image): ?>
                        <div class="room-photo-item <?= $imageIndex === 0 ? 'active' : '' ?>" data-gallery-slide aria-hidden="<?= $imageIndex === 0 ? 'false' : 'true' ?>">
                            <a href="index.php?action=room_detail&amp;id=<?= (int) $r['id'] ?>"
                               aria-label="Xem chi tiết phòng <?= htmlspecialchars($r['room_name'], ENT_QUOTES, 'UTF-8') ?>">
                                <img class="room-image"
                                     src="<?= htmlspecialchars($image['image_path'], ENT_QUOTES, 'UTF-8') ?>"
                                     alt="Ảnh <?= htmlspecialchars($r['room_name'], ENT_QUOTES, 'UTF-8') ?>"
                                     loading="lazy">
                            </a>
                        </div>
                    <?php endforeach; ?>
                    <?php if (count($r['images']) > 1): ?>
                        <div class="gallery-controls">
                            <button type="button" data-gallery-previous aria-label="Ảnh trước">‹</button>
                            <span class="gallery-counter" data-gallery-counter aria-live="polite">1 / <?= count($r['images']) ?></span>
                            <button type="button" data-gallery-next aria-label="Ảnh tiếp theo">›</button>
                        </div>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="room-image-placeholder" role="img"
                     aria-label="Phòng <?= htmlspecialchars($r['room_name'], ENT_QUOTES, 'UTF-8') ?> chưa có ảnh">
                    Chưa có ảnh phòng
                </div>
            <?php endif; ?>
            <h2><a href="index.php?action=room_detail&amp;id=<?= (int) $r['id'] ?>">
                Phòng <?= htmlspecialchars($r['room_name'], ENT_QUOTES, 'UTF-8') ?>
            </a></h2>
            <p><span class="status <?= $r['status'] === 'Trống' ? '' : 'rented' ?>">
                <?= htmlspecialchars($r['status'], ENT_QUOTES, 'UTF-8') ?>
            </span></p>
            <p>Giá thuê: <strong><?= number_format((int) $r['nightly_rate'], 0, ',', '.') ?> ₫/đêm</strong></p>
            <a href="index.php?action=room_detail&amp;id=<?= (int) $r['id'] ?>" class="btn btn-warning">Xem thông tin phòng</a>
            <?php if ($r['status'] === 'Trống'): ?>
                <a href="index.php?action=book&amp;id=<?= (int) $r['id'] ?>" class="btn">Đặt phòng</a>
            <?php else: ?>
                <p>Phòng hiện không còn trống.</p>
            <?php endif; ?>
        </article>
    <?php endforeach; ?>
    <?php if (!$rooms): ?>
        <p>Hiện chưa có phòng nào được mở bán.</p>
    <?php endif; ?>
</div>
