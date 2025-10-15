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
  <style>
    .chat-button { position: fixed; right:20px; bottom:20px; width:60px; height:60px; border-radius:50%; background:#0ea5e9; color:#fff; font-weight:bold; display:flex; align-items:center; justify-content:center; cursor:pointer; box-shadow:0 4px 12px rgba(0,0,0,0.2); }
    .chat-panel { position: fixed; right:20px; bottom:90px; width:320px; max-height:70vh; background:#fff; border-radius:10px; box-shadow:0 6px 20px rgba(0,0,0,0.25); display:none; flex-direction:column; overflow:hidden; }
    .chat-header { background:#0ea5e9; color:#fff; padding:10px; font-weight:600; }
    .chat-body { flex:1; padding:10px; overflow-y:auto; background:#f8fafc; }
    .chat-input { display:flex; gap:5px; padding:8px; border-top:1px solid #ddd; }
    .chat-input input { flex:1; padding:6px; border:1px solid #ccc; border-radius:6px; }
    .message { margin:6px 0; }
    .msg-user { text-align:right; }
    .msg-user .msg-text { background:#dcfce7; display:inline-block; padding:6px 10px; border-radius:8px; max-width:80%; }
    .msg-bot .msg-text { background:#fff; display:inline-block; padding:6px 10px; border-radius:8px; box-shadow:0 1px 4px rgba(0,0,0,0.1); max-width:80%; text-align:left; }
    .small { font-size:12px; color:#555; margin-top:4px; }
    .msg-text b { color:#0ea5e9; }
    .products { display:flex; flex-wrap:wrap; gap:12px; margin:20px; }
    .product-card { border:1px solid #ddd; border-radius:8px; padding:10px; width:150px; text-align:center; box-shadow:0 2px 8px rgba(0,0,0,0.1); }
    .product-card img { width:100px; height:100px; object-fit:cover; border-radius:6px; margin-bottom:6px; }
  </style>
</head>
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
          <div style="width:100px;height:100px;background:#eee;border-radius:6px;margin-bottom:6px;"></div>
        <?php endif; ?>
        <div><b><?= htmlspecialchars($p['name']) ?></b></div>
        <div>Rp<?= number_format($p['price'],0,",",".") ?></div>
        <div>Stok: <?= $p['stock'] ?></div>
        <div><?= htmlspecialchars($p['category']) ?></div>
      </div>
    <?php endforeach; ?>
  </div>

  <script>
    const openBtn = document.getElementById('openBtn');
    const panel = document.getElementById('panel');
    const body = document.getElementById('body');
    const input = document.getElementById('inputMsg');
    const sendBtn = document.getElementById('sendBtn');

    let selectedLang = null;

    openBtn.onclick = ()=> {
      panel.style.display = panel.style.display==='none' || panel.style.display==='' ? 'flex':'none';
      if (panel.style.display === 'flex' && !selectedLang) {
        showLangChoice();
      }
    };

    function showLangChoice() {
      body.innerHTML = '';
      // Nonaktifkan input dan tombol kirim
      input.disabled = true;
      sendBtn.disabled = true;
      // Bubble dengan dua tombol bahasa dan pesan bantuan
      const msg = document.createElement('div');
      msg.className = 'message msg-bot';
      const bubble = document.createElement('div');
      bubble.className = 'msg-text';
      bubble.innerHTML = `Silakan pilih bahasa anda<br><span style='font-size:13px;color:#666;'>Please select your language</span><br><br>
        <button id='btnLangId' style='margin:6px 10px 0 0;padding:7px 16px;font-size:14px;border-radius:7px;border:none;background:#0ea5e9;color:#fff;cursor:pointer;'>Bahasa Indonesia</button>
        <button id='btnLangEn' style='margin:6px 0 0 0;padding:7px 16px;font-size:14px;border-radius:7px;border:none;background:#0ea5e9;color:#fff;cursor:pointer;'>English</button><br><br>
        <span style='font-size:13px;color:#888;'>Gunakan tombol di bawah ini untuk memulai percakapan / Use the buttons below to start</span>`;
      msg.appendChild(bubble);
      body.appendChild(msg);
      body.scrollTop = body.scrollHeight;
      setTimeout(()=>{
        document.getElementById('btnLangId').onclick = ()=> chooseLang('id');
        document.getElementById('btnLangEn').onclick = ()=> chooseLang('en');
      }, 100);
    }

    function chooseLang(lang) {
      selectedLang = lang;
      body.innerHTML = '';
      // Aktifkan input dan tombol kirim setelah pilih bahasa
      input.disabled = false;
      sendBtn.disabled = false;
      // Tampilkan sapaan dan pilihan bantuan dalam bentuk button
      let greet, btnProduk, btnCS, btnOrder;
      if(lang==='id') {
        greet = 'Halo! Saya asisten layanan pelanggan toko ini. Ada yang bisa saya bantu?';
        btnProduk = 'Tanya Produk';
        btnCS = 'Hubungi CS';
        btnOrder = 'Buat Pesanan';
        input.placeholder = 'Ketik pesan...';
        sendBtn.textContent = 'Kirim';
      } else {
        greet = 'Hello! I am the customer service assistant for this shop. How can I help you today?';
        btnProduk = 'Ask About Products';
        btnCS = 'Contact CS';
        btnOrder = 'Place Order';
        input.placeholder = 'Type a message...';
        sendBtn.textContent = 'Send';
      }
      // Bubble dengan tombol (tanpa Bantuan Lainnya)
      const msg = document.createElement('div');
      msg.className = 'message msg-bot';
      const bubble = document.createElement('div');
      bubble.className = 'msg-text';
      bubble.innerHTML = `${greet}<br><br>`+
        `<button id='btnProduk' style='margin:4px 8px 4px 0;padding:7px 16px;font-size:14px;border-radius:7px;border:none;background:#0ea5e9;color:#fff;cursor:pointer;'>${btnProduk}</button>`+
        `<button id='btnOrder' style='margin:4px 8px 4px 0;padding:7px 16px;font-size:14px;border-radius:7px;border:none;background:#fbbf24;color:#fff;cursor:pointer;'>${btnOrder}</button>`+
        `<button id='btnCS' style='margin:4px 8px 4px 0;padding:7px 16px;font-size:14px;border-radius:7px;border:none;background:#22c55e;color:#fff;cursor:pointer;'>${btnCS}</button>`;
      msg.appendChild(bubble);
      body.appendChild(msg);
      body.scrollTop = body.scrollHeight;
      setTimeout(()=>{
        document.getElementById('btnProduk').onclick = ()=> handleQuickAction('produk');
        document.getElementById('btnOrder').onclick = ()=> handleQuickAction('order');
        document.getElementById('btnCS').onclick = ()=> handleQuickAction('cs');
      }, 100);
    }

    // Handler tombol bantuan cepat
    function handleQuickAction(action) {
      let userMsg = '';
      if(action==='produk') {
        // Isi input dengan template pertanyaan, user harus klik Kirim agar dikirim ke AI
        input.value = selectedLang==='id' ? 'Saya ingin bertanya mengenai produk dari website ini.' : 'I want to ask about the products from this website.';
        input.focus();
        return;
      } else if(action==='order') {
        userMsg = selectedLang==='id' ? 'Saya ingin buat pesanan' : 'I want to place an order';
        appendMessage(userMsg, 'user');
        showOrderForm();
      } else if(action==='cs') {
        userMsg = selectedLang==='id' ? 'Saya ingin menghubungi customer service' : 'I want to contact customer service';
        appendMessage(userMsg, 'user');
        showEmailForm();
      } else {
        userMsg = selectedLang==='id' ? 'Saya butuh bantuan lain' : 'I need other help';
        appendMessage(userMsg, 'user');
        sendQuick(userMsg);
      }

    // Tampilkan form order di chat
    function showOrderForm() {
      const msg = document.createElement('div');
      msg.className = 'message msg-bot';
      const bubble = document.createElement('div');
      bubble.className = 'msg-text';
      bubble.innerHTML =
        `<b>${selectedLang==='id' ? 'Formulir Pesanan' : 'Order Form'}</b><br><br>`+
        `<form id='formOrder' style='display:flex;flex-direction:column;gap:7px;'>`+
        `<input name='name' required placeholder='${selectedLang==='id' ? 'Nama Anda' : 'Your Name'}' style='padding:6px;border-radius:6px;border:1px solid #ccc;'>`+
        `<input name='email' type='email' required placeholder='${selectedLang==='id' ? 'Email Anda' : 'Your Email'}' style='padding:6px;border-radius:6px;border:1px solid #ccc;'>`+
        `<input name='product' required placeholder='${selectedLang==='id' ? 'Nama Produk' : 'Product Name'}' style='padding:6px;border-radius:6px;border:1px solid #ccc;'>`+
        `<input name='qty' type='number' min='1' required placeholder='${selectedLang==='id' ? 'Jumlah' : 'Quantity'}' style='padding:6px;border-radius:6px;border:1px solid #ccc;'>`+
        `<textarea name='address' required placeholder='${selectedLang==='id' ? 'Alamat Pengiriman' : 'Shipping Address'}' style='padding:6px;border-radius:6px;border:1px solid #ccc;min-height:50px;'></textarea>`+
        `<button type='submit' style='margin-top:4px;padding:7px 0;font-size:15px;border-radius:7px;border:none;background:#fbbf24;color:#fff;cursor:pointer;'>${selectedLang==='id' ? 'Kirim Pesanan' : 'Send Order'}</button>`+
        `</form>`;
      msg.appendChild(bubble);
      body.appendChild(msg);
      body.scrollTop = body.scrollHeight;
      setTimeout(()=>{
        document.getElementById('formOrder').onsubmit = handleOrderSubmit;
      }, 100);
    }

    // Proses submit form order
    function handleOrderSubmit(e) {
      e.preventDefault();
      const form = e.target;
      const name = form.name.value.trim();
      const email = form.email.value.trim();
      const product = form.product.value.trim();
      const qty = form.qty.value.trim();
      const address = form.address.value.trim();
      if(!name || !email || !product || !qty || !address) return;
      appendMessage(`<b>${selectedLang==='id' ? 'Mengirim pesanan...' : 'Sending order...'}</b>`, 'bot');
      // Kirim ke endpoint PHP untuk order
      fetch('order_email.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({name, email, product, qty, address})
      })
      .then(resp => resp.json())
      .then(json => {
        if(json.success) {
          appendMessage(selectedLang==='id' ? 'Pesanan berhasil dikirim ke owner.' : 'Order sent to owner successfully.', 'bot');
        } else {
          appendMessage((selectedLang==='id' ? 'Gagal mengirim pesanan: ' : 'Failed to send order: ') + (json.error||'-'), 'bot');
        }
      })
      .catch(()=>{
        appendMessage(selectedLang==='id' ? 'Gagal mengirim pesanan.' : 'Failed to send order.', 'bot');
      });
      // Hapus form
      form.parentElement.parentElement.remove();
    }
    }

    // Kirim pesan ke AI tanpa input box
    function sendQuick(msg) {
      appendMessage(selectedLang==='en' ? 'Typing...' : 'Mengetik...', 'bot');
      fetch('chat.php', {
        method:'POST',
        headers:{'Content-Type':'application/json'},
        body: JSON.stringify({message:msg, lang: selectedLang})
      })
      .then(resp => resp.json())
      .then(json => {
        body.removeChild(body.lastChild);
        if(resp.ok){
          appendMessage(json.answer);
          if(json.products && json.products.length){
            let answerLower = (json.answer||'').toLowerCase();
            if(json.products.length === 1){
              const p = json.products[0];
              let detail = `<div class="small"><b>${p.name}</b></div>`;
              if(p.image_url){
                detail += `<div><img src="${p.image_url}" alt="${p.name}" style="max-width:90px;max-height:90px;border-radius:8px;margin:6px 0;"></div>`;
              }
              detail += `<div class="small">SKU: <b>${p.sku}</b></div>`;
              detail += `<div class="small">Harga: <b>Rp${Number(p.price).toLocaleString('id-ID')}</b></div>`;
              detail += `<div class="small">Stok: <b>${p.stock}</b></div>`;
              detail += `<div class="small">Kategori: <b>${p.category}</b></div>`;
              appendMessage(detail);
            }
          }
        }else{
          appendMessage('Error: '+(json.error|| (selectedLang==='en'?'Unknown':'Tidak diketahui')));
        }
      })
      .catch(()=>{
        body.removeChild(body.lastChild);
        appendMessage(selectedLang==='en' ? 'Failed to connect to server.' : 'Gagal terhubung ke server.');
      });
    }

    // Tampilkan form email di chat
    function showEmailForm() {
      const msg = document.createElement('div');
      msg.className = 'message msg-bot';
      const bubble = document.createElement('div');
      bubble.className = 'msg-text';
      bubble.innerHTML =
        `<b>${selectedLang==='id' ? 'Formulir Hubungi CS' : 'Contact CS Form'}</b><br><br>`+
        `<form id='formCS' style='display:flex;flex-direction:column;gap:7px;'>`+
        `<input name='from' type='email' required placeholder='${selectedLang==='id' ? 'Email Anda' : 'Your Email'}' style='padding:6px;border-radius:6px;border:1px solid #ccc;'>`+
        `<input name='subject' required placeholder='${selectedLang==='id' ? 'Judul Pesan' : 'Subject'}' style='padding:6px;border-radius:6px;border:1px solid #ccc;'>`+
        `<textarea name='body' required placeholder='${selectedLang==='id' ? 'Isi Pesan' : 'Message'}' style='padding:6px;border-radius:6px;border:1px solid #ccc;min-height:60px;'></textarea>`+
        `<button type='submit' style='margin-top:4px;padding:7px 0;font-size:15px;border-radius:7px;border:none;background:#22c55e;color:#fff;cursor:pointer;'>${selectedLang==='id' ? 'Kirim ke Owner' : 'Send to Owner'}</button>`+
        `</form>`;
      msg.appendChild(bubble);
      body.appendChild(msg);
      body.scrollTop = body.scrollHeight;
      setTimeout(()=>{
        document.getElementById('formCS').onsubmit = handleEmailSubmit;
      }, 100);
    }

    // Proses submit form email
    function handleEmailSubmit(e) {
      e.preventDefault();
      const form = e.target;
      const from = form.from.value.trim();
      const subject = form.subject.value.trim();
      const bodyMsg = form.body.value.trim();
      if(!from || !subject || !bodyMsg) return;
      appendMessage(`<b>${selectedLang==='id' ? 'Mengirim email ke owner...' : 'Sending email to owner...'}</b>`, 'bot');
      // Kirim ke endpoint PHP untuk email
      fetch('send_email.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({from, subject, body: bodyMsg})
      })
      .then(resp => resp.json())
      .then(json => {
        if(json.success) {
          appendMessage(selectedLang==='id' ? 'Email berhasil dikirim ke owner.' : 'Email sent to owner successfully.', 'bot');
        } else {
          appendMessage((selectedLang==='id' ? 'Gagal mengirim email: ' : 'Failed to send email: ') + (json.error||'-'), 'bot');
        }
      })
      .catch(()=>{
        appendMessage(selectedLang==='id' ? 'Gagal mengirim email.' : 'Failed to send email.', 'bot');
      });
      // Hapus form
      form.parentElement.parentElement.remove();
    }

    sendBtn.onclick = send;
    input.addEventListener('keydown', e=>{ if(e.key==='Enter') send(); });

    function markdownToHtml(md){
      let html = md.replace(/\*\*(.*?)\*\*/g, '<b>$1</b>')
                   .replace(/\*(.*?)\*/g, '<i>$1</i>')
                   .replace(/`(.*?)`/g, '<code>$1</code>');
      html = html.replace(/(?:\d+\.\s.*(?:\n|$))+?/g, match => {
        let items = match.trim().split(/\n/).map(line => line.replace(/^\d+\.\s*/, '')).map(line => `<li>${line}</li>`).join('');
        return `<ol>${items}</ol>`;
      });
      html = html.replace(/(?:-\s.*(?:\n|$))+?/g, match => {
        let items = match.trim().split(/\n/).map(line => line.replace(/^-+\s*/, '')).map(line => `<li>${line}</li>`).join('');
        return `<ul>${items}</ul>`;
      });
      return html.replace(/\n/g, '<br>');
    }

    function appendMessage(text, who='bot'){
      const msg = document.createElement('div');
      msg.className = 'message '+(who==='user'?'msg-user':'msg-bot');
      const bubble = document.createElement('div');
      bubble.className = 'msg-text';
      bubble.innerHTML = markdownToHtml(text);
      msg.appendChild(bubble);
      body.appendChild(msg);
      body.scrollTop = body.scrollHeight;
    }

    async function send(){
      const msg = input.value.trim();
      if(!msg) return;
      appendMessage(msg, 'user');
      input.value = '';
      appendMessage(selectedLang==='en' ? 'Typing...' : 'Mengetik...', 'bot');

      try{
        const resp = await fetch('chat.php', {
          method:'POST',
          headers:{'Content-Type':'application/json'},
          body: JSON.stringify({message:msg, lang: selectedLang})
        });
        const json = await resp.json();
        body.removeChild(body.lastChild);
        if(resp.ok){
          appendMessage(json.answer);
          if(json.products && json.products.length){
            let answerLower = (json.answer||'').toLowerCase();
            if(json.products.length === 1){
              const p = json.products[0];
              let detail = `<div class="small"><b>${p.name}</b></div>`;
              if(p.image_url){
                detail += `<div><img src="${p.image_url}" alt="${p.name}" style="max-width:90px;max-height:90px;border-radius:8px;margin:6px 0;"></div>`;
              }
              detail += `<div class="small">SKU: <b>${p.sku}</b></div>`;
              detail += `<div class="small">Harga: <b>Rp${Number(p.price).toLocaleString('id-ID')}</b></div>`;
              detail += `<div class="small">Stok: <b>${p.stock}</b></div>`;
              detail += `<div class="small">Kategori: <b>${p.category}</b></div>`;
              appendMessage(detail);
            }
          }
        }else{
          appendMessage('Error: '+(json.error|| (selectedLang==='en'?'Unknown':'Tidak diketahui')));
        }
      }catch(e){
        body.removeChild(body.lastChild);
        appendMessage(selectedLang==='en' ? 'Failed to connect to server.' : 'Gagal terhubung ke server.');
      }
    }
  </script>
</body>
</html>