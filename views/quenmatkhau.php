<?php /** @var string $csrfToken */ ?>
<h2 style="text-align: center;">Quên mật khẩu</h2>
<p style="max-width: 420px; margin: 0 auto 15px;">
    Nhập email đã đăng ký. Nếu email khớp với tài khoản, chúng tôi sẽ gửi liên kết đặt lại mật khẩu.
</p>
<form method="POST" action="index.php?action=forgot_password" style="max-width: 420px; margin: 0 auto;">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
    <div class="form-group">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" maxlength="254" required>
    </div>
    <button type="submit" class="btn" style="width: 100%;">Gửi liên kết đặt lại</button>
    <a href="index.php?action=login" class="btn btn-warning" style="display: block; text-align: center;">
        Quay lại đăng nhập
    </a>
</form>
