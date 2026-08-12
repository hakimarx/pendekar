<?php ob_start(); ?>

<section style="padding: 40px 0; background-color: #F8FAFC; min-height: 80vh;">
    <div class="container" style="max-width: 1200px;">
        <!-- Header -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; border-bottom: 2px solid #E2E8F0; padding-bottom: 15px;">
            <div>
                <h2 style="color: #2D5D7C; margin: 0; font-size: 1.8rem; font-weight: 700;">Dashboard Admin Aplikasi</h2>
                <p style="color: #64748B; margin: 5px 0 0 0; font-size: 0.95rem;">Selamat datang, <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong> (<?php echo htmlspecialchars($_SESSION['role']); ?>)</p>
            </div>
            <div style="display: flex; gap: 10px;">
                <a href="/admin/create" class="btn-primary" style="background-color: #2D5D7C; border-color: #2D5D7C; padding: 10px 20px; font-size: 0.9rem; text-decoration: none; border-radius: 6px; display: inline-flex; align-items: center; gap: 8px; color: white;">
                    <i class="fas fa-plus"></i> Tambah Data Baru
                </a>
                <a href="/verifikator" class="btn-primary" style="background-color: #10B981; border-color: #10B981; padding: 10px 20px; font-size: 0.9rem; text-decoration: none; border-radius: 6px; display: inline-flex; align-items: center; gap: 8px;">
                    <i class="fas fa-clipboard-list"></i> Halaman Verifikasi
                </a>
                <a href="/logout" class="btn-primary" style="background-color: #EF4444; border-color: #EF4444; padding: 10px 20px; font-size: 0.9rem; text-decoration: none; border-radius: 6px; display: inline-flex; align-items: center; gap: 8px;">
                    <i class="fas fa-right-from-bracket"></i> Keluar / Logout
                </a>
            </div>
        </div>

        <!-- Flash Messages -->
        <?php if (!empty($_SESSION['flash_success'])): ?>
            <div style="background-color: #D1FAE5; color: #065F46; padding: 14px 20px; border-radius: 8px; margin-bottom: 24px; font-weight: 500; display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-circle-check" style="font-size: 1.2rem;"></i>
                <span><?php echo htmlspecialchars($_SESSION['flash_success']); unset($_SESSION['flash_success']); ?></span>
            </div>
        <?php endif; ?>

        <?php if (!empty($_SESSION['flash_error'])): ?>
            <div style="background-color: #FEE2E2; color: #991B1B; padding: 14px 20px; border-radius: 8px; margin-bottom: 24px; font-weight: 500; display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-circle-xmark" style="font-size: 1.2rem;"></i>
                <span><?php echo htmlspecialchars($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?></span>
            </div>
        <?php endif; ?>

        <!-- Statistics Cards -->
        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 30px;">
            <div style="background: white; border-radius: 8px; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.04); border: 1px solid #E2E8F0; border-left: 5px solid #2D5D7C; text-align: center;">
                <div style="color: #64748B; font-size: 0.85rem; font-weight: 600; text-transform: uppercase;">Total Pengajuan</div>
                <div style="color: #1E293B; font-size: 2rem; font-weight: 700; margin-top: 5px;"><?php echo $stats['total']; ?></div>
            </div>
            <div style="background: white; border-radius: 8px; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.04); border: 1px solid #E2E8F0; border-left: 5px solid #FCA311; text-align: center;">
                <div style="color: #64748B; font-size: 0.85rem; font-weight: 600; text-transform: uppercase;">Menunggu Verifikasi</div>
                <div style="color: #1E293B; font-size: 2rem; font-weight: 700; margin-top: 5px;"><?php echo $stats['pending']; ?></div>
            </div>
            <div style="background: white; border-radius: 8px; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.04); border: 1px solid #E2E8F0; border-left: 5px solid #10B981; text-align: center;">
                <div style="color: #64748B; font-size: 0.85rem; font-weight: 600; text-transform: uppercase;">Disetujui</div>
                <div style="color: #1E293B; font-size: 2rem; font-weight: 700; margin-top: 5px;"><?php echo $stats['approved']; ?></div>
            </div>
            <div style="background: white; border-radius: 8px; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.04); border: 1px solid #E2E8F0; border-left: 5px solid #EF4444; text-align: center;">
                <div style="color: #64748B; font-size: 0.85rem; font-weight: 600; text-transform: uppercase;">Ditolak</div>
                <div style="color: #1E293B; font-size: 2rem; font-weight: 700; margin-top: 5px;"><?php echo $stats['rejected']; ?></div>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px;">
            <!-- Left Side: Submissions Table -->
            <div style="background: white; border-radius: 12px; padding: 25px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid #E2E8F0;">
                <h3 style="color: #2D5D7C; margin-top: 0; margin-bottom: 20px; font-size: 1.25rem;">Riwayat Seluruh Pengajuan</h3>
                
                <?php if (empty($submissions)): ?>
                    <div style="text-align: center; padding: 40px 10px; color: #64748B;">
                        <p style="margin: 0;">Belum ada riwayat pengajuan pendataan pesantren.</p>
                    </div>
                <?php else: ?>
                    <div style="overflow-x: auto;">
                        <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.85rem;">
                            <thead>
                                <tr style="background-color: #F1F5F9; border-bottom: 2px solid #E2E8F0;">
                                    <th style="padding: 10px; color: #334155; font-weight: 600;">Waktu</th>
                                    <th style="padding: 10px; color: #334155; font-weight: 600;">Pesantren</th>
                                    <th style="padding: 10px; color: #334155; font-weight: 600;">Kategori</th>
                                    <th style="padding: 10px; color: #334155; font-weight: 600;">Status</th>
                                    <th style="padding: 10px; color: #334155; font-weight: 600; text-align: center;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($submissions as $row): ?>
                                    <?php
                                        $statusText = 'Pending';
                                        $statusBg = '#FDE68A';
                                        $statusColor = '#92400E';
                                        if ($row['status'] === 'approved') {
                                            $statusText = 'Disetujui';
                                            $statusBg = '#A7F3D0';
                                            $statusColor = '#065F46';
                                        } elseif ($row['status'] === 'rejected') {
                                            $statusText = 'Ditolak';
                                            $statusBg = '#FCA5A5';
                                            $statusColor = '#991B1B';
                                        }
                                    ?>
                                    <tr style="border-bottom: 1px solid #E2E8F0;">
                                        <td style="padding: 12px 10px; color: #475569; white-space: nowrap;"><?php echo date('d-m-Y H:i', strtotime($row['created_at'])); ?></td>
                                        <td style="padding: 12px 10px;">
                                            <div style="font-weight: 600; color: #0F172A;"><?php echo htmlspecialchars($row['nama_pesantren']); ?></div>
                                            <div style="font-size: 0.75rem; color: #64748B;">NSPP: <?php echo htmlspecialchars($row['nspp']); ?> | <?php echo htmlspecialchars($row['kabupaten']); ?></div>
                                        </td>
                                        <td style="padding: 12px 10px; text-transform: uppercase; font-weight: 500; font-size: 0.75rem;"><?php echo htmlspecialchars($row['kategori']); ?></td>
                                        <td style="padding: 12px 10px;">
                                            <span style="display: inline-block; padding: 2px 8px; border-radius: 4px; font-weight: 600; font-size: 0.75rem; background-color: <?php echo $statusBg; ?>; color: <?php echo $statusColor; ?>;">
                                                <?php echo $statusText; ?>
                                            </span>
                                        </td>
                                        <td style="padding: 12px 10px; text-align: center; white-space: nowrap;">
                                            <a href="/admin/edit?id=<?php echo $row['id']; ?>" class="btn-primary" style="background-color: #3B82F6; border-color: #3B82F6; padding: 4px 10px; font-size: 0.75rem; text-decoration: none; border-radius: 4px; display: inline-flex; align-items: center; gap: 4px;">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <form action="/admin/delete" method="POST" style="display: inline-block; margin-left: 5px;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data pengajuan ini?');">
                                                <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                                <button type="submit" class="btn-primary" style="background-color: #EF4444; border-color: #EF4444; padding: 4px 10px; font-size: 0.75rem; border-radius: 4px; cursor: pointer; display: inline-flex; align-items: center; gap: 4px; border: none; color: white;">
                                                    <i class="fas fa-trash"></i> Hapus
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Right Side: User Management -->
            <div>
                <!-- Add User Form -->
                <div style="background: white; border-radius: 12px; padding: 25px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid #E2E8F0; margin-bottom: 25px;">
                    <h3 style="color: #2D5D7C; margin-top: 0; margin-bottom: 20px; font-size: 1.25rem;">Daftarkan User Baru</h3>
                    
                    <form action="/admin/users" method="POST">
                        <div style="margin-bottom: 15px;">
                            <label for="reg-username" style="display: block; font-weight: 600; margin-bottom: 6px; color: #475569; font-size: 0.85rem;">Username</label>
                            <input type="text" id="reg-username" name="username" required placeholder="Contoh: verifikator2" style="width: 100%; padding: 8px 12px; border: 1px solid #CBD5E1; border-radius: 6px; box-sizing: border-box; font-size: 0.85rem;">
                        </div>

                        <div style="margin-bottom: 15px;">
                            <label for="reg-password" style="display: block; font-weight: 600; margin-bottom: 6px; color: #475569; font-size: 0.85rem;">Password</label>
                            <input type="password" id="reg-password" name="password" required placeholder="Min. 6 Karakter" style="width: 100%; padding: 8px 12px; border: 1px solid #CBD5E1; border-radius: 6px; box-sizing: border-box; font-size: 0.85rem;">
                        </div>

                        <div style="margin-bottom: 20px;">
                            <label for="reg-role" style="display: block; font-weight: 600; margin-bottom: 6px; color: #475569; font-size: 0.85rem;">Hak Akses / Role</label>
                            <select id="reg-role" name="role" required style="width: 100%; padding: 8px 12px; border: 1px solid #CBD5E1; border-radius: 6px; box-sizing: border-box; font-size: 0.85rem; background: white;">
                                <option value="verifikator">Verifikator (Persetujuan Data)</option>
                                <option value="admin">Admin Aplikasi (Semua Hak)</option>
                            </select>
                        </div>

                        <button type="submit" class="btn-primary" style="width: 100%; padding: 10px; font-size: 0.9rem; border-radius: 6px; background-color: #2D5D7C; border: none; color: white; cursor: pointer; font-weight: 600;">
                            <i class="fas fa-user-plus"></i> Tambah Pengguna
                        </button>
                    </form>
                </div>

                <!-- Users List -->
                <div style="background: white; border-radius: 12px; padding: 25px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid #E2E8F0;">
                    <h3 style="color: #2D5D7C; margin-top: 0; margin-bottom: 20px; font-size: 1.25rem;">Daftar Pengguna Aktif</h3>
                    
                    <div style="max-height: 250px; overflow-y: auto;">
                        <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.85rem;">
                            <thead>
                                <tr style="background-color: #F1F5F9; border-bottom: 1px solid #E2E8F0;">
                                    <th style="padding: 8px 10px; color: #334155; font-weight: 600;">Username</th>
                                    <th style="padding: 8px 10px; color: #334155; font-weight: 600;">Role</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $u): ?>
                                    <tr style="border-bottom: 1px solid #E2E8F0;">
                                        <td style="padding: 10px; font-weight: 600; color: #1E293B;"><i class="fas fa-user" style="color: #64748B; margin-right: 6px;"></i> <?php echo htmlspecialchars($u['username']); ?></td>
                                        <td style="padding: 10px; text-transform: capitalize; color: #475569; font-weight: 500;"><?php echo htmlspecialchars($u['role']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php 
$slot = ob_get_clean(); 
include __DIR__ . '/layouts/app.blade.php';
?>
