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
