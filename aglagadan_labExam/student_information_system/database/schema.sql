-- First create/select your hosting database in the control panel, then import this file.

CREATE TABLE IF NOT EXISTS users (
  user_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(80) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('admin') NOT NULL DEFAULT 'admin',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS departments (
  department_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  department_code VARCHAR(30) NOT NULL UNIQUE,
  department_name VARCHAR(160) NOT NULL,
  description TEXT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS courses (
  course_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  department_id INT UNSIGNED NOT NULL,
  course_code VARCHAR(30) NOT NULL UNIQUE,
  course_name VARCHAR(180) NOT NULL,
  duration_years TINYINT UNSIGNED NOT NULL DEFAULT 4,
  description TEXT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_courses_department FOREIGN KEY (department_id) REFERENCES departments(department_id)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS students (
  student_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  student_number VARCHAR(40) NOT NULL UNIQUE,
  first_name VARCHAR(100) NOT NULL,
  middle_name VARCHAR(100) NULL,
  last_name VARCHAR(100) NOT NULL,
  sex ENUM('Male','Female','Prefer not to say') NOT NULL,
  birth_date DATE NULL,
  email VARCHAR(190) NULL,
  phone VARCHAR(40) NULL,
  address TEXT NULL,
  course_id INT UNSIGNED NOT NULL,
  enrollment_date DATE NOT NULL,
  status ENUM('Active','Inactive','Graduated','Dropped') NOT NULL DEFAULT 'Active',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_students_course FOREIGN KEY (course_id) REFERENCES courses(course_id)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  INDEX idx_students_name (last_name, first_name),
  INDEX idx_students_status (status)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS academics (
  academic_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  student_id INT UNSIGNED NOT NULL,
  school_year VARCHAR(20) NOT NULL,
  semester ENUM('1st','2nd','Summer') NOT NULL,
  year_level TINYINT UNSIGNED NOT NULL,
  academic_status ENUM('Enrolled','Passed','Failed','Incomplete','On Leave') NOT NULL DEFAULT 'Enrolled',
  remarks TEXT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_academics_student FOREIGN KEY (student_id) REFERENCES students(student_id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  INDEX idx_academics_student_year (student_id, school_year)
) ENGINE=InnoDB;
