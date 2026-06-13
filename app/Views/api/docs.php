<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($pageTitle) ?></title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --blue:    #2563eb;
            --green:   #16a34a;
            --amber:   #d97706;
            --red:     #dc2626;
            --gray-50: #f9fafb;
            --gray-100:#f3f4f6;
            --gray-200:#e5e7eb;
            --gray-600:#4b5563;
            --gray-700:#374151;
            --gray-900:#111827;
            --sidebar: 260px;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            font-size: 14px;
            color: var(--gray-700);
            background: #fff;
            display: flex;
            min-height: 100vh;
        }

        /* ── Sidebar ── */
        #sidebar {
            width: var(--sidebar);
            min-height: 100vh;
            background: var(--gray-900);
            color: #e5e7eb;
            position: fixed;
            top: 0; left: 0;
            overflow-y: auto;
            padding: 0 0 2rem;
            flex-shrink: 0;
        }

        .sb-brand {
            padding: 1.25rem 1.25rem 1rem;
            border-bottom: 1px solid rgba(255,255,255,.08);
            margin-bottom: .75rem;
        }
        .sb-brand .org { font-size: .7rem; color: #9ca3af; text-transform: uppercase; letter-spacing: .08em; }
        .sb-brand .title { font-size: 1rem; font-weight: 700; color: #fff; margin-top: .15rem; }
        .sb-brand .version {
            display: inline-block;
            margin-top: .35rem;
            font-size: .65rem;
            background: var(--blue);
            color: #fff;
            border-radius: 3px;
            padding: 1px 6px;
        }

        .sb-section {
            font-size: .65rem;
            text-transform: uppercase;
            letter-spacing: .1em;
            color: #6b7280;
            padding: .5rem 1.25rem .2rem;
        }

        .sb-link {
            display: flex;
            align-items: center;
            gap: .5rem;
            padding: .45rem 1.25rem;
            color: #d1d5db;
            text-decoration: none;
            font-size: .82rem;
            border-left: 3px solid transparent;
            transition: background .1s, border-color .1s;
        }
        .sb-link:hover, .sb-link.active {
            background: rgba(255,255,255,.06);
            border-left-color: var(--blue);
            color: #fff;
        }

        .method-pill {
            font-size: .58rem;
            font-weight: 700;
            padding: 1px 5px;
            border-radius: 3px;
            letter-spacing: .04em;
            text-transform: uppercase;
        }
        .pill-post { background: #16a34a22; color: #86efac; }
        .pill-get  { background: #2563eb22; color: #93c5fd; }

        /* ── Content ── */
        #content {
            margin-left: var(--sidebar);
            flex: 1;
            padding: 2.5rem 3rem 4rem;
            max-width: 860px;
        }

        section { margin-bottom: 3.5rem; }
        section:target { scroll-margin-top: 1.5rem; }

        h1 { font-size: 1.6rem; font-weight: 800; color: var(--gray-900); margin-bottom: .4rem; }
        h2 {
            font-size: 1.1rem; font-weight: 700; color: var(--gray-900);
            margin-bottom: 1rem; padding-bottom: .5rem;
            border-bottom: 2px solid var(--gray-200);
        }
        h3 { font-size: .92rem; font-weight: 700; color: var(--gray-700); margin: 1.25rem 0 .5rem; }

        p { line-height: 1.7; margin-bottom: .75rem; color: var(--gray-600); }
        ul, ol { padding-left: 1.25rem; color: var(--gray-600); line-height: 1.8; }

        a { color: var(--blue); text-decoration: none; }
        a:hover { text-decoration: underline; }

        /* ── Endpoint block ── */
        .endpoint-header {
            display: flex;
            align-items: center;
            gap: .75rem;
            background: var(--gray-50);
            border: 1px solid var(--gray-200);
            border-radius: 8px 8px 0 0;
            padding: .75rem 1rem;
        }
        .endpoint-body {
            border: 1px solid var(--gray-200);
            border-top: none;
            border-radius: 0 0 8px 8px;
            padding: 1.25rem;
        }

        .badge-method {
            font-size: .72rem;
            font-weight: 800;
            padding: .25rem .55rem;
            border-radius: 4px;
            letter-spacing: .05em;
        }
        .badge-post { background: #dcfce7; color: #15803d; }
        .badge-get  { background: #dbeafe; color: #1d4ed8; }

        .endpoint-url {
            font-family: 'SF Mono', 'Fira Code', monospace;
            font-size: .88rem;
            color: var(--gray-900);
            font-weight: 600;
        }

        /* ── Code blocks ── */
        pre {
            background: #1e2433;
            color: #e2e8f0;
            border-radius: 8px;
            padding: 1rem 1.25rem;
            overflow-x: auto;
            font-family: 'SF Mono', 'Fira Code', 'Cascadia Code', monospace;
            font-size: .8rem;
            line-height: 1.65;
            margin: .75rem 0;
            position: relative;
        }

        .copy-btn {
            position: absolute;
            top: .6rem; right: .75rem;
            background: rgba(255,255,255,.1);
            border: none;
            color: #94a3b8;
            border-radius: 4px;
            padding: .2rem .55rem;
            font-size: .7rem;
            cursor: pointer;
            transition: background .15s;
        }
        .copy-btn:hover { background: rgba(255,255,255,.2); color: #fff; }

        code {
            font-family: 'SF Mono', 'Fira Code', monospace;
            font-size: .82em;
            background: #f1f5f9;
            color: #0f172a;
            padding: .1em .35em;
            border-radius: 4px;
        }
        pre code { background: none; color: inherit; padding: 0; font-size: 1em; }

        /* Syntax highlights */
        .hl-key    { color: #93c5fd; }
        .hl-str    { color: #86efac; }
        .hl-num    { color: #fcd34d; }
        .hl-bool   { color: #f9a8d4; }
        .hl-null   { color: #94a3b8; }
        .hl-method { color: #f472b6; font-weight: 600; }
        .hl-url    { color: #67e8f9; }
        .hl-flag   { color: #fbbf24; }
        .hl-cmt    { color: #64748b; font-style: italic; }

        /* ── Tables ── */
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: .82rem;
            margin: .75rem 0;
        }
        th {
            background: var(--gray-100);
            font-weight: 600;
            text-align: left;
            padding: .5rem .75rem;
            border: 1px solid var(--gray-200);
            color: var(--gray-700);
        }
        td {
            padding: .5rem .75rem;
            border: 1px solid var(--gray-200);
            vertical-align: top;
        }
        tr:hover td { background: var(--gray-50); }

        .tag-req {
            display: inline-block;
            font-size: .65rem;
            font-weight: 700;
            padding: 1px 5px;
            border-radius: 3px;
            background: #fef3c7;
            color: #92400e;
            text-transform: uppercase;
            letter-spacing: .04em;
        }
        .tag-opt {
            display: inline-block;
            font-size: .65rem;
            font-weight: 700;
            padding: 1px 5px;
            border-radius: 3px;
            background: #f0fdf4;
            color: #166534;
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        /* ── Status codes ── */
        .status-list { list-style: none; padding: 0; }
        .status-list li {
            display: flex;
            align-items: flex-start;
            gap: .75rem;
            padding: .5rem 0;
            border-bottom: 1px solid var(--gray-100);
        }
        .status-list li:last-child { border-bottom: none; }

        .sc {
            display: inline-block;
            min-width: 40px;
            text-align: center;
            font-weight: 700;
            font-size: .8rem;
            padding: .15rem .4rem;
            border-radius: 4px;
        }
        .sc-2 { background: #dcfce7; color: #15803d; }
        .sc-4 { background: #fee2e2; color: #991b1b; }

        .alert {
            border-radius: 8px;
            padding: .9rem 1.1rem;
            margin: 1rem 0;
            font-size: .85rem;
            line-height: 1.6;
        }
        .alert-blue  { background: #eff6ff; border-left: 4px solid var(--blue); color: #1e40af; }
        .alert-amber { background: #fffbeb; border-left: 4px solid var(--amber); color: #92400e; }
        .alert-green { background: #f0fdf4; border-left: 4px solid var(--green); color: #14532d; }

        /* ── Tabs ── */
        .tabs { display: flex; gap: 2px; margin-bottom: -1px; }
        .tab-btn {
            padding: .35rem .85rem;
            border: 1px solid var(--gray-200);
            border-bottom: none;
            background: var(--gray-100);
            border-radius: 6px 6px 0 0;
            font-size: .78rem;
            cursor: pointer;
            color: var(--gray-600);
            transition: background .1s;
        }
        .tab-btn.active { background: #1e2433; color: #e2e8f0; border-color: #1e2433; }
        .tab-pane { display: none; }
        .tab-pane.active { display: block; }

        @media (max-width: 768px) {
            #sidebar { display: none; }
            #content { margin-left: 0; padding: 1.5rem; }
        }
    </style>
</head>
<body>

<!-- ── Sidebar ──────────────────────────────────────────────── -->
<nav id="sidebar">
    <div class="sb-brand">
        <div class="org">Lazismu UMS · Keuangan</div>
        <div class="title">API Reference</div>
        <span class="version">v1.0</span>
    </div>

    <div class="sb-section">Pengantar</div>
    <a href="#pengantar" class="sb-link active">Tentang API</a>
    <a href="#autentikasi" class="sb-link">Autentikasi</a>
    <a href="#format" class="sb-link">Format & Konvensi</a>
    <a href="#kode-status" class="sb-link">Kode Status HTTP</a>

    <div class="sb-section">Endpoint</div>
    <a href="#ep-masuk" class="sb-link">
        <span class="method-pill pill-post">POST</span>
        Kirim Antrian
    </a>

    <div class="sb-section">Referensi</div>
    <a href="#ref-jenis-dana" class="sb-link">Jenis Dana</a>
    <a href="#ref-contoh" class="sb-link">Contoh Lengkap</a>
    <a href="#ref-env" class="sb-link">Konfigurasi</a>
</nav>

<!-- ── Content ──────────────────────────────────────────────── -->
<main id="content">

    <!-- Header -->
    <div style="margin-bottom:2.5rem;">
        <p style="font-size:.75rem;color:#9ca3af;text-transform:uppercase;letter-spacing:.1em;margin-bottom:.3rem;">
            Lazismu UMS · Sistem Keuangan
        </p>
        <h1>Dokumentasi API</h1>
        <p>Referensi lengkap untuk integrasi sistem eksternal dengan modul antrian penyaluran dana.</p>
        <div class="alert alert-blue" style="margin-top:1rem;">
            <strong>Base URL:</strong>
            <code><?= esc($baseUrl) ?></code>
        </div>
    </div>

    <!-- ── Pengantar ───────────────────────────────────────── -->
    <section id="pengantar">
        <h2>Tentang API</h2>
        <p>
            API ini digunakan oleh sistem eksternal (aplikasi donasi, portal web, dll.) untuk
            mengirimkan data penyaluran ke dalam sistem keuangan Lazismu. Data yang masuk akan
            tersimpan sebagai <strong>antrian</strong> dengan status <code>pending</code> dan
            menunggu verifikasi admin sebelum dicatat ke jurnal akuntansi.
        </p>
        <p>
            Admin dapat memverifikasi atau menolak setiap entri antrian melalui menu
            <strong>Penyaluran → Antrian Penyaluran</strong> di dashboard.
        </p>

        <h3>Alur Kerja</h3>
        <ol style="line-height:2;">
            <li>Sistem eksternal mengirim data via <code>POST /api/penyaluran/masuk</code></li>
            <li>Data tersimpan di tabel <code>penyaluran_antrian</code> dengan status <strong>pending</strong></li>
            <li>Admin membuka dashboard, memilih akun penyaluran dan rekening bank sumber</li>
            <li>Klik <strong>Verifikasi</strong> → jurnal double-entry otomatis terbuat</li>
            <li>Status berubah menjadi <strong>verified</strong>, data terhubung ke nomor jurnal</li>
        </ol>
    </section>

    <!-- ── Autentikasi ─────────────────────────────────────── -->
    <section id="autentikasi">
        <h2>Autentikasi</h2>
        <p>
            Semua endpoint di bawah <code>/api/</code> dilindungi dengan
            <strong>HTTP Basic Authentication</strong>. Sertakan header <code>Authorization</code>
            di setiap request.
        </p>

        <div class="alert alert-amber">
            <strong>Penting:</strong> Gunakan HTTPS di production agar credentials tidak terekspos
            dalam plain text.
        </div>

        <h3>Format Header</h3>
        <pre><code><span class="hl-key">Authorization</span>: Basic <span class="hl-str">&lt;base64(username:password)&gt;</span></code></pre>

        <h3>Contoh</h3>
        <pre><button class="copy-btn" onclick="copyCode(this)">Salin</button><code><span class="hl-cmt"># Encode credentials (username: lazismu, password: lazismu-api-2026)</span>
$ echo -n <span class="hl-str">"lazismu:lazismu-api-2026"</span> | base64
<span class="hl-num">bGF6aXNtdTpsYXppc211LWFwaS0yMDI2</span>

<span class="hl-cmt"># Gunakan di request</span>
<span class="hl-method">Authorization</span>: Basic <span class="hl-num">bGF6aXNtdTpsYXppc211LWFwaS0yMDI2</span>

<span class="hl-cmt"># Atau gunakan flag -u di curl (otomatis encode)</span>
curl <span class="hl-flag">-u</span> <span class="hl-str">"lazismu:lazismu-api-2026"</span> ...</code></pre>

        <h3>Respons jika Gagal Autentikasi</h3>
        <pre><button class="copy-btn" onclick="copyCode(this)">Salin</button><code>HTTP/1.1 <span class="hl-num">401</span> Unauthorized
<span class="hl-key">WWW-Authenticate</span>: Basic realm=<span class="hl-str">"Lazismu API"</span>
<span class="hl-key">Content-Type</span>: application/json

{
  <span class="hl-key">"success"</span>: <span class="hl-bool">false</span>,
  <span class="hl-key">"message"</span>: <span class="hl-str">"Username atau password tidak valid."</span>
}</code></pre>
    </section>

    <!-- ── Format ──────────────────────────────────────────── -->
    <section id="format">
        <h2>Format & Konvensi</h2>

        <table>
            <thead>
                <tr><th>Aspek</th><th>Detail</th></tr>
            </thead>
            <tbody>
                <tr><td>Format request body</td><td><code>application/json</code></td></tr>
                <tr><td>Format response</td><td><code>application/json</code></td></tr>
                <tr><td>Format tanggal</td><td><code>YYYY-MM-DD</code> (contoh: <code>2026-06-08</code>)</td></tr>
                <tr><td>Format jumlah</td><td>Angka desimal tanpa format mata uang (contoh: <code>500000</code> atau <code>500000.50</code>)</td></tr>
                <tr><td>Encoding</td><td>UTF-8</td></tr>
                <tr><td>Deteksi duplikat</td><td>Kombinasi <code>sumber</code> + <code>ref_eksternal</code> yang sama akan ditolak dengan <code>409 Conflict</code></td></tr>
            </tbody>
        </table>

        <h3>Struktur Respons</h3>
        <p>Semua respons mengikuti struktur berikut:</p>
        <pre><button class="copy-btn" onclick="copyCode(this)">Salin</button><code>{
  <span class="hl-key">"success"</span>: <span class="hl-bool">true</span> | <span class="hl-bool">false</span>,
  <span class="hl-key">"message"</span>: <span class="hl-str">"..."</span>,          <span class="hl-cmt">// Pesan singkat</span>
  <span class="hl-key">"id"</span>: <span class="hl-num">123</span>,                  <span class="hl-cmt">// Hanya pada 201 Created</span>
  <span class="hl-key">"errors"</span>: { <span class="hl-str">"field"</span>: <span class="hl-str">"..."</span> }  <span class="hl-cmt">// Hanya pada 422 Unprocessable</span>
}</code></pre>
    </section>

    <!-- ── Kode Status ─────────────────────────────────────── -->
    <section id="kode-status">
        <h2>Kode Status HTTP</h2>
        <ul class="status-list">
            <li>
                <span class="sc sc-2">201</span>
                <div>
                    <strong>Created</strong><br>
                    <span style="color:var(--gray-600);">Data berhasil diterima dan masuk ke antrian.</span>
                </div>
            </li>
            <li>
                <span class="sc sc-4">400</span>
                <div>
                    <strong>Bad Request</strong><br>
                    <span style="color:var(--gray-600);">Request tidak dapat diparsing (body kosong atau bukan JSON valid).</span>
                </div>
            </li>
            <li>
                <span class="sc sc-4">401</span>
                <div>
                    <strong>Unauthorized</strong><br>
                    <span style="color:var(--gray-600);">Header <code>Authorization</code> tidak ada atau credentials salah.</span>
                </div>
            </li>
            <li>
                <span class="sc sc-4">409</span>
                <div>
                    <strong>Conflict</strong><br>
                    <span style="color:var(--gray-600);">Kombinasi <code>sumber</code> + <code>ref_eksternal</code> sudah ada di antrian.</span>
                </div>
            </li>
            <li>
                <span class="sc sc-4">422</span>
                <div>
                    <strong>Unprocessable Entity</strong><br>
                    <span style="color:var(--gray-600);">Field wajib tidak diisi atau nilainya tidak valid. Lihat <code>errors</code> di body respons.</span>
                </div>
            </li>
        </ul>
    </section>

    <!-- ── Endpoint: POST /api/penyaluran/masuk ───────────── -->
    <section id="ep-masuk">
        <h2>Endpoint: Kirim Data Antrian Penyaluran</h2>

        <div class="endpoint-header">
            <span class="badge-method badge-post">POST</span>
            <span class="endpoint-url"><?= esc($baseUrl) ?>/api/penyaluran/masuk</span>
        </div>
        <div class="endpoint-body">
            <p>
                Mengirimkan satu record data penyaluran dari sistem eksternal ke antrian.
                Data akan tersimpan dengan status <code>pending</code> dan dapat diverifikasi
                admin melalui dashboard.
            </p>

            <h3>Request Headers</h3>
            <table>
                <thead>
                    <tr><th>Header</th><th>Nilai</th><th>Keterangan</th></tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code>Authorization</code></td>
                        <td><code>Basic &lt;base64&gt;</code></td>
                        <td><span class="tag-req">Wajib</span> Lihat bagian Autentikasi</td>
                    </tr>
                    <tr>
                        <td><code>Content-Type</code></td>
                        <td><code>application/json</code></td>
                        <td><span class="tag-req">Wajib</span></td>
                    </tr>
                </tbody>
            </table>

            <h3>Request Body</h3>
            <table>
                <thead>
                    <tr><th>Field</th><th>Tipe</th><th>Status</th><th>Keterangan</th></tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code>tanggal</code></td>
                        <td><code>string</code></td>
                        <td><span class="tag-req">Wajib</span></td>
                        <td>Tanggal penyaluran. Format: <code>YYYY-MM-DD</code></td>
                    </tr>
                    <tr>
                        <td><code>jenis_dana_id</code></td>
                        <td><code>integer</code></td>
                        <td><span class="tag-req">Wajib</span></td>
                        <td>ID jenis dana. Lihat tabel <a href="#ref-jenis-dana">Referensi Jenis Dana</a></td>
                    </tr>
                    <tr>
                        <td><code>uraian</code></td>
                        <td><code>string</code></td>
                        <td><span class="tag-req">Wajib</span></td>
                        <td>Deskripsi singkat penyaluran. Maks. 255 karakter</td>
                    </tr>
                    <tr>
                        <td><code>jumlah</code></td>
                        <td><code>number</code></td>
                        <td><span class="tag-req">Wajib</span></td>
                        <td>Nominal dalam Rupiah. Harus lebih dari 0</td>
                    </tr>
                    <tr>
                        <td><code>program_nama</code></td>
                        <td><code>string</code></td>
                        <td><span class="tag-opt">Opsional</span></td>
                        <td>Nama program penyaluran dari sistem pengirim</td>
                    </tr>
                    <tr>
                        <td><code>program_ext_id</code></td>
                        <td><code>integer</code></td>
                        <td><span class="tag-opt">Opsional</span></td>
                        <td>ID program di sistem pengirim (untuk referensi silang)</td>
                    </tr>
                    <tr>
                        <td><code>nama_penerima</code></td>
                        <td><code>string</code></td>
                        <td><span class="tag-opt">Opsional</span></td>
                        <td>Nama penerima manfaat sebagaimana di sistem pengirim</td>
                    </tr>
                    <tr>
                        <td><code>keterangan</code></td>
                        <td><code>string</code></td>
                        <td><span class="tag-opt">Opsional</span></td>
                        <td>Catatan atau informasi tambahan</td>
                    </tr>
                    <tr>
                        <td><code>sumber</code></td>
                        <td><code>string</code></td>
                        <td><span class="tag-opt">Opsional</span></td>
                        <td>Identitas sistem pengirim (contoh: <code>"app_donasi"</code>, <code>"web_portal"</code>)</td>
                    </tr>
                    <tr>
                        <td><code>ref_eksternal</code></td>
                        <td><code>string</code></td>
                        <td><span class="tag-opt">Opsional</span></td>
                        <td>ID unik transaksi di sistem pengirim. Dipakai untuk deteksi duplikat bersama <code>sumber</code></td>
                    </tr>
                </tbody>
            </table>

            <h3>Contoh Request & Respons</h3>

            <div class="tabs">
                <button class="tab-btn active" onclick="switchTab(this, 'tab-success')">201 Berhasil</button>
                <button class="tab-btn" onclick="switchTab(this, 'tab-unauth')">401 Unauthorized</button>
                <button class="tab-btn" onclick="switchTab(this, 'tab-unprocess')">422 Validasi</button>
                <button class="tab-btn" onclick="switchTab(this, 'tab-conflict')">409 Duplikat</button>
            </div>

            <div id="tab-success" class="tab-pane active">
                <pre><button class="copy-btn" onclick="copyCode(this)">Salin</button><code><span class="hl-cmt">### Request</span>
<span class="hl-method">POST</span> <span class="hl-url">/api/penyaluran/masuk</span>
<span class="hl-key">Authorization</span>: Basic bGF6aXNtdTpsYXppc211LWFwaS0yMDI2
<span class="hl-key">Content-Type</span>: application/json

{
  <span class="hl-key">"tanggal"</span>:        <span class="hl-str">"2026-06-08"</span>,
  <span class="hl-key">"jenis_dana_id"</span>:  <span class="hl-num">1</span>,
  <span class="hl-key">"program_nama"</span>:   <span class="hl-str">"Beasiswa Yatim Semester Genap"</span>,
  <span class="hl-key">"program_ext_id"</span>: <span class="hl-num">42</span>,
  <span class="hl-key">"nama_penerima"</span>:  <span class="hl-str">"Ahmad Fauzi"</span>,
  <span class="hl-key">"uraian"</span>:         <span class="hl-str">"Penyaluran beasiswa bulan Juni 2026"</span>,
  <span class="hl-key">"keterangan"</span>:     <span class="hl-str">"Semester genap TA 2025/2026"</span>,
  <span class="hl-key">"jumlah"</span>:         <span class="hl-num">500000</span>,
  <span class="hl-key">"sumber"</span>:         <span class="hl-str">"app_donasi"</span>,
  <span class="hl-key">"ref_eksternal"</span>:  <span class="hl-str">"TRX-2026-00142"</span>
}

<span class="hl-cmt">### Response — 201 Created</span>
{
  <span class="hl-key">"success"</span>: <span class="hl-bool">true</span>,
  <span class="hl-key">"id"</span>:      <span class="hl-num">7</span>,
  <span class="hl-key">"message"</span>: <span class="hl-str">"Data antrian penyaluran berhasil diterima dan menunggu verifikasi admin."</span>
}</code></pre>
            </div>

            <div id="tab-unauth" class="tab-pane">
                <pre><button class="copy-btn" onclick="copyCode(this)">Salin</button><code><span class="hl-cmt">### Response — 401 Unauthorized</span>
<span class="hl-cmt"># Tidak ada header Authorization, atau username/password salah</span>

HTTP/1.1 <span class="hl-num">401</span> Unauthorized
<span class="hl-key">WWW-Authenticate</span>: Basic realm=<span class="hl-str">"Lazismu API"</span>

{
  <span class="hl-key">"success"</span>: <span class="hl-bool">false</span>,
  <span class="hl-key">"message"</span>: <span class="hl-str">"Username atau password tidak valid."</span>
}</code></pre>
            </div>

            <div id="tab-unprocess" class="tab-pane">
                <pre><button class="copy-btn" onclick="copyCode(this)">Salin</button><code><span class="hl-cmt">### Response — 422 Unprocessable Entity</span>
<span class="hl-cmt"># Field wajib tidak diisi atau nilainya tidak valid</span>

{
  <span class="hl-key">"success"</span>: <span class="hl-bool">false</span>,
  <span class="hl-key">"errors"</span>: {
    <span class="hl-key">"tanggal"</span>:      <span class="hl-str">"Tanggal wajib diisi dan harus valid (YYYY-MM-DD)."</span>,
    <span class="hl-key">"jenis_dana_id"</span>: <span class="hl-str">"Jenis dana wajib diisi."</span>,
    <span class="hl-key">"jumlah"</span>:       <span class="hl-str">"Jumlah harus lebih dari 0."</span>
  }
}</code></pre>
            </div>

            <div id="tab-conflict" class="tab-pane">
                <pre><button class="copy-btn" onclick="copyCode(this)">Salin</button><code><span class="hl-cmt">### Response — 409 Conflict</span>
<span class="hl-cmt"># Kombinasi sumber + ref_eksternal sudah ada di antrian</span>

{
  <span class="hl-key">"success"</span>: <span class="hl-bool">false</span>,
  <span class="hl-key">"message"</span>: <span class="hl-str">"Referensi duplikat sudah ada di antrian."</span>
}</code></pre>
            </div>

        </div>
    </section>

    <!-- ── Referensi: Jenis Dana ───────────────────────────── -->
    <section id="ref-jenis-dana">
        <h2>Referensi: Jenis Dana</h2>
        <p>Gunakan nilai <code>id</code> pada field <code>jenis_dana_id</code> di request body.</p>
        <table>
            <thead>
                <tr><th>id</th><th>kode</th><th>Nama</th><th>Keterangan</th></tr>
            </thead>
            <tbody>
                <tr><td><code>1</code></td><td><code>ZAKAT</code></td><td>Dana Zakat</td><td>Zakat maal, fitrah, bagi hasil zakat</td></tr>
                <tr><td><code>2</code></td><td><code>INFAK_T</code></td><td>Infak Terikat</td><td>Infak yang peruntukannya ditentukan donatur</td></tr>
                <tr><td><code>3</code></td><td><code>INFAK_TT</code></td><td>Infak Tidak Terikat</td><td>Infak umum, kotak amal, sabtu seribu</td></tr>
                <tr><td><code>4</code></td><td><code>AMIL</code></td><td>Dana Amil</td><td>Hak pengelola/amil</td></tr>
            </tbody>
        </table>
        <div class="alert alert-green">
            Jika sistem eksternal menangani dana zakat, gunakan <code>jenis_dana_id: 1</code>.
            Untuk infak/sedekah umum, gunakan <code>jenis_dana_id: 3</code>.
        </div>
    </section>

    <!-- ── Referensi: Contoh Kode ──────────────────────────── -->
    <section id="ref-contoh">
        <h2>Contoh Kode Integrasi</h2>

        <div class="tabs" id="lang-tabs">
            <button class="tab-btn active" onclick="switchTab(this, 'ex-curl', 'lang-tabs')">cURL</button>
            <button class="tab-btn" onclick="switchTab(this, 'ex-php', 'lang-tabs')">PHP</button>
            <button class="tab-btn" onclick="switchTab(this, 'ex-js', 'lang-tabs')">JavaScript</button>
            <button class="tab-btn" onclick="switchTab(this, 'ex-python', 'lang-tabs')">Python</button>
        </div>

        <div id="ex-curl" class="tab-pane active">
            <pre><button class="copy-btn" onclick="copyCode(this)">Salin</button><code>curl <span class="hl-flag">-X POST</span> <span class="hl-str">"<?= esc($baseUrl) ?>/api/penyaluran/masuk"</span> \
  <span class="hl-flag">-u</span> <span class="hl-str">"lazismu:lazismu-api-2026"</span> \
  <span class="hl-flag">-H</span> <span class="hl-str">"Content-Type: application/json"</span> \
  <span class="hl-flag">-d</span> <span class="hl-str">'{
    "tanggal":        "2026-06-08",
    "jenis_dana_id":  1,
    "program_nama":   "Beasiswa Yatim",
    "nama_penerima":  "Ahmad Fauzi",
    "uraian":         "Penyaluran beasiswa bulan Juni",
    "jumlah":         500000,
    "sumber":         "app_donasi",
    "ref_eksternal":  "TRX-2026-00142"
  }'</span></code></pre>
        </div>

        <div id="ex-php" class="tab-pane">
            <pre><button class="copy-btn" onclick="copyCode(this)">Salin</button><code><span class="hl-bool">&lt;?php</span>

<span class="hl-key">$url</span>  = <span class="hl-str">'<?= esc($baseUrl) ?>/api/penyaluran/masuk'</span>;
<span class="hl-key">$user</span> = <span class="hl-str">'lazismu'</span>;
<span class="hl-key">$pass</span> = <span class="hl-str">'lazismu-api-2026'</span>;

<span class="hl-key">$payload</span> = json_encode([
    <span class="hl-str">'tanggal'</span>       => <span class="hl-str">'2026-06-08'</span>,
    <span class="hl-str">'jenis_dana_id'</span> => <span class="hl-num">1</span>,
    <span class="hl-str">'program_nama'</span>  => <span class="hl-str">'Beasiswa Yatim'</span>,
    <span class="hl-str">'nama_penerima'</span> => <span class="hl-str">'Ahmad Fauzi'</span>,
    <span class="hl-str">'uraian'</span>        => <span class="hl-str">'Penyaluran beasiswa bulan Juni'</span>,
    <span class="hl-str">'jumlah'</span>        => <span class="hl-num">500000</span>,
    <span class="hl-str">'sumber'</span>        => <span class="hl-str">'app_donasi'</span>,
    <span class="hl-str">'ref_eksternal'</span> => <span class="hl-str">'TRX-2026-00142'</span>,
]);

<span class="hl-key">$ch</span> = curl_init(<span class="hl-key">$url</span>);
curl_setopt_array(<span class="hl-key">$ch</span>, [
    CURLOPT_POST           => <span class="hl-bool">true</span>,
    CURLOPT_POSTFIELDS     => <span class="hl-key">$payload</span>,
    CURLOPT_RETURNTRANSFER => <span class="hl-bool">true</span>,
    CURLOPT_USERPWD        => <span class="hl-str">"<span class="hl-key">$user</span>:<span class="hl-key">$pass</span>"</span>,
    CURLOPT_HTTPHEADER     => [<span class="hl-str">'Content-Type: application/json'</span>],
]);

<span class="hl-key">$response</span> = json_decode(curl_exec(<span class="hl-key">$ch</span>), <span class="hl-bool">true</span>);
<span class="hl-key">$status</span>   = curl_getinfo(<span class="hl-key">$ch</span>, CURLINFO_HTTP_CODE);
curl_close(<span class="hl-key">$ch</span>);

if (<span class="hl-key">$status</span> === <span class="hl-num">201</span>) {
    echo <span class="hl-str">"Berhasil. ID antrian: "</span> . <span class="hl-key">$response</span>[<span class="hl-str">'id'</span>];
} else {
    echo <span class="hl-str">"Gagal: "</span> . <span class="hl-key">$response</span>[<span class="hl-str">'message'</span>];
}</code></pre>
        </div>

        <div id="ex-js" class="tab-pane">
            <pre><button class="copy-btn" onclick="copyCode(this)">Salin</button><code><span class="hl-cmt">// Node.js / Browser (fetch API)</span>

const credentials = btoa(<span class="hl-str">'lazismu:lazismu-api-2026'</span>);

const response = await fetch(<span class="hl-str">'<?= esc($baseUrl) ?>/api/penyaluran/masuk'</span>, {
  method:  <span class="hl-str">'POST'</span>,
  headers: {
    <span class="hl-str">'Authorization'</span>: <span class="hl-str">`Basic ${credentials}`</span>,
    <span class="hl-str">'Content-Type'</span>:  <span class="hl-str">'application/json'</span>,
  },
  body: JSON.stringify({
    tanggal:       <span class="hl-str">'2026-06-08'</span>,
    jenis_dana_id: <span class="hl-num">1</span>,
    program_nama:  <span class="hl-str">'Beasiswa Yatim'</span>,
    nama_penerima: <span class="hl-str">'Ahmad Fauzi'</span>,
    uraian:        <span class="hl-str">'Penyaluran beasiswa bulan Juni'</span>,
    jumlah:        <span class="hl-num">500000</span>,
    sumber:        <span class="hl-str">'app_donasi'</span>,
    ref_eksternal: <span class="hl-str">'TRX-2026-00142'</span>,
  }),
});

const data = await response.json();

if (response.status === <span class="hl-num">201</span>) {
  console.log(<span class="hl-str">'Berhasil. ID antrian:'</span>, data.id);
} else {
  console.error(<span class="hl-str">'Gagal:'</span>, data.message || data.errors);
}</code></pre>
        </div>

        <div id="ex-python" class="tab-pane">
            <pre><button class="copy-btn" onclick="copyCode(this)">Salin</button><code><span class="hl-cmt"># pip install requests</span>
import requests

url  = <span class="hl-str">"<?= esc($baseUrl) ?>/api/penyaluran/masuk"</span>
auth = (<span class="hl-str">"lazismu"</span>, <span class="hl-str">"lazismu-api-2026"</span>)

payload = {
    <span class="hl-str">"tanggal"</span>:       <span class="hl-str">"2026-06-08"</span>,
    <span class="hl-str">"jenis_dana_id"</span>: <span class="hl-num">1</span>,
    <span class="hl-str">"program_nama"</span>:  <span class="hl-str">"Beasiswa Yatim"</span>,
    <span class="hl-str">"nama_penerima"</span>: <span class="hl-str">"Ahmad Fauzi"</span>,
    <span class="hl-str">"uraian"</span>:        <span class="hl-str">"Penyaluran beasiswa bulan Juni"</span>,
    <span class="hl-str">"jumlah"</span>:        <span class="hl-num">500000</span>,
    <span class="hl-str">"sumber"</span>:        <span class="hl-str">"app_donasi"</span>,
    <span class="hl-str">"ref_eksternal"</span>: <span class="hl-str">"TRX-2026-00142"</span>,
}

r = requests.post(url, json=payload, auth=auth)

if r.status_code == <span class="hl-num">201</span>:
    print(<span class="hl-str">f"Berhasil. ID antrian: {r.json()['id']}"</span>)
else:
    print(<span class="hl-str">f"Gagal ({r.status_code}): {r.text}"</span>)</code></pre>
        </div>

    </section>

    <!-- ── Konfigurasi ─────────────────────────────────────── -->
    <section id="ref-env">
        <h2>Konfigurasi</h2>
        <p>
            Credentials Basic Auth dikonfigurasi melalui file <code>.env</code> di root project.
            Ubah nilai berikut sesuai kebutuhan production:
        </p>
        <pre><button class="copy-btn" onclick="copyCode(this)">Salin</button><code><span class="hl-cmt"># .env</span>
<span class="hl-key">API_BASIC_USERNAME</span> = <span class="hl-str">lazismu</span>
<span class="hl-key">API_BASIC_PASSWORD</span> = <span class="hl-str">ganti-dengan-password-kuat</span></code></pre>

        <div class="alert alert-amber">
            <strong>Production Checklist:</strong>
            <ul style="margin-top:.5rem;line-height:2;">
                <li>Ganti password default sebelum deploy ke production</li>
                <li>Pastikan API diakses via <strong>HTTPS</strong> agar credentials tidak terekspos</li>
                <li>Simpan credentials di secret manager atau environment variable CI/CD, bukan di version control</li>
            </ul>
        </div>
    </section>

</main>

<script>
function switchTab(btn, targetId, groupId) {
    const group = groupId ? document.getElementById(groupId) : btn.closest('section');
    group.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    group.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById(targetId).classList.add('active');
}

function copyCode(btn) {
    const pre  = btn.parentElement;
    const text = pre.innerText.replace(/^Salin\n/, '');
    navigator.clipboard.writeText(text).then(() => {
        btn.textContent = 'Tersalin!';
        setTimeout(() => btn.textContent = 'Salin', 2000);
    });
}

// Highlight active sidebar link on scroll
const sections = document.querySelectorAll('section[id]');
const sbLinks   = document.querySelectorAll('.sb-link');

window.addEventListener('scroll', () => {
    let current = '';
    sections.forEach(s => {
        if (window.scrollY >= s.offsetTop - 80) current = s.id;
    });
    sbLinks.forEach(l => {
        l.classList.toggle('active', l.getAttribute('href') === '#' + current);
    });
}, { passive: true });
</script>

</body>
</html>
