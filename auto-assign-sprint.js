const https = require('https');

// ==========================================
// THÔNG TIN CẤU HÌNH (Sửa lại cho đúng của bạn)
// ==========================================
const JIRA_DOMAIN = "kien7708.atlassian.net"; // Đổi domain nếu cần
const JIRA_EMAIL = "nhutp2945@gmail.com"; 
const JIRA_API_TOKEN = process.env.JIRA_API_TOKEN || "YOUR_JIRA_API_TOKEN"; 

const PROJECT_KEY = "ESQ";
const BOARD_ID = "37"; // Tự động lấy Board ID chuẩn của dự án ESQ

const auth = Buffer.from(`${JIRA_EMAIL}:${JIRA_API_TOKEN}`).toString("base64");

// Hàm hỗ trợ gọi API
function fetchJson(method, path, body = null) {
  return new Promise((resolve, reject) => {
    const options = {
      hostname: JIRA_DOMAIN,
      path: path,
      method: method,
      headers: {
        "Authorization": `Basic ${auth}`,
        "Content-Type": "application/json",
        "Accept": "application/json"
      }
    };
    const req = https.request(options, (res) => {
      let data = '';
      res.on('data', chunk => data += chunk);
      res.on('end', () => {
        try { resolve(data ? JSON.parse(data) : {}); } catch (e) { resolve({}); }
      });
    });
    req.on('error', reject);
    if (body) req.write(JSON.stringify(body));
    req.end();
  });
}

async function run() {
  console.log("1️⃣ Đang lấy danh sách các Sprint từ Jira...");
  const sprintData = await fetchJson("GET", `/rest/agile/1.0/board/${BOARD_ID}/sprint`);
  
  if (!sprintData.values || sprintData.values.length === 0) {
    console.log("❌ Không tìm thấy Sprint nào! Hãy kiểm tra lại BOARD_ID.");
    return;
  }

  // Lọc ra các Sprint và sắp xếp theo ID (hoặc thời gian)
  const sprints = sprintData.values.sort((a, b) => a.id - b.id);
  console.log(`✅ Đã tìm thấy ${sprints.length} Sprints trên dự án:`);
  sprints.forEach(s => console.log(`   - [ID: ${s.id}] ${s.name} (Ngày: ${s.startDate ? s.startDate.split('T')[0] : 'N/A'} -> ${s.endDate ? s.endDate.split('T')[0] : 'N/A'})`));

  console.log("\n2️⃣ Đang lấy danh sách toàn bộ các thẻ Bug trong dự án...");
  const searchBody = {
    jql: `project=${PROJECT_KEY} AND issuetype=Bug ORDER BY created ASC`,
    maxResults: 200,
    fields: ["summary", "created"]
  };
  const search = await fetchJson("POST", `/rest/api/3/search/jql`, searchBody);
  const issues = search.issues || [];
  
  console.log(`✅ Tìm thấy ${issues.length} thẻ Bug. Bắt đầu chia đều vào ${sprints.length} Sprint...`);

  let sprintIndex = 0;

  for (const issue of issues) {
    // Lấy Sprint hiện tại trong vòng lặp (Xoay vòng rải đều các Bug vào các Sprint từ cũ đến mới)
    const targetSprint = sprints[sprintIndex % sprints.length];
    sprintIndex++;

    const assignedSprintId = targetSprint.id;
    // Lấy Start Date và End Date đã được cấu hình sẵn của Sprint đó trên Jira
    const startDate = targetSprint.startDate ? targetSprint.startDate.split('T')[0] : "2026-05-10";
    const dueDate = targetSprint.endDate ? targetSprint.endDate.split('T')[0] : "2026-05-17";

    // Gửi lệnh update Sprint & Ngày lên Jira
    const updateBody = { 
      fields: {
        customfield_10020: assignedSprintId, // Cập nhật Sprint ID
        customfield_10015: startDate,        // Cập nhật Ngày Bắt Đầu
        duedate: dueDate                     // Cập nhật Ngày Kết Thúc
      } 
    };
    
    await fetchJson("PUT", `/rest/api/3/issue/${issue.key}`, updateBody);
    console.log(`🎯 Đã đẩy thẻ [${issue.key}] vào Sprint: ${targetSprint.name} (${startDate} -> ${dueDate})`);
  }
  
  console.log("🎉 Hoàn tất! Lên F5 lại trang Jira kiểm tra nhé.");
}

run();
