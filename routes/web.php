<?php

use Ace\Application;

/** @var \Ace\Router $router */

use App\Controllers\Admin\AdminAttendeeController;
use App\Controllers\Admin\AdminBroadcastController;
use App\Controllers\Admin\AdminController;
use App\Controllers\Admin\AdminEventController;
use App\Controllers\Admin\AdminPayoutController;
use App\Controllers\Admin\AdminSettingsController;
use App\Controllers\Admin\AdminSponsorController;
use App\Controllers\Admin\AdminSupportController;
use App\Controllers\Admin\AdminTeamController;
use App\Controllers\Admin\AdminUserController;
use App\Controllers\Team\TeamController;
use App\Controllers\AuthController;
use App\Controllers\CheckoutController;
use App\Controllers\EventController;
use App\Controllers\HomeController;
use App\Controllers\Host\HostController;
use App\Controllers\Host\HostEventController;
use App\Controllers\Host\HostReferralController;
use App\Controllers\Host\HostSettingsController;
use App\Controllers\Host\HostTicketController;
use App\Controllers\Host\PromoCodeController;
use App\Controllers\ProfileController;
use App\Controllers\TicketController;
use App\Controllers\LayawayController;
use App\Controllers\EventActivityController;
use App\Controllers\Host\HostActivityController;
use App\Controllers\UserPassportController;

// ==================================================
// APPLICATION WEB ROUTES
// ==================================================

// Branded Ticket Admission QR Code Generator
$router->get('/ticket/qr/{code}', [TicketController::class, 'qrCode']);
$router->get('/pass/{code}', [TicketController::class, 'viewPass']);
$router->post('/pass/{code}/unlock', [TicketController::class, 'unlockPass']);
$router->post('/pass/{code}/resend-pin', [TicketController::class, 'resendPinAjax']);

// Dynamic PWA Manifest Route
$router->get('/manifest.json', function () {
    header('Content-Type: application/manifest+json; charset=utf-8');
    echo json_encode([
        'name' => site_setting('site_title', 'Eventlyy') . ' - Event Ticketing & Management',
        'short_name' => site_setting('site_title', 'Eventlyy'),
        'description' => site_setting('site_description', 'Discover, book, manage and verify event tickets with Eventlyy.'),
        'start_url' => '/',
        'scope' => '/',
        'display' => 'standalone',
        'orientation' => 'portrait-primary',
        'background_color' => '#ffffff',
        'theme_color' => function_exists('theme_primary_color') ? theme_primary_color() : '#108a55',
        'icons' => [
            ['src' => '/assets/img/branding/pwa-icon-192.png', 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'any'],
            ['src' => '/assets/img/branding/pwa-icon-512.png', 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any'],
            ['src' => '/assets/img/branding/pwa-icon-maskable.png', 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'maskable']
        ]
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    exit;
});

// Home Route
$router->get('/', [HomeController::class, 'index']);
$router->get('/about', [HomeController::class, 'about']);
$router->get('/support', [HomeController::class, 'support']);
$router->post('/support', [HomeController::class, 'postSupport']);
$router->get('/profile', [ProfileController::class, 'profile']);
$router->post('/profile', [ProfileController::class, 'updateProfile']);
$router->post('/profile/password', [ProfileController::class, 'changePassword']);
$router->post('/profile/delete-account', [ProfileController::class, 'deleteAccount']);
$router->post('/profile/ticket/transfer', [ProfileController::class, 'transferTicket']);
$router->get('/event/{id}', [EventController::class, 'event']);
$router->get('/e/{slug}', [EventController::class, 'eventBySlug']);
$router->get('/f/{id}', [EventController::class, 'flyerPage']);
$router->get('/f/{id}/checkout', [CheckoutController::class, 'flyerCheckout']);
$router->post('/f/{id}/checkout', [CheckoutController::class, 'processCheckout']);
$router->get('/flyer/{id}', [EventController::class, 'flyerPage']);
$router->get('/flyer/{id}/checkout', [CheckoutController::class, 'flyerCheckout']);
$router->post('/flyer/{id}/checkout', [CheckoutController::class, 'processCheckout']);
$router->get('/event/{id}/feedback', [EventController::class, 'feedbackForm']);
$router->post('/event/{id}/feedback', [EventController::class, 'submitFeedback']);
$router->get('/event/{id}/calendar.ics', [EventController::class, 'calendarIcs']);

// Event Activities, Contests & Community Hub
$router->get('/event/{id}/activities', [EventActivityController::class, 'hub']);
$router->post('/event/{id}/vote/{poll_id}', [EventActivityController::class, 'vote']);
$router->post('/event/{id}/trivia/submit', [EventActivityController::class, 'submitTrivia']);
$router->post('/event/{id}/request', [EventActivityController::class, 'submitRequest']);
$router->post('/event/{id}/request/{req_id}/upvote', [EventActivityController::class, 'upvoteRequest']);
$router->post('/event/{id}/community', [EventActivityController::class, 'postCommunity']);
$router->post('/event/{id}/community/{post_id}/like', [EventActivityController::class, 'likeCommunity']);

// ⚡ Power Vote (Paid Voting Packs via Paystack)
$router->post('/event/{id}/vote/{poll_id}/power/init', [EventActivityController::class, 'initPowerVote']);
$router->get('/event/{id}/vote/paystack/callback', [EventActivityController::class, 'powerVoteCallback']);
$router->post('/event/{id}/vote/power/verify', [EventActivityController::class, 'verifyPowerVote']);

// Gamified User Passport & Badges
$router->get('/user/passport', [UserPassportController::class, 'index']);

$router->get('/checkout/paystack/callback', [CheckoutController::class, 'paystackCallback']);
$router->post('/checkout/validate-promo', [CheckoutController::class, 'validatePromo']);
$router->post('/checkout/validate-credit', [CheckoutController::class, 'validateCreditAjax']);
$router->post('/checkout/credit/send-otp', [CheckoutController::class, 'sendCreditOtpAjax']);
$router->post('/checkout/credit/verify-otp', [CheckoutController::class, 'verifyCreditOtpAjax']);
$router->get('/checkout/{event_id?}', [CheckoutController::class, 'checkout']);
$router->post('/checkout/{event_id?}', [CheckoutController::class, 'processCheckout']);
$router->get('/success', [CheckoutController::class, 'success']);

// Automated Server-to-Server Payment Webhooks
$router->post('/webhook/paystack', [\App\Controllers\WebhookController::class, 'paystack']);
$router->get('/webhook/paystack', [\App\Controllers\WebhookController::class, 'healthCheck']);

// Public Self-Service Ticket & Order Recovery (Disconnection Protection)
$router->get('/order/verify/{reference?}', [CheckoutController::class, 'verifyOrderView']);
$router->post('/order/verify', [CheckoutController::class, 'verifyOrderPost']);
$router->get('/order/resend-email/{reference}', [CheckoutController::class, 'resendOrderEmailGet']);
$router->post('/order/resend-email', [CheckoutController::class, 'resendOrderEmail']);
$router->post('/order/send-whatsapp', [CheckoutController::class, 'sendWhatsAppPass']);
$router->get('/cron/reconcile-payments', [CheckoutController::class, 'reconcilePaymentsCron']);
$router->get('/cron/process-email-queue', [CheckoutController::class, 'processEmailQueueCron']);

// Layaway & Installment Payment System
$router->get('/layaway', [LayawayController::class, 'dashboard']);
$router->get('/layaway/credits', [LayawayController::class, 'publicCreditsView']);
$router->get('/credits', [LayawayController::class, 'publicCreditsView']);
$router->post('/layaway/check-credits', [LayawayController::class, 'checkCreditsAjax']);
$router->post('/layaway/pay', [LayawayController::class, 'makePayment']);
$router->get('/layaway/callback', [LayawayController::class, 'paymentCallback']);
$router->post('/layaway/apply-credit', [LayawayController::class, 'applyCredit']);
$router->get('/cron/expire-layaways', [LayawayController::class, 'expireCron']);

$router->get('/terms', [HomeController::class, 'terms']);
$router->get('/privacy', [HomeController::class, 'privacy']);
$router->get('/guide', [HomeController::class, 'guide']);

// Auth Routes
$router->get('/login', [AuthController::class, 'loginView']);
$router->post('/login', [AuthController::class, 'login']);
$router->get('/register', [AuthController::class, 'registerView']);
$router->post('/register', [AuthController::class, 'register']);
$router->get('/verify-email', [AuthController::class, 'verifyEmail']);
$router->get('/resend-verification', [AuthController::class, 'resendVerificationView']);
$router->post('/resend-verification', [AuthController::class, 'resendVerification']);
$router->get('/forgot-password', [AuthController::class, 'forgotPasswordView']);
$router->post('/forgot-password', [AuthController::class, 'forgotPassword']);
$router->get('/reset-password', [AuthController::class, 'resetPasswordView']);
$router->post('/reset-password', [AuthController::class, 'resetPassword']);
$router->get('/logout', [AuthController::class, 'logout']);
$router->post('/logout', [AuthController::class, 'logout']);

// Host Routes(Organizer)
$router->get('/host', [HostController::class, 'dashboard']);
$router->get('/host/dashboard', [HostController::class, 'dashboard']);
$router->get('/host/financials', [HostController::class, 'financials']);
$router->get('/host/payouts', [HostController::class, 'financials']);
$router->post('/host/withdraw', [HostController::class, 'requestWithdrawal']);
$router->get('/host/event/create', [HostEventController::class, 'createEvent']);
$router->post('/host/event/create', [HostEventController::class, 'storeEvent']);
$router->get('/host/event/{id}/occurrences', [HostEventController::class, 'getOccurrencesJson']);
$router->get('/host/event/edit/{id}', [HostEventController::class, 'editEvent']);
$router->post('/host/event/edit/{id}', [HostEventController::class, 'updateEvent']);
$router->get('/host/event/management/{id}', [HostEventController::class, 'manageEvent']);
$router->get('/host/event/accounting/{id}', [HostEventController::class, 'accountingReport']);
$router->get('/host/event/accounting/{id}/export', [HostEventController::class, 'exportAccountingCsv']);
$router->get('/host/event/management/{id}/export', [HostEventController::class, 'exportAttendees']);
$router->get('/host/event/management/{id}/export-pdf', [HostEventController::class, 'exportAttendeesPdf']);
$router->get('/host/event/management/{id}/export-accounting', [HostEventController::class, 'exportAccountingReport']);
$router->post('/host/event/management/{id}', [HostEventController::class, 'addTicketManually']);
$router->post('/host/event/management/{id}/send-reminders', [HostEventController::class, 'sendReminders']);
$router->post('/host/event/management/{id}/send-event-day-alert', [HostEventController::class, 'sendEventDayAlerts']);
$router->post('/host/event/management/{id}/send-feedback', [HostEventController::class, 'sendFeedbackRequests']);
$router->post('/host/event/management/{id}/complete', [HostEventController::class, 'markEventCompleted']);
$router->post('/host/event/management/{id}/toggle-flyer', [HostEventController::class, 'toggleFlyerPage']);
$router->post('/host/event/management/{id}/request-staff', [HostEventController::class, 'requestStaff']);
$router->post('/host/event/management/{id}/toggle-staff-checkin', [HostEventController::class, 'toggleStaffCheckin']);
$router->post('/host/event/management/{id}/request-domain', [HostEventController::class, 'requestCustomDomain']);
$router->post('/host/event/management/{id}/addon-redeem', [HostEventController::class, 'toggleAddonRedemption']);
$router->post('/host/event/domain-request/{id}/pay', [HostEventController::class, 'payCustomDomainFee']);
$router->get('/host/event/domain-request/callback', [HostEventController::class, 'domainPaymentCallback']);
$router->post('/host/event/duplicate/{id}', [HostEventController::class, 'duplicateEvent']);
$router->post('/host/event/delete/{id}', [HostEventController::class, 'deleteEvent']);
$router->post('/host/event/transaction/{ref}/verify', [HostEventController::class, 'reverifyPayment']);
$router->post('/host/event/transaction/{ref}/dismiss', [HostEventController::class, 'dismissTransaction']);
$router->get('/host/event/ticket/{id}', [HostTicketController::class, 'ticketDetail']);
$router->post('/host/event/ticket/{id}/action', [HostTicketController::class, 'ticketAction']);
$router->get('/host/event/ticket-verification/{id?}', [HostTicketController::class, 'verifyTicket']);
$router->post('/host/event/ticket-verification', [HostTicketController::class, 'checkIn']);
$router->get('/host/event/referrals/{id}', [HostReferralController::class, 'index']);
$router->post('/host/event/referrals/{id}/create', [HostReferralController::class, 'create']);
$router->post('/host/event/referrals/{id}/update/{ref_id}', [HostReferralController::class, 'update']);
$router->post('/host/event/referrals/{id}/payout/{ref_id}', [HostReferralController::class, 'recordPayout']);
$router->get('/host/event/referrals/{id}/orders/{ref_id}', [HostReferralController::class, 'orders']);
$router->get('/host/event/referrals/{id}/export', [HostReferralController::class, 'export']);
$router->post('/host/event/referrals/{id}/toggle/{ref_id}', [HostReferralController::class, 'toggle']);
$router->post('/host/event/referrals/{id}/delete/{ref_id}', [HostReferralController::class, 'delete']);

// Host Activities Management (supports both /host/event/{id}/activities and /host/event/activities/{id})
$router->get('/host/event/{id}/activities', [HostActivityController::class, 'index']);
$router->get('/host/event/activities/{id}', [HostActivityController::class, 'index']);
$router->post('/host/event/{id}/activities/poll/create', [HostActivityController::class, 'createPoll']);
$router->post('/host/event/activities/{id}/poll/create', [HostActivityController::class, 'createPoll']);
$router->post('/host/event/{id}/activities/poll/{poll_id}/toggle', [HostActivityController::class, 'togglePoll']);
$router->post('/host/event/activities/{id}/poll/{poll_id}/toggle', [HostActivityController::class, 'togglePoll']);
$router->post('/host/event/{id}/activities/trivia/create', [HostActivityController::class, 'createTrivia']);
$router->post('/host/event/activities/{id}/trivia/create', [HostActivityController::class, 'createTrivia']);
$router->post('/host/event/{id}/activities/trivia/{trivia_id}/delete', [HostActivityController::class, 'deleteTrivia']);
$router->post('/host/event/activities/{id}/trivia/{trivia_id}/delete', [HostActivityController::class, 'deleteTrivia']);
$router->post('/host/event/{id}/activities/request/{req_id}/moderate', [HostActivityController::class, 'moderateRequest']);
$router->post('/host/event/activities/{id}/request/{req_id}/moderate', [HostActivityController::class, 'moderateRequest']);
$router->post('/host/event/{id}/activities/community/{post_id}/delete', [HostActivityController::class, 'moderateCommunity']);
$router->post('/host/event/activities/{id}/community/{post_id}/delete', [HostActivityController::class, 'moderateCommunity']);
$router->get('/host/settings', [HostSettingsController::class, 'settings']);
$router->post('/host/settings', [HostSettingsController::class, 'saveSettings']);
$router->get('/host/promo-codes', [PromoCodeController::class, 'index']);
$router->post('/host/promo-codes/create', [PromoCodeController::class, 'store']);
$router->post('/host/promo-codes/delete', [PromoCodeController::class, 'delete']);

// Admin Routes
$router->get('/admin', [AdminController::class, 'dashboard']);
$router->get('/admin/dashboard', [AdminController::class, 'dashboard']);
$router->get('/admin/overview', [AdminController::class, 'dashboard']);
$router->get('/admin/users', [AdminUserController::class, 'users']);
$router->get('/admin/users/detail', [AdminUserController::class, 'userDetail']);
$router->post('/admin/users', [AdminUserController::class, 'postAction']);
$router->get('/admin/users/export-data', [AdminUserController::class, 'exportUserData']);
$router->get('/admin/users/dossier', [AdminUserController::class, 'userDossier']);
$router->get('/admin/attendees', [AdminAttendeeController::class, 'index']);
$router->get('/admin/attendees/export', [AdminAttendeeController::class, 'exportCsv']);
$router->get('/admin/attendees/export-pdf', [AdminAttendeeController::class, 'exportPdf']);
$router->get('/admin/events', [AdminEventController::class, 'events']);
$router->post('/admin/events', [AdminEventController::class, 'deleteEvent']);
$router->post('/admin/events/duplicate/{id}', [AdminEventController::class, 'duplicateEvent']);
$router->post('/admin/events/toggle-featured', [AdminEventController::class, 'toggleFeatured']);
$router->get('/admin/events/{id}', [AdminEventController::class, 'eventDetail']);
$router->get('/admin/domain-requests', [AdminEventController::class, 'domainRequests']);
$router->post('/admin/domain-requests/{id}/approve', [AdminEventController::class, 'approveDomainRequest']);
$router->post('/admin/domain-requests/{id}/reject', [AdminEventController::class, 'rejectDomainRequest']);
$router->post('/admin/domain-requests/{id}/mark-paid', [AdminEventController::class, 'markDomainRequestPaid']);
$router->post('/admin/domain-requests/{id}/verify-payment', [AdminEventController::class, 'verifyDomainPayment']);
$router->get('/admin/teams', [AdminTeamController::class, 'teams']);
$router->get('/admin/teams/export', [AdminTeamController::class, 'exportCsv']);
$router->get('/admin/teams/detail', [AdminTeamController::class, 'staffDetail']);
$router->post('/admin/teams', [AdminTeamController::class, 'postAction']);
$router->get('/admin/communications', [AdminBroadcastController::class, 'index']);
$router->post('/admin/communications', [AdminBroadcastController::class, 'send']);
$router->get('/admin/communications/search', [AdminBroadcastController::class, 'searchRecipients']);
$router->get('/admin/payouts', [AdminPayoutController::class, 'payouts']);
$router->get('/admin/payouts/export', [AdminPayoutController::class, 'exportCsv']);
$router->post('/admin/payouts', [AdminPayoutController::class, 'processAction']);
$router->get('/cron/auto-payouts', [AdminPayoutController::class, 'runAutoPayoutsCron']);
$router->get('/admin/settings', [AdminSettingsController::class, 'settings']);
$router->post('/admin/settings', [AdminSettingsController::class, 'saveSettings']);
$router->post('/admin/cache/clear', [AdminSettingsController::class, 'clearCache']);
$router->get('/admin/cache/clear', [AdminSettingsController::class, 'clearCache']);
$router->get('/admin/support', [AdminSupportController::class, 'inquiries']);
$router->post('/admin/support', [AdminSupportController::class, 'postAction']);

// Admin Sponsors & Partners Tape Routes
$router->get('/admin/sponsors', [AdminSponsorController::class, 'index']);
$router->post('/admin/sponsors', [AdminSponsorController::class, 'store']);
$router->post('/admin/sponsors/settings', [AdminSponsorController::class, 'saveSettings']);
$router->post('/admin/sponsors/reorder', [AdminSponsorController::class, 'reorder']);
$router->post('/admin/sponsors/{id}/update', [AdminSponsorController::class, 'update']);
$router->post('/admin/sponsors/{id}/delete', [AdminSponsorController::class, 'delete']);
$router->post('/admin/sponsors/{id}/toggle', [AdminSponsorController::class, 'toggle']);

// Team Workstation Routes
$router->get('/teams', [TeamController::class, 'loginView']);
$router->get('/teams/login', [TeamController::class, 'loginView']);
$router->post('/teams/login', [TeamController::class, 'loginPost']);
$router->post('/teams/switch-event', [TeamController::class, 'switchEvent']);
$router->get('/teams/logout', [TeamController::class, 'logout']);

$router->get('/teams/security-scanner', [TeamController::class, 'securityScanner']);
$router->post('/teams/verify-ticket', [TeamController::class, 'verifyTicketApi']);

$router->get('/teams/coordinator-dashboard', [TeamController::class, 'coordinatorDashboard']);
$router->post('/teams/toggle-staff-checkin', [TeamController::class, 'toggleStaffCheckin']);
$router->post('/teams/issue-gate-ticket', [TeamController::class, 'issueGateTicket']);

$router->get('/teams/usher-guide', [TeamController::class, 'usherGuide']);

$router->get('/teams/support-desk', [TeamController::class, 'supportDesk']);
$router->post('/teams/support-action', [TeamController::class, 'supportAction']);

$router->get('/teams/tech-ops', [TeamController::class, 'techOps']);
$router->get('/teams/finance-desk', [TeamController::class, 'financeDesk']);

// ==================================================
// EXTERNAL API ROUTES (v1) — Headless Ticketing & Checkout
// ==================================================
// These routes allow event hosts on custom domains to fetch event data,
// validate promo codes, initiate checkouts, and verify orders via JSON API.
// CORS is handled automatically by SecurityHeadersMiddleware and Router.
// CSRF is exempted for /api/* paths in CsrfMiddleware.

use App\Controllers\Api\EventApiController;

// Event Data & Details
$router->get('/api/v1/events/{id}', [EventApiController::class, 'getEvent']);

// Checkout & Purchase
$router->post('/api/v1/events/{id}/checkout', [EventApiController::class, 'checkout']);
$router->post('/api/v1/checkout', [EventApiController::class, 'checkout']);

// Promo Code Validation
$router->post('/api/v1/checkout/validate-promo', [EventApiController::class, 'validatePromo']);

// Order Verification & Ticket Retrieval
$router->get('/api/v1/checkout/verify/{reference}', [EventApiController::class, 'verifyOrder']);

// Embeddable Checkout Widget (iframe-friendly, relaxed X-Frame-Options)
$router->get('/embed/{id}', [EventApiController::class, 'embedCheckout']);

// Browser and crawler utilities (prevent 404 spam)
$router->get('/favicon.ico', function () {
    $favicon = Application::$ROOT_DIR . '/assets/favicon.ico';
    if (file_exists($favicon)) {
        header('Content-Type: image/x-icon');
        readfile($favicon);
        exit;
    }
    http_response_code(204);
    exit;
});

$router->get('/robots.txt', function () {
    header('Content-Type: text/plain');
    echo "User-agent: *\nDisallow:";
    exit;
});
