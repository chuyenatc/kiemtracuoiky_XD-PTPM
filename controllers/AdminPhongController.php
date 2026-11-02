<?php
declare(strict_types=1);

require_once __DIR__ . '/ControllerChung.php';

class AdminPhongController extends ControllerChung
{
    public function handle(string $action = 'admin_rooms'): void
    {
        $roomName = '';
        if (in_array($action, ['room_add', 'room_price', 'room_delete', 'room_photo', 'room_image_delete', 'room_description'], true)) {
            if (!$this->isPost()) {
                $this->redirect('admin_rooms');
            }

            if ($action === 'room_add') {
                $submittedRoomName = $_POST['room_name'] ?? '';
                $nightlyRate = $this->parseVndAmount($_POST['nightly_rate'] ?? null);
                $description = $_POST['description'] ?? '';
                $imagePaths = [];
                if (!is_string($submittedRoomName)
                    || trim($submittedRoomName) === ''
                    || strlen(trim($submittedRoomName)) > 20
                    || $nightlyRate === null
                    || !is_string($description)
                    || strlen($description) > 6000) {
                    $this->error = 'Tên phòng, giá thuê hoặc mô tả không hợp lệ. Mô tả không vượt quá 2.000 ký tự.';
                    $roomName = is_string($submittedRoomName) ? trim($submittedRoomName) : '';
                } else {
                    try {
                        $imagePaths = $this->storeRoomImages($_FILES['room_images'] ?? null);
                    } catch (InvalidArgumentException $exception) {
                        $this->error = $exception->getMessage();
                    }

                    if ($this->error === '') {
                        try {
                            $created = $this->model->taoPhong(
                                trim($submittedRoomName),
                                $nightlyRate,
                                trim($description),
                                $imagePaths
                            );
                        } catch (Throwable $exception) {
                            foreach ($imagePaths as $imagePath) {
                                $this->deleteRoomImage($imagePath);
                            }
                            throw $exception;
                        }

                        if (!$created) {
                            foreach ($imagePaths as $imagePath) {
                                $this->deleteRoomImage($imagePath);
                            }
                            $this->error = 'Tên phòng đã tồn tại. Vui lòng chọn tên khác.';
                            $roomName = trim($submittedRoomName);
                        } else {
                            $_SESSION['flash_success'] = 'Đã thêm phòng mới.';
                            $this->redirect('admin_rooms');
                        }
                    }
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
            } elseif ($action === 'room_delete') {
                $roomId = filter_input(INPUT_POST, 'room_id', FILTER_VALIDATE_INT);
                $room = $roomId ? $this->model->layPhong($roomId) : null;
                if (!$room || !$this->model->xoaPhong($roomId)) {
                    $this->error = 'Không thể xóa phòng không tồn tại, đang được thuê hoặc đã có lịch sử đặt.';
                } else {
                    foreach ($room['images'] as $image) {
                        if (is_string($image['image_path'] ?? null)) {
                            $this->deleteRoomImage($image['image_path']);
                        }
                    }
                    $this->deleteRoomImage($room['image_path'] ?? null);
                    $_SESSION['flash_success'] = 'Đã xóa phòng.';
                    $this->redirect('admin_rooms');
                }
            } elseif ($action === 'room_image_delete') {
                $imageId = filter_input(INPUT_POST, 'image_id', FILTER_VALIDATE_INT);
                $imagePath = $imageId ? $this->model->xoaAnhPhong($imageId) : null;
                if ($imagePath === null) {
                    $this->error = 'Không tìm thấy ảnh phòng cần xóa.';
                } else {
                    $this->deleteRoomImage($imagePath);
                    $_SESSION['flash_success'] = 'Đã xóa ảnh phòng.';
                    $this->redirect('admin_rooms');
                }
            } elseif ($action === 'room_description') {
                $roomId = filter_input(INPUT_POST, 'room_id', FILTER_VALIDATE_INT);
                $description = $_POST['description'] ?? '';
                if (!$roomId || !is_string($description) || strlen($description) > 6000) {
                    $this->error = 'Mô tả phòng không hợp lệ hoặc vượt quá 2.000 ký tự.';
                } elseif (!$this->model->capNhatMoTaPhong($roomId, trim($description))) {
                    $this->error = 'Không tìm thấy phòng cần cập nhật mô tả.';
                } else {
                    $_SESSION['flash_success'] = 'Đã cập nhật mô tả phòng.';
                    $this->redirect('admin_rooms');
                }
            } else {
                $roomId = filter_input(INPUT_POST, 'room_id', FILTER_VALIDATE_INT);
                if (!$roomId) {
                    $this->error = 'Phòng không hợp lệ.';
                } else {
                    $room = $this->model->layPhong($roomId);
                    if (!$room) {
                        $this->error = 'Không tìm thấy phòng cần cập nhật ảnh.';
                    } else {
                        try {
                            $imagePaths = $this->storeRoomImages($_FILES['room_images'] ?? null);
                        } catch (InvalidArgumentException $exception) {
                            $this->error = $exception->getMessage();
                            $imagePaths = [];
                        }

                        if ($this->error === '' && !$imagePaths) {
                            $this->error = 'Vui lòng chọn ít nhất một ảnh để tải lên.';
                        } elseif ($this->error === '') {
                            try {
                                $updated = $this->model->themNhieuAnhPhong($roomId, $imagePaths);
                            } catch (Throwable $exception) {
                                foreach ($imagePaths as $imagePath) {
                                    $this->deleteRoomImage($imagePath);
                                }
                                throw $exception;
                            }

                            if (!$updated) {
                                foreach ($imagePaths as $imagePath) {
                                    $this->deleteRoomImage($imagePath);
                                }
                                $this->error = 'Không thể lưu ảnh cho phòng này.';
                            } else {
                                $_SESSION['flash_success'] = 'Đã thêm ảnh cho phòng.';
                                $this->redirect('admin_rooms');
                            }
                        }
                    }
                }
            }
        }

        $this->render('adminphong.php', [
            'rooms' => $this->model->quanly(),
            'roomName' => $roomName,
        ]);
    }
}
