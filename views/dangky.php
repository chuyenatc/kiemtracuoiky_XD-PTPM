<h2 style="text-align: center;">Đăng ký tài khoản</h2>
<form method="POST" action="index.php?action=register" style="max-width: 400px; margin: 0 auto;">
    <input type="hidden" name="return_to" value="<?= htmlspecialchars($returnTo ?? '', ENT_QUOTES, 'UTF-8') ?>">
    <div class="form-group">
        <label>Tài khoản</label>
        <input type="text" name="username" maxlength="50" required>
    </div>
    <div class="form-group">
        <label>Mật khẩu (ít nhất 6 ký tự)</label>
        <input type="password" name="password" minlength="6" required>
    </div>
    <div class="form-group">
        <label>Xác nhận mật khẩu</label>
        <input type="password" name="confirm_password" minlength="6" required>
    </div>
    <button type="submit" class="btn" style="width: 100%;">Đăng ký</button>
    <a href="index.php?action=login<?= isset($returnTo) ? '&amp;return_to=' . rawurlencode($returnTo) : '' ?>"
       class="btn btn-warning" style="display: block; text-align: center;">Đã có tài khoản? Đăng nhập</a>
</form>
