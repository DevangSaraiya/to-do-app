<?php

use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

Route::get('/', [TaskController::class, 'index'])->name('task.index');
Route::post('/tasks', [TaskController::class, 'store'])->name('task.store');
Route::patch('task/{task}', [TaskController::class, 'update'])->name('task.update');
Route::patch('task-done/{task}', [TaskController::class, 'completeTask'])->name('task.done');
Route::get('task/{task}', [TaskController::class, 'show'])->name('task.show');

