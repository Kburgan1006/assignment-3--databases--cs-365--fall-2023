--Sets up database and tables
DROP DATABASE IF EXISTS student_passwords;

CREATE DATABASE student_passwords;

DROP USER IF EXISTS 'passwords_user'@'localhost';

CREATE USER 'passwords_user'@'localhost' IDENTIFIED BY 'uecn!wo93jdn&dk';
GRANT ALL ON student_passwords.* TO 'passwords_user'@'localhost';

USE student_passwords;

DROP TABLE IF EXISTS registraion;
DROP TABLE IF EXISTS passwords;

--Creates Tables
CREATE TABLE registration (
    Registration_id  INT(5) AUTO_INCREMENT,
    Comment TEXT,
    Creation_Time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY(Registration_id)
);

CREATE TABLE passwords (
    Password_id  INT(5) NOT NULL PRIMARY KEY AUTO_INCREMENT,
    Website_Name VARCHAR(50) NOT NULL,
    Website_URL VARCHAR(2083),
    User_Name VARCHAR(20) NOT NULL,
    Email_Address VARCHAR(320),
    Password_ VARBINARY(512) NOT NULL,
    Registration_id  INT(5) NOT NULL,
    FOREIGN KEY (Registration_id)
        REFERENCES registration (Registration_id)
        ON DELETE CASCADE
);
