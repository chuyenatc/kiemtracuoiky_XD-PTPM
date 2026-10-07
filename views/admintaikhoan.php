<?php /** @var array $admins */ ?>
<h3>Quản lý tài khoản admin</h3>
<form method="POST" action="index.php?action=admin_add" style="max-width: 420px;">
    <div class="form-group">
        <label for="username">Tên tài khoản</label>
        <input type="text" id="username" name="username" maxlength="50" required>
    </div>
    <div class="form-group">
        <label for="password">Mật khẩu (ít nhất 6 ký tự)</label>
        <input type="password" id="password" name="password" minlength="6" required>
    </div>
    <div class="form-group">
        <label for="confirm_password">Xác nhận mật khẩu</label>
        <input type="password" id="confirm_password" name="confirm_password" minlength="6" required>
    </div>
    <button type="submit" class="btn">Thêm tài khoản admin</button>
</form>

<h4>Danh sách admin</h4>
<table>
    <thead>
        <tr><th>#</th><th>Tên tài khoản</th></tr>
    </thead>
    <tbody>
        <?php foreach ($admins as $index => $admin): ?>
            <tr>
                <td><?= $index + 1 ?></td>
                <td><?= htmlspecialchars($admin['username'], ENT_QUOTES, 'UTF-8') ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
