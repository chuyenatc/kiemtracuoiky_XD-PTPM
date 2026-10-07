<?php /** @var string $action */ ?>
<?php /** @var string $error */ ?>
<?php /** @var string $view */ ?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Hệ thống Quản lý Khách sạn</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f7f6; margin: 0; padding: 20px; }
        .container { max-width: 1000px; margin: 0 auto; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #eee; padding-bottom: 10px; margin-bottom: 20px; }
        .tabs { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 20px; }
        .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 15px; }
        .card { padding: 15px; border-radius: 8px; border: 1px solid #ddd; text-align: center; }
        .card.empty { background-color: #e8f5e9; border-color: #c8e6c9; }
        .card.rented { background-color: #ffebee; border-color: #ffcdd2; }
        .btn { display: inline-block; padding: 8px 12px; border-radius: 4px; text-decoration: none; color: #fff; background: #007bff; border: none; cursor: pointer; margin-top: 5px;}
        .btn-danger { background: #dc3545; }
        .btn-warning { background: #ffc107; color: #333; }
        .form-group { margin-bottom: 15px; text-align: left; }
        input[type="text"], input[type="password"], input[type="date"] { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px; border-bottom: 1px solid #ddd; text-align: left; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Quản lý Khách sạn</h2>
            <div>
                <?php if (isset($_SESSION['user'])): ?>
                    Xin chào, <strong><?= htmlspecialchars((string) $_SESSION['user'], ENT_QUOTES, 'UTF-8') ?></strong>
                    <form method="POST" action="index.php?action=logout" style="display: inline;">
                        <button type="submit" class="btn btn-danger" style="margin-left: 10px;">Đăng xuất</button>
                    </form>
                <?php else: ?>
                    <a href="index.php?action=login" class="btn">Đăng nhập</a>
                <?php endif; ?>
            </div>
        </div>

        <nav class="tabs" aria-label="Điều hướng">
            <?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
                <a href="index.php?action=admin" class="btn">Tổng quan</a>
                <a href="index.php?action=admin_rooms" class="btn">Quản lý phòng</a>
                <a href="index.php?action=admin_accounts" class="btn">Tài khoản admin</a>
                <a href="index.php?action=customer" class="btn btn-warning">Khu vực khách hàng</a>
            <?php else: ?>
                <a href="index.php?action=dashboard" class="btn btn-warning">Khu vực khách hàng</a>
                <?php if (!isset($_SESSION['user'])): ?>
                    <a href="index.php?action=register" class="btn">Đăng ký</a>
                <?php endif; ?>
            <?php endif; ?>
        </nav>

        <?php if ($error !== ''): ?>
            <p role="alert" style="color: #b00020;"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>
        <?php if ($success !== ''): ?>
            <p role="status" style="color: #176b2c;"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>

        <?php
        $allowedViews = [
            'dangnhap.php', 'dangky.php', 'quanlyadmin.php', 'datphongadmin.php',
            'giahanadmin.php', 'khachhang.php', 'adminphong.php', 'admintaikhoan.php',
        ];
        if (in_array($view, $allowedViews, true)) {
            require __DIR__ . '/' . $view;
        }
        ?>
    </div>
</body>
</html>