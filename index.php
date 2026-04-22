<?php
// index.php
require_once 'includes/header.php';

$pageTitle = 'Trang chủ';

// Featured products
$sql = "SELECT DISTINCT p.*, c.name as cat_name,
        ROUND(p.import_price * (1 + p.profit_rate/100)) as sell_price
        FROM products p JOIN categories c ON p.category_id = c.id
                        JOIN product_varieties pv ON p.id = pv.product_id
        WHERE p.status = 'active' AND pv.stock_quantity > 0
        ORDER BY p.created_at DESC 
        LIMIT 8";
$products = $conn->query($sql);

// Categories with count
$categories = $conn->query("SELECT c.*, COUNT(p.id) as product_count 
    FROM categories c LEFT JOIN products p ON c.id = p.category_id AND p.status='active'
    GROUP BY c.id ORDER BY c.name");
?>

<!-- Hero Banner -->
<div class="bg-dark text-white py-5 mb-4" style="background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%) !important;">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1 class="display-4 fw-bold mb-3">Sneaker <span style="color:#ff6b35">Chính Hãng</span></h1>
                <p class="lead mb-4">Bộ sưu tập sneaker đa dạng từ Nike, Adidas, Jordan và nhiều thương hiệu nổi tiếng.</p>
                <a href="search.php" class="btn btn-primary btn-lg me-2">
                    <i class="bi bi-search me-1"></i> Khám phá ngay
                </a>
                <a href="category.php" class="btn btn-outline-light btn-lg">Xem tất cả</a>
            </div>
            <div class="col-lg-6 text-center d-none d-lg-block">
                <i class="bi bi-shoe" style="font-size: 12rem; color: #ff6b35; opacity: 0.3;"></i>
            </div>
        </div>
    </div>
</div>

<div class="container">
    <!-- Categories -->
    <div class="mb-5">
        <h3 class="section-title mb-4">Danh Mục Sản Phẩm</h3>
        <div class="row g-3">
            <?php while ($cat = $categories->fetch_assoc()): ?>
                <div class="col-6 col-md-3">
                    <a href="category.php?id=<?= $cat['id'] ?>" class="text-decoration-none">
                        <div class="card text-center p-3 h-100 border-0 shadow-sm" style="border-radius:12px">
                            <i class="bi bi-grid fs-2 text-warning mb-2"></i>
                            <h6 class="mb-1 text-dark"><?= htmlspecialchars($cat['name']) ?></h6>
                            <small class="text-muted"><?= $cat['product_count'] ?> sản phẩm</small>
                        </div>
                    </a>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <!-- Featured Products -->
    <div class="mb-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="section-title mb-0">Sản Phẩm Mới Nhất</h3>
            <a href="search.php" class="btn btn-outline-secondary btn-sm">Xem tất cả <i class="bi bi-arrow-right"></i></a>
        </div>
        <div class="row g-4">
            <?php while ($p = $products->fetch_assoc()): ?>
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="card product-card h-100 shadow-sm">
                        <?php if ($p['image'] && file_exists('uploads/' . $p['image'])): ?>
                            <img src="uploads/<?= htmlspecialchars($p['image']) ?>" class="card-img-top product-img" alt="<?= htmlspecialchars($p['name']) ?>">
                        <?php else: ?>
                            <div class="product-img d-flex align-items-center justify-content-center bg-light">
                                <i class="bi bi-shoe fs-1 text-secondary"></i>
                            </div>
                        <?php endif; ?>
                        <div class="card-body d-flex flex-column">
                            <span class="badge badge-category text-white small mb-2"><?= htmlspecialchars($p['cat_name']) ?></span>
                            <h6 class="card-title"><?= htmlspecialchars($p['name']) ?></h6>
                            <p class="price-tag mt-auto mb-2"><?= formatPrice($p['sell_price']) ?></p>
                            <a href="product.php?id=<?= $p['id'] ?>" class="btn btn-primary btn-sm">
                                <i class="bi bi-eye me-1"></i>Xem chi tiết
                            </a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <!-- Banner strip -->
    <div class="row g-3 mb-5">
        <div class="col-md-4">
            <div class="p-4 rounded-3 text-center" style="background:#fff3ee; border: 1px solid #ffd4c2">
                <i class="bi bi-truck fs-2 text-warning mb-2"></i>
                <h6>Giao Hàng Nhanh</h6>
                <small class="text-muted">Nội thành 2 giờ, toàn quốc 1-3 ngày</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="p-4 rounded-3 text-center" style="background:#fff3ee; border: 1px solid #ffd4c2">
                <i class="bi bi-shield-check fs-2 text-warning mb-2"></i>
                <h6>Hàng Chính Hãng</h6>
                <small class="text-muted">Cam kết 100% authentic có giấy tờ</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="p-4 rounded-3 text-center" style="background:#fff3ee; border: 1px solid #ffd4c2">
                <i class="bi bi-arrow-repeat fs-2 text-warning mb-2"></i>
                <h6>Đổi Trả 7 Ngày</h6>
                <small class="text-muted">Không hài lòng, đổi ngay miễn phí</small>
            </div>
        </div>
    </div>
</div>

<!-- Floating Voucher Button -->
<button type="button" class="btn btn-warning rounded-circle shadow-lg d-flex align-items-center justify-content-center" 
        style="position:fixed; bottom:30px; right:30px; width:60px; height:60px; z-index:1000;" 
        data-bs-toggle="modal" data-bs-target="#voucherModal" title="Kho Voucher">
    <i class="bi bi-ticket-perforated fs-3 text-dark"></i>
</button>

<!-- Voucher Modal -->
<div class="modal fade" id="voucherModal" tabindex="-1" aria-labelledby="voucherModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-bold" id="voucherModalLabel"><i class="bi bi-ticket-perforated me-2 text-warning"></i>Kho Voucher Nóng</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="homeCouponList" class="d-flex flex-column gap-3">
            <div class="text-center text-muted py-4">Đang tải mã giảm giá...</div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
    async function loadHomeCoupons() {
        const container = document.getElementById('homeCouponList');
        try {
            const res = await fetch('api/get_all_active_coupons.php');
            const coupons = await res.json();

            if (coupons.length > 0) {
                container.innerHTML = '';
                let guestSaved = JSON.parse(localStorage.getItem('guest_coupons') || '[]');

                coupons.forEach(c => {
                    const isSaved = c.is_saved || guestSaved.includes(c.code);
                    const card = document.createElement('div');
                    card.className = 'd-flex align-items-center justify-content-between p-3 rounded shadow-sm border bg-light';
                    card.innerHTML = `
                        <div class="d-flex flex-column gap-1">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-primary text-white fs-6 py-1 px-2" style="border: 1px dashed #fff">${c.code}</span>
                                <span class="badge bg-secondary opacity-75">${c.scope_text}</span>
                            </div>
                            <div class="fw-bold text-dark mt-1">${c.description}</div>
                            <div class="text-muted small">Đơn từ ${new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(c.min_order)}</div>
                        </div>
                        <button class="btn btn-sm ${isSaved ? 'btn-secondary disabled' : 'btn-outline-primary fw-semibold'} save-home-coupon-btn flex-shrink-0 ms-3" 
                                data-id="${c.id}" data-code="${c.code}" onclick="saveHomeCoupon(this)">
                            ${isSaved ? 'Đã lưu' : 'Lưu mã'}
                        </button>
                    `;
                    container.appendChild(card);
                });
            } else {
                container.innerHTML = '<div class="text-center text-muted py-4">Hiện chưa có mã giảm giá nào.</div>';
            }
        } catch (err) {
            container.innerHTML = '<div class="text-danger text-center py-4">Lỗi kết nối. Vui lòng thử lại.</div>';
        }
    }

    async function saveHomeCoupon(btn) {
        const cid = btn.dataset.id;
        const code = btn.dataset.code;

        if (document.body.classList.contains('logged-in') || <?= isLoggedIn() ? 'true' : 'false' ?>) {
            // Save to DB
            try {
                const formData = new FormData();
                formData.append('coupon_id', cid);
                const res = await fetch('api/save_coupon.php', { method: 'POST', body: formData });
                const data = await res.json();
                
                if (data.success) {
                    btn.innerText = 'Đã lưu';
                    btn.classList.remove('btn-outline-primary');
                    btn.classList.add('btn-secondary', 'disabled');
                    alert(data.message);
                } else {
                    alert(data.message);
                }
            } catch (err) {
                alert('Lỗi kết nối. Vui lòng thử lại.');
            }
        } else {
            // Save to localStorage
            let saved = JSON.parse(localStorage.getItem('guest_coupons') || '[]');
            if (!saved.includes(code)) {
                saved.push(code);
                localStorage.setItem('guest_coupons', JSON.stringify(saved));
                btn.innerText = 'Đã lưu';
                btn.classList.remove('btn-outline-primary');
                btn.classList.add('btn-secondary', 'disabled');
                alert('Đã lưu mã vào máy của bạn! Đăng nhập để lưu vĩnh viễn.');
            } else {
                alert('Mã này đã được lưu.');
            }
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        const modalEl = document.getElementById('voucherModal');
        if (modalEl) {
            modalEl.addEventListener('show.bs.modal', loadHomeCoupons);
        }
    });
</script>

<?php require_once 'includes/footer.php'; ?>