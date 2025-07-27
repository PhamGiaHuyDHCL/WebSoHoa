<!-- thêm tài khoản -->
<div class="modal fade" id="addAccountModal" tabindex="-1" aria-labelledby="addAccountModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form method="POST">
        <div class="modal-header">
          <h5 class="modal-title" id="addAccountModalLabel">Thêm tài khoản mới</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row mb-3">
            <div class="col-md-4">
              <label for="hoten">Họ tên *</label>
              <input name="hoten" id="hoten" class="form-control" required pattern="^[\p{L}\s]{3,50}$" title="Họ tên phải từ 3-50 ký tự, chỉ gồm chữ và khoảng trắng">
            </div>
            <div class="col-md-4">
              <label for="sdt">Số điện thoại *</label>
              <input name="sdt" id="sdt" class="form-control" required pattern="^\d{10}$" title="Số điện thoại phải đúng 10 chữ số">
            </div>
            <div class="col-md-4">
              <label for="cccd">Số CCCD *</label>
              <input name="cccd" id="cccd" class="form-control" required pattern="^\d{12}$" title="CCCD phải đúng 12 chữ số">
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-4">
              <label for="taikhoan">Tài khoản *</label>
              <input name="taikhoan" id="taikhoan" class="form-control" required
                    pattern="^[a-zA-Z0-9_]{4,20}$" title="Chỉ gồm chữ cái, số, dấu gạch dưới, từ 4–20 ký tự">
            </div>

            <div class="col-md-4">
              <label for="matkhau">Mật khẩu *</label>
              <input name="matkhau" id="matkhau" type="password" class="form-control" 
              required pattern="^[a-zA-Z0-9_]{4,20}$" title="Chỉ gồm chữ cái, số, dấu gạch dưới, từ 4–20 ký tự">


            </div>
            <div class="col-md-4">
              <label for="quyen">Quyền *</label>
              <select name="quyen" id="quyen" class="form-select" required>
                <option value="" disabled selected hidden>-- Chọn quyền --</option>
                <?php foreach ($roles as $r): ?>
                  <option value="<?= $r['ID'] ?>"><?= htmlspecialchars($r['role_name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Lưu</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
        </div>
      </form>
    </div>
  </div>
</div>
