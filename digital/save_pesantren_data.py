import csv
import os

# Compiled data of 9 major digital pesantren in East Java (Jatim)
pesantren_data = [
    {
        "Nama Pesantren": "Pondok Pesantren Nurul Jadid",
        "Alamat": "Karanganyar, Paiton, Probolinggo, Jawa Timur",
        "Kabupaten/Kota": "Probolinggo",
        "Unggulan Aplikasi": "Bekal Santri NJ (e-Bekal)",
        "Layanan Digital": "Kartu smart card cashless untuk transaksi kantin & koperasi, presensi kegiatan, perizinan santri, layanan kesehatan, dan Virtual Account untuk top-up saldo.",
        "Dampak Kualitas": "Meningkatkan transparansi keuangan, memudahkan wali santri melakukan monitoring secara real-time, mencegah kehilangan/pencurian uang di asrama, dan melatih kedisiplinan finansial santri.",
        "Website/Referensi": "https://www.nuruljadid.net/"
    },
    {
        "Nama Pesantren": "Pondok Pesantren Al-Amien Prenduan",
        "Alamat": "Jln. Raya Prenduan, Prataan, Prenduan, Sumenep, Madura, Jawa Timur",
        "Kabupaten/Kota": "Sumenep",
        "Unggulan Aplikasi": "SAS TMI / SiSMTA",
        "Layanan Digital": "Transaksi nontunai (cashless) berbasis sidik jari (fingerprint) di koperasi/kantin, virtual account (BNI) terintegrasi NIS untuk pengiriman uang saku, and monitoring transaksi real-time.",
        "Dampak Kualitas": "Menjamin keamanan dan kecepatan transaksi harian santri, transparansi penuh penggunaan uang saku bagi wali santri, serta efisiensi administrasi internal pondok.",
        "Website/Referensi": "https://tmial-amien.sch.id/"
    },
    {
        "Nama Pesantren": "Pondok Pesantren Sidogiri",
        "Alamat": "Sidogiri, Kraton, Pasuruan, Jawa Timur",
        "Kabupaten/Kota": "Pasuruan",
        "Unggulan Aplikasi": "Walisantri Sidogiri (Android & iOS)",
        "Layanan Digital": "Portal pemantauan data santri, nilai akademik & rapot digital, administrasi & iuran bulanan, riwayat perizinan, rekam medis kesehatan, dan sistem transfer uang saku terintegrasi.",
        "Dampak Kualitas": "Meningkatkan kualitas komunikasi dan kepercayaan orang tua (wali santri) melalui pemantauan jarak jauh yang transparan dan real-time.",
        "Website/Referensi": "https://sidogiri.net/"
    },
    {
        "Nama Pesantren": "Pondok Modern Darussalam Gontor",
        "Alamat": "Gontor, Mlarak, Ponorogo, Jawa Timur",
        "Kabupaten/Kota": "Ponorogo",
        "Unggulan Aplikasi": "Gontor App (GontorPay) & ADM Gontor",
        "Layanan Digital": "Transaksi cashless terintegrasi kartu santri di kantin & koperasi, virtual account untuk pengisian uang saku (Tabungan Santri/Tabsis) dan pembayaran SPP.",
        "Dampak Kualitas": "Mengeliminasi risiko kehilangan uang tunai santri di asrama, mempermudah wali santri memantau pengeluaran real-time, and memodernisasi tata kelola administrasi keuangan pondok.",
        "Website/Referensi": "https://www.gontor.ac.id/"
    },
    {
        "Nama Pesantren": "Pondok Pesantren Wali Barokah",
        "Alamat": "Jln. Hos Cokroaminoto No. 195, Burengan, Kediri, Jawa Timur",
        "Kabupaten/Kota": "Kediri",
        "Unggulan Aplikasi": "Smart Card (Digitren) terintegrasi BSI & TKI",
        "Layanan Digital": "Transaksi cashless untuk belanja harian di koperasi/toko pondok, absensi kehadiran kegiatan belajar mengajar digital, dan manajemen porsi makan santri di dapur secara digital.",
        "Dampak Kualitas": "Meningkatkan efisiensi logistik dapur (porsi makan terpantau otomatis), akuntabilitas koperasi pondok, serta pembiasaan teknologi digital bagi ribuan santri.",
        "Website/Referensi": "https://walibarokah.org/"
    },
    {
        "Nama Pesantren": "Pondok Pesantren Gadingmangu",
        "Alamat": "Jln. Masjid Luhur, Gadingmangu, Perak, Jombang, Jawa Timur",
        "Kabupaten/Kota": "Jombang",
        "Unggulan Aplikasi": "Smartcard B-School (terintegrasi BSI)",
        "Layanan Digital": "Presensi kegiatan mengaji dan makan via smartcard, transaksi cashless di koperasi pondok, serta monitoring real-time keuangan santri bagi orang tua.",
        "Dampak Kualitas": "Mencegah kehilangan uang fisik, efisiensi presensi kehadiran kegiatan pondok, and memudahkan monitoring uang saku santri oleh orang tua secara real-time.",
        "Website/Referensi": "https://pondokgadingmangu.org/"
    },
    {
        "Nama Pesantren": "Pondok Pesantren Tebuireng",
        "Alamat": "Jln. Irian Jaya No. 10, Tebuireng, Cukir, Diwek, Jombang, Jawa Timur",
        "Kabupaten/Kota": "Jombang",
        "Unggulan Aplikasi": "Lantabur Mobile & Kartu E-Santri",
        "Layanan Digital": "Alat transaksi cashless di kantin & koperasi terintegrasi BPRS Lantabur Tebuireng, pembayaran SPP/Syahriah bulanan menggunakan BRI Virtual Account (BRIVA).",
        "Dampak Kualitas": "Menjamin keamanan uang saku santri (meminimalisir peredaran uang tunai), memberikan kemudahan transaksi dan pengawasan pengeluaran santri secara real-time dari luar kota.",
        "Website/Referensi": "https://tebuireng.online/"
    },
    {
        "Nama Pesantren": "Pondok Pesantren Al-Amanah Junwangi",
        "Alamat": "Junwangi, Krian, Sidoarjo, Jawa Timur",
        "Kabupaten/Kota": "Sidoarjo",
        "Unggulan Aplikasi": "Ponpes Al Amanah Junwangi (Android) & e-Pesantren",
        "Layanan Digital": "Sistem digitalisasi uang saku cashless santri, integrasi manajemen administrasi & keuangan internal via e-Pesantren, serta dashboard monitoring wali santri secara real-time.",
        "Dampak Kualitas": "Meningkatkan efisiensi pengelolaan administrasi internal pondok, menekan risiko kehilangan uang tunai santri, and memberikan laporan keuangan transparan kepada wali santri.",
        "Website/Referensi": "https://al-amanah.id/"
    },
    {
        "Nama Pesantren": "Pondok Pesantren Salafiyah Syafi'iyah Sukorejo",
        "Alamat": "Sumberejo, Banyuputih, Situbondo, Jawa Timur",
        "Kabupaten/Kota": "Situbondo",
        "Unggulan Aplikasi": "el-Santri, SIMANTAP (Sistem UTAP), SABAR (Sistem Pendaftaran)",
        "Layanan Digital": "Kartu elektronik 'el-Santri' untuk transaksi cashless harian, pemantauan kesehatan, perkembangan akademik, data pelanggaran/prestasi, pendaftaran online & Virtual Account.",
        "Dampak Kualitas": "Tata kelola keuangan dan administrasi puluhan ribu santri terpusat, transparan, dan efisien, serta memudahkan interaksi dan koordinasi wali santri dari seluruh penjuru Indonesia.",
        "Website/Referensi": "https://www.sukorejo.com/"
    }
]

csv_file = "pesantren_digital_jatim.csv"
fieldnames = [
    "Nama Pesantren",
    "Alamat",
    "Kabupaten/Kota",
    "Unggulan Aplikasi",
    "Layanan Digital",
    "Dampak Kualitas",
    "Website/Referensi"
]

try:
    with open(csv_file, mode='w', encoding='utf-8-sig', newline='') as file:
        writer = csv.DictWriter(file, fieldnames=fieldnames)
        writer.writeheader()
        writer.writerows(pesantren_data)
    print(f"Successfully generated {csv_file} with {len(pesantren_data)} records.")
except Exception as e:
    print(f"Error generating CSV file: {e}")
