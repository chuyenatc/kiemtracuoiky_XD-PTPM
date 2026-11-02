ALTER TABLE bookings
    ADD COLUMN payment_method ENUM('direct', 'online', 'unknown') NOT NULL DEFAULT 'unknown',
    ADD COLUMN deposit_required BIGINT UNSIGNED NOT NULL DEFAULT 0,
    ADD COLUMN paid_amount BIGINT UNSIGNED NOT NULL DEFAULT 0;

UPDATE bookings
SET deposit_required = CEIL(
    DATEDIFF(check_out, check_in) * nightly_rate * 0.20
)
WHERE check_in > CURDATE();
