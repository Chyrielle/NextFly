<?php
session_start();
session_regenerate_id(true);

// Cek session expired
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("location: login.html");
    exit();
}
if (time() > ($_SESSION['expire'] ?? 0)) {
    session_destroy();
    header("location: login.html");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel</title>
</head>
<body>

<h1>Dashboard Admin</h1>
<p>Halo, <strong><?= htmlspecialchars($_SESSION['user']) ?></strong>!</p>

<nav>
    <button onclick="location.href='beranda.php'">Beranda</button>
    <button onclick="location.href='editor.php'">Editor</button>
    <button onclick="doLogout()" style="background:#e53935">Logout</button>
</nav>

<div>
    <h2>Tambah User</h2>
    <p id="pesan"></p>
    <input type="text"     id="new_username" placeholder="Username">
    <input type="password" id="new_password" placeholder="Password">
    <select id="new_role">
        <option value="admin">Admin</option>
        <option value="editor">Editor</option>
        <option value="viewer">Viewer</option>
    </select>
    <button class="btn btn-primary" onclick="tambahUser()">+ Simpan</button>
</div>

<div>
    <h2>Daftar User</h2>
    <table border="1" cellpadding="6">
        <thead>
            <tr>
                <th>Username</th>
                <th>Role</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody id="bodyUser">
            <tr><td colspan="3">Memuat data...</td></tr>
        </tbody>
    </table>
</div>

<script>
    async function loadUsers() {
        const res  = await fetch('api/users.php', { method: 'GET' });
        const json = await res.json();

        const tbody = document.getElementById('bodyUser');
        if (!json.success) {
            tbody.innerHTML = `<tr><td colspan="3">${json.message}</td></tr>`;
            return;
        }

        tbody.innerHTML = json.data.map(u => `
            <tr id="row-${u.id}">
                <td>${u.username}</td>
                <td>
                    <select id="role-${u.id}">
                        <option value="admin"  ${u.role==='admin'  ? 'selected':''}>Admin</option>
                        <option value="editor" ${u.role==='editor' ? 'selected':''}>Editor</option>
                        <option value="viewer" ${u.role==='viewer' ? 'selected':''}>Viewer</option>
                    </select>
                </td>
                <td>
                    <button class="btn btn-warning" onclick="updateRole(${u.id})">Ganti Role</button>
                    <button class="btn btn-danger"  onclick="hapusUser(${u.id})">Hapus</button>
                </td>
            </tr>
        `).join('');
    }

    async function tambahUser() {
        const username = document.getElementById('new_username').value;
        const password = document.getElementById('new_password').value;
        const role     = document.getElementById('new_role').value;

        const res  = await fetch('api/users.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ username, password, role })
        });
        const json = await res.json();

        document.getElementById('pesan').textContent = json.message;
        if (json.success) {
            document.getElementById('new_username').value = '';
            document.getElementById('new_password').value = '';
            loadUsers(); 
        }
    }

    async function updateRole(id) {
        const role = document.getElementById(`role-${id}`).value;

        const res  = await fetch('api/users.php', {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id, role })
        });
        const json = await res.json();
        document.getElementById('pesan').textContent = json.message;
        loadUsers();
    }

    async function hapusUser(id) {
        if (!confirm(`Yakin hapus user ID ${id}?`)) return;

        const res  = await fetch('api/users.php', {
            method: 'DELETE',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id })
        });
        const json = await res.json();
        document.getElementById('pesan').textContent = json.message;
        loadUsers();
    }

    async function doLogout() {
        await fetch('api/logout.php', { method: 'POST' });
        window.location.href = 'login.html';
    }

    loadUsers();
</script>

</body>
</html>
