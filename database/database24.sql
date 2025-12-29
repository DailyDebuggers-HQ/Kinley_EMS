CREATE DATABASE IF NOT EXISTS enrollmentSys24;

use enrollmentSys24;


CREATE TABLE students (
    id int auto_increment primary key,
    lastname varchar(30) not null,
    firstname varchar(30) not null,
    middlename varchar(30),
    age tinyint unsigned not null
);

CREATE TABLE curriculum (
    curID int auto_increment primary key,
    subjectCode varchar(8) not null unique,
    semester varchar(1) not null,
    yearlevel varchar(1) not null,
    subdescription varchar(50) not null,
    units tinyint unsigned not null
);

CREATE TABLE sub_enrolled (
    subEnID int auto_increment primary key,
    studProgID int not null,
    curID int not null,
    enrolled_date date,
    midterm varchar(4) default null,
    final varchar(4) default null,

    constraint fk_sub_enr_studProg foreign key (studProgID) references student_programs(studProgID),
    constraint fk_sub_enr_cur foreign key (curID) references curriculum(curID)
);

CREATE TABLE course (
    courseID int auto_increment primary key,
    courseName varchar(10) not null unique,
    courseDesc varchar(255) not null
);

CREATE TABLE student_programs (
    studProgID int auto_increment primary key,
    student_id int not null,
    courseID int not null,
    startDate date not null,
    end_date date default null,
    status enum('ACTIVE', 'TERMINATED', 'TRANSFERRED') not null,

    foreign key (student_id) references students(id),
    foreign key (courseID) references course(courseID)
);

CREATE TABLE course_curriculum (
    courCurID int auto_increment primary key,
    courseID int not null,
    curID int not null,

    foreign key (courseID) references course(courseID),
    foreign key (curID) references curriculum(curID)
);

INSERT INTO students (lastname, firstname, middlename, age) VALUES
('Lavina24', 'Jhon', 'Mcberry', 18),
('Reyes24', 'Junrick', NULL, 19),
('Cano24', 'Jay Patrick', 'Montreal', 20),
('Baltazar24', 'Leslie', NULL, 18),
('Villaranda24', 'Rainy', 'Amour', 21),
('Picorro24', 'Joana', NULL, 19),
('Ganolo24', 'Isaac', 'Santos', 20),
('Calucin24', 'Mark', NULL, 18),
('Lavina24', 'Evangeline', 'Cruz', 22),
('Android24', 'Benson', NULL, 19),
('Pascual24', 'Paolo', 'Fernandez', 21),
('Poe24', 'Fernando', NULL, 20),
('Poe24', 'Grace', 'Gomez', 18),
('Enriquez24', 'Mike', NULL, 19),
('Villaruel24', 'Claire', 'Castro', 22),
('Salamanca24', 'Bianca', NULL, 20),
('Chuandez24', 'Milky', 'Tan', 21),
('Velasco24', 'Patricia', NULL, 18),
('Egido24', 'Joshua', 'Manalo', 19),
('Estrada24', 'Joseph', NULL, 20),
('Henley24', 'Joseph', 'Cadayona', 25);

INSERT INTO curriculum (subjectCode, semester, yearlevel, subdescription, units) VALUES
('CS301', '1', '3', 'Information Assurance and Security', 3),
('CS302', '1', '3', 'Platform Technologies', 3),
('CS303', '1', '3', 'Discrete Structures 2', 3),
('CS304', '1', '3', 'Algorithms and Complexity', 3),
('CS305', '1', '3', 'Software Engineering 1', 3),
('CS306', '1', '3', 'Business Analytics', 3),
('CS307', '1', '3', 'Enterprise Data Management', 3),
('THEO3A', '1', '3', 'Christian Morality', 3),
('CS311', '2', '3', 'Analytics Tools and Techniques', 3),
('CS312', '2', '3', 'Analytics Modeling', 3),
('CS313', '2', '3', 'Social Issues and Professional Practice', 3),
('CS314', '2', '3', 'Operating Systems', 3),
('CS315', '2', '3', 'Software Engineering 2', 3),
('CS316', '2', '3', 'Programming Languages', 3),
('CSEL1', '2', '3', 'CS ELECTIVE 1', 3),
('THEO3B', '2', '3', 'The Commandments', 3),
('THESIS1', '0', '3', 'Thesis Writing 1', 3),
('THEO4A', '0', '3', 'Intro to Pastoral Life/BEC', 3),
('THESIS2', '1', '4', 'Thesis Writing 2', 3),
('CS401', '1', '4', 'Automata Theory and Formal Languages', 3),
('CS402', '1', '4', 'Analytics Application', 3),
('CS403', '1', '4', 'Professional Enhancement', 3),
('IT101', '1', '1', 'Intro to Computing', 3),
('IT102', '1', '1', 'Computer Programming 1', 3);

INSERT INTO course (courseName, courseDesc) VALUES
('CS', 'Bachelor of Science in Computer Science'),
('IT', 'Bachelor of Science in Information Technology'),
('MBA', 'Masters of Business Administration'),
('PHILO', 'Bachelor of Arts in Philosophy'),
('Polsci', 'Bachelor of Arts in Political Science'),
('BEED', 'Bachelor of Elementary Education'),
('BSA', 'Bachelor of Science in Accountancy'),
('CE', 'Bachelor of Science in Civil Engineering'),
('ARC', 'Bachelor of Science in Architecture'),
('CRIM', 'Bachelor of Science in Criminology'),
('HM', 'Bachelor of Science in Hospitality Management'),
('NUR', 'Bachelor of Science in Nursing'),
('OA', 'Bachelor of Science in Office Administration'),
('TM', 'Bachelor of Science in Tourism Management');

insert into course_curriculum (courseID, curID)
VALUES (1, 1),
(1,2),
(1,3),
(1,4),
(1,5),
(1,6),
(1,7),
(2,23),
(2,24);

INSERT INTO student_programs (student_id, courseID, startDate, status) VALUES
(10, 1, '2025-06-01', 'ACTIVE'),
(1, 1, '2025-06-01', 'ACTIVE');


INSERT into sub_enrolled (studProgID, curID, enrolled_date, midterm, final) VALUES
(1, 1, '2025-06-15', '1.00', '1.25'),
(1, 2, '2025-06-15', '2.25', '1.00'),
(1, 3, '2025-06-15', '1.00', '1.00'),
(2, 5, '2025-06-15', '1.00', '1.25'),
(2, 6, '2025-06-15', '1.00', '1.25'),
(2, 7, '2025-06-15', '1.00', '1.25'),
(2, 8, '2025-06-15', '1.00', '1.25');