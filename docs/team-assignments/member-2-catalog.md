> ✅ **PROJECT VERIFIED & CI/CD READY**
> *Hệ thống đã được nâng cấp lên chuẩn Enterprise với 284 kịch bản PHPUnit Tests (Coverage > 66.9%) và tích hợp hoàn toàn vào CI/CD Pipeline (Jenkins & GitHub Actions). Vui lòng tham khảo thêm tại `Testing_Architecture_Overview.md` và `jenkins-ci-guide.md` để biết cấu trúc kiểm thử mới nhất.*

# 🕶️ Member 2 — Product Catalog & Inventory

**Module Tag**: `M2-CATALOG`  
**Priority**: 🔴 High (Core for Shopping & Orders)

---

## 📋 Scope Overview

This member owns the **heart of the store**: Branded frames, Lens types, and the logic that combines them. You handle the inventory (stock) and the product search/filtering functionality. You are also responsible for the configuration of product variants (SKUs, attributes) for the Manager.

---

## ✅ TODO Checklist
 
 ### Database (Schema)
 - [x] Create `category`, `product`, `productvariant`, `lens`, `inventory` in `database/schema.sql`
 
 ### Backend — Application Layer (Services)
 - [x] Complete `CatalogService.php`: Filter products (brand, category, price), Get categories.
 - [x] Complete `InventoryService.php`: Update stock quantities, Manage reserved stock (Reservation).
 - [x] Complete `LensService.php`: Retrieve lens list and pricing by index/features.
 - [x] Complete `RecommendationService.php`: Face-shape quiz logic to match eyewear products.
 
 ### Backend — Controllers & Routes
 - [x] Implement Controllers: `ProductController`, `CategoryController`, `InventoryController`, `LensController`.
 - [x] Define API Endpoints for public catalog and internal inventory.
 
 ### Frontend (Vanilla JS)
 - [x] Implement `pages/catalog/index.html`: Product Grid + Attribute Filters.
 - [x] Implement `pages/details/index.html`: Single View + Variant Selection + 2D/3D demo (Placeholder).
 - [x] Create `inventory.html` module in Dashboard: Stock In/Out management.
 - [x] Create `products.html` module in Dashboard: Manager configuration for product variants and attributes.
 - [x] Create `js/services/adminService.js` for product management API calls.
 
 ### Testing
 - [x] Test API: Filtering products by brand and category.
 - [x] Test API: Stock updates and verification in `inventory` table.
 - [x] UI: Internal modules correctly display product and stock lists.
 
 ### 🚀 Final Phase (Integration & Polish)
 - [x] **Navigation Cleanup**: Systematically identify and replace/remove dead `href="#"` links across the Header, Footer, and Shop menus.
 - [x] **Wishlist Integration**: Develop `GET /api/v1/wishlist` and replace static mock elements in `pages/wishlist/index.html`.
 - [x] **Newsletter Setup**: Integrate or hide the static newsletter form appearing at the bottom of standard product pages.
 
 ---
 
 ## 📁 Files Owned
 
 ### Backend
 - `app/Application/CatalogService.php`
 - `app/Application/InventoryService.php`
 - `app/Application/LensService.php`
 - `app/Application/RecommendationService.php`
 - `app/Http/Controllers/Api/V1/ProductController.php`
 - `app/Http/Controllers/Api/V1/InventoryController.php`
 
 ### Frontend
 - `frontend/pages/catalog/index.html`
 - `frontend/pages/details/index.html`
 - `frontend/pages/portal/modules/inventory.html`
 - `frontend/pages/portal/modules/products.html`
 - `frontend/js/services/catalogService.js`
 - `frontend/js/pages/admin-inventory.js`

---

## 🔗 Dependencies

- **Depends on**: M1-IDENTITY (for staff roles in inventory)
- **Blocks**: M3-SHOPPING (needs products to add to cart)

---

## ⏱️ Estimated Timeline

| Phase | Duration |
|-------|----------|
| API: Catalog & Filters | 3 days |
| API: Inventory & stock | 2 days |
| UI: Listing Page | 3 days |
| UI: Details Page | 3 days |
| Testing | 2 days |
| **Total** | **~13 days** |

