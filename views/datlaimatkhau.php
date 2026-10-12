<?php /** @var string $csrfToken */ ?>
<?php /** @var string $resetToken */ ?>
<h2 style="text-align: center;">Đặt lại mật khẩu</h2>
<form method="POST" action="index.php?action=reset_password" style="max-width: 400px; margin: 0 auto;">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
    <input type="hidden" name="token" value="<?= htmlspecialchars($resetToken, ENT_QUOTES, 'UTF-8') ?>">
    <div class="form-group">
        <label for="password">Mật khẩu mới (ít nhất 6 ký tự)</label>
        <input type="password" id="password" name="password" minlength="6" required>
    </div>
    <div class="form-group">
        <label for="confirm_password">Xác nhận mật khẩu mới</label>
        <input type="password" id="confirm_password" name="confirm_password" minlength="6" required>
    </div>
    <button type="submit" class="btn" style="width: 100%;">Cập nhật mật khẩu</button>
</form>
