CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `first_name` VARCHAR(50) NOT NULL,
  `last_name` VARCHAR(50) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('guest', 'customer', 'staff', 'admin') DEFAULT 'customer',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS `categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(100) NOT NULL UNIQUE
);

CREATE TABLE IF NOT EXISTS `products` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `category_id` INT NOT NULL,
  `name` VARCHAR(150) NOT NULL,
  `slug` VARCHAR(150) NOT NULL UNIQUE,
  `description` TEXT,
  `price` DECIMAL(10, 2) NOT NULL,
  `stock_quantity` INT DEFAULT 0,
  `image_url` VARCHAR(255) DEFAULT NULL,
  `is_digital` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`category_id`) REFERENCES categories(`id`) ON DELETE CASCADE
);

-- Seed Categories
INSERT IGNORE INTO `categories` (`id`, `name`, `slug`) VALUES
(1, 'Guitars', 'guitars'),
(2, 'Keyboards', 'keyboards'),
(3, 'Drums', 'drums'),
(4, 'Digital Lessons', 'digital-lessons');

-- Seed Products
INSERT IGNORE INTO `products` (`id`, `category_id`, `name`, `slug`, `description`, `price`, `stock_quantity`, `image_url`, `is_digital`) VALUES
(1, 1, 'Classic Acoustic Guitar', 'classic-acoustic-guitar', 'A beautifully crafted acoustic guitar suitable for beginners and pros.', 199.99, 15, 'https://placehold.co/400x400/3b82f6/white?text=Acoustic+Guitar', 0),
(2, 1, 'Electric Rocker Guitar', 'electric-rocker-guitar', 'High-quality electric guitar with humbucker pickups for that heavy sound.', 399.99, 5, 'https://placehold.co/400x400/3b82f6/white?text=Electric+Guitar', 0),
(3, 2, 'Professional Stage Piano', 'pro-stage-piano', '88-key weighted action digital piano. Perfect for live performances.', 850.00, 3, 'https://placehold.co/400x400/3b82f6/white?text=Stage+Piano', 0),
(4, 3, '5-Piece Drum Kit', '5-piece-drum-kit', 'Complete acoustic drum set including cymbals and hardware.', 650.00, 2, 'https://placehold.co/400x400/3b82f6/white?text=Drum+Kit', 0),
(5, 4, 'Masterclass: Piano Basics', 'masterclass-piano-basics', 'A complete video course detailing everything from posture to playing your first song.', 49.99, 9999, 'https://placehold.co/400x400/10b981/white?text=Piano+Course', 1);
