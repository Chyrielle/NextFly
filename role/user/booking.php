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

    sendResponse(200, [
        "success"      => true,
        "booking_code" => $booking_code,
        "service_type" => $service_type,
        "nama_layanan" => $nama_layanan,
        "lokasi"       => $lokasi,
        "total"        => $total
    ]);
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
      btn.addEventListener('click', () => pilihTiket(btn));
    });
  }

  async function pilihTiket(btn) {
    const key  = btn.dataset.key;
    const item = results[key][btn.dataset.idx];
    const errorBox = document.getElementById('errorBox');
    errorBox.style.display = 'none';

    btn.disabled = true;
    btn.textContent = 'Memproses...';

    try {
      const res = await fetch('booking.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          service_type: key,
          nama_layanan: item.nama,
          lokasi: item.lokasi,
          total: item.harga
        })
      });

      const data = await res.json();

      if (!data.success) {
        errorBox.textContent = data.message || 'Gagal membuat booking.';
        errorBox.style.display = 'block';
        btn.disabled = false;
        btn.textContent = 'Pilih';
        return;
      }

      const params = new URLSearchParams({
        booking_code: data.booking_code,
        service_type: data.service_type,
        nama_layanan: data.nama_layanan,
        lokasi: data.lokasi,
        total: data.total
      });

      window.location.href = 'payment.php?' + params.toString();

    } catch (err) {
      errorBox.textContent = 'Terjadi kesalahan koneksi. Silakan coba lagi.';
      errorBox.style.display = 'block';
      btn.disabled = false;
      btn.textContent = 'Pilih';
    }
  }

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
