<?php
header('Content-Type: application/json; charset=utf-8');

// Konfigurasi database
$dbHost = "localhost";
$dbUser = "root";
$dbPass = "";
$dbName = "product_catalog";

// Konfigurasi API Gemini
$GENAI_ENDPOINT = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent";
$GENAI_API_KEY  = "AIzaSyDpZaWZKkwGNiFMqVpERWVm-GxQmgNMFyQ"; // ⚠️ ganti dengan key baru, jangan pakai yang sudah diposting publik

// Ambil input JSON dari frontend
$input = json_decode(file_get_contents("php://input"), true);
$message = isset($input['message']) ? trim($input['message']) : "";

if ($message === "") {
    echo json_encode(["error" => "Pesan kosong"]);
    exit;
}

// Koneksi DB
$conn = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
if ($conn->connect_error) {
    echo json_encode(["error" => "DB connection failed"]);
    exit;
}

// Ambil semua produk
$stmt = $conn->prepare("SELECT id, sku, name, short_desc, long_desc, price, stock, category, image_url FROM products");
$stmt->execute();
$result = $stmt->get_result();

$allProducts = [];
while($row = $result->fetch_assoc()){
    $allProducts[] = $row;
}
$stmt->close();
$conn->close();

$lowerMsg = strtolower($message);

// Intent: user minta list produk
if (strpos($lowerMsg, "produk apa saja") !== false || strpos($lowerMsg, "ada produk apa") !== false) {
    $contextText = "";
    foreach($allProducts as $p){
        $contextText .= "- {$p['name']} (Rp".number_format($p['price'],0,",",".").", stok: {$p['stock']})\n";
    }

    $answer = "Berikut daftar produk yang tersedia saat ini:\n\n".$contextText;
    echo json_encode([
        "answer" => $answer,
        "products" => $allProducts
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Intent: user tanya spesifik produk
$foundProduct = null;
foreach ($allProducts as $p) {
    if (strpos($lowerMsg, strtolower($p['name'])) !== false || strpos($lowerMsg, strtolower($p['sku'])) !== false) {
        $foundProduct = $p;
        break;
    }
}

if ($foundProduct) {
    $answer = "Produk <b>{$foundProduct['name']}</b>:\n".
              "- Deskripsi: {$foundProduct['short_desc']}\n".
              "- Keunggulan: {$foundProduct['long_desc']}\n".
              "- Harga: Rp".number_format($foundProduct['price'],0,",",".")."\n".
              "- Stok: {$foundProduct['stock']}";
    echo json_encode([
        "answer" => $answer,
        "products" => [$foundProduct]
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Pertanyaan umum → kirim ke Gemini TANPA spam produk
$prompt = "Kamu adalah asisten toko online. Jawablah singkat dan jelas pertanyaan berikut: \"$message\".\n";

// Panggil API Gemini
$ch = curl_init($GENAI_ENDPOINT."?key=".$GENAI_API_KEY);
$data = [
    "contents" => [[ "parts" => [[ "text" => $prompt ]] ]]
];

curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

$response = curl_exec($ch);
if (curl_errno($ch)) {
    echo json_encode(["error" => curl_error($ch)]);
    exit;
}
curl_close($ch);

$aiResp = json_decode($response, true);
$answer = $aiResp["candidates"][0]["content"]["parts"][0]["text"] ?? "Maaf, saya tidak menemukan jawaban.";

// Balikkan hasil ke frontend
echo json_encode([
    "answer" => $answer,
    "products" => [] // kosong, karena bukan pertanyaan produk
], JSON_UNESCAPED_UNICODE);
