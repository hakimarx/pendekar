import csv
import json
import os

# Compiled data for Child-Friendly/Friendly-Themed Pesantren (Tema "Ramah") in East Java
pesantren_ramah_data = [
    {
        "nama": "Pondok Pesantren Modern Al-Amanah Junwangi",
        "alamat": "Jl. Raya Junwangi No. 43, RT 09/RW 03, Desa Junwangi, Kecamatan Krian, Sidoarjo, Jawa Timur 61262",
        "latitude": -7.387428,
        "longitude": 112.576214,
        "program_ramah_anak": "Tim Husada Cilik & Husada Santri (kesehatan/sanitasi), Sanggar Literasi & Seni, peringatan Hari Anak Nasional, Cek Kesehatan Gratis & Layanan Mental.",
        "integrasi_kurikulum": "Terintegrasi dalam kegiatan ekstrakurikuler, pendidikan kesehatan harian, dan sanggar literasi secara berkala.",
        "dampak_karakter": "Mengembangkan kepedulian sosial, kesadaran kebersihan diri/lingkungan, jiwa kepemimpinan, serta kecakapan literasi santri."
    },
    {
        "nama": "Pondok Pesantren Darul Ulum",
        "alamat": "Jl. Rejoso No. 36, Desa Rejoso, Kecamatan Peterongan, Jombang, Jawa Timur 61481",
        "latitude": -7.545802,
        "longitude": 112.281134,
        "program_ramah_anak": "Pilot project PRA tingkat provinsi, penerapan pilar pengawasan & keterbukaan, jaminan pemenuhan hak santri (minat bakat, sehat, kasih sayang).",
        "integrasi_kurikulum": "Terintegrasi dalam kurikulum formal Madrasah (materi akhlakul karimah, saling menyayangi, toleransi) dan pedoman harian kehidupan asrama.",
        "dampak_karakter": "Menumbuhkan sikap saling menghormati, toleransi tinggi, meminimalisir kekerasan fisik/verbal (bullying), serta kemandirian santri."
    },
    {
        "nama": "Pondok Pesantren Nurul Jadid",
        "alamat": "Jl. KH. Zaini Mun'im, Dusun Tanjung Lor, Desa Karanganyar, Kecamatan Paiton, Probolinggo, Jawa Timur 67291",
        "latitude": -7.771221,
        "longitude": 113.504245,
        "program_ramah_anak": "Formula Disiplin Positif (mengganti hukuman fisik/verbal dengan sanksi edukatif & dialog), Qudwah Leadership Camp & TOT Capacity Building Pengurus Ramah Santri (LKP, BK, Kamtib), kerjasama Lembaga Perlindungan Anak (LPA) (edukasi anti-bullying, perlindungan diri, literasi digital).",
        "integrasi_kurikulum": "Terintegrasi dalam modul pelatihan berkala pengurus asrama/keamanan serta materi kepemimpinan santri.",
        "dampak_karakter": "Meningkatkan kedewasaan emosional santri, disiplin diri berbasis kesadaran tanpa rasa takut, dan hubungan antar-santri yang sehat dan aman."
    },
    {
        "nama": "Pondok Pesantren Al-Qodiri 1",
        "alamat": "Jl. Manggar No. 139A, Kelurahan Gebang, Kecamatan Patrang, Jember, Jawa Timur 68117",
        "latitude": -8.151121,
        "longitude": 113.709212,
        "program_ramah_anak": "Pilot project PRA Kemenag, Sosialisasi MPLS Ramah Anak berkolaborasi dengan Dinsos PPPA Jember, edukasi pencegahan bullying & pernikahan dini dengan melibatkan orang tua.",
        "integrasi_kurikulum": "Dimasukkan ke dalam materi Masa Pengenalan Lingkungan Sekolah (MPLS) dan seminar parenting berkala dengan wali santri.",
        "dampak_karakter": "Memperkuat pemahaman tentang keselamatan diri, pencegahan kekerasan, serta komunikasi yang sehat dan terbuka dengan orang tua."
    },
    {
        "nama": "Pondok Pesantren Al-Hayatul Islamiyah",
        "alamat": "Jl. KH. Malik Dalam No. 01, RT 01 RW 04, Kelurahan Kedungkandang, Kecamatan Kedungkandang, Kota Malang, Jawa Timur 65137",
        "latitude": -7.9942,
        "longitude": 112.6568,
        "program_ramah_anak": "Deklarasi Pesantren Ramah Anak pertama di Malang, pembentukan satgas internal perlindungan anak, kampanye stop-bullying harian.",
        "integrasi_kurikulum": "Diintegrasikan ke dalam kurikulum pengajian kitab akhlak (pembiasaan sopan santun) dan penyuluhan psikologis santri.",
        "dampak_karakter": "Menumbuhkan empati tinggi antar-santri, menghilangkan senioritas negatif, dan menciptakan kedekatan harmonis santri-ustadz."
    },
    {
        "nama": "Pondok Pesantren Darun Nun",
        "alamat": "Perum Bukit Cemara Tidar F3, No. 4, Karangbesuki, Kecamatan Sukun, Kota Malang, Jawa Timur 65146",
        "latitude": -7.9542,
        "longitude": 112.5978,
        "program_ramah_anak": "Ditetapkan resmi sebagai Pondok Pesantren Ramah Anak melalui SK Kemenag Kota Malang, pemenuhan hak tumbuh kembang anak, bimbingan konseling santri.",
        "integrasi_kurikulum": "Metode pembelajaran terapan ramah anak serta muatan moderasi beragama dalam sistem asrama.",
        "dampak_karakter": "Membentuk santri dengan cara berpikir terbuka, toleran, mandiri secara emosional, dan santun."
    },
    {
        "nama": "Pondok Pesantren Imam Asy-Syafi'i (PPIA)",
        "alamat": "Jl. KH. Hasyim Asy'ari No. 99E, Dusun Krajan, Desa Kembiritan, Kecamatan Genteng, Kabupaten Banyuwangi, Jawa Timur 68465",
        "latitude": -8.3712,
        "longitude": 114.1685,
        "program_ramah_anak": "Menerapkan sistem asrama ramah anak, edukasi pencegahan kekerasan fisik/verbal, pembatasan ketat kontak fisik keras dalam pendisiplinan.",
        "integrasi_kurikulum": "Integrasi ke dalam etika kehidupan asrama harian dan kurikulum kajian akhlak aswaja.",
        "dampak_karakter": "Meningkatkan kontrol emosi santri, etika berbicara yang santun, dan menghilangkan budaya kekerasan di kalangan santri."
    }
]

# Compiled data for Ecotheology/Eco-Pesantren (Tema "Ecoteologi" & Lomba Lingkungan) in East Java
pesantren_ecoteologi_data = [
    {
        "nama": "Pondok Pesantren Zainul Hasan Genggong (Zainul Hasanain)",
        "alamat": "Desa Karangbong, Kecamatan Pajarakan, Kabupaten Probolinggo, Jawa Timur 67281",
        "latitude": -7.7981,
        "longitude": 113.3332,
        "program_ecoteologi": "Ditetapkan sebagai Duta Eco-Pesantren Jawa Timur, pengelolaan bank sampah santri, pembuatan pupuk organik dari limbah asrama, dan gerakan konservasi air.",
        "prestasi_lomba": "Penghargaan Duta Eco-Pesantren Jawa Timur dan pelopor gerakan kebersihan lingkungan pesantren tingkat regional.",
        "dampak_karakter": "Santri memiliki tanggung jawab ekologis yang tinggi, sadar akan kebersihan spiritual dan fisik, serta terbiasa mandiri mengelola sampah."
    },
    {
        "nama": "Pondok Pesantren Mamba'ul Hikam",
        "alamat": "Desa Diwek, Kecamatan Diwek, Kabupaten Jombang, Jawa Timur 61471",
        "latitude": -7.5851,
        "longitude": 112.2281,
        "program_ecoteologi": "Mengembangkan ekomoderasi, gerakan sedekah sampah oleh santri, kolaborasi pelestarian alam lintas iman di sekitar mata air setempat.",
        "prestasi_lomba": "Pelopor ekomoderasi pesantren Jawa Timur, aktif dalam lokakarya aksi ekologis lintas agama Kemenag.",
        "dampak_karakter": "Membangun karakter inklusif, toleransi sosial yang kuat, kepedulian lingkungan praktis, dan kepekaan sosial santri."
    },
    {
        "nama": "Pondok Pesantren Al-Umm",
        "alamat": "Jl. Joyo Agung No.1, Merjosari, Kecamatan Lowokwaru, Kota Malang, Jawa Timur 65144",
        "latitude": -7.9404,
        "longitude": 112.5932,
        "program_ecoteologi": "Pengolahan limbah mandiri (sistem sanitasi terpadu), pembuatan eco-enzim dari kulit buah/sayur dapur, penanaman sayur organik skala asrama.",
        "prestasi_lomba": "Meraih penghargaan Juara Lomba Eco-Pesantren tingkat Kota Malang.",
        "dampak_karakter": "Menumbuhkan etos kerja keras santri, rasa menghargai bahan pangan, dan kecintaan pada alam sekitar."
    },
    {
        "nama": "Pondok Pesantren Maftahul 'Uluum Jatinom",
        "alamat": "Jatinom, Kecamatan Kanigoro, Kabupaten Blitar, Jawa Timur 66181",
        "latitude": -8.1158,
        "longitude": 112.2132,
        "program_ecoteologi": "Penerapan pertanian dan peternakan organik terintegrasi (agroforestry pesantren), konservasi air tanah menggunakan biopori.",
        "prestasi_lomba": "Masuk nominasi Anugerah Eco-Pesantren tingkat Provinsi Jawa Timur.",
        "dampak_karakter": "Membentuk santri yang cakap bertani mandiri, menghargai kelestarian bumi, dan berdisiplin tinggi."
    },
    {
        "nama": "Pondok Pesantren Darun Najah",
        "alamat": "Desa Petahunan, Kecamatan Sumbersuko, Kabupaten Lumajang, Jawa Timur 67316",
        "latitude": -8.1561,
        "longitude": 113.2081,
        "program_ecoteologi": "Kampanye zero plastic di asrama, bank sampah daur ulang, pembuatan biogas dari limbah peternakan pondok.",
        "prestasi_lomba": "Masuk 3 besar Lomba Eco-Pesantren tingkat Provinsi Jawa Timur.",
        "dampak_karakter": "Membiasakan santri hidup sederhana, hemat energi, peduli kelestarian iklim, dan inovatif."
    },
    {
        "nama": "Pondok Pesantren Wali Barokah",
        "alamat": "Jl. Hos Cokroaminoto No. 195, Burengan, Kecamatan Pesantren, Kota Kediri, Jawa Timur 64131",
        "latitude": -7.8281,
        "longitude": 112.0298,
        "program_ecoteologi": "Penerapan pembangkit listrik tenaga surya (PLTS) skala besar di lingkungan pondok untuk efisiensi energi terbarukan, pengelolaan air limbah (greywater) ramah lingkungan.",
        "prestasi_lomba": "Pesantren pionir energi terbarukan (PLTS) di Jawa Timur, memperoleh apresiasi khusus dari Pemprov Jatim dan Kemenag.",
        "dampak_karakter": "Menanamkan wawasan teknologi ramah lingkungan, kepekaan terhadap krisis energi global, serta kemandirian teknologi."
    },
    {
        "nama": "Pondok Pesantren Gadingmangu",
        "alamat": "Gadingmangu, Kecamatan Perak, Kabupaten Jombang, Jawa Timur 61461",
        "latitude": -7.5612,
        "longitude": 112.1648,
        "program_ecoteologi": "Kurikulum fiqh bi'ah (fikih lingkungan), gerakan massal penanaman pohon penghijauan, pembatasan penggunaan kemasan sekali pakai bagi santri.",
        "prestasi_lomba": "Pelopor gerakan penanaman ribuan pohon di Jombang bekerjasama dengan Perhutani, penerima sertifikat Eco-Pesantren.",
        "dampak_karakter": "Santri memiliki kepedulian tinggi terhadap reboisasi, pemahaman ekologis syar'i (fiqh lingkungan), dan disiplin pelestarian air."
    },
    {
        "nama": "Pondok Pesantren Al Ubaidah",
        "alamat": "Jl. KH. Wahid Hasyim No. 23, Kertosono, Kecamatan Kertosono, Kabupaten Nganjuk, Jawa Timur 64311",
        "latitude": -7.5951,
        "longitude": 112.1281,
        "program_ecoteologi": "Inovasi komposter limbah organik dapur asrama, kampanye ecobrick kreatif untuk infrastruktur taman pondok.",
        "prestasi_lomba": "Pesantren pelopor pengolahan sampah mandiri Kabupaten Nganjuk, nominasi eco-pesantren berwawasan kerajinan lingkungan.",
        "dampak_karakter": "Membentuk santri yang kreatif dalam daur ulang, memiliki kesadaran tinggi akan bahaya plastik, dan rajin bergotong royong menjaga kebersihan."
    }
]

# Write Ramah data
ramah_csv_path = "pesantren_ramah_jatim.csv"
ramah_json_path = "pesantren_ramah_jatim.json"

try:
    with open(ramah_csv_path, mode='w', newline='', encoding='utf-8') as csv_file:
        fieldnames = ["nama", "alamat", "latitude", "longitude", "program_ramah_anak", "integrasi_kurikulum", "dampak_karakter"]
        writer = csv.DictWriter(csv_file, fieldnames=fieldnames)
        writer.writeheader()
        for row in pesantren_ramah_data:
            writer.writerow(row)
    print(f"Successfully generated RAMAH CSV at: {os.path.abspath(ramah_csv_path)}")
except Exception as e:
    print(f"Error writing RAMAH CSV: {e}")

try:
    with open(ramah_json_path, mode='w', encoding='utf-8') as json_file:
        json.dump(pesantren_ramah_data, json_file, indent=4, ensure_ascii=False)
    print(f"Successfully generated RAMAH JSON at: {os.path.abspath(ramah_json_path)}")
except Exception as e:
    print(f"Error writing RAMAH JSON: {e}")


# Write Ecoteologi data
ecoteologi_csv_path = "pesantren_ecoteologi_jatim.csv"
ecoteologi_json_path = "pesantren_ecoteologi_jatim.json"

try:
    with open(ecoteologi_csv_path, mode='w', newline='', encoding='utf-8') as csv_file:
        fieldnames = ["nama", "alamat", "latitude", "longitude", "program_ecoteologi", "prestasi_lomba", "dampak_karakter"]
        writer = csv.DictWriter(csv_file, fieldnames=fieldnames)
        writer.writeheader()
        for row in pesantren_ecoteologi_data:
            writer.writerow(row)
    print(f"Successfully generated ECOTEOLOGI CSV at: {os.path.abspath(ecoteologi_csv_path)}")
except Exception as e:
    print(f"Error writing ECOTEOLOGI CSV: {e}")

try:
    with open(ecoteologi_json_path, mode='w', encoding='utf-8') as json_file:
        json.dump(pesantren_ecoteologi_data, json_file, indent=4, ensure_ascii=False)
    print(f"Successfully generated ECOTEOLOGI JSON at: {os.path.abspath(ecoteologi_json_path)}")
except Exception as e:
    print(f"Error writing ECOTEOLOGI JSON: {e}")
