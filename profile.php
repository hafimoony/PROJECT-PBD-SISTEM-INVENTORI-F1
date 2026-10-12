<?php
session_start();
require_once 'config/database.php';
include 'includes/header.php';

$iduser = $_SESSION['user']['iduser'];
$success = '';
$error = '';

// Ambil data user terbaru dari database
$query = "SELECT * FROM user WHERE iduser = '$iduser'";
$user_data = mysqli_fetch_assoc(mysqli_query($conn, $query));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_user = mysqli_real_escape_string($conn, $_POST['nama_user']);
    $username  = mysqli_real_escape_string($conn, $_POST['username']);
    
    // Cek apakah username sudah dipakai user lain
    $check_username = mysqli_query($conn, "SELECT iduser FROM user WHERE username = '$username' AND iduser != '$iduser'");
    if (mysqli_num_rows($check_username) > 0) {
        $error = "Username sudah digunakan oleh akun lain!";
    } else {
        $foto_name = $user_data['foto']; // Default foto lama

        // Cek jika user mengunggah foto baru
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $file_tmp   = $_FILES['foto']['tmp_name'];
            $file_name  = $_FILES['foto']['name'];
            $file_ext   = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $allowed_ext = ['jpg', 'jpeg', 'png', 'webp'];

            if (in_array($file_ext, $allowed_ext)) {
                $new_foto_name = 'avatar_' . $iduser . '_' . time() . '.' . $file_ext;
                $upload_dir    = 'assets/img/profiles/';

                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }

                if (move_uploaded_file($file_tmp, $upload_dir . $new_foto_name)) {
                    // Hapus foto lama jika ada
                    if (!empty($user_data['foto']) && file_exists($upload_dir . $user_data['foto'])) {
                        unlink($upload_dir . $user_data['foto']);
                    }
                    $foto_name = $new_foto_name;
                }
            } else {
                $error = "Format foto harus JPG, JPEG, PNG, atau WEBP!";
            }
        }

        if (empty($error)) {
            $update_query = "UPDATE user SET nama_user = '$nama_user', username = '$username', foto = '$foto_name' WHERE iduser = '$iduser'";
            if (mysqli_query($conn, $update_query)) {
                // Update session dengan data baru
                $_SESSION['user']['nama']     = $nama_user;
                $_SESSION['user']['username'] = $username;
                $_SESSION['user']['foto']     = $foto_name;

                $success = "Profil berhasil diperbarui!";
                // Refresh data lokal
                $user_data['nama_user'] = $nama_user;
                $user_data['username']  = $username;
                $user_data['foto']      = $foto_name;
            } else {
                $error = "Gagal memperbarui profil: " . mysqli_error($conn);
            }
        }
    }
}

$foto_src = !empty($user_data['foto']) && file_exists('assets/img/profiles/' . $user_data['foto']) 
            ? 'http://localhost:8000/assets/img/profiles/' . $user_data['foto'] 
            : 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&q=80&w=250';
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card card-f1 p-4 border-danger">
            <h4 class="mb-3 text-f1-red"><i class="fa-solid fa-user-gear me-2"></i> Pengaturan Profil User</h4>

            <?php if ($success) : ?>
                <div class="alert alert-success py-2 fs-6"><i class="fa-solid fa-circle-check me-1"></i> <?= $success; ?></div>
            <?php endif; ?>
            <?php if ($error) : ?>
                <div class="alert alert-danger py-2 fs-6"><i class="fa-solid fa-triangle-exclamation me-1"></i> <?= $error; ?></div>
            <?php endif; ?>

            <form action="" method="POST" enctype="multipart/form-data">
                <div class="text-center mb-4">
                    <img src="<?= $foto_src; ?>" alt="Foto Profil" class="avatar-f1 mb-2" style="width: 90px; height: 90px;">
                    <div>
                        <label for="foto" class="btn btn-sm btn-outline-danger mt-1">
                            <i class="fa-solid fa-camera me-1"></i> Ubah Foto Profil
                        </label>
                        <input type="file" name="foto" id="foto" class="d-none" accept="image/*" onchange="previewImage(event)">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label text-secondary small fw-bold">NAMA LENGKAP</label>
                    <input type="text" name="nama_user" value="<?= htmlspecialchars($user_data['nama_user'] ?? $user_data['username']); ?>" class="form-control bg-dark text-light border-secondary" required>
                </div>

                <div class="mb-3">
                    <label class="form-label text-secondary small fw-bold">USERNAME</label>
                    <input type="text" name="username" value="<?= htmlspecialchars($user_data['username']); ?>" class="form-control bg-dark text-light border-secondary" required>
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <a href="index.php" class="btn btn-secondary"><i class="fa-solid fa-arrow-left me-1"></i> Kembali</a>
                    <button type="submit" class="btn btn-f1-red"><i class="fa-solid fa-floppy-disk me-1"></i> Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function previewImage(event) {
    const reader = new FileReader();
    reader.onload = function() {
        const output = document.querySelector('.avatar-f1');
        output.src = reader.result;
    };
    reader.readAsDataURL(event.target.files[0]);
}
</script>

<?php include 'includes/footer.php'; ?>