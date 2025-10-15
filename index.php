<?php
// Ambil 5 produk dari database (gunakan config.php)
require_once __DIR__ . '/config.php';

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) die("DB connection failed");

$stmt = $conn->prepare("SELECT id, sku, name, price, stock, category, image_url FROM products ORDER BY id ASC LIMIT 5");
$stmt->execute();
$result = $stmt->get_result();
$fiveProducts = [];
while($row = $result->fetch_assoc()){
    $fiveProducts[] = $row;
}
$stmt->close();
$conn->close();
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8" />
  <title>Chat & Produk</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/style.css">
</head>
<header class="navbar">
  <div class="logo">ShopAI</div>
  <button class="search-btn">🔍</button>
  <input type="text" placeholder="Cari produk..." class="search-box">
</header>

<body>
  <!-- Chat button dan panel -->
  <div class="chat-button" id="openBtn">Chat</div>
  <!-- Language selection now in chat bubble -->
  <div class="chat-panel" id="panel">
    <div class="chat-header">Asisten Produk</div>
    <div class="chat-body" id="body">
      <!-- Sapaan akan diisi via JS setelah pilih bahasa -->
    </div>
    <div class="chat-input">
      <input id="inputMsg" placeholder="Ketik pesan..." />
      <button id="sendBtn">Kirim</button>
    </div>
  </div>

  <!-- Produk 5 item -->
  <h3 style="margin:20px 20px 10px 20px;">5 Produk Teratas</h3>
  <div class="products">
    <?php foreach($fiveProducts as $p): ?>
      <div class="product-card">
  <?php if($p['image_url']): ?>
    <img src="<?= htmlspecialchars($p['image_url']) ?>" alt="<?= htmlspecialchars($p['name']) ?>">
  <?php else: ?>
    <div style="width:100%;height:160px;background:#eee;border-radius:6px;margin-bottom:6px;"></div>
  <?php endif; ?>
  <div class="name"><?= htmlspecialchars($p['name']) ?></div>
  <div class="price">Rp<?= number_format($p['price'],0,",",".") ?></div>
  <div class="meta">Stok: <?= $p['stock'] ?> | <?= htmlspecialchars($p['category']) ?></div>
  <button>Beli Sekarang</button>
</div>

    <?php endforeach; ?>
  </div>

  <script src="assets/chat.js"></script>
</body>
<footer class="footer">
  <p>© 2025 ShopAI.</p>
</footer>

</html>

