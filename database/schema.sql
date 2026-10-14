-- Ejecutar en una base seleccionada. No contiene DROP, ni datos privados.
CREATE TABLE admins (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 email VARCHAR(190) NOT NULL UNIQUE,
 password_hash VARCHAR(255) NOT NULL,
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE categories (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 name VARCHAR(60) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE news (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 title VARCHAR(160) NOT NULL,
 summary VARCHAR(300) NOT NULL,
 content MEDIUMTEXT NOT NULL,
 category_id INT UNSIGNED NOT NULL,
 image VARCHAR(80) NULL,
 image_alt VARCHAR(180) NOT NULL DEFAULT '',
 status ENUM('draft','published') NOT NULL DEFAULT 'draft',
 published_at DATETIME NULL,
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT fk_news_category FOREIGN KEY(category_id) REFERENCES categories(id) ON DELETE RESTRICT,
 INDEX idx_public(status,published_at,id), INDEX idx_category(category_id,status,published_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
