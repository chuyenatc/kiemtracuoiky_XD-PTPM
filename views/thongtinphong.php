<?php /** @var array $room */ ?>
<section class="room-detail">
    <nav class="room-breadcrumb" aria-label="Điều hướng">
        <a href="index.php?action=customer">Khách sạn</a>
        <span>/</span>
        <a href="index.php?action=customer">Danh sách phòng</a>
        <span>/</span>
        <span>Phòng <?= htmlspecialchars($room['room_name'], ENT_QUOTES, 'UTF-8') ?></span>
    </nav>

    <?php if (!empty($room['images'])): ?>
        <?php $visibleImages = array_slice($room['images'], 0, 7); ?>
        <div class="room-gallery-mosaic"
             aria-label="Ảnh phòng <?= htmlspecialchars($room['room_name'], ENT_QUOTES, 'UTF-8') ?>">
            <?php foreach ($visibleImages as $imageIndex => $image): ?>
                <div class="room-mosaic-image">
                    <img class="room-image"
                         src="<?= htmlspecialchars($image['image_path'], ENT_QUOTES, 'UTF-8') ?>"
                         alt="Ảnh <?= htmlspecialchars($room['room_name'], ENT_QUOTES, 'UTF-8') ?>"
                         loading="eager">
                    <?php if (count($room['images']) > 7 && $imageIndex === 6): ?>
                        <button type="button" class="room-mosaic-more"
                                data-room-photos-open="room-photos-<?= (int) $room['id'] ?>">
                            ▧ Xem tất cả <?= count($room['images']) ?> ảnh
                        </button>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
        <?php if (count($room['images']) > 7): ?>
            <dialog class="room-photo-dialog" id="room-photos-<?= (int) $room['id'] ?>" data-room-photos-dialog>
                <div class="room-photo-dialog-header">
                    <h2>Ảnh phòng <?= htmlspecialchars($room['room_name'], ENT_QUOTES, 'UTF-8') ?></h2>
                    <button type="button" class="room-photo-dialog-close" data-room-photos-close>Đóng</button>
                </div>
                <div class="room-photo-dialog-grid">
                    <?php foreach ($room['images'] as $image): ?>
                        <img src="<?= htmlspecialchars($image['image_path'], ENT_QUOTES, 'UTF-8') ?>"
                             alt="Ảnh phòng <?= htmlspecialchars($room['room_name'], ENT_QUOTES, 'UTF-8') ?>"
                             loading="lazy">
                    <?php endforeach; ?>
                </div>
            </dialog>
        <?php endif; ?>
    <?php else: ?>
        <div class="room-image-placeholder" role="img"
             aria-label="Phòng <?= htmlspecialchars($room['room_name'], ENT_QUOTES, 'UTF-8') ?> chưa có ảnh">
            Phòng này chưa có ảnh mô tả
        </div>
    <?php endif; ?>

    <div class="room-detail-content">
        <div class="room-summary">
            <div>
                <h1><?= htmlspecialchars($room['room_name'], ENT_QUOTES, 'UTF-8') ?></h1>
                <div class="room-summary-meta">
                    <span>Khách sạn</span>
                    <span class="status <?= $room['status'] === 'Trống' ? '' : 'rented' ?>">
                        <?= htmlspecialchars($room['status'], ENT_QUOTES, 'UTF-8') ?>
                    </span>
                </div>
            </div>
            <div class="room-summary-booking">
                <div class="room-summary-price">
                    <span>Giá/phòng/đêm từ</span>
                    <strong><?= number_format((int) $room['nightly_rate'], 0, ',', '.') ?> ₫</strong>
                </div>
                <?php if ($room['status'] === 'Trống'): ?>
                    <a href="index.php?action=book&amp;id=<?= (int) $room['id'] ?>" class="btn">Chọn phòng</a>
                <?php else: ?>
                    <span class="status rented">Hiện không thể đặt</span>
                <?php endif; ?>
            </div>
        </div>
        <div class="panel">
            <h2>Mô tả phòng</h2>
            <?php if (trim((string) ($room['description'] ?? '')) !== ''): ?>
                <p class="room-detail-description"><?= htmlspecialchars((string) $room['description'], ENT_QUOTES, 'UTF-8') ?></p>
            <?php else: ?>
                <p class="room-detail-description">Phòng chưa có thông tin mô tả. Vui lòng liên hệ khách sạn để biết thêm chi tiết.</p>
            <?php endif; ?>
        </div>
    </div>
</section>
