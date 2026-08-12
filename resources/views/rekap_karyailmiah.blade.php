<?php ob_start(); ?>

<section style="padding: 60px 0; background-color: #F8FAFC;">
    <div class="container">
        <div class="section-title">
            <h2>Rekap Data Karya & Kitab Pesantren</h2>
            <p>Katalog keilmuan, karya kitab kuning, manuskrip, jurnal, dan karya digital santri se-Jawa Timur</p>
        </div>

        <div class="table-card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h3 style="color: #2D5D7C; margin: 0;"><i class="fas fa-book-journal-whills" style="color: #00A896;"></i> Data Karya Kitab & Keilmuan</h3>
                <div>
                    <span style="background: rgba(0, 168, 150, 0.1); color: #008080; padding: 6px 14px; border-radius: 20px; font-weight: 600; font-size: 0.88rem; margin-right: 10px;">
                        Total: <?php echo number_format($paginated['totalRecords'] ?? 0); ?> Karya/Kitab
                    </span>
                    <a href="/pendataan" class="btn-primary" style="padding: 8px 18px; font-size: 0.88rem;"><i class="fas fa-plus"></i> Tambah Karya/Kitab</a>
                </div>
            </div>

            
            <form method="GET" action="" style="display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap;">
                <input type="text" name="search" placeholder="Cari nama, nspp, judul..." value="<?php echo $paginated['search'] ?? ''; ?>" style="flex: 1; min-width: 200px; padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px;">
                <select name="kabupaten" style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; min-width: 200px;">
                    <option value="">Semua Kabupaten/Kota</option>
                    <?php foreach ($kabupatenList ?? [] as $kab): ?>
                        <option value="<?php echo htmlspecialchars($kab); ?>" <?php echo ($paginated['selectedKabupaten'] ?? '') === $kab ? 'selected' : ''; ?>><?php echo htmlspecialchars($kab); ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn-primary" style="padding: 8px 20px; border: none; cursor: pointer;"><i class="fas fa-search"></i> Filter</button>
            </form>

            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th><?php echo $paginated['sortLink']('judul', 'Judul Kitab / Karya Ilmiah'); ?></th>
                            <th><?php echo $paginated['sortLink']('pesantren', 'Pesantren & Lokasi'); ?></th>
                            <th><?php echo $paginated['sortLink']('penulis', 'Pengarang / Penyusun'); ?></th>
                            <th><?php echo $paginated['sortLink']('deskripsi', 'Deskripsi Ringkas'); ?></th>
                            <th><?php echo $paginated['sortLink']('tahun', 'Spesifikasi & Penerbit'); ?></th>
                            <th><?php echo $paginated['sortLink']('tautan', 'Akses / Tautan'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($paginated['list'])): ?>
                            <?php foreach ($paginated['list'] as $index => $item): ?>
                                <tr>
                                    <td><?php echo (($paginated['currentPage'] - 1) * 20) + $index + 1; ?></td>
                                    <td style="max-width: 250px;">
                                        <strong style="color: #00A896; font-size: 0.95rem; display: block; margin-bottom: 4px;"><?php echo htmlspecialchars($item['judul']); ?></strong>
                                        <span style="background: rgba(45, 93, 124, 0.1); color: #2D5D7C; padding: 2px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 600;">
                                            <?php echo htmlspecialchars($item['kategori']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($item['pesantren']); ?></strong>
                                        <br>
                                        <small style="color: #64748B;"><?php echo htmlspecialchars($item['kabupaten']); ?></small>
                                    </td>
                                    <td style="color: #2D5D7C; font-weight: 500; font-size: 0.88rem;">
                                        <i class="fas fa-feather-pointed" style="color: #00A896;"></i> <?php echo htmlspecialchars($item['penulis']); ?>
                                    </td>
                                    <td style="max-width: 260px; font-size: 0.82rem; color: #475569;">
                                        <?php echo htmlspecialchars($item['deskripsi']); ?>
                                    </td>
                                    <td style="font-size: 0.8rem; color: #64748B;">
                                        <strong>Tahun:</strong> <?php echo htmlspecialchars($item['tahun']); ?><br>
                                        <?php if (!empty($item['halaman']) && $item['halaman'] !== '-'): ?>
                                            <strong>Halaman:</strong> <?php echo htmlspecialchars($item['halaman']); ?><br>
                                        <?php endif; ?>
                                        <?php if (!empty($item['isbn']) && $item['isbn'] !== '-'): ?>
                                            <strong>ISBN:</strong> <?php echo htmlspecialchars($item['isbn']); ?><br>
                                        <?php endif; ?>
                                        <?php if (!empty($item['penerbit']) && $item['penerbit'] !== '-'): ?>
                                            <strong>Penerbit:</strong> <?php echo htmlspecialchars($item['penerbit']); ?>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($item['link_karya'])): ?>
                                            <a href="<?php echo htmlspecialchars($item['link_karya']); ?>" target="_blank" style="background: #00A896; color: white; padding: 5px 12px; border-radius: 4px; text-decoration: none; font-size: 0.78rem; font-weight: 600; display: inline-flex; align-items: center; gap: 4px; margin-bottom: 4px;">
                                                <i class="fas fa-arrow-up-right-from-square"></i> Buka Karya
                                            </a>
                                            <br>
                                        <?php endif; ?>
                                        <?php if (!empty($item['telepon']) && $item['telepon'] !== '-'): ?>
                                            <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $item['telepon']); ?>" target="_blank" style="color: #059669; text-decoration: none; font-weight: 500; font-size: 0.8rem;">
                                                <i class="fab fa-whatsapp" style="color: #25D366;"></i> Kontak
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" style="text-align: center; color: #6C757D;">Belum ada data karya kitab.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if (($paginated['totalPages'] ?? 0) > 1): ?>
            <div style="display: flex; justify-content: center; gap: 5px; margin-top: 20px; flex-wrap: wrap;">
                <?php 
                $cp = $paginated['currentPage'];
                $tp = $paginated['totalPages'];
                $q = $_GET;
                
                if ($cp > 1) {
                    $q['page'] = $cp - 1;
                    echo '<a href="?'.http_build_query($q).'" style="padding: 6px 12px; border: 1px solid #00A896; border-radius: 4px; color: #00A896; text-decoration: none;">&laquo; Prev</a>';
                }
                
                $start = max(1, $cp - 2);
                $end = min($tp, $cp + 2);
                if ($start > 1) echo '<span style="padding: 6px;">...</span>';
                
                for ($i = $start; $i <= $end; $i++) {
                    $q['page'] = $i;
                    $activeStyle = $i == $cp ? 'background: #00A896; color: white;' : 'color: #00A896; border: 1px solid #00A896;';
                    echo '<a href="?'.http_build_query($q).'" style="padding: 6px 12px; border-radius: 4px; text-decoration: none; ' . $activeStyle . '">' . $i . '</a>';
                }
                
                if ($end < $tp) echo '<span style="padding: 6px;">...</span>';
                
                if ($cp < $tp) {
                    $q['page'] = $cp + 1;
                    echo '<a href="?'.http_build_query($q).'" style="padding: 6px 12px; border: 1px solid #00A896; border-radius: 4px; color: #00A896; text-decoration: none;">Next &raquo;</a>';
                }
                ?>
            </div>
            <?php endif; ?>

        </div>
    </div>
</section>

<?php 
$slot = ob_get_clean(); 
include __DIR__ . '/layouts/app.blade.php';
?>
