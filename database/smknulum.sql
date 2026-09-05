-- ============================================================================
--  SISTEM AKADEMIK SMK NURUL ULUM
--  Database dump: MySQL 8.x  (charset: utf8mb4 / collation: utf8mb4_unicode_ci)
--
--  File ini berisi SCHEMA LENGKAP (40 tabel: 33 tabel domain + 6 tabel sistem
--  Laravel + tabel migrations) + DATA AWAL (seeder) sistem.
--  Struktur dihasilkan dari seluruh migration Laravel di database/migrations/,
--  dan data awal disesuaikan dengan DatabaseSeeder (Roles, Admin, AcademicData).
--
--  CARA IMPORT (dari terminal):
--      mysql -u USERNAME -p < database/smknulum.sql
--  atau lewat phpMyAdmin / MySQL Workbench (Import).
--
--  CATATAN:
--  * File ini MENG-HAPUS database `smknulum` bila sudah ada, lalu membuat
--    ulang dari nol (DROP DATABASE ... CREATE DATABASE ...). Jangan dijalankan
--    pada server produksi yang berisi data penting.
--  * Kolom timestamp sengaja memakai tipe DATETIME (aman, tanpa zona waktu).
--  * Password semua user dummy: 'password' (bcrypt, cost 12) — segera ganti
--    di produksi sesuai SECURITY.md.
--  * Tabel `migrations` sudah diisi agar `php artisan migrate:status`
--    menampilkan semua migration sebagai sudah dijalankan.
-- ============================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;
SET TIME_ZONE = '+07:00';
SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';

-- ----------------------------------------------------------------------------
-- 0. DATABASE
-- ----------------------------------------------------------------------------
DROP DATABASE IF EXISTS `smknulum`;
CREATE DATABASE IF NOT EXISTS `smknulum`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `smknulum`;

-- ============================================================================
-- 1. TABEL SISTEM LARAVEL (cache, queue, session)
-- ============================================================================

DROP TABLE IF EXISTS `cache`;
CREATE TABLE `cache` (
  `key`        VARCHAR(255) NOT NULL,
  `value`      MEDIUMTEXT   NOT NULL,
  `expiration` BIGINT       NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `cache_locks`;
CREATE TABLE `cache_locks` (
  `key`        VARCHAR(255) NOT NULL,
  `owner`      VARCHAR(255) NOT NULL,
  `expiration` BIGINT       NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `jobs`;
CREATE TABLE `jobs` (
  `id`           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `queue`        VARCHAR(255)    NOT NULL,
  `payload`      LONGTEXT        NOT NULL,
  `attempts`     SMALLINT UNSIGNED NOT NULL,
  `reserved_at`  INT UNSIGNED    NULL,
  `available_at` INT UNSIGNED    NOT NULL,
  `created_at`   INT UNSIGNED    NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `job_batches`;
CREATE TABLE `job_batches` (
  `id`             VARCHAR(255) NOT NULL,
  `name`           VARCHAR(255) NOT NULL,
  `total_jobs`     INT          NOT NULL,
  `pending_jobs`   INT          NOT NULL,
  `failed_jobs`    INT          NOT NULL,
  `failed_job_ids` LONGTEXT     NOT NULL,
  `options`        MEDIUMTEXT   NULL,
  `cancelled_at`   INT          NULL,
  `created_at`     INT          NOT NULL,
  `finished_at`    INT          NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `failed_jobs`;
CREATE TABLE `failed_jobs` (
  `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid`       VARCHAR(255)    NOT NULL,
  `connection` VARCHAR(255)    NOT NULL,
  `queue`      VARCHAR(255)    NOT NULL,
  `payload`    LONGTEXT        NOT NULL,
  `exception`  LONGTEXT        NOT NULL,
  `failed_at`  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`),
  KEY `failed_jobs_connection_queue_failed_at_index` (`connection`, `queue`, `failed_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `sessions`;
CREATE TABLE `sessions` (
  `id`            VARCHAR(255) NOT NULL,
  `user_id`       BIGINT UNSIGNED NULL,
  `ip_address`    VARCHAR(45)     NULL,
  `user_agent`    TEXT            NULL,
  `payload`       LONGTEXT        NOT NULL,
  `last_activity` INT             NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 2. MASTER PENGGUNA & PERAN
-- ============================================================================

DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
  `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`       VARCHAR(255)    NOT NULL,
  `label`      VARCHAR(255)    NOT NULL,
  `created_at` DATETIME        NULL,
  `updated_at` DATETIME        NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id`                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`              VARCHAR(255)    NOT NULL,
  `email`             VARCHAR(255)    NOT NULL,
  `email_verified_at` DATETIME        NULL,
  `password`          VARCHAR(255)    NOT NULL,
  `role_id`           BIGINT UNSIGNED NOT NULL,
  `avatar`            VARCHAR(255)    NULL,
  `phone`             VARCHAR(255)    NULL,
  `address`           TEXT            NULL,
  `is_active`         TINYINT(1)      NOT NULL DEFAULT 1,
  `remember_token`    VARCHAR(100)    NULL,
  `created_at`        DATETIME        NULL,
  `updated_at`        DATETIME        NULL,
  `deleted_at`        DATETIME        NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_role_id_index` (`role_id`),
  CONSTRAINT `users_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 3. MASTER AKADEMIK
-- ============================================================================

DROP TABLE IF EXISTS `academic_years`;
CREATE TABLE `academic_years` (
  `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`       VARCHAR(255)    NOT NULL,
  `start_date` DATE            NOT NULL,
  `end_date`   DATE            NOT NULL,
  `is_active`  TINYINT(1)      NOT NULL DEFAULT 0,
  `created_at` DATETIME        NULL,
  `updated_at` DATETIME        NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `semesters`;
CREATE TABLE `semesters` (
  `id`               BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `academic_year_id` BIGINT UNSIGNED NOT NULL,
  `name`             ENUM('ganjil','genap') NOT NULL,
  `start_date`       DATE            NOT NULL,
  `end_date`         DATE            NOT NULL,
  `is_active`        TINYINT(1)      NOT NULL DEFAULT 0,
  `created_at`       DATETIME        NULL,
  `updated_at`       DATETIME        NULL,
  PRIMARY KEY (`id`),
  KEY `semesters_academic_year_id_index` (`academic_year_id`),
  CONSTRAINT `semesters_academic_year_id_foreign` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `majors`;
CREATE TABLE `majors` (
  `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`        VARCHAR(255)    NOT NULL,
  `code`        VARCHAR(255)    NOT NULL,
  `description` TEXT            NULL,
  `is_active`   TINYINT(1)      NOT NULL DEFAULT 1,
  `created_at`  DATETIME        NULL,
  `updated_at`  DATETIME        NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `majors_code_unique` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `competencies`;
CREATE TABLE `competencies` (
  `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `major_id`   BIGINT UNSIGNED NOT NULL,
  `name`       VARCHAR(255)    NOT NULL,
  `code`       VARCHAR(255)    NOT NULL,
  `description` TEXT           NULL,
  `created_at` DATETIME        NULL,
  `updated_at` DATETIME        NULL,
  PRIMARY KEY (`id`),
  KEY `competencies_major_id_index` (`major_id`),
  CONSTRAINT `competencies_major_id_foreign` FOREIGN KEY (`major_id`) REFERENCES `majors` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `rooms`;
CREATE TABLE `rooms` (
  `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`       VARCHAR(255)    NOT NULL,
  `code`       VARCHAR(255)    NOT NULL,
  `capacity`   INT             NOT NULL,
  `building`   VARCHAR(255)    NULL,
  `floor`      INT             NULL,
  `is_active`  TINYINT(1)      NOT NULL DEFAULT 1,
  `created_at` DATETIME        NULL,
  `updated_at` DATETIME        NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `rooms_code_unique` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `subjects`;
CREATE TABLE `subjects` (
  `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`        VARCHAR(255)    NOT NULL,
  `code`        VARCHAR(255)    NOT NULL,
  `major_id`    BIGINT UNSIGNED NULL,
  `description` TEXT            NULL,
  `is_active`   TINYINT(1)      NOT NULL DEFAULT 1,
  `created_at`  DATETIME        NULL,
  `updated_at`  DATETIME        NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `subjects_code_unique` (`code`),
  KEY `subjects_major_id_index` (`major_id`),
  CONSTRAINT `subjects_major_id_foreign` FOREIGN KEY (`major_id`) REFERENCES `majors` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `classes`;
CREATE TABLE `classes` (
  `id`               BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`             VARCHAR(255)    NOT NULL,
  `major_id`         BIGINT UNSIGNED NOT NULL,
  `competency_id`    BIGINT UNSIGNED NULL,
  `academic_year_id` BIGINT UNSIGNED NOT NULL,
  `semester_id`      BIGINT UNSIGNED NOT NULL,
  `capacity`         INT             NOT NULL DEFAULT 36,
  `is_active`        TINYINT(1)      NOT NULL DEFAULT 1,
  `created_at`       DATETIME        NULL,
  `updated_at`       DATETIME        NULL,
  PRIMARY KEY (`id`),
  KEY `classes_major_id_index` (`major_id`),
  KEY `classes_competency_id_index` (`competency_id`),
  KEY `classes_academic_year_id_index` (`academic_year_id`),
  KEY `classes_semester_id_index` (`semester_id`),
  CONSTRAINT `classes_major_id_foreign` FOREIGN KEY (`major_id`) REFERENCES `majors` (`id`) ON DELETE CASCADE,
  CONSTRAINT `classes_competency_id_foreign` FOREIGN KEY (`competency_id`) REFERENCES `competencies` (`id`) ON DELETE SET NULL,
  CONSTRAINT `classes_academic_year_id_foreign` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`) ON DELETE CASCADE,
  CONSTRAINT `classes_semester_id_foreign` FOREIGN KEY (`semester_id`) REFERENCES `semesters` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `parents`;
CREATE TABLE `parents` (
  `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`    BIGINT UNSIGNED NOT NULL,
  `occupation` VARCHAR(255)    NULL,
  `income`     DECIMAL(15,2)   NULL,
  `created_at` DATETIME        NULL,
  `updated_at` DATETIME        NULL,
  PRIMARY KEY (`id`),
  KEY `parents_user_id_index` (`user_id`),
  CONSTRAINT `parents_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `teachers`;
CREATE TABLE `teachers` (
  `id`                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`           BIGINT UNSIGNED NOT NULL,
  `nip`               VARCHAR(255)    NULL,
  `nuptk`             VARCHAR(255)    NULL,
  `subject_id`        BIGINT UNSIGNED NULL,
  `join_date`         DATE            NOT NULL,
  `contract_end_date` DATE            NULL,
  `is_active`         TINYINT(1)      NOT NULL DEFAULT 1,
  `created_at`        DATETIME        NULL,
  `updated_at`        DATETIME        NULL,
  PRIMARY KEY (`id`),
  KEY `teachers_user_id_index` (`user_id`),
  KEY `teachers_subject_id_index` (`subject_id`),
  CONSTRAINT `teachers_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `teachers_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `students`;
CREATE TABLE `students` (
  `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`       BIGINT UNSIGNED NOT NULL,
  `nis`           VARCHAR(255)    NOT NULL,
  `nisn`          VARCHAR(255)    NOT NULL,
  `class_id`      BIGINT UNSIGNED NOT NULL,
  `parent_id`     BIGINT UNSIGNED NULL,
  `birth_place`   VARCHAR(255)    NULL,
  `birth_date`    DATE            NULL,
  `gender`        ENUM('male','female') NOT NULL,
  `religion`      VARCHAR(255)    NULL,
  `address`       TEXT            NULL,
  `phone`         VARCHAR(255)    NULL,
  `admission_date` DATE           NOT NULL,
  `status`        ENUM('active','inactive','graduated','transferred','dropped') NOT NULL DEFAULT 'active',
  `photo`         VARCHAR(255)    NULL,
  `created_at`    DATETIME        NULL,
  `updated_at`    DATETIME        NULL,
  `deleted_at`    DATETIME        NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `students_nis_unique` (`nis`),
  UNIQUE KEY `students_nisn_unique` (`nisn`),
  KEY `students_user_id_index` (`user_id`),
  KEY `students_class_id_index` (`class_id`),
  KEY `students_parent_id_index` (`parent_id`),
  CONSTRAINT `students_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `students_class_id_foreign` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `students_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `parents` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 4. KEANGGOTAAN KELAS, PENGAJARAN & JADWAL
-- ============================================================================

DROP TABLE IF EXISTS `class_members`;
CREATE TABLE `class_members` (
  `id`               BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `class_id`         BIGINT UNSIGNED NOT NULL,
  `student_id`       BIGINT UNSIGNED NOT NULL,
  `academic_year_id` BIGINT UNSIGNED NOT NULL,
  `semester_id`      BIGINT UNSIGNED NOT NULL,
  `is_active`        TINYINT(1)      NOT NULL DEFAULT 1,
  `created_at`       DATETIME        NULL,
  `updated_at`       DATETIME        NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `class_members_class_id_student_id_academic_year_id_semester_id_unique` (`class_id`, `student_id`, `academic_year_id`, `semester_id`),
  KEY `class_members_student_id_index` (`student_id`),
  KEY `class_members_academic_year_id_index` (`academic_year_id`),
  KEY `class_members_semester_id_index` (`semester_id`),
  CONSTRAINT `class_members_class_id_foreign` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `class_members_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  CONSTRAINT `class_members_academic_year_id_foreign` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`) ON DELETE CASCADE,
  CONSTRAINT `class_members_semester_id_foreign` FOREIGN KEY (`semester_id`) REFERENCES `semesters` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `teaching_assignments`;
CREATE TABLE `teaching_assignments` (
  `id`               BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `teacher_id`       BIGINT UNSIGNED NOT NULL,
  `subject_id`       BIGINT UNSIGNED NOT NULL,
  `class_id`         BIGINT UNSIGNED NOT NULL,
  `academic_year_id` BIGINT UNSIGNED NOT NULL,
  `semester_id`      BIGINT UNSIGNED NOT NULL,
  `created_at`       DATETIME        NULL,
  `updated_at`       DATETIME        NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `teaching_assignments_teacher_id_subject_id_class_id_academic_year_id_semester_id_unique` (`teacher_id`, `subject_id`, `class_id`, `academic_year_id`, `semester_id`),
  KEY `teaching_assignments_subject_id_index` (`subject_id`),
  KEY `teaching_assignments_class_id_index` (`class_id`),
  KEY `teaching_assignments_academic_year_id_index` (`academic_year_id`),
  KEY `teaching_assignments_semester_id_index` (`semester_id`),
  CONSTRAINT `teaching_assignments_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `teaching_assignments_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `teaching_assignments_class_id_foreign` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `teaching_assignments_academic_year_id_foreign` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`) ON DELETE CASCADE,
  CONSTRAINT `teaching_assignments_semester_id_foreign` FOREIGN KEY (`semester_id`) REFERENCES `semesters` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Catatan: unique index (room_id, day, start_time) dihapus oleh migration
-- 000099_make_room_id_nullable_in_schedules_table; room_id boleh NULL.
DROP TABLE IF EXISTS `schedules`;
CREATE TABLE `schedules` (
  `id`                     BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `teaching_assignment_id` BIGINT UNSIGNED NOT NULL,
  `room_id`                BIGINT UNSIGNED NULL,
  `day`                    ENUM('senin','selasa','rabu','kamis','jumat','sabtu') NOT NULL,
  `start_time`             TIME            NOT NULL,
  `end_time`               TIME            NOT NULL,
  `created_at`             DATETIME        NULL,
  `updated_at`             DATETIME        NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `schedules_teaching_assignment_id_day_start_time_unique` (`teaching_assignment_id`, `day`, `start_time`),
  KEY `schedules_room_id_index` (`room_id`),
  CONSTRAINT `schedules_teaching_assignment_id_foreign` FOREIGN KEY (`teaching_assignment_id`) REFERENCES `teaching_assignments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `schedules_room_id_foreign` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `attendances`;
CREATE TABLE `attendances` (
  `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `student_id`  BIGINT UNSIGNED NOT NULL,
  `schedule_id` BIGINT UNSIGNED NOT NULL,
  `date`        DATE            NOT NULL,
  `status`      ENUM('hadir','sakit','izin','alpa','terlambat') NOT NULL,
  `note`        TEXT            NULL,
  `recorded_by` BIGINT UNSIGNED NOT NULL,
  `created_at`  DATETIME        NULL,
  `updated_at`  DATETIME        NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `attendances_student_id_schedule_id_date_unique` (`student_id`, `schedule_id`, `date`),
  KEY `attendances_schedule_id_index` (`schedule_id`),
  KEY `attendances_recorded_by_index` (`recorded_by`),
  CONSTRAINT `attendances_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  CONSTRAINT `attendances_schedule_id_foreign` FOREIGN KEY (`schedule_id`) REFERENCES `schedules` (`id`) ON DELETE CASCADE,
  CONSTRAINT `attendances_recorded_by_foreign` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `journals`;
CREATE TABLE `journals` (
  `id`                  BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `teacher_id`          BIGINT UNSIGNED NOT NULL,
  `class_id`            BIGINT UNSIGNED NOT NULL,
  `subject_id`          BIGINT UNSIGNED NOT NULL,
  `schedule_id`         BIGINT UNSIGNED NULL,
  `date`                DATE            NOT NULL,
  `material`            TEXT            NOT NULL,
  `learning_objectives` TEXT            NOT NULL,
  `activities`          TEXT            NOT NULL,
  `notes`               TEXT            NULL,
  `created_at`          DATETIME        NULL,
  `updated_at`          DATETIME        NULL,
  PRIMARY KEY (`id`),
  KEY `journals_teacher_id_index` (`teacher_id`),
  KEY `journals_class_id_index` (`class_id`),
  KEY `journals_subject_id_index` (`subject_id`),
  KEY `journals_schedule_id_index` (`schedule_id`),
  CONSTRAINT `journals_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `journals_class_id_foreign` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `journals_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `journals_schedule_id_foreign` FOREIGN KEY (`schedule_id`) REFERENCES `schedules` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `assignments`;
CREATE TABLE `assignments` (
  `id`           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title`        VARCHAR(255)    NOT NULL,
  `description`  TEXT            NOT NULL,
  `subject_id`   BIGINT UNSIGNED NOT NULL,
  `class_id`     BIGINT UNSIGNED NOT NULL,
  `teacher_id`   BIGINT UNSIGNED NOT NULL,
  `type`         VARCHAR(255)    NOT NULL DEFAULT 'assignment',
  `deadline`     DATETIME        NOT NULL,
  `max_score`    DECIMAL(5,2)    NOT NULL DEFAULT 100.00,
  `is_published` TINYINT(1)      NOT NULL DEFAULT 0,
  `created_at`   DATETIME        NULL,
  `updated_at`   DATETIME        NULL,
  PRIMARY KEY (`id`),
  KEY `assignments_subject_id_index` (`subject_id`),
  KEY `assignments_class_id_index` (`class_id`),
  KEY `assignments_teacher_id_index` (`teacher_id`),
  CONSTRAINT `assignments_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `assignments_class_id_foreign` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `assignments_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `assignment_submissions`;
CREATE TABLE `assignment_submissions` (
  `id`              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `assignment_id`   BIGINT UNSIGNED NOT NULL,
  `student_id`      BIGINT UNSIGNED NOT NULL,
  `submission_text` TEXT            NULL,
  `file_path`       VARCHAR(255)    NULL,
  `submitted_at`    DATETIME        NOT NULL,
  `score`           DECIMAL(5,2)    NULL,
  `graded_at`       DATETIME        NULL,
  `graded_by`       BIGINT UNSIGNED NULL,
  `feedback`        TEXT            NULL,
  `status`          ENUM('pending','submitted','graded','returned') NOT NULL DEFAULT 'pending',
  `created_at`      DATETIME        NULL,
  `updated_at`      DATETIME        NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `assignment_submissions_assignment_id_student_id_unique` (`assignment_id`, `student_id`),
  KEY `assignment_submissions_student_id_index` (`student_id`),
  KEY `assignment_submissions_graded_by_index` (`graded_by`),
  CONSTRAINT `assignment_submissions_assignment_id_foreign` FOREIGN KEY (`assignment_id`) REFERENCES `assignments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `assignment_submissions_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  CONSTRAINT `assignment_submissions_graded_by_foreign` FOREIGN KEY (`graded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 5. BANK SOAL & UJIAN
-- ============================================================================

DROP TABLE IF EXISTS `question_banks`;
CREATE TABLE `question_banks` (
  `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`        VARCHAR(255)    NOT NULL,
  `subject_id`  BIGINT UNSIGNED NOT NULL,
  `teacher_id`  BIGINT UNSIGNED NOT NULL,
  `description` TEXT            NULL,
  `created_at`  DATETIME        NULL,
  `updated_at`  DATETIME        NULL,
  PRIMARY KEY (`id`),
  KEY `question_banks_subject_id_index` (`subject_id`),
  KEY `question_banks_teacher_id_index` (`teacher_id`),
  CONSTRAINT `question_banks_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `question_banks_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `questions`;
CREATE TABLE `questions` (
  `id`               BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `question_bank_id` BIGINT UNSIGNED NOT NULL,
  `type`             ENUM('mcq','mcq_complex','true_false','matching','short_answer','essay','file_upload','practical') NOT NULL,
  `question`         TEXT            NOT NULL,
  `explanation`      TEXT            NULL,
  `difficulty`       ENUM('easy','medium','hard') NOT NULL,
  `points`           DECIMAL(5,2)    NOT NULL DEFAULT 1.00,
  `topic`            VARCHAR(255)    NULL,
  `is_active`        TINYINT(1)      NOT NULL DEFAULT 1,
  `created_at`       DATETIME        NULL,
  `updated_at`       DATETIME        NULL,
  PRIMARY KEY (`id`),
  KEY `questions_question_bank_id_index` (`question_bank_id`),
  CONSTRAINT `questions_question_bank_id_foreign` FOREIGN KEY (`question_bank_id`) REFERENCES `question_banks` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `question_options`;
CREATE TABLE `question_options` (
  `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `question_id` BIGINT UNSIGNED NOT NULL,
  `option_text` TEXT            NOT NULL,
  `is_correct`  TINYINT(1)      NOT NULL DEFAULT 0,
  `order`       INT             NOT NULL DEFAULT 0,
  `created_at`  DATETIME        NULL,
  `updated_at`  DATETIME        NULL,
  PRIMARY KEY (`id`),
  KEY `question_options_question_id_index` (`question_id`),
  CONSTRAINT `question_options_question_id_foreign` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `exams`;
CREATE TABLE `exams` (
  `id`                        BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title`                     VARCHAR(255)    NOT NULL,
  `description`               TEXT            NULL,
  `subject_id`                BIGINT UNSIGNED NOT NULL,
  `teacher_id`                BIGINT UNSIGNED NOT NULL,
  `type`                      ENUM('quiz','pre_test','post_test','assessment','mid_test','pts','pas','practical_exam','project_exam','final_exam') NOT NULL,
  `academic_year_id`          BIGINT UNSIGNED NOT NULL,
  `semester_id`               BIGINT UNSIGNED NOT NULL,
  `start_at`                  DATETIME        NOT NULL,
  `end_at`                    DATETIME        NOT NULL,
  `duration_minutes`          INT             NOT NULL,
  `attempt_limit`             INT             NOT NULL DEFAULT 1,
  `random_question`           TINYINT(1)      NOT NULL DEFAULT 0,
  `random_option`             TINYINT(1)      NOT NULL DEFAULT 0,
  `shuffle_options`           TINYINT(1)      NOT NULL DEFAULT 0,
  `show_result`               TINYINT(1)      NOT NULL DEFAULT 0,
  `show_answer_after_submit`  TINYINT(1)      NOT NULL DEFAULT 0,
  `passing_score`             DECIMAL(5,2)    NOT NULL DEFAULT 60.00,
  `token`                     VARCHAR(255)    NULL,
  `status`                    ENUM('draft','published','active','completed','archived') NOT NULL DEFAULT 'draft',
  `created_at`                DATETIME        NULL,
  `updated_at`                DATETIME        NULL,
  PRIMARY KEY (`id`),
  KEY `exams_subject_id_index` (`subject_id`),
  KEY `exams_teacher_id_index` (`teacher_id`),
  KEY `exams_academic_year_id_index` (`academic_year_id`),
  KEY `exams_semester_id_index` (`semester_id`),
  CONSTRAINT `exams_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `exams_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `exams_academic_year_id_foreign` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`) ON DELETE CASCADE,
  CONSTRAINT `exams_semester_id_foreign` FOREIGN KEY (`semester_id`) REFERENCES `semesters` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `exam_classes`;
CREATE TABLE `exam_classes` (
  `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `exam_id`    BIGINT UNSIGNED NOT NULL,
  `class_id`   BIGINT UNSIGNED NOT NULL,
  `created_at` DATETIME        NULL,
  `updated_at` DATETIME        NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `exam_classes_exam_id_class_id_unique` (`exam_id`, `class_id`),
  KEY `exam_classes_class_id_index` (`class_id`),
  CONSTRAINT `exam_classes_exam_id_foreign` FOREIGN KEY (`exam_id`) REFERENCES `exams` (`id`) ON DELETE CASCADE,
  CONSTRAINT `exam_classes_class_id_foreign` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `exam_questions`;
CREATE TABLE `exam_questions` (
  `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `exam_id`     BIGINT UNSIGNED NOT NULL,
  `question_id` BIGINT UNSIGNED NOT NULL,
  `order`       INT             NOT NULL DEFAULT 0,
  `points`      DECIMAL(5,2)    NOT NULL,
  `created_at`  DATETIME        NULL,
  `updated_at`  DATETIME        NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `exam_questions_exam_id_question_id_unique` (`exam_id`, `question_id`),
  KEY `exam_questions_question_id_index` (`question_id`),
  CONSTRAINT `exam_questions_exam_id_foreign` FOREIGN KEY (`exam_id`) REFERENCES `exams` (`id`) ON DELETE CASCADE,
  CONSTRAINT `exam_questions_question_id_foreign` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `exam_attempts`;
CREATE TABLE `exam_attempts` (
  `id`             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `exam_id`        BIGINT UNSIGNED NOT NULL,
  `student_id`     BIGINT UNSIGNED NOT NULL,
  `attempt_number` INT             NOT NULL DEFAULT 1,
  `started_at`     DATETIME        NOT NULL,
  `submitted_at`   DATETIME        NULL,
  `status`         ENUM('in_progress','submitted','graded','abandoned') NOT NULL DEFAULT 'in_progress',
  `score`          DECIMAL(8,2)    NULL,
  `percentage`     DECIMAL(5,2)    NULL,
  `ip_address`     VARCHAR(255)    NULL,
  `user_agent`     TEXT            NULL,
  `created_at`     DATETIME        NULL,
  `updated_at`     DATETIME        NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `exam_attempts_exam_id_student_id_attempt_number_unique` (`exam_id`, `student_id`, `attempt_number`),
  KEY `exam_attempts_student_id_index` (`student_id`),
  CONSTRAINT `exam_attempts_exam_id_foreign` FOREIGN KEY (`exam_id`) REFERENCES `exams` (`id`) ON DELETE CASCADE,
  CONSTRAINT `exam_attempts_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `exam_answers`;
CREATE TABLE `exam_answers` (
  `id`                 BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `exam_attempt_id`    BIGINT UNSIGNED NOT NULL,
  `question_id`        BIGINT UNSIGNED NOT NULL,
  `answer`             TEXT            NULL,
  `selected_option_id` BIGINT UNSIGNED NULL,
  `essay_answer`       TEXT            NULL,
  `file_path`          VARCHAR(255)    NULL,
  `points_earned`      DECIMAL(5,2)    NULL,
  `is_correct`         TINYINT(1)      NULL,
  `is_graded`          TINYINT(1)      NOT NULL DEFAULT 0,
  `graded_by`          BIGINT UNSIGNED NULL,
  `graded_at`          DATETIME        NULL,
  `created_at`         DATETIME        NULL,
  `updated_at`         DATETIME        NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `exam_answers_exam_attempt_id_question_id_unique` (`exam_attempt_id`, `question_id`),
  KEY `exam_answers_question_id_index` (`question_id`),
  KEY `exam_answers_selected_option_id_index` (`selected_option_id`),
  KEY `exam_answers_graded_by_index` (`graded_by`),
  CONSTRAINT `exam_answers_exam_attempt_id_foreign` FOREIGN KEY (`exam_attempt_id`) REFERENCES `exam_attempts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `exam_answers_question_id_foreign` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `exam_answers_selected_option_id_foreign` FOREIGN KEY (`selected_option_id`) REFERENCES `question_options` (`id`) ON DELETE SET NULL,
  CONSTRAINT `exam_answers_graded_by_foreign` FOREIGN KEY (`graded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 6. PENILAIAN & RAPOR
-- ============================================================================

DROP TABLE IF EXISTS `grading_configs`;
CREATE TABLE `grading_configs` (
  `id`               BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `subject_id`       BIGINT UNSIGNED NOT NULL,
  `class_id`         BIGINT UNSIGNED NOT NULL,
  `academic_year_id` BIGINT UNSIGNED NOT NULL,
  `semester_id`      BIGINT UNSIGNED NOT NULL,
  `method`           ENUM('automatic','manual') NOT NULL DEFAULT 'automatic',
  `tugas_weight`     DECIMAL(5,2)    NOT NULL DEFAULT 20.00,
  `quiz_weight`      DECIMAL(5,2)    NOT NULL DEFAULT 10.00,
  `uts_weight`       DECIMAL(5,2)    NOT NULL DEFAULT 20.00,
  `uas_weight`       DECIMAL(5,2)    NOT NULL DEFAULT 30.00,
  `practical_weight` DECIMAL(5,2)    NOT NULL DEFAULT 10.00,
  `project_weight`   DECIMAL(5,2)    NOT NULL DEFAULT 10.00,
  `created_at`       DATETIME        NULL,
  `updated_at`       DATETIME        NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `grading_configs_subject_id_class_id_academic_year_id_semester_id_unique` (`subject_id`, `class_id`, `academic_year_id`, `semester_id`),
  KEY `grading_configs_class_id_index` (`class_id`),
  KEY `grading_configs_academic_year_id_index` (`academic_year_id`),
  KEY `grading_configs_semester_id_index` (`semester_id`),
  CONSTRAINT `grading_configs_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `grading_configs_class_id_foreign` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `grading_configs_academic_year_id_foreign` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`) ON DELETE CASCADE,
  CONSTRAINT `grading_configs_semester_id_foreign` FOREIGN KEY (`semester_id`) REFERENCES `semesters` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `grades`;
CREATE TABLE `grades` (
  `id`               BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `student_id`       BIGINT UNSIGNED NOT NULL,
  `subject_id`       BIGINT UNSIGNED NOT NULL,
  `class_id`         BIGINT UNSIGNED NOT NULL,
  `academic_year_id` BIGINT UNSIGNED NOT NULL,
  `semester_id`      BIGINT UNSIGNED NOT NULL,
  `tugas_score`      DECIMAL(5,2)    NULL,
  `quiz_score`       DECIMAL(5,2)    NULL,
  `uts_score`        DECIMAL(5,2)    NULL,
  `uas_score`        DECIMAL(5,2)    NULL,
  `practical_score`  DECIMAL(5,2)    NULL,
  `project_score`    DECIMAL(5,2)    NULL,
  `final_score`      DECIMAL(5,2)    NULL,
  `final_percentage` DECIMAL(5,2)    NULL,
  `letter_grade`     VARCHAR(255)    NULL,
  `is_remedial`      TINYINT(1)      NOT NULL DEFAULT 0,
  `created_at`       DATETIME        NULL,
  `updated_at`       DATETIME        NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `grades_student_id_subject_id_class_id_academic_year_id_semester_id_is_remedial_unique` (`student_id`, `subject_id`, `class_id`, `academic_year_id`, `semester_id`, `is_remedial`),
  KEY `grades_subject_id_index` (`subject_id`),
  KEY `grades_class_id_index` (`class_id`),
  KEY `grades_academic_year_id_index` (`academic_year_id`),
  KEY `grades_semester_id_index` (`semester_id`),
  CONSTRAINT `grades_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  CONSTRAINT `grades_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `grades_class_id_foreign` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `grades_academic_year_id_foreign` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`) ON DELETE CASCADE,
  CONSTRAINT `grades_semester_id_foreign` FOREIGN KEY (`semester_id`) REFERENCES `semesters` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `report_cards`;
CREATE TABLE `report_cards` (
  `id`                   BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `student_id`           BIGINT UNSIGNED NOT NULL,
  `class_id`             BIGINT UNSIGNED NOT NULL,
  `academic_year_id`     BIGINT UNSIGNED NOT NULL,
  `semester_id`          BIGINT UNSIGNED NOT NULL,
  `total_score`          DECIMAL(8,2)    NULL,
  `average_score`        DECIMAL(5,2)    NULL,
  `rank`                 INT             NULL,
  `attendance_summary`   TEXT            NULL,
  `class_teacher_notes`  TEXT            NULL,
  `principal_notes`      TEXT            NULL,
  `is_finalized`         TINYINT(1)      NOT NULL DEFAULT 0,
  `finalized_at`         DATETIME        NULL,
  `finalized_by`         BIGINT UNSIGNED NULL,
  `created_at`           DATETIME        NULL,
  `updated_at`           DATETIME        NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `report_cards_student_id_class_id_academic_year_id_semester_id_unique` (`student_id`, `class_id`, `academic_year_id`, `semester_id`),
  KEY `report_cards_class_id_index` (`class_id`),
  KEY `report_cards_academic_year_id_index` (`academic_year_id`),
  KEY `report_cards_semester_id_index` (`semester_id`),
  KEY `report_cards_finalized_by_index` (`finalized_by`),
  CONSTRAINT `report_cards_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  CONSTRAINT `report_cards_class_id_foreign` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `report_cards_academic_year_id_foreign` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`) ON DELETE CASCADE,
  CONSTRAINT `report_cards_semester_id_foreign` FOREIGN KEY (`semester_id`) REFERENCES `semesters` (`id`) ON DELETE CASCADE,
  CONSTRAINT `report_cards_finalized_by_foreign` FOREIGN KEY (`finalized_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 7. PENGUMUMAN, NOTIFIKASI & AUDIT
-- ============================================================================

DROP TABLE IF EXISTS `announcements`;
CREATE TABLE `announcements` (
  `id`                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title`             VARCHAR(255)    NOT NULL,
  `content`           TEXT            NOT NULL,
  `author_id`         BIGINT UNSIGNED NOT NULL,
  `target_roles`      JSON            NULL,
  `target_class_ids`  JSON            NULL,
  `published_at`      DATETIME        NULL,
  `expires_at`        DATETIME        NULL,
  `attachment_path`   VARCHAR(255)    NULL,
  `is_published`      TINYINT(1)      NOT NULL DEFAULT 0,
  `created_at`        DATETIME        NULL,
  `updated_at`        DATETIME        NULL,
  PRIMARY KEY (`id`),
  KEY `announcements_author_id_index` (`author_id`),
  CONSTRAINT `announcements_author_id_foreign` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `notifications`;
CREATE TABLE `notifications` (
  `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`    BIGINT UNSIGNED NOT NULL,
  `type`       VARCHAR(255)    NOT NULL,
  `title`      VARCHAR(255)    NOT NULL,
  `message`    TEXT            NOT NULL,
  `data`       JSON            NULL,
  `read_at`    DATETIME        NULL,
  `created_at` DATETIME        NULL,
  `updated_at` DATETIME        NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_user_id_index` (`user_id`),
  CONSTRAINT `notifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `audit_logs`;
CREATE TABLE `audit_logs` (
  `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`    BIGINT UNSIGNED NULL,
  `action`     VARCHAR(255)    NOT NULL,
  `module`     VARCHAR(255)    NOT NULL,
  `record_id`  VARCHAR(255)    NULL,
  `old_data`   JSON            NULL,
  `new_data`   JSON            NULL,
  `ip_address` VARCHAR(255)    NULL,
  `user_agent` TEXT            NULL,
  `created_at` DATETIME        NULL,
  `updated_at` DATETIME        NULL,
  PRIMARY KEY (`id`),
  KEY `audit_logs_user_id_index` (`user_id`),
  CONSTRAINT `audit_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 8. TABEL MIGRATIONS (agar Laravel mengenali schema sudah terpasang)
-- ============================================================================

DROP TABLE IF EXISTS `migrations`;
CREATE TABLE `migrations` (
  `id`        INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `migration` VARCHAR(255) NOT NULL,
  `batch`     INT          NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 9. DATA AWAL (SEED)
-- ============================================================================

-- ----------------------------------------------------------------------------
-- 9.1 Roles (8 peran)
-- ----------------------------------------------------------------------------
INSERT INTO `roles` (`id`, `name`, `label`, `created_at`, `updated_at`) VALUES
(1, 'super_admin',        'Super Admin',         NOW(), NOW()),
(2, 'admin_sekolah',      'Admin Sekolah',       NOW(), NOW()),
(3, 'kepala_sekolah',     'Kepala Sekolah',      NOW(), NOW()),
(4, 'wakil_kepala_sekolah','Wakil Kepala Sekolah', NOW(), NOW()),
(5, 'guru',               'Guru',                NOW(), NOW()),
(6, 'wali_kelas',         'Wali Kelas',          NOW(), NOW()),
(7, 'siswa',              'Siswa',               NOW(), NOW()),
(8, 'orang_tua',          'Orang Tua / Wali',    NOW(), NOW());

-- ----------------------------------------------------------------------------
-- 9.2 Users (admin, 5 guru, 20 siswa) — password semua: 'password'
-- ----------------------------------------------------------------------------
INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `role_id`, `avatar`, `phone`, `address`, `is_active`, `remember_token`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1,  'Admin Utama',        'admin@smknurululum.sch.id',            NOW(), '$2y$12$FCHt/uFGDAmTb/U5L89wOe8pz7n2YPz73kzeD49CXkJiBjv4OehqW', 1, NULL, NULL, NULL, 1, NULL, NOW(), NOW(), NULL),
(2,  'Ahmad Fauzi, S.Pd',  'ahmad@smknurululum.sch.id',            NOW(), '$2y$12$FCHt/uFGDAmTb/U5L89wOe8pz7n2YPz73kzeD49CXkJiBjv4OehqW', 5, NULL, NULL, NULL, 1, NULL, NOW(), NOW(), NULL),
(3,  'Siti Rahmawati, S.Pd','siti@smknurululum.sch.id',            NOW(), '$2y$12$FCHt/uFGDAmTb/U5L89wOe8pz7n2YPz73kzeD49CXkJiBjv4OehqW', 5, NULL, NULL, NULL, 1, NULL, NOW(), NOW(), NULL),
(4,  'Budi Santoso, S.Kom','budi@smknurululum.sch.id',             NOW(), '$2y$12$FCHt/uFGDAmTb/U5L89wOe8pz7n2YPz73kzeD49CXkJiBjv4OehqW', 5, NULL, NULL, NULL, 1, NULL, NOW(), NOW(), NULL),
(5,  'Dewi Lestari, S.E.', 'dewi@smknurululum.sch.id',             NOW(), '$2y$12$FCHt/uFGDAmTb/U5L89wOe8pz7n2YPz73kzeD49CXkJiBjv4OehqW', 5, NULL, NULL, NULL, 1, NULL, NOW(), NOW(), NULL),
(6,  'Eko Prasetyo, S.Pd', 'eko@smknurululum.sch.id',              NOW(), '$2y$12$FCHt/uFGDAmTb/U5L89wOe8pz7n2YPz73kzeD49CXkJiBjv4OehqW', 5, NULL, NULL, NULL, 1, NULL, NOW(), NOW(), NULL),
(7,  'Ahmad Rizky',        'ahmad.rizky@student.smknurululum.sch.id',    NOW(), '$2y$12$FCHt/uFGDAmTb/U5L89wOe8pz7n2YPz73kzeD49CXkJiBjv4OehqW', 7, NULL, NULL, NULL, 1, NULL, NOW(), NOW(), NULL),
(8,  'Siti Nurhaliza',     'siti.nurhaliza@student.smknurululum.sch.id', NOW(), '$2y$12$FCHt/uFGDAmTb/U5L89wOe8pz7n2YPz73kzeD49CXkJiBjv4OehqW', 7, NULL, NULL, NULL, 1, NULL, NOW(), NOW(), NULL),
(9,  'Muhammad Fadil',     'muhammad.fadil@student.smknurululum.sch.id', NOW(), '$2y$12$FCHt/uFGDAmTb/U5L89wOe8pz7n2YPz73kzeD49CXkJiBjv4OehqW', 7, NULL, NULL, NULL, 1, NULL, NOW(), NOW(), NULL),
(10, 'Aisyah Putri',       'aisyah.putri@student.smknurululum.sch.id',   NOW(), '$2y$12$FCHt/uFGDAmTb/U5L89wOe8pz7n2YPz73kzeD49CXkJiBjv4OehqW', 7, NULL, NULL, NULL, 1, NULL, NOW(), NOW(), NULL),
(11, 'Abdullah Alfarizi',  'abdullah.alfarizi@student.smknurululum.sch.id', NOW(), '$2y$12$FCHt/uFGDAmTb/U5L89wOe8pz7n2YPz73kzeD49CXkJiBjv4OehqW', 7, NULL, NULL, NULL, 1, NULL, NOW(), NOW(), NULL),
(12, 'Fatimah Azzahra',    'fatimah.azzahra@student.smknurululum.sch.id', NOW(), '$2y$12$FCHt/uFGDAmTb/U5L89wOe8pz7n2YPz73kzeD49CXkJiBjv4OehqW', 7, NULL, NULL, NULL, 1, NULL, NOW(), NOW(), NULL),
(13, 'Hendra Wijaya',      'hendra.wijaya@student.smknurululum.sch.id',   NOW(), '$2y$12$FCHt/uFGDAmTb/U5L89wOe8pz7n2YPz73kzeD49CXkJiBjv4OehqW', 7, NULL, NULL, NULL, 1, NULL, NOW(), NOW(), NULL),
(14, 'Dian Permata',       'dian.permata@student.smknurululum.sch.id',    NOW(), '$2y$12$FCHt/uFGDAmTb/U5L89wOe8pz7n2YPz73kzeD49CXkJiBjv4OehqW', 7, NULL, NULL, NULL, 1, NULL, NOW(), NOW(), NULL),
(15, 'Rizal Ramadhan',     'rizal.ramadhan@student.smknurululum.sch.id',  NOW(), '$2y$12$FCHt/uFGDAmTb/U5L89wOe8pz7n2YPz73kzeD49CXkJiBjv4OehqW', 7, NULL, NULL, NULL, 1, NULL, NOW(), NOW(), NULL),
(16, 'Nurul Hidayah',      'nurul.hidayah@student.smknurululum.sch.id',   NOW(), '$2y$12$FCHt/uFGDAmTb/U5L89wOe8pz7n2YPz73kzeD49CXkJiBjv4OehqW', 7, NULL, NULL, NULL, 1, NULL, NOW(), NOW(), NULL),
(17, 'Fajar Nugroho',      'fajar.nugroho@student.smknurululum.sch.id',   NOW(), '$2y$12$FCHt/uFGDAmTb/U5L89wOe8pz7n2YPz73kzeD49CXkJiBjv4OehqW', 7, NULL, NULL, NULL, 1, NULL, NOW(), NOW(), NULL),
(18, 'Maya Sari',          'maya.sari@student.smknurululum.sch.id',       NOW(), '$2y$12$FCHt/uFGDAmTb/U5L89wOe8pz7n2YPz73kzeD49CXkJiBjv4OehqW', 7, NULL, NULL, NULL, 1, NULL, NOW(), NOW(), NULL),
(19, 'Dimas Pratama',      'dimas.pratama@student.smknurululum.sch.id',   NOW(), '$2y$12$FCHt/uFGDAmTb/U5L89wOe8pz7n2YPz73kzeD49CXkJiBjv4OehqW', 7, NULL, NULL, NULL, 1, NULL, NOW(), NOW(), NULL),
(20, 'Lestari Wulandari',  'lestari.wulandari@student.smknurululum.sch.id', NOW(), '$2y$12$FCHt/uFGDAmTb/U5L89wOe8pz7n2YPz73kzeD49CXkJiBjv4OehqW', 7, NULL, NULL, NULL, 1, NULL, NOW(), NOW(), NULL),
(21, 'Arif Setiawan',      'arif.setiawan@student.smknurululum.sch.id',   NOW(), '$2y$12$FCHt/uFGDAmTb/U5L89wOe8pz7n2YPz73kzeD49CXkJiBjv4OehqW', 7, NULL, NULL, NULL, 1, NULL, NOW(), NOW(), NULL),
(22, 'Putri Maharani',     'putri.maharani@student.smknurululum.sch.id',  NOW(), '$2y$12$FCHt/uFGDAmTb/U5L89wOe8pz7n2YPz73kzeD49CXkJiBjv4OehqW', 7, NULL, NULL, NULL, 1, NULL, NOW(), NOW(), NULL),
(23, 'Bayu Firmansyah',    'bayu.firmansyah@student.smknurululum.sch.id', NOW(), '$2y$12$FCHt/uFGDAmTb/U5L89wOe8pz7n2YPz73kzeD49CXkJiBjv4OehqW', 7, NULL, NULL, NULL, 1, NULL, NOW(), NOW(), NULL),
(24, 'Ratna Dewi',         'ratna.dewi@student.smknurululum.sch.id',      NOW(), '$2y$12$FCHt/uFGDAmTb/U5L89wOe8pz7n2YPz73kzeD49CXkJiBjv4OehqW', 7, NULL, NULL, NULL, 1, NULL, NOW(), NOW(), NULL),
(25, 'Gilang Ramadhan',    'gilang.ramadhan@student.smknurululum.sch.id', NOW(), '$2y$12$FCHt/uFGDAmTb/U5L89wOe8pz7n2YPz73kzeD49CXkJiBjv4OehqW', 7, NULL, NULL, NULL, 1, NULL, NOW(), NOW(), NULL),
(26, 'Anisa Rahmawati',    'anisa.rahmawati@student.smknurululum.sch.id', NOW(), '$2y$12$FCHt/uFGDAmTb/U5L89wOe8pz7n2YPz73kzeD49CXkJiBjv4OehqW', 7, NULL, NULL, NULL, 1, NULL, NOW(), NOW(), NULL);

-- ----------------------------------------------------------------------------
-- 9.3 Tahun Ajaran & Semester
-- ----------------------------------------------------------------------------
INSERT INTO `academic_years` (`id`, `name`, `start_date`, `end_date`, `is_active`, `created_at`, `updated_at`) VALUES
(1, '2024/2025', '2024-07-15', '2025-06-30', 0, NOW(), NOW()),
(2, '2025/2026', '2025-07-14', '2026-06-30', 1, NOW(), NOW());

INSERT INTO `semesters` (`id`, `academic_year_id`, `name`, `start_date`, `end_date`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, 'ganjil', '2024-07-15', '2024-12-20', 0, NOW(), NOW()),
(2, 1, 'genap',  '2025-01-06', '2025-06-13', 0, NOW(), NOW()),
(3, 2, 'ganjil', '2025-07-14', '2025-12-19', 1, NOW(), NOW()),
(4, 2, 'genap',  '2026-01-05', '2026-06-12', 0, NOW(), NOW());

-- ----------------------------------------------------------------------------
-- 9.4 Jurusan & Kompetensi Keahlian
-- ----------------------------------------------------------------------------
INSERT INTO `majors` (`id`, `name`, `code`, `description`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Teknik Komputer dan Jaringan',          'TKJ',  'Program keahlian bidang teknologi informasi', 1, NOW(), NOW()),
(2, 'Rekayasa Perangkat Lunak',              'RPL',  'Program keahlian pengembangan perangkat lunak', 1, NOW(), NOW()),
(3, 'Akuntansi dan Keuangan Lembaga',        'AKL',  'Program keahlian bidang akuntansi', 1, NOW(), NOW()),
(4, 'Otomatisasi dan Tata Kelola Perkantoran','OTKP', 'Program keahlian tata kelola perkantoran', 1, NOW(), NOW());

INSERT INTO `competencies` (`id`, `major_id`, `name`, `code`, `description`, `created_at`, `updated_at`) VALUES
(1, 1, 'Jaringan Komputer',        'TKJ-01',  NULL, NOW(), NOW()),
(2, 1, 'Sistem Operasi',           'TKJ-02',  NULL, NOW(), NOW()),
(3, 2, 'Pemrograman Web',          'RPL-01',  NULL, NOW(), NOW()),
(4, 2, 'Basis Data',               'RPL-02',  NULL, NOW(), NOW()),
(5, 3, 'Akuntansi Dasar',          'AKL-01',  NULL, NOW(), NOW()),
(6, 3, 'Laporan Keuangan',         'AKL-02',  NULL, NOW(), NOW()),
(7, 4, 'Tata Kelola Perkantoran',  'OTKP-01', NULL, NOW(), NOW()),
(8, 4, 'Kepengurusan Dokumen',     'OTKP-02', NULL, NOW(), NOW());

-- ----------------------------------------------------------------------------
-- 9.5 Ruangan
-- ----------------------------------------------------------------------------
INSERT INTO `rooms` (`id`, `name`, `code`, `capacity`, `building`, `floor`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Ruang 101',     'R101',  36, 'Gedung A', 1, 1, NOW(), NOW()),
(2, 'Ruang 102',     'R102',  36, 'Gedung A', 1, 1, NOW(), NOW()),
(3, 'Lab Komputer',  'LAB01', 30, 'Gedung B', 2, 1, NOW(), NOW()),
(4, 'Ruang Guru',    'RG01',  20, 'Gedung A', 3, 1, NOW(), NOW());

-- ----------------------------------------------------------------------------
-- 9.6 Mata Pelajaran
-- ----------------------------------------------------------------------------
INSERT INTO `subjects` (`id`, `name`, `code`, `major_id`, `description`, `is_active`, `created_at`, `updated_at`) VALUES
(1,  'Bahasa Indonesia',              'IND', NULL, NULL, 1, NOW(), NOW()),
(2,  'Bahasa Inggris',                'ENG', NULL, NULL, 1, NOW(), NOW()),
(3,  'Matematika',                    'MTK', NULL, NULL, 1, NOW(), NOW()),
(4,  'Pendidikan Agama Islam',        'PAI', NULL, NULL, 1, NOW(), NOW()),
(5,  'Pendidikan Kewarganegaraan',    'PKn', NULL, NULL, 1, NOW(), NOW()),
(6,  'Pemrograman Web',               'PWB', 2,    NULL, 1, NOW(), NOW()),
(7,  'Jaringan Komputer',             'JKO', 1,    NULL, 1, NOW(), NOW()),
(8,  'Akuntansi Dasar',               'AKD', 3,    NULL, 1, NOW(), NOW()),
(9,  'Tata Kelola Perkantoran',       'TKP', 4,    NULL, 1, NOW(), NOW()),
(10, 'Basis Data',                    'BDA', 2,    NULL, 1, NOW(), NOW());

-- ----------------------------------------------------------------------------
-- 9.7 Guru
-- ----------------------------------------------------------------------------
INSERT INTO `teachers` (`id`, `user_id`, `nip`, `nuptk`, `subject_id`, `join_date`, `contract_end_date`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 2, '1985010120100101', '123456789001', 1, '2010-07-01', NULL, 1, NOW(), NOW()),
(2, 3, '1985010120100102', '123456789002', 2, '2010-07-01', NULL, 1, NOW(), NOW()),
(3, 4, '1985010120100103', '123456789003', 3, '2010-07-01', NULL, 1, NOW(), NOW()),
(4, 5, '1985010120100104', '123456789004', 4, '2010-07-01', NULL, 1, NOW(), NOW()),
(5, 6, '1985010120100105', '123456789005', 5, '2010-07-01', NULL, 1, NOW(), NOW());

-- ----------------------------------------------------------------------------
-- 9.8 Kelas
-- ----------------------------------------------------------------------------
INSERT INTO `classes` (`id`, `name`, `major_id`, `competency_id`, `academic_year_id`, `semester_id`, `capacity`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'X TKJ 1',  1, 1, 2, 3, 36, 1, NOW(), NOW()),
(2, 'X TKJ 2',  1, 1, 2, 3, 36, 1, NOW(), NOW()),
(3, 'X RPL 1',  2, 3, 2, 3, 36, 1, NOW(), NOW()),
(4, 'X RPL 2',  2, 3, 2, 3, 36, 1, NOW(), NOW()),
(5, 'X AKL 1',  3, 5, 2, 3, 36, 1, NOW(), NOW()),
(6, 'X AKL 2',  3, 5, 2, 3, 36, 1, NOW(), NOW()),
(7, 'X OTKP 1', 4, 7, 2, 3, 36, 1, NOW(), NOW()),
(8, 'X OTKP 2', 4, 7, 2, 3, 36, 1, NOW(), NOW());

-- ----------------------------------------------------------------------------
-- 9.9 Siswa
-- ----------------------------------------------------------------------------
INSERT INTO `students` (`id`, `user_id`, `nis`, `nisn`, `class_id`, `parent_id`, `birth_place`, `birth_date`, `gender`, `religion`, `address`, `phone`, `admission_date`, `status`, `photo`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1,  7,  '000001', '00000001', 1, NULL, 'Jakarta', '2008-01-15', 'male',   'Islam', 'Jl. Pendidikan No. 1',  NULL, '2025-07-14', 'active', NULL, NOW(), NOW(), NULL),
(2,  8,  '000002', '00000002', 2, NULL, 'Jakarta', '2008-01-15', 'female', 'Islam', 'Jl. Pendidikan No. 2',  NULL, '2025-07-14', 'active', NULL, NOW(), NOW(), NULL),
(3,  9,  '000003', '00000003', 3, NULL, 'Jakarta', '2008-01-15', 'male',   'Islam', 'Jl. Pendidikan No. 3',  NULL, '2025-07-14', 'active', NULL, NOW(), NOW(), NULL),
(4,  10, '000004', '00000004', 4, NULL, 'Jakarta', '2008-01-15', 'female', 'Islam', 'Jl. Pendidikan No. 4',  NULL, '2025-07-14', 'active', NULL, NOW(), NOW(), NULL),
(5,  11, '000005', '00000005', 5, NULL, 'Jakarta', '2008-01-15', 'male',   'Islam', 'Jl. Pendidikan No. 5',  NULL, '2025-07-14', 'active', NULL, NOW(), NOW(), NULL),
(6,  12, '000006', '00000006', 6, NULL, 'Jakarta', '2008-01-15', 'female', 'Islam', 'Jl. Pendidikan No. 6',  NULL, '2025-07-14', 'active', NULL, NOW(), NOW(), NULL),
(7,  13, '000007', '00000007', 7, NULL, 'Jakarta', '2008-01-15', 'male',   'Islam', 'Jl. Pendidikan No. 7',  NULL, '2025-07-14', 'active', NULL, NOW(), NOW(), NULL),
(8,  14, '000008', '00000008', 8, NULL, 'Jakarta', '2008-01-15', 'female', 'Islam', 'Jl. Pendidikan No. 8',  NULL, '2025-07-14', 'active', NULL, NOW(), NOW(), NULL),
(9,  15, '000009', '00000009', 1, NULL, 'Jakarta', '2008-01-15', 'male',   'Islam', 'Jl. Pendidikan No. 9',  NULL, '2025-07-14', 'active', NULL, NOW(), NOW(), NULL),
(10, 16, '000010', '00000010', 2, NULL, 'Jakarta', '2008-01-15', 'female', 'Islam', 'Jl. Pendidikan No. 10', NULL, '2025-07-14', 'active', NULL, NOW(), NOW(), NULL),
(11, 17, '000011', '00000011', 3, NULL, 'Jakarta', '2008-01-15', 'male',   'Islam', 'Jl. Pendidikan No. 11', NULL, '2025-07-14', 'active', NULL, NOW(), NOW(), NULL),
(12, 18, '000012', '00000012', 4, NULL, 'Jakarta', '2008-01-15', 'female', 'Islam', 'Jl. Pendidikan No. 12', NULL, '2025-07-14', 'active', NULL, NOW(), NOW(), NULL),
(13, 19, '000013', '00000013', 5, NULL, 'Jakarta', '2008-01-15', 'male',   'Islam', 'Jl. Pendidikan No. 13', NULL, '2025-07-14', 'active', NULL, NOW(), NOW(), NULL),
(14, 20, '000014', '00000014', 6, NULL, 'Jakarta', '2008-01-15', 'female', 'Islam', 'Jl. Pendidikan No. 14', NULL, '2025-07-14', 'active', NULL, NOW(), NOW(), NULL),
(15, 21, '000015', '00000015', 7, NULL, 'Jakarta', '2008-01-15', 'male',   'Islam', 'Jl. Pendidikan No. 15', NULL, '2025-07-14', 'active', NULL, NOW(), NOW(), NULL),
(16, 22, '000016', '00000016', 8, NULL, 'Jakarta', '2008-01-15', 'female', 'Islam', 'Jl. Pendidikan No. 16', NULL, '2025-07-14', 'active', NULL, NOW(), NOW(), NULL),
(17, 23, '000017', '00000017', 1, NULL, 'Jakarta', '2008-01-15', 'male',   'Islam', 'Jl. Pendidikan No. 17', NULL, '2025-07-14', 'active', NULL, NOW(), NOW(), NULL),
(18, 24, '000018', '00000018', 2, NULL, 'Jakarta', '2008-01-15', 'female', 'Islam', 'Jl. Pendidikan No. 18', NULL, '2025-07-14', 'active', NULL, NOW(), NOW(), NULL),
(19, 25, '000019', '00000019', 3, NULL, 'Jakarta', '2008-01-15', 'male',   'Islam', 'Jl. Pendidikan No. 19', NULL, '2025-07-14', 'active', NULL, NOW(), NOW(), NULL),
(20, 26, '000020', '00000020', 4, NULL, 'Jakarta', '2008-01-15', 'female', 'Islam', 'Jl. Pendidikan No. 20', NULL, '2025-07-14', 'active', NULL, NOW(), NOW(), NULL);

-- ----------------------------------------------------------------------------
-- 9.10 Anggota Kelas
-- ----------------------------------------------------------------------------
INSERT INTO `class_members` (`class_id`, `student_id`, `academic_year_id`, `semester_id`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1,  2, 3, 1, NOW(), NOW()),
(2, 2,  2, 3, 1, NOW(), NOW()),
(3, 3,  2, 3, 1, NOW(), NOW()),
(4, 4,  2, 3, 1, NOW(), NOW()),
(5, 5,  2, 3, 1, NOW(), NOW()),
(6, 6,  2, 3, 1, NOW(), NOW()),
(7, 7,  2, 3, 1, NOW(), NOW()),
(8, 8,  2, 3, 1, NOW(), NOW()),
(1, 9,  2, 3, 1, NOW(), NOW()),
(2, 10, 2, 3, 1, NOW(), NOW()),
(3, 11, 2, 3, 1, NOW(), NOW()),
(4, 12, 2, 3, 1, NOW(), NOW()),
(5, 13, 2, 3, 1, NOW(), NOW()),
(6, 14, 2, 3, 1, NOW(), NOW()),
(7, 15, 2, 3, 1, NOW(), NOW()),
(8, 16, 2, 3, 1, NOW(), NOW()),
(1, 17, 2, 3, 1, NOW(), NOW()),
(2, 18, 2, 3, 1, NOW(), NOW()),
(3, 19, 2, 3, 1, NOW(), NOW()),
(4, 20, 2, 3, 1, NOW(), NOW());

-- ----------------------------------------------------------------------------
-- 9.11 Penugasan Mengajar
-- ----------------------------------------------------------------------------
INSERT INTO `teaching_assignments` (`id`, `teacher_id`, `subject_id`, `class_id`, `academic_year_id`, `semester_id`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, 2, 3, NOW(), NOW()),
(2, 2, 2, 2, 2, 3, NOW(), NOW()),
(3, 3, 3, 3, 2, 3, NOW(), NOW()),
(4, 4, 4, 4, 2, 3, NOW(), NOW()),
(5, 5, 5, 5, 2, 3, NOW(), NOW());

-- ----------------------------------------------------------------------------
-- 9.12 Jadwal
-- ----------------------------------------------------------------------------
INSERT INTO `schedules` (`id`, `teaching_assignment_id`, `room_id`, `day`, `start_time`, `end_time`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'senin',  '07:30:00', '09:00:00', NOW(), NOW()),
(2, 2, 2, 'selasa', '09:15:00', '10:45:00', NOW(), NOW()),
(3, 3, 3, 'rabu',   '11:00:00', '12:30:00', NOW(), NOW()),
(4, 4, 4, 'kamis',  '07:30:00', '09:00:00', NOW(), NOW()),
(5, 5, 1, 'jumat',  '09:15:00', '10:45:00', NOW(), NOW());

-- ----------------------------------------------------------------------------
-- 9.13 Bank Soal, Soal & Opsi
-- ----------------------------------------------------------------------------
INSERT INTO `question_banks` (`id`, `name`, `subject_id`, `teacher_id`, `description`, `created_at`, `updated_at`) VALUES
(1, 'Bank Soal Pemrograman Web', 6, 3, 'Bank soal untuk mata pelajaran Pemrograman Web', NOW(), NOW());

INSERT INTO `questions` (`id`, `question_bank_id`, `type`, `question`, `explanation`, `difficulty`, `points`, `topic`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, 'mcq',          'Apa kepanjangan dari HTML?',                          NULL, 'easy',   10.00, NULL, 1, NOW(), NOW()),
(2, 1, 'mcq',          'Tag yang digunakan untuk membuat link pada HTML adalah...', NULL, 'easy', 10.00, NULL, 1, NOW(), NOW()),
(3, 1, 'true_false',   'CSS adalah singkatan dari Cascading Style Sheets.',   NULL, 'easy',   10.00, NULL, 1, NOW(), NOW()),
(4, 1, 'essay',        'Jelaskan fungsi dari tag <div> dalam HTML!',          NULL, 'medium', 20.00, NULL, 1, NOW(), NOW()),
(5, 1, 'short_answer', 'Apa atribut src digunakan pada tag <img>?',           NULL, 'easy',   10.00, NULL, 1, NOW(), NOW());

INSERT INTO `question_options` (`id`, `question_id`, `option_text`, `is_correct`, `order`, `created_at`, `updated_at`) VALUES
(1,  1, 'Hyper Text Markup Language',      1, 1, NOW(), NOW()),
(2,  1, 'High Tech Modern Language',       0, 2, NOW(), NOW()),
(3,  1, 'Hyper Transfer Markup Language',  0, 3, NOW(), NOW()),
(4,  1, 'Home Tool Markup Language',       0, 4, NOW(), NOW()),
(5,  2, '<link>',                          0, 1, NOW(), NOW()),
(6,  2, '<a>',                             1, 2, NOW(), NOW()),
(7,  2, '<href>',                          0, 3, NOW(), NOW()),
(8,  2, '<url>',                           0, 4, NOW(), NOW()),
(9,  3, 'Benar',                           1, 1, NOW(), NOW()),
(10, 3, 'Salah',                           0, 2, NOW(), NOW());

-- ----------------------------------------------------------------------------
-- 9.14 Ujian (draft) & Soal Ujian
-- ----------------------------------------------------------------------------
INSERT INTO `exams` (`id`, `title`, `description`, `subject_id`, `teacher_id`, `type`, `academic_year_id`, `semester_id`, `start_at`, `end_at`, `duration_minutes`, `attempt_limit`, `random_question`, `random_option`, `shuffle_options`, `show_result`, `show_answer_after_submit`, `passing_score`, `token`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Quiz Pemrograman Web - HTML Dasar', 'Quiz mengenai dasar-dasar HTML', 6, 3, 'quiz', 2, 3, '2026-09-06 08:00:00', '2026-09-06 10:00:00', 60, 1, 0, 0, 0, 1, 0, 60.00, NULL, 'draft', NOW(), NOW());

INSERT INTO `exam_questions` (`exam_id`, `question_id`, `order`, `points`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 10.00, NOW(), NOW()),
(1, 2, 2, 10.00, NOW(), NOW()),
(1, 3, 3, 10.00, NOW(), NOW()),
(1, 4, 4, 20.00, NOW(), NOW()),
(1, 5, 5, 10.00, NOW(), NOW());

-- ----------------------------------------------------------------------------
-- 9.15 Riwayat Migration (batch 1) — agar Laravel tahu schema sudah terpasang
-- ----------------------------------------------------------------------------
INSERT INTO `migrations` (`migration`, `batch`) VALUES
('0001_01_01_000001_create_cache_table', 1),
('0001_01_01_000002_create_jobs_table', 1),
('2024_01_01_000001_create_roles_table', 1),
('2024_01_01_000002_create_users_table', 1),
('2024_01_01_000003_create_academic_years_table', 1),
('2024_01_01_000004_create_semesters_table', 1),
('2024_01_01_000005_create_majors_table', 1),
('2024_01_01_000006_create_competencies_table', 1),
('2024_01_01_000007_create_rooms_table', 1),
('2024_01_01_000008_create_subjects_table', 1),
('2024_01_01_000009_create_classes_table', 1),
('2024_01_01_000010_create_parents_table', 1),
('2024_01_01_000011_create_teachers_table', 1),
('2024_01_01_000012_create_students_table', 1),
('2024_01_01_000013_create_class_members_table', 1),
('2024_01_01_000014_create_teaching_assignments_table', 1),
('2024_01_01_000015_create_schedules_table', 1),
('2024_01_01_000016_create_attendances_table', 1),
('2024_01_01_000017_create_journals_table', 1),
('2024_01_01_000018_create_assignments_table', 1),
('2024_01_01_000019_create_assignment_submissions_table', 1),
('2024_01_01_000020_create_question_banks_table', 1),
('2024_01_01_000021_create_questions_table', 1),
('2024_01_01_000022_create_question_options_table', 1),
('2024_01_01_000023_create_exams_table', 1),
('2024_01_01_000024_create_exam_classes_table', 1),
('2024_01_01_000025_create_exam_questions_table', 1),
('2024_01_01_000026_create_exam_attempts_table', 1),
('2024_01_01_000027_create_exam_answers_table', 1),
('2024_01_01_000028_create_grading_configs_table', 1),
('2024_01_01_000029_create_grades_table', 1),
('2024_01_01_000030_create_report_cards_table', 1),
('2024_01_01_000031_create_announcements_table', 1),
('2024_01_01_000032_create_notifications_table', 1),
('2024_01_01_000033_create_audit_logs_table', 1),
('2024_01_01_000099_make_room_id_nullable_in_schedules_table', 1),
('2026_09_03_160500_create_sessions_table', 1);

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================================
-- SELESAI. Jumlah tabel: 40 (33 domain + 6 sistem Laravel + migrations).
-- ============================================================================