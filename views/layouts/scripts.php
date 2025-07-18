<!-- External Libraries -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- DataTables -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>

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
<script>
document.addEventListener('hidden.bs.modal', function () {
  const backdrops = document.querySelectorAll('.modal-backdrop');
  backdrops.forEach(b => b.remove());
  document.body.classList.remove('modal-open');
  document.body.style.overflow = '';
});
</script>
<script>
function editAccount(account) {
  const modalEl = document.getElementById('editAccountModal');
  const form = modalEl.querySelector('form');

  // Reset form trước khi gán dữ liệu mới
  form.reset();

  // Đổ dữ liệu vào form
  document.getElementById('editUserId').value = account.ID || '';
  document.getElementById('editHoTen').value = account.HoTen || '';
  document.getElementById('editSdt').value = account.SoDienThoai || '';
  document.getElementById('editCccd').value = account.CCCD || '';
  document.getElementById('editTaiKhoan').value = account.TaiKhoan || '';
  document.getElementById('editQuyen').value = account.QuyenID || '';
  document.getElementById('editMatKhau').value = ''; // không hiển thị mật khẩu cũ

  // Mở modal
  const modal = new bootstrap.Modal(modalEl);
  modal.show();
}

// Reset form khi đóng modal để tránh giữ dữ liệu cũ
document.getElementById('editAccountModal').addEventListener('hidden.bs.modal', function () {
  this.querySelector('form').reset();
});
</script>
<!-- CSS -->
<style>
.card-stat {
    transition: transform 0.2s ease;
}
.card-stat:hover {
    transform: scale(1.05);
}
</style>

<!-- JS -->
<script>
const ctx = document.getElementById('chartCanvas').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?= json_encode($chart_labels) ?>,
        datasets: [{
            label: 'Số lượng đã nhập',
            data: <?= json_encode($chart_data) ?>,
            backgroundColor: '#198754'
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true } }
    }
});

function toggleAll(source) {
    document.querySelectorAll('.file-check').forEach(cb => cb.checked = source.checked);
}
</script>


