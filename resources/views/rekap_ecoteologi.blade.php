<?php ob_start(); ?>

<section style="padding: 60px 0; background-color: #F8FAFC;">
    <div class="container">
        <div class="section-title">
            <h2>Data Pesantren Ecoteologi</h2>
            <p>Inisiatif pelestarian lingkungan hidup, keberlanjutan energi, dan fikih lingkungan berbasis pesantren</p>
        </div>

        <div class="table-card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h3 style="color: #2D5D7C; margin: 0;"><i class="fas fa-leaf" style="color: #10B981;"></i> Program Pesantren Ecoteologi</h3>
                <div>
                    <span style="background: rgba(16, 185, 129, 0.1); color: #059669; padding: 6px 14px; border-radius: 20px; font-weight: 600; font-size: 0.88rem; margin-right: 10px;">
                        Kelestarian Lingkungan & Fikih Bi'ah
                    </span>
                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                        <a href="/admin/create" class="btn-primary" style="padding: 8px 18px; font-size: 0.88rem;"><i class="fas fa-plus"></i> Tambahkan Data</a>
                    <?php else: ?>
                        <a href="/pendataan?kategori=ecoteologi" class="btn-primary" style="padding: 8px 18px; font-size: 0.88rem;"><i class="fas fa-plus"></i> Tambahkan Data</a>
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
                            <th><?php echo $paginated['sortLink']('jenis', 'Inisiatif & Jenis Ecoteologi'); ?></th>
                            <th><?php echo $paginated['sortLink']('alamat', 'Alamat Lokasi Inovasi'); ?></th>
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
                                    <td><a href="javascript:void(0)" onclick="showPesantrenDetail(this)" data-pesantren='<?php echo $item["raw_json"] ?? "{}"; ?>'><strong style="color: #00A896;"><?php echo htmlspecialchars($item['nama']); ?></strong></a></td>
                                    <td><?php echo htmlspecialchars($item['kabupaten']); ?></td>
                                    <td><span style="background: rgba(16, 185, 129, 0.1); color: #047857; padding: 4px 12px; border-radius: 20px; font-size: 0.85rem; font-weight: 600;"><?php echo htmlspecialchars($item['jenis']); ?></span></td>
                                    <td><?php echo htmlspecialchars($item['alamat']); ?></td>
                                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                                        <td style="white-space: nowrap;">
                                            <a href="/admin/edit?id=<?php echo $item['id']; ?>" class="btn-primary" style="background-color: #3B82F6; border-color: #3B82F6; padding: 4px 8px; font-size: 0.75rem; text-decoration: none; border-radius: 4px; display: inline-flex; align-items: center; gap: 4px;">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <form action="/admin/delete" method="POST" style="display: inline-block; margin-left: 5px;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?');">
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
                                <td colspan="5" style="text-align: center; color: #6C757D;">Belum ada data pesantren ecoteologi.</td>
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
