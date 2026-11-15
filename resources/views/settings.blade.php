<?php ob_start(); ?>

<section style="padding: 40px 0; background-color: #F8FAFC; min-height: 80vh;">
    <div class="container" style="max-width: 800px;">
        <div style="margin-bottom: 25px; border-bottom: 2px solid #E2E8F0; padding-bottom: 15px;">
            <h2 style="color: #2D5D7C; margin: 0; font-size: 1.6rem; font-weight: 700;">Pengaturan Akun</h2>
            <p style="color: #64748B; margin: 5px 0 0 0; font-size: 0.9rem;">Kelola detail profil dan keamanan akun Anda</p>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 30px;">
            <!-- Left Panel: Profile Info -->
            <div style="background: white; border-radius: 12px; padding: 25px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid #E2E8F0; height: fit-content; text-align: center;">
                <div style="width: 80px; height: 80px; background-color: #E2E8F0; color: #475569; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 15px; font-size: 2.2rem;">
                    <i class="fas fa-user-gear"></i>
                </div>
                <h3 style="color: #0F172A; margin: 0; font-size: 1.2rem; font-weight: 700; text-transform: capitalize;"><?php echo htmlspecialchars($_SESSION['username']); ?></h3>
                <p style="color: #64748B; margin: 5px 0 20px 0; font-size: 0.85rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;"><?php echo htmlspecialchars($_SESSION['role']); ?></p>
                
                <div style="border-top: 1px solid #E2E8F0; padding-top: 15px; font-size: 0.8rem; color: #64748B; text-align: left;">
                    <div style="margin-bottom: 8px;"><strong>ID Akun:</strong> <?php echo htmlspecialchars($_SESSION['user_id']); ?></div>
                    <div><strong>Akses Level:</strong> <?php echo $_SESSION['role'] === 'admin' ? 'Super Administrator' : 'Staff Verifikator'; ?></div>
                </div>
            </div>

            <!-- Right Panel: Password Form -->
            <div style="background: white; border-radius: 12px; padding: 30px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid #E2E8F0;">
                <h3 style="color: #2D5D7C; margin-top: 0; margin-bottom: 20px; font-size: 1.2rem; font-weight: 600; border-bottom: 1px solid #E2E8F0; padding-bottom: 10px;">
                    <i class="fas fa-lock"></i> Ubah Password Keamanan
                </h3>

                <?php if (!empty($error)): ?>
                    <div style="background-color: #FEE2E2; color: #991B1B; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-weight: 500; font-size: 0.85rem; display: flex; align-items: center; gap: 8px;">
                        <i class="fas fa-circle-exclamation"></i>
                        <span><?php echo htmlspecialchars($error); ?></span>
                    </div>
                <?php endif; ?>

                <?php if (!empty($success)): ?>
                    <div style="background-color: #DEF7EC; color: #03543F; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-weight: 500; font-size: 0.85rem; display: flex; align-items: center; gap: 8px;">
                        <i class="fas fa-circle-check"></i>
                        <span><?php echo htmlspecialchars($success); ?></span>
                    </div>
                <?php endif; ?>

                <form action="<?php echo url('/settings'); ?>" method="POST">
                    <div class="form-group" style="margin-bottom: 18px;">
                        <label for="old_password" style="display: block; font-weight: 600; margin-bottom: 8px; color: #2D5D7C; font-size: 0.9rem;">Password Saat Ini</label>
                        <input type="password" id="old_password" name="old_password" class="form-control" required placeholder="Masukkan password saat ini" style="width: 100%; padding: 10px 14px; border: 1px solid #CBD5E1; border-radius: 6px; box-sizing: border-box;">
                    </div>

                    <div class="form-group" style="margin-bottom: 18px;">
                        <label for="new_password" style="display: block; font-weight: 600; margin-bottom: 8px; color: #2D5D7C; font-size: 0.9rem;">Password Baru</label>
                        <input type="password" id="new_password" name="new_password" class="form-control" required placeholder="Minimal 6 karakter" style="width: 100%; padding: 10px 14px; border: 1px solid #CBD5E1; border-radius: 6px; box-sizing: border-box;">
                    </div>

                    <div class="form-group" style="margin-bottom: 22px;">
                        <label for="confirm_password" style="display: block; font-weight: 600; margin-bottom: 8px; color: #2D5D7C; font-size: 0.9rem;">Ulangi Password Baru</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" required placeholder="Ketik ulang password baru" style="width: 100%; padding: 10px 14px; border: 1px solid #CBD5E1; border-radius: 6px; box-sizing: border-box;">
                    </div>

                    <button type="submit" class="btn-primary" style="padding: 10px 25px; font-size: 0.95rem; border-radius: 6px; background-color: #2D5D7C; border: none; color: white; cursor: pointer; font-weight: 600;">
                        <i class="fas fa-key"></i> Simpan Password Baru
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>

<?php 
$slot = ob_get_clean(); 
include __DIR__ . '/layouts/app.blade.php';
?>
