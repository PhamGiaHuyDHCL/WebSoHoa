<!-- chỉnh sửa tài khoản -->
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
   
              <input name="hoten"  id="editHoTen" class="form-control" required pattern="^[a-zA-ZÀ-ỹ\s]+$" title="Chỉ cho phép chữ cái và khoảng trắng">

            </div>
            <div class="col-md-4">
              <label>Số điện thoại *</label>
              <input name="sdt" id="editSdt" class="form-control"
                 required
                 pattern="^0\d{9}$"
                 title="Số điện thoại hợp lệ gồm 10 chữ số và bắt đầu bằng số 0">

            </div>
            <div class="col-md-4">
              <label>Số CCCD *</label>
              <input name="cccd" id="editCccd" class="form-control"
                 required
                 pattern="^\d{12}$"
                 title="CCCD phải gồm đúng 12 chữ số">

            </div>
          </div>
          <div class="row mb-3">
            <div class="col-md-4">
              <label>Tài khoản *</label>
              <input name="taikhoan" id="editTaiKhoan" class="form-control" required pattern="^[a-zA-Z0-9_]{4,20}$" title="Chỉ gồm chữ cái, số, dấu gạch dưới, từ 4–20 ký tự">

            </div>
            <div class="col-md-4">
              <label>Mật khẩu mới (để trống nếu không đổi)</label>
              <input type="password" id="editMatKhau" name="matkhau" class="form-control"
                   pattern="(?=.*[A-Za-z])(?=.*\d).{6,}"
                   title="Tối thiểu 6 ký tự, gồm ít nhất 1 chữ và 1 số"
                   >

            </div>
            <div class="col-md-4">
              <label for="editQuyen">Quyền *</label>
                <select name="quyen" id="editQuyen" class="form-select" required>
                  <option value="" disabled selected hidden>-- Chọn quyền --</option>
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
