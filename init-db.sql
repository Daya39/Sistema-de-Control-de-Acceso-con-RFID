-- init-db.sql: Script para inicializar la base de datos rfid_access

CREATE DATABASE IF NOT EXISTS rfid_access;
USE rfid_access;

-- Tabla de usuarios autorizados
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  uid VARCHAR(32) NOT NULL UNIQUE,
  name VARCHAR(100) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de logs de accesos
CREATE TABLE IF NOT EXISTS logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  uid VARCHAR(32) NOT NULL,
  access_status ENUM('GRANTED', 'DENIED') NOT NULL,
  timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Usuario de ejemplo
INSERT IGNORE INTO users (uid, name) 
VALUES ('A1B2C3D4', 'Tarjeta Demo');