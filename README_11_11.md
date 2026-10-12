BÁO CÁO 12/10

1, Làm sao để xây dựng 1 hệ thống với sự trợ giúp của Agent
+Để bắt đầu xây rựng 1 hệ thông với sự trợ giúp của Agent đầu tiên chúng ta cần sác định được rõ mục tiêu của hệ thống như:

    -hệ thống có các trức năng gì
    vd:hệ thống quản lý khác sạn sẽ có cấc chức năng cơ bản như: quản lý số lượng phòng, xử lý yêu cầu đặt phòng của khách hàng, xủa lý yêu cầu trả phòng,..... 

    -cấu trúc hệ thống mà mình hướng tới MVC, MVP, MVVM,....

    -lựa trong ngôn ngữ lập trình cho dự án của bạn một ngôn ngữ mà mình giỏi nhất(c,c#,java....)

+Tiếp theo ta cần yêu cầu Agent tạo cho ta môt database với các thực thể và các mối quan hệ giữa chúng để phuc vụ các hàm chức năng mà ta yêu cầu AI tạo ra sau này

+tạo giao diên và tính năng với AI
    -với giao diện có thể sử dụng cá công cụ kếu thả để thiết kế

    -với các tính năng thay vì yêu cầu AI làm cả 1 tính năng to thì chỉ lên yêu cầu Ai làm cái tính năng nhỏ dạng module

+Đặc biệt luôn yêu cầu AI giải thích các đoạn code có độ phúc tạp cao để lắm rõ các luồng logic, đảm bảo quyền kiểm soát codE


DỰ ÁN WEBSITE QUẢN LÝ KHÁC SẠN
Dự án Website quản lý khách sạn được xây dựng theo mô hình MVC (Model - View - Controller) bằng ngôn ngữ PHP thuần (Vanilla PHP).

1, Kiến trúc hệ thống (MVC)
Controllers (app/Controllers/): Điều hướng và xử lý logic các luồng người dùng (HomeController, ProductController, AdminProductController).

Models (app/Models/): Xử lý logic nghiệp vụ và tương tác với cơ sở dữ liệu MySQL thông qua PDO (ProductModel, BaseModel).

Views (app/Views/): Chứa giao diện người dùng hiển thị thông tin sản phẩm.

2, Cá chức năng người dùng
người dùng cơ bản có khả năng:

đặt phòng trong khoảng thời gina từ ngày dd/mm/yyyy - dd/mm/yyyy trong tab đặt phòng

trả phòng trước hạn nếu không còn nhưu cầu lưu trú nữa

gia hạn phòng nếu có yêu cầu ở thêm

3, các chức năng của admin 
 - Xem tổng quan trạng thái phòng, lượt thuê, gia hạn và trả phòng.
 - Thêm phòng và xóa phòng chưa từng có lịch sử đặt. Phòng đang thuê hoặc có lịch sử đặt được giữ lại để bảo toàn dữ liệu.
 - Tạo tài khoản admin mới trong tab quản lý tài khoản; khách hàng không được truy cập các chức năng quản trị.

4, Khu vực khách hàng
 - Tab khách hàng hiển thị trạng thái phòng và cho phép tài khoản khách hàng đặt phòng còn trống.
 - Khách hàng đăng ký tài khoản thường; chỉ tài khoản có vai trò admin mới được cấp quyền quản trị.

5, Cập nhật cơ sở dữ liệu
 - Cài mới: chạy `database.sql`.
 - CSDL đã tồn tại: bảo đảm bảng `users` có cột `role` với giá trị `admin`/`customer`; đặt tài khoản quản trị hiện có thành `admin`. Để ngăn tên phòng trùng lặp, sau khi xử lý các tên phòng hiện có bị trùng, chạy:
   `ALTER TABLE rooms MODIFY room_name VARCHAR(20) NOT NULL;`
   `ALTER TABLE rooms ADD UNIQUE KEY uq_rooms_room_name (room_name);`

6, Giá thuê và khôi phục mật khẩu
 - Giá phòng được tính theo đêm, bằng VND. Admin có thể đặt giá khi thêm phòng hoặc cập nhật giá trong tab quản lý phòng. Hệ thống lưu lại đơn giá tại thời điểm đặt phòng để các lượt thuê cũ không bị đổi giá khi admin cập nhật bảng giá; tổng tiền dự kiến được tính theo số đêm.
 - Để cập nhật CSDL cũ, chạy một lần các lệnh sau. Trước khi chạy, sao lưu CSDL:

   ```sql
   ALTER TABLE users
       ADD COLUMN email VARCHAR(254) NULL,
       ADD UNIQUE KEY uq_users_email (email),
       ADD COLUMN reset_token_hash CHAR(64) NULL,
       ADD COLUMN reset_expires_at DATETIME NULL;

   ALTER TABLE rooms
       ADD COLUMN nightly_rate BIGINT UNSIGNED NOT NULL DEFAULT 0;

   UPDATE rooms SET nightly_rate = 500000 WHERE room_name IN ('101', '102');
   UPDATE rooms SET nightly_rate = 650000 WHERE room_name IN ('201', '202');

   ALTER TABLE bookings
       ADD COLUMN nightly_rate BIGINT UNSIGNED NOT NULL DEFAULT 0;

   UPDATE bookings b
   INNER JOIN rooms r ON r.id = b.room_id
   SET b.nightly_rate = r.nightly_rate;
   ```

   Các lượt đặt cũ không có lịch sử giá chính xác; câu `UPDATE bookings` gán giá hiện tại của phòng làm giá ước tính. Đặt email cho tài khoản cũ để họ có thể tự khôi phục mật khẩu, ví dụ:

   ```sql
   UPDATE users SET email = 'admin@example.com' WHERE username = 'admin';
   ```

 - Đăng ký tài khoản mới yêu cầu email duy nhất. Khi tạo tài khoản admin, nhập email khôi phục; tài khoản đã tồn tại cần được cập nhật email bằng SQL hoặc cách quản trị CSDL hiện có.
 - Để gửi liên kết đặt lại mật khẩu, cấu hình hai biến môi trường cho tiến trình PHP/Apache: `HOTEL_APP_URL` (URL gốc của ứng dụng, ví dụ `http://localhost/kiemtracuoiky_XD-PTPM`) và `HOTEL_MAIL_FROM` (địa chỉ email gửi). PHP `mail()` cũng phải được cấu hình để gửi thư qua SMTP/sendmail trong `php.ini`/XAMPP. Trên website thật, dùng URL HTTPS và SMTP của nhà cung cấp email; không lưu mật khẩu SMTP trong mã nguồn.
 - Liên kết đặt lại chỉ dùng một lần và hết hạn sau 30 phút. Nếu PHP không gửi được email, chi tiết lỗi được ghi vào PHP error log; người dùng cần liên hệ quản trị viên hoặc thử lại sau.
