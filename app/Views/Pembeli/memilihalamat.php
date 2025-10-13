<!DOCTYPE html>
<html lang="id">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pilih Alamat - FarmUnand</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
      :root{ --brand:#198754; --brand-dark:#145c32; --muted:#f8f9fa; }
      html,body{margin:0; padding:0; height:100%; background:var(--muted); font-family:'Segoe UI',sans-serif;}
      .main-content{ margin-left:250px; padding:30px; min-height:100vh; }
      .page-header{
        background:linear-gradient(135deg,#198754,#28a745);
        color:#fff; border-radius:12px; padding:16px 18px;
        display:flex; justify-content:space-between; align-items:center;
        box-shadow:0 6px 14px rgba(0,0,0,.08); margin-bottom:16px;
      }
      .page-header h5{margin:0; font-weight:700}
      .card-container{ background:#fff; border-radius:12px; box-shadow:0 6px 18px rgba(0,0,0,.06); padding:18px; }
      .address-card{
        border:1px solid #dee2e6; border-radius:12px; padding:16px; background:#fff;
        transition:all .2s ease; position:relative; cursor:pointer;
      }
      .address-card:hover{ transform:translateY(-2px); box-shadow:0 8px 18px rgba(0,0,0,.08); }
      .address-card.selected{ border-color:#28a745; box-shadow:0 0 0 3px rgba(40,167,69,.18); }
      .active-badge{ position:absolute; top:12px; right:12px; }
      .addr-name{ font-weight:700; color:#222; }
      .addr-line{ color:#666; margin:2px 0; }
      .btn-success{ background:#198754; border:none; font-weight:600 }
      .btn-success:hover{ background:#145c32 }
      .btn-outline-success{ border-radius:999px }
      #toastContainer{ position:fixed; top:50%; left:50%; transform:translate(-50%,-50%); z-index:1080; display:flex; flex-direction:column; align-items:center }
      .toast{ min-width:260px; max-width:360px; border-radius:12px; padding:14px 18px; font-weight:500; font-size:14px; text-align:center }
      #mapTambah, #mapUbah{ height:300px; margin-bottom:10px; border:1px solid #ddd; border-radius:8px; }
      @media (max-width:992px){ .main-content{ margin-left:0; padding:18px; } }
    </style>
  </head>

  <body>
    <!-- Sidebar -->
    <?= $this->include('layout/sidebar') ?>

    <!-- Main Content -->
    <div class="main-content">
      <div class="page-header">
        <h5>Alamat Pengiriman</h5>
        <div class="d-flex gap-2">
          <button type="button" class="btn btn-light btn-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#tambahAlamatModal">
            Tambah Alamat
          </button>
          <?php if(!empty($alamat)): ?>
            <button class="btn btn-light btn-sm fw-semibold" id="gunakanAlamatBtn">Gunakan Alamat Terpilih</button>
          <?php endif; ?>
        </div>
      </div>

      <div class="card-container">
        <?php if(!empty($alamat)): ?>
          <div class="row g-3">
            <?php foreach($alamat as $a): ?>
              <div class="col-12">
                <div class="address-card d-flex justify-content-between align-items-start flex-wrap <?= !empty($a['aktif']) ? 'selected' : '' ?>"
                    data-id="<?= (int)$a['id_alamat'] ?>">

                  <div class="d-flex align-items-start" style="gap:10px;" onclick="this.closest('.address-card').querySelector('input[type=radio]').click()">
                    <input type="radio" name="alamat" class="form-check-input mt-1"
                          value="<?= (int)$a['id_alamat'] ?>" <?= !empty($a['aktif']) ? 'checked' : '' ?>>
                    <div>
                      <div class="addr-name"><?= esc($a['nama_penerima']) ?> <span class="text-muted">(<?= esc($a['no_telepon']) ?>)</span></div>
                      <div class="addr-line"><?= esc($a['jalan']) ?>, <?= esc($a['kota']) ?>, <?= esc($a['provinsi']) ?></div>
                      <div class="addr-line">Kode Pos: <?= esc($a['kode_pos']) ?></div>
                    </div>
                  </div>

                  <div class="d-flex flex-column align-items-end">
                    <?php if(!empty($a['aktif'])): ?>
                      <span class="badge bg-success active-badge">Aktif</span>
                    <?php endif; ?>

                    <button class="btn btn-outline-success btn-sm mt-2 ubahAlamatBtn"
                            data-id="<?= (int)$a['id_alamat'] ?>"
                            data-nama="<?= esc($a['nama_penerima'], 'attr') ?>"
                            data-no="<?= esc($a['no_telepon'], 'attr') ?>"
                            data-jalan="<?= esc($a['jalan'], 'attr') ?>"
                            data-kota="<?= esc($a['kota'], 'attr') ?>"
                            data-provinsi="<?= esc($a['provinsi'], 'attr') ?>"
                            data-kodepos="<?= esc($a['kode_pos'], 'attr') ?>"
                            data-lat="<?= esc($a['latitude'] ?? '', 'attr') ?>"
                            data-lng="<?= esc($a['longitude'] ?? '', 'attr') ?>"
                            data-bs-toggle="modal" data-bs-target="#ubahAlamatModal">
                      Ubah
                    </button>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <div class="alert alert-warning mb-0">Belum ada alamat. Silakan tambahkan alamat baru.</div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Modal Tambah Alamat -->
    <div class="modal fade" id="tambahAlamatModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <form action="<?= base_url('/memilihalamat/tambah') ?>" method="post">
            <div class="modal-header">
              <h5 class="modal-title">Tambah Alamat</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
              <div id="mapTambah"></div>
              <input type="hidden" name="latitude" id="latitude_tambah">
              <input type="hidden" name="longitude" id="longitude_tambah">

              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label">Nama Penerima</label>
                  <input type="text" class="form-control" name="nama_penerima" id="nama_penerima_tambah" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label">No. Telepon</label>
                  <input type="text" class="form-control" name="no_telepon" required>
                </div>
                <div class="col-12">
                  <label class="form-label">Jalan</label>
                  <input type="text" class="form-control" name="jalan" id="jalan_tambah" required>
                </div>
                <div class="col-md-4">
                  <label class="form-label">Kota</label>
                  <input type="text" class="form-control" name="kota" id="kota_tambah" required>
                </div>
                <div class="col-md-4">
                  <label class="form-label">Provinsi</label>
                  <input type="text" class="form-control" name="provinsi" id="provinsi_tambah" required>
                </div>
                <div class="col-md-4">
                  <label class="form-label">Kode Pos</label>
                  <input type="text" class="form-control" name="kode_pos" id="kodepos_tambah" required>
                </div>
              </div>
            </div>

            <div class="modal-footer">
              <button class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
              <button class="btn btn-success">Simpan</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Modal Ubah Alamat -->
    <div class="modal fade" id="ubahAlamatModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <form id="formUbahAlamat">
            <div class="modal-header">
              <h5 class="modal-title">Ubah Alamat</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
              <div id="mapUbah"></div>
              <input type="hidden" id="ubah_id_alamat">
              <input type="hidden" id="latitude_ubah">
              <input type="hidden" id="longitude_ubah">

              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label">Nama Penerima</label>
                  <input type="text" class="form-control" id="ubah_nama_penerima" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label">No. Telepon</label>
                  <input type="text" class="form-control" id="ubah_no_telepon" required>
                </div>
                <div class="col-12">
                  <label class="form-label">Jalan</label>
                  <input type="text" class="form-control" id="ubah_jalan" required>
                </div>
                <div class="col-md-4">
                  <label class="form-label">Kota</label>
                  <input type="text" class="form-control" id="ubah_kota" required>
                </div>
                <div class="col-md-4">
                  <label class="form-label">Provinsi</label>
                  <input type="text" class="form-control" id="ubah_provinsi" required>
                </div>
                <div class="col-md-4">
                  <label class="form-label">Kode Pos</label>
                  <input type="text" class="form-control" id="ubah_kode_pos" required>
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
              <button class="btn btn-success" type="submit">Simpan Perubahan</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- FIX: container toast yang benar -->
    <div id="toastContainer"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
      // Toast helper
      function showToast(message, type='success'){
        const id='t'+Date.now();
        const html=`
          <div id="${id}" class="toast align-items-center text-bg-${type} border-0 mb-2" role="alert">
            <div class="d-flex">
              <div class="toast-body">${message}</div>
              <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
          </div>`;
        document.getElementById('toastContainer').insertAdjacentHTML('beforeend', html);
        new bootstrap.Toast(document.getElementById(id), {delay:2500}).show();
      }

      // Tombol “Gunakan Alamat Terpilih”
      document.getElementById('gunakanAlamatBtn')?.addEventListener('click', selectActiveAddress);

      // Klik kartu untuk memilih radio
      document.querySelectorAll('.address-card').forEach(card=>{
        card.addEventListener('dblclick', ()=>{
          card.querySelector('input[type=radio]').checked = true;
          reflectSelectionUI();
          selectActiveAddress();
        });
        card.addEventListener('click', (e)=>{
          if(e.detail === 1){
            card.querySelector('input[type=radio]').checked = true;
            reflectSelectionUI();
          }
        });
      });

      function reflectSelectionUI(){
        document.querySelectorAll('.address-card').forEach(c=>c.classList.remove('selected'));
        document.querySelectorAll('.active-badge').forEach(b=>b.remove());
        const checked = document.querySelector('input[name="alamat"]:checked');
        if(checked){
          const card = checked.closest('.address-card');
          card.classList.add('selected');
          card.insertAdjacentHTML('beforeend','<span class="badge bg-success active-badge">Aktif</span>');
        }
      }

      // POST kosong ke /memilihalamat/pilih/{id}
      function selectActiveAddress(){
        const radio = document.querySelector('input[name="alamat"]:checked');
        if(!radio){ showToast('Silakan pilih alamat terlebih dahulu.','danger'); return; }

        const alamatId = radio.value;
        fetch('<?= base_url("memilihalamat/pilih") ?>/'+alamatId, {
          method: 'POST',
          credentials: 'same-origin'
        })
        .then(r=>r.json())
        .then(res=>{
          if(res.success){
            showToast('Alamat aktif diperbarui.');
            reflectSelectionUI();
            setTimeout(()=> { window.location.href = '<?= base_url("melakukanpemesanan") ?>?from=alamat'; }, 900);
          }else{
            showToast(res.message || 'Gagal memperbarui alamat aktif.','danger');
          }
        })
        .catch(()=>showToast('Kesalahan koneksi.','danger'));
      }

      // === Ubah alamat (kirim JSON sesuai controller) ===
      let originalData = {};
      document.querySelectorAll('.ubahAlamatBtn').forEach(btn=>{
        btn.addEventListener('click', function(){
          const id = this.dataset.id;
          document.getElementById('ubah_id_alamat').value = id;
          document.getElementById('ubah_nama_penerima').value = this.dataset.nama || '';
          document.getElementById('ubah_no_telepon').value  = this.dataset.no || '';
          document.getElementById('ubah_jalan').value       = this.dataset.jalan || '';
          document.getElementById('ubah_kota').value        = this.dataset.kota || '';
          document.getElementById('ubah_provinsi').value    = this.dataset.provinsi || '';
          document.getElementById('ubah_kode_pos').value    = this.dataset.kodepos || '';

          originalData[id] = {
            nama_penerima: this.dataset.nama || '',
            no_telepon: this.dataset.no || '',
            jalan: this.dataset.jalan || '',
            kota: this.dataset.kota || '',
            provinsi: this.dataset.provinsi || '',
            kode_pos: this.dataset.kodepos || ''
          };

          const lat = parseFloat(this.dataset.lat) || -0.9492;
          const lng = parseFloat(this.dataset.lng) || 100.3544;
          setTimeout(()=> initLeafletMap('mapUbah','latitude_ubah','longitude_ubah','ubah_jalan','ubah_kota','ubah_provinsi','ubah_kode_pos',lat,lng), 150);
        });
      });

      document.getElementById('formUbahAlamat')?.addEventListener('submit', function(e){
        e.preventDefault();
        const id = document.getElementById('ubah_id_alamat').value;
        const changed = {};
        const fields = ['nama_penerima','no_telepon','jalan','kota','provinsi','kode_pos'];

        fields.forEach(f=>{
          const cur = document.getElementById('ubah_'+f).value.trim();
          if(cur !== (originalData[id]?.[f] ?? '')) changed[f] = cur;
        });

        const lat = document.getElementById('latitude_ubah').value;
        const lng = document.getElementById('longitude_ubah').value;
        if(lat) changed.latitude = lat;
        if(lng) changed.longitude = lng;

        if(Object.keys(changed).length === 0){
          showToast('Tidak ada perubahan.','info'); return;
        }

        fetch('<?= base_url("memilihalamat/ubah") ?>/'+id, {
          method:'POST',
          headers: { 'Content-Type':'application/json' },
          credentials: 'same-origin',
          body: JSON.stringify(changed)
        })
        .then(r=>r.json())
        .then(res=>{
          if(res.success){
            showToast(res.message || 'Alamat diperbarui.');
            setTimeout(()=> location.reload(), 900);
          }else{
            showToast(res.message || 'Gagal memperbarui alamat.','danger');
          }
        })
        .catch(()=> showToast('Kesalahan koneksi.','danger'));
      });

      // Leaflet + reverse geocode
      function initLeafletMap(mapId, latId, lngId, jalanId, kotaId, provId, kodeposId, dLat=-0.9492, dLng=100.3544){
        const map = L.map(mapId).setView([dLat,dLng], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{ attribution:'&copy; OpenStreetMap contributors' }).addTo(map);
        const marker = L.marker([dLat,dLng], { draggable:true }).addTo(map);

        function updateInputs(lat,lng){
          document.getElementById(latId).value = lat;
          document.getElementById(lngId).value = lng;
          fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}`)
            .then(res=>res.json())
            .then(d=>{
              const addr = d.address || {};
              document.getElementById(jalanId).value    = addr.road || document.getElementById(jalanId).value;
              document.getElementById(kotaId).value     = addr.city || addr.town || addr.village || document.getElementById(kotaId).value;
              document.getElementById(provId).value     = addr.state || document.getElementById(provId).value;
              document.getElementById(kodeposId).value  = addr.postcode || document.getElementById(kodeposId).value;
            }).catch(()=>{});
        }

        marker.on('dragend', e => updateInputs(e.target.getLatLng().lat, e.target.getLatLng().lng));
        map.on('click', e => { marker.setLatLng(e.latlng); updateInputs(e.latlng.lat, e.latlng.lng); });

        updateInputs(dLat, dLng);
        setTimeout(()=> map.invalidateSize(), 200);
        return {map, marker};
      }

      document.getElementById('tambahAlamatModal')?.addEventListener('shown.bs.modal', ()=>{
        setTimeout(()=> initLeafletMap('mapTambah','latitude_tambah','longitude_tambah','jalan_tambah','kota_tambah','provinsi_tambah','kodepos_tambah'), 120);
      });
    </script>
  </body>
</html>
