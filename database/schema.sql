SET foreign_key_checks = 0;

DROP TABLE IF EXISTS pallet_movement_photos;
DROP TABLE IF EXISTS pallet_movements;
DROP TABLE IF EXISTS movements;
DROP TABLE IF EXISTS pallets;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    encrypted_password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'basic') NOT NULL DEFAULT 'basic',
    avatar_name VARCHAR(65),
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_users_email (email)
) ENGINE=InnoDB;

CREATE TABLE pallets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    item VARCHAR(100) NOT NULL,
    width DECIMAL(8,2) NOT NULL,
    length DECIMAL(8,2) NOT NULL,
    location VARCHAR(100) NOT NULL,
    amount INT NOT NULL DEFAULT 0,
    minimum_amount INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_pallets_format (item, width, length),
    CONSTRAINT ck_pallets_width CHECK (width > 0),
    CONSTRAINT ck_pallets_length CHECK (length > 0),
    CONSTRAINT ck_pallets_amount CHECK (amount >= 0),
) ENGINE=InnoDB;

CREATE TABLE movements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type ENUM('inbound', 'outbound', 'inventory') NOT NULL,
    user_id INT NOT NULL,
    observation VARCHAR(500),
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_movements_user_id (user_id),
    CONSTRAINT fk_movements_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE pallet_movements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    movement_id INT NOT NULL,
    pallet_id INT NOT NULL,
    quantity INT NOT NULL,
    quantity_before INT NOT NULL,
    quantity_after INT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_pallet_movements_movement_pallet (movement_id, pallet_id),
    INDEX idx_pallet_movements_pallet (pallet_id),
    CONSTRAINT fk_pallet_movements_movement
        FOREIGN KEY (movement_id)
        REFERENCES movements(id)
        ON DELETE RESTRICT,
    CONSTRAINT fk_pallet_movements_pallet
        FOREIGN KEY (pallet_id)
        REFERENCES pallets(id)
        ON DELETE RESTRICT,
    CONSTRAINT ck_pallet_movements_quantity CHECK (quantity >= 0),
    CONSTRAINT ck_pallet_movements_after CHECK (quantity_after >= 0)
) ENGINE=InnoDB;

CREATE TABLE pallet_movement_photos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pallet_movement_id INT NOT NULL,
    type ENUM('order', 'pallet') NOT NULL,
    url VARCHAR(255) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    mime_type VARCHAR(100) NOT NULL,
    size_bytes INT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_photos_pallet_movement (pallet_movement_id),
    CONSTRAINT fk_photos_pallet_movement
        FOREIGN KEY (pallet_movement_id)
        REFERENCES pallet_movements(id)
        ON DELETE CASCADE
) ENGINE=InnoDB;

SET foreign_key_checks = 1;
