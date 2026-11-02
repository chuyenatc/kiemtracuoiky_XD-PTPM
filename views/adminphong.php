<?php /** @var array $rooms */ ?>
<?php /** @var string $roomName */ ?>
<?php /** @var string $csrfToken */ ?>
<h3>Quản lý phòng</h3>
<form method="POST" action="index.php?action=room_add" enctype="multipart/form-data" class="panel" style="max-width: 560px;">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
    <div class="form-group">
        <label for="room_name">Tên phòng</label>
        <input type="text" id="room_name" name="room_name" maxlength="20"
               value="<?= htmlspecialchars($roomName, ENT_QUOTES, 'UTF-8') ?>" required>
    </div>
    <div class="form-group">
        <label for="nightly_rate">Giá thuê mỗi đêm (VND)</label>
        <input type="number" id="nightly_rate" name="nightly_rate" min="1" max="999999999" step="1" required>
    </div>
    <div class="form-group">
        <label for="description">Mô tả phòng (tối đa 2.000 ký tự)</label>
        <textarea id="description" name="description" rows="4" maxlength="2000"
                  placeholder="Mô tả tiện nghi, diện tích, loại giường và các thông tin nổi bật..."></textarea>
    </div>
    <div class="form-group">
        <label for="room_images">Ảnh mô tả phòng (JPEG, PNG hoặc WebP; tối đa 5 MB/ảnh, 10 ảnh/lần)</label>
        <input type="file" id="room_images" name="room_images[]" accept="image/jpeg,image/png,image/webp" multiple>
    </div>
    <button type="submit" class="btn">Thêm phòng</button>
</form>

<h4>Danh sách phòng</h4>
<div class="grid">
    <?php foreach ($rooms as $r): ?>
        <article class="card room-card <?= $r['status'] === 'Trống' ? 'empty' : 'rented' ?>">
            <?php if (!empty($r['images'])): ?>
                <div class="room-gallery" data-room-gallery aria-label="Ảnh phòng <?= htmlspecialchars($r['room_name'], ENT_QUOTES, 'UTF-8') ?>">
                    <?php foreach ($r['images'] as $imageIndex => $image): ?>
                        <div class="room-photo-item <?= $imageIndex === 0 ? 'active' : '' ?>" data-gallery-slide aria-hidden="<?= $imageIndex === 0 ? 'false' : 'true' ?>">
                            <img class="room-image"
                                 src="<?= htmlspecialchars($image['image_path'], ENT_QUOTES, 'UTF-8') ?>"
                                 alt="Ảnh phòng <?= htmlspecialchars($r['room_name'], ENT_QUOTES, 'UTF-8') ?>"
                                 loading="lazy">
                            <?php if (!empty($image['id'])): ?>
                                <form method="POST" action="index.php?action=room_image_delete"
                                      onsubmit="return confirm('Bạn có chắc muốn xóa ảnh này?');">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                                    <input type="hidden" name="image_id" value="<?= (int) $image['id'] ?>">
                                    <button type="submit" class="btn btn-danger">Xóa ảnh</button>
                                </form>
                            <?php endif; ?>
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
                <div class="room-image-placeholder">Chưa có ảnh phòng</div>
            <?php endif; ?>
            <h3>Phòng <?= htmlspecialchars($r['room_name'], ENT_QUOTES, 'UTF-8') ?></h3>
            <p>Trạng thái: <strong><?= htmlspecialchars($r['status'], ENT_QUOTES, 'UTF-8') ?></strong></p>
            <p>Giá hiện tại: <strong><?= number_format((int) $r['nightly_rate'], 0, ',', '.') ?> ₫/đêm</strong></p>
            <form method="POST" action="index.php?action=room_description" class="panel">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="room_id" value="<?= (int) $r['id'] ?>">
                <div class="form-group">
                    <label for="description_<?= (int) $r['id'] ?>">Mô tả phòng</label>
                    <textarea id="description_<?= (int) $r['id'] ?>" name="description" rows="4"
                              maxlength="2000"><?= htmlspecialchars((string) ($r['description'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                </div>
                <button type="submit" class="btn btn-warning">Lưu mô tả</button>
            </form>
            <form method="POST" action="index.php?action=room_photo" enctype="multipart/form-data"
                  style="margin: 14px 0;">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="room_id" value="<?= (int) $r['id'] ?>">
                <div class="form-group">
                    <label for="room_image_<?= (int) $r['id'] ?>">
                        Thêm ảnh phòng (JPEG, PNG hoặc WebP; tối đa 5 MB/ảnh, 10 ảnh/lần)
                    </label>
                    <input type="file" id="room_image_<?= (int) $r['id'] ?>" name="room_images[]"
                           accept="image/jpeg,image/png,image/webp" multiple required>
                </div>
                <button type="submit" class="btn btn-warning">Thêm ảnh</button>
            </form>
            <form method="POST" action="index.php?action=room_price" style="max-width: 320px; margin: 0 auto;">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="room_id" value="<?= (int) $r['id'] ?>">
                <div class="form-group">
                    <label for="nightly_rate_<?= (int) $r['id'] ?>">Cập nhật giá (VND/đêm)</label>
                    <input type="number" id="nightly_rate_<?= (int) $r['id'] ?>" name="nightly_rate"
                           min="1" max="999999999" step="1" value="<?= (int) $r['nightly_rate'] ?>" required>
                </div>
                <button type="submit" class="btn btn-warning">Lưu giá</button>
            </form>
            <?php if ($r['can_delete'] && $r['status'] === 'Trống'): ?>
                <form method="POST" action="index.php?action=room_delete"
                      onsubmit="return confirm('Bạn có chắc muốn xóa phòng này?');">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="room_id" value="<?= (int) $r['id'] ?>">
                    <button type="submit" class="btn btn-danger">Xóa phòng</button>
                </form>
            <?php else: ?>
                <p><small>Không thể xóa phòng đang thuê hoặc đã có lịch sử đặt.</small></p>
            <?php endif; ?>
        </article>
    <?php endforeach; ?>
    <?php if (!$rooms): ?>
        <p>Chưa có phòng. Hãy thêm phòng mới ở biểu mẫu phía trên.</p>
    <?php endif; ?>
</div>
