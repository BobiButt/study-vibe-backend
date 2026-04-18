<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\SocialAuthController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\API\ProgramController;
use App\Http\Controllers\API\SubjectController;
use App\Http\Controllers\API\NoteController;
use App\Http\Controllers\API\TopicController;
use App\Http\Controllers\API\UserProfileController;
use App\Http\Controllers\API\StudyProgressController;
use App\Http\Controllers\API\SchoolController;
use App\Http\Controllers\API\CollegeController;
use App\Http\Controllers\API\UniversityController;
use App\Http\Controllers\API\AINoteController;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('auth')->group(function () {
    Route::get('/{provider}/redirect', [SocialAuthController::class, 'redirect']);
    Route::get('/{provider}/callback', [SocialAuthController::class, 'callback']);
});

Route::post('/register', [RegisteredUserController::class, 'store']);
Route::post('/login', [AuthenticatedSessionController::class, 'store']);
Route::post('/forgot-password', [\App\Http\Controllers\Auth\PasswordResetLinkController::class, 'store']);
Route::post('/reset-password', [\App\Http\Controllers\Auth\NewPasswordController::class, 'store']);

Route::get('/email/verify/{id}/{hash}', [VerifyEmailController::class, 'verify'])->name('verification.verify');
Route::post('/email/verification-notification', [VerifyEmailController::class, 'resend'])->middleware('throttle:6,1');
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy']);
Route::get('/users/{id}/profile', [UserProfileController::class, 'getPublicProfile']);

Route::prefix('v1')->group(function () {
    
    // ==================== PUBLIC ROUTES ====================
    
    // Program routes
    Route::get('/programs', [ProgramController::class, 'index']);
    Route::get('/programs/university', [ProgramController::class, 'getUniversityPrograms']);
    Route::get('/programs/college', [ProgramController::class, 'getCollegePrograms']);
    Route::get('/programs/school', [ProgramController::class, 'getSchoolPrograms']);
    Route::get('/programs/{id}', [ProgramController::class, 'show']);
    Route::get('/programs/{id}/subjects', [ProgramController::class, 'getSubjects']);
    
    // Subject routes
    Route::get('/subjects', [SubjectController::class, 'index']);
    Route::get('/subjects/{id}', [SubjectController::class, 'show']);
    Route::get('/subjects/{id}/topics', [SubjectController::class, 'getTopics']);
    Route::get('/subjects/{id}/notes', [SubjectController::class, 'getNotes']);
    //  Route::get('/subjects/{id}/topics', [SubjectController::class, 'getTopics']);

    // Note routes (public)
    Route::get('/notes/top', [NoteController::class, 'getTopPublicNotes']);

    // Topic routes
    Route::get('/topics', [TopicController::class, 'index']);
    Route::get('/topics/{id}', [TopicController::class, 'show']);
    Route::get('/topics/{id}/notes', [TopicController::class, 'getNotes']);

    // School routes (full)
    Route::prefix('school')->group(function () {
        Route::get('/subjects', [SchoolController::class, 'getSubjects']);
        Route::get('/grades', [SchoolController::class, 'getGradeLevels']);
        Route::get('/notes', [SchoolController::class, 'getNotes']);
    });

    // College routes (full)
    Route::prefix('college')->group(function () {
        Route::get('/streams', [CollegeController::class, 'getStreams']);
        Route::get('/streams/{stream}/subjects', [CollegeController::class, 'getSubjectsByStream']);
        Route::get('/notes', [CollegeController::class, 'getNotes']);
    });

    // University routes
    Route::prefix('university')->group(function () {
        Route::get('/notes', [UniversityController::class, 'getNotes']);
    });

    // AI Models
    Route::get('/ai-models', [AINoteController::class, 'getModels']);

    // ==================== AUTHENTICATED ROUTES ====================
    Route::middleware('auth:sanctum')->group(function () {
        
        // Note Reading
        Route::get('/notes', [NoteController::class, 'index']);
        Route::get('/notes/{id}', [NoteController::class, 'show']);

        // Note CRUD
        Route::post('/notes', [NoteController::class, 'store']);
        Route::put('/notes/{id}', [NoteController::class, 'update']);
        Route::delete('/notes/{id}', [NoteController::class, 'destroy']);
        
        Route::patch('/notes/{id}/toggle-visibility', [NoteController::class, 'toggleVisibility']);
        Route::post('/notes/{id}/reviews', [NoteController::class, 'addReview']);
        Route::post('/notes/{id}/bookmark', [NoteController::class, 'toggleBookmark']);
        
        Route::get('/my/notes', [NoteController::class, 'myNotes']);
        Route::get('/my/bookmarks', [NoteController::class, 'bookmarkedNotes']);

        Route::delete('/reviews/{review}', [App\Http\Controllers\API\NoteController::class, 'deleteReview']);

        // Profile routes
        Route::get('/profile', [UserProfileController::class, 'show']);
        Route::put('/profile', [UserProfileController::class, 'update']);
        Route::get('/profile/stats', [UserProfileController::class, 'getStats']);
        
        // Study routes
        Route::get('/study/overview', [StudyProgressController::class, 'overview']);
        Route::post('/study/topics/{topicId}/progress', [StudyProgressController::class, 'updateTopicProgress']);
        Route::post('/study/log-time', [StudyProgressController::class, 'logStudyTime']);
        Route::get('/study/calendar', [StudyProgressController::class, 'getStudyCalendar']);
        Route::get('/study/streak', [StudyProgressController::class, 'getStreak']);
        
        // AI Notes
        Route::post('/ai-notes', [AINoteController::class, 'store']);

        Route::post('/subjects', [SubjectController::class, 'store']);
        Route::put('/subjects/{id}', [SubjectController::class, 'update']);
        Route::delete('/subjects/{id}', [SubjectController::class, 'destroy']);
    });
});