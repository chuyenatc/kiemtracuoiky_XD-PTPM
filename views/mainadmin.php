<?php /** @var string $action */ ?>
<?php /** @var string $error */ ?>
<?php /** @var string $view */ ?>
<?php /** @var string $success */ ?>
<?php /** @var string $csrfToken */ ?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Hệ thống Quản lý Khách sạn</title>
    <style>
        :root { color-scheme: light; --ink: #172b4d; --muted: #64748b; --line: #e2e8f0; --brand: #176b63; --brand-dark: #10544e; --surface: #fff; }
        * { box-sizing: border-box; }
        body { min-height: 100vh; font-family: Inter, "Segoe UI", Arial, sans-serif; background: #f3f6f8; color: var(--ink); margin: 0; padding: 0; }
        .container { width: 100%; min-height: 100vh; margin: 0; background: var(--surface); padding: clamp(18px, 2.4vw, 36px); border-radius: 0; box-shadow: none; }
        .header { display: flex; justify-content: space-between; align-items: center; gap: 16px; border-bottom: 1px solid var(--line); padding-bottom: 18px; margin-bottom: 22px; }
        .header h2 { margin: 0; letter-spacing: -.03em; }
        .tabs { display: flex; flex-wrap: wrap; gap: 9px; margin-bottom: 24px; }
        .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(min(100%, 280px), 1fr)); gap: clamp(14px, 1.5vw, 22px); }
        .card { padding: 16px; border-radius: 16px; border: 1px solid var(--line); background: #fff; text-align: left; overflow: hidden; }
        .card.empty { border-color: #c8e9de; }
        .card.rented { border-color: #f2d4d1; }
        .btn { display: inline-flex; align-items: center; justify-content: center; gap: 6px; padding: 10px 15px; border-radius: 10px; text-decoration: none; color: #fff; background: var(--brand); border: none; cursor: pointer; margin-top: 5px; font: inherit; font-weight: 650; transition: background .15s, transform .15s; }
        .btn:hover { color: #fff; background: var(--brand-dark); transform: translateY(-1px); }
        .btn-danger { background: #b93838; }
        .btn-danger:hover { background: #922c2c; }
        .btn-warning { color: #573d0b; background: #f4c96b; }
        .btn-warning:hover { color: #573d0b; background: #eebc4c; }
        .tabs .btn { background: #eef3f6; color: #334155; }
        .tabs .btn:hover, .tabs .btn.active { background: var(--brand); color: #fff; }
        .tabs .btn.btn-warning { background: #fff4d8; color: #75530f; }
        .form-group { margin-bottom: 15px; text-align: left; }
        input[type="text"], input[type="password"], input[type="date"], input[type="month"], input[type="email"], input[type="number"], input[type="file"], textarea { width: 100%; padding: 11px 12px; border: 1px solid #cbd5e1; border-radius: 9px; background: #fff; font: inherit; }
        input[type="radio"] { accent-color: var(--brand); }
        .payment-options { padding: 0; border: 0; }
        .payment-options legend { margin-bottom: 8px; font-weight: 700; }
        .payment-option { display: flex; align-items: center; gap: 9px; padding: 11px; margin: 7px 0; border: 1px solid var(--line); border-radius: 9px; cursor: pointer; }
        .payment-option input { width: auto; margin: 0; }
        .form-hint { color: var(--muted); font-size: .88rem; line-height: 1.5; }
        .payment-status { display: inline-flex; padding: 5px 9px; border-radius: 999px; background: #fff4d8; color: #75530f; font-size: .82rem; font-weight: 700; white-space: nowrap; }
        .payment-status.paid { background: #e6f4ee; color: #176b4b; }
        .payment-status.partial { background: #eaf3ff; color: #285d91; }
        .table-wrap { width: 100%; overflow-x: auto; border: 1px solid var(--line); border-radius: 12px; }
        .table-wrap table { min-width: 1050px; }
        .summary-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(190px, 1fr)); gap: 12px; margin: 18px 0; }
        .summary-card { padding: 16px; border: 1px solid var(--line); border-radius: 12px; background: #fff; }
        .summary-card span { display: block; color: var(--muted); font-size: .88rem; }
        .summary-card strong { display: block; margin-top: 6px; font-size: 1.2rem; }
        .inline-payment-form { min-width: 200px; }
        .inline-payment-form input, .inline-payment-form select { width: 100%; margin: 4px 0; padding: 8px; border: 1px solid #cbd5e1; border-radius: 8px; font: inherit; }
        .notice { padding: 12px 14px; border-radius: 10px; background: #eef6ff; color: #285d91; line-height: 1.5; }
        .qr-preview { max-width: 360px; margin: 14px 0; padding: 18px; border: 1px solid var(--line); border-radius: 14px; background: #fff; text-align: center; }
        .qr-preview[hidden] { display: none; }
        .qr-preview h4 { margin: 0 0 8px; }
        .booking-payment-panel { margin: 16px 0; padding: 18px; border: 1px solid var(--line); border-radius: 14px; background: #f8fbfa; }
        .booking-payment-panel h4 { margin: 0 0 12px; }
        .booking-payment-panel table { background: #fff; border: 1px solid var(--line); border-radius: 10px; overflow: hidden; }
        .booking-payment-panel .table-wrap table { min-width: 0; }
        .booking-payment-panel th { width: 58%; }
        .booking-payment-panel td { text-align: right; font-weight: 700; }
        .booking-payment-panel tr:last-child th, .booking-payment-panel tr:last-child td { color: var(--brand-dark); font-size: 1.05rem; }
        .qr-mock { display: grid; grid-template-columns: repeat(21, 1fr); gap: 1px; width: min(100%, 220px); aspect-ratio: 1; margin: 14px auto; padding: 8px; border: 1px solid #e2e8f0; background: #fff; }
        .qr-mock span { background: #fff; }
        .qr-mock span.is-dark { background: #172b4d; }
        .qr-preview-warning { color: #a63e20; font-size: .88rem; font-weight: 700; line-height: 1.5; }
        .booking-layout { display: block; }
        .booking-details, .booking-sidebar, .booking-fields { min-width: 0; }
        .booking-details .room-gallery { width: min(100%, 760px); max-width: 760px; }
        .booking-details .room-gallery .room-photo-item.active { width: 100%; }
        .booking-details .room-gallery .room-image { width: 100%; height: auto; max-height: 430px; aspect-ratio: 16 / 9; object-fit: cover; }
        .booking-details .room-gallery .gallery-controls { width: max-content; margin: 8px auto 0; }
        .booking-form-row { display: grid; grid-template-columns: minmax(0, 1fr) minmax(340px, .9fr); align-items: start; gap: clamp(20px, 3vw, 40px); margin-top: 20px; }
        .booking-fields .form-group { max-width: 620px; }
        .booking-sidebar { position: static; }
        .booking-sidebar .booking-payment-panel { margin-top: 0; }
        .booking-actions { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 14px; }
        .booking-actions .btn { flex: 1 1 150px; margin-top: 0; }
        input:focus { outline: 3px solid rgba(23, 107, 99, .15); border-color: var(--brand); }
        textarea:focus { outline: 3px solid rgba(23, 107, 99, .15); border-color: var(--brand); }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px 11px; border-bottom: 1px solid var(--line); text-align: left; }
        th { background: #f4f7f9; color: #475569; font-size: .88rem; }
        .room-image { display: block; width: 100%; height: 190px; object-fit: cover; border-radius: 12px; background: #e8eef1; }
        .room-gallery { margin: 10px 0; }
        .room-card .room-gallery[data-room-gallery] { position: relative; height: 190px; margin: 0 0 12px; }
        .room-card .room-gallery[data-room-gallery] .room-photo-item.active { height: 100%; }
        .room-card .room-gallery[data-room-gallery] .room-image { height: 100%; }
        .room-card .room-gallery[data-room-gallery] .gallery-controls { position: absolute; right: 0; bottom: 7px; left: 0; width: max-content; margin: 0 auto; padding: 3px 7px; border-radius: 999px; background: rgba(255, 255, 255, .92); box-shadow: 0 2px 8px rgba(15, 23, 42, .12); }
        .room-gallery[data-room-gallery] .gallery-controls { opacity: 0; pointer-events: none; transition: opacity .18s ease; }
        .room-gallery[data-room-gallery]:hover .gallery-controls, .room-gallery[data-room-gallery]:focus-within .gallery-controls { opacity: 1; pointer-events: auto; }
        .room-card > .room-image-placeholder { height: 190px; margin-bottom: 12px; }
        .room-gallery-mosaic { display: grid; width: 100%; height: clamp(320px, 36vw, 430px); grid-template-columns: repeat(5, minmax(0, 1fr)); grid-template-rows: repeat(2, minmax(0, 1fr)); gap: 8px; margin: 18px 0 0; }
        .room-gallery-mosaic .room-mosaic-image { position: relative; min-width: 0; min-height: 0; overflow: hidden; border-radius: 9px; background: #fff; }
        .room-gallery-mosaic .room-mosaic-image:first-child { grid-column: span 2; grid-row: span 2; }
        .room-gallery-mosaic .room-mosaic-image img { display: block; width: 100%; height: 100%; object-fit: cover; background: transparent; }
        .room-detail .room-gallery-mosaic .room-image { width: 100%; height: 100%; min-height: 0; object-fit: cover; background: transparent; }
        .room-mosaic-more { position: absolute; inset: 0; display: grid; place-items: center; border: 0; background: rgba(15, 23, 42, .56); color: #fff; font: inherit; font-weight: 700; cursor: pointer; }
        .room-mosaic-more:hover { background: rgba(15, 23, 42, .68); }
        .room-breadcrumb { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; color: var(--muted); font-size: .9rem; }
        .room-breadcrumb a { color: #0876c9; text-decoration: none; }
        .room-summary { display: flex; align-items: center; justify-content: space-between; gap: 20px; padding: 18px 8px; }
        .room-summary h1 { margin: 0 0 9px; font-size: clamp(1.45rem, 2.4vw, 2rem); }
        .room-summary-meta { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; color: var(--muted); }
        .room-summary-booking { display: flex; align-items: center; gap: 15px; flex-shrink: 0; }
        .room-summary-price { text-align: right; color: var(--muted); }
        .room-summary-price strong { display: block; color: #e85a24; font-size: 1.4rem; white-space: nowrap; }
        .room-summary .btn { border-radius: 999px; padding: 13px 22px; background: #0799ed; }
        .room-summary .btn:hover { background: #087bc0; }
        .room-detail-content { padding: 20px; background: #fff; border: 1px solid var(--line); border-radius: 18px; box-shadow: 0 4px 16px rgba(15, 23, 42, .08); }
        .room-detail-content .panel { margin-bottom: 0; }
        .room-photo-dialog { width: min(1100px, calc(100vw - 32px)); max-height: 90vh; padding: 20px; border: 0; border-radius: 16px; }
        .room-photo-dialog::backdrop { background: rgba(15, 23, 42, .72); }
        .room-photo-dialog-header { display: flex; justify-content: space-between; align-items: center; gap: 12px; margin-bottom: 16px; }
        .room-photo-dialog-header h2 { margin: 0; }
        .room-photo-dialog-close { border: 0; border-radius: 999px; padding: 8px 13px; background: #edf2f7; font: inherit; cursor: pointer; }
        .room-photo-dialog-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 12px; max-height: calc(90vh - 90px); overflow: auto; }
        .room-photo-dialog-grid img { width: 100%; aspect-ratio: 4 / 3; object-fit: contain; background: #f3f6f8; border-radius: 10px; }
        .room-photo-item { display: none; }
        .room-photo-item.active { display: block; }
        .room-photo-item form { margin-top: 4px; }
        .gallery-controls { display: flex; align-items: center; justify-content: center; gap: 12px; margin-top: 8px; }
        .gallery-controls button { border: 0; border-radius: 999px; padding: 7px 12px; background: #e8f0ef; color: var(--brand-dark); font: inherit; font-weight: 700; cursor: pointer; }
        .gallery-controls button:hover { background: #d7e8e4; }
        .gallery-counter { min-width: 48px; text-align: center; color: var(--muted); font-size: .9rem; }
        .room-image-placeholder { display: grid; place-items: center; width: 100%; height: 190px; border-radius: 12px; background: linear-gradient(135deg, #e1efeb, #edf2f7); color: #56716b; font-weight: 650; }
        .room-card h2, .room-card h3 { margin: 13px 0 8px; }
        .status { display: inline-flex; padding: 5px 9px; border-radius: 999px; background: #e6f4ee; color: #176b4b; font-size: .82rem; font-weight: 700; }
        .status.rented { background: #fff0ed; color: #a53d33; }
        .panel { padding: 20px; margin: 18px 0; background: #f7f9fb; border: 1px solid var(--line); border-radius: 14px; }
        .summary-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(190px, 1fr)); gap: 14px; margin: 20px 0; }
        .summary-card { padding: 18px; border: 1px solid var(--line); border-radius: 14px; background: linear-gradient(145deg, #fff, #f8fbfa); }
        .summary-card span { color: var(--muted); font-size: .9rem; }
        .summary-card strong { display: block; margin-top: 9px; font-size: 1.5rem; }
        .table-wrap { width: 100%; overflow-x: auto; }
        .notice { padding: 13px 15px; border-radius: 10px; background: #eef6f4; color: #285d55; }
        .room-detail { width: 100%; max-width: 1440px; margin: 0 auto; }
        .room-detail > .room-image-placeholder { width: 100%; height: 340px; min-height: 0; border-radius: 10px; }
        .room-detail .room-gallery { max-width: 100% !important; }
        .room-detail .panel { max-width: 100%; }
        .room-detail-description { white-space: pre-line; line-height: 1.7; color: #475569; }
        @media (min-width: 1600px) { .grid { grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); } }
        @media (hover: none) { .room-gallery[data-room-gallery] .gallery-controls { opacity: 1; pointer-events: auto; } }
        @media (max-width: 760px) { .room-gallery-mosaic { height: 310px; grid-template-columns: repeat(3, minmax(0, 1fr)); grid-template-rows: repeat(2, minmax(0, 1fr)); gap: 5px; } .room-gallery-mosaic .room-mosaic-image:first-child { grid-column: span 1; grid-row: span 2; } .room-gallery-mosaic .room-mosaic-image:nth-child(n+5) { display: none; } .room-detail > .room-image-placeholder { height: 280px; } .room-summary { align-items: flex-start; flex-direction: column; } .room-summary-booking { width: 100%; justify-content: space-between; } }
        @media (max-width: 900px) { .booking-form-row { grid-template-columns: minmax(0, 1fr); } }
        @media (max-width: 640px) { .container { padding: 16px; } .header { align-items: flex-start; flex-direction: column; } .room-image, .room-image-placeholder { height: 190px; } .room-detail > .room-image-placeholder { height: 240px; } .room-detail-content { padding: 14px; } .room-summary-booking { align-items: flex-start; flex-direction: column; } .room-summary-price { text-align: left; } th, td { padding: 9px 8px; } }
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
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                        <button type="submit" class="btn btn-danger" style="margin-left: 10px;">Đăng xuất</button>
                    </form>
                <?php else: ?>
                    <a href="index.php?action=login" class="btn">Đăng nhập</a>
                <?php endif; ?>
            </div>
        </div>

        <nav class="tabs" aria-label="Điều hướng">
            <?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
                <a href="index.php?action=admin" class="btn <?= in_array($action, ['admin', 'dashboard', 'checkout'], true) ? 'active' : '' ?>">Tổng quan</a>
                <a href="index.php?action=admin_rooms" class="btn <?= in_array($action, ['admin_rooms', 'room_add', 'room_price', 'room_delete', 'room_photo', 'room_image_delete', 'room_description'], true) ? 'active' : '' ?>">Quản lý phòng</a>
                <a href="index.php?action=monthly_report" class="btn <?= $action === 'monthly_report' ? 'active' : '' ?>">Lượt thuê &amp; doanh thu</a>
                <a href="index.php?action=payments" class="btn <?= in_array($action, ['payments', 'payment_record'], true) ? 'active' : '' ?>">Thanh toán</a>
                <a href="index.php?action=admin_accounts" class="btn <?= in_array($action, ['admin_accounts', 'admin_add'], true) ? 'active' : '' ?>">Tài khoản admin</a>
                <a href="index.php?action=customer" class="btn btn-warning">Khu vực khách hàng</a>
            <?php else: ?>
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
            'quenmatkhau.php', 'datlaimatkhau.php',
            'baocao.php', 'thongtinphong.php', 'thanhtoan.php',
        ];
        if (in_array($view, $allowedViews, true)) {
            require __DIR__ . '/' . $view;
        }
        ?>
    </div>
    <script>
        document.querySelectorAll('[data-room-photos-dialog]').forEach((dialog) => {
            const opener = document.querySelector(`[data-room-photos-open="${dialog.id}"]`);
            const closeButton = dialog.querySelector('[data-room-photos-close]');
            opener?.addEventListener('click', () => dialog.showModal());
            closeButton?.addEventListener('click', () => dialog.close());
            dialog.addEventListener('click', (event) => {
                if (event.target === dialog) dialog.close();
            });
        });
        document.querySelectorAll('[data-room-gallery]').forEach((gallery) => {
            const slides = Array.from(gallery.querySelectorAll('[data-gallery-slide]'));
            const counter = gallery.querySelector('[data-gallery-counter]');
            let currentIndex = slides.findIndex((slide) => slide.classList.contains('active'));
            if (currentIndex < 0) currentIndex = 0;

            const showSlide = (index) => {
                currentIndex = (index + slides.length) % slides.length;
                slides.forEach((slide, slideIndex) => {
                    const active = slideIndex === currentIndex;
                    slide.classList.toggle('active', active);
                    slide.setAttribute('aria-hidden', active ? 'false' : 'true');
                });
                if (counter) counter.textContent = `${currentIndex + 1} / ${slides.length}`;
            };

            gallery.querySelector('[data-gallery-previous]')?.addEventListener('click', () => {
                showSlide(currentIndex - 1);
            });
            gallery.querySelector('[data-gallery-next]')?.addEventListener('click', () => {
                showSlide(currentIndex + 1);
            });
            showSlide(currentIndex);
        });
    </script>
</body>
</html>