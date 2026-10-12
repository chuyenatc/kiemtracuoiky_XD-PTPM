<?php /** @var string $csrfToken */ ?>
<h2 style="text-align: center;">Đăng nhập hệ thống</h2>
<form method="POST" style="max-width: 400px; margin: 0 auto;">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
    <input type="hidden" name="return_to" value="<?= htmlspecialchars($returnTo ?? '', ENT_QUOTES, 'UTF-8') ?>">
    <div class="form-group">
        <label>Tài khoản</label>
        <input type="text" name="username" required>
    </div>
    <div class="form-group">
        <label>Mật khẩu</label>
        <input type="password" name="password" required>
    </div>
    <button type="submit" class="btn" style="width: 100%;">Đăng nhập</button>
    <a href="index.php?action=forgot_password" class="btn btn-warning" style="display: block; text-align: center;">
        Quên mật khẩu?
    </a>
    <a href="index.php?action=register" class="btn btn-warning" style="display: block; text-align: center;">
        Chưa có tài khoản? Đăng ký
    </a>
</form>