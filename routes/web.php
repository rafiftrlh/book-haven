<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OfficerController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\BorrowingController;

use Illuminate\Support\Facades\Route;
use Mpdf\Mpdf;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/


Route::get('/', function () {
    return redirect('/login');
});

// Route::view('/login', 'login')->name('login');
// Route::view('/register', 'register')->name('register');
// Route::view('/bookcatalog', '__bookcatalog')->name('bookcatalog');




// web.php



// metode nya get lalu masukkan namespace AuthController 
// attribute name merupakan penamaan dari route yang kita buat
// kita tinggal panggil fungsi route(name) pada layout atau controller
Route::get('login', [AuthController::class, 'index'])->name('login');
Route::get('register', [AuthController::class, 'register'])->name('register');

// Route::get('register', [AuthController::class, 'register'])->name('register');
Route::post('proses_login', [AuthController::class, 'proses_login'])->name('proses_login');
Route::get('logout', [AuthController::class, 'logout'])->name('logout');
// Route::get('logout', [AuthController::class, 'logout'])->name('logout');

Route::post('proses_register', [AuthController::class, 'proses_register'])->name('proses_register');

// kita atur juga untuk middleware menggunakan group pada routing
// didalamnya terdapat group untuk mengecek kondisi login
// jika user yang login merupakan admin maka akan diarahkan ke AdminController
// jika user yang login merupakan user biasa maka akan diarahkan ke UserController
Route::group(['middleware' => ['auth']], function () {
    Route::group(['middleware' => ['cek_login:1']], function () {
        Route::get('admin', [AdminController::class, 'index'])->name('admin.home');
        Route::get('admin/users', [AdminController::class, 'users'])->name('admin.users');
        Route::get('admin/books', [AdminController::class, 'books'])->name('admin.books');
        Route::get('admin/books-create', [AdminController::class, 'createBook'])->name('admin.books.create');
        Route::get('admin/categories', [AdminController::class, 'categories'])->name('admin.categories');
        Route::get('admin/authors', [AdminController::class, 'authors'])->name('admin.authors');

        // Borrowings
        Route::get('admin/borrowings', [AdminController::class, 'borrowings'])->name('admin.borrowings');
        Route::get('admin/req-approvals', [AdminController::class, 'reqApprovals'])->name('admin.req_approvals');
        Route::get('admin/being-borrowings', [AdminController::class, 'beingBorrowings'])->name('admin.being_borrowings');
        Route::get('admin/late-returned', [AdminController::class, 'lateReturned'])->name('admin.late_returned');
        Route::get('admin/pdf_borrowing', [AdminController::class, 'exportPdf'])->name('admin.exportPdf');

        // Fines
        Route::get('admin/fines', [AdminController::class, 'fines'])->name('admin.fines');
        Route::get('/admin/fines-late', [AdminController::class, 'allLateFines'])->name('admin.allLateFines');
        Route::get('/admin/fines-broken', [AdminController::class, 'allBrokenFines'])->name('admin.allBrokenFines');
        Route::get('/admin/fines-lost', [AdminController::class, 'allLostFines'])->name('admin.allLostFines');
        Route::get('/admin/fines-late-and-broken', [AdminController::class, 'allLateAndBrokenFines'])->name('admin.allLateAndBrokenFines');

        Route::get('admin/monthly-borrowing-data', [AdminController::class, 'monthlyBorrowingData']);
    });

    Route::group(['middleware' => ['cek_login:2']], function () {
        // Home
        Route::get('officer', [OfficerController::class, 'index'])->name("officer.home");
        Route::get('officer/add-category', [OfficerController::class, 'categories'])->name('officer.add_category');
        Route::get('officer/add_author', [OfficerController::class, 'authors'])->name('officer.add_author');

        // Books
        Route::get('officer/data_buku', [OfficerController::class, 'books'])->name('officer.data_buku');
        Route::get('officer/books', [OfficerController::class, 'books'])->name('officer.books');
        Route::get('officer/books_create', [OfficerController::class, 'createBook'])->name('officer.books_create');

        // Borrowing
        Route::get('officer/confirm_peminjaman', [OfficerController::class, 'borrowings'])->name('officer.confirm_peminjaman');
        Route::get('officer/req-approvals', [OfficerController::class, 'reqApprovals'])->name('officer.req_approvals');
        Route::get('officer/being-borrowings', [OfficerController::class, 'beingBorrowings'])->name('officer.being_borrowings');
        Route::get('officer/late-returned', [OfficerController::class, 'lateReturned'])->name('officer.late_returned');

        // Fines
        Route::get('officer/data_fines', [OfficerController::class, 'fines'])->name('officer.data_fines');

        Route::get('officer/pdf_borrowing', [OfficerController::class, 'exportPdf'])->name('officer.exportPdf');

        // web.php
        Route::get('officer/monthly-borrowing-data', [OfficerController::class, 'monthlyBorrowingData']);

    });

    Route::group(['middleware' => ['cek_login:3']], function () {
        Route::get('home', [CustomerController::class, 'index'])->name('customer.home');
        Route::view('notification', 'roles.customer.index')->name('customer.notification');
        Route::get('/filter-books-by-category', [UserController::class, 'filterByCategory'])->name('filter.books.by.category');
        Route::get('detailbuku', [UserController::class, 'Showdetailbuku'])->name('customer.detail');

        Route::get('book-catalog', [CustomerController::class, 'allBook'])->name('customer.bookcatalog');
        Route::get('/profile', [ProfileController::class, 'index'])->name('customer.profile');
        Route::get('/borrowed-books', [ProfileController::class, 'showBorrowedBooksPage'])->name('borrowed_books_page');
        Route::get('/borrowing-history', [ProfileController::class, 'showHistoryBorrowed'])->name('borrowing_history');
        Route::get('/bookmarks', [ProfileController::class, 'showBookmarkedBooks'])->name('customer.bookmarks');
        Route::get('/notification', [NotificationController::class, 'index'])->name('customer.notification')->middleware('auth');
        Route::delete('/notifications/{id}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
        Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
        Route::get('/get-notifications', [NotificationController::class, 'UnreadNotif'])->name('get.notifications');
        // routes/web.php
Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
Route::post('/profile/update', [ProfileController::class, 'update'])->name('profile.update');

    });
});



// Route::get('/dashboard', [AdminController::class, 'show_user'])->name('dashboard.admin');
// Route::view('/home', 'roles.customer.index')->name('dashboard.customer');
