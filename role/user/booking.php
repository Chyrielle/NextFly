<?php
session_start();
session_regenerate_id(true);

if (!isset($_SESSION['expire'])) {
    $_SESSION['expire'] = time() + 30;
}
if (time() > $_SESSION['expire']) {
    $_SESSION = [];
    session_destroy();
    header("Location: ../../login.html");
    exit();
}

$role = $_SESSION['role'] ?? '';
if (!isset($_SESSION['login']) || $role !== 'user') {
    header("Location: ../../login.html");
    exit();
}

/* ==========================================================
   AJAX: dipanggil lewat fetch() saat user klik "Pilih"
   Body: { service_type, nama_layanan, lokasi, total }
   Balikan: booking_code + data lengkap untuk dilempar ke payment.php
========================================================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header("Content-Type: application/json; charset=UTF-8");

    function sendResponse($status, $data) {
        http_response_code($status);
        echo json_encode($data);
        exit();
    }

    $input = json_decode(file_get_contents("php://input"), true);

    $service_type = trim($input['service_type'] ?? '');
    $nama_layanan = trim($input['nama_layanan'] ?? '');
    $lokasi       = trim($input['lokasi'] ?? '');
    $total        = $input['total'] ?? '';

    if (empty($service_type) || empty($nama_layanan) || empty($lokasi) || empty($total)) {
        sendResponse(400, [
            "success" => false,
            "message" => "Data booking tidak lengkap."
        ]);
    }

    $booking_code = 'BKG' . strtoupper(substr(uniqid(), -8));

    $extra = [];
    foreach (['checkin', 'checkout', 'malam', 'tamu', 'kamar', 'tanggal', 'penumpang', 'asal', 'tujuan'] as $key) {
        if (isset($input[$key]) && $input[$key] !== '') {
            $extra[$key] = $input[$key];
        }
    }

    sendResponse(200, array_merge([
        "success"      => true,
        "booking_code" => $booking_code,
        "service_type" => $service_type,
        "nama_layanan" => $nama_layanan,
        "lokasi"       => $lokasi,
        "total"        => $total
    ], $extra));
}

// Nama user yang login, dipakai untuk sapaan/avatar
$namaUser = $_SESSION['nama'] ?? ($_SESSION['user'] ?? 'User');
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Booking - Nextfly</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: 'Poppins', sans-serif; color: #1b3b3b; background: #f4f1ea; }
  a { text-decoration: none; color: inherit; }

  /* NAVBAR */
  nav {
    display: flex; justify-content: space-between; align-items: center;
    padding: 16px 40px; background: #0e3b38; color: #fff;
  }
  nav .logo { font-size: 20px; font-weight: 700; }
  nav .menu { display: flex; align-items: center; gap: 24px; font-size: 14px; }
  nav .menu a:hover { color: #e8664b; }
  nav .avatar {
    width: 34px; height: 34px; border-radius: 50%; background: #e8664b;
    display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 14px;
  }

  main { padding: 32px 40px; max-width: 1100px; margin: 0 auto; }

  h1 { font-size: 24px; margin-bottom: 4px; }
  main > p.sub { color: #556; font-size: 14px; margin-bottom: 24px; }

  /* TAB TIKET */
  .tabs { display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 18px; }
  .tabs button {
    background: #fff; border: 1px solid #ddd; color: #1b3b3b;
    padding: 10px 20px; border-radius: 20px; cursor: pointer; font-size: 14px;
  }
  .tabs button.active { background: #0e3b38; color: #fff; border-color: #0e3b38; }

  /* SEARCH BOX */
  .search-box {
    background: #fff; border-radius: 16px; box-shadow: 0 4px 14px rgba(0,0,0,0.06);
    display: flex; flex-wrap: wrap; overflow: hidden; margin-bottom: 32px;
  }
  .search-box .field {
    flex: 1; min-width: 180px; padding: 16px 18px; border-right: 1px solid #eee;
  }
  .search-box .field label { display: block; font-size: 11px; color: #888; margin-bottom: 4px; }
  .search-box .field input {
    border: none; outline: none; width: 100%; font-family: 'Poppins'; font-size: 14px; color: #1b3b3b;
  }
  .search-box button {
    background: #e8664b; color: #fff; border: none; padding: 0 30px; font-weight: 600; cursor: pointer;
  }

  /* RESULT LIST */
  .results h2 { font-size: 17px; margin-bottom: 14px; }
  .result-card {
    background: #fff; border-radius: 14px; padding: 18px 22px; margin-bottom: 14px;
    display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;
    box-shadow: 0 4px 14px rgba(0,0,0,0.06);
  }
  .result-card .info { display: flex; gap: 14px; align-items: center; }
  .result-card .icon {
    width: 46px; height: 46px; border-radius: 12px; background: #e8f5f3;
    display: flex; align-items: center; justify-content: center; font-size: 20px;
  }
  .result-card h3 { font-size: 15.5px; margin-bottom: 3px; }
  .result-card p { font-size: 13px; color: #667; }
  .result-card .price { text-align: right; }
  .result-card .price .amount { font-size: 16px; font-weight: 700; color: #0e3b38; }
  .result-card .price .unit { font-size: 11.5px; color: #999; }
  .result-card .select-btn {
    margin-top: 8px; background: #e8664b; color: #fff; border: none; padding: 8px 20px;
    border-radius: 20px; font-size: 13px; font-weight: 600; cursor: pointer;
  }
  .result-card .select-btn:disabled { opacity: .6; cursor: not-allowed; }

  .error-box {
    display: none; background: #fdeceb; color: #b3261e; border-radius: 12px;
    padding: 12px 16px; font-size: 13px; margin-bottom: 18px;
  }

  /* MODAL DETAIL PEMESANAN */
  .modal-overlay {
    display: none; position: fixed; inset: 0; background: rgba(14,59,56,0.55);
    align-items: center; justify-content: center; z-index: 50; padding: 20px;
  }
  .modal-overlay.active { display: flex; }
  .modal-box {
    background: #fff; border-radius: 16px; padding: 26px 28px; max-width: 420px; width: 100%;
    box-shadow: 0 20px 50px rgba(0,0,0,0.25);
  }
  .modal-box h3 { font-size: 18px; margin-bottom: 4px; }
  .modal-item-name { font-size: 13.5px; color: #667; margin-bottom: 18px; }
  .modal-field { margin-bottom: 14px; }
  .modal-field label { display: block; font-size: 12px; color: #778; margin-bottom: 5px; font-weight: 600; }
  .modal-field input {
    width: 100%; border: 1px solid #ddd; border-radius: 10px; padding: 10px 12px;
    font-family: 'Poppins'; font-size: 14px; color: #1b3b3b;
  }
  .modal-error { color: #b3261e; font-size: 12.5px; margin-bottom: 8px; display: none; }
  .modal-actions { display: flex; gap: 10px; margin-top: 6px; }
  .modal-actions button {
    flex: 1; padding: 11px; border-radius: 20px; border: none; font-size: 13.5px; font-weight: 600; cursor: pointer;
  }
  .modal-btn-cancel { background: #eee; color: #445; }
  .modal-btn-confirm { background: #e8664b; color: #fff; }
  .modal-btn-confirm:disabled { opacity: .6; cursor: not-allowed; }

  @media (max-width: 700px) {
    nav .menu { display: none; }
    main { padding: 24px 18px; }
    .search-box .field { border-right: none; border-bottom: 1px solid #eee; }
    .result-card { flex-direction: column; align-items: flex-start; }
    .result-card .price { text-align: left; }
  }
</style>
</head>
<body>

<nav>
  <div class="logo">Nextfly</div>
  <div class="menu">
    <a href="User.php">Dashboard</a>
    <a href="booking.php">Booking</a>
    <a href="payment.php">Pembayaran</a>
    <a href="#" onclick="logoutUser(); return false;">Keluar</a>
  </div>
  <div class="avatar"><?php echo htmlspecialchars(strtoupper(substr($namaUser, 0, 1))); ?></div>
</nav>

<main>
  <h1>Cari & Pilih Tiket</h1>
  <p class="sub">Pilih jenis tiket, isi detail perjalanan, lalu pilih opsi yang sesuai.</p>

  <div class="error-box" id="errorBox"></div>

  <div class="tabs" id="tabs">
    <button class="active" data-tab="Hotel">🏨 Hotel</button>
    <button data-tab="Pesawat">✈️ Pesawat</button>
    <button data-tab="Kereta">🚆 Kereta</button>
    <button data-tab="Bus">🚌 Bus</button>
  </div>

  <form class="search-box" id="form">
    <div class="field">
      <label id="destLabel">Hotel Tujuan</label>
      <input type="text" id="destInput" placeholder="Kota, Provinsi, Negara">
    </div>
    <div class="field">
      <label>Tanggal</label>
      <input type="text" id="dateInput" placeholder="Check in & Check out">
    </div>
    <div class="field">
      <label id="guestLabel">Tamu dan Kamar</label>
      <input type="text" id="guestInput" placeholder="Usia & Kamar">
    </div>
    <button type="submit">Cari</button>
  </form>

  <div class="results">
    <h2 id="resultTitle">Hasil Pencarian Hotel</h2>
    <div id="resultList"></div>
  </div>
</main>

<div class="modal-overlay" id="modalOverlay">
  <div class="modal-box">
    <h3 id="modalTitle">Detail Pemesanan</h3>
    <p class="modal-item-name" id="modalItemName"></p>

    <div id="modalHotelFields" style="display:none;">
      <div class="modal-field">
        <label>Check-in</label>
        <input type="date" id="modalCheckin">
      </div>
      <div class="modal-field">
        <label>Check-out</label>
        <input type="date" id="modalCheckout">
      </div>
      <div class="modal-field">
        <label>Jumlah Tamu</label>
        <input type="number" id="modalTamu" min="1" value="1">
      </div>
      <div class="modal-field">
        <label>Jumlah Kamar</label>
        <input type="number" id="modalKamar" min="1" value="1">
      </div>
    </div>

    <div id="modalTravelFields" style="display:none;">
      <div class="modal-field">
        <label>Dari</label>
        <input type="text" id="modalAsal" placeholder="Kota/Stasiun/Terminal asal">
      </div>
      <div class="modal-field">
        <label>Ke</label>
        <input type="text" id="modalTujuan" placeholder="Kota/Stasiun/Terminal tujuan">
      </div>
      <div class="modal-field">
        <label>Tanggal Berangkat</label>
        <input type="date" id="modalTanggal">
      </div>
      <div class="modal-field">
        <label>Jumlah Penumpang</label>
        <input type="number" id="modalPenumpang" min="1" value="1">
      </div>
    </div>

    <p class="modal-error" id="modalError"></p>

    <div class="modal-actions">
      <button type="button" class="modal-btn-cancel" onclick="closeModal()">Batal</button>
      <button type="button" class="modal-btn-confirm" id="modalConfirmBtn">Lanjutkan ke Pembayaran</button>
    </div>
  </div>
</div>

<script>
  const labels = {
    Hotel:   { dest: "Hotel Tujuan", guest: "Tamu dan Kamar", guestPh: "Usia & Kamar", title: "Hasil Pencarian Hotel" },
    Pesawat: { dest: "Kota Tujuan", guest: "Penumpang", guestPh: "Jumlah Penumpang", title: "Hasil Pencarian Pesawat" },
    Kereta:  { dest: "Stasiun Tujuan", guest: "Penumpang", guestPh: "Jumlah Penumpang", title: "Hasil Pencarian Kereta" },
    Bus:     { dest: "Terminal Tujuan", guest: "Penumpang", guestPh: "Jumlah Penumpang", title: "Hasil Pencarian Bus" },
  };

  const results = {
    Hotel: [
      { icon: "🏨", nama: "Swiss Belhotel Solo", lokasi: "Kota Surakarta, Jawa Tengah", ket: "Kota Surakarta, Jawa Tengah · ⭐ 4.9 (900 ulasan)", harga: 700000, satuan: "/ malam" },
      { icon: "🏨", nama: "Gets Hotel Malang", lokasi: "Kota Malang, Jawa Timur", ket: "Kota Malang, Jawa Timur · ⭐ 4.4 (388 ulasan)", harga: 460000, satuan: "/ malam" },
      { icon: "🏨", nama: "Quest Hotel", lokasi: "Denpasar Barat, Bali", ket: "Denpasar Barat, Bali · ⭐ 4.8 (512 ulasan)", harga: 350000, satuan: "/ malam" },
    ],
    Pesawat: [
      { icon: "✈️", nama: "Citilink - Ekonomi", lokasi: "Jakarta (CGK) → Kertajati (KJT)", ket: "Jakarta (CGK) → Kertajati (KJT) · 06:00 - 08:50", harga: 890000, satuan: "/ orang" },
      { icon: "✈️", nama: "Garuda Indonesia", lokasi: "Jakarta (CGK) → Denpasar (DPS)", ket: "Jakarta (CGK) → Denpasar (DPS) · 09:15 - 12:05", harga: 1150000, satuan: "/ orang" },
      { icon: "✈️", nama: "Lion Air", lokasi: "Denpasar (DPS) → Tanjung Harapan (TJS)", ket: "Denpasar (DPS) → Tanjung Harapan (TJS) · 14:30 - 17:20", harga: 900000, satuan: "/ orang" },
    ],
    Kereta: [
      { icon: "🚆", nama: "Whoosh", lokasi: "Bandung → Jakarta", ket: "Bandung → Jakarta · 07:00 - 10:15", harga: 150000, satuan: "/ orang" },
      { icon: "🚆", nama: "Gajayana", lokasi: "Gambir → Malang", ket: "Gambir → Malang · 09:30 - 13:00", harga: 85000, satuan: "/ orang" },
      { icon: "🚆", nama: "Taksaka", lokasi: "Gambir → Yogyakarta", ket: "Gambir → Yogyakarta · 16:00 - 19:10", harga: 120000, satuan: "/ orang" },
    ],
    Bus: [
      { icon: "🚌", nama: "Sinar Jaya", lokasi: "Jabodetabek → Jakarta", ket: "Jabodetabek → Jakarta · 08:00 - 11:30", harga: 45000, satuan: "/ orang" },
      { icon: "🚌", nama: "Harapan Jaya", lokasi: "Blitar → Kediri", ket: "Blitar → Kediri · 10:00 - 11:40", harga: 30000, satuan: "/ orang" },
      { icon: "🚌", nama: "Rosalia Indah Eksekutif", lokasi: "Yogyakarta → Solo", ket: "Yogyakarta → Solo · 13:00 - 14:30", harga: 55000, satuan: "/ orang" },
    ],
  };

  let currentTab = 'Hotel';

  function formatRupiah(n) {
    return 'Rp ' + n.toLocaleString('id-ID');
  }

  function renderResults(key) {
    const container = document.getElementById('resultList');
    container.innerHTML = results[key].map((item, idx) => `
      <div class="result-card">
        <div class="info">
          <div class="icon">${item.icon}</div>
          <div>
            <h3>${item.nama}</h3>
            <p>${item.ket}</p>
          </div>
        </div>
        <div class="price">
          <div class="amount">${formatRupiah(item.harga)}</div>
          <div class="unit">${item.satuan}</div>
          <button type="button" class="select-btn" data-key="${key}" data-idx="${idx}">Pilih</button>
        </div>
      </div>
    `).join('');

    container.querySelectorAll('.select-btn').forEach(btn => {
      btn.addEventListener('click', () => openModal(btn.dataset.key, btn.dataset.idx));
    });
  }

  let selectedKey = null;
  let selectedItem = null;

  function openModal(key, idx) {
    selectedKey = key;
    selectedItem = results[key][idx];

    document.getElementById('modalItemName').textContent =
      selectedItem.nama + ' — ' + formatRupiah(selectedItem.harga) + ' ' + selectedItem.satuan;
    document.getElementById('modalError').style.display = 'none';

    const isHotel = key === 'Hotel';
    document.getElementById('modalHotelFields').style.display = isHotel ? 'block' : 'none';
    document.getElementById('modalTravelFields').style.display = isHotel ? 'none' : 'block';
    document.getElementById('modalTitle').textContent = isHotel ? 'Detail Menginap' : 'Detail Perjalanan';

    if (isHotel) {
      document.getElementById('modalCheckin').value = '';
      document.getElementById('modalCheckout').value = '';
      document.getElementById('modalTamu').value = 1;
      document.getElementById('modalKamar').value = 1;
    } else {
      const [asalAwal, tujuanAwal] = selectedItem.lokasi.split('→').map(s => (s || '').trim());
      document.getElementById('modalAsal').value = asalAwal || '';
      document.getElementById('modalTujuan').value = tujuanAwal || '';
      document.getElementById('modalTanggal').value = '';
      document.getElementById('modalPenumpang').value = 1;
    }

    const confirmBtn = document.getElementById('modalConfirmBtn');
    confirmBtn.disabled = false;
    confirmBtn.textContent = 'Lanjutkan ke Pembayaran';

    document.getElementById('modalOverlay').classList.add('active');
  }

  function closeModal() {
    document.getElementById('modalOverlay').classList.remove('active');
  }

  document.getElementById('modalConfirmBtn').addEventListener('click', async () => {
    const errorEl = document.getElementById('modalError');
    errorEl.style.display = 'none';

    const isHotel = selectedKey === 'Hotel';
    let hargaFinal = selectedItem.harga;
    let lokasiFinal = selectedItem.lokasi;
    let extra = {};

    if (isHotel) {
      const checkin  = document.getElementById('modalCheckin').value;
      const checkout = document.getElementById('modalCheckout').value;
      const tamu     = parseInt(document.getElementById('modalTamu').value || '1', 10);
      const kamar    = parseInt(document.getElementById('modalKamar').value || '1', 10);

      if (!checkin || !checkout) {
        errorEl.textContent = 'Isi tanggal check-in dan check-out dulu, ya.';
        errorEl.style.display = 'block';
        return;
      }
      const malam = Math.round((new Date(checkout) - new Date(checkin)) / 86400000);
      if (malam <= 0) {
        errorEl.textContent = 'Tanggal check-out harus setelah check-in.';
        errorEl.style.display = 'block';
        return;
      }
      hargaFinal = selectedItem.harga * malam * kamar;
      extra = { checkin, checkout, malam, tamu, kamar };
    } else {
      const asal      = document.getElementById('modalAsal').value.trim();
      const tujuan    = document.getElementById('modalTujuan').value.trim();
      const tanggal   = document.getElementById('modalTanggal').value;
      const penumpang = parseInt(document.getElementById('modalPenumpang').value || '1', 10);

      if (!asal || !tujuan) {
        errorEl.textContent = 'Isi asal dan tujuan perjalanan dulu, ya.';
        errorEl.style.display = 'block';
        return;
      }
      if (!tanggal) {
        errorEl.textContent = 'Isi tanggal keberangkatan dulu, ya.';
        errorEl.style.display = 'block';
        return;
      }
      hargaFinal  = selectedItem.harga * penumpang;
      lokasiFinal = asal + ' → ' + tujuan;
      extra = { asal, tujuan, tanggal, penumpang };
    }

    const confirmBtn = document.getElementById('modalConfirmBtn');
    confirmBtn.disabled = true;
    confirmBtn.textContent = 'Memproses...';

    try {
      const res = await fetch('booking.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          service_type: selectedKey,
          nama_layanan: selectedItem.nama,
          lokasi: lokasiFinal,
          total: hargaFinal,
          ...extra
        })
      });

      const data = await res.json();

      if (!data.success) {
        errorEl.textContent = data.message || 'Gagal membuat booking.';
        errorEl.style.display = 'block';
        confirmBtn.disabled = false;
        confirmBtn.textContent = 'Lanjutkan ke Pembayaran';
        return;
      }

      const params = new URLSearchParams({
        booking_code: data.booking_code,
        service_type: data.service_type,
        nama_layanan: data.nama_layanan,
        lokasi: data.lokasi,
        total: data.total
      });
      ['checkin', 'checkout', 'malam', 'tamu', 'kamar', 'tanggal', 'penumpang', 'asal', 'tujuan'].forEach(k => {
        if (data[k] !== undefined) params.set(k, data[k]);
      });

      window.location.href = 'payment.php?' + params.toString();

    } catch (err) {
      errorEl.textContent = 'Terjadi kesalahan koneksi. Silakan coba lagi.';
      errorEl.style.display = 'block';
      confirmBtn.disabled = false;
      confirmBtn.textContent = 'Lanjutkan ke Pembayaran';
    }
  });

  document.querySelectorAll('#tabs button').forEach(btn => {
    btn.addEventListener('click', () => {
      document.querySelectorAll('#tabs button').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      currentTab = btn.dataset.tab;
      document.getElementById('destLabel').textContent = labels[currentTab].dest;
      document.getElementById('guestLabel').textContent = labels[currentTab].guest;
      document.getElementById('guestInput').placeholder = labels[currentTab].guestPh;
      document.getElementById('resultTitle').textContent = labels[currentTab].title;
      document.getElementById('errorBox').style.display = 'none';
      renderResults(currentTab);
    });
  });

  document.getElementById('form').addEventListener('submit', e => e.preventDefault());

  function logoutUser() {
    fetch("../../api/logout.php", { method: "POST" })
      .then(res => res.json())
      .then(() => { window.location.href = "../../login.html"; })
      .catch(() => { window.location.href = "../../login.html"; });
  }

  renderResults('Hotel');
</script>

</body>
</html>
