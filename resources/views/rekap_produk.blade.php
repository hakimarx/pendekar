<?php ob_start(); ?>

<section style="padding: 60px 0; background-color: #F8FAFC;">
    <div class="container">
        <div class="section-title">
            <h2>Rekap Data Produk Pesantren (OPOP)</h2>
            <p>Katalog produk inovasi unggulan, karya kewirausahaan, dan produk santri preneur (One Pesantren One Product) Jawa Timur</p>
        </div>

        <div class="table-card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h3 style="color: #2D5D7C; margin: 0;"><i class="fas fa-box-open" style="color: #00A896;"></i> Katalog Produk OPOP & Inovasi</h3>
                <div>
                    <span style="background: rgba(0, 168, 150, 0.1); color: #008080; padding: 6px 14px; border-radius: 20px; font-weight: 600; font-size: 0.88rem; margin-right: 10px;">
                        Total: <?php echo number_format($paginated['totalRecords'] ?? 0); ?> Produk
                    </span>
                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                        <a href="<?php echo url('/admin/create'); ?>" class="btn-primary" style="padding: 8px 18px; font-size: 0.88rem;"><i class="fas fa-plus"></i> Tambah Produk</a>
                    <?php else: ?>
                        <a href="<?php echo url('/pendataan'); ?>?kategori=produk" class="btn-primary" style="padding: 8px 18px; font-size: 0.88rem;"><i class="fas fa-plus"></i> Tambah Produk</a>
                    <?php endif; ?>
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
                            <th><?php echo $paginated['sortLink']('nama', 'Nama Pesantren'); ?></th>
                            <th><?php echo $paginated['sortLink']('kabupaten', 'Kabupaten/Kota'); ?></th>
                            <th>Foto</th>
                            <th><?php echo $paginated['sortLink']('judul', 'Nama Produk Inovasi'); ?></th>
                            <th><?php echo $paginated['sortLink']('deskripsi', 'Deskripsi Produk'); ?></th>
                            <th><?php echo $paginated['sortLink']('kategori', 'Kategori / Tahun'); ?></th>
                            <th><?php echo $paginated['sortLink']('kontak', 'Kontak / Telepon'); ?></th>
                            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                                <th>Aksi</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($paginated['list'])): ?>
                            <?php foreach ($paginated['list'] as $index => $item): ?>
                                <tr>
                                    <td><?php echo (($paginated['currentPage'] - 1) * 20) + $index + 1; ?></td>
                                    <td><strong><?php echo htmlspecialchars($item['pesantren']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($item['kabupaten']); ?></td>
                                    <td>
                                        <?php if (!empty($item['gambar'])): ?>
                                            <img src="<?php echo url('/assets/opop/'); ?>/<?php echo htmlspecialchars($item['gambar']); ?>" alt="Foto Produk" style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px; border: 1px solid #e2e8f0;">
                                        <?php else: ?>
                                            <div style="width: 80px; height: 80px; background: #f1f5f9; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #94a3b8; font-size: 0.8rem;">
                                                No Image
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td style="color: #00A896; font-weight: 600; font-size: 0.95rem;"><?php echo htmlspecialchars($item['nama_produk']); ?></td>
                                    <td style="max-width: 300px; font-size: 0.85rem; color: #475569;"><?php echo htmlspecialchars($item['deskripsi']); ?></td>
                                    <td>
                                        <span style="background: rgba(45, 93, 124, 0.1); color: #2D5D7C; padding: 4px 10px; border-radius: 20px; font-size: 0.78rem; font-weight: 600; display: inline-block; margin-bottom: 4px;">
                                            <?php echo htmlspecialchars($item['kategori']); ?>
                                        </span>
                                        <br>
                                        <span style="background: #E2E8F0; padding: 2px 8px; border-radius: 4px; font-size: 0.75rem;">
                                            <?php echo htmlspecialchars($item['tahun']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if (!empty($item['kontak']) && $item['kontak'] !== '-'): ?>
                                            <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $item['kontak']); ?>" target="_blank" style="color: #059669; text-decoration: none; font-weight: 500; font-size: 0.85rem;">
                                                <i class="fab fa-whatsapp" style="color: #25D366; font-size: 1rem;"></i> <?php echo htmlspecialchars($item['kontak']); ?>
                                            </a>
                                        <?php else: ?>
                                            <span style="color: #94A3B8;">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                                        <td style="white-space: nowrap;">
                                            <a href="<?php echo url('/admin/edit'); ?>?id=<?php echo $item['id']; ?>" class="btn-primary" style="background-color: #3B82F6; border-color: #3B82F6; padding: 4px 8px; font-size: 0.75rem; text-decoration: none; border-radius: 4px; display: inline-flex; align-items: center; gap: 4px;">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <form action="<?php echo url('/admin/delete'); ?>" method="POST" style="display: inline-block; margin-left: 5px;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?');">
                                                <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                                                <button type="submit" class="btn-primary" style="background-color: #EF4444; border-color: #EF4444; padding: 4px 8px; font-size: 0.75rem; border-radius: 4px; cursor: pointer; display: inline-flex; align-items: center; gap: 4px; border: none; color: white;">
                                                    <i class="fas fa-trash"></i> Hapus
                                                </button>
                                            </form>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" style="text-align: center; color: #6C757D;">Belum ada data produk inovasi OPOP.</td>
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
