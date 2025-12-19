<?php

$path = __DIR__ . '/.env';
$content = file_get_contents($path);

// Cari posisi VITE_PUSHER_APP_CLUSTER, ambil sampai akhir baris itu.
// Lalu potong sisanya (yang berisi sampah UTF-16).
$key = 'VITE_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"';
$pos = strpos($content, $key);

if ($pos !== false) {
    // Ambil konten valid sampai akhir baris VITE_PUSHER
    $cleanContent = substr($content, 0, $pos + strlen($key));

    // Simpan kembali dengan nambahin newline bersih
    file_put_contents($path, $cleanContent . "\n");
    echo "Fixed .env file successfully.\n";
} else {
    echo "Marker not found. content length: " . strlen($content) . "\n";
    // Fallback: coba regex remove MIDTRANS lines including binary garbage
    // Remove anything looking like M.I.D.T.R.A.N.S
    $cleanContent = preg_replace('/M\x00I\x00D\x00T\x00.*/s', '', $content);
    $cleanContent = preg_replace('/MIDTRANS_ALLOWED_IPS.*/s', '', $cleanContent);
    file_put_contents($path, $cleanContent);
    echo "Attempted regex fix.\n";
}
