<!-- Modal chỉnh sửa tài khoản -->
<div class="modal fade" id="editAccountModal" tabindex="-1" aria-labelledby="editAccountModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form method="POST" action="edit_taikhoan_xuly.php" id="editAccountForm">
        <div class="modal-header">
          <h5 class="modal-title" id="editAccountModalLabel">Chỉnh sửa tài khoản</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="edit_id" id="editUserId">
          <div class="row mb-3">
            <div class="col-md-4">
              <label for="editHoTen">Họ tên *</label>
              <input type="text" name="hoten" id="editHoTen" class="form-control" required>
            </div>
            <div class="col-md-4">
              <label for="editSdt">Số điện thoại *</label>
              <input type="text" name="sdt" id="editSdt" class="form-control" required pattern="\d{10}">
            </div>
            <div class="col-md-4">
              <label for="editCccd">Số CCCD *</label>
              <input type="text" name="cccd" id="editCccd" class="form-control" required pattern="\d{12}">
            </div>
          </div>
          <div class="row mb-3">
            <div class="col-md-4">
              <label for="editTaiKhoan">Tài khoản *</label>
              <input type="text" name="taikhoan" id="editTaiKhoan" class="form-control" required>
            </div>
            <div class="col-md-4">
              <label for="editMatKhau">Mật khẩu mới (để trống nếu không đổi)</label>
              <input type="password" name="new_password" id="editMatKhau" class="form-control">
            </div>
            <div class="col-md-4">
              <label for="editQuyen">Quyền *</label>
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
