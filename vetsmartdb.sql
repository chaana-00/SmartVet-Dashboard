CREATE DATABASE vetsmartdb;
USE vetsmartdb,

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  fullname VARCHAR(100),
  designation VARCHAR(100),
  company VARCHAR(100),
  telephone VARCHAR(20),
  username VARCHAR(50) UNIQUE,
  password VARCHAR(255),
  role VARCHAR(20) DEFAULT 'user'
);

CREATE TABLE farms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    
    farm_name VARCHAR(150) NOT NULL,
    farm_id VARCHAR(100) NOT NULL,            -- Stored as text because PHP binds it as string
    farm_location VARCHAR(255),

    farm_contact1 VARCHAR(50),
    farm_contact2 VARCHAR(50),
    farm_contact3 VARCHAR(50),

    owner_name VARCHAR(150),
    owner_contact1 VARCHAR(50),
    owner_contact2 VARCHAR(50),

    farm_type VARCHAR(100),
    
    farm_capacity INT,                         -- PHP binds this as integer
    note TEXT,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE batch_details (
    id INT AUTO_INCREMENT PRIMARY KEY,
    farm_id INT NOT NULL,
    farm_location VARCHAR(255) NOT NULL,
    cage_no VARCHAR(100) NOT NULL,
    breed VARCHAR(100) NOT NULL,
    hatchery VARCHAR(100) NOT NULL,
    total_input INT NOT NULL,
    vaccination_details TEXT,
    treatment_history TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (farm_id) REFERENCES farms(id)
);



