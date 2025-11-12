<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="utf-8">
<title>Admin Panel - EngBot</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body {
    font-family: 'Kanit', sans-serif;
    background: #f5f7fa;
    color: #333;
  }
  .container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 20px;
  }
  header {
    background: linear-gradient(135deg, #a90d2c, #8b0823);
    color: white;
    padding: 30px;
    border-radius: 12px;
    margin-bottom: 30px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
  }
  header h1 { font-size: 2em; margin-bottom: 10px; }
  header p { opacity: 0.9; font-size: 1.1em; }
  
  .tabs {
    display: flex;
    gap: 10px;
    margin-bottom: 20px;
    flex-wrap: wrap;
  }
  .tab {
    background: white;
    border: none;
    padding: 12px 24px;
    border-radius: 8px;
    cursor: pointer;
    font-family: 'Kanit', sans-serif;
    font-size: 1em;
    font-weight: 500;
    transition: all 0.3s;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
  }
  .tab:hover { background: #f0f0f0; }
  .tab.active {
    background: #a90d2c;
    color: white;
  }
  
  .tab-content {
    display: none;
    background: white;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
  }
  .tab-content.active { display: block; }
  
  .stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
  }
  .stat-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
  }
  .stat-card.red { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
  .stat-card.green { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
  .stat-card.orange { background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); }
  .stat-card h3 { font-size: 2.5em; margin-bottom: 5px; }
  .stat-card p { opacity: 0.9; font-size: 1.1em; }
  
  table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
  }
  table th, table td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #eee;
  }
  table th {
    background: #f8f9fa;
    font-weight: 600;
    color: #555;
  }
  table tr:hover { background: #f8f9fa; }
  
  .btn {
    background: #a90d2c;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 6px;
    cursor: pointer;
    font-family: 'Kanit', sans-serif;
    font-size: 0.95em;
    transition: all 0.3s;
    margin-right: 5px;
  }
  .btn:hover { background: #8b0823; }
  .btn-sm { padding: 6px 12px; font-size: 0.85em; }
  .btn-success { background: #28a745; }
  .btn-success:hover { background: #218838; }
  .btn-danger { background: #dc3545; }
  .btn-danger:hover { background: #c82333; }
  
  .form-group {
    margin-bottom: 20px;
  }
  .form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 500;
    color: #555;
  }
  .form-group input, .form-group textarea, .form-group select {
    width: 100%;
    padding: 10px;
    border: 2px solid #e0e0e0;
    border-radius: 6px;
    font-family: 'Kanit', sans-serif;
    font-size: 1em;
    transition: border-color 0.3s;
  }
  .form-group input:focus, .form-group textarea:focus, .form-group select:focus {
    outline: none;
    border-color: #a90d2c;
  }
  .form-group textarea { min-height: 120px; }
  
  .alert {
    padding: 15px 20px;
    border-radius: 8px;
    margin-bottom: 20px;
    display: none;
  }
  .alert.success { background: #d4edda; color: #155724; border-left: 4px solid #28a745; }
  .alert.error { background: #f8d7da; color: #721c24; border-left: 4px solid #dc3545; }
  .alert.show { display: block; }
  
  .chart-container {
    margin-top: 30px;
    padding: 20px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
  }
  
  .question-list {
    max-height: 400px;
    overflow-y: auto;
  }
  .question-item {
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
    margin-bottom: 10px;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }
  .question-item:hover { background: #e9ecef; }
  
  .badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 0.85em;
    font-weight: 500;
  }
  .badge-primary { background: #e3f2fd; color: #1976d2; }
  .badge-success { background: #e8f5e9; color: #388e3c; }
  .badge-warning { background: #fff3e0; color: #f57c00; }
</style>
</head>
<body>

<div class="container">
  <header>
    <h1>🤖 EngBot Admin Panel</h1>
    <p>ระบบจัดการ Chatbot คณะวิศวกรรมศาสตร์ RMUTP</p>
  </header>

  <div class="tabs">
    <button class="tab active" onclick="showTab('dashboard')">📊 Dashboard</button>
    <button class="tab" onclick="showTab('faq')">❓ FAQ</button>
    <button class="tab" onclick="showTab('news')">📰 ข่าวประชาสัมพันธ์</button>
    <button class="tab" onclick="showTab('staff')">👥 บุคลากร</button>
    <button class="tab" onclick="showTab('logs')">📝 Chat Logs</button>
    <button class="tab" onclick="showTab('scraper')">🔄 Web Scraper</button>
  </div>

  <!-- Dashboard Tab -->
  <div id="dashboard" class="tab-content active">
    <h2>📊 สถิติภาพรวม</h2>
    <div class="stats-grid">
      <div class="stat-card">
        <h3 id="totalChats">-</h3>
        <p>การสนทนาทั้งหมด</p>
      </div>
      <div class="stat-card red">
        <h3 id="totalFAQ">-</h3>
        <p>คำถาม-คำตอบ (FAQ)</p>
      </div>
      <div class="stat-card green">
        <h3 id="totalNews">-</h3>
        <p>ข่าวประชาสัมพันธ์</p>
      </div>
      <div class="stat-card orange">
        <h3 id="totalStaff">-</h3>
        <p>บุคลากร</p>
      </div>
    </div>

    <h3>🔥 คำถามยอดนิยม (Top 10)</h3>
    <div class="question-list" id="popularQuestions">
      <p>กำลังโหลด...</p>
    </div>
  </div>

  <!-- FAQ Tab -->
  <div id="faq" class="tab-content">
    <h2>❓ จัดการ FAQ</h2>
    <button class="btn" onclick="showAddFAQ()">+ เพิ่ม FAQ ใหม่</button>
    
    <div id="faqForm" style="display:none; margin-top:20px; padding:20px; background:#f8f9fa; border-radius:8px;">
      <h3>เพิ่ม/แก้ไข FAQ</h3>
      <div class="form-group">
        <label>คำถาม</label>
        <input type="text" id="faqQuestion" placeholder="ตัวอย่าง: ติดต่อคณะวิศวกรรมศาสตร์ยังไง">
      </div>
      <div class="form-group">
        <label>คำตอบ</label>
        <textarea id="faqAnswer" placeholder="ใส่คำตอบ..."></textarea>
      </div>
      <div class="form-group">
        <label>หมวดหมู่</label>
        <input type="text" id="faqCategory" placeholder="เช่น: ติดต่อคณะ, ทุนการศึกษา">
      </div>
      <div class="form-group">
        <label>คำสำคัญ (คั่นด้วยเครื่องหมาย ,)</label>
        <input type="text" id="faqKeywords" placeholder="เช่น: ติดต่อ, โทร, email">
      </div>
      <button class="btn" onclick="saveFAQ()">💾 บันทึก</button>
      <button class="btn btn-danger" onclick="cancelFAQ()">❌ ยกเลิก</button>
    </div>

    <div id="alertFAQ" class="alert"></div>
    <table id="faqTable">
      <thead>
        <tr>
          <th>คำถาม</th>
          <th>หมวดหมู่</th>
          <th>สถานะ</th>
          <th>จัดการ</th>
        </tr>
      </thead>
      <tbody></tbody>
    </table>
  </div>

  <!-- News Tab -->
  <div id="news" class="tab-content">
    <h2>📰 จัดการข่าวประชาสัมพันธ์</h2>
    <button class="btn" onclick="showAddNews()">+ เพิ่มข่าวใหม่</button>
    
    <div id="newsForm" style="display:none; margin-top:20px; padding:20px; background:#f8f9fa; border-radius:8px;">
      <h3>เพิ่ม/แก้ไข ข่าว</h3>
      <div class="form-group">
        <label>หัวข้อข่าว</label>
        <input type="text" id="newsTitle">
      </div>
      <div class="form-group">
        <label>สรุปข่าว</label>
        <textarea id="newsSummary"></textarea>
      </div>
      <div class="form-group">
        <label>URL ลิงก์</label>
        <input type="text" id="newsUrl" placeholder="https://eng.rmutp.ac.th/...">
      </div>
      <div class="form-group">
        <label>วันที่โพสต์</label>
        <input type="date" id="newsDate" value="<?= date('Y-m-d') ?>">
      </div>
      <button class="btn" onclick="saveNews()">💾 บันทึก</button>
      <button class="btn btn-danger" onclick="cancelNews()">❌ ยกเลิก</button>
    </div>

    <div id="alertNews" class="alert"></div>
    <table id="newsTable">
      <thead>
        <tr>
          <th>หัวข้อ</th>
          <th>วันที่</th>
          <th>แหล่งที่มา</th>
          <th>สถานะ</th>
          <th>จัดการ</th>
        </tr>
      </thead>
      <tbody></tbody>
    </table>
  </div>

  <!-- Staff Tab -->
  <div id="staff" class="tab-content">
    <h2>👥 จัดการข้อมูลบุคลากร</h2>
    <button class="btn" onclick="showAddStaff()">+ เพิ่มบุคลากรใหม่</button>
    
    <div id="staffForm" style="display:none; margin-top:20px; padding:20px; background:#f8f9fa; border-radius:8px;">
      <h3>เพิ่ม/แก้ไข บุคลากร</h3>
      <div class="form-group">
        <label>ชื่อ-นามสกุล</label>
        <input type="text" id="staffName">
      </div>
      <div class="form-group">
        <label>ตำแหน่งทางวิชาการ</label>
        <input type="text" id="staffTitle" placeholder="เช่น: รศ.ดร., ผศ.ดร., อ.">
      </div>
      <div class="form-group">
        <label>สาขาวิชา</label>
        <input type="text" id="staffDept">
      </div>
      <div class="form-group">
        <label>บทบาท</label>
        <input type="text" id="staffRole" placeholder="เช่น: หัวหน้าสาขาวิชา, อาจารย์ประจำ">
      </div>
      <div class="form-group">
        <label>อีเมล</label>
        <input type="email" id="staffEmail">
      </div>
      <div class="form-group">
        <label>เบอร์โทร</label>
        <input type="text" id="staffPhone">
      </div>
      <button class="btn" onclick="saveStaff()">💾 บันทึก</button>
      <button class="btn btn-danger" onclick="cancelStaff()">❌ ยกเลิก</button>
    </div>

    <div id="alertStaff" class="alert"></div>
    <table id="staffTable">
      <thead>
        <tr>
          <th>ชื่อ-นามสกุล</th>
          <th>ตำแหน่ง</th>
          <th>สาขา</th>
          <th>อีเมล</th>
          <th>จัดการ</th>
        </tr>
      </thead>
      <tbody></tbody>
    </table>
  </div>

  <!-- Chat Logs Tab -->
  <div id="logs" class="tab-content">
    <h2>📝 ประวัติการสนทนา</h2>
    <div style="margin-bottom:20px;">
      <label>กรองตาม: </label>
      <select id="logFilter" onchange="loadLogs()">
        <option value="all">ทั้งหมด</option>
        <option value="no_match">ไม่พบคำตอบ</option>
        <option value="faq">FAQ</option>
        <option value="news">ข่าว</option>
        <option value="staff">บุคลากร</option>
      </select>
    </div>
    <table id="logsTable">
      <thead>
        <tr>
          <th>เวลา</th>
          <th>คำถาม</th>
          <th>ประเภทคำตอบ</th>
          <th>Session ID</th>
        </tr>
      </thead>
      <tbody></tbody>
    </table>
  </div>

  <!-- Scraper Tab -->
  <div id="scraper" class="tab-content">
    <h2>🔄 Web Scraper - อัปเดตข้อมูลอัตโนมัติ</h2>
    <p style="color:#666; margin-bottom:20px;">ดึงข้อมูลข่าวและบุคลากรจากเว็บไซต์คณะวิศวกรรมศาสตร์</p>
    
    <div style="display:flex; gap:15px; margin-bottom:30px; flex-wrap:wrap;">
      <button class="btn btn-success" onclick="runScraper('news', { category: 'pr' })">🔄 ดึงข่าวประชาสัมพันธ์</button>
      <button class="btn btn-success" onclick="runScraper('news', { category: 'activities' })">🔄 ดึงข่าวกิจกรรม</button>
      <button class="btn" onclick="runScraper('news')">🔄 ดึงข่าวทั้งหมด</button>
      <button class="btn btn-success" onclick="runScraper('staff')">🔄 ดึงบุคลากร</button>
      <button class="btn" onclick="runScraper('all')">🔄 ดึงทั้งหมด</button>
    </div>

    <div id="scraperResult" style="padding:20px; background:#f8f9fa; border-radius:8px; display:none;">
      <h3>ผลการดึงข้อมูล:</h3>
      <pre id="scraperOutput" style="white-space:pre-wrap; font-family:monospace;"></pre>
    </div>
  </div>

</div>

<script>
// Tab switching
function showTab(tabName) {
  document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
  document.querySelectorAll('.tab').forEach(el => el.classList.remove('active'));
  document.getElementById(tabName).classList.add('active');
  event.target.classList.add('active');
  
  // Load data when tab is opened
  if(tabName === 'faq') loadFAQs();
  if(tabName === 'news') loadNews();
  if(tabName === 'staff') loadStaff();
  if(tabName === 'logs') loadLogs();
}

// Load dashboard statistics
function loadDashboard() {
  fetch('admin_api.php?action=stats')
    .then(r => r.json())
    .then(data => {
      document.getElementById('totalChats').textContent = data.total_chats || 0;
      document.getElementById('totalFAQ').textContent = data.total_faq || 0;
      document.getElementById('totalNews').textContent = data.total_news || 0;
      document.getElementById('totalStaff').textContent = data.total_staff || 0;
      
      // Load popular questions
      if(data.popular_questions) {
        let html = '';
        data.popular_questions.forEach(q => {
          html += `<div class="question-item">
            <div>
              <strong>${q.user_message}</strong><br>
              <small style="color:#666;">ถูกถาม ${q.frequency} ครั้ง</small>
            </div>
            <span class="badge badge-primary">${q.response_type}</span>
          </div>`;
        });
        document.getElementById('popularQuestions').innerHTML = html;
      }
    });
}

// FAQ Management
let currentFAQId = null;
let currentStaffId = null;
let staffCache = [];
function showAddFAQ() {
  document.getElementById('faqForm').style.display = 'block';
  currentFAQId = null;
  document.getElementById('faqQuestion').value = '';
  document.getElementById('faqAnswer').value = '';
  document.getElementById('faqCategory').value = '';
  document.getElementById('faqKeywords').value = '';
}

function cancelFAQ() {
  document.getElementById('faqForm').style.display = 'none';
}

function saveFAQ() {
  const data = {
    question: document.getElementById('faqQuestion').value,
    answer: document.getElementById('faqAnswer').value,
    category: document.getElementById('faqCategory').value,
    keywords: document.getElementById('faqKeywords').value
  };
  
  const url = currentFAQId ? `admin_api.php?action=update_faq&id=${currentFAQId}` : 'admin_api.php?action=add_faq';
  
  fetch(url, {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify(data)
  })
  .then(r => r.json())
  .then(result => {
    showAlert('alertFAQ', result.success ? 'success' : 'error', result.message);
    if(result.success) {
      cancelFAQ();
      loadFAQs();
    }
  });
}

function loadFAQs() {
  fetch('admin_api.php?action=list_faq')
    .then(r => r.json())
    .then(data => {
      let html = '';
      data.items.forEach(faq => {
        html += `<tr>
          <td>${faq.question}</td>
          <td><span class="badge badge-primary">${faq.category || '-'}</span></td>
          <td>${faq.is_active ? '<span class="badge badge-success">ใช้งาน</span>' : '<span class="badge badge-warning">ปิด</span>'}</td>
          <td>
            <button class="btn btn-sm" onclick="editFAQ(${faq.id})">แก้ไข</button>
            <button class="btn btn-sm btn-danger" onclick="deleteFAQ(${faq.id})">ลบ</button>
          </td>
        </tr>`;
      });
      document.querySelector('#faqTable tbody').innerHTML = html;
    });
}

// Similar functions for News and Staff...
function showAddNews() { document.getElementById('newsForm').style.display = 'block'; }
function cancelNews() { document.getElementById('newsForm').style.display = 'none'; }

function showAddStaff(staff = null) {
  document.getElementById('staffForm').style.display = 'block';
  currentStaffId = staff ? staff.id : null;
  document.getElementById('staffName').value = staff ? staff.fullname : '';
  document.getElementById('staffTitle').value = staff ? (staff.title || '') : '';
  document.getElementById('staffDept').value = staff ? (staff.department || '') : '';
  document.getElementById('staffRole').value = staff ? (staff.role || '') : '';
  document.getElementById('staffEmail').value = staff ? (staff.email || '') : '';
  document.getElementById('staffPhone').value = staff ? (staff.phone || '') : '';
}

function cancelStaff() {
  document.getElementById('staffForm').style.display = 'none';
  currentStaffId = null;
  document.getElementById('staffName').value = '';
  document.getElementById('staffTitle').value = '';
  document.getElementById('staffDept').value = '';
  document.getElementById('staffRole').value = '';
  document.getElementById('staffEmail').value = '';
  document.getElementById('staffPhone').value = '';
}

function loadNews() {
  fetch('admin_api.php?action=list_news')
    .then(r => r.json())
    .then(data => {
      let html = '';
      data.items.forEach(news => {
        html += `<tr>
          <td>${news.title}</td>
          <td>${news.date_post}</td>
          <td><span class="badge badge-primary">${news.source}</span></td>
          <td>${news.is_active ? '<span class="badge badge-success">ใช้งาน</span>' : ''}</td>
          <td>
            <button class="btn btn-sm btn-danger" onclick="deleteNews(${news.id})">ลบ</button>
          </td>
        </tr>`;
      });
      document.querySelector('#newsTable tbody').innerHTML = html;
    });
}

function loadStaff() {
  fetch('admin_api.php?action=list_staff')
    .then(r => r.json())
    .then(data => {
      let html = '';
      staffCache = data.items || [];
      staffCache.forEach((staff, idx) => {
        html += `<tr>
          <td>${staff.fullname}</td>
          <td>${staff.title || '-'}</td>
          <td>${staff.department || '-'}</td>
          <td>${staff.email || '-'}</td>
          <td>
            <button class="btn btn-sm" onclick="editStaff(${idx})">แก้ไข</button>
            <button class="btn btn-sm btn-danger" onclick="deleteStaff(${staff.id})">ลบ</button>
          </td>
        </tr>`;
      });
      document.querySelector('#staffTable tbody').innerHTML = html;
    });
}

function editStaff(index) {
  const staff = staffCache[index];
  if(staff) {
    showAddStaff(staff);
  }
}

function saveStaff() {
  const data = {
    fullname: document.getElementById('staffName').value.trim(),
    title: document.getElementById('staffTitle').value.trim(),
    department: document.getElementById('staffDept').value.trim(),
    role: document.getElementById('staffRole').value.trim(),
    email: document.getElementById('staffEmail').value.trim(),
    phone: document.getElementById('staffPhone').value.trim()
  };

  if(!data.fullname) {
    showAlert('alertStaff', 'error', 'กรุณากรอกชื่อ-นามสกุล');
    return;
  }

  const url = currentStaffId ? `admin_api.php?action=update_staff&id=${currentStaffId}` : 'admin_api.php?action=add_staff';

  fetch(url, {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify(data)
  })
  .then(r => r.json())
  .then(result => {
    showAlert('alertStaff', result.success ? 'success' : 'error', result.message || 'เกิดข้อผิดพลาด');
    if(result.success) {
      cancelStaff();
      loadStaff();
    }
  });
}

function deleteStaff(id) {
  if(!confirm('ยืนยันการลบบุคลากรนี้?')) return;
  fetch(`admin_api.php?action=delete_staff&id=${id}`)
    .then(r => r.json())
    .then(result => {
      showAlert('alertStaff', result.success ? 'success' : 'error', result.message || 'เกิดข้อผิดพลาด');
      if(result.success) loadStaff();
    });
}

function loadLogs() {
  const filter = document.getElementById('logFilter').value;
  fetch(`admin_api.php?action=logs&filter=${filter}`)
    .then(r => r.json())
    .then(data => {
      let html = '';
      data.items.forEach(log => {
        html += `<tr>
          <td>${log.date_created}</td>
          <td>${log.user_message}</td>
          <td><span class="badge badge-primary">${log.response_type}</span></td>
          <td><small>${log.session_id}</small></td>
        </tr>`;
      });
      document.querySelector('#logsTable tbody').innerHTML = html;
    });
}

function runScraper(type, options = {}) {
  document.getElementById('scraperResult').style.display = 'block';
  document.getElementById('scraperOutput').textContent = 'กำลังดึงข้อมูล...';
  
  const params = new URLSearchParams({ action: type });
  if(options.category) {
    params.append('category', options.category);
  }

  fetch(`scraper.php?${params.toString()}`)
    .then(async response => {
      const text = await response.text();
      let data;
      try {
        data = JSON.parse(text);
      } catch (err) {
        throw new Error(text || 'ไม่สามารถอ่านผลลัพธ์ได้');
      }
      if(!response.ok || !data.success) {
        throw new Error(data.message || 'เกิดข้อผิดพลาดขณะดึงข้อมูล');
      }
      return data;
    })
    .then(data => {
      let display = JSON.stringify(data, null, 2);
      if(data.data && Array.isArray(data.data.warnings) && data.data.warnings.length) {
        display += '\n\n⚠️ คำเตือน:\n- ' + data.data.warnings.join('\n- ');
      }
      document.getElementById('scraperOutput').textContent = display + '\n\n✅ ' + (data.message || 'ดึงข้อมูลสำเร็จ');
    })
    .catch(err => {
      document.getElementById('scraperOutput').textContent = `❌ Error: ${err.message}`;
    });
}

function showAlert(id, type, msg) {
  const el = document.getElementById(id);
  el.className = `alert ${type} show`;
  el.textContent = msg;
  setTimeout(() => el.classList.remove('show'), 3000);
}

// Load dashboard on page load
loadDashboard();
</script>

</body>
</html>
