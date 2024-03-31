<?php

use App\Http\Controllers\userController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

//public routes
Route::post('/register', [userController::class, 'register']);

Route::post('/login', [userController::class, 'login']);

Route::post('/test', [userController::class, 'test']);

//protected routes
Route::middleware('auth:sanctum')->post('/logout', [userController::class, 'log_out']);

Route::middleware(['auth:sanctum'])->get('/home', [userController::class, 'home']);

Route::middleware('auth:sanctum')->post('/emprunter', [userController::class, 'emprunter']);

Route::middleware('auth:sanctum')->post('/desemprunter', [userController::class, 'desemprunter']);

Route::middleware('auth:sanctum')->put('/updateUserdata',[userController::class,'updateUserdata']);

Route::middleware('auth:sanctum')->put('/updatePassword',[userController::class,'updatePassword']);

Route::middleware('auth:sanctum')->get('/myData', [UserController::class, 'myData']);

Route::middleware('auth:sanctum')->get('/getLivres', [UserController::class, 'getLivres']);