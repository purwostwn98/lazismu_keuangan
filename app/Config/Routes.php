<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Home::index');
$routes->get('dashboard', 'Dashboard::index');

// Penerimaan ZIS
$routes->get('penerimaan',               'Penerimaan::index');
$routes->get('penerimaan/input',         'Penerimaan::input');
$routes->post('penerimaan/store',        'Penerimaan::store');
$routes->get('penerimaan/laporan',       'Penerimaan::laporan');
$routes->get('penerimaan/delete/(:num)', 'Penerimaan::delete/$1');

// Penyaluran
$routes->get('penyaluran/daftar',           'Penyaluran::index');
$routes->get('penyaluran/input',     'Penyaluran::input');
$routes->post('penyaluran/store',    'Penyaluran::store');
$routes->get('penyaluran/program',   'Program::index');
$routes->get('penyaluran/(:num)',    'Penyaluran::show/$1');

// Antrian Penyaluran (On-Coming dari sistem eksternal)
$routes->get('penyaluran/antrian',                    'PenyaluranAntrian::index');
$routes->get('penyaluran/antrian/(:num)',              'PenyaluranAntrian::show/$1');
$routes->post('penyaluran/antrian/verifikasi/(:num)',  'PenyaluranAntrian::verifikasi/$1');
$routes->post('penyaluran/antrian/tolak/(:num)',       'PenyaluranAntrian::tolak/$1');
$routes->post('penyaluran/antrian/hapus/(:num)',       'PenyaluranAntrian::hapus/$1');

// API endpoint (dilindungi BasicAuthFilter)
$routes->post('api/penyaluran/masuk', 'PenyaluranAntrian::apiTerima');

// Dokumentasi API (publik, tanpa login)
$routes->get('api/docs', 'ApiDocs::index');

// AJAX
$routes->get('ajax/rekening',  'Penyaluran::ajaxRekening');
$routes->get('ajax/program',   'Penyaluran::ajaxProgram');
$routes->get('ajax/penerima',  'Penyaluran::ajaxPenerima');


// Master Data
$routes->get('master/akun',                  'Master\Akun::index');
$routes->post('master/akun/store',           'Master\Akun::store');
$routes->post('master/akun/update/(:num)',   'Master\Akun::update/$1');
$routes->get('master/akun/delete/(:num)',    'Master\Akun::delete/$1');

$routes->get('master/penerima',                  'Master\PenerimaManfaat::index');
$routes->post('master/penerima/store',           'Master\PenerimaManfaat::store');
$routes->post('master/penerima/update/(:num)',   'Master\PenerimaManfaat::update/$1');
$routes->get('master/penerima/delete/(:num)',    'Master\PenerimaManfaat::delete/$1');

$routes->get('master/donatur',                      'Master\Donatur::index');
$routes->get('master/donatur/dashboard',            'Master\Donatur::dashboard');
$routes->post('master/donatur/store',               'Master\Donatur::store');
$routes->post('master/donatur/update/(:num)',        'Master\Donatur::update/$1');
$routes->get('master/donatur/toggle/(:num)',         'Master\Donatur::toggleAktif/$1');
$routes->get('master/donatur/delete/(:num)',         'Master\Donatur::delete/$1');
$routes->post('master/donatur/buat-akun/(:num)',     'Master\Donatur::buatAkun/$1');
$routes->get('master/donatur/hapus-akun/(:num)',     'Master\Donatur::hapusAkun/$1');

$routes->get('master/periode',                 'Master\Periode::index');
$routes->post('master/periode/store',          'Master\Periode::store');
$routes->post('master/periode/update/(:num)',  'Master\Periode::update/$1');
$routes->get('master/periode/tutup/(:num)',    'Master\Periode::tutup/$1');
$routes->get('master/periode/delete/(:num)',   'Master\Periode::delete/$1');

$routes->get('master/rekening',             'Master\RekeningBank::index');
$routes->post('master/rekening/store',      'Master\RekeningBank::store');
$routes->post('master/rekening/update/(:num)', 'Master\RekeningBank::update/$1');
$routes->get('master/rekening/toggle/(:num)', 'Master\RekeningBank::toggleAktif/$1');

$routes->get('master/saldo-awal',           'Master\SaldoAwal::index');
$routes->post('master/saldo-awal/store',    'Master\SaldoAwal::store');

// Piutang
$routes->get('piutang',                  'Piutang::index');
$routes->get('piutang/input',            'Piutang::input');
$routes->post('piutang/store',           'Piutang::store');
$routes->get('piutang/(:num)',           'Piutang::show/$1');
$routes->post('piutang/bayar/(:num)',    'Piutang::bayar/$1');
$routes->get('piutang/hapus-buku/(:num)','Piutang::hapusBuku/$1');
$routes->get('piutang/delete/(:num)',    'Piutang::delete/$1');

// Persediaan
$routes->get('persediaan',                        'Persediaan::index');
$routes->get('persediaan/input',                  'Persediaan::input');
$routes->post('persediaan/store',                 'Persediaan::store');
$routes->get('persediaan/(:num)',                 'Persediaan::show/$1');
$routes->post('persediaan/mutasi-store/(:num)',   'Persediaan::mutasiStore/$1');
$routes->get('persediaan/mutasi-delete/(:num)',   'Persediaan::mutasiDelete/$1');
$routes->get('persediaan/delete/(:num)',          'Persediaan::delete/$1');

// Biaya Operasional
$routes->get('biaya',                    'BiayaOperasional::index');
$routes->get('biaya/input',              'BiayaOperasional::input');
$routes->post('biaya/store',             'BiayaOperasional::store');
$routes->get('biaya/cetak/(:num)',           'BiayaOperasional::cetak/$1');
$routes->get('biaya/edit/(:num)',            'BiayaOperasional::edit/$1');
$routes->post('biaya/update/(:num)',         'BiayaOperasional::update/$1');
$routes->get('biaya/delete/(:num)',          'BiayaOperasional::delete/$1');
$routes->post('biaya/upload-bukti/(:num)',  'BiayaOperasional::uploadBukti/$1');
$routes->get('biaya/delete-bukti/(:num)',   'BiayaOperasional::deleteBukti/$1');
$routes->get('biaya/(:num)',                'BiayaOperasional::show/$1');

// Jurnal
$routes->get('jurnal',               'Jurnal::index');
$routes->get('jurnal/input',         'Jurnal::input');
$routes->post('jurnal/store',        'Jurnal::store');
$routes->get('jurnal/delete/(:num)',  'Jurnal::delete/$1');
$routes->get('jurnal/reverse/(:num)', 'Jurnal::reverse/$1');

// Mutasi / Transfer Rekening
$routes->get('mutasi',               'Mutasi::index');
$routes->get('mutasi/input',         'Mutasi::input');
$routes->post('mutasi/store',        'Mutasi::store');
$routes->get('mutasi/delete/(:num)', 'Mutasi::delete/$1');

// Laporan Keuangan
$routes->get('laporan/posisi-keuangan',  'Laporan::posisiKeuangan');
$routes->get('laporan/perubahan-dana',   'Laporan::perubahanDana');
$routes->get('laporan/arus-kas',         'Laporan::arusKas');

// Pengguna
$routes->get('pengguna',                   'Pengguna::index');
$routes->post('pengguna/store',            'Pengguna::store');
$routes->post('pengguna/update/(:num)',    'Pengguna::update/$1');
$routes->get('pengguna/toggle/(:num)',     'Pengguna::toggleAktif/$1');
$routes->get('pengguna/delete/(:num)',     'Pengguna::delete/$1');

// Portal Donatur
$routes->get('donatur/portal',            'Donatur\Portal::index');
$routes->get('donatur/profil',            'Donatur\Portal::profil');
$routes->post('donatur/profil/update',    'Donatur\Portal::updateProfil');
$routes->post('donatur/ganti-password',   'Donatur\Portal::gantiPassword');

// Auth
$routes->get('login', 'Auth::index');
$routes->post('login', 'Auth::login');
$routes->get('logout', 'Auth::logout');
