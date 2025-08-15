<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Class BaseController
 *
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 * Extend this class in any new controllers:
 *     class Home extends BaseController
 *
 * For security be sure to declare any new methods as protected or private.
 */
abstract class BaseController extends ResourceController
{
    /**
     * Instance of the main Request object.
     *
     * @var CLIRequest|IncomingRequest
     */
    protected $request;

    /**
     * An array of helpers to be loaded automatically upon
     * class instantiation. These helpers will be available
     * to all other controllers that extend BaseController.
     *
     * @var list<string>
     */
    protected $helpers = [];

    /**
     * Be sure to declare properties for any property fetch you initialized.
     * The creation of dynamic property is deprecated in PHP 8.2.
     */
    // protected $session;

    /**
     * @return void
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Do Not Edit This Line
        parent::initController($request, $response, $logger);

        // Preload any models, libraries, etc, here.

        // E.g.: $this->session = service('session');
    }

    /**
     * Get current user ID from session, token, or request parameters
     * 
     * @return int|null
     */
    protected function getCurrentUserId()
    {
        // Try to get from session first
        $session = service('session');
        $userId = $session->get('user_id');
        
        if ($userId) {
            return $userId;
        }

        // Try to get from Authorization header (Bearer token)
        $authHeader = $this->request->getHeaderLine('Authorization');
        if (strpos($authHeader, 'Bearer ') === 0) {
            $token = substr($authHeader, 7);
            // Here you would validate the token and extract user ID
            // For now, we'll return null if no session user
            return null;
        }

        // Try to get from X-User-ID header (for mobile app)
        $userId = $this->request->getHeaderLine('X-User-ID');
        if ($userId && is_numeric($userId)) {
            return (int) $userId;
        }

        // Try to get from query parameter (for GET requests)
        $userId = $this->request->getGet('user_id');
        if ($userId && is_numeric($userId)) {
            return (int) $userId;
        }

        // Try to get from POST data (for POST requests)
        $userId = $this->request->getPost('user_id');
        if ($userId && is_numeric($userId)) {
            return (int) $userId;
        }

        return null;
    }
}
