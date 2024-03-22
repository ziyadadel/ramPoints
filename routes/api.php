<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\KindController;
use App\Http\Controllers\GeerController;
use App\Http\Controllers\CarController;
use App\Http\Controllers\transactionController;
use App\Http\Controllers\branchController;
use App\Http\Controllers\VoutcherPlanController;
use App\Http\Controllers\UserVoutcherController;
use App\Http\Controllers\CompanyDetailsController;
use App\Http\Controllers\MagazinesController;
use App\Http\Controllers\OffersController;
use App\Http\Controllers\ForgetPasswordController;

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
Route::post('/user/register', [UserController::class, 'register']);
Route::post('/admin/login', [UserController::class, 'login']);
Route::post('/user/login', [UserController::class, 'login']);
Route::get('/voutcherplan', [VoutcherPlanController::class, 'index']);
Route::get('/branch', [branchController::class, 'index']);


Route::group(['middleware' => ['api','checkAdmin'],'prefix' => 'auth','namespace'=>'Admin'], function () {

    Route::post('/admin/logout', [UserController::class, 'logout']);
    
    Route::get('/user', [UserController::class, 'allUsers']);
    Route::post('/user/searchByDate', [UserController::class, 'searchByDate']);
    Route::post('/refresh', [AdminController::class, 'refresh']);
    
    Route::get('/trans', [transactionController::class, 'index']);
    Route::get('/trans/{transaction_number}/{branch_id}', [transactionController::class, 'show']);
    Route::post('/trans', [transactionController::class, 'store']);
    Route::put('/trans/{id}', [transactionController::class, 'update']);
    Route::delete('/trans/{id}', [transactionController::class, 'destroy']);
    Route::post('/trans/searchByRecordDate', [transactionController::class, 'searchByRecordDate']);
    Route::post('/trans/searchByAll', [transactionController::class, 'searchByAll']);
    Route::get('/trans/currentDate', [transactionController::class, 'currentDate']);
    Route::post('/trans/bulktrans', [transactionController::class, 'reserveBulk']);
    
    
    Route::get('/branch/{id}', [branchController::class, 'show']);
    Route::post('/branch', [branchController::class, 'store']);
    Route::put('/branch/{id}', [branchController::class, 'update']);
    Route::delete('/branch/{id}', [branchController::class, 'destroy']);
    
    Route::get('/voutcherplan', [VoutcherPlanController::class, 'index']);
    Route::get('/voutcherplan/{id}', [VoutcherPlanController::class, 'show']);
    Route::post('/voutcherplan', [VoutcherPlanController::class, 'store']);
    Route::put('/voutcherplan/{id}', [VoutcherPlanController::class, 'update']);
    Route::delete('/voutcherplan/{id}', [VoutcherPlanController::class, 'destroy']);
    
    Route::get('/uservoutcher', [UserVoutcherController::class, 'index']);
    Route::get('/uservoutcher/{id}', [UserVoutcherController::class, 'show']);
    Route::put('/uservoutcher/{id}', [UserVoutcherController::class, 'update']);
    Route::delete('/uservoutcher/{id}', [UserVoutcherController::class, 'destroy']);
    Route::post('/updateStatusAndSoldDate/{id}', [UserVoutcherController::class, 'updateStatusAndSoldDate']);
    Route::post('/searchByCustomerPhoneNumber', [UserVoutcherController::class, 'searchByCustomerPhoneNumber']);
    Route::post('/uservoutcher/updatesoldandstatus/{id}', [UserVoutcherController::class, 'updateSoldDate']);
    Route::post('/uservoutcher/searchByDate', [UserVoutcherController::class, 'searchByDate']);
    Route::post('/uservoutcher/updateVoucherNumber/{id}', [UserVoutcherController::class, 'updateVoucherNumber']);
    
    
    Route::get('/companydetails', [CompanyDetailsController::class, 'index']);
    Route::post('/companydetails', [CompanyDetailsController::class, 'store']);
    Route::get('/companydetails/{id}', [CompanyDetailsController::class, 'show']);
    Route::put('/companydetails/{id}', [CompanyDetailsController::class, 'update']);
    Route::delete('/companydetails/{id}', [CompanyDetailsController::class, 'destroy']);
    
    
    Route::get('/offer', [OffersController::class, 'index']);
    Route::get('/offer/{id}', [OffersController::class, 'show']);
    Route::post('/offer', [OffersController::class, 'store']);
    Route::put('/offer/{id}', [OffersController::class, 'update']);
    Route::delete('/offer/{id}', [OffersController::class, 'destroy']);
    
    Route::post('/changeAdminPassword', [UserController::class, 'changePassword']);
});

Route::post('/user/forgotPassword', [ForgetPasswordController::class, 'sendEmail']);
Route::post('/user/verify', [UserController::class, 'verify']);

Route::group(['middleware' => ['api','checkUser'],'prefix' => 'auth','namespace'=>'User'], function () {
    
    
    Route::post('/user/logout', [UserController::class, 'logout']);
    Route::get('/getuser', [UserController::class, 'getUser']);
    Route::post('/changeUserPassword', [UserController::class, 'changePassword']);
    
    Route::get('/uservoutcher', [UserVoutcherController::class, 'index']);
    Route::post('/uservoutcher', [UserVoutcherController::class, 'store']);
    Route::get('/uservoutcher/{id}', [UserVoutcherController::class, 'show']);
    Route::get('/uservoutcher/getvouchers/{user_id}', [UserVoutcherController::class, 'allUserVoucher']);
    Route::post('/trans/updateTransaction', [transactionController::class, 'updateTransaction']);
    
});
