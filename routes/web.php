<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\TagController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/', [ContactController::class, 'index']);
Route::post('/contacts/confirm', [ContactController::class, 'confirm'])->name('contacts.confirm');
Route::post('/contacts', [ContactController::class, 'store'])->name('contacts.store');
Route::get('/contact/thanks', [ContactController::class, 'thanks'])->name('contact.thanks');

Route::middleware('auth')->group(function () {
    Route::get('/admin', [AdminController::class, 'index'])->name('admin');
    Route::get('/admin/contacts/{contact}', [AdminController::class, 'show']);
    Route::delete('/admin/contacts/{contact}', [AdminController::class, 'destroy']);
    Route::resource('/admin/tags', TagController::class)->only(['store', 'edit', 'update', 'destroy']);
});
