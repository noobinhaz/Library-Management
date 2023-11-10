
CREATE DATABASE IF NOT EXISTS library_db;
USE library_db;

CREATE TABLE IF NOT EXISTS authors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    dob DATE
);

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('librarian', 'student') NOT NULL
);

CREATE TABLE IF NOT EXISTS books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    version VARCHAR(50),
    author_id INT,
    isbn_code VARCHAR(190) NOT NULL,
    sbn_code VARCHAR(190) NOT NULL,
    release_date DATE,
    shelf_position VARCHAR(20)
);

CREATE TABLE IF NOT EXISTS book_borrows (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    book_id INT,
    borrow_date DATE DEFAULT NOW(),
    return_date DATE DEFAULT NULL
);

INSERT INTO authors (name, dob) VALUES
    ('J.K. Rowling', '1965-07-31'),
    ('George Orwell', '1903-06-25'),
    ('Harper Lee', '1926-04-28'),
    ('Agatha Christie', '1890-09-15'),
    ('Stephen King', '1947-09-21');

INSERT INTO users (name, email, password, role) VALUES
    ('Librarian 1', 'librarian1@example.com', '$2y$10$ZNiaa3B7vc0b8gW3bLuxAu6Q.IcgB/5FP24tREHdP8PIKJ8DsfZyC', 'librarian'),
    ('Librarian 2', 'librarian2@example.com', '$2y$10$ZNiaa3B7vc0b8gW3bLuxAu6Q.IcgB/5FP24tREHdP8PIKJ8DsfZyC', 'librarian'),
    ('Student 1', 'student1@example.com', '$2y$10$ZNiaa3B7vc0b8gW3bLuxAu6Q.IcgB/5FP24tREHdP8PIKJ8DsfZyC', 'student'),
    ('Student 2', 'student2@example.com', '$2y$10$ZNiaa3B7vc0b8gW3bLuxAu6Q.IcgB/5FP24tREHdP8PIKJ8DsfZyC', 'student');

INSERT INTO books (name, version, author_id, isbn_code, sbn_code, release_date, shelf_position) VALUES
    ('Harry Potter and the Sorcerer''s Stone', '1st Edition', 1, '9780590353427', '123-456-789-0', '1997-06-26', 'A1'),
    ('1984', '1st Edition', 2, '9780451524935', '987-654-321-0', '1949-06-08', 'B2'),
    ('To Kill a Mockingbird', '1st Edition', 3, '9780061120084', '555-555-555-5', '1960-07-11', 'C3'),
    ('Murder on the Orient Express', '1st Edition', 4, '9780062693662', '987-654-321-0', '1934-01-01', 'D4'),
    ('The Shining', '1st Edition', 5, '9780385121675', '123-456-789-0', '1977-01-28', 'E5');

INSERT INTO book_borrows (user_id, book_id, borrow_date, return_date) VALUES
    (3, 1, '2022-02-01', '2022-02-15'),
    (4, 2, '2022-03-10', '2022-03-25'),
    (3, 3, '2022-04-05', NULL); -- Currently borrowed
