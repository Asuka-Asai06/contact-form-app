<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\TagController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ContactController::class, 'index'])->name('contacts.index');
Route::post('/contacts/confirm', [ContactController::class, 'confirm'])->name('contacts.confirm');
Route::post('/contacts', [ContactController::class, 'store'])->name('contacts.store');
Route::get('/contact/thanks', [ContactController::class, 'thanks'])->name('contacts.thanks');

Route::middleware('auth')
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::get('/', [AdminController::class, 'index'])
            ->name('index');

        Route::get('/contacts/{contact}', [AdminController::class, 'show'])
            ->name('contacts.show');

        Route::delete('/contacts/{contact}', [AdminController::class, 'destroy'])
            ->name('contacts.destroy');

        Route::resource('tags', TagController::class)
            ->only([
                'store',
                'edit',
                'update',
                'destroy',
            ]);
    });
