<?php
declare(strict_types=1);

require_once __DIR__ . '/ControllerChung.php';

class AdminPhongController extends ControllerChung
{
    public function handle(string $action = 'admin_rooms'): void
    {
        $roomName = '';
        if ($action === 'room_add' || $action === 'room_price' || $action === 'room_delete') {
            if (!$this->isPost()) {
                $this->redirect('admin_rooms');
            }

            if ($action === 'room_add') {
                $submittedRoomName = $_POST['room_name'] ?? '';
                $nightlyRate = $this->parseVndAmount($_POST['nightly_rate'] ?? null);
                if (!is_string($submittedRoomName)
                    || trim($submittedRoomName) === ''
                    || strlen(trim($submittedRoomName)) > 20
                    || $nightlyRate === null) {
                    $this->error = 'Tên phòng phải có từ 1 đến 20 ký tự và giá thuê mỗi đêm phải lớn hơn 0.';
                    $roomName = is_string($submittedRoomName) ? trim($submittedRoomName) : '';
                } elseif (!$this->model->themPhong(trim($submittedRoomName), $nightlyRate)) {
                    $this->error = 'Tên phòng đã tồn tại. Vui lòng chọn tên khác.';
                    $roomName = trim($submittedRoomName);
                } else {
                    $_SESSION['flash_success'] = 'Đã thêm phòng mới.';
                    $this->redirect('admin_rooms');
                }
            } elseif ($action === 'room_price') {
                $roomId = filter_input(INPUT_POST, 'room_id', FILTER_VALIDATE_INT);
                $nightlyRate = $this->parseVndAmount($_POST['nightly_rate'] ?? null);
                if (!$roomId || $nightlyRate === null
                    || !$this->model->capNhatGiaPhong($roomId, $nightlyRate)) {
                    $this->error = 'Không thể cập nhật giá. Hãy kiểm tra phòng và nhập giá lớn hơn 0.';
                } else {
                    $_SESSION['flash_success'] = 'Đã cập nhật giá thuê phòng.';
                    $this->redirect('admin_rooms');
                }
            } else {
                $roomId = filter_input(INPUT_POST, 'room_id', FILTER_VALIDATE_INT);
                if (!$roomId || !$this->model->xoaPhong($roomId)) {
                    $this->error = 'Không thể xóa phòng không tồn tại, đang được thuê hoặc đã có lịch sử đặt.';
                } else {
                    $_SESSION['flash_success'] = 'Đã xóa phòng.';
                    $this->redirect('admin_rooms');
                }
            }
        }

        $this->render('adminphong.php', [
            'rooms' => $this->model->quanly(),
            'roomName' => $roomName,
        ]);
    }
}
