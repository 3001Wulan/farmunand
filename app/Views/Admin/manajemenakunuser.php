<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manajemen Akun User - Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
html, body {
  margin: 0;
  padding: 0;
  height: 100%;
  background: #f8f9fa;
}

/* Hapus padding container bootstrap */
.container-fluid {
  margin: 0;
  padding: 0;
}

/* Hapus gap row */
.row.g-0 {
  margin: 0;
}

/* Sidebar */
.sidebar {
  min-height: 100vh;
  background: #198754; /* hijau */
  padding: 20px;
  margin: 0; 
  color: white;
}

/* Profil di sidebar */
.sidebar .profile {
  width: 120px;
  height: 120px;
  border-radius: 50%;
  background: white;
  margin: 0 auto 20px auto;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  color: #198754;
  font-size: 18px;
}

/* Link sidebar */
.sidebar a {
  display: block;
  padding: 10px;
  margin: 10px 0;
  background: white;
  color: #198754;
  text-decoration: none;
  border-radius: 5px;
  font-weight: 500;
  text-align: center;
  transition: all 0.3s;
}

.sidebar a:hover,
.sidebar a.active {
  background: #145c32;
  color: white;
}

/* Content */
.content {
  padding: 30px;
}

table th {
  background: #198754;
  color: white;
}

  </style>
</head>
<body>
<div class="container-fluid">
  <div class="row g-0">
    
    <!-- Sidebar -->
    <div class="col-md-3 col-lg-2 sidebar">
      <div class="profile">Admin</div>
      <a href="#">Profil</a>
      <a href="#">Dashboard</a>
      <a href="#">Product</a>
      <a href="#">Chart</a>
      <a href="#">History</a>
      <a href="#" class="active">Manajemen User</a>
      <a href="#">Log Out</a>
    </div>

    <!-- Content -->
    <div class="col-md-9 col-lg-10 content">
      <h3 class="mb-4 text-success">Manajemen Akun User</h3>
      
      <table class="table table-bordered table-hover">
        <thead>
          <tr>
            <th>#</th>
            <th>Nama</th>
            <th>Email</th>
            <th>No. HP</th>
            <th>Status</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>1</td>
            <td>Wulandari Yulianis</td>
            <td>wulan@example.com</td>
            <td>+62 822-8567-1644</td>
            <td><span class="badge bg-success">Aktif</span></td>
            <td>
              <button class="btn btn-sm btn-warning">Edit</button>
              <button class="btn btn-sm btn-danger">Hapus</button>
            </td>
          </tr>
          <tr>
            <td>2</td>
            <td>Budi Santoso</td>
            <td>budi@example.com</td>
            <td>+62 811-2233-4455</td>
            <td><span class="badge bg-secondary">Nonaktif</span></td>
            <td>
              <button class="btn btn-sm btn-warning">Edit</button>
              <button class="btn btn-sm btn-danger">Hapus</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

  </div>
</div>
</body>
</html>