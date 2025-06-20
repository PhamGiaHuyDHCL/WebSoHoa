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
            <div class="col-md-4"><label>Họ tên *</label><input name="hoten" class="form-control" required></div>
            <div class="col-md-4"><label>Số điện thoại *</label><input name="sdt" class="form-control" required pattern="\d{10}"></div>
            <div class="col-md-4"><label>Số CCCD *</label><input name="cccd" class="form-control" required pattern="\d{12}"></div>
          </div>
          <div class="row mb-3">
            <div class="col-md-4"><label>Tài khoản *</label><input name="taikhoan" class="form-control" required></div>
            <div class="col-md-4"><label>Mật khẩu *</label><input name="matkhau" type="password" class="form-control" required></div>
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
