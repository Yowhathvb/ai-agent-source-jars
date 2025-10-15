-- SQL untuk tabel produk AI Agent e-commerce
CREATE DATABASE IF NOT EXISTS product_catalog;
USE product_catalog;

CREATE TABLE IF NOT EXISTS products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sku VARCHAR(32) UNIQUE NOT NULL,
  name VARCHAR(128) NOT NULL,
  short_desc VARCHAR(255),
  long_desc TEXT,
  price INT NOT NULL,
  stock INT DEFAULT 0,
  category VARCHAR(64),
  image_url VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO products (sku, name, short_desc, long_desc, price, stock, category, image_url)
VALUES
('SKU001', 'Kaos Polos', 'Kaos polos bahan katun, nyaman dipakai', 'Kaos polos lengan pendek, bahan katun combed 30s, tersedia berbagai warna dan ukuran.', 50000, 100, 'Fashion', 'https://dummyimage.com/200x200/0ea5e9/fff&text=Kaos+Polos'),
('SKU002', 'Celana Jeans', 'Celana jeans biru slim fit', 'Celana jeans biru, model slim fit, bahan stretch nyaman dipakai.', 120000, 50, 'Fashion', 'https://dummyimage.com/200x200/222/fff&text=Jeans'),
('SKU003', 'Sepatu Sneakers', 'Sneakers casual untuk sehari-hari', 'Sepatu sneakers casual, sol karet anti slip, cocok untuk pria dan wanita.', 250000, 30, 'Sepatu', 'https://dummyimage.com/200x200/333/fff&text=Sneakers'),
('SKU004', 'Jaket Hoodie', 'Jaket hoodie tebal, cocok untuk musim hujan', 'Jaket hoodie bahan fleece, hangat dan nyaman, tersedia warna hitam dan abu.', 150000, 20, 'Fashion', 'https://dummyimage.com/200x200/444/fff&text=Hoodie'),
('SKU005', 'Topi Baseball', 'Topi gaya sporty, adjustable', 'Topi baseball model sporty, bahan katun, strap belakang bisa diatur.', 35000, 80, 'Aksesoris', 'https://dummyimage.com/200x200/555/fff&text=Topi')
ON DUPLICATE KEY UPDATE name=VALUES(name);
