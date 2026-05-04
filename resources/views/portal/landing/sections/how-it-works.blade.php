<section id="hiw" aria-labelledby="hiw-heading">
  <div class="wrap">
    <div class="hiw-head">
      <div>
        <div class="eyebrow reveal" data-i18n="s_hiw">Cara Kerja</div>
        <h2 id="hiw-heading" class="hiw-h reveal d1">
          <span data-i18n="hiw_choice_h1">Apa yang ingin</span><br/>
          <em data-i18n="hiw_choice_h2">Anda lakukan?</em>
        </h2>
      </div>

      <p class="hiw-sub reveal d2" data-i18n="hiw_choice_sub">
        Portal ini membantu Anda melakukan dua hal utama: melaporkan bug baru atau melacak tiket yang sudah ada.
      </p>
    </div>

    <div class="hiw-choice reveal d3">
      <!-- Path: Report -->
      <article class="hiw-path hiw-path-report">
        <div class="hiw-path-top">
          <div class="hiw-path-badge" data-i18n="hiw_path_1_badge">Jalur 01</div>
          <h3 class="hiw-path-title" data-i18n="hiw_path_1_title">Laporkan Bug</h3>
          <p class="hiw-path-desc" data-i18n="hiw_path_1_desc">
            Paling cocok jika Anda menemukan kendala baru dan perlu tim kami menindaklanjuti.
          </p>
        </div>

        <ol class="hiw-steps" aria-label="Langkah pelaporan bug">
          <li class="hiw-step">
            <div class="hiw-step-no" aria-hidden="true">1</div>
            <div class="hiw-step-copy">
              <div class="hiw-step-t" data-i18n="hiw_r_1_t">Buka Form Laporan</div>
              <div class="hiw-step-d" data-i18n="hiw_r_1_d">Klik tombol "Laporkan Bug" untuk masuk ke formulir.</div>
            </div>
          </li>

          <li class="hiw-step">
            <div class="hiw-step-no" aria-hidden="true">2</div>
            <div class="hiw-step-copy">
              <div class="hiw-step-t" data-i18n="hiw_r_2_t">Isi Detail & Lampiran</div>
              <div class="hiw-step-d" data-i18n="hiw_r_2_d">Tambahkan langkah reproduksi, severity, dan screenshot jika ada.</div>
            </div>
          </li>

          <li class="hiw-step">
            <div class="hiw-step-no" aria-hidden="true">3</div>
            <div class="hiw-step-copy">
              <div class="hiw-step-t" data-i18n="hiw_r_3_t">Kirim & Simpan ID Tiket</div>
              <div class="hiw-step-d" data-i18n="hiw_r_3_d">Setelah submit, tiket tercatat dan Anda menerima ID pelacakan.</div>
            </div>
          </li>
        </ol>

        <div class="hiw-path-note" data-i18n="hiw_r_note">
          <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
          </svg>
          <span data-i18n="hiw_r_email_note">ID tiket juga dikirim otomatis ke email Anda.</span>
        </div>

        <div class="hiw-path-cta">
          <a href="{{ route('client.report') }}" class="btn btn-solid" data-i18n="cta_report">Laporkan Bug</a>
        </div>
      </article>

      <!-- Divider -->
      <div class="hiw-or" aria-hidden="true">
        <span class="hiw-or-pill" data-i18n="hiw_or">atau</span>
      </div>

      <!-- Path: Track -->
      <article class="hiw-path hiw-path-track">
        <div class="hiw-path-top">
          <div class="hiw-path-badge" data-i18n="hiw_path_2_badge">Jalur 02</div>
          <h3 class="hiw-path-title" data-i18n="hiw_path_2_title">Lacak Tiket</h3>
          <p class="hiw-path-desc" data-i18n="hiw_path_2_desc">
            Paling cocok jika Anda sudah punya ID tiket dan ingin melihat update status terbaru.
          </p>
        </div>

        <ol class="hiw-steps" aria-label="Langkah pelacakan tiket">
          <li class="hiw-step">
            <div class="hiw-step-no" aria-hidden="true">1</div>
            <div class="hiw-step-copy">
              <div class="hiw-step-t" data-i18n="hiw_t_1_t">Buka Halaman Pelacakan</div>
              <div class="hiw-step-d" data-i18n="hiw_t_1_d">Klik tombol "Lacak Tiket" untuk membuka halaman tracking.</div>
            </div>
          </li>

          <li class="hiw-step">
            <div class="hiw-step-no" aria-hidden="true">2</div>
            <div class="hiw-step-copy">
              <div class="hiw-step-t" data-i18n="hiw_t_2_t">Masukkan ID Tiket</div>
              <div class="hiw-step-d" data-i18n="hiw_t_2_d">Gunakan ID yang Anda terima setelah submit laporan.</div>
            </div>
          </li>

          <li class="hiw-step">
            <div class="hiw-step-no" aria-hidden="true">3</div>
            <div class="hiw-step-copy">
              <div class="hiw-step-t" data-i18n="hiw_t_3_t">Lihat Status & Update</div>
              <div class="hiw-step-d" data-i18n="hiw_t_3_d">Pantau progres, catatan, dan status hingga tiket selesai.</div>
            </div>
          </li>
        </ol>

        <div class="hiw-path-note" data-i18n="hiw_t_note">
          <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
          </svg>
          <span data-i18n="hiw_t_email_note">Setiap perubahan status dikirim langsung ke email Anda.</span>
        </div>

        <div class="hiw-path-cta">
          <a href="{{ route('client.tracking') }}" class="btn btn-ghost" data-i18n="nav_track">Lacak Tiket</a>
        </div>
      </article>
    </div>

    <p class="hiw-note reveal d4" data-i18n="hiw_choice_note">
      Tip: Setelah mengirim laporan, simpan ID tiket Anda untuk memudahkan pelacakan.
    </p>
  </div>
</section>