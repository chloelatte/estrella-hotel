-- ============================================================
-- Estrella Hotel & Resort — Database Export
-- MySQL 8.0+ | utf8mb4 | Untuk XAMPP/Laragon
-- Memenuhi syarat Basis Data: 6 tabel, 2 VIEW, 2 FUNCTION, 2 TRIGGER
-- ============================================================

CREATE DATABASE IF NOT EXISTS estrella_hotel CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE estrella_hotel;

SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- TABLE 1: users
-- ============================================================
CREATE TABLE IF NOT EXISTS users (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    full_name  VARCHAR(100) NOT NULL,
    email      VARCHAR(100) NOT NULL UNIQUE,
    username   VARCHAR(50)  NOT NULL UNIQUE,
    password   VARCHAR(255) NOT NULL,
    phone      VARCHAR(20),
    role       ENUM('admin','guest') DEFAULT 'guest',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- TABLE 2: rooms
-- ============================================================
CREATE TABLE IF NOT EXISTS rooms (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    name           VARCHAR(100) NOT NULL,
    type           ENUM('standard','deluxe','executive','presidential') NOT NULL DEFAULT 'standard',
    description    TEXT,
    price_per_night DECIMAL(15,2) NOT NULL,
    capacity       INT NOT NULL DEFAULT 2,
    size_sqm       INT,
    view_type      VARCHAR(80),
    bed_type       VARCHAR(80),
    is_available   TINYINT(1) DEFAULT 1,
    image_url      VARCHAR(255),
    created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- TABLE 3: amenities
-- ============================================================
CREATE TABLE IF NOT EXISTS amenities (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(100) NOT NULL,
    icon       VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- TABLE 4: room_amenities (relasi M:N)
-- ============================================================
CREATE TABLE IF NOT EXISTS room_amenities (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    room_id     INT NOT NULL,
    amenity_id  INT NOT NULL,
    UNIQUE KEY uq_room_amenity (room_id, amenity_id),
    FOREIGN KEY (room_id)    REFERENCES rooms(id)     ON DELETE CASCADE,
    FOREIGN KEY (amenity_id) REFERENCES amenities(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- TABLE 5: reservations
-- ============================================================
CREATE TABLE IF NOT EXISTS reservations (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    booking_code   VARCHAR(30)  NOT NULL UNIQUE,
    user_id        INT,
    room_id        INT NOT NULL,
    guest_name     VARCHAR(100) NOT NULL,
    guest_email    VARCHAR(100) NOT NULL,
    guest_phone    VARCHAR(20)  NOT NULL,
    check_in       DATE NOT NULL,
    check_out      DATE NOT NULL,
    guests         INT NOT NULL DEFAULT 2,
    rooms_count    INT NOT NULL DEFAULT 1,
    room_price     DECIMAL(15,2) NOT NULL,
    addon_total    DECIMAL(15,2) DEFAULT 0,
    service_charge DECIMAL(15,2) DEFAULT 0,
    tax            DECIMAL(15,2) DEFAULT 0,
    total_price    DECIMAL(15,2) NOT NULL,
    special_request TEXT,
    payment_method VARCHAR(50),
    payment_status ENUM('pending','paid','cancelled') DEFAULT 'pending',
    status         ENUM('pending','confirmed','cancelled','completed') DEFAULT 'pending',
    addons_json    TEXT,
    created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (room_id) REFERENCES rooms(id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================================
-- TABLE 6: reservation_logs (untuk trigger)
-- ============================================================
CREATE TABLE IF NOT EXISTS reservation_logs (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    reservation_id INT NOT NULL,
    action         VARCHAR(20) NOT NULL,
    old_status     VARCHAR(30),
    new_status     VARCHAR(30),
    logged_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (reservation_id) REFERENCES reservations(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- VIEW 1: v_reservation_summary
-- Query kompleks #1: JOIN 3 tabel (reservations + rooms + users)
-- ============================================================
CREATE OR REPLACE VIEW v_reservation_summary AS
    SELECT
        r.id,
        r.booking_code,
        r.check_in,
        r.check_out,
        DATEDIFF(r.check_out, r.check_in) AS nights,
        r.guests,
        r.total_price,
        r.status,
        r.payment_status,
        r.payment_method,
        r.created_at,
        rm.name    AS room_name,
        rm.type    AS room_type,
        rm.price_per_night,
        u.full_name AS guest_name,
        u.email     AS guest_email
    FROM reservations r
    JOIN rooms rm ON r.room_id = rm.id
    LEFT JOIN users u ON r.user_id = u.id;

-- ============================================================
-- VIEW 2: v_room_availability
-- Query kompleks #2: LEFT JOIN + subquery / aggregation
-- ============================================================
CREATE OR REPLACE VIEW v_room_availability AS
    SELECT
        rm.id,
        rm.name,
        rm.type,
        rm.price_per_night,
        rm.capacity,
        rm.is_available,
        (
            SELECT COUNT(*)
            FROM reservations res
            WHERE res.room_id = rm.id
              AND res.status NOT IN ('cancelled')
              AND res.check_in <= CURDATE()
              AND res.check_out > CURDATE()
        ) AS active_bookings_today,
        (
            SELECT COUNT(*)
            FROM reservations res
            WHERE res.room_id = rm.id
        ) AS total_bookings_ever
    FROM rooms rm;

-- ============================================================
-- FUNCTION 1: fn_calculate_total
-- Query kompleks #3: digunakan sebagai callable function
-- ============================================================
DELIMITER $$

CREATE FUNCTION IF NOT EXISTS fn_calculate_total(
    p_room_price  DECIMAL(15,2),
    p_nights      INT,
    p_rooms       INT,
    p_addon_total DECIMAL(15,2)
) RETURNS DECIMAL(15,2)
DETERMINISTIC
BEGIN
    DECLARE v_room_total  DECIMAL(15,2);
    DECLARE v_service     DECIMAL(15,2);
    DECLARE v_tax         DECIMAL(15,2);
    DECLARE v_grand_total DECIMAL(15,2);

    SET v_room_total  = p_room_price * p_nights * p_rooms;
    SET v_service     = v_room_total * 0.10;
    SET v_tax         = v_room_total * 0.10;
    SET v_grand_total = v_room_total + v_service + v_tax + p_addon_total;

    RETURN v_grand_total;
END $$

-- ============================================================
-- FUNCTION 2: fn_generate_booking_code
-- ============================================================
CREATE FUNCTION IF NOT EXISTS fn_generate_booking_code(
    p_date DATE
) RETURNS VARCHAR(30)
DETERMINISTIC
BEGIN
    DECLARE v_code VARCHAR(30);
    SET v_code = CONCAT(
        'EST-',
        DATE_FORMAT(p_date, '%y%m%d'),
        '-',
        LPAD(FLOOR(RAND() * 9999 + 1), 4, '0')
    );
    RETURN v_code;
END $$

-- ============================================================
-- TRIGGER 1: trg_after_reservation_insert
-- ============================================================
CREATE TRIGGER IF NOT EXISTS trg_after_reservation_insert
    AFTER INSERT ON reservations
    FOR EACH ROW
BEGIN
    INSERT INTO reservation_logs (reservation_id, action, new_status)
    VALUES (NEW.id, 'INSERT', NEW.status);
END $$

-- ============================================================
-- TRIGGER 2: trg_after_reservation_update
-- ============================================================
CREATE TRIGGER IF NOT EXISTS trg_after_reservation_update
    AFTER UPDATE ON reservations
    FOR EACH ROW
BEGIN
    IF OLD.status <> NEW.status THEN
        INSERT INTO reservation_logs (reservation_id, action, old_status, new_status)
        VALUES (NEW.id, 'STATUS_CHANGE', OLD.status, NEW.status);
    END IF;
END $$

DELIMITER ;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- SAMPLE DATA
-- ============================================================

-- Admin & sample users (password: admin123 / guest123)
INSERT INTO users (full_name, email, username, password, phone, role) VALUES
('Administrator', 'admin@estrella.com', 'admin',
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- admin123
 '+62 21 6017 8120', 'admin'),
('Sophie Laurent', 'sophie@example.com', 'guest1',
 '$2y$10$TKh8H1.PpuAjmi.h6/M8.uRlFp0wJuHmZqGp3nIsBVh6OvC5FEjUG', -- guest123
 '+62 812 3456 7890', 'guest'),
('Marco Bellini', 'marco@example.com', 'guest2',
 '$2y$10$TKh8H1.PpuAjmi.h6/M8.uRlFp0wJuHmZqGp3nIsBVh6OvC5FEjUG',
 '+62 821 9876 5432', 'guest'),
('Isabelle Dubois', 'isabelle@example.com', 'guest3',
 '$2y$10$TKh8H1.PpuAjmi.h6/M8.uRlFp0wJuHmZqGp3nIsBVh6OvC5FEjUG',
 '+62 813 5555 1234', 'guest'),
('Alexandre Moreau', 'alex@example.com', 'guest4',
 '$2y$10$TKh8H1.PpuAjmi.h6/M8.uRlFp0wJuHmZqGp3nIsBVh6OvC5FEjUG',
 '+62 878 1111 2222', 'guest');

-- Rooms
INSERT INTO rooms (name, type, description, price_per_night, capacity, size_sqm, view_type, bed_type, is_available, image_url) VALUES
('Standard Room',
 'standard',
 'A cozy and comfortable room with modern amenities for a relaxing stay. Features elegant coastal décor and all the essentials for a pleasant overnight experience.',
 1200000, 2, 24, 'Garden View', '1 Queen Bed', 1,
 'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=700&q=80'),

('Deluxe Room',
 'deluxe',
 'Experience refined comfort in our Deluxe Room, featuring elegant interiors, modern amenities, and a private balcony with stunning ocean views. Perfect for couples.',
 1800000, 2, 32, 'Ocean View', '1 King Bed / 2 Twin Beds', 1,
 'https://images.unsplash.com/photo-1618773928121-c32242e63f39?w=700&q=80'),

('Executive Room',
 'executive',
 'Enjoy extra space, a private seating area, and premium ocean-view facilities. Perfect for business travelers and couples seeking a luxurious retreat.',
 2500000, 3, 40, 'Premium Ocean View', '1 King Bed', 1,
 'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?w=700&q=80'),

('Presidential Room',
 'presidential',
 'The pinnacle of luxury with expansive space, exclusive services, and breathtaking panoramic ocean views. Unmatched elegance for the most discerning guests.',
 5000000, 4, 60, 'Panoramic Ocean View', '1 King Bed + Living Area', 1,
 'https://images.unsplash.com/photo-1578683010236-d716f9a3f461?w=700&q=80');

-- Amenities
INSERT INTO amenities (name, icon) VALUES
('Free Wi-Fi',         'bi-wifi'),
('Air Conditioning',   'bi-thermometer-half'),
('Ocean View',         'bi-water'),
('Smart TV',           'bi-tv'),
('Minibar',            'bi-cup-straw'),
('Private Balcony',    'bi-door-open'),
('Room Service',       'bi-bell'),
('Safety Box',         'bi-shield-lock'),
('Bath Amenities',     'bi-droplet'),
('Hair Dryer',         'bi-wind'),
('Bathrobe & Slippers','bi-star'),
('Daily Housekeeping', 'bi-house-heart'),
('Coffee & Tea Maker', 'bi-cup-hot'),
('Workspace Desk',     'bi-briefcase');

-- Room amenities (each room gets a set)
INSERT INTO room_amenities (room_id, amenity_id) VALUES
-- Standard Room (id=1): Wi-Fi, AC, Smart TV, Room Service, Bath, Housekeeping, Coffee
(1,1),(1,2),(1,4),(1,7),(1,9),(1,12),(1,13),
-- Deluxe Room (id=2): Wi-Fi, AC, Ocean View, Smart TV, Minibar, Balcony, Room Service, Safety, Bath, Hair Dryer, Housekeeping, Coffee
(2,1),(2,2),(2,3),(2,4),(2,5),(2,6),(2,7),(2,8),(2,9),(2,10),(2,12),(2,13),
-- Executive Room (id=3): all above + Bathrobe, Workspace
(3,1),(3,2),(3,3),(3,4),(3,5),(3,6),(3,7),(3,8),(3,9),(3,10),(3,11),(3,12),(3,13),(3,14),
-- Presidential Room (id=4): all amenities
(4,1),(4,2),(4,3),(4,4),(4,5),(4,6),(4,7),(4,8),(4,9),(4,10),(4,11),(4,12),(4,13),(4,14);

-- Sample reservations (5+ records)
INSERT INTO reservations (booking_code, user_id, room_id, guest_name, guest_email, guest_phone,
    check_in, check_out, guests, rooms_count, room_price, addon_total, service_charge, tax,
    total_price, payment_method, payment_status, status)
VALUES
('EST-250601-0001', 2, 2, 'Sophie Laurent', 'sophie@example.com', '+62 812 3456 7890',
 '2025-06-01','2025-06-04', 2, 1, 1800000, 900000, 540000, 540000, 6780000,
 'credit_card','paid','completed'),

('EST-250610-0042', 3, 3, 'Marco Bellini', 'marco@example.com', '+62 821 9876 5432',
 '2025-06-10','2025-06-13', 2, 1, 2500000, 0, 750000, 750000, 9000000,
 'bank_transfer','paid','completed'),

('EST-250615-0017', 4, 1, 'Isabelle Dubois', 'isabelle@example.com', '+62 813 5555 1234',
 '2025-06-15','2025-06-17', 1, 1, 1200000, 300000, 240000, 240000, 2980000,
 'ewallet','paid','confirmed'),

('EST-260620-0088', 5, 4, 'Alexandre Moreau', 'alex@example.com', '+62 878 1111 2222',
 '2026-06-20','2026-06-25', 4, 1, 5000000, 1500000, 1500000, 1500000, 29500000,
 'credit_card','pending','pending'),

('EST-260625-0099', 2, 2, 'Sophie Laurent', 'sophie@example.com', '+62 812 3456 7890',
 '2026-06-25','2026-06-28', 2, 1, 1800000, 650000, 540000, 540000, 7330000,
 'credit_card','pending','confirmed'),

('EST-260701-0111', 3, 1, 'Marco Bellini', 'marco@example.com', '+62 821 9876 5432',
 '2026-07-01','2026-07-03', 2, 1, 1200000, 0, 240000, 240000, 2880000,
 'pay_hotel','pending','pending');

-- Sample reservation logs (populated by triggers on future inserts)
INSERT INTO reservation_logs (reservation_id, action, new_status) VALUES
(1,'INSERT','pending'),
(2,'INSERT','pending'),
(3,'INSERT','pending'),
(4,'INSERT','pending'),
(5,'INSERT','pending'),
(6,'INSERT','pending');

INSERT INTO reservation_logs (reservation_id, action, old_status, new_status) VALUES
(1,'STATUS_CHANGE','pending','confirmed'),
(1,'STATUS_CHANGE','confirmed','completed'),
(2,'STATUS_CHANGE','pending','confirmed'),
(2,'STATUS_CHANGE','confirmed','completed'),
(3,'STATUS_CHANGE','pending','confirmed'),
(5,'STATUS_CHANGE','pending','confirmed');

-- ============================================================
-- COMPLEX QUERIES untuk dokumentasi / demo
-- ============================================================

-- Query Kompleks #1: JOIN 3 tabel — Laporan reservasi lengkap
-- SELECT r.booking_code, u.full_name, rm.name AS room_name, rm.type,
--        r.check_in, r.check_out, DATEDIFF(r.check_out,r.check_in) AS nights,
--        r.total_price, r.status
-- FROM reservations r
-- JOIN rooms rm ON r.room_id = rm.id
-- LEFT JOIN users u ON r.user_id = u.id
-- ORDER BY r.created_at DESC;

-- Query Kompleks #2: Subquery — Kamar paling sering dipesan
-- SELECT rm.name, rm.type, rm.price_per_night,
--        (SELECT COUNT(*) FROM reservations WHERE room_id = rm.id AND status != 'cancelled') AS booking_count
-- FROM rooms rm
-- ORDER BY booking_count DESC;

-- Query Kompleks #3: GROUP BY + HAVING — Pendapatan per tipe kamar
-- SELECT rm.type,
--        COUNT(r.id) AS total_reservations,
--        SUM(r.total_price) AS total_revenue,
--        AVG(r.total_price) AS avg_revenue
-- FROM reservations r
-- JOIN rooms rm ON r.room_id = rm.id
-- WHERE r.status != 'cancelled'
-- GROUP BY rm.type
-- HAVING total_reservations > 0
-- ORDER BY total_revenue DESC;
