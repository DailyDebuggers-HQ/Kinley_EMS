CREATE DATABASE IF NOT EXISTS enrollmentSys24samp;

use enrollmentSys24samp;


CREATE TABLE students (
    studentID int auto_increment primary key,
    lastname varchar(30) not null,
    firstname varchar(30) not null,
    middlename varchar(30),
    birthdate date not null
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

CREATE TABLE academic_years (
    acadYearID int auto_increment primary key,
    academicYear varchar(9) not null unique
);


CREATE TABLE student_programs (
    studProgID int auto_increment primary key,
    student_id int not null unique,
    courseID int not null,
    startDate date not null default (current_date),
    end_date date default null,
    status enum('ACTIVE', 'TERMINATED', 'TRANSFERRED') not null default 'ACTIVE',

    foreign key (student_id) references students(studentID),
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

CREATE TABLE student_enrollments (
    enrollmentID int auto_increment primary key,
    studEnrollID int not null,
    acadYearID int not null,
    semester tinyint not null,
    enrollment_date date default current_date,
    status enum ('COMPLETED', 'ONGOING') NOT NULL DEFAULT 'ONGOING',

    constraint fk_stud_enr_studProg foreign key (studEnrollID) references student_programs(studProgID),
    constraint fk_acadYear foreign key(acadYearID) references academic_years(acadYearID),
    unique (studEnrollID, acadYearID, semester)
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

CREATE TABLE schedule (
    schedID INT AUTO_INCREMENT PRIMARY KEY,
    courCurID INT NOT NULL,
    acadYearID int not null,
    semester TINYINT NOT NULL,
    day ENUM('MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT') NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    room VARCHAR(20) DEFAULT NULL,
    section VARCHAR(10) DEFAULT NULL,

    CONSTRAINT fk_schedule_CourseCurriculum
        FOREIGN KEY (courCurID)
        REFERENCES course_curriculum(courCurID)
        ON DELETE CASCADE,

    CONSTRAINT fk_schedule_acadYear
        FOREIGN KEY (acadYearID)
        REFERENCES academic_years(acadYearID),

    CONSTRAINT uq_schedule UNIQUE (courCurID, acadYearID, semester, section, day, start_time, end_time),

    constraint chk_time CHECK (end_time > start_time)
);

CREATE TABLE student_schedule (
    studSchedID int auto_increment primary key,
    enrollmentID int not null,
    schedID int not null,

    constraint fk_studSched_enrollment foreign key (enrollmentID) references student_enrollments(enrollmentID),
    constraint fk_studSched_schedule foreign key (schedID) references schedule(schedID),
    constraint uq_enrollment_schedule unique (enrollmentID, schedID)
);

CREATE TABLE subject_fees (
    subFeesID int auto_increment primary key,
    courCurID int not null,
    acadYearID int not null,
    semester tinyint not null,
    price decimal(10,2) not null,

    constraint fk_fee_courseCur foreign key (courCurID) references course_curriculum(courCurID),
    constraint fk_fee_acadYear foreign key (acadYearID) references academic_years(acadYearID),

    constraint uq_subFee unique (courCurID, acadYearID, semester)
);

CREATE TABLE student_assessment (
    studAssessID int auto_increment primary key,
    enrollmentID int not null,
    totalAmount decimal(10,2) not null,
    assessedDate date not null default (current_date),

    constraint fk_studAssess_enrollment foreign key (enrollmentID) references student_enrollments(enrollmentID),
    constraint uq_enrollment_assessment unique (enrollmentID)
);

create table payments (
    paymentID int auto_increment primary key,
    enrollmentID int not null,
    paymentDate date not null default (current_date),
    amountPaid decimal(10,2) not null,

    constraint fk_payment_enrollment foreign key (enrollmentID) references student_enrollments(enrollmentID)
);