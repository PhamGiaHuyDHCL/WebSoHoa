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
            <div class="col-md-4"><label>Họ tên *</label>
              <input name="hoten"  id="editHoTen" class="form-control" required pattern="^[a-zA-ZÀ-ỹ\s]+$" title="Chỉ cho phép chữ cái và khoảng trắng">
</div>
            <div class="col-md-4"><label>Số điện thoại *</label><input name="sdt" class="form-control" required pattern="\d{10}"></div>
            <div class="col-md-4"><label>Số CCCD *</label><input name="cccd" class="form-control" required pattern="\d{12}"></div>
          </div>
          <div class="row mb-3">
            <div class="col-md-4"><label>Tài khoản *</label>
               <input name="taikhoan" id="editTaiKhoan" class="form-control" required pattern="^[a-zA-Z0-9_]{4,20}$" title="Chỉ gồm chữ cái, số, dấu gạch dưới, từ 4–20 ký tự">
            </div>
            <div class="col-md-4"><label>Mật khẩu *</label>
              <input name="matkhau" type="password" class="form-control" required></div>
             <input type="password" name="matkhau" class="form-control" required
                   pattern="(?=.*[A-Za-z])(?=.*\d).{6,}"
                   title="Tối thiểu 6 ký tự, gồm ít nhất 1 chữ và 1 số"
                   >

            <div class="col-md-4">
              <label>Quyền *</label>
              <select name="quyen" class="form-select" required>
                <option value="">-- Chọn quyền --</option>
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
