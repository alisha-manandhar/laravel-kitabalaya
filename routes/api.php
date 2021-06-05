<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\OrderController;


Route::post('/login',[LoginController::class,'login']);
Route::post('/register',[LoginController::class,'register']);

Route::middleware('auth:api')->post('/logout',[LoginController::class,'logout']);
Route::middleware('auth:api')->put('/updateContact',[LoginController::class,'updateContact']);
Route::middleware('auth:api')->put('/updateEmail',[LoginController::class,'updateEmail']);
Route::middleware('auth:api')->put('/updatePassword',[LoginController::class,'changePassword']);
Route::middleware('auth:api')->get('/me',[LoginController::class,'show']);
Route::middleware('auth:api')->post('/updateProfilePicture',[LoginController::class,'updateProfilePicture']);

Route::middleware('auth:api')->apiresource('/books',BookController::class);

Route::middleware('auth:api')->get('/getBuyers',[OrderController::class,'getBuyers']);
Route::middleware('auth:api')->post('/order',[OrderController::class,'storeOrder']);
Route::middleware('auth:api')->put('/order/updateBuyer/{id}',[OrderController::class,'updateBuyer']);
Route::middleware('auth:api')->put('/order/updateSeller/{id}',[OrderController::class,'updateSeller']);
Route::middleware('auth:api')->put('/cancel-order/{id}',[OrderController::class,'cancelOrder']);
Route::middleware('auth:api')->put('/order/:id',[OrderController::class,'confirmSold']);
Route::middleware('auth:api')->get('/getSellerResponse',[OrderController::class,'getSellerResponse']);
Route::middleware('auth:api')->get('/getConfirmList',[OrderController::class,'getConfirmList']);
Route::middleware('auth:api')->put('/confirmSold/{id}',[OrderController::class,'confirmSold']);
Route::middleware('auth:api')->get('/getAddedBooks',[OrderController::class,'getAddedBooks']);

Route::middleware('auth:api')->post('/update-book-image/{id}',[OrderController::class,'updateBookImage']);










