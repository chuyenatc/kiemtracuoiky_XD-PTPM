<?php
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

    public function dangky(string $user, string $pass): bool
    {
        $existing = $this->pdo->prepare('SELECT id FROM users WHERE username = ?');
        $existing->execute([$user]);
        if ($existing->fetch()) {
            return false;
        }

        try {
            $stmt = $this->pdo->prepare(
                "INSERT INTO users (username, password, role) VALUES (?, ?, 'customer')"
            );
            $stmt->execute([$user, password_hash($pass, PASSWORD_DEFAULT)]);
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
            "SELECT r.*, b.id AS booking_id, b.customer_name, b.check_out
             FROM rooms r
             LEFT JOIN bookings b ON r.id = b.room_id AND b.status = 'Active'
             ORDER BY r.room_name"
        );
        return $stmt->fetchAll();
    }

    public function layPhong(int $id)
    {
        $stmt = $this->pdo->prepare('SELECT * FROM rooms WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function layLuotThue(int $id)
    {
        $stmt = $this->pdo->prepare(
            "SELECT b.*, r.room_name
             FROM bookings b
             INNER JOIN rooms r ON r.id = b.room_id
             WHERE b.id = ? AND b.status = 'Active'"
        );
        $stmt->execute([$id]);
        return $stmt->fetch();
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

            $insert = $this->pdo->prepare(
                'INSERT INTO bookings (room_id, customer_name, check_in, check_out)
                 VALUES (?, ?, ?, ?)'
            );
            $insert->execute([$roomId, $customerName, $checkIn, $checkOut]);
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

    private function isValidDate(string $date): bool
    {
        $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $date);
        return $parsed !== false && $parsed->format('Y-m-d') === $date;
    }
}
