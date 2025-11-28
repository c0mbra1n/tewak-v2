CREATE DATABASE IF NOT EXISTS mogu;
USE mogu;

-- Users table (Teachers, Admin Piket, Admin Kelas)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('super_admin', 'admin_kelas', 'guru') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Classes table
CREATE TABLE IF NOT EXISTS classes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    class_name VARCHAR(50) NOT NULL UNIQUE,
    qr_code VARCHAR(255) NOT NULL UNIQUE, -- The string content of the QR code
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Attendance/Monitoring table
CREATE TABLE IF NOT EXISTS attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    class_id INT NOT NULL,
    scan_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('hadir', 'telat', 'izin', 'sakit', 'alpa') DEFAULT 'hadir',
    date DATE NOT NULL, -- To easily query per day
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (class_id) REFERENCES classes(id)
);

-- Insert default Super Admin
-- Password is 'admin123' (hashed)
INSERT INTO users (username, password, full_name, role) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Super Admin', 'super_admin')
ON DUPLICATE KEY UPDATE id=id;

-- Insert some dummy classes
INSERT INTO classes (class_name, qr_code) VALUES 
('X IPA 1', 'CLASS_X_IPA_1'),
('X IPA 2', 'CLASS_X_IPA_2'),
('XI IPS 1', 'CLASS_XI_IPS_1')
ON DUPLICATE KEY UPDATE id=id;

-- Insert some dummy teachers
-- Password is 'guru123'
INSERT INTO users (username, password, full_name, role) VALUES 
('guru1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Budi Santoso', 'guru'),
('guru2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Siti Aminah', 'guru')
ON DUPLICATE KEY UPDATE id=id;
