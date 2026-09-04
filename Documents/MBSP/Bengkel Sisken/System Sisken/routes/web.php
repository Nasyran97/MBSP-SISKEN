<?php

// use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
// use Illuminate\Support\Facades\DB;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\KenderaanController;
use App\Http\Controllers\RoadTaxController;
use App\Http\Controllers\InsuransController;
use App\Http\Controllers\PengawasController;
use App\Http\Controllers\TuntutanController;
use App\Http\Controllers\MahkamahController;
use App\Http\Controllers\LupusController;
use App\Http\Controllers\InsuransStatistikController;
use App\Http\Controllers\PenyelenggaraanController;
use App\Http\Controllers\ImpersonateController;


// dashboard pages
// Route::get('/dashboard', function () {
//     if (!session()->has('user_id')) {
//         return redirect()->route('signin');
//     }
//     return view('pages.dashboard.ecommerce', ['title' => 'E-commerce Dashboard']);
// })->name('dashboard');

//Login process
Route::post('/login', [LoginController::class, 'login'])->name('login.submit');

Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// authentication pages
Route::get('/', function () {
    return view('pages.auth.signin', ['title' => 'Log Masuk']);
})->name('signin');

Route::get('/signup', function () {
    return view('pages.auth.signup', ['title' => 'Sign Up']);
})->name('signup');

Route::middleware(['check.login', 'nocache'])->group(function () {

    // calender pages
    Route::get('/calendar', function () {
        return view('pages.calender', ['title' => 'Calendar']);
    })->name('calendar');

    // profile pages
    Route::get('/profile', function () {
        return view('pages.profile', ['title' => 'Profile']);
    })->name('profile');

    // form pages
    Route::get('/form-elements', function () {
        return view('pages.form.form-elements', ['title' => 'Form Elements']);
    })->name('form-elements');

    // tables pages
    Route::get('/basic-tables', function () {
        return view('pages.tables.basic-tables', ['title' => 'Basic Tables']);
    })->name('basic-tables');

    // pages

    Route::get('/blank', function () {
        return view('pages.blank', ['title' => 'Blank']);
    })->name('blank');

    // error pages
    Route::get('/error-404', function () {
        return view('pages.errors.error-404', ['title' => 'Error 404']);
    })->name('error-404');

    // chart pages
    Route::get('/line-chart', function () {
        return view('pages.chart.line-chart', ['title' => 'Line Chart']);
    })->name('line-chart');

    Route::get('/bar-chart', function () {
        return view('pages.chart.bar-chart', ['title' => 'Bar Chart']);
    })->name('bar-chart');

    // ui elements pages
    Route::get('/alerts', function () {
        return view('pages.ui-elements.alerts', ['title' => 'Alerts']);
    })->name('alerts');

    Route::get('/avatars', function () {
        return view('pages.ui-elements.avatars', ['title' => 'Avatars']);
    })->name('avatars');

    Route::get('/badge', function () {
        return view('pages.ui-elements.badges', ['title' => 'Badges']);
    })->name('badges');

    Route::get('/buttons', function () {
        return view('pages.ui-elements.buttons', ['title' => 'Buttons']);
    })->name('buttons');

    Route::get('/image', function () {
        return view('pages.ui-elements.images', ['title' => 'Images']);
    })->name('images');

    Route::get('/videos', function () {
        return view('pages.ui-elements.videos', ['title' => 'Videos']);
    })->name('videos');


    Route::get('/dashboard', function () {
        return view('pages.dashboard.ecommerce', ['title' => 'SISKEN Dashboard']);
    })->name('dashboard');


    //SIDEMENU TETAPAN - USER
    Route::get('/user-list', [UserController::class, 'index'])->name('user-list');
    Route::get('/user-add', [UserController::class, 'create'])->name('user-add');
    Route::post('/user-store', [UserController::class, 'store'])->name('user-store');
    Route::get('/user-edit/{id}', [UserController::class, 'edit'])->name('user-edit');
    Route::put('/user-update/{id}', [UserController::class, 'update'])->name('user-update');
    Route::post('/user-delete/{id}', [UserController::class, 'delete'])->name('user-delete');
    Route::post('/get-roles-by-module', [UserController::class, 'getRolesByModule'])->name('get-roles-by-module');
    Route::post('/user-activate/{id}', [UserController::class, 'activate'])->name('user-activate');
    Route::post('/get-staff-by-department', [UserController::class, 'getStaffByDepartment'])->name('get-staff-by-department');

    //SIDEMENU TETAPAN - PENGAWAS KENDERAAN
    Route::get('/pengawas-list', [PengawasController::class, 'list'])->name('pengawas-list');
    Route::get('/pengawas-add', [PengawasController::class, 'create'])->name('pengawas-add');
    Route::post('/pengawas-store', [PengawasController::class, 'store'])->name('pengawas-store');
    Route::post('/pengawas-delete/{userId}', [PengawasController::class, 'delete'])->name('pengawas-delete');
    Route::post('/pengawas-activate/{userId}', [PengawasController::class, 'activate'])->name('pengawas-activate');
    Route::get('/pengawas/{userId}/edit', [PengawasController::class, 'edit'])->name('pengawas-edit');
    Route::put('/pengawas/{userId}/update-vehicle', [PengawasController::class, 'updateVehicle'])->name('pengawas-update-vehicle');

    //SIDEMENU TETEPAN - REKOD KENDERAAN
    Route::get('/rekod-car-list', [KenderaanController::class, 'carList'])->name('rekod-car-list');
    Route::get('/rekod-car-add', [KenderaanController::class, 'create'])->name('rekod-car-add');
    Route::post('/rekod-car-store', [KenderaanController::class, 'store'])->name('rekod-car-store');
    Route::get('/rekod-car-edit/{id}', [KenderaanController::class, 'edit'])->name('rekod-car-edit');
    Route::put('/rekod-car-update/{id}', [KenderaanController::class, 'update'])->name('rekod-car-update');
    Route::post('/rekod-car/{id}/upload-file', [KenderaanController::class, 'uploadVehicleFile'])->name('rekod-car-upload-file');
    Route::delete('/rekod-car/{id}/delete-file', [KenderaanController::class, 'deleteVehicleFile'])->name('rekod-car-delete-file');
    Route::get('/rekod-car/view-file', function () {
        $path = request('path');
    
        if (!$path || !Storage::disk('synology')->exists($path)) {
            abort(404, 'File not found.');
        }
    
        $mimeType = Storage::disk('synology')->mimeType($path) ?? 'application/octet-stream';
        $fileName = basename($path);
    
        return response(Storage::disk('synology')->get($path), 200)
            ->header('Content-Type', $mimeType)
            ->header('Content-Disposition', 'inline; filename="' . $fileName . '"');
    })->name('rekod-car-view-file');

    //SIDEMENU TETAPAN - KENDERAAN - JENIS BADAN
    Route::get('/jenis-badan-list', [KenderaanController::class, 'jenisBadanList'])->name('jenis-badan-list');
    Route::get('/jenis-badan-add', [KenderaanController::class, 'jenisBadanAdd'])->name('jenis-badan-add');
    Route::post('/jenis-badan-store', [KenderaanController::class, 'jenisBadanStore'])->name('jenis-badan-store');
    Route::patch('/jenis-badan/{id}/update', [KenderaanController::class, 'jenisBadanUpdate'])->name('jenis-badan-update');
    Route::delete('/jenis-badan/{id}/delete', [KenderaanController::class, 'jenisBadanDelete'])->name('jenis-badan-delete');

    //SIDEMENU TETAPAN - KENDERAAN - KELAS KEGUNAAN
    Route::get('/kelas-guna-list', [KenderaanController::class, 'kelasGunaList'])->name('kelas-guna-list');
    Route::get('/kelas-guna-add', [KenderaanController::class, 'kelasGunaAdd'])->name('kelas-guna-add');
    Route::post('/kelas-guna-store', [KenderaanController::class, 'kelasGunaStore'])->name('kelas-guna-store');
    Route::patch('/kelas-guna/{id}/update', [KenderaanController::class, 'kelasGunaUpdate'])->name('kelas-guna-update');
    Route::delete('/kelas-guna/{id}/delete', [KenderaanController::class, 'kelasGunaDelete'])->name('kelas-guna-delete');

    //SIDEMENU TETAPAN - KENDERAAN - LOKASI PENEMPATAN
    Route::get('/lokasi-penempatan-list', [KenderaanController::class, 'lokasiPenempatanList'])->name('lokasi-penempatan-list');
    Route::get('/lokasi-penempatan-add', [KenderaanController::class, 'lokasiPenempatanAdd'])->name('lokasi-penempatan-add');
    Route::post('/lokasi-penempatan-store', [KenderaanController::class, 'lokasiPenempatanStore'])->name('lokasi-penempatan-store');
    Route::patch('/lokasi-penempatan/{id}/update', [KenderaanController::class, 'lokasiPenempatanUpdate'])->name('lokasi-penempatan-update');
    Route::delete('/lokasi-penempatan/{id}/delete', [KenderaanController::class, 'lokasiPenempatanDelete'])->name('lokasi-penempatan-delete');

    //SIDEMENU TETAPAN - KENDERAAN - PEMBEKAL
    Route::get('/pembekal-list', [KenderaanController::class, 'pembekalList'])->name('pembekal-list');
    Route::get('/pembekal-add', [KenderaanController::class, 'pembekalAdd'])->name('pembekal-add');
    Route::post('/pembekal-store', [KenderaanController::class, 'pembekalStore'])->name('pembekal-store');
    Route::patch('/pembekal/{id}/update', [KenderaanController::class, 'pembekalUpdate'])->name('pembekal-update');
    Route::delete('/pembekal/{id}/delete', [KenderaanController::class, 'pembekalDelete'])->name('pembekal-delete');

    //SIDEMENU TETAPAN - INSURANS
    Route::get('/insurans-panel-list', [InsuransController::class, 'panelList'])->name('insurans-panel-list');
    Route::patch('/insurans-panel/{id}/activate', [InsuransController::class, 'activatePanel'])->name('insurans-panel-activate');
    Route::patch('/insurans-panel/{id}/deactivate', [InsuransController::class, 'deactivatePanel'])->name('insurans-panel-deactivate');
    Route::get('/insurans-panel-add', [InsuransController::class, 'panelAdd'])->name('insurans-panel-add');
    Route::post('/insurans-panel-store', [InsuransController::class, 'panelStore'])->name('insurans-panel-store');

    //SIDEMENU TETAPAN TUNTUTAN AWAM
    Route::get('/tuntutan-awam-list', [TuntutanController::class, 'tuntutanAwamList'])->name('tuntutan-awam-list');
    Route::get('/tuntutan-awam-add', [TuntutanController::class, 'tuntutanAwamAdd'])->name('tuntutan-awam-add');
    Route::post('/tuntutan-awam-store', [TuntutanController::class, 'tuntutanAwamStore'])->name('tuntutan-awam-store');
    Route::patch('/tuntutan-awam/{id}/update', [TuntutanController::class, 'tuntutanAwamUpdate'])->name('tuntutan-awam-update');
    Route::delete('/tuntutan-awam/{id}/delete', [TuntutanController::class, 'tuntutanAwamDelete'])->name('tuntutan-awam-delete');
    Route::patch('/tuntutan-awam/{id}/activate', [TuntutanController::class, 'tuntutanAwamActivate'])->name('tuntutan-awam-activate');


    // SIDEMENU TETAPAN TUNTUTAN MAHKAMAH
    Route::get('/mahkamah-tuntutan-list', [MahkamahController::class, 'tuntutanMahkamahList'])->name('mahkamah-tuntutan-list');
    Route::get('/mahkamah-tuntutan-add', [MahkamahController::class, 'tuntutanMahkamahAdd'])->name('mahkamah-tuntutan-add');
    Route::post('/mahkamah-tuntutan-store', [MahkamahController::class, 'tuntutanMahkamahStore'])->name('mahkamah-tuntutan-store');
    Route::patch('/mahkamah-tuntutan/{id}/update', [MahkamahController::class, 'tuntutanMahkamahUpdate'])->name('mahkamah-tuntutan-update');
    Route::delete('/mahkamah-tuntutan/{id}/delete', [MahkamahController::class, 'tuntutanMahkamahDelete'])->name('mahkamah-tuntutan-delete');
    Route::patch('/mahkamah-tuntutan/{id}/activate', [MahkamahController::class, 'tuntutanMahkamahActivate'])->name('mahkamah-tuntutan-activate');

    // SIDEMENU TETAPAN TUNTUTAN PIHAK KETIGA
    Route::get('/pihak-ketiga-tuntutan-list', [TuntutanController::class, 'pihakKetigaTuntutanList'])->name('pihak-ketiga-tuntutan-list');
    Route::get('/pihak-ketiga-tuntutan-add', [TuntutanController::class, 'pihakKetigaTuntutanAdd'])->name('pihak-ketiga-tuntutan-add');
    Route::post('/pihak-ketiga-tuntutan-store', [TuntutanController::class, 'pihakKetigaTuntutanStore'])->name('pihak-ketiga-tuntutan-store');
    Route::patch('/pihak-ketiga-tuntutan/{id}/update', [TuntutanController::class, 'pihakKetigaTuntutanUpdate'])->name('pihak-ketiga-tuntutan-update');
    Route::delete('/pihak-ketiga-tuntutan/{id}/delete', [TuntutanController::class, 'pihakKetigaTuntutanDelete'])->name('pihak-ketiga-tuntutan-delete');

    //SIDEMENU CUKAI JALAN DAN INSURANS - PEMBAHARUAN CUKAI JALAN
    Route::get('/cukai-jalan-car-list', [RoadTaxController::class, 'cukaiCarList'])->name('cukai-jalan-car-list');
    Route::get('/cukai-jalan-info-list', [RoadTaxController::class, 'cukaiJalanInfoList'])->name('cukai-jalan-info-list');
    Route::get('/cukai-jalan-edaran-list', [RoadTaxController::class, 'cukaiJalanEdaranList'])->name('cukai-jalan-edaran-list');
    Route::post('/roadtax/store', [RoadTaxController::class, 'storeRoadtax'])->name('roadtax-store');
    Route::post('/roadtax/edaran/pdf', [RoadTaxController::class, 'printEdaranPdf'])->name('roadtax-edaran-pdf');

    //SIDEMENU CUKAI JALAN DAN INSURANS - INSURANS KENDERAAN MBSP
    // Route::get('/insurans-polisi', [InsuransController::class, 'cukaiCarList'])->name('cukai-jalan-car-list');
    // Route::get('/insurans-jalan-info-list', [InsuransController::class, 'cukaiJalanInfoList'])->name('cukai-jalan-info-list');
    // Route::get('/insurans-jalan-edaran-list', [InsuransController::class, 'cukaiJalanEdaranList'])->name('cukai-jalan-edaran-list');
    // Route::post('/roadtax/store', [RoadTaxController::class, 'storeRoadtax'])->name('roadtax-store');
    // Route::post('/roadtax/edaran/pdf', [RoadTaxController::class, 'printEdaranPdf'])->name('roadtax-edaran-pdf');

    //SIDEMENU CUKAI JALAN DAN INSURANS - INSURANS KENDERAAN MBSP
    Route::get('/insurans-polisi-list', [InsuransController::class, 'polisiList'])->name('insurans-polisi-list');
    Route::post('/insurans-polisi-store', [InsuransController::class, 'polisiStore'])->name('insurans-polisi-store');
    Route::get('/insurans-data-add', [InsuransController::class, 'create'])->name('insurans-data-add');
    Route::post('/insurans-data-add-store', [InsuransController::class, 'storeKemalangan'])->name('insurans-data-add-store');
    Route::post('/insurans-staff-by-department', [InsuransController::class, 'staffByDepartment'])->name('insurans.staff-by-department');
    Route::get('/insurans-kemalangan-list', [InsuransController::class, 'kemalanganList'])->name('insurans-kemalangan-list');
    Route::patch('/insurans-kemalangan/{id}/update-status', [InsuransController::class, 'updateKemalanganStatus'])->name('insurans-kemalangan-update-status');
    Route::get('/insurans-gantirugi-list', [InsuransController::class, 'gantirugiList'])->name('insurans-gantirugi-list');
    Route::patch('/insurans-gantirugi/{id}/update-status', [InsuransController::class, 'updateGantirugiStatus'])->name('insurans-gantirugi-update-status');
    Route::get('/insurans-repair-list', [InsuransController::class, 'repairList'])->name('insurans-repair-list');
    Route::patch('/insurans-repair/{id}/update-status', [InsuransController::class, 'updateRepairStatus'])->name('insurans-repair-update-status');
    Route::get('/insurans-kemalangan/{id}/lampiran', [InsuransController::class, 'kemalanganLampiran'])->name('insurans-kemalangan-lampiran');

    //SIDEMENU CUKAI JALAN DAN INSURANS - LIABILITI AWAM
    Route::get('/liabiliti-data-add', [TuntutanController::class, 'liabilitiAwamAdd'])->name('liabiliti-data-add');
    Route::post('/liabiliti-store-liabiliti', [TuntutanController::class, 'liabilitiAwamStore'])->name('liabiliti-store-liabiliti');
    Route::get('/liabiliti-kes-list', [TuntutanController::class, 'liabilitiKesList'])->name('liabiliti-kes-list');
    Route::get('/liabiliti-kes/{id}/print', [TuntutanController::class, 'printLiabilitiKes'])->name('liabiliti-kes-print');
    Route::get('/liabiliti-kes/{id}/edit', [TuntutanController::class, 'liabilitiKesEdit'])->name('liabiliti-kes-edit');
    Route::put('/liabiliti-kes/{id}/update', [TuntutanController::class, 'liabilitiKesUpdate'])->name('liabiliti-kes-update');
    Route::get('/liabiliti-kes/{id}/file', [TuntutanController::class, 'liabilitiKesFile'])->name('liabiliti-kes-file');


    // SIDEMENU CUKAI JALAN DAN INSURANS - KES MAHKAMAH
    Route::get('/mahkamah-kes-list', [MahkamahController::class, 'mahkamahKesList'])->name('mahkamah-kes-list');
    Route::get('/mahkamah-data-add', [MahkamahController::class, 'mahkamahDataAdd'])->name('mahkamah-data-add');
    Route::post('/mahkamah-data-store', [MahkamahController::class, 'mahkamahDataStore'])->name('mahkamah-data-store');
    Route::post('/mahkamah/staff-by-department', [MahkamahController::class, 'staffByDepartment'])->name('mahkamah.staff-by-department');
    Route::get('/mahkamah-kes-list', [MahkamahController::class, 'mahkamahKesDalamProsesList'])->name('mahkamah-kes-list');
    Route::get('/mahkamah-kes-selesai-list', [MahkamahController::class, 'mahkamahKesSelesaiList'])->name('mahkamah-kes-selesai-list');
    Route::get('/mahkamah-kes/{id}/edit', [MahkamahController::class, 'mahkamahKesEdit'])->name('mahkamah-kes-edit');
    Route::put('/mahkamah-kes/{id}/update', [MahkamahController::class, 'mahkamahKesUpdate'])->name('mahkamah-kes-update');
    Route::get('/mahkamah-kes/{id}/pdf', [MahkamahController::class, 'printMahkamahKesPdf'])->name('mahkamah-kes-pdf');

    // SIDEMENU CUKAI JALAN DAN INSURANS - LUPUS KENDERAAN
    Route::get('/lupus-car-list', [LupusController::class, 'lupusCarList'])->name('lupus-car-list');  
    Route::get('/lupus-car/{id}/edit', [LupusController::class, 'lupusCarEdit'])->name('lupus-car-edit');
    Route::put('/lupus-car/{id}/update', [LupusController::class, 'lupusCarUpdate'])->name('lupus-car-update');
    Route::get('/lupus-car/add/{vehicleId}', [LupusController::class, 'create'])->name('lupus-car-add');
    Route::post('/lupus-car/store', [LupusController::class, 'store'])->name('lupus-car-store');

    //STATISTIK DAN LAPORAN -> STATISTIK
    Route::get('/statistik-data-display', [InsuransStatistikController::class, 'statistikDataDisplay'])->name('statistik-data-display');


    //PENYELENGGARAAN KENDERAAN
    Route::get('/penyelenggaraan-list', [PenyelenggaraanController::class, 'list'])->name('penyelenggaraan-list');
    Route::get('/penyelenggaraan-add', [PenyelenggaraanController::class, 'create'])->name('penyelenggaraan-add');
    Route::post('/penyelenggaraan-store', [PenyelenggaraanController::class, 'store'])->name('penyelenggaraan-store');
    Route::get('/penyelenggaraan/{id}/edit', [PenyelenggaraanController::class, 'edit'])->name('penyelenggaraan-edit');
    Route::put('/penyelenggaraan/{id}/update', [PenyelenggaraanController::class, 'update'])->name('penyelenggaraan-update');
    Route::post('/penyelenggaraan/{id}/delete', [PenyelenggaraanController::class, 'delete'])->name('penyelenggaraan-delete');

    //MOD UJIAN (SUPERADMIN) - TUKAR AKAUN UNTUK TESTING
    Route::post('/impersonate/start', [ImpersonateController::class, 'start'])->name('impersonate-start');
    Route::post('/impersonate/stop', [ImpersonateController::class, 'stop'])->name('impersonate-stop');

});






















