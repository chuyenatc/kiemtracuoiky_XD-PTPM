<?php
declare(strict_types=1);

class Model
{
    private PDO $pdo;

    public function __construct()
    {
        $dbHost = getenv('DB_HOST') ?: 'localhost';
        $dbName = getenv('DB_NAME') ?: 'hotel_mvc';
        $dbUser = getenv('DB_USER') ?: 'root';
        $dbPassword = getenv('DB_PASSWORD');
        if ($dbPassword === false) {
            $dbPassword = '';
        }

        $this->pdo = new PDO(
            "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4",
            $dbUser,
            $dbPassword,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );
    }

    public function dangnhap(string $user, string $pass): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, username, password, role FROM users WHERE username = ?'
        );
        $stmt->execute([$user]);
        $account = $stmt->fetch();
        if (!$account) {
            return null;
        }

        $storedPassword = (string) $account['password'];
        $passwordInfo = password_get_info($storedPassword);
        if ($passwordInfo['algo'] !== null) {
            return password_verify($pass, $storedPassword)
                ? ['id' => $account['id'], 'username' => $account['username'], 'role' => $account['role']]
                : null;
        }

        if (!hash_equals($storedPassword, $pass)) {
            return null;
        }

        $update = $this->pdo->prepare('UPDATE users SET password = ? WHERE id = ?');
        $update->execute([password_hash($pass, PASSWORD_DEFAULT), $account['id']]);
        return ['id' => $account['id'], 'username' => $account['username'], 'role' => $account['role']];
    }

    public function dangky(string $user, string $pass, string $email): bool
    {
        $existing = $this->pdo->prepare('SELECT id FROM users WHERE username = ?');
        $existing->execute([$user]);
        if ($existing->fetch()) {
            return false;
        }

        try {
            $stmt = $this->pdo->prepare(
                "INSERT INTO users (username, password, email, role) VALUES (?, ?, ?, 'customer')"
            );
            $stmt->execute([$user, password_hash($pass, PASSWORD_DEFAULT), $email]);
            return true;
        } catch (PDOException $exception) {
            if (isset($exception->errorInfo[1]) && $exception->errorInfo[1] === 1062) {
                return false;
            }
            throw $exception;
        }
    }

    public function quanly(): array
    {
        $stmt = $this->pdo->query(
            "SELECT r.*, b.id AS booking_id, b.customer_name, b.check_out,
                    b.nightly_rate AS booking_nightly_rate,
                    DATEDIFF(b.check_out, b.check_in) * b.nightly_rate AS booking_total,
                    b.payment_method, b.deposit_required, b.paid_amount,
                    CASE WHEN b.payment_method = 'unknown' THEN NULL ELSE GREATEST(
                        CAST(DATEDIFF(b.check_out, b.check_in) * b.nightly_rate AS DECIMAL(30, 0))
                            - CAST(b.paid_amount AS DECIMAL(30, 0)),
                        0
                    ) END AS balance_due,
                    CASE
                        WHEN b.id IS NULL THEN NULL
                        WHEN b.payment_method = 'unknown' THEN 'Chưa đối soát'
                        WHEN b.paid_amount >= DATEDIFF(b.check_out, b.check_in) * b.nightly_rate
                            THEN 'Đã thanh toán'
                        WHEN b.deposit_required > 0 AND b.paid_amount >= b.deposit_required
                            THEN 'Đã đặt cọc'
                        WHEN b.paid_amount > 0 THEN 'Đã thu một phần'
                        ELSE 'Chưa thanh toán'
                    END AS payment_status,
                    NOT EXISTS (
                        SELECT 1 FROM bookings history WHERE history.room_id = r.id
                    ) AS can_delete
             FROM rooms r
             LEFT JOIN bookings b ON r.id = b.room_id AND b.status = 'Active'
             ORDER BY r.room_name"
        );
        return $this->ganAnhPhong($stmt->fetchAll());
    }

    public function danhSachPhongKhach(): array
    {
        $stmt = $this->pdo->query(
            'SELECT id, room_name, status, nightly_rate, image_path FROM rooms ORDER BY room_name'
        );
        return $this->ganAnhPhong($stmt->fetchAll());
    }

    public function themPhong(string $roomName, int $nightlyRate, array $imagePaths = []): bool
    {
        return $this->taoPhong($roomName, $nightlyRate, '', $imagePaths);
    }

    public function taoPhong(string $roomName, int $nightlyRate, string $description, array $imagePaths = []): bool
    {
        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare(
                "INSERT INTO rooms (room_name, status, nightly_rate, description) VALUES (?, 'Trống', ?, ?)"
            );
            $stmt->execute([$roomName, $nightlyRate, $description]);
            $roomId = (int) $this->pdo->lastInsertId();
            $this->insertRoomImages($roomId, $imagePaths);
            $this->pdo->commit();
            return true;
        } catch (PDOException $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            if (isset($exception->errorInfo[1]) && $exception->errorInfo[1] === 1062) {
                return false;
            }
            throw $exception;
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $exception;
        }
    }

    public function capNhatMoTaPhong(int $roomId, string $description): bool
    {
        $stmt = $this->pdo->prepare('UPDATE rooms SET description = ? WHERE id = ?');
        $stmt->execute([$description, $roomId]);
        if ($stmt->rowCount() === 1) {
            return true;
        }

        $exists = $this->pdo->prepare('SELECT id FROM rooms WHERE id = ?');
        $exists->execute([$roomId]);
        return $exists->fetchColumn() !== false;
    }

    public function themNhieuAnhPhong(int $roomId, array $imagePaths): bool
    {
        $this->pdo->beginTransaction();
        try {
            $room = $this->pdo->prepare('SELECT id FROM rooms WHERE id = ? FOR UPDATE');
            $room->execute([$roomId]);
            if ($room->fetchColumn() === false) {
                $this->pdo->rollBack();
                return false;
            }

            $this->insertRoomImages($roomId, $imagePaths);
            $this->pdo->commit();
            return true;
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $exception;
        }
    }

    public function xoaAnhPhong(int $imageId): ?string
    {
        $this->pdo->beginTransaction();
        try {
            $lookup = $this->pdo->prepare(
                'SELECT image_path FROM room_images WHERE id = ? FOR UPDATE'
            );
            $lookup->execute([$imageId]);
            $imagePath = $lookup->fetchColumn();
            if ($imagePath === false) {
                $this->pdo->rollBack();
                return null;
            }

            $delete = $this->pdo->prepare('DELETE FROM room_images WHERE id = ?');
            $delete->execute([$imageId]);
            $this->pdo->commit();
            return (string) $imagePath;
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $exception;
        }
    }

    public function baoCaoLuotThueTheoThang(string $monthStart, string $nextMonthStart): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT b.id, b.customer_name, b.check_in, b.check_out, b.nightly_rate, b.status,
                    b.payment_method, b.deposit_required, b.paid_amount,
                    r.room_name,
                    DATEDIFF(b.check_out, b.check_in) * b.nightly_rate AS booking_total,
                    CASE WHEN b.payment_method = 'unknown' THEN NULL ELSE GREATEST(
                        CAST(DATEDIFF(b.check_out, b.check_in) * b.nightly_rate AS DECIMAL(30, 0))
                            - CAST(b.paid_amount AS DECIMAL(30, 0)),
                        0
                    ) END AS balance_due,
                    CASE
                        WHEN b.payment_method = 'unknown' THEN 'Chưa đối soát'
                        WHEN b.paid_amount >= DATEDIFF(b.check_out, b.check_in) * b.nightly_rate
                            THEN 'Đã thanh toán'
                        WHEN b.deposit_required > 0 AND b.paid_amount >= b.deposit_required
                            THEN 'Đã đặt cọc'
                        WHEN b.paid_amount > 0 THEN 'Đã thu một phần'
                        ELSE 'Chưa thanh toán'
                    END AS payment_status,
                    GREATEST(
                        DATEDIFF(LEAST(b.check_out, ?), GREATEST(b.check_in, ?)),
                        0
                    ) AS nights_in_month
             FROM bookings b
             INNER JOIN rooms r ON r.id = b.room_id
             WHERE b.check_in < ? AND b.check_out > ?
             ORDER BY b.check_in DESC, b.id DESC"
        );
        $stmt->execute([$nextMonthStart, $monthStart, $nextMonthStart, $monthStart]);
        return $stmt->fetchAll();
    }

    public function capNhatGiaPhong(int $roomId, int $nightlyRate): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE rooms SET nightly_rate = ? WHERE id = ?'
        );
        $stmt->execute([$nightlyRate, $roomId]);
        if ($stmt->rowCount() === 1) {
            return true;
        }

        $exists = $this->pdo->prepare('SELECT id FROM rooms WHERE id = ?');
        $exists->execute([$roomId]);
        return $exists->fetchColumn() !== false;
    }

    public function xoaPhong(int $roomId): bool
    {
        $this->pdo->beginTransaction();
        try {
            $room = $this->pdo->prepare(
                'SELECT status FROM rooms WHERE id = ? FOR UPDATE'
            );
            $room->execute([$roomId]);
            $record = $room->fetch();
            if (!$record || $record['status'] !== 'Trống') {
                $this->pdo->rollBack();
                return false;
            }

            $history = $this->pdo->prepare(
                'SELECT id FROM bookings WHERE room_id = ? LIMIT 1'
            );
            $history->execute([$roomId]);
            if ($history->fetch()) {
                $this->pdo->rollBack();
                return false;
            }

            $delete = $this->pdo->prepare('DELETE FROM rooms WHERE id = ?');
            $delete->execute([$roomId]);
            if ($delete->rowCount() !== 1) {
                $this->pdo->rollBack();
                return false;
            }

            $this->pdo->commit();
            return true;
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $exception;
        }
    }

    public function taoTaiKhoanAdmin(string $username, string $password, string $email): bool
    {
        try {
            $stmt = $this->pdo->prepare(
                "INSERT INTO users (username, password, email, role) VALUES (?, ?, ?, 'admin')"
            );
            $stmt->execute([$username, password_hash($password, PASSWORD_DEFAULT), $email]);
            return true;
        } catch (PDOException $exception) {
            if (isset($exception->errorInfo[1]) && $exception->errorInfo[1] === 1062) {
                return false;
            }
            throw $exception;
        }
    }

    public function danhSachAdmin(): array
    {
        $stmt = $this->pdo->query(
            "SELECT username, email FROM users WHERE role = 'admin' ORDER BY username"
        );
        return $stmt->fetchAll();
    }

    public function layPhong(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM rooms WHERE id = ?');
        $stmt->execute([$id]);
        $room = $stmt->fetch();
        if ($room === false) {
            return null;
        }

        return $this->ganAnhPhong([$room])[0];
    }

    private function ganAnhPhong(array $rooms): array
    {
        if (!$rooms) {
            return [];
        }

        $roomIds = array_map(static fn (array $room): int => (int) $room['id'], $rooms);
        $placeholders = implode(',', array_fill(0, count($roomIds), '?'));
        $stmt = $this->pdo->prepare(
            "SELECT id, room_id, image_path FROM room_images
             WHERE room_id IN ({$placeholders})
             ORDER BY room_id, id"
        );
        $stmt->execute($roomIds);

        $imagesByRoom = [];
        foreach ($stmt->fetchAll() as $image) {
            $imagesByRoom[(int) $image['room_id']][] = $image;
        }

        foreach ($rooms as &$room) {
            $roomId = (int) $room['id'];
            $room['images'] = $imagesByRoom[$roomId] ?? [];
            $legacyPath = $room['image_path'] ?? null;
            if (is_string($legacyPath)
                && $legacyPath !== ''
                && !in_array($legacyPath, array_column($room['images'], 'image_path'), true)) {
                array_unshift($room['images'], [
                    'id' => null,
                    'room_id' => $roomId,
                    'image_path' => $legacyPath,
                ]);
            }
        }
        unset($room);

        return $rooms;
    }

    private function insertRoomImages(int $roomId, array $imagePaths): void
    {
        if (!$imagePaths) {
            return;
        }

        $stmt = $this->pdo->prepare(
            'INSERT INTO room_images (room_id, image_path) VALUES (?, ?)'
        );
        foreach ($imagePaths as $imagePath) {
            $stmt->execute([$roomId, $imagePath]);
        }
    }

    public function layLuotThue(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            "SELECT b.*, r.room_name
             FROM bookings b
             INNER JOIN rooms r ON r.id = b.room_id
             WHERE b.id = ? AND b.status = 'Active'"
        );
        $stmt->execute([$id]);
        $booking = $stmt->fetch();
        return $booking === false ? null : $booking;
    }

    public function datphong(
        int $roomId,
        string $customerName,
        string $checkIn,
        string $checkOut,
        string $paymentMethod
    ): bool {
        if (trim($customerName) === ''
            || !$this->isValidDate($checkIn)
            || !$this->isValidDate($checkOut)
            || $checkOut <= $checkIn
            || !in_array($paymentMethod, ['direct', 'online'], true)) {
            return false;
        }

        $this->pdo->beginTransaction();
        try {
            $update = $this->pdo->prepare(
                "UPDATE rooms SET status = 'Đang thuê' WHERE id = ? AND status = 'Trống'"
            );
            $update->execute([$roomId]);
            if ($update->rowCount() !== 1) {
                $this->pdo->rollBack();
                return false;
            }

            $room = $this->pdo->prepare('SELECT nightly_rate, CURDATE() AS today FROM rooms WHERE id = ?');
            $room->execute([$roomId]);
            $roomData = $room->fetch();
            if (!$roomData) {
                $this->pdo->rollBack();
                return false;
            }

            $nights = (int) (new DateTimeImmutable($checkIn))->diff(new DateTimeImmutable($checkOut))->days;
            $nightlyRate = (int) $roomData['nightly_rate'];
            $totalAmount = $nights * $nightlyRate;
            $depositRequired = $checkIn > $roomData['today']
                ? intdiv($totalAmount, 5) + ($totalAmount % 5 > 0 ? 1 : 0)
                : 0;
            $insert = $this->pdo->prepare(
                'INSERT INTO bookings (
                    room_id, customer_name, check_in, check_out, nightly_rate,
                    payment_method, deposit_required, paid_amount
                 ) VALUES (?, ?, ?, ?, ?, ?, ?, 0)'
            );
            $insert->execute([
                $roomId,
                $customerName,
                $checkIn,
                $checkOut,
                $nightlyRate,
                $paymentMethod,
                $depositRequired,
            ]);
            $this->pdo->commit();
            return true;
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $exception;
        }
    }

    public function danhSachThanhToan(): array
    {
        $stmt = $this->pdo->query(
            "SELECT b.id, b.customer_name, b.check_in, b.check_out, b.nightly_rate,
                    b.payment_method, b.deposit_required, b.paid_amount, b.status,
                    r.room_name,
                    DATEDIFF(b.check_out, b.check_in) * b.nightly_rate AS total_amount,
                    CASE WHEN b.payment_method = 'unknown' THEN NULL ELSE GREATEST(
                        CAST(DATEDIFF(b.check_out, b.check_in) * b.nightly_rate AS DECIMAL(30, 0))
                            - CAST(b.paid_amount AS DECIMAL(30, 0)),
                        0
                    ) END AS balance_due,
                    CASE
                        WHEN b.payment_method = 'unknown' THEN 'Chưa đối soát'
                        WHEN b.paid_amount >= DATEDIFF(b.check_out, b.check_in) * b.nightly_rate
                            THEN 'Đã thanh toán'
                        WHEN b.deposit_required > 0 AND b.paid_amount >= b.deposit_required
                            THEN 'Đã đặt cọc'
                        WHEN b.paid_amount > 0 THEN 'Đã thu một phần'
                        ELSE 'Chưa thanh toán'
                    END AS payment_status
             FROM bookings b
             INNER JOIN rooms r ON r.id = b.room_id
             ORDER BY b.check_in DESC, b.id DESC"
        );
        return $stmt->fetchAll();
    }

    public function ghiNhanThanhToan(
        int $bookingId,
        int $amount,
        ?string $reconciledMethod = null
    ): bool
    {
        if ($amount < 0
            || ($reconciledMethod !== null
                && !in_array($reconciledMethod, ['direct', 'online'], true))) {
            return false;
        }

        $this->pdo->beginTransaction();
        try {
            $lookup = $this->pdo->prepare(
                'SELECT nightly_rate, check_in, check_out, paid_amount, payment_method
                 FROM bookings WHERE id = ? FOR UPDATE'
            );
            $lookup->execute([$bookingId]);
            $booking = $lookup->fetch();
            if (!$booking) {
                $this->pdo->rollBack();
                return false;
            }

            $isUnreconciled = $booking['payment_method'] === 'unknown';
            if (($isUnreconciled && $reconciledMethod === null)
                || (!$isUnreconciled && ($reconciledMethod !== null || $amount < 1))) {
                $this->pdo->rollBack();
                return false;
            }

            $totalAmount = (int) $booking['nightly_rate']
                * (int) (new DateTimeImmutable($booking['check_in']))
                    ->diff(new DateTimeImmutable($booking['check_out']))->days;
            $paidAmount = (int) $booking['paid_amount'];
            if ($amount > $totalAmount - $paidAmount) {
                $this->pdo->rollBack();
                return false;
            }

            if ($isUnreconciled) {
                $update = $this->pdo->prepare(
                    'UPDATE bookings SET payment_method = ?, paid_amount = paid_amount + ?
                     WHERE id = ?'
                );
                $update->execute([$reconciledMethod, $amount, $bookingId]);
            } else {
                $update = $this->pdo->prepare(
                    'UPDATE bookings SET paid_amount = paid_amount + ? WHERE id = ?'
                );
                $update->execute([$amount, $bookingId]);
            }
            $this->pdo->commit();
            return true;
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $exception;
        }
    }

    public function traphong(int $roomId, int $bookingId): bool
    {
        $this->pdo->beginTransaction();
        try {
            $updateBooking = $this->pdo->prepare(
                "UPDATE bookings SET status = 'Completed'
                 WHERE id = ? AND room_id = ? AND status = 'Active'"
            );
            $updateBooking->execute([$bookingId, $roomId]);
            if ($updateBooking->rowCount() !== 1) {
                $this->pdo->rollBack();
                return false;
            }

            $updateRoom = $this->pdo->prepare(
                "UPDATE rooms SET status = 'Trống'
                 WHERE id = ? AND status = 'Đang thuê'"
            );
            $updateRoom->execute([$roomId]);
            if ($updateRoom->rowCount() !== 1) {
                $this->pdo->rollBack();
                return false;
            }

            $this->pdo->commit();
            return true;
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $exception;
        }
    }

    public function giahan(int $bookingId, string $newCheckout): bool
    {
        if (!$this->isValidDate($newCheckout)) {
            return false;
        }

        $stmt = $this->pdo->prepare(
            "UPDATE bookings SET check_out = ?
             WHERE id = ? AND status = 'Active' AND check_out < ?"
        );
        $stmt->execute([$newCheckout, $bookingId, $newCheckout]);
        return $stmt->rowCount() === 1;
    }

    public function taoYeuCauDatLaiMatKhau(string $email): ?string
    {
        $stmt = $this->pdo->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $userId = $stmt->fetchColumn();
        if ($userId === false) {
            return null;
        }

        $token = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $token);
        $update = $this->pdo->prepare(
            'UPDATE users
             SET reset_token_hash = ?, reset_expires_at = DATE_ADD(NOW(), INTERVAL 30 MINUTE)
             WHERE id = ?'
        );
        $update->execute([$tokenHash, $userId]);
        return $token;
    }

    public function datLaiMatKhau(string $token, string $password): bool
    {
        $this->pdo->beginTransaction();
        try {
            $lookup = $this->pdo->prepare(
                'SELECT id FROM users
                 WHERE reset_token_hash = ? AND reset_expires_at > NOW()
                 LIMIT 1 FOR UPDATE'
            );
            $lookup->execute([hash('sha256', $token)]);
            $userId = $lookup->fetchColumn();
            if ($userId === false) {
                $this->pdo->rollBack();
                return false;
            }

            $update = $this->pdo->prepare(
                'UPDATE users
                 SET password = ?, reset_token_hash = NULL, reset_expires_at = NULL
                 WHERE id = ?'
            );
            $update->execute([password_hash($password, PASSWORD_DEFAULT), $userId]);
            $this->pdo->commit();
            return true;
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $exception;
        }
    }

    private function isValidDate(string $date): bool
    {
        $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $date);
        return $parsed !== false && $parsed->format('Y-m-d') === $date;
    }
}
