
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f4f7fb;
            color: #1f2937;
        }

        .sidebar {
            width: 260px;
            min-height: 100vh;
            background: linear-gradient(135deg, #0f172a, #1e293b);
            color: #fff;
            padding: 24px 20px;
        }

        .brand {
            font-size: 1.3rem;
            font-weight: 700;
            margin-bottom: 28px;
        }

        .nav-link {
            display: block;
            padding: 12px 14px;
            color: #cbd5e1;
            text-decoration: none;
            border-radius: 10px;
            margin-bottom: 8px;
        }

        .nav-link:hover,
        .nav-link.active {
            background: rgba(255,255,255,0.12);
            color: #fff;
        }

        .content {
            flex: 1;
            padding: 24px;
        }

        .topbar {
            background: #fff;
            border-radius: 16px;
            padding: 20px 24px;
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .stat-card {
            background: #fff;
            border: none;
            border-radius: 16px;
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
        }

        .chart-box {
            height: 220px;
            border-radius: 14px;
            background: linear-gradient(135deg, #e0f2fe, #f5f3ff);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #475569;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="d-flex">
        <aside class="sidebar">
            <div class="brand">Admin Panel</div>
            <nav>
                <a href="#" class="nav-link active">Dashboard</a>
                <a href="#" class="nav-link">Users</a>
                <a href="#" class="nav-link">Orders</a>
                <a href="#" class="nav-link">Products</a>
                <a href="#" class="nav-link">Settings</a>
            </nav>
        </aside>

        <main class="content">
            <div class="topbar">
                <div>
                    <h2 class="mb-1">Selamat datang, Admin</h2>
                    <p class="text-muted mb-0">Ringkasan aktivitas sistem hari ini</p>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-secondary">Export</button>
                    <button class="btn btn-primary">+ Tambah Data</button>
                </div>
            </div>

            <div class="row g-4 mt-2">
                <div class="col-md-6 col-xl-3">
                    <div class="card stat-card p-4">
                        <div class="text-muted small">Total User</div>
                        <h3 class="mt-2 mb-0 fw-bold">1,248</h3>
                        <div class="text-success small mt-2">+8.2% dari bulan lalu</div>
                    </div>
                </div>

                <div class="col-md-6 col-xl-3">
                    <div class="card stat-card p-4">
                        <div class="text-muted small">Pendapatan</div>
                        <h3 class="mt-2 mb-0 fw-bold">Rp 42.5J</h3>
                        <div class="text-success small mt-2">+12.4% dari minggu lalu</div>
                    </div>
                </div>

                <div class="col-md-6 col-xl-3">
                    <div class="card stat-card p-4">
                        <div class="text-muted small">Pesanan</div>
                        <h3 class="mt-2 mb-0 fw-bold">320</h3>
                        <div class="text-danger small mt-2">-3.1% dari hari kemarin</div>
                    </div>
                </div>

                <div class="col-md-6 col-xl-3">
                    <div class="card stat-card p-4">
                        <div class="text-muted small">Produk Aktif</div>
                        <h3 class="mt-2 mb-0 fw-bold">84</h3>
                        <div class="text-success small mt-2">+5 barang baru</div>
                    </div>
                </div>
            </div>

            <div class="row g-4 mt-1">
                <div class="col-lg-8">
                    <div class="card stat-card p-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="fw-semibold mb-1">Pertumbuhan Pengguna</h5>
                                <p class="text-muted small mb-0">7 hari terakhir</p>
                            </div>
                            <span class="badge bg-success">+12.4%</span>
                        </div>
                        <div class="chart-box mt-4">
                            Grafik Statistik
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card stat-card p-4">
                        <h5 class="fw-semibold mb-3">Aktivitas Terbaru</h5>
                        <ul class="list-unstyled mb-0">
                            <li class="d-flex justify-content-between py-2 border-bottom">
                                <span>Admin login</span>
                                <small class="text-muted">10:20</small>
                            </li>
                            <li class="d-flex justify-content-between py-2 border-bottom">
                                <span>Pesanan baru</span>
                                <small class="text-muted">10:05</small>
                            </li>
                            <li class="d-flex justify-content-between py-2">
                                <span>Produk diupdate</span>
                                <small class="text-muted">09:40</small>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>