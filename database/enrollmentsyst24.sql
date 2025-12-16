CREATE DATABASE IF NOT EXISTS enrollmentsyst24;

use enrollmentsyst24;


CREATE TABLE students (
    id int auto_increment primary key,
    studentID varchar(8) not null unique,
    lastname varchar(30) not null,
    firstname varchar(30) not null,
    middlename varchar(30),
    age tinyint unsigned not null
);

INSERT INTO students (studentID, lastname, firstname, middlename, age) VALUES
('80000001', 'Lavina', 'Jhon', 'Mcberry', 18),
('80000002', 'Reyes', 'Junrick', NULL, 19),
('80000003', 'Cano', 'Jay Patrick', 'Montreal', 20),
('80000004', 'Baltazar', 'Leslie', NULL, 18),
('80000005', 'Villaranda', 'Rainy', 'Amour', 21),
('80000006', 'Picorro', 'Joana', NULL, 19),
('80000007', 'Ganolo', 'Isaac', 'Santos', 20),
('80000008', 'Calucin', 'Mark', NULL, 18),
('80000009', 'Lavina', 'Evangeline', 'Cruz', 22),
('80000010', 'Android', 'Benson', NULL, 19),
('80000011', 'Pascual', 'Paolo', 'Fernandez', 21),
('80000012', 'Poe', 'Fernando', NULL, 20),
('80000013', 'Poe', 'Grace', 'Gomez', 18),
('80000014', 'Enriquez', 'Mike', NULL, 19),
('80000015', 'Villaruel', 'CLaire', 'Castro', 22),
('80000016', 'Salamanca', 'Bianca', NULL, 20),
('80000017', 'Chuandez', 'Milky', 'Tan', 21),
('80000018', 'Velasco', 'Patricia', NULL, 18),
('80000019', 'Egido', 'Joshua', 'Manalo', 19),
('80000020', 'Estrada', 'Joseph', NULL, 20);
