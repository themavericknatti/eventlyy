<?php

use Ace\Application;

/** @var \Ace\Router $router */

use App\Controllers\Admin\AdminAttendeeController;
use App\Controllers\Admin\AdminBroadcastController;
use App\Controllers\Admin\AdminController;
use App\Controllers\Admin\AdminEventController;
use App\Controllers\Admin\AdminPayoutController;
use App\Controllers\Admin\AdminSettingsController;
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

// ==================================================
// APPLICATION WEB ROUTES
// ==================================================

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

$router->get('/checkout/paystack/callback', [CheckoutController::class, 'paystackCallback']);
$router->post('/checkout/validate-promo', [CheckoutController::class, 'validatePromo']);
$router->get('/checkout/{event_id?}', [CheckoutController::class, 'checkout']);
$router->post('/checkout/{event_id?}', [CheckoutController::class, 'processCheckout']);
$router->get('/success', [CheckoutController::class, 'success']);

// Automated Server-to-Server Payment Webhooks
$router->post('/webhook/paystack', [\App\Controllers\WebhookController::class, 'paystack']);
$router->get('/webhook/paystack', [\App\Controllers\WebhookController::class, 'healthCheck']);

// Public Self-Service Ticket & Order Recovery (Disconnection Protection)
$router->get('/order/verify/{reference?}', [CheckoutController::class, 'verifyOrderView']);
$router->post('/order/verify', [CheckoutController::class, 'verifyOrderPost']);
$router->get('/cron/reconcile-payments', [CheckoutController::class, 'reconcilePaymentsCron']);

$router->get('/terms', [HomeController::class, 'terms']);
$router->get('/privacy', [HomeController::class, 'privacy']);

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
$router->get('/host/event/edit/{id}', [HostEventController::class, 'editEvent']);
$router->post('/host/event/edit/{id}', [HostEventController::class, 'updateEvent']);
$router->get('/host/event/management/{id}', [HostEventController::class, 'manageEvent']);
$router->get('/host/event/accounting/{id}', [HostEventController::class, 'accountingReport']);
$router->get('/host/event/accounting/{id}/export', [HostEventController::class, 'exportAccountingCsv']);
$router->get('/host/event/management/{id}/export', [HostEventController::class, 'exportAttendees']);
$router->get('/host/event/management/{id}/export-accounting', [HostEventController::class, 'exportAccountingReport']);
$router->post('/host/event/management/{id}', [HostEventController::class, 'addTicketManually']);
$router->post('/host/event/management/{id}/send-reminders', [HostEventController::class, 'sendReminders']);
$router->post('/host/event/management/{id}/send-feedback', [HostEventController::class, 'sendFeedbackRequests']);
$router->post('/host/event/management/{id}/complete', [HostEventController::class, 'markEventCompleted']);
$router->post('/host/event/management/{id}/toggle-flyer', [HostEventController::class, 'toggleFlyerPage']);
$router->post('/host/event/management/{id}/request-staff', [HostEventController::class, 'requestStaff']);
$router->post('/host/event/management/{id}/toggle-staff-checkin', [HostEventController::class, 'toggleStaffCheckin']);
$router->post('/host/event/duplicate/{id}', [HostEventController::class, 'duplicateEvent']);
$router->post('/host/event/delete/{id}', [HostEventController::class, 'deleteEvent']);
$router->post('/host/event/transaction/{ref}/verify', [HostEventController::class, 'reverifyPayment']);
$router->get('/host/event/ticket/{id}', [HostTicketController::class, 'ticketDetail']);
$router->post('/host/event/ticket/{id}/action', [HostTicketController::class, 'ticketAction']);
$router->get('/host/event/ticket-verification/{id?}', [HostTicketController::class, 'verifyTicket']);
$router->post('/host/event/ticket-verification', [HostTicketController::class, 'checkIn']);
$router->get('/host/event/referrals/{id}', [HostReferralController::class, 'index']);
$router->post('/host/event/referrals/{id}/create', [HostReferralController::class, 'create']);
$router->post('/host/event/referrals/{id}/toggle/{ref_id}', [HostReferralController::class, 'toggle']);
$router->post('/host/event/referrals/{id}/delete/{ref_id}', [HostReferralController::class, 'delete']);
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
$router->get('/admin/attendees', [AdminAttendeeController::class, 'index']);
$router->get('/admin/attendees/export', [AdminAttendeeController::class, 'exportCsv']);
$router->get('/admin/events', [AdminEventController::class, 'events']);
$router->post('/admin/events', [AdminEventController::class, 'deleteEvent']);
$router->post('/admin/events/duplicate/{id}', [AdminEventController::class, 'duplicateEvent']);
$router->post('/admin/events/toggle-featured', [AdminEventController::class, 'toggleFeatured']);
$router->get('/admin/events/{id}', [AdminEventController::class, 'eventDetail']);
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
$router->get('/admin/support', [AdminSupportController::class, 'inquiries']);
$router->post('/admin/support', [AdminSupportController::class, 'postAction']);

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
