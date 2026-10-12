<?php
declare(strict_types=1);

class Model
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = new PDO(
            'mysql:host=localhost;dbname=hotel_mvc;charset=utf8mb4',
            'root',
            '',
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
                    NOT EXISTS (
                        SELECT 1 FROM bookings history WHERE history.room_id = r.id
                    ) AS can_delete
             FROM rooms r
             LEFT JOIN bookings b ON r.id = b.room_id AND b.status = 'Active'
             ORDER BY r.room_name"
        );
        return $stmt->fetchAll();
    }

    public function danhSachPhongKhach(): array
    {
        $stmt = $this->pdo->query(
            'SELECT id, room_name, status, nightly_rate FROM rooms ORDER BY room_name'
        );
        return $stmt->fetchAll();
    }

    public function themPhong(string $roomName, int $nightlyRate): bool
    {
        try {
            $stmt = $this->pdo->prepare(
                "INSERT INTO rooms (room_name, status, nightly_rate) VALUES (?, 'Trống', ?)"
            );
            $stmt->execute([$roomName, $nightlyRate]);
            return true;
        } catch (PDOException $exception) {
            if (isset($exception->errorInfo[1]) && $exception->errorInfo[1] === 1062) {
                return false;
            }
            throw $exception;
        }
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
        return $room === false ? null : $room;
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
        string $checkOut
    ): bool {
        if (trim($customerName) === ''
            || !$this->isValidDate($checkIn)
            || !$this->isValidDate($checkOut)
            || $checkOut <= $checkIn) {
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

            $room = $this->pdo->prepare('SELECT nightly_rate FROM rooms WHERE id = ?');
            $room->execute([$roomId]);
            $nightlyRate = $room->fetchColumn();
            if ($nightlyRate === false) {
                $this->pdo->rollBack();
                return false;
            }

            $insert = $this->pdo->prepare(
                'INSERT INTO bookings (room_id, customer_name, check_in, check_out, nightly_rate)
                 VALUES (?, ?, ?, ?, ?)'
            );
            $insert->execute([$roomId, $customerName, $checkIn, $checkOut, $nightlyRate]);
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
