<?php
// send_email.php
// Ganti alamat email di bawah ini dengan email owner yang diinginkan
$to = "bwfzbw@gmail.com"; // <-- GANTI EMAIL INI

header('Content-Type: application/json');
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['from'], $data['subject'], $data['body'])) {
    echo json_encode(['success' => false, 'error' => 'Data tidak lengkap']);
    exit;
}

$from = filter_var($data['from'], FILTER_VALIDATE_EMAIL);
$subject = strip_tags($data['subject']);
$body = strip_tags($data['body']);

if (!$from) {
    echo json_encode(['success' => false, 'error' => 'Email pengirim tidak valid']);
    exit;
}

$headers = "From: $from\r\n" .
           "Reply-To: $from\r\n" .
           "Content-Type: text/plain; charset=UTF-8\r\n";

$mailBody = "Pesan dari: $from\n\nJudul: $subject\n\nIsi Pesan:\n$body\n";

if (mail($to, $subject, $mailBody, $headers)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Gagal mengirim email.']);
}
