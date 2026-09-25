<?php

/**
 * HomeController
 *
 * This controller handles the home page of the application.
 *
 * @package Evently
 * @subpackage Controllers/Home
 * @author Your Name <Celio Natti>
 * @license MIT License
 * @version 1.0.0
 */

namespace App\Controllers;

use Ace\Controller;
use Ace\Request;
use App\Services\EventService;

class HomeController extends Controller
{
    protected EventService $eventService;

    public function __construct()
    {
        $this->eventService = new EventService();
    }

    /**
     * Render the landing / welcome page with dynamic events, search filters, and pagination.
     */
    public function index(Request $request): string
    {
        $search = $request->getString('q');
        $location = $request->getString('location');
        $category = $request->getString('category');
        $page = max(1, $request->getInt('page', 1));

        // Fetch dynamic public events list
        $result = $this->eventService->getPublicEvents($search, $location, $category, $page, 6);
        
        // Fetch featured events for carousel
        $featuredEvents = $this->eventService->getFeaturedEvents(5);

        // Fetch available locations for filter
        $locations = $this->eventService->getPublicLocations();

        $categories = ['All', 'Concerts', 'Tech', 'Comedy', 'Sports', 'Theater', 'Family', 'Art & Wine'];

        return $this->render('home', [
            'title' => 'Welcome to Evently - Find live events, concerts & experiences.',
            'events' => $result['events'],
            'total' => $result['total'],
            'page' => $result['page'],
            'limit' => $result['limit'],
            'totalPages' => $result['totalPages'],
            'featuredEvents' => $featuredEvents,
            'locations' => $locations,
            'categories' => $categories,
            'search' => $search,
            'location' => $location,
            'category' => $category,
        ]);
    }

    /**
     * Render the about page
     */
    public function about(Request $request): string
    {
        return $this->render('about', [
            'title' => 'About Evently - Learn More About Our Mission and Team.'
        ]);
    }

    /**
     * Render the support page
     */
    public function support(Request $request): string
    {
        return $this->render('support', [
            'title' => 'Support - Get Help with Evently'
        ]);
    }

    /**
     * Process Support Contact Form Submission
     */
    public function postSupport(Request $request)
    {
        $name = $request->getString('name');
        $email = $request->getString('email');
        $subject = $request->getString('subject', 'general');
        $message = $request->getString('message');

        if (empty($name) || empty($email) || empty($message)) {
            \Ace\Application::$app->session->setFlash('error', 'Please fill in all required fields (Name, Email, Message).');
            return redirect('/support');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            \Ace\Application::$app->session->setFlash('error', 'Please provide a valid email address.');
            return redirect('/support');
        }

        try {
            $db = \Ace\Application::$app->db->pdo;

            $stmt = $db->prepare("
                INSERT INTO support_inquiries (name, email, subject, message, status, created_at)
                VALUES (:name, :email, :subject, :message, 'new', NOW())
            ");
            $stmt->execute([
                'name' => $name,
                'email' => $email,
                'subject' => $subject,
                'message' => $message
            ]);

            \Ace\Application::$app->session->setFlash('success', 'Thank you! Your support message has been sent successfully. Our team will get back to you shortly.');
        } catch (\Exception $e) {
            error_log("Support form submission error: " . $e->getMessage());
            \Ace\Application::$app->session->setFlash('error', 'Failed to send message. Please try again later.');
        }

        return redirect('/support');
    }

    /**
     * Render the terms of service page
     */
    public function terms(Request $request): string
    {
        $siteName = site_setting('site_title', 'Eventlyy');
        $supportEmail = site_setting('support_email', 'support@eventlyy.com');

        return $this->render('others/terms', [
            'title' => 'Terms of Service - ' . $siteName,
            'siteName' => $siteName,
            'supportEmail' => $supportEmail,
        ]);
    }

    /**
     * Render the privacy policy page
     */
    public function privacy(Request $request): string
    {
        $siteName = site_setting('site_title', 'Eventlyy');
        $supportEmail = site_setting('support_email', 'support@eventlyy.com');

        return $this->render('others/privacy', [
            'title' => 'Privacy Policy - ' . $siteName,
            'siteName' => $siteName,
            'supportEmail' => $supportEmail,
        ]);
    }
}
