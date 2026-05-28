# Checklist Trien Khai Kiem Chung Chuyen Nghiep - Eyewear System

Tai lieu nay huong dan tung buoc de thuc hien quy trinh kiem chung phan mem cho du an Eyewear System mot cach chuyen nghiep. Co the dung tai lieu nay de lam viec nhom, nop bao cao, hoac giai thich quy trinh truoc giang vien.

## 1. Chuan bi moi truong

## 1.1 Cong cu can co

| Cong cu | Muc dich |
|---|---|
| Git | Quan ly source code |
| GitHub/GitLab | Luu repository va quan ly branch/pull request |
| Jira | Quan ly Epic, Story, Task, Test Case |
| PHP 8.x | Chay backend API |
| MySQL/XAMPP/Laragon | Chay database |
| Postman | Test REST API |
| Jenkins | Tu dong kiem tra CI |
| SonarQube hoac SonarLint | Kiem tra chat luong code |
| CodeceptJS | Kiem thu UI/E2E neu can |

## 1.2 Chuan bi project local

Mo terminal tai thu muc project:

```bash
cd "D:\UTH dealine\Eyewear-System"
```

Kiem tra Git:

```bash
git status
```

Kiem tra PHP:

```bash
php -v
```

Neu may bao khong nhan `php`, them PHP vao PATH:

```text
C:\xampp\php
```

hoac duong dan PHP cua Laragon.

## 1.3 Chuan bi database

1. Mo XAMPP hoac Laragon.
2. Bat Apache va MySQL.
3. Tao database:

```text
eyewear_system
```

4. Import file:

```text
backend/database/schema.sql
```

5. Neu co seeder, chay:

```bash
cd backend
php database/seeder.php
```

## 1.4 Chay backend

```bash
cd backend
php -S localhost:8000 -t public
```

Kiem tra tren browser:

```text
http://localhost:8000
```

Neu thay API live message la backend da chay.

## 2. Lam viec voi Jira

## 2.1 Cau truc ticket chuyen nghiep

Moi yeu cau nen co cau truc:

```text
Epic -> Story -> Test Case
```

Vi du:

```text
Epic: Auth Module
Story: Complete Login API
Test Case: AUTH - Test Login Success
Test Case: AUTH - Test Wrong Password
```

## 2.2 Mau Jira Story

```text
Summary:
Complete Login API

Description:
As a user, I want to login with email and password so that I can access my account.

Acceptance Criteria:
- User can login with valid email and password.
- API returns user information and token.
- Wrong password returns 401.
- Missing email or password returns validation error.
- Inactive user cannot login before email verification.

Endpoint:
POST /api/v1/auth/login

Evidence Required:
- Git commit link
- Jenkins build result
- Postman screenshot
```

## 2.3 Quy tac trang thai Jira

| Trang thai | Khi nao dung |
|---|---|
| To Do | Chua lam |
| In Progress | Dang code hoac dang test |
| In Review | Da code/test xong, cho review |
| Done | Da pass test va co evidence |

Khong chuyen ticket sang Done neu chua co bang chung test.

## 3. Lam viec voi Git

## 3.1 Tao branch theo Jira ticket

Tu branch chinh:

```bash
git checkout main
git pull origin main
git checkout -b feature/ES-11-login-api
```

Quy tac dat ten branch:

```text
feature/ES-xx-ten-chuc-nang
fix/ES-xx-ten-loi
test/ES-xx-ten-test-case
docs/ES-xx-ten-tai-lieu
```

Vi du:

```text
feature/ES-11-login-api
fix/ES-15-reset-password
test/ES-46-auth-login-success
docs/ES-90-srs-document
```

## 3.2 Commit code

Sau khi sua code:

```bash
git status
git add <file-can-commit>
git commit -m "feat(auth): complete login API"
git push origin feature/ES-11-login-api
```

Quy tac commit:

| Type | Khi nao dung |
|---|---|
| feat | Them chuc nang |
| fix | Sua loi |
| test | Them/sua test |
| docs | Them/sua tai lieu |
| chore | Cau hinh, Jenkins, viec phu |
| refactor | Cai tien code khong doi logic |

## 3.3 Pull Request / Merge Request

Mo pull request voi noi dung:

```text
Title:
ES-11 Complete Login API

Description:
- Implemented login validation.
- Returned user, roles, permissions and token.
- Tested with Postman.

Evidence:
- Jenkins build: Passed
- Postman: Login Success, Wrong Password, Missing Email

Jira:
ES-11
```

## 4. Jenkins CI

## 4.1 Jenkins dung de lam gi?

Jenkins dung de tu dong kiem tra project sau khi push code. Voi du an nay, Jenkins se:

- Checkout code.
- Kiem tra cau truc project.
- Kiem tra syntax PHP neu may co PHP CLI.
- Kiem tra frontend static file.
- Kiem tra Postman collection va test folder.
- Luu artifact de lam evidence.

## 4.2 Tao Jenkins job

1. Mo Jenkins.
2. Chon `New Item`.
3. Dat ten:

```text
Eyewear-System-CI
```

4. Chon `Pipeline`.
5. Chon `Pipeline script from SCM`.
6. SCM chon `Git`.
7. Nhap repository URL.
8. Branch:

```text
*/main
```

hoac branch nhom dang dung.

9. Script Path:

```text
Jenkinsfile
```

10. Save.
11. Chon `Build Now`.

## 4.3 Doc ket qua Jenkins

Ket qua pass:

```text
Finished: SUCCESS
```

Ket qua fail:

```text
Finished: FAILURE
```

Neu fail:

1. Mo Console Output.
2. Tim stage bi do.
3. Sua loi trong code.
4. Commit va push lai.
5. Chay lai Jenkins.

## 5. Postman API Testing

## 5.1 Tao Workspace

Trong Postman:

1. Chon Workspaces.
2. Tao workspace:

```text
Eyewear System Testing
```

Workspace dung de chua collection, environment va ket qua test.

## 5.2 Import Collection

Import file:

```text
Eyewear-System.postman_collection.json
```

Sau khi import, collection se chua cac request API cua project.

## 5.3 Tao Environment

Tao environment:

```text
Eyewear Local
```

Them bien:

| Variable | Initial Value | Current Value |
|---|---|---|
| `base_url` | `http://localhost:8000/api/v1` | `http://localhost:8000/api/v1` |
| `token` | | |
| `product_id` | | |
| `variant_id` | | |
| `cart_item_id` | | |
| `order_id` | | |
| `payment_id` | | |

## 5.4 Thu tu test API chuyen nghiep

Nen test theo luong nghiep vu:

```text
Health Check
-> Register
-> Login
-> Product Listing
-> Product Detail
-> Add To Cart
-> Update Cart
-> Apply Voucher
-> Checkout
-> Payment
-> Order History
-> Admin/Dashboard/Ops
```

## 5.5 Mau ghi evidence Postman

Moi test case can ghi:

```text
Test Case: AUTH - Test Login Success
Method: POST
Endpoint: {{base_url}}/auth/login
Body:
{
  "email": "customer@example.com",
  "password": "123456"
}
Expected Status: 200
Actual Status: 200
Result: Passed
Evidence: Screenshot attached
```

## 6. SonarQube / SonarLint

## 6.1 Dung de lam gi?

SonarQube hoac SonarLint dung de kiem tra chat luong code:

- Bug.
- Code smell.
- Security issue.
- Duplicate code.
- Maintainability.

## 6.2 Cach dung nhanh voi nhom sinh vien

Neu chua cai SonarQube server, dung SonarLint extension trong IDE truoc.

Quy trinh:

1. Cai SonarLint trong VS Code/Cursor.
2. Mo project.
3. Scan file PHP/JS.
4. Sua cac loi nghiem trong.
5. Chup screenshot ket qua lam evidence.

Neu dung SonarQube server, them file:

```text
sonar-project.properties
```

Noi dung goi y:

```properties
sonar.projectKey=eyewear-system
sonar.projectName=Eyewear System
sonar.projectVersion=1.0
sonar.sources=backend,frontend
sonar.exclusions=backend/PHPMailer/**,frontend/assets/images/**
sonar.sourceEncoding=UTF-8
```

## 7. CodeceptJS UI/E2E Testing

## 7.1 Dung de lam gi?

CodeceptJS dung de test giao dien theo hanh vi nguoi dung that.

Vi du:

- User mo trang login.
- User nhap email/password.
- User bam login.
- He thong chuyen sang trang account/profile.

## 7.2 Khi nao can dung?

Dung khi can test flow tren frontend:

- Login UI.
- Search product UI.
- Add to cart UI.
- Checkout UI.
- Admin dashboard UI.

Neu chi test API backend thi Postman la du.

## 7.3 Luu y voi du an hien tai

Du an hien tai chua co:

```text
package.json
codecept.conf.js
```

Nen CodeceptJS la cong cu bo sung, khong bat buoc neu deadline gan. Neu can lam chuyen nghiep hon thi tao rieng bo E2E test sau.

## 8. Quy trinh thuc hien mot ticket mau

Vi du ticket:

```text
ES-11 Complete Login API
```

## 8.1 Tren Jira

1. Mo ticket ES-11.
2. Doc description va acceptance criteria.
3. Chuyen status sang `In Progress`.

## 8.2 Tren Git

```bash
git checkout main
git pull origin main
git checkout -b feature/ES-11-login-api
```

## 8.3 Sua code

Kiem tra cac file:

```text
backend/app/Http/Controllers/Api/V1/AuthController.php
backend/app/Application/AuthService.php
backend/routes/api.php
```

## 8.4 Test local bang Postman

Chay backend:

```bash
cd backend
php -S localhost:8000 -t public
```

Test:

```text
POST {{base_url}}/auth/login
```

Voi body:

```json
{
  "email": "customer@example.com",
  "password": "123456"
}
```

## 8.5 Commit va push

```bash
git add backend/app/Http/Controllers/Api/V1/AuthController.php backend/app/Application/AuthService.php
git commit -m "feat(auth): complete login API"
git push origin feature/ES-11-login-api
```

## 8.6 Jenkins

1. Chay Jenkins job.
2. Neu pass, copy link build.
3. Neu fail, sua loi va push lai.

## 8.7 Cap nhat Jira

Comment mau:

```text
Verification completed.

Branch: feature/ES-11-login-api
Commit: <commit-hash>
Jenkins: Passed
Postman:
- Login Success: Passed
- Wrong Password: Passed
- Missing Email: Passed

Evidence: screenshots attached.
```

Sau do chuyen ticket sang:

```text
In Review
```

Neu reviewer dong y thi chuyen:

```text
Done
```

## 9. Quy tac chuyen nghiep khi kiem chung

## 9.1 Khong test lung tung

Moi test phai tra loi 4 cau:

```text
Dang test yeu cau nao?
Endpoint nao?
Expected result la gi?
Evidence nam o dau?
```

## 9.2 Khong chuyen Done neu chua co bang chung

Ticket Done phai co toi thieu:

- Link commit.
- Jenkins pass.
- Postman screenshot hoac test result.
- Comment tom tat ket qua.

## 9.3 Tach test thanh valid va invalid case

Vi du Login:

| Case | Expected |
|---|---|
| Email/password dung | 200 |
| Sai password | 401 |
| Thieu email | 422/400 |
| Thieu password | 422/400 |
| Account inactive | 403 |

## 9.4 Moi bug phai tao ticket rieng

Neu test fail:

1. Khong sua am tham.
2. Tao bug ticket tren Jira.
3. Gan bug voi story/test case lien quan.
4. Sua bug tren branch rieng hoac branch hien tai.
5. Retest.

Mau bug:

```text
Bug: Login API returns 500 when password is missing
Steps:
1. Send POST /auth/login
2. Body only contains email
Expected: validation error
Actual: 500 server error
```

## 10. Evidence folder goi y

Co the tao thu muc:

```text
docs/evidence/
```

Cau truc:

```text
docs/evidence/
  ES-11-login-api/
    postman-login-success.png
    postman-wrong-password.png
    jenkins-build-15.png
    sonar-result.png
```

Jira ticket se dinh kem cac anh nay.

## 11. Checklist cuoi cung truoc khi nop

Truoc khi nop hoac demo:

- [ ] Jira co Epic, Story, Test Case ro rang.
- [ ] Moi ticket quan trong co trang thai dung.
- [ ] Code da push len Git.
- [ ] Jenkinsfile co trong repository.
- [ ] Jenkins build da chay thanh cong.
- [ ] Postman collection da import va test duoc.
- [ ] Environment Postman co `base_url` va `token`.
- [ ] Cac API chinh da co screenshot evidence.
- [ ] SonarLint/SonarQube co screenshot neu duoc yeu cau.
- [ ] Neu co UI test, CodeceptJS co test result.
- [ ] SRS va quy trinh kiem chung da dua vao docs.

## 12. Tom tat ngan gon de thuyet trinh

Co the noi trong demo:

```text
Nhom em su dung Jira de quan ly yeu cau va test case, Git de quan ly source code, Jenkins de tu dong kiem tra cau truc va syntax backend, Postman de kiem thu REST API, SonarQube/SonarLint de danh gia chat luong code, va CodeceptJS cho kiem thu UI/E2E neu can. Moi ticket chi duoc chuyen Done khi co commit, Jenkins pass va evidence tu Postman hoac cong cu test tuong ung.
```

