# 🚀 คู่มือการ Deploy ไปยัง RMUTP Server

## 📋 ข้อมูล Server

```
FTP Server:  203.158.145.147
Username:    anuwat-kl@shost.rmutp.ac.th
Password:    wbmbCmxVlN6mBtU

Database:    https://shost.rmutp.ac.th/phpmyadmin
Host:        localhost
Database:    anuwat-kl
Username:    anuwat-kl@shost.rmutp.ac.th
Password:    wbmbCmxVlN6mBtU
```

---

## 🔧 ขั้นตอนการ Deploy

### 1️⃣ เตรียมไฟล์

ไฟล์ที่ต้อง Upload ผ่าน FTP:

```
✅ chat.html
✅ chatbot.php
✅ db.php (ปรับแต่งแล้ว)
✅ admin.php
✅ admin_api.php
✅ scraper.php
✅ rmutp-logo.png
✅ rmutp-logo1.png
✅ README.md
✅ database.sql
```

---

### 2️⃣ Upload ไฟล์ผ่าน FTP

#### วิธีที่ 1: ใช้ FileZilla (แนะนำ)

1. **ดาวน์โหลด FileZilla**: https://filezilla-project.org/
2. **เชื่อมต่อ Server**:
   ```
   Host:     203.158.145.147
   Username: anuwat-kl@shost.rmutp.ac.th
   Password: wbmbCmxVlN6mBtU
   Port:     21
   ```
3. **Upload ไฟล์**:
   - ลากไฟล์ทั้งหมดไปที่ `/public_html/` หรือ `/public_html/engbot/`
   - รอให้ Upload เสร็จ (ประมาณ 2-3 นาที)

#### วิธีที่ 2: ใช้ WinSCP (Windows)

1. ดาวน์โหลด: https://winscp.net/
2. New Site → FTP → ใส่ข้อมูล Server
3. Connect และ Upload ไฟล์

#### วิธีที่ 3: ใช้ Command Line (Advanced)

```bash
# Windows Command Prompt
ftp 203.158.145.147
# Username: anuwat-kl@shost.rmutp.ac.th
# Password: wbmbCmxVlN6mBtU

cd public_html
mput *.php *.html *.png *.sql
bye
```

---

### 3️⃣ สร้างฐานข้อมูล

1. **เข้า phpMyAdmin**: https://shost.rmutp.ac.th/phpmyadmin
   ```
   Username: anuwat-kl@shost.rmutp.ac.th
   Password: wbmbCmxVlN6mBtU
   ```

2. **เลือกฐานข้อมูล**: `anuwat-kl` (มีอยู่แล้ว)

3. **Import database.sql**:
   - คลิก **Import**
   - เลือกไฟล์ `database.sql`
   - Character set: `utf8mb4_unicode_ci`
   - คลิก **Go**

4. **ตรวจสอบตาราง**:
   ```
   ✅ news
   ✅ staff
   ✅ faq
   ✅ faq_keyword
   ✅ chat_logs
   ✅ contact_info
   ```

---

### 4️⃣ ตั้งค่า Permissions (สิทธิ์การเข้าถึง)

ใช้ FileZilla หรือ FTP Client ตั้งค่า:

```
✅ chat.html       → 644 (rw-r--r--)
✅ chatbot.php     → 644
✅ admin.php       → 644
✅ admin_api.php   → 644
✅ scraper.php     → 644
✅ db.php          → 600 (rw-------) ← ปลอดภัย
✅ *.png           → 644
```

**คำสั่งตั้งค่า (ถ้าใช้ SSH)**:
```bash
chmod 644 *.html *.php
chmod 600 db.php
chmod 644 *.png
```

---

### 5️⃣ ทดสอบระบบ

#### ✅ ทดสอบ Chatbot
```
https://shost.rmutp.ac.th/~anuwat-kl/chat.html
หรือ
http://203.158.145.147/~anuwat-kl/chat.html
```

#### ✅ ทดสอบ API
```
https://shost.rmutp.ac.th/~anuwat-kl/chatbot.php?msg=ข่าว
```

#### ✅ ทดสอบ Admin Panel
```
https://shost.rmutp.ac.th/~anuwat-kl/admin.php
```

---

## 🔐 เพิ่มความปลอดภัยให้ Admin Panel

### สร้างไฟล์ `.htaccess` ป้องกัน Admin

สร้างไฟล์ `.htaccess` ใน folder เดียวกับ `admin.php`:

```apache
# .htaccess - Protect Admin Panel
AuthType Basic
AuthName "Admin Access Only"
AuthUserFile /home/anuwat-kl/.htpasswd
Require valid-user

# Protect db.php
<Files "db.php">
    Order Allow,Deny
    Deny from all
</Files>
```

### สร้างรหัสผ่าน `.htpasswd`

```bash
# ใช้ Tool online: https://www.web2generators.com/apache-tools/htpasswd-generator
# หรือใช้ command:
htpasswd -c .htpasswd admin
```

Upload ไฟล์ `.htpasswd` ไปที่ `/home/anuwat-kl/` (นอก public_html)

---

## 🔄 ตั้งค่า Cron Job (Auto-update)

### เข้า cPanel หรือ Server Panel

1. ไปที่ **Cron Jobs**
2. เพิ่ม Cron Job ใหม่:

```bash
# รันทุก 6 ชั่วโมง เพื่ออัปเดตข้อมูล
0 */6 * * * /usr/bin/php /home/anuwat-kl/public_html/scraper.php?action=all
```

หรือใช้ `curl`:
```bash
0 */6 * * * curl https://shost.rmutp.ac.th/~anuwat-kl/scraper.php?action=all
```

### ตารางเวลา Cron

```
# รันทุกวันเวลา 8:00 น.
0 8 * * * /usr/bin/php /path/to/scraper.php?action=news

# รันทุก 3 ชั่วโมง
0 */3 * * * curl https://...scraper.php?action=all

# รันทุกวันจันทร์ เวลา 9:00 น.
0 9 * * 1 /usr/bin/php /path/to/scraper.php?action=staff
```

---

## 🌐 URL สำหรับใช้งาน

### 🎯 URL หลัก (แนะนำ)
```
https://shost.rmutp.ac.th/~anuwat-kl/chat.html
```

### 🔧 API Endpoints
```
Chatbot API:
https://shost.rmutp.ac.th/~anuwat-kl/chatbot.php?msg=คำถาม

Admin API:
https://shost.rmutp.ac.th/~anuwat-kl/admin_api.php?action=stats

Web Scraper:
https://shost.rmutp.ac.th/~anuwat-kl/scraper.php?action=all
```

---

## ✅ Checklist หลัง Deploy

- [ ] ✅ Upload ไฟล์ทั้งหมดผ่าน FTP
- [ ] ✅ Import `database.sql` ใน phpMyAdmin
- [ ] ✅ ตั้งค่า Permissions ของไฟล์
- [ ] ✅ ทดสอบ `chat.html` ในเบราว์เซอร์
- [ ] ✅ ทดสอบ `chatbot.php?msg=ข่าว`
- [ ] ✅ ทดสอบ `admin.php`
- [ ] ✅ เพิ่มข้อมูลจริงใน Admin Panel
- [ ] ✅ ตั้งค่า `.htaccess` ป้องกัน Admin
- [ ] ✅ ตั้ง Cron Job สำหรับ Auto-update
- [ ] ✅ ทดสอบการทำงานทั้งหมด

---

## 🐛 แก้ปัญหาที่พบบ่อย

### ❌ Error: Connection failed

**สาเหตุ**: ฐานข้อมูลยังไม่พร้อม

**แก้ไข**:
1. ตรวจสอบ `db.php` ว่าข้อมูลถูกต้อง
2. ตรวจสอบว่า Import `database.sql` เสร็จแล้ว
3. ลองเข้า phpMyAdmin ตรวจสอบตาราง

---

### ❌ ภาษาไทยแสดงผิด (??????)

**แก้ไข**:
1. ใน phpMyAdmin:
   ```sql
   ALTER DATABASE `anuwat-kl` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

2. เพิ่มใน `db.php`:
   ```php
   $conn->set_charset("utf8mb4");
   ```

---

### ❌ 403 Forbidden / 500 Internal Error

**สาเหตุ**: Permissions ไม่ถูกต้อง

**แก้ไข**:
```bash
chmod 644 *.php *.html
chmod 755 public_html
```

---

### ❌ Web Scraper ไม่ทำงาน

**สาเหตุ**: PHP cURL ไม่เปิดใช้งาน

**แก้ไข**:
1. ติดต่อ Admin Server เพื่อเปิด `php-curl`
2. หรือใช้ `file_get_contents()` แทน cURL:
   ```php
   $html = file_get_contents($url);
   ```

---

## 📱 QR Code สำหรับเข้าใช้งาน

สร้าง QR Code ที่ https://www.qr-code-generator.com/

```
URL: https://shost.rmutp.ac.th/~anuwat-kl/chat.html
```

พิมพ์ปะที่ป้ายประชาสัมพันธ์คณะ!

---

## 🚀 อัปเดตระบบในอนาคต

### วิธีอัปเดตไฟล์

1. **แก้ไขไฟล์ใน Local** (XAMPP)
2. **Upload ผ่าน FTP** เฉพาะไฟล์ที่แก้
3. **ทดสอบ** บน Server จริง

### สำรองข้อมูล

```sql
-- Export ฐานข้อมูลเป็นไฟล์ SQL
-- ผ่าน phpMyAdmin > Export
-- เก็บไฟล์ไว้สำรอง
```

---

## 📞 ติดต่อเมื่อมีปัญหา

- 🌐 cPanel/Server Support: https://shost.rmutp.ac.th/support
- 📧 IT Support: it@rmutp.ac.th
- 📱 โทร: 02-555-xxxx

---

## ✨ เสร็จสิ้น!

ระบบพร้อมใช้งานบน Server จริงแล้ว! 🎉

```
🔗 URL: https://shost.rmutp.ac.th/~anuwat-kl/chat.html
```

**Good luck!** 🚀
