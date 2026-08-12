<?php ob_start(); ?>

<section style="padding: 40px 0; background-color: #F8FAFC; min-height: 80vh;">
    <div class="container" style="max-width: 1100px;">
        <!-- Header -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; border-bottom: 2px solid #E2E8F0; padding-bottom: 15px;">
            <div>
                <h2 style="color: #2D5D7C; margin: 0; font-size: 1.8rem; font-weight: 700;">Dashboard Verifikator</h2>
                <p style="color: #64748B; margin: 5px 0 0 0; font-size: 0.95rem;">Selamat datang, <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong> (<?php echo htmlspecialchars($_SESSION['role']); ?>)</p>
            </div>
            <div>
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

        <!-- Pending Submissions -->
        <div style="background: white; border-radius: 12px; padding: 30px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid #E2E8F0;">
            <h3 style="color: #2D5D7C; margin-top: 0; margin-bottom: 20px; font-size: 1.3rem; border-left: 4px solid #FCA311; padding-left: 10px;">Menunggu Persetujuan (<?php echo count($pendingList); ?>)</h3>

            <?php if (empty($pendingList)): ?>
                <div style="text-align: center; padding: 40px 20px; color: #64748B;">
                    <i class="fas fa-clipboard-check" style="font-size: 3rem; color: #CBD5E1; margin-bottom: 15px;"></i>
                    <p style="margin: 0; font-size: 1rem; font-weight: 500;">Tidak ada pendataan baru yang perlu diverifikasi.</p>
                </div>
            <?php else: ?>
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.9rem;">
                        <thead>
                            <tr style="background-color: #F1F5F9; border-bottom: 2px solid #E2E8F0;">
                                <th style="padding: 12px 15px; color: #334155; font-weight: 600;">Waktu Kirim</th>
                                <th style="padding: 12px 15px; color: #334155; font-weight: 600;">Nama Pesantren</th>
                                <th style="padding: 12px 15px; color: #334155; font-weight: 600;">NSPP / Wilayah</th>
                                <th style="padding: 12px 15px; color: #334155; font-weight: 600;">Program Kategori</th>
                                <th style="padding: 12px 15px; color: #334155; font-weight: 600;">Detail Inovasi</th>
                                <th style="padding: 12px 15px; color: #334155; font-weight: 600; text-align: center;">Tindakan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pendingList as $row): ?>
                                <?php 
                                    $details = json_decode($row['details'], true) ?: []; 
                                    $catText = '';
                                    $catColor = '#2D5D7C';
                                    switch ($row['kategori']) {
                                        case 'produk': $catText = 'OPOP/Entrepreneur'; $catColor = '#6366F1'; break;
                                        case 'digital': $catText = 'Digital'; $catColor = '#38BDF8'; break;
                                        case 'ecoteologi': $catText = 'Ecoteologi'; $catColor = '#10B981'; break;
                                        case 'karya': $catText = 'Karya / Kitab'; $catColor = '#EC4899'; break;
                                        case 'aman': $catText = 'Aman Bencana'; $catColor = '#EF4444'; break;
                                        case 'ramah_anak': $catText = 'Ramah Anak'; $catColor = '#F59E0B'; break;
                                    }
                                ?>
                                <tr style="border-bottom: 1px solid #E2E8F0;">
                                    <td style="padding: 15px; color: #475569;"><?php echo date('d-m-Y H:i', strtotime($row['created_at'])); ?></td>
                                    <td style="padding: 15px; font-weight: 600; color: #0F172A;"><?php echo htmlspecialchars($row['nama_pesantren']); ?></td>
                                    <td style="padding: 15px; color: #475569;">
                                        <div><strong>NSPP:</strong> <?php echo htmlspecialchars($row['nspp']); ?></div>
                                        <div style="font-size: 0.8rem; color: #64748B;">Kab. <?php echo htmlspecialchars($row['kabupaten']); ?></div>
                                    </td>
                                    <td style="padding: 15px;">
                                        <span style="display: inline-block; padding: 4px 8px; border-radius: 4px; color: white; font-weight: 600; font-size: 0.75rem; background-color: <?php echo $catColor; ?>;">
                                            <?php echo $catText; ?>
                                        </span>
                                    </td>
                                    <td style="padding: 15px; color: #334155; max-width: 300px;">
                                        <div style="background-color: #F8FAFC; border: 1px solid #E2E8F0; padding: 10px; border-radius: 6px; font-size: 0.8rem;">
                                            <?php if ($row['kategori'] === 'produk'): ?>
                                                <div><strong>Produk:</strong> <?php echo htmlspecialchars($details['produk_nama'] ?? '-'); ?></div>
                                                <div><strong>Kontak:</strong> <?php echo htmlspecialchars($details['produk_kontak'] ?? '-'); ?></div>
                                                <div style="margin-top: 4px; font-style: italic; color: #64748B;"><?php echo htmlspecialchars($details['produk_deskripsi'] ?? '-'); ?></div>
                                            <?php elseif ($row['kategori'] === 'karya'): ?>
                                                <div><strong>Judul:</strong> <?php echo htmlspecialchars($details['karya_judul'] ?? '-'); ?></div>
                                                <div><strong>Penulis:</strong> <?php echo htmlspecialchars($details['karya_penulis'] ?? '-'); ?></div>
                                            <?php elseif ($row['kategori'] === 'digital'): ?>
                                                <div><strong>Layanan:</strong> <?php echo htmlspecialchars($details['digital_layanan'] ?? '-'); ?></div>
                                                <div><strong>Website:</strong> <a href="<?php echo htmlspecialchars($details['digital_web'] ?? '#'); ?>" target="_blank" style="color: #38BDF8; text-decoration: none;"><?php echo htmlspecialchars($details['digital_web'] ?? '-'); ?></a></div>
                                            <?php elseif ($row['kategori'] === 'ecoteologi'): ?>
                                                <div><strong>Program:</strong> <?php echo htmlspecialchars($details['eco_program'] ?? '-'); ?></div>
                                            <?php elseif ($row['kategori'] === 'aman'): ?>
                                                <div><strong>Mitigasi:</strong> <?php echo htmlspecialchars($details['aman_program'] ?? '-'); ?></div>
                                            <?php elseif ($row['kategori'] === 'ramah_anak'): ?>
                                                <div><strong>Program:</strong> <?php echo htmlspecialchars($details['ramah_program'] ?? '-'); ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td style="padding: 15px; text-align: center;">
                                        <div style="display: flex; gap: 8px; justify-content: center;">
                                            <!-- Approve Form -->
                                            <form action="/verifikator/approve" method="POST" onsubmit="return confirm('Apakah Anda yakin menyetujui pendataan ini?');">
                                                <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                                <button type="submit" style="background-color: #10B981; border: none; color: white; padding: 6px 12px; border-radius: 4px; font-size: 0.8rem; cursor: pointer; font-weight: 600; display: inline-flex; align-items: center; gap: 4px;">
                                                    <i class="fas fa-check"></i> Setujui
                                                </button>
                                            </form>
                                            
                                            <!-- Reject Form -->
                                            <form action="/verifikator/reject" method="POST" onsubmit="return confirm('Apakah Anda yakin menolak pendataan ini?');">
                                                <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                                <button type="submit" style="background-color: #EF4444; border: none; color: white; padding: 6px 12px; border-radius: 4px; font-size: 0.8rem; cursor: pointer; font-weight: 600; display: inline-flex; align-items: center; gap: 4px;">
                                                    <i class="fas fa-xmark"></i> Tolak
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php 
$slot = ob_get_clean(); 
include __DIR__ . '/layouts/app.blade.php';
?>
