<?php
declare(strict_types=1);

require_once __DIR__ . '/../model.php';

abstract class ControllerChung
{
    protected Model $model;
    protected string $csrfToken;
    protected string $error;
    protected string $success;

    public function __construct(Model $model, string $csrfToken, string $error = '', string $success = '')
    {
        $this->model = $model;
        $this->csrfToken = $csrfToken;
        $this->error = $error;
        $this->success = $success;
    }

    protected function render(string $view, array $data = []): void
    {
        $action = $_GET['action'] ?? 'dashboard';
        $csrfToken = $this->csrfToken;
        $error = $this->error;
        $success = $this->success;
        extract($data, EXTR_SKIP);
        require __DIR__ . '/../views/mainadmin.php';
    }

    protected function redirect(string $action): void
    {
        header('Location: index.php?action=' . rawurlencode($action));
        exit;
    }

    protected function redirectToDashboard(): void
    {
        $this->redirect(($_SESSION['role'] ?? '') === 'admin' ? 'admin' : 'dashboard');
    }

    protected function isPost(): bool
    {
        return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
    }

    protected function validDate(string $date): bool
    {
        $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $date);
        return $parsed !== false && $parsed->format('Y-m-d') === $date;
    }

    protected function bookingReturnTo(mixed $value): ?string
    {
        if (!is_string($value)
            || !preg_match('/\Aindex\.php\?action=book&id=([1-9][0-9]*)\z/', $value, $matches)) {
            return null;
        }

        return 'index.php?action=book&id=' . $matches[1];
    }

    protected function parseVndAmount(mixed $value, bool $allowZero = false): ?int
    {
        if (!is_string($value) || !ctype_digit($value)) {
            return null;
        }

        $amount = filter_var($value, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => $allowZero ? 0 : 1, 'max_range' => 999999999],
        ]);
        return $amount === false ? null : $amount;
    }

    protected function storeRoomImage(mixed $upload): string
    {
        if (!is_array($upload) || !isset($upload['error'], $upload['tmp_name'], $upload['size'])) {
            throw new InvalidArgumentException('Không nhận được tệp ảnh tải lên.');
        }

        if ($upload['error'] !== UPLOAD_ERR_OK) {
            $message = match ($upload['error']) {
                UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'Ảnh vượt quá dung lượng cho phép 5 MB.',
                UPLOAD_ERR_NO_FILE => 'Vui lòng chọn ảnh phòng.',
                default => 'Không thể nhận ảnh tải lên. Vui lòng thử lại.',
            };
            throw new InvalidArgumentException($message);
        }

        if (!is_string($upload['tmp_name'])
            || !is_uploaded_file($upload['tmp_name'])
            || !is_int($upload['size'])
            || $upload['size'] < 1
            || $upload['size'] > 5 * 1024 * 1024) {
            throw new InvalidArgumentException('Ảnh phải có dung lượng tối đa 5 MB.');
        }

        $imageInfo = @getimagesize($upload['tmp_name']);
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($upload['tmp_name']);
        $extensions = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
        ];
        if (!is_array($imageInfo)
            || !isset($extensions[$mimeType])
            || $imageInfo['mime'] !== $mimeType
            || $imageInfo[0] > 10000
            || $imageInfo[1] > 10000) {
            throw new InvalidArgumentException('Chỉ chấp nhận ảnh JPEG, PNG hoặc WebP hợp lệ.');
        }

        $directory = __DIR__ . '/../uploads/rooms';
        if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
            throw new RuntimeException('Không thể tạo thư mục lưu ảnh phòng.');
        }

        $filename = bin2hex(random_bytes(16)) . '.' . $extensions[$mimeType];
        $destination = $directory . '/' . $filename;
        if (!move_uploaded_file($upload['tmp_name'], $destination)) {
            throw new RuntimeException('Không thể lưu ảnh phòng đã tải lên.');
        }

        return 'uploads/rooms/' . $filename;
    }

    protected function storeRoomImages(mixed $uploads): array
    {
        if ($uploads === null) {
            return [];
        }

        if (!is_array($uploads)
            || !isset($uploads['name'], $uploads['tmp_name'], $uploads['error'], $uploads['size'])
            || !is_array($uploads['name'])
            || !is_array($uploads['tmp_name'])
            || !is_array($uploads['error'])
            || !is_array($uploads['size'])) {
            throw new InvalidArgumentException('Dữ liệu ảnh tải lên không hợp lệ.');
        }

        if (count($uploads['name']) > 10) {
            throw new InvalidArgumentException('Mỗi lần chỉ có thể tải lên tối đa 10 ảnh.');
        }

        $imagePaths = [];
        try {
            foreach ($uploads['name'] as $index => $name) {
                $error = $uploads['error'][$index] ?? UPLOAD_ERR_NO_FILE;
                if ($error === UPLOAD_ERR_NO_FILE) {
                    continue;
                }

                $imagePaths[] = $this->storeRoomImage([
                    'name' => $name,
                    'tmp_name' => $uploads['tmp_name'][$index] ?? null,
                    'error' => $error,
                    'size' => $uploads['size'][$index] ?? null,
                ]);
            }
        } catch (Throwable $exception) {
            foreach ($imagePaths as $imagePath) {
                $this->deleteRoomImage($imagePath);
            }
            throw $exception;
        }

        return $imagePaths;
    }

    protected function deleteRoomImage(?string $imagePath): void
    {
        if (!is_string($imagePath)
            || !preg_match('/\Auploads\/rooms\/[a-f0-9]{32}\.(?:jpg|png|webp)\z/', $imagePath)) {
            return;
        }

        $file = __DIR__ . '/../' . $imagePath;
        if (is_file($file) && !unlink($file)) {
            error_log('Unable to remove replaced room image: ' . $imagePath);
        }
    }

    protected function mailConfigured(): bool
    {
        $appUrl = rtrim((string) getenv('HOTEL_APP_URL'), '/');
        $from = (string) getenv('HOTEL_MAIL_FROM');
        $urlParts = parse_url($appUrl);

        return $appUrl !== ''
            && filter_var($appUrl, FILTER_VALIDATE_URL) !== false
            && is_array($urlParts)
            && in_array($urlParts['scheme'] ?? '', ['http', 'https'], true)
            && isset($urlParts['host'])
            && filter_var($from, FILTER_VALIDATE_EMAIL) !== false;
    }

    protected function sendPasswordResetEmail(string $email, string $token): void
    {
        $appUrl = rtrim((string) getenv('HOTEL_APP_URL'), '/');
        $from = (string) getenv('HOTEL_MAIL_FROM');
        if (!$this->mailConfigured()) {
            error_log('Password reset email is not configured; set HOTEL_APP_URL and HOTEL_MAIL_FROM.');
            return;
        }

        $resetUrl = $appUrl . '/index.php?action=reset_password&token=' . rawurlencode($token);
        $message = "Chúng tôi nhận được yêu cầu đặt lại mật khẩu cho tài khoản của bạn.\n\n"
            . "Mở liên kết sau trong vòng 30 phút để đặt mật khẩu mới:\n"
            . $resetUrl . "\n\n"
            . "Nếu bạn không yêu cầu, hãy bỏ qua email này.";
        $headers = "From: {$from}\r\n"
            . "Content-Type: text/plain; charset=UTF-8\r\n";

        if (!mail($email, 'Dat lai mat khau tai khoan', $message, $headers)) {
            error_log('PHP mail() failed while sending a password reset email.');
        }
    }
}
