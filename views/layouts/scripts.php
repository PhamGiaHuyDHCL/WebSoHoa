<!-- External Libraries -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- DataTables -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
function editAccount(account) {
  document.getElementById('editUserId').value = account.ID;
  document.getElementById('editHoTen').value = account.HoTen;
  document.getElementById('editSdt').value = account.SoDienThoai;
  document.getElementById('editCccd').value = account.CCCD;
  document.getElementById('editTaiKhoan').value = account.TaiKhoan;

  const quyenMap = <?= json_encode(array_column($roles, 'ID', 'role_name')) ?>;
  document.getElementById('editQuyen').value = quyenMap[account.Quyen] || '';
  document.getElementById('editMatKhau').value = '';

  const modal = new bootstrap.Modal(document.getElementById('editAccountModal'));
  modal.show();
}

$(document).ready(function () {
  $('#accountTable').DataTable({
    language: {
      zeroRecords: 'Không tìm thấy dữ liệu',
      info: 'Hiển thị _START_ đến _END_ trong _TOTAL_ dòng',
      infoEmpty: 'Không có dữ liệu',
      infoFiltered: '(lọc từ _MAX_ tổng dòng)',
      paginate: {
        first: "Đầu",
        last: "Cuối",
        next: "›",
        previous: "‹"
      },
    },
    paging: true,
    ordering: true,
    info: true,
    lengthChange: false,
    searching: true
  });
});

function toggleCardBody(btn) {
  const card = btn.closest('.card');
  const body = card.querySelector('.card-body');
  const icon = btn.querySelector('i');

  if (body.style.display === 'none') {
    body.style.display = '';
    icon.classList.remove('bi-plus');
    icon.classList.add('bi-dash');
  } else {
    body.style.display = 'none';
    icon.classList.remove('bi-dash');
    icon.classList.add('bi-plus');
  }
}

function closeCard(btn) {
  const card = btn.closest('.card');
  card.style.display = 'none';
}
</script>
