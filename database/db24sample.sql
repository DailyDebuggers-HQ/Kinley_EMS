CREATE DATABASE IF NOT EXISTS enrollmentSys24samp;

use enrollmentSys24samp;


CREATE TABLE students (
    id int auto_increment primary key,
    lastname varchar(30) not null,
    firstname varchar(30) not null,
    middlename varchar(30),
    age tinyint unsigned not null
);

CREATE TABLE curriculum (
    curID int auto_increment primary key,
    subjectCode varchar(20) not null unique,
    semester varchar(1) not null,
    yearlevel varchar(1) not null,
    subdescription varchar(50) not null,
    units tinyint unsigned not null
);

CREATE TABLE course (
    courseID int auto_increment primary key,
    courseName varchar(10) not null unique,
    courseDesc varchar(255) not null
);

CREATE TABLE schedule (
    schedID INT AUTO_INCREMENT PRIMARY KEY,
    curID INT NOT NULL,
    day ENUM('MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT') NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    room VARCHAR(20) DEFAULT NULL,
    section VARCHAR(10) DEFAULT NULL,

    CONSTRAINT fk_schedule_curriculum
        FOREIGN KEY (curID)
        REFERENCES curriculum(curID)
        ON DELETE CASCADE,

    CONSTRAINT uq_schedule UNIQUE (curID, section, day, start_time, end_time),

    constraint chk_time CHECK (end_time > start_time)
);


CREATE TABLE student_programs (
    studProgID int auto_increment primary key,
    student_id int not null unique,
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
    foreign key (curID) references curriculum(curID),

    constraint unique_course_curriculum unique (courseID, curID)
);

CREATE TABLE academic_years (
    acadYearID int auto_increment primary key,
    academicYear varchar(9) not null unique
);

CREATE TABLE student_enrollments (
    enrollmentID int auto_increment primary key,
    studProgID int not null,
    acadYearID int not null,
    semester tinyint not null,
    enrollment_date date default current_date,
    status enum ('COMPLETED', 'ONGOING') NOT NULL DEFAULT 'ONGOING',

    constraint fk_stud_enr_studProg foreign key (studProgID) references student_programs(studProgID),
    constraint fk_acadYear foreign key(acadYearID) references academic_years(acadYearID),
    unique (studProgID, acadYearID, semester)
);

CREATE TABLE grades (
    gradeID int auto_increment primary key,
    enrollmentID int not null,
    courCurID int not null,
    midterm varchar(4) default null,
    final varchar(4) default null,

    constraint fk_grades_enrollment foreign key (enrollmentID) references student_enrollments(enrollmentID),
    constraint fk_grades_curriculum foreign key (courCurID) references course_curriculum(courCurID),
    constraint uq_enrollment_sub unique (enrollmentID, courCurID)
);