<?php

use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

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

Route::middleware('guest')->get('/', function () {
    return view('auth.login');
});

Auth::routes(['register' => false]);

Route::middleware('auth')->group(function () {
    // Application Routes
    Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
    Route::resource('invoices',\App\Http\Controllers\InvoicesController::class);
    Route::resource('sections',\App\Http\Controllers\SectionsController::class);
    Route::resource('products',\App\Http\Controllers\ProductsController::class);
    Route::resource('InvoiceAttachments', \App\Http\Controllers\InvoiceAttachmentsController::class);
    Route::get('/section/{id}',[\App\Http\Controllers\InvoicesController::class,'getproducts'])->name('getproducts');
    Route::get('/InvoicesDetails/{id}',[\App\Http\Controllers\InvoicesDetailsController::class,'show'])->name('details');
    Route::get('/edit_invoice/{id}', [\App\Http\Controllers\InvoicesController::class,'edit']);
    Route::get('/download/{invoice_number}/{file_name}', [\App\Http\Controllers\InvoicesDetailsController::class,'get_file']);

    Route::get('/View_file/{invoice_number}/{file_name}', [\App\Http\Controllers\InvoicesDetailsController::class,'open_file']);
    Route::delete('delete_file', [\App\Http\Controllers\InvoicesDetailsController::class,'destroy'])->name('delete_file');
    Route::get('/Status_show/{id}',[\App\Http\Controllers\InvoicesController::class,'show'])->name('Status_show');
    Route::post('/Status_Update/{id}', [\App\Http\Controllers\InvoicesController::class,'Status_Update'])->name('Status_Update');

    Route::resource('Archive', \App\Http\Controllers\InvoicesArchiveController::class);

    Route::get('Invoice_Paid',[\App\Http\Controllers\InvoicesController::class,'Invoice_Paid']);

    Route::get('Invoice_UnPaid',[\App\Http\Controllers\InvoicesController::class,'Invoice_unPaid']);

    Route::get('Invoice_Partial',[\App\Http\Controllers\InvoicesController::class,'Invoice_Partial']);

    Route::get('Print_invoice/{id}',[\App\Http\Controllers\InvoicesController::class,'Print_invoice']);

    Route::get('export_invoices', [\App\Http\Controllers\InvoicesController::class, 'export']);

    // Our resource routes
    Route::resource('roles', RoleController::class);
    Route::resource('users', UserController::class);

    //Reports, search and notifications
    Route::get('invoices_report', [\App\Http\Controllers\Invoices_Report::class,'index']);

    Route::post('Search_invoices', [\App\Http\Controllers\Invoices_Report::class,'Search_invoices']);

    Route::get('customers_report', [\App\Http\Controllers\Customers_Report::class,'index']);

    Route::post('Search_customers', [\App\Http\Controllers\Customers_Report::class,'Search_customers']);

    Route::get('/MarkAsRead_all',[\App\Http\Controllers\InvoicesController::class,'MarkAsRead_all'])->name('MarkAsRead_all');
    Route::get('/MarkAsRead/{notify_id}/{inv_id}',[\App\Http\Controllers\InvoicesController::class,'markAsRead'])->name('invoice.markAsRead');
});
