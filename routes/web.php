<?php

use App\Http\Controllers\ButtonController;
use App\Http\Controllers\ContactsController;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::middleware(['auth'])->name('contacts.')->prefix('contacts')->group(function () {
    Route::get('/', [ContactsController::class, 'index'])->name('list');
    Route::get('/create', [ContactsController::class, 'create'])->name('create');
    Route::post('/', [ContactsController::class, 'store'])->name('store');
    Route::delete('/{contact}', [ContactsController::class, 'destroy'])->name('destroy');
    Route::get('/{contact}/edit', [ContactsController::class, 'edit'])->name('edit');
    Route::put('/{contact}', [ContactsController::class, 'update'])->name('update');
    Route::post('/import', [ContactsController::class, 'import'])->name('import');
});

Route::post('/button', [ButtonController::class, 'click'])->name('button.click');

require __DIR__.'/auth.php';
