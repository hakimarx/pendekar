<?php

namespace App\Http\Controllers;

class PendataanController
{
    public function index()
    {
        return $this->renderView('pendataan', [
            'pageTitle' => 'Isi Pendataan | PENDEKAR PD PONTREN JATIM'
        ]);
    }

    public function store()
    {
        $namaPesantren = $_POST['nama_pesantren'] ?? '';
        $nspp = $_POST['nspp'] ?? '';
        $kabupaten = $_POST['kabupaten'] ?? '';
        $kategori = $_POST['kategori'] ?? '';

        // Validate
        if (empty($namaPesantren) || empty($nspp) || empty($kabupaten) || empty($kategori)) {
            $errorMessage = "Semua field bertanda bintang (*) wajib diisi.";
            return $this->renderView('pendataan', [
                'pageTitle' => 'Isi Pendataan | PENDEKAR PD PONTREN JATIM',
                'errorMessage' => $errorMessage
            ]);
        }

        // Collect details based on category
        $details = [];
        switch ($kategori) {
            case 'produk':
                $details = [
                    'produk_nama' => $_POST['produk_nama'] ?? '',
                    'produk_kontak' => $_POST['produk_kontak'] ?? '',
                    'produk_deskripsi' => $_POST['produk_deskripsi'] ?? '',
                ];
                break;
            case 'karya':
                $details = [
                    'karya_penulis' => $_POST['karya_penulis'] ?? '',
                    'karya_judul' => $_POST['karya_judul'] ?? '',
                ];
                break;
            case 'digital':
                $details = [
                    'digital_layanan' => $_POST['digital_layanan'] ?? '',
                    'digital_web' => $_POST['digital_web'] ?? '',
                ];
                break;
            case 'ecoteologi':
                $details = [
                    'eco_program' => $_POST['eco_program'] ?? '',
                ];
                break;
            case 'aman':
                $details = [
                    'aman_program' => $_POST['aman_program'] ?? '',
                ];
                break;
            case 'ramah_anak':
                $details = [
                    'ramah_program' => $_POST['ramah_program'] ?? '',
                ];
                break;
        }

        $detailsJson = json_encode($details);

        // Insert into database
        try {
            $pdo = \App\Database::getInstance()->getConnection();
            $stmt = $pdo->prepare("INSERT INTO submissions (nama_pesantren, nspp, kabupaten, kategori, details, status) VALUES (:nama_pesantren, :nspp, :kabupaten, :kategori, :details, 'pending')");
            $stmt->execute([
                ':nama_pesantren' => $namaPesantren,
                ':nspp' => $nspp,
                ':kabupaten' => $kabupaten,
                ':kategori' => $kategori,
                ':details' => $detailsJson
            ]);

            $successMessage = "Data pendataan untuk '{$namaPesantren}' berhasil dikirim dan menunggu verifikasi!";
            return $this->renderView('pendataan', [
                'pageTitle' => 'Isi Pendataan | PENDEKAR PD PONTREN JATIM',
                'successMessage' => $successMessage
            ]);
        } catch (\PDOException $e) {
            $errorMessage = "Gagal menyimpan data ke database: " . $e->getMessage();
            return $this->renderView('pendataan', [
                'pageTitle' => 'Isi Pendataan | PENDEKAR PD PONTREN JATIM',
                'errorMessage' => $errorMessage
            ]);
        }
    }

    private function renderView($viewName, $data = [])
    {
        extract($data);
        $viewFile = __DIR__ . "/../../../resources/views/{$viewName}.blade.php";
        if (file_exists($viewFile)) {
            include $viewFile;
        } else {
            echo "View {$viewName} not found.";
        }
    }
}
