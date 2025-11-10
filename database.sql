-- EngBot Database Schema
-- สร้างฐานข้อมูลสำหรับ Chatbot คณะวิศวกรรมศาสตร์ RMUTP

CREATE DATABASE IF NOT EXISTS eng_chatbot CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE eng_chatbot;

-- ตาราง news: เก็บข่าวประชาสัมพันธ์
CREATE TABLE IF NOT EXISTS news (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(500) NOT NULL,
  summary TEXT,
  url VARCHAR(1000),
  date_post DATE,
  date_created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  source VARCHAR(255) DEFAULT 'manual' COMMENT 'manual, scraped',
  is_active TINYINT(1) DEFAULT 1,
  INDEX idx_date (date_post),
  INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ตาราง staff: เก็บข้อมูลบุคลากร
CREATE TABLE IF NOT EXISTS staff (
  id INT AUTO_INCREMENT PRIMARY KEY,
  fullname VARCHAR(255) NOT NULL,
  title VARCHAR(100) COMMENT 'ตำแหน่งทางวิชาการ เช่น ผศ., รศ., ดร.',
  department VARCHAR(255) COMMENT 'สาขาวิชา',
  role VARCHAR(255) COMMENT 'หัวหน้าสาขา, อาจารย์ประจำ',
  email VARCHAR(255),
  phone VARCHAR(50),
  office VARCHAR(255) COMMENT 'ห้องทำงาน',
  expertise TEXT COMMENT 'ความเชี่ยวชาญ',
  photo_url VARCHAR(500),
  date_created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  is_active TINYINT(1) DEFAULT 1,
  INDEX idx_name (fullname),
  INDEX idx_dept (department),
  INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ตาราง faq: คำถาม-คำตอบที่พบบ่อย
CREATE TABLE IF NOT EXISTS faq (
  id INT AUTO_INCREMENT PRIMARY KEY,
  question TEXT NOT NULL,
  answer TEXT NOT NULL,
  category VARCHAR(100) COMMENT 'ติดต่อคณะ, ทุนการศึกษา, หลักสูตร',
  priority INT DEFAULT 0 COMMENT 'ความสำคัญในการแสดงผล',
  date_created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  date_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  is_active TINYINT(1) DEFAULT 1,
  INDEX idx_category (category),
  INDEX idx_active (is_active),
  FULLTEXT idx_question (question)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ตาราง faq_keyword: คีย์เวิร์ดสำหรับจับคู่คำถาม
CREATE TABLE IF NOT EXISTS faq_keyword (
  id INT AUTO_INCREMENT PRIMARY KEY,
  faq_id INT NOT NULL,
  keyword VARCHAR(255) NOT NULL,
  weight INT DEFAULT 1 COMMENT 'น้ำหนักของคีย์เวิร์ด',
  FOREIGN KEY (faq_id) REFERENCES faq(id) ON DELETE CASCADE,
  INDEX idx_keyword (keyword),
  INDEX idx_faq (faq_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ตาราง chat_logs: บันทึกประวัติการสนทนา
CREATE TABLE IF NOT EXISTS chat_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_message TEXT NOT NULL,
  bot_response TEXT,
  response_type VARCHAR(50) COMMENT 'text, news, staff, faq',
  matched_keywords TEXT COMMENT 'คีย์เวิร์ดที่จับได้',
  session_id VARCHAR(100),
  user_ip VARCHAR(50),
  date_created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_session (session_id),
  INDEX idx_date (date_created),
  FULLTEXT idx_message (user_message)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ตาราง contact_info: ข้อมูลติดต่อคณะ
CREATE TABLE IF NOT EXISTS contact_info (
  id INT AUTO_INCREMENT PRIMARY KEY,
  info_key VARCHAR(100) UNIQUE NOT NULL COMMENT 'เช่น phone, email, address, facebook',
  info_value TEXT NOT NULL,
  display_name VARCHAR(255) COMMENT 'ชื่อที่แสดง',
  icon VARCHAR(50) COMMENT 'ไอคอน emoji หรือ class',
  sort_order INT DEFAULT 0,
  is_active TINYINT(1) DEFAULT 1,
  date_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===== ข้อมูลตัวอย่าง =====

-- ข้อมูล news ตัวอย่าง
INSERT INTO news (title, summary, url, date_post, source) VALUES
('ทุนการศึกษาสำหรับนักศึกษาชั้นปีที่ 1', 'คณะวิศวกรรมศาสตร์เปิดรับสมัครทุนการศึกษาสำหรับนักศึกษาชั้นปีที่ 1 ที่มีผลการเรียนดี และมีความประพฤติเรียบร้อย', 'https://eng.rmutp.ac.th/scholarship', '2024-11-10', 'manual'),
('กิจกรรม Engineering Open House 2024', 'เปิดบ้านวิศวะ รับน้องใหม่ พบกับกิจกรรมมากมาย พร้อมให้คำปรึกษาการสมัครเรียน', 'https://eng.rmutp.ac.th/openhouse', '2024-11-08', 'manual'),
('ประกาศผลสอบกลางภาค ภาคเรียนที่ 1/2567', 'ประกาศผลสอบกลางภาค สามารถตรวจสอบผลสอบได้ที่ระบบทะเบียน', 'https://reg.rmutp.ac.th', '2024-11-05', 'manual');

-- ข้อมูล staff ตัวอย่าง
INSERT INTO staff (fullname, title, department, role, email, phone, office) VALUES
('รศ.ดร.สมชาย วิศวกรรม', 'รองศาสตราจารย์ ดร.', 'วิศวกรรมคอมพิวเตอร์', 'หัวหน้าสาขาวิชา', 'somchai.w@rmutp.ac.th', '02-555-0001', 'ห้อง EN-301'),
('ผศ.ดร.สมหญิง เทคโนโลยี', 'ผู้ช่วยศาสตราจารย์ ดร.', 'วิศวกรรมไฟฟ้า', 'หัวหน้าสาขาวิชา', 'somying.t@rmutp.ac.th', '02-555-0002', 'ห้อง EN-302'),
('ดร.วิศวะ อุตสาหการ', 'อาจารย์ ดร.', 'วิศวกรรมอุตสาหการ', 'หัวหน้าสาขาวิชา', 'wisawa.i@rmutp.ac.th', '02-555-0003', 'ห้อง EN-303'),
('อ.ธนพล โยธา', 'อาจารย์', 'วิศวกรรมโยธา', 'อาจารย์ประจำสาขา', 'thanapol.c@rmutp.ac.th', '02-555-0004', 'ห้อง EN-304');

-- ข้อมูล FAQ ตัวอย่าง
INSERT INTO faq (question, answer, category, priority) VALUES
('ติดต่อคณะวิศวกรรมศาสตร์ยังไง', 'ติดต่อได้ที่\n📞 โทร: 02-555-2000\n✉️ Email: eng@rmutp.ac.th\n📍 ที่อยู่่: 399 ถนนสามเสน แขวงวชิรพยาบาล เขตดุสิต กรุงเทพฯ 10300', 'ติดต่อคณะ', 10),
('สมัครเรียนวิศวะต้องทำอย่างไร', 'สมัครเรียนได้ผ่านระบบ TCAS ทั้ง 4 รอบ\n- รอบที่ 1: โควตา\n- รอบที่ 2: Portfolio\n- รอบที่ 3: Admission\n- รอบที่ 4: Direct Admission\nรายละเอียดเพิ่มเติม: https://admission.rmutp.ac.th', 'การรับสมัคร', 9),
('มีทุนการศึกษาไหม', 'มีทุนการศึกษาหลายประเภท:\n1. ทุนเรียนดี (เกรดเฉลี่ย 3.50 ขึ้นไป)\n2. ทุนขาดแคลนทุนทรัพย์\n3. ทุนกิจกรรม\n4. ทุนบริษัทเอกชน\nติดต่อสอบถาม: กองทุนการศึกษา โทร 02-555-2001', 'ทุนการศึกษา', 8),
('สาขาวิชาในคณะวิศวกรรมศาสตร์มีอะไรบ้าง', 'คณะวิศวกรรมศาสตร์มี 6 สาขาวิชา:\n1. วิศวกรรมคอมพิวเตอร์\n2. วิศวกรรมไฟฟ้า\n3. วิศวกรรมอิเล็กทรอนิกส์\n4. วิศวกรรมโยธา\n5. วิศวกรรมอุตสาหการ\n6. วิศวกรรมเครื่องกล', 'หลักสูตร', 7),
('ค่าเทอมวิศวะเท่าไหร่', 'ค่าเทอมประมาณ 16,000-18,000 บาท/ภาคเรียน (ขึ้นอยู่กับสาขาวิชา)\nค่าธรรมเนียมแรกเข้า 3,000 บาท (ครั้งแรกเท่านั้น)\nสามารถผ่อนชำระได้', 'ค่าใช้จ่าย', 6);

-- ข้อมูล faq_keyword
INSERT INTO faq_keyword (faq_id, keyword, weight) VALUES
(1, 'ติดต่อ', 10),
(1, 'โทร', 8),
(1, 'email', 8),
(1, 'ที่อยู่', 7),
(1, 'เบอร์', 7),
(2, 'สมัคร', 10),
(2, 'รับสมัคร', 9),
(2, 'TCAS', 8),
(2, 'เข้าเรียน', 7),
(3, 'ทุน', 10),
(3, 'ทุนการศึกษา', 10),
(3, 'เรียนฟรี', 7),
(3, 'ช่วยเหลือ', 6),
(4, 'สาขา', 10),
(4, 'สาขาวิชา', 10),
(4, 'หลักสูตร', 8),
(4, 'เรียนอะไร', 7),
(5, 'ค่าเทอม', 10),
(5, 'ค่าใช้จ่าย', 9),
(5, 'ราคา', 8),
(5, 'เงิน', 7);

-- ข้อมูล contact_info
INSERT INTO contact_info (info_key, info_value, display_name, icon, sort_order) VALUES
('phone', '02-555-2000', 'เบอร์โทรศัพท์', '📞', 1),
('email', 'eng@rmutp.ac.th', 'อีเมล', '✉️', 2),
('address', '399 ถนนสามเสน แขวงวชิรพยาบาล เขตดุสิต กรุงเทพฯ 10300', 'ที่อยู่', '📍', 3),
('website', 'https://eng.rmutp.ac.th', 'เว็บไซต์', '🌐', 4),
('facebook', 'https://facebook.com/rmutp.engineering', 'Facebook', '📘', 5),
('line', '@rmutpeng', 'LINE Official', '💬', 6),
('hours', 'จันทร์-ศุกร์ เวลา 08:30-16:30 น.', 'เวลาทำการ', '🕐', 7);

-- สร้าง View สำหรับ Statistics
CREATE OR REPLACE VIEW chat_statistics AS
SELECT 
  DATE(date_created) as chat_date,
  COUNT(*) as total_chats,
  COUNT(DISTINCT session_id) as unique_sessions,
  response_type,
  COUNT(CASE WHEN bot_response IS NULL THEN 1 END) as no_answer_count
FROM chat_logs
GROUP BY DATE(date_created), response_type;

-- สร้าง View สำหรับ Popular Questions
CREATE OR REPLACE VIEW popular_questions AS
SELECT 
  user_message,
  COUNT(*) as frequency,
  response_type,
  MAX(date_created) as last_asked
FROM chat_logs
WHERE LENGTH(user_message) > 3
GROUP BY user_message, response_type
ORDER BY frequency DESC
LIMIT 50;
