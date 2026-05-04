<section id="faq" aria-labelledby="faq-heading">
  <div class="wrap">
    <div class="faq-wrap">
      <div class="faq-left">
        <div class="eyebrow reveal">FAQ</div>

        <h2 id="faq-heading" class="faq-h reveal d1">
          <span data-i18n="faq_h1">Pertanyaan</span><br/>
          <em data-i18n="faq_h2">yang sering muncul.</em>
        </h2>

        <p class="faq-p reveal d2" data-i18n="faq_sub">
          Semua yang perlu Anda tahu sebelum mulai — dan beberapa hal yang paling sering ditanyakan saat pelaporan tiket.
        </p>

        <div class="reveal d3">
          <a href="{{ route('client.report') }}" class="btn btn-solid">
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            <span data-i18n="cta_report">Laporkan Bug</span>
          </a>
        </div>
      </div>

      <div class="faq-list" role="list">
        <div class="faq-item" role="listitem">
          <button class="faq-btn open" aria-expanded="true" aria-controls="fa-0">
            <span class="faq-q" data-i18n="q1">Siapa yang bisa mengajukan laporan?</span>
            <span class="faq-ico" aria-hidden="true">
              <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.2">
                <path stroke-linecap="round" stroke-linejoin="round" d="m5 7.5 5 5 5-5"/>
              </svg>
            </span>
          </button>
          <div class="faq-ans open" id="fa-0">
            <p data-i18n="a1">Semua klien PRANALA dengan proyek aktif. Kalau tidak yakin apakah proyek Anda termasuk, tanya saja ke project manager Anda — biasanya satu pesan cukup.</p>
          </div>
        </div>

        <div class="faq-item" role="listitem">
          <button class="faq-btn" aria-expanded="false" aria-controls="fa-1">
            <span class="faq-q" data-i18n="q2">Seberapa cepat seseorang akan merespons?</span>
            <span class="faq-ico" aria-hidden="true">
              <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.2">
                <path stroke-linecap="round" stroke-linejoin="round" d="m5 7.5 5 5 5-5"/>
              </svg>
            </span>
          </button>
          <div class="faq-ans" id="fa-1">
            <p data-i18n="a2">Tim kami berusaha merespons setiap laporan secepat mungkin selama jam kerja. Bug dengan keparahan kritis diprioritaskan dan dieskalasi lebih awal. Anda akan menerima konfirmasi email segera setelah tiket diterima.</p>
          </div>
        </div>

        <div class="faq-item" role="listitem">
          <button class="faq-btn" aria-expanded="false" aria-controls="fa-2">
            <span class="faq-q" data-i18n="q3">Apa yang harus saya sertakan dalam laporan?</span>
            <span class="faq-ico" aria-hidden="true">
              <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.2">
                <path stroke-linecap="round" stroke-linejoin="round" d="m5 7.5 5 5 5-5"/>
              </svg>
            </span>
          </button>
          <div class="faq-ans" id="fa-2">
            <p data-i18n="a3">Formulir terstruktur kami memandu Anda melalui semua hal: deskripsi, langkah reproduksi, lingkungan (browser, OS, perangkat), tingkat keparahan, dan screenshot opsional. Formulir memastikan Anda tidak secara tidak sengaja melewatkan informasi penting.</p>
          </div>
        </div>

        <div class="faq-item" role="listitem">
          <button class="faq-btn" aria-expanded="false" aria-controls="fa-3">
            <span class="faq-q" data-i18n="q4">Bisakah saya melacak tiket setelah mengajukan?</span>
            <span class="faq-ico" aria-hidden="true">
              <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.2">
                <path stroke-linecap="round" stroke-linejoin="round" d="m5 7.5 5 5 5-5"/>
              </svg>
            </span>
          </button>
          <div class="faq-ans" id="fa-3">
            <p data-i18n="a4">Ya — Anda akan menerima ID tiket unik saat pengajuan. Gunakan halaman pelacakan kami untuk melihat pembaruan status real-time, siapa yang ditugaskan, dan catatan engineer. Notifikasi email otomatis dikirim pada setiap perubahan status.</p>
          </div>
        </div>

        <div class="faq-item" role="listitem">
          <button class="faq-btn" aria-expanded="false" aria-controls="fa-4">
            <span class="faq-q" data-i18n="q5">Apakah data saya dijaga kerahasiaannya?</span>
            <span class="faq-ico" aria-hidden="true">
              <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.2">
                <path stroke-linecap="round" stroke-linejoin="round" d="m5 7.5 5 5 5-5"/>
              </svg>
            </span>
          </button>
          <div class="faq-ans" id="fa-4">
            <p data-i18n="a5">Semua laporan bersifat privat untuk organisasi Anda dan tim engineering PRANALA. Tidak ada data yang dibagikan ke pihak ketiga. Akses dibatasi berdasarkan peran — hanya engineer yang ditugaskan untuk proyek Anda yang dapat melihat tiket Anda.</p>
          </div>
        </div>

        <div class="faq-item" role="listitem">
          <button class="faq-btn" aria-expanded="false" aria-controls="fa-5">
            <span class="faq-q" data-i18n="q6">Apa yang terjadi setelah bug ditandai selesai?</span>
            <span class="faq-ico" aria-hidden="true">
              <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.2">
                <path stroke-linecap="round" stroke-linejoin="round" d="m5 7.5 5 5 5-5"/>
              </svg>
            </span>
          </button>
          <div class="faq-ans" id="fa-5">
            <p data-i18n="a6">Anda akan menerima email penutupan dengan ringkasan resolusi lengkap — akar penyebab, perbaikan yang diterapkan, dan bagaimana pengujiannya. Tiket tetap dapat diakses di riwayat Anda. Jika masalah muncul kembali, Anda dapat membukanya kembali langsung dari halaman pelacakan.</p>
          </div>
        </div>

        <div class="faq-item" role="listitem">
          <button class="faq-btn" aria-expanded="false" aria-controls="fa-6">
            <span class="faq-q" data-i18n="q7">Kenapa saya tidak menerima email ID tiket atau update progres?</span>
            <span class="faq-ico" aria-hidden="true">
              <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.2">
                <path stroke-linecap="round" stroke-linejoin="round" d="m5 7.5 5 5 5-5"/>
              </svg>
            </span>
          </button>
          <div class="faq-ans" id="fa-6">
            <p data-i18n="a7">Pastikan alamat email yang Anda masukkan saat mengirim laporan sudah benar. Jika sudah benar tetapi email belum masuk, silakan cek folder spam, junk, atau promotions. Bila masih tidak ditemukan, hubungi tim kami agar kami dapat membantu memverifikasi tiket Anda.</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>