// Separated chat JS (extracted from index.php)
(function(){
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
    input.disabled = true; sendBtn.disabled = true;
    const msg = document.createElement('div'); msg.className='message msg-bot';
    const bubble = document.createElement('div'); bubble.className='msg-text';
    bubble.innerHTML = `Silakan pilih bahasa anda<br><span style='font-size:13px;color:#666;'>Please select your language</span><br><br>
      <button id='btnLangId' class='btn-lang'>Bahasa Indonesia</button>
      <button id='btnLangEn' class='btn-lang'>English</button><br><br>
      <span style='font-size:13px;color:#888;'>Gunakan tombol di bawah ini untuk memulai percakapan / Use the buttons below to start</span>`;
    msg.appendChild(bubble); body.appendChild(msg); body.scrollTop = body.scrollHeight;
    setTimeout(()=>{
      document.getElementById('btnLangId').onclick = ()=> chooseLang('id');
      document.getElementById('btnLangEn').onclick = ()=> chooseLang('en');
    },100);
  }

  function chooseLang(lang){
    selectedLang = lang; body.innerHTML=''; input.disabled=false; sendBtn.disabled=false;
    let greet = lang==='id' ? 'Halo! Saya asisten layanan pelanggan toko ini. Ada yang bisa saya bantu?' : 'Hello! I am the customer service assistant for this shop. How can I help you today?';
    const msg = document.createElement('div'); msg.className='message msg-bot';
    const bubble = document.createElement('div'); bubble.className='msg-text';
    bubble.innerHTML = `${greet}<br><br><button id='btnProduk' class='quick'>${lang==='id'?'Tanya Produk':'Ask About Products'}</button> <button id='btnOrder' class='quick'>${lang==='id'?'Buat Pesanan':'Place Order'}</button> <button id='btnCS' class='quick'>${lang==='id'?'Hubungi CS':'Contact CS'}</button>`;
    msg.appendChild(bubble); body.appendChild(msg); body.scrollTop = body.scrollHeight;
    setTimeout(()=>{
      document.getElementById('btnProduk').onclick = ()=> handleQuickAction('produk');
      document.getElementById('btnOrder').onclick = ()=> handleQuickAction('order');
      document.getElementById('btnCS').onclick = ()=> handleQuickAction('cs');
    },100);
  }

  function handleQuickAction(action){
    let userMsg='';
    if(action==='produk'){
      input.value = selectedLang==='id' ? 'Saya ingin bertanya mengenai produk dari website ini.' : 'I want to ask about the products from this website.'; input.focus(); return;
    } else if(action==='order'){
      userMsg = selectedLang==='id' ? 'Saya ingin buat pesanan' : 'I want to place an order'; appendMessage(userMsg,'user'); showOrderForm();
    } else if(action==='cs'){
      userMsg = selectedLang==='id' ? 'Saya ingin menghubungi customer service' : 'I want to contact customer service'; appendMessage(userMsg,'user'); showEmailForm();
    }
  }

  function showOrderForm(){
    const msg=document.createElement('div'); msg.className='message msg-bot'; const bubble=document.createElement('div'); bubble.className='msg-text';
    bubble.innerHTML = `<b>${selectedLang==='id'?'Formulir Pesanan':'Order Form'}</b><br><br><form id='formOrder' style='display:flex;flex-direction:column;gap:7px;'><input name='name' required placeholder='${selectedLang==='id'?'Nama Anda':'Your Name'}'><input name='email' type='email' required placeholder='${selectedLang==='id'?'Email Anda':'Your Email'}'><input name='product' required placeholder='${selectedLang==='id'?'Nama Produk':'Product Name'}'><input name='qty' type='number' min='1' required placeholder='${selectedLang==='id'?'Jumlah':'Quantity'}'><textarea name='address' required placeholder='${selectedLang==='id'?'Alamat Pengiriman':'Shipping Address'}'></textarea><button type='submit'>${selectedLang==='id'?'Kirim Pesanan':'Send Order'}</button></form>`;
    msg.appendChild(bubble); body.appendChild(msg); body.scrollTop = body.scrollHeight; setTimeout(()=>{document.getElementById('formOrder').onsubmit = handleOrderSubmit;},100);
  }

  function handleOrderSubmit(e){
    e.preventDefault(); const form=e.target; const name=form.name.value.trim(); const email=form.email.value.trim(); const product=form.product.value.trim(); const qty=form.qty.value.trim(); const address=form.address.value.trim(); if(!name||!email||!product||!qty||!address) return; appendMessage(`<b>${selectedLang==='id'?'Mengirim pesanan...':'Sending order...'}</b>`,'bot'); fetch('order_email.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({name,email,product,qty,address})}).then(r=>r.json()).then(json=>{ if(json.success) appendMessage(selectedLang==='id'?'Pesanan berhasil dikirim ke owner.':'Order sent to owner successfully.','bot'); else appendMessage((selectedLang==='id'?'Gagal mengirim pesanan: ':'Failed to send order: ')+(json.error||'-'),'bot');}).catch(()=>appendMessage(selectedLang==='id'?'Gagal mengirim pesanan.':'Failed to send order.','bot')); e.target.parentElement.parentElement.remove();
  }

  function showEmailForm(){
    const msg=document.createElement('div'); msg.className='message msg-bot'; const bubble=document.createElement('div'); bubble.className='msg-text';
    bubble.innerHTML = `<b>${selectedLang==='id'?'Formulir Hubungi CS':'Contact CS Form'}</b><br><br><form id='formCS' style='display:flex;flex-direction:column;gap:7px;'><input name='from' type='email' required placeholder='${selectedLang==='id'?'Email Anda':'Your Email'}'><input name='subject' required placeholder='${selectedLang==='id'?'Judul Pesan':'Subject'}'><textarea name='body' required placeholder='${selectedLang==='id'?'Isi Pesan':'Message'}'></textarea><button type='submit'>${selectedLang==='id'?'Kirim ke Owner':'Send to Owner'}</button></form>`;
    msg.appendChild(bubble); body.appendChild(msg); body.scrollTop = body.scrollHeight; setTimeout(()=>{document.getElementById('formCS').onsubmit = handleEmailSubmit;},100);
  }

  function handleEmailSubmit(e){
    e.preventDefault(); const form=e.target; const from=form.from.value.trim(); const subject=form.subject.value.trim(); const bodyMsg=form.body.value.trim(); if(!from||!subject||!bodyMsg) return; appendMessage(`<b>${selectedLang==='id'?'Mengirim email ke owner...':'Sending email to owner...'}</b>`,'bot'); fetch('send_email.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({from,subject,body:bodyMsg})}).then(r=>r.json()).then(json=>{ if(json.success) appendMessage(selectedLang==='id'?'Email berhasil dikirim ke owner.':'Email sent to owner successfully.','bot'); else appendMessage((selectedLang==='id'?'Gagal mengirim email: ':'Failed to send email: ')+(json.error||'-'),'bot'); }).catch(()=>appendMessage(selectedLang==='id'?'Gagal mengirim email.':'Failed to send email.','bot')); e.target.parentElement.parentElement.remove();
  }

  function sendQuick(msg){ appendMessage(selectedLang==='en' ? 'Typing...' : 'Mengetik...', 'bot'); fetch('chat.php',{method:'POST',headers:{'Content-Type':'application/json'},body: JSON.stringify({message:msg, lang: selectedLang})}).then(resp=>resp.json()).then(json=>{ body.removeChild(body.lastChild); if(json.answer){ appendMessage(json.answer); if(json.products && json.products.length===1){ const p = json.products[0]; let detail = `<div class="small"><b>${p.name}</b></div>`; if(p.image_url){ detail += `<div><img src="${p.image_url}" alt="${p.name}" style="max-width:90px;max-height:90px;border-radius:8px;margin:6px 0;"></div>`; } detail += `<div class="small">SKU: <b>${p.sku}</b></div>`; detail += `<div class="small">Harga: <b>Rp${Number(p.price).toLocaleString('id-ID')}</b></div>`; detail += `<div class="small">Stok: <b>${p.stock}</b></div>`; detail += `<div class="small">Kategori: <b>${p.category}</b></div>`; appendMessage(detail); } } else { appendMessage('Error: '+(json.error|| (selectedLang==='en'?'Unknown':'Tidak diketahui'))); } }).catch(()=>{ body.removeChild(body.lastChild); appendMessage(selectedLang==='en' ? 'Failed to connect to server.' : 'Gagal terhubung ke server.'); }); }

  function markdownToHtml(md){
    let html = md.replace(/\*\*(.*?)\*\*/g, '<b>$1</b>').replace(/\*(.*?)\*/g, '<i>$1</i>').replace(/`(.*?)`/g, '<code>$1</code>');
    html = html.replace(/(?:\d+\.\s.*(?:\n|$))+?/g, match => { let items = match.trim().split(/\n/).map(line => line.replace(/^\d+\.\s*/, '')).map(line => `<li>${line}</li>`).join(''); return `<ol>${items}</ol>`; });
    html = html.replace(/(?:-\s.*(?:\n|$))+?/g, match => { let items = match.trim().split(/\n/).map(line => line.replace(/^-+\s*/, '')).map(line => `<li>${line}</li>`).join(''); return `<ul>${items}</ul>`; });
    return html.replace(/\n/g, '<br>');
  }

  function appendMessage(text, who='bot'){ const msg=document.createElement('div'); msg.className='message '+(who==='user'?'msg-user':'msg-bot'); const bubble=document.createElement('div'); bubble.className='msg-text'; bubble.innerHTML = markdownToHtml(text); msg.appendChild(bubble); body.appendChild(msg); body.scrollTop = body.scrollHeight; }

  async function send(){ const msg=input.value.trim(); if(!msg) return; appendMessage(msg,'user'); input.value=''; appendMessage(selectedLang==='en' ? 'Typing...' : 'Mengetik...', 'bot'); try{ const resp = await fetch('chat.php',{method:'POST',headers:{'Content-Type':'application/json'},body: JSON.stringify({message:msg, lang: selectedLang})}); const json = await resp.json(); body.removeChild(body.lastChild); if(resp.ok){ appendMessage(json.answer); if(json.products && json.products.length === 1){ const p = json.products[0]; let detail = `<div class="small"><b>${p.name}</b></div>`; if(p.image_url){ detail += `<div><img src="${p.image_url}" alt="${p.name}" style="max-width:90px;max-height:90px;border-radius:8px;margin:6px 0;"></div>`; } detail += `<div class="small">SKU: <b>${p.sku}</b></div>`; detail += `<div class="small">Harga: <b>Rp${Number(p.price).toLocaleString('id-ID')}</b></div>`; detail += `<div class="small">Stok: <b>${p.stock}</b></div>`; detail += `<div class="small">Kategori: <b>${p.category}</b></div>`; appendMessage(detail); } }else{ appendMessage('Error: '+(json.error|| (selectedLang==='en'?'Unknown':'Tidak diketahui'))); } }catch(e){ body.removeChild(body.lastChild); appendMessage(selectedLang==='en' ? 'Failed to connect to server.' : 'Gagal terhubung ke server.'); } }

  sendBtn.onclick = send; input.addEventListener('keydown', e=>{ if(e.key==='Enter') send(); });

})();
