USE gogetgro;

CREATE TABLE IF NOT EXISTS admin_accounts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(60) NOT NULL UNIQUE,
    display_name VARCHAR(120) NOT NULL,
    role ENUM('owner', 'staff') NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

DELETE FROM admin_accounts;

INSERT INTO admin_accounts (username, display_name, role, password_hash) VALUES
('owner', 'Store Owner', 'owner', '$2y$10$28U98p./P4nyo4oPy0.omOzXunIxq9qSl/hUCOuJmUGUT9oO1usEq'),
('staff', 'Store Staff', 'staff', '$2y$10$riNwHqkbsDqdXSHg2GuIWePYZ/KU8ly4RgtcXLP5lEIEJDPrgSOMe');
