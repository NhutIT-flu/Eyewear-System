> ✅ **PROJECT VERIFIED & CI/CD READY**
> *Hệ thống đã được nâng cấp lên chuẩn Enterprise với 284 kịch bản PHPUnit Tests (Coverage > 66.9%) và tích hợp hoàn toàn vào CI/CD Pipeline (Jenkins & GitHub Actions). Vui lòng tham khảo thêm tại `Testing_Architecture_Overview.md` và `jenkins-ci-guide.md` để biết cấu trúc kiểm thử mới nhất.*

# ⚙️ Member 5 — Operations, Logistics & Dashboard

**Module Tag**: `M5-OPS`  
**Priority**: 🔵 Medium-Low (Final stage of lifecycle)

---

## 📋 Scope Overview

This member owns the **back-office workflow**: Production (lens cutting, frame mounting), Quality Control, Shipping/Logistics, and the Management Analytics Dashboard.

---

## ✅ TODO Checklist
 
 ### Database (Schema)
 - [x] Create `shipment` in `database/schema.sql`
 - [x] Add production status columns (`production_status`) to `order` table.
 
 ### Backend — Application Layer (Services)
 - [x] Complete `OperationsService.php`:
   - Manage production steps (Lens cutting -> Mounting -> QC).
   - Shipment creation and tracking assignment.
 - [x] Complete `DashboardService.php`:
   - Aggregate statistics (Revenue, Top products, Active orders).
 - [x] Complete `AdminService.php`:
   - Management of staff members and system configuration.
 
 ### Backend — Controllers & Routes
 - [x] Implement `OperationsController`, `DashboardController`, `AdminController`.
 - [x] Define API Endpoints for operations and analytics reports.
 
 ### Frontend (Vanilla JS)
 - [x] Created common Dashboard Shell (`pages/portal/index.html`).
 - [x] Created `analytics.html` module: Revenue charts and manager reports.
 - [x] Created `ops.html` module: Production workflow and shipping for Ops Staff.
 - [x] Created `users.html` module: Staff management and RBAC configuration (Admin).
 - [x] Define API endpoints in `js/services/adminService.js` and `js/services/dashboardService.js`.
 
 ### Testing
 - [x] Test API: Advancing an order through production steps.
 - [x] Test API: Creating a shipment and verifying order status update.
 - [x] Analytics: Ensuring revenue matches paid invoices.
 
 ### 🚀 Final Phase (Integration & Polish)
 - [x] **Codebase Sanitization**: Audit all modules to strip lingering `console.log()` and `var_dump()` debug calls.
 - [x] **Environment Prep**: Setup `.env.production` scaffolding and ensure PHP displays no error traces to end users.
 - [x] **E2E Ops Workflow**: Test the lifecycle of creating a shipment in Ops Dashboard and verify that Customer Order History accurately reads "Shipping".
 
 ---
 
 ## 📁 Files Owned
 
 ### Backend
 - `app/Application/OperationsService.php`
 - `app/Application/DashboardService.php`
 - `app/Application/AdminService.php`
 - `app/Http/Controllers/Api/V1/OperationsController.php`
 - `app/Http/Controllers/Api/V1/DashboardController.php`
 
 ### Frontend
 - `frontend/pages/portal/index.html` (Shell)
 - `frontend/pages/portal/modules/analytics.html`
 - `frontend/pages/portal/modules/ops.html`
 - `frontend/pages/portal/modules/users.html`
 - `frontend/js/services/dashboardService.js`

---

## 🔗 Dependencies

- **Depends on**: M1-IDENTITY (users), M2-CATALOG (products), M3-SHOPPING (orders), M4-SALES (verified orders)
- **Blocks**: Nothing (End of workflow)

---

## ⏱️ Estimated Timeline

| Phase | Duration |
|-------|----------|
| API: Operations Workflow | 4 days |
| API: Dashboard Analytics | 3 days |
| UI: Admin Dashboard | 4 days |
| UI: Staff/Ops Dashboard | 2 days |
| Testing & Optimization | 2 days |
| **Total** | **~15 days** |
