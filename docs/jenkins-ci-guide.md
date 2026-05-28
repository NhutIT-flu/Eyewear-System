# Jenkins CI Guide - Eyewear System

## 1. Jenkins la gi?

Jenkins la cong cu tu dong hoa CI/CD. Trong du an Eyewear System, Jenkins duoc dung de tu dong kiem tra source code sau moi lan push len Git repository.

Pipeline hien tai tap trung vao CI co ban:

- Lay source code tu Git.
- Kiem tra cau truc thu muc quan trong cua du an.
- Kiem tra cu phap PHP neu Jenkins agent co cai PHP CLI.
- Kiem tra cac file frontend tinh can thiet.
- Kiem tra tai san test va Postman collection.
- Luu README, docs va Postman collection thanh build artifact.

## 2. Ly do can Jenkins trong du an

Jenkins giup nhom tranh truong hop code bi thieu file, sai cau truc, hoac PHP co loi cu phap nhung khong ai phat hien truoc khi nop. Moi lan build thanh cong co the dung lam evidence cho Jira hoac bao cao kiem thu.

## 3. File Jenkinsfile

Du an da co file:

```text
Jenkinsfile
```

File nay dinh nghia pipeline gom cac stage:

1. Checkout
2. Project Structure Check
3. Backend PHP Syntax Check
4. Frontend Static File Check
5. Test Documentation Check
6. Archive Artifacts

## 4. Yeu cau tren may Jenkins

Toi thieu:

- Jenkins da duoc cai dat.
- Git plugin da duoc cai dat.
- Jenkins co quyen truy cap repository.

Khuyen nghi:

- Cai PHP CLI tren Jenkins agent de chay `php -l`.
- Neu dung Windows agent, them PHP vao PATH, vi du:

```text
C:\xampp\php
```

Kiem tra PHP:

```bash
php -v
```

Neu Jenkins agent chua co PHP, pipeline van chay nhung se bo qua buoc PHP syntax check.

## 5. Cach tao Jenkins Pipeline Job

1. Mo Jenkins dashboard.
2. Chon New Item.
3. Nhap ten job, vi du:

```text
Eyewear-System-CI
```

4. Chon Pipeline.
5. Trong phan Pipeline, chon:

```text
Pipeline script from SCM
```

6. SCM chon Git.
7. Nhap repository URL cua nhom.
8. Chon branch, vi du:

```text
*/main
```

9. Script Path de mac dinh:

```text
Jenkinsfile
```

10. Bam Save.
11. Bam Build Now de chay pipeline.

## 6. Ket qua mong doi

Build thanh cong khi:

- Cac thu muc `backend`, `frontend`, `docs` ton tai dung cau truc.
- File route backend va schema database ton tai.
- File frontend chinh ton tai.
- Postman collection ton tai.
- Neu co PHP CLI, tat ca file PHP khong co loi syntax.

Build that bai khi:

- Thieu file bat buoc.
- Thieu thu muc quan trong.
- PHP syntax check phat hien loi.

## 7. Cach dung Jenkins lam evidence cho Jira

Voi moi ticket lien quan CI/CD hoac testing, co the dinh kem:

- Anh chup man hinh Jenkins build status.
- Console output stage thanh cong.
- Build artifact gom README, docs va Postman collection.

Vi du ghi vao Jira:

```text
Jenkins CI pipeline was executed successfully. The pipeline verified project structure, backend PHP syntax, frontend static files, and test documentation assets.
```

## 8. Gioi han hien tai

Pipeline nay chua chay PHPUnit vi du an hien tai chua co cau hinh PHPUnit chuan nhu `composer.json` hoac `phpunit.xml`.

Pipeline nay chua chay frontend build vi frontend hien tai la HTML/CSS/JavaScript tinh, khong co `package.json`.

Pipeline nay chua tu dong chay Postman collection vi chua co Newman dependency trong du an.

## 9. Huong phat trien tiep theo

Neu muon pipeline manh hon, co the bo sung:

- `composer.json` va PHPUnit cho backend.
- Newman de chay Postman collection tu dong.
- Database test rieng cho CI.
- Deploy tu dong len server demo.
- Jenkins webhook de build moi lan push code.

