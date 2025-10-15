<?php
// order_email.php
// Ganti alamat email di bawah ini dengan email owner yang diinginkan
$to = "bwfzbw@gmail.com"; // <-- GANTI EMAIL INI

header('Content-Type: application/json');
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['name'], $data['email'], $data['product'], $data['qty'], $data['address'])) {
    echo json_encode(['success' => false, 'error' => 'Data tidak lengkap']);
    exit;
}

$name = strip_tags($data['name']);
$email = filter_var($data['email'], FILTER_VALIDATE_EMAIL);
$product = strip_tags($data['product']);
$qty = intval($data['qty']);
$address = strip_tags($data['address']);

if (!$email) {
    echo json_encode(['success' => false, 'error' => 'Email tidak valid']);
    exit;
}

$subject = "Pesanan Baru dari $name";
$mailBody = "Pesanan Baru dari Chat Agent\n\n" .
            "Nama: $name\n" .
            "Email: $email\n" .
            "Produk: $product\n" .
            "Jumlah: $qty\n" .
            "Alamat: $address\n";
$headers = "From: $email\r\n" .
           "Reply-To: $email\r\n" .
           "Content-Type: text/plain; charset=UTF-8\r\n";

if (mail($to, $subject, $mailBody, $headers)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Gagal mengirim email.']);
}
