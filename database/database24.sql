CREATE DATABASE IF NOT EXISTS enrollmentSystem24;

use enrollmentSystem24;


CREATE TABLE students (
    studentID varchar(8) not null primary key,
    firstname varchar(30) not null,
    lastname varchar(30) not null,
    middlename varchar(30) not null,
    email varchar(100) not null
);

CREATE TABLE programs (
    programCode varchar(10) not null primary key,
    programName varchar(8) not null,
    department varchar(30) not null
);

CREATE TABLE subjects (
    subjectCode varchar(10) not null primary key,
    subjectName varchar(50) not null,
    units int not null,
    programCode varchar(10) not null,

    foreign key (programCode) references programs(programCode)
);

CREATE TABLE enrollment (
    enrollID int auto_increment primary key,
    studentID varchar(8) not null,
    subjectCode varchar(10) not null,
    schoolYear varchar(15) not null,
    date_enrolled timestamp default current_timestamp,
    FOREIGN KEY (studentID) references students(studentID),
    FOREIGN KEY (subjectCode) references subjects(subjectCode)
);

CREATE TABLE enrolled_subs(
    enrolledsubsID int auto_increment primary key,
    enrollID int not null,
    subjectCode varchar(10) not null,

    foreign key (enrollID) references enrollment(enrollID),
    foreign key (subjectCode) references subjects(subjectCode)
);