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
    enrolled_date date default (current_date),
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
    startDate date not null default (current_date),
    end_date date default null,
    status enum('ACTIVE', 'TERMINATED', 'TRANSFERRED') not null default 'ACTIVE',

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

INSERT INTO curriculum (subjectCode, semester, yearlevel, subdescription, units) VALUES
('CS101', '1', '1', 'Introduction to Computing', 3),
('CS102', '1', '1', 'Computer Programming 1', 3),
('GE1', '1', '1', 'Understanding the Self', 3),
('GE2', '1', '1', 'Readings in Phil History', 3),
('GE3', '1', '1', 'Mathematics in the Modern World', 3),
('GEEL1', '1', '1', 'Living in the IT Era', 3),
('THEO1A', '1', '1', 'Old Testament', 2),
('PATHFIT1', '1', '1', 'Physical Activities Towards Health & Fitness 1', 2),
('NSTP11', '1', '1', 'National Service Training Program', 3),
('CS111', '2', '1', 'Networks and Communications 1', 3),
('CS112', '2', '1', 'Computer Programming 2', 3),   
('CS113', '2', '1', 'Discrete Structures 1', 3),
('GE4', '2', '1', 'Purposive Communication', 3),
('GE5', '2', '1', 'Science, Technology, and Society', 3),
('GE6', '2', '1', 'The Contemporary World', 3),
('GEEL2', '2', '1', 'Gender and Society', 3),
('THEO1B', '2', '1', 'New Testament', 2),
('PATHFIT2', '2', '1', 'Physical Activities Towards Health & Fitness 2', 2),
('NSTP12', '2', '1', 'National Service Training Program 2', 3);

insert into course_curriculum (courseID, curID)
VALUES (1, 25),
(1,26),
(1,27),
(1,28),
(1,29),
(1,30),
(1,31),
(1,32),
(1,33),
(1,34),
(1,35),
(1,36),
(1,37),
(1,38),
(1,39),
(1,40),
(1,41),
(1,42),
(1,43);

INSERT INTO curriculum (subjectCode, semester, yearlevel, subdescription, units) VALUES
('CS201', '1', '2', 'Information Management', 3),
('CS202', '1', '2', 'Data Structures and Algorithms', 3),
('CS203', '1', '2', 'Web Systems and Technologies', 3),
('STAT1A', '1', '2', 'Probability and Statistics', 3),
('GE7', '1', '2', 'Art Appreciation', 3),
('GE8', '1', '2', 'Ethics', 3),
('GEEL3D', '1', '2', 'The Entrepreneurial Mind', 3),
('THEO2A', '1', '2', 'Christology', 2),
('PATHFIT3', '1', '2', 'Physical Activities Towards Health & Fitness 3', 2),
('CS211', '2', '2', 'Object-Oriented Programming', 3),
('CS212', '2', '2', 'Human Computer Interaction', 3),
('CS213', '2', '2', 'Application Dev and Emerging Tech', 3),
('CS214', '2', '2', 'Information Management 2', 3),
('CS215', '2', '2', 'System Analysis and Design', 3),
('GE9', '2', '2', 'Life and Works of Rizal', 3),
('COURSEEN1', '2', '2', 'Course Enhancement 1', 3),
('THEO2B', '2', '2', 'Mariology', 2),
('PATHFIT4', '2', '2', 'Physical Activities Towards Health & Fitness 4', 2);

insert into course_curriculum (courseID, curID)
VALUES (1, 44),
(1,45),
(1,46),
(1,47),
(1,48),
(1,49),
(1,50),
(1,51),
(1,52),
(1,53),
(1,54),
(1,55),
(1,56),
(1,57),
(1,58),
(1,59),
(1,60),
(1,61);

insert into course_curriculum (courseID, curID)
VALUES (1, 19),
(1,20),
(1,21),
(1,22);

INSERT into sub_enrolled (studProgID, curID, enrolled_date, midterm, final) VALUES
(1, 25, '2025-06-15', '1.00', '1.25'),
(1, 26, '2025-06-15', '2.25', '1.00'),
(1, 27, '2025-06-15', '1.00', '1.00'),
(1, 28, '2025-06-15', '1.00', '1.25'),
(1, 29, '2025-06-15', '1.00', '1.25'),
(1, 30, '2025-06-15', '1.00', '1.25'),
(1, 31, '2025-06-15', '1.00', '1.25'),
(1, 32, '2025-06-15', '1.00', '1.25'),
(1, 33, '2025-06-15', '1.00', '1.25'),
(1, 34, '2025-06-15', '1.00', '1.25'),
(1, 35, '2025-06-15', '1.00', '1.25'),
(1, 36, '2025-06-15', '1.00', '1.25'),
(1, 37, '2025-06-15', '1.00', '1.25'),
(1, 38, '2025-06-15', '1.00', '1.25'),
(1, 39, '2025-06-15', '1.00', '1.25'),
(1, 40, '2025-06-15', '1.00', '1.25'),
(1, 41, '2025-06-15', '1.00', '1.25'),
(1, 42, '2025-06-15', '1.00', '1.25'),
(1, 43, '2025-06-15', '1.00', '1.25'),
(1, 44, '2025-06-15', '1.00', '1.25'),
(1, 45, '2025-06-15', '1.00', '1.25'),
(1, 46, '2025-06-15', '1.00', '1.25'),
(1, 47, '2025-06-15', '1.00', '1.25'),
(1, 48, '2025-06-15', '1.00', '1.25'),
(1, 49, '2025-06-15', '1.00', '1.25'),
(1, 50, '2025-06-15', '1.00', '1.25'),
(1, 51, '2025-06-15', '1.00', '1.25'),
(1, 52, '2025-06-15', '1.00', '1.25'),
(1, 53, '2025-06-15', '1.00', '1.25'),
(1, 54, '2025-06-15', '1.00', '1.25'),
(1, 55, '2025-06-15', '1.00', '1.25'),
(1, 56, '2025-06-15', '1.00', '1.25'),
(1, 57, '2025-06-15', '1.00', '1.25'),
(1, 58, '2025-06-15', '1.00', '1.25'),
(1, 59, '2025-06-15', '1.00', '1.25'),
(1, 60, '2025-06-15', '1.00', '1.25'),
(1, 61, '2025-06-15', '1.00', '1.25');