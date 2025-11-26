-- Seed data for Attendance Management System
-- Run after `database/schema.sql` has been applied.

CREATE DATABASE IF NOT EXISTS attendance_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE attendance_system;

-- Reuse same password hash as default admin (password: admin123)
SET @default_pass = '$2a$12$8Yc9cCc0oJCVKSIXSTv.FeBpI8/3vWiGS.5wxaaB1GCkCDJF1Rw9i';

-- Insert professors and students (ignore if username/email already exist)
INSERT IGNORE INTO users (username, email, password_hash, first_name, last_name, role) VALUES
('prof_john', 'john.prof@university.dz', @default_pass, 'John', 'Doe', 'professor'),
('prof_ali', 'ali.prof@university.dz', @default_pass, 'Ali', 'Bensaid', 'professor'),
('prof_zara', 'zara.prof@university.dz', @default_pass, 'Zara', 'Zohra', 'professor'),
('student01', 's01@university.dz', @default_pass, 'Amine', 'Khelifi', 'student'),
('student02', 's02@university.dz', @default_pass, 'Lina', 'Meziane', 'student'),
('student03', 's03@university.dz', @default_pass, 'Karim', 'Haddad', 'student'),
('student04', 's04@university.dz', @default_pass, 'Sara', 'Benali', 'student'),
('student05', 's05@university.dz', @default_pass, 'Yacine', 'Ould', 'student'),
('student06', 's06@university.dz', @default_pass, 'Rania', 'Amrani', 'student'),
('student07', 's07@university.dz', @default_pass, 'Nabil', 'Cherif', 'student'),
('student08', 's08@university.dz', @default_pass, 'Meryem', 'Saadi', 'student'),
('student09', 's09@university.dz', @default_pass, 'Omar', 'Zitouni', 'student'),
('student10', 's10@university.dz', @default_pass, 'Amina', 'Kassab', 'student');

-- Insert courses linked to professors using subqueries
INSERT IGNORE INTO courses (code, name, professor_id) VALUES
('CS101', 'Introduction to Computer Science', (SELECT id FROM users WHERE username='prof_john')),
('MATH201', 'Calculus II', (SELECT id FROM users WHERE username='prof_ali')),
('PHYS301', 'Physics III', (SELECT id FROM users WHERE username='prof_zara')),
('ENG150', 'Academic English', (SELECT id FROM users WHERE username='prof_john'));

-- Insert groups for some courses
INSERT IGNORE INTO groups (name, course_id) VALUES
('G1', (SELECT id FROM courses WHERE code='CS101')),
('G2', (SELECT id FROM courses WHERE code='CS101')),
('A', (SELECT id FROM courses WHERE code='MATH201'));

-- Enroll students into courses (use scalar subqueries to look up ids)
INSERT IGNORE INTO enrollments (student_id, course_id, group_id) VALUES
((SELECT id FROM users WHERE username='student01'), (SELECT id FROM courses WHERE code='CS101'), (SELECT id FROM groups WHERE name='G1' AND course_id=(SELECT id FROM courses WHERE code='CS101'))),
((SELECT id FROM users WHERE username='student02'), (SELECT id FROM courses WHERE code='CS101'), (SELECT id FROM groups WHERE name='G1' AND course_id=(SELECT id FROM courses WHERE code='CS101'))),
((SELECT id FROM users WHERE username='student03'), (SELECT id FROM courses WHERE code='CS101'), (SELECT id FROM groups WHERE name='G2' AND course_id=(SELECT id FROM courses WHERE code='CS101'))),
((SELECT id FROM users WHERE username='student04'), (SELECT id FROM courses WHERE code='MATH201'), (SELECT id FROM groups WHERE name='A' AND course_id=(SELECT id FROM courses WHERE code='MATH201'))),
((SELECT id FROM users WHERE username='student05'), (SELECT id FROM courses WHERE code='PHYS301'), NULL),
((SELECT id FROM users WHERE username='student06'), (SELECT id FROM courses WHERE code='CS101'), (SELECT id FROM groups WHERE name='G2' AND course_id=(SELECT id FROM courses WHERE code='CS101'))),
((SELECT id FROM users WHERE username='student07'), (SELECT id FROM courses WHERE code='ENG150'), NULL),
((SELECT id FROM users WHERE username='student08'), (SELECT id FROM courses WHERE code='CS101'), (SELECT id FROM groups WHERE name='G1' AND course_id=(SELECT id FROM courses WHERE code='CS101'))),
((SELECT id FROM users WHERE username='student09'), (SELECT id FROM courses WHERE code='MATH201'), (SELECT id FROM groups WHERE name='A' AND course_id=(SELECT id FROM courses WHERE code='MATH201'))),
((SELECT id FROM users WHERE username='student10'), (SELECT id FROM courses WHERE code='PHYS301'), NULL);

-- Create a few attendance sessions
INSERT IGNORE INTO attendance_sessions (course_id, group_id, session_date, session_time, status, created_by) VALUES
((SELECT id FROM courses WHERE code='CS101'), (SELECT id FROM groups WHERE name='G1' AND course_id=(SELECT id FROM courses WHERE code='CS101')), '2025-11-01', '09:00:00', 'closed', (SELECT id FROM users WHERE username='prof_john')),
((SELECT id FROM courses WHERE code='CS101'), (SELECT id FROM groups WHERE name='G2' AND course_id=(SELECT id FROM courses WHERE code='CS101')), '2025-11-02', '09:00:00', 'closed', (SELECT id FROM users WHERE username='prof_john')),
((SELECT id FROM courses WHERE code='MATH201'), (SELECT id FROM groups WHERE name='A' AND course_id=(SELECT id FROM courses WHERE code='MATH201')), '2025-11-03', '11:00:00', 'closed', (SELECT id FROM users WHERE username='prof_ali')),
((SELECT id FROM courses WHERE code='PHYS301'), NULL, '2025-11-04', '13:00:00', 'open', (SELECT id FROM users WHERE username='prof_zara'));

-- Mark attendance records for sessions (mix of present/absent/late/excused)
INSERT IGNORE INTO attendance_records (session_id, student_id, status, participation_score, behavior_notes) VALUES
((SELECT id FROM attendance_sessions WHERE course_id=(SELECT id FROM courses WHERE code='CS101') AND session_date='2025-11-01'), (SELECT id FROM users WHERE username='student01'), 'present', 8, 'Good participation'),
((SELECT id FROM attendance_sessions WHERE course_id=(SELECT id FROM courses WHERE code='CS101') AND session_date='2025-11-01'), (SELECT id FROM users WHERE username='student02'), 'late', 6, 'Arrived 10 minutes late'),
((SELECT id FROM attendance_sessions WHERE course_id=(SELECT id FROM courses WHERE code='CS101') AND session_date='2025-11-01'), (SELECT id FROM users WHERE username='student08'), 'absent', 0, 'No show'),
((SELECT id FROM attendance_sessions WHERE course_id=(SELECT id FROM courses WHERE code='CS101') AND session_date='2025-11-02'), (SELECT id FROM users WHERE username='student03'), 'present', 7, NULL),
((SELECT id FROM attendance_sessions WHERE course_id=(SELECT id FROM courses WHERE code='MATH201') AND session_date='2025-11-03'), (SELECT id FROM users WHERE username='student04'), 'present', 9, 'Excellent'),
((SELECT id FROM attendance_sessions WHERE course_id=(SELECT id FROM courses WHERE code='PHYS301') AND session_date='2025-11-04'), (SELECT id FROM users WHERE username='student05'), 'absent', 0, 'Sick');

-- Add a justification request for a missing record (student08 for CS101 2025-11-01)
INSERT IGNORE INTO justifications (attendance_record_id, student_id, reason, file_path, status, submitted_at) VALUES
((SELECT ar.id FROM attendance_records ar JOIN attendance_sessions s ON ar.session_id = s.id JOIN users u ON ar.student_id = u.id WHERE u.username='student08' AND s.session_date='2025-11-01' LIMIT 1), (SELECT id FROM users WHERE username='student08'), 'Medical appointment', 'uploads/justifications/student08_note.pdf', 'pending', NOW());

-- End of seed file
