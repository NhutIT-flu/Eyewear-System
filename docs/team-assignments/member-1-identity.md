> ✅ **PROJECT VERIFIED & CI/CD READY**
> *Hệ thống đã được nâng cấp lên chuẩn Enterprise với 284 kịch bản PHPUnit Tests (Coverage > 66.9%) và tích hợp hoàn toàn vào CI/CD Pipeline (Jenkins & GitHub Actions). Vui lòng tham khảo thêm tại `Testing_Architecture_Overview.md` và `jenkins-ci-guide.md` để biết cấu trúc kiểm thử mới nhất.*

# 🆔 Member 1 — Identity, Access & User Profiles

**Module Tag**: `M1-IDENTITY`  
**Priority**: 🔴 Highest (Core for all other modules)

---

## 📋 Scope Overview

This member owns the entry point: Login, Registration, Session/Token protection, and User/Staff Profile management. This module provides the `user` and `role` database logic and authentication services that everyone else relies on.

---

## ✅ TODO Checklist
 
 ### Database (Schema)
 - [x] Create `role`, `user`, `profiles` tables in `database/schema.sql`
 - [x] Implement seed data for roles (`database/seeder.php`)
 
 ### Backend — Application Layer (Services)
 - [x] Complete `AuthService.php`: Register, Login, Logout.
 - [x] Complete `ProfileService.php`: Get info & Update Profile.
 
 ### Backend — Controllers & Routes
 - [x] Implement `AuthController.php`: `register()`, `login()`, `me()`.
 - [x] Implement `ProfileController.php`: `show()`, `update()`.
 - [x] Define API Endpoints in `routes/api.php` under `api/auth` prefix.
 
 ### Frontend (Vanilla JS)
 - [x] Finalize `auth/index.html`: Dual Login/Register form.
 - [x] Implement `js/core/rbac.js`: RBAC permission engine.
 - [x] Implement `js/core/layout-guard.js`: Access protection & Layout switching (Staff vs Customer).
 - [x] Finalize login redirection logic in `js/pages/auth.js`.
 - [x] Create `profile.html` module in Dashboard.
 
 ### Testing
 - [x] Test new user registration (must have default `customer` role).
 - [x] Test login for each role (Staff to Dashboard, Customer to Shop).
 - [x] Test Route Protection: Staff without Customer role cannot access Shop.
 
 ### 🚀 Final Phase (Integration & Polish)
 - [x] **Global Error Handling**: Implement persistent 401 (Unauthorized) redirection across all API calls to force re-authentication.
 - [x] **Missing UI Implementation**: Develop missing static pages (`404.html`, `pages/about/index.html`, `pages/contact/index.html`).
 - [x] **Profile Purge**: In `pages/accounts/index.html`, replace mock static HTML data for Billing Addresses and Orders with actual Profile API calls.
 
 ---
 
 ## 📁 Files Owned
 
 ### Backend
 - `app/Application/AuthService.php`
 - `app/Application/ProfileService.php`
 - `app/Http/Controllers/Api/V1/AuthController.php`
 - `app/Http/Controllers/Api/V1/ProfileController.php`
 - `database/schema.sql` (User/Profile/Role sections)
 
 ### Frontend
 - `frontend/pages/auth/index.html`
 - `frontend/js/core/rbac.js`
 - `frontend/js/core/layout-guard.js`
 - `frontend/js/pages/auth.js`
 - `frontend/pages/portal/modules/profile.html`
 - `frontend/js/services/authService.js`
---

## 🔗 Dependencies

- **Depends on**: Nothing (Base module)
- **Blocks**: Everything (M2, M3, M4, M5 all need `user_id` or authentication)

---

## ⏱️ Estimated Timeline

| Phase | Duration |
|-------|----------|
| Database + Seeding | 1 day |
| Auth API Logic | 2 days |
| Profile API Logic | 1 day |
| Frontend Auth UI/Logic | 2 days |
| Frontend Account UI/Logic| 2 days |
| Testing & Validation | 1 day |
| **Total** | **~9 days** |

