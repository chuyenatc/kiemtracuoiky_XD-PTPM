BÁO CÁO 4/10

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
Controllers (`controllers/`): Mỗi màn hình có controller riêng với tên tiếng Việt tương ứng (như `DangNhapController.php`, `AdminPhongController.php`, `DatPhongController.php`). `index.php` định tuyến request; `ControllerChung.php` chứa các hàm dùng chung. `views/mainadmin.php` là layout bao các view nội dung.

Models (`model.php`): Xử lý truy vấn và thao tác dữ liệu MySQL thông qua PDO.

Views (`views/`): Chứa layout và các mẫu giao diện; không xử lý request hoặc truy vấn cơ sở dữ liệu.

`index.php` là điểm vào ứng dụng, khởi tạo Model và Controller.

2, Cá chức năng người dùng
    -đặt phòng trong khoảng thời gina từ ngày dd/mm/yyyy - dd/mm/yyyy trong tab đặt phòng
    -trả phòng trước hạn nếu không còn nhưu cầu lưu trú nữa
    -gia hạn phòng nếu có yêu cầu ở thêm

3, các chức năng của admin 
 - Xem tổng quan trạng thái phòng, lượt thuê, gia hạn và trả phòng.
 - Thêm phòng và xóa phòng chưa từng có lịch sử đặt. Phòng đang thuê hoặc có lịch sử đặt được giữ lại để bảo toàn dữ liệu.
 - Tạo tài khoản admin mới trong tab quản lý tài khoản; khách hàng không được truy cập các chức năng quản trị.

4, Khu vực khách hàng
 - Tab khách hàng hiển thị trạng thái phòng và cho phép tài khoản khách hàng đặt phòng còn trống.
 - Khách hàng đăng ký tài khoản thường; chỉ tài khoản có vai trò admin mới được cấp quyền quản trị.

6, Giá thuê và khôi phục mật khẩu
 - Giá phòng được tính theo đêm, bằng VND. Admin có thể đặt giá khi thêm phòng hoặc cập nhật giá trong tab quản lý phòng. Hệ thống lưu lại đơn giá tại thời điểm đặt phòng để các lượt thuê cũ không bị đổi giá khi admin cập nhật bảng giá; tổng tiền dự kiến được tính theo số đêm.
 - Để cập nhật CSDL cũ, chạy một lần các lệnh sau. Trước khi chạy, sao lưu CSDL:

   
dùng plane sinh ra hệ thống

## Chạy dự án bằng Docker

1. Cài Docker Desktop và mở Docker Engine.
2. Tại thư mục dự án, sao chép `docker/.env.example` thành `docker/.env` và đặt các mật khẩu riêng cho `MYSQL_PASSWORD` và `MYSQL_ROOT_PASSWORD`. Không chia sẻ hoặc commit `docker/.env`.
3. Khởi động ứng dụng và MySQL:

   ```powershell
   docker compose --env-file docker/.env -f docker/docker-compose.yml up --build -d
   ```

4. Mở `http://localhost:8080` (hoặc cổng đã đặt trong `APP_PORT`).
5. Dừng các container bằng `docker compose --env-file docker/.env -f docker/docker-compose.yml down`. Dữ liệu MySQL được lưu trong volume `hotel_mysql_data`; không dùng `docker compose down -v` nếu muốn giữ dữ liệu.

`database.sql` chỉ được MySQL nạp tự động khi volume dữ liệu được tạo lần đầu. Tài khoản ứng dụng kết nối MySQL bằng `MYSQL_USER`; `MYSQL_ROOT_PASSWORD` chỉ dành cho quản trị database. MySQL không được publish cổng ra máy host. Tài khoản demo của website là `admin` / `123456`; hãy đổi mật khẩu này trước khi đưa ứng dụng lên môi trường thật. Các biến `HOTEL_APP_URL` và `HOTEL_MAIL_FROM` dùng cho chức năng gửi email đặt lại mật khẩu.

Nếu volume MySQL đã được khởi tạo bằng cấu hình cũ, các biến mật khẩu mới không tự đổi thông tin đăng nhập trong database đang tồn tại. Không xóa volume nếu cần giữ dữ liệu; cần cập nhật user MySQL hiện có trước khi khởi động lại ứng dụng.