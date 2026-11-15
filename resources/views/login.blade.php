<?php ob_start(); ?>

<section style="padding: 80px 0; background-color: #F8FAFC; min-height: 70vh; display: flex; align-items: center;">
    <div class="container" style="max-width: 450px;">
        <div style="background: white; border-radius: 12px; padding: 40px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); border: 1px solid #E2E8F0;">
            <div style="text-align: center; margin-bottom: 30px;">
                <div style="width: 60px; height: 60px; background-color: #2D5D7C; color: white; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 15px; font-size: 1.8rem;">
                    <i class="fas fa-lock"></i>
                </div>
                <h2 style="color: #2D5D7C; margin: 0; font-size: 1.6rem; font-weight: 700;">Akses Masuk</h2>
                <p style="color: #64748B; margin-top: 5px; font-size: 0.9rem;">Masuk ke Portal Pendataan PENDEKAR</p>
            </div>

            <?php if (!empty($error)): ?>
                <div style="background-color: #FEE2E2; color: #991B1B; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-weight: 500; font-size: 0.85rem; display: flex; align-items: center; gap: 8px;">
                    <i class="fas fa-circle-exmark"></i>
                    <span><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>

            <?php if (!empty($_GET['info'])): ?>
                <div style="background-color: #EFF6FF; color: #1E40AF; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-weight: 500; font-size: 0.85rem; display: flex; align-items: center; gap: 8px;">
                    <i class="fas fa-circle-info"></i>
                    <span><?php echo htmlspecialchars($_GET['info']); ?></span>
                </div>
            <?php endif; ?>

            <form action="<?php echo url('/login'); ?>" method="POST">
                <div class="form-group" style="margin-bottom: 20px;">
                    <label for="username" style="display: block; font-weight: 600; margin-bottom: 8px; color: #2D5D7C;">Username</label>
                    <input type="text" id="username" name="username" class="form-control" required placeholder="Masukkan username Anda" style="width: 100%; padding: 10px 14px; border: 1px solid #CBD5E1; border-radius: 6px; box-sizing: border-box;">
                </div>

                <div class="form-group" style="margin-bottom: 25px;">
                    <label for="password" style="display: block; font-weight: 600; margin-bottom: 8px; color: #2D5D7C;">Password</label>
                    <input type="password" id="password" name="password" class="form-control" required placeholder="Masukkan password Anda" style="width: 100%; padding: 10px 14px; border: 1px solid #CBD5E1; border-radius: 6px; box-sizing: border-box;">
                </div>

                <button type="submit" class="btn-primary" style="width: 100%; padding: 12px; font-size: 1rem; border-radius: 6px; background-color: #2D5D7C; border: none; color: white; cursor: pointer; font-weight: 600; transition: background-color 0.2s;">
                    <i class="fas fa-right-to-bracket"></i> Masuk
                </button>
            </form>
            
            <div style="text-align: center; margin-top: 25px; border-top: 1px solid #E2E8F0; padding-top: 20px; font-size: 0.8rem; color: #64748B;">
                Hubungi administrator jika Anda lupa password atau memerlukan akses baru.
            </div>
        </div>
    </div>
</section>

<?php 
$slot = ob_get_clean(); 
include __DIR__ . '/layouts/app.blade.php';
?>
