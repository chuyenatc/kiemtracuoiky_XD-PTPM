---
name: chuyentest-skills
description: 'Use when testing, validating, or debugging this hotel-management web system: PHP MVC routes, customer/admin permissions, booking, room management, extension, reports, payments, database migrations, or browser-based regression tests.'
argument-hint: 'Describe the feature, route, or workflow to test'
user-invocable: true
---

# Chuyen Test Skills

## Muc tieu

Kiem thu he thong website quan ly khach san PHP MVC trong workspace nay. Uu tien tim loi co the lap lai, bao ve du lieu, xac nhan hanh vi theo dung yeu cau va bao cao ro nhung gi da/chua duoc kiem tra.

## Khi nao su dung

- Kiem thu chuc nang moi hoac sau khi sua code.
- Kiem tra luong end-to-end tren trinh duyet.
- Kiem tra phan quyen admin/khach hang, CSRF, validate form.
- Kiem tra dat phong, tra phong, gia han, thanh toan va bao cao.
- Kiem tra migration hoac loi ket noi PHP/MySQL.
- Chay regression test sau khi thay doi controller, model, route hoac view.

## Quy trinh

### 1. Xac dinh pham vi va rui ro

1. Doc yeu cau va xac dinh action, controller, model, view, bang du lieu va vai tro lien quan.
2. Kiem tra route trong `index.php`, controller `handle()`, cac truy van Model va view duoc render.
3. Ghi nhan tien dieu kien: schema/migration can co, tai khoan/role can dung, app/server co chay hay khong.
4. Khong tu y sua code khi nguoi dung chi yeu cau kiem thu. Neu tim loi, trinh bay bang chung va de xuat sua; chi sua khi nguoi dung yeu cau hoac giao viec bao gom fix.

### 2. Kiem tra tinh hop le va phan tich tinh

1. Chay PHP syntax lint cho cac file PHP thay doi; neu pham vi lon thi lint toan bo PHP:

   ```powershell
   $root = (Get-Location).Path
   Get-ChildItem -LiteralPath $root -Recurse -Filter '*.php' |
       ForEach-Object { & 'C:\xampp\php\php.exe' -l $_.FullName }
   ```

2. Dung test runner/configuration da co trong du an neu ton tai; khong tu them dependency chi de test mot thay doi nho.
3. Kiem tra route khong map nham controller, view duoc allowlist, form POST co CSRF va action nhay cam duoc kiem tra role.
4. Neu co migration, doi chieu schema cua cai moi va CSDL cu. Khong chay migration tren database co du lieu that neu chua co ban sao luu va su dong y ro rang.

### 3. Kiem thu chuc nang theo vai tro

Kiem tra cac luong lien quan den thay doi, bao gom ca truong hop thanh cong va that bai:

- **Khach hang:** xem phong/anh/chi tiet; dat phong voi ngay hop le va khong hop le; xem booking cua minh; chi gia han booking do tai khoan minh so huu.
- **Admin:** tong quan, quan ly phong/anh/gia/mo ta, tra phong, gia han, bao cao theo thang, doi soat thanh toan, quan ly tai khoan.
- **Danh tinh va phan quyen:** khach khong vao duoc action admin; khach khong doc/sua booking cua nguoi khac; admin van quan ly duoc booking cu chua co `user_id`.
- **Dat phong/thanh toan:** tinh so dem, tong tien, coc 20% cho ngay nhan trong tuong lai; khong yeu cau coc cho ngay nhan hom nay; method chi nhan `direct`/`online`; QR minh hoa phai duoc ghi ro khong thanh toan that; so tien ghi nhan khong vuot tong booking.
- **Gia han:** ngay moi phai sau ngay tra hien tai; chi booking `Active`; kiem tra owner o ca truy van doc va cap nhat; tinh lai coc khi luot thue tuong lai thay doi tong tien.
- **Anh phong:** chi chap nhan dinh dang/dung luong cho phep; khong lam hong danh sach phong neu chua co anh hoac du lieu anh loi.

Chi chay nhung case phu hop voi scope hien tai; khong danh dau case la dat neu chua thuc su chay.

### 4. Kiem thu trinh duyet

1. Su dung browser tool san co de mo trang va thao tac that khi app dang chay.
2. Kiem tra desktop va kich thuoc nho cho cac thay doi giao dien.
3. Kiem tra loi hien thi, trang thai form, dieu huong, responsive, console/browser error neu cong cu cho phep.
4. Khong tu dong gui form co the tao booking, thu tien, xoa phong/anh hoac thay doi du lieu that neu chua duoc phep. Neu can thao tac, uu tien du lieu test co the don sach va xac nhan moi truong la dev.
5. QR tren du an hien la anh minh hoa; khong xem no la ma thanh toan hop le.

### 5. Bao cao ket qua

Bao cao ngan gon theo cau truc:

- Pham vi va moi truong kiem thu.
- Cac lenh/test case da chay va ket qua.
- Loi tim thay, muc do anh huong, buoc tai hien va file/action lien quan.
- Phan chua the kiem tra (vi du: khong co MySQL credentials, Docker, browser session, hoac migration chua duoc ap dung).
- Khong khang dinh end-to-end/databased test thanh cong neu chi moi lint code.

## Nguyen tac an toan du lieu

- Khong reset, xoa, truncate, seed de len hoac sua du lieu that.
- Khong chay migration khong the dao nguoc tren database dang su dung ma khong co backup va chap thuan.
- Khong hien thi, ghi log hoac dua credentials vao output.
- Dung transaction/rollback hoac database rieng cho test ghi du lieu neu co the.
- Neu khong xac dinh duoc moi truong co an toan hay khong, dung lai va hoi nguoi dung truoc khi thay doi du lieu.
