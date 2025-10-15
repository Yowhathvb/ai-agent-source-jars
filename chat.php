<?php
header('Content-Type: application/json; charset=utf-8');

// =====================
// Konfigurasi Database
// =====================
// Load shared config (DB credentials + API key)
require_once __DIR__ . '/config.php';

// =====================
// Konfigurasi API Gemini
// =====================
$GENAI_ENDPOINT = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent";
$GENAI_API_KEY  = defined('GENAI_API_KEY') ? GENAI_API_KEY : ''; // from config.php

// =====================
// Ambil input JSON
// =====================
$input = json_decode(file_get_contents("php://input"), true);

$message = isset($input['message']) ? trim($input['message']) : "";
$lang = isset($input['lang']) ? $input['lang'] : 'id';

if ($message === "") {
    echo json_encode(["error" => "Pesan kosong"]);
    exit;
}

// =====================
// Koneksi Database
// =====================
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    echo json_encode(["error" => "DB connection failed"]);
    exit;
}

// =====================
// Ambil semua produk
// =====================
$stmt = $conn->prepare("SELECT id, sku, name, short_desc, long_desc, price, stock, category, image_url FROM products");
$stmt->execute();
$result = $stmt->get_result();

$allProducts = [];
while($row = $result->fetch_assoc()){
    $allProducts[] = $row;
}
$stmt->close();
$conn->close();

// =====================
// Cek pertanyaan "produk apa saja"
// =====================
$lowerMsg = strtolower($message);
if (strpos($lowerMsg, "produk apa saja") !== false || strpos($lowerMsg, "ada produk apa") !== false) {
    $contextText = "";
    foreach($allProducts as $p){
        $contextText .= "- {$p['name']} (Rp".number_format($p['price'],0,",",".").", stok: {$p['stock']})\n";
    }
    if ($lang === 'en') {
        $answer = "Here is a list of products currently available:\n\n".$contextText;
    } else {
        $answer = "Berikut daftar produk yang tersedia saat ini:\n\n".$contextText;
    }
    echo json_encode([
        "answer" => $answer,
        "products" => $allProducts
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// =====================
// Pertanyaan umum → panggil Gemini
// =====================

if ($lang === 'en') {
    $prompt = "You are a sales assistant. User's question: \"$message\".\n".
              "Here are some products you can offer:\n";
    foreach($allProducts as $p){
        $prompt .= "- {$p['name']} ({$p['short_desc']}), price Rp".number_format($p['price'],0,",",".").", stock: {$p['stock']}\n";
    }
    $prompt .= "\nPlease answer the user's question in English.";
} else {
    $prompt = "Kamu adalah asisten penjualan. Pertanyaan user: \"$message\".\n".
              "Berikut beberapa produk yang bisa kamu tawarkan:\n";
    foreach($allProducts as $p){
        $prompt .= "- {$p['name']} ({$p['short_desc']}), harga Rp".number_format($p['price'],0,",",".").", stok: {$p['stock']}\n";
    }
    $prompt .= "\nJawablah pertanyaan user dalam bahasa Indonesia.";
}

// Panggil API Gemini pakai cURL
$ch = curl_init($GENAI_ENDPOINT."?key=".$GENAI_API_KEY);
$data = [
    "contents" => [[ "parts" => [[ "text" => $prompt ]] ]]
];

curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json"
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

$response = curl_exec($ch);
if (curl_errno($ch)) {
    echo json_encode(["error" => curl_error($ch)]);
    exit;
}
curl_close($ch);

// Ambil jawaban AI
$aiResp = json_decode($response, true);
$answer = $aiResp["candidates"][0]["content"]["parts"][0]["text"] ?? "Maaf, saya tidak menemukan jawaban.";

// =====================
// Ambil produk yang disebut AI saja
// =====================
$recommendedProducts = [];
foreach($allProducts as $p){
    if(stripos($answer, $p['name']) !== false){
        $recommendedProducts[] = $p;
    }
}

// =====================
// Kirim ke frontend
// =====================
echo json_encode([
    "answer" => $answer,
    "products" => $recommendedProducts
], JSON_UNESCAPED_UNICODE);
