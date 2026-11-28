<?php

use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\CandidateController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ElectionController;
use App\Http\Controllers\Admin\ElectionStatusController;
use App\Http\Controllers\Admin\InstitutionController;
use App\Http\Controllers\Admin\InstitutionStructureController;
use App\Http\Controllers\Admin\PositionController;
use App\Http\Controllers\Admin\ResultController as AdminResultController;
use App\Http\Controllers\Admin\SiteSettingController;
use App\Http\Controllers\Admin\SponsorController;
use App\Http\Controllers\Admin\VoterController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicResultsController;
use App\Http\Controllers\Voter\BallotController;
use App\Http\Controllers\Voter\VoterAuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', LandingController::class)->name('home');
Route::post('/lang/{locale}', [LanguageController::class, 'update'])
    ->whereIn('locale', ['sw', 'en'])
    ->name('language.update');
Route::get('/matokeo/{election}', [PublicResultsController::class, 'show'])->name('results.show');

Route::get('/kura', [VoterAuthController::class, 'create'])->name('voter.login');
Route::post('/kura', [VoterAuthController::class, 'store'])->name('voter.login.store');
Route::get('/kura/umeshapiga', [VoterAuthController::class, 'alreadyVoted'])->name('voter.already-voted');
Route::post('/kura/toka', [VoterAuthController::class, 'logout'])->name('voter.logout');
Route::get('/kura/imepokelewa', [BallotController::class, 'success'])->name('voter.success');

Route::middleware('voter')->prefix('kura')->name('voter.')->group(function () {
    Route::get('/piga', [BallotController::class, 'show'])->name('ballot');
    Route::post('/hakiki', [BallotController::class, 'review'])->name('review');
    Route::post('/hariri', [BallotController::class, 'restore'])->name('ballot.restore');
    Route::post('/tuma', [BallotController::class, 'submit'])->name('submit');
});

Route::get('/dashboard', fn () => redirect()->route('admin.dashboard'))
    ->middleware(['auth', 'admin'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', DashboardController::class)->name('dashboard');
    Route::resource('chaguzi', ElectionController::class)
        ->parameters(['chaguzi' => 'election'])
        ->names('elections')
        ->except(['destroy']);
    Route::delete('/chaguzi/{election}', [ElectionController::class, 'destroy'])->name('elections.destroy');

    Route::post('/chaguzi/{election}/nafasi', [PositionController::class, 'store'])->name('positions.store');
    Route::put('/chaguzi/{election}/nafasi/{position}', [PositionController::class, 'update'])->name('positions.update');
    Route::delete('/chaguzi/{election}/nafasi/{position}', [PositionController::class, 'destroy'])->name('positions.destroy');
    Route::post('/chaguzi/{election}/nafasi/{position}/wagombea', [CandidateController::class, 'store'])->name('candidates.store');
    Route::put('/chaguzi/{election}/nafasi/{position}/wagombea/{candidate}', [CandidateController::class, 'update'])->name('candidates.update');
    Route::delete('/chaguzi/{election}/nafasi/{position}/wagombea/{candidate}', [CandidateController::class, 'destroy'])->name('candidates.destroy');
    Route::get('/wagombea', [CandidateController::class, 'index'])->name('candidates.index');
    Route::get('/wagombea/{candidate}', [CandidateController::class, 'show'])->name('candidates.show');
    Route::post('/wagombea/{candidate}/idhinisha', [CandidateController::class, 'approve'])->name('candidates.approve');
    Route::post('/wagombea/{candidate}/kataa', [CandidateController::class, 'reject'])->name('candidates.reject');
    Route::post('/wagombea/{candidate}/jiondoa', [CandidateController::class, 'withdraw'])->name('candidates.withdraw');

    Route::post('/chaguzi/{election}/wapiga-kura/ingiza', [VoterController::class, 'import'])->name('voters.import');
    Route::get('/wapiga-kura', [VoterController::class, 'index'])->name('voters.index');
    Route::post('/wapiga-kura', [VoterController::class, 'store'])->name('voters.store');
    Route::put('/wapiga-kura/{voter}', [VoterController::class, 'update'])->name('voters.update');
    Route::post('/wapiga-kura/{voter}/thibitisha', [VoterController::class, 'verify'])->name('voters.verify');
    Route::post('/wapiga-kura/{voter}/hali', [VoterController::class, 'active'])->name('voters.active');
    Route::post('/wapiga-kura/{voter}/msimbo-mpya', [VoterController::class, 'regenerate'])->name('voters.regenerate');
    Route::post('/wapiga-kura/wingi', [VoterController::class, 'bulk'])->name('voters.bulk');
    Route::get('/wapiga-kura/kiolezo.csv', [VoterController::class, 'template'])->name('voters.template');
    Route::get('/wapiga-kura/ingiza/makosa.csv', [VoterController::class, 'downloadImportErrors'])->name('voters.import-errors');
    Route::post('/chaguzi/{election}/fungua', [ElectionStatusController::class, 'open'])->name('elections.open');
    Route::post('/chaguzi/{election}/funga', [ElectionStatusController::class, 'close'])->name('elections.close');

    Route::get('/matokeo', [AdminResultController::class, 'index'])->name('results.index');
    Route::get('/matokeo/{election}', [AdminResultController::class, 'show'])->name('results.show');
    Route::get('/taasisi', [InstitutionController::class, 'edit'])->name('institution.edit');
    Route::put('/taasisi', [InstitutionController::class, 'update'])->name('institution.update');
    Route::get('/taasisi/miundo', [InstitutionStructureController::class, 'index'])->name('institution.structure');
    Route::post('/taasisi/miundo/{resource}', [InstitutionStructureController::class, 'store'])->name('institution.structure.store');
    Route::put('/taasisi/miundo/{resource}/{record}', [InstitutionStructureController::class, 'update'])->name('institution.structure.update');
    Route::delete('/taasisi/miundo/{resource}/{record}', [InstitutionStructureController::class, 'destroy'])->name('institution.structure.destroy');
    Route::middleware('super-admin')->group(function () {
        Route::get('/mipangilio', [SiteSettingController::class, 'edit'])->name('settings.edit');
        Route::put('/mipangilio', [SiteSettingController::class, 'update'])->name('settings.update');
        Route::get('/ukaguzi', [AuditLogController::class, 'index'])->name('audit.index');
        Route::resource('washirika', SponsorController::class)
            ->only(['index', 'store', 'update', 'destroy'])
            ->parameters(['washirika' => 'sponsor'])
            ->names('sponsors');
    });
});

require __DIR__.'/auth.php';
