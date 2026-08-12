<?php

namespace App\Http\Controllers;

class DashboardController
{
    public function index()
    {
        $stats = [
            'total_pesantren' => 7423,
            'enterpreneur' => 387,
            'digital' => 9,
            'karya_kitab' => 6,
            'ecoteologi' => 16,
            'aman' => 7423,
            'ramah_anak' => 7,
        ];

        return $this->renderView('dashboard', [
            'pageTitle' => 'PENDEKAR | PD PONTREN JAWA TIMUR',
            'stats' => $stats
        ]);
    }

    public function apiPesantren()
    {
        header('Content-Type: application/json');
        $mapPath = __DIR__ . '/../../../public/data/pesantren_map_data.json';
        $dataPath = __DIR__ . '/../../../public/data/pesantren_integrated.json';
        
        if (file_exists($mapPath)) {
            echo file_get_contents($mapPath);
        } elseif (file_exists($dataPath)) {
            echo file_get_contents($dataPath);
        } else {
            $fallback = __DIR__ . '/../../../data/pesantren_integrated.json';
            if (file_exists($fallback)) {
                echo file_get_contents($fallback);
            } else {
                echo json_encode([]);
            }
        }
        exit;
    }

    public function apiStats()
    {
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success',
            'data' => [
                'total_pesantren' => 1250,
                'karya_ilmiah' => 2500,
                'produk_inovasi' => 150,
                'layanan' => '24/7 Online'
            ]
        ]);
        exit;
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
