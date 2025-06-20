<!-- Modal chỉnh sửa tài khoản -->
<div class="modal fade" id="editAccountModal" tabindex="-1" aria-labelledby="editAccountModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form method="POST" id="editAccountForm">
        <div class="modal-header">
          <h5 class="modal-title" id="editAccountModalLabel">Chỉnh sửa tài khoản</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="edit_id" id="editUserId">
          <div class="row mb-3">
            <div class="col-md-4">
              <label>Họ tên *</label>
              <input name="hoten" id="editHoTen" class="form-control" required>
            </div>
            <div class="col-md-4">
              <label>Số điện thoại *</label>
              <input name="sdt" id="editSdt" class="form-control" required pattern="\d{10}">
            </div>
            <div class="col-md-4">
              <label>Số CCCD *</label>
              <input name="cccd" id="editCccd" class="form-control" required pattern="\d{12}">
            </div>
          </div>
          <div class="row mb-3">
            <div class="col-md-4">
              <label>Tài khoản *</label>
              <input name="taikhoan" id="editTaiKhoan" class="form-control" required>
            </div>
            <div class="col-md-4">
              <label>Mật khẩu mới (để trống nếu không đổi)</label>
              <input name="new_password" type="password" id="editMatKhau" class="form-control">
            </div>
            <div class="col-md-4">
              <label>Quyền *</label>
              <select name="quyen" id="editQuyen" class="form-select" required>
                <option value="">-- Chọn quyền --</option>
                <?php foreach ($roles as $r): ?>
                  <option value="<?= $r['ID'] ?>"><?= htmlspecialchars($r['role_name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-warning">Cập nhật</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
        </div>
      </form>
    </div>
  </div>
</div>
