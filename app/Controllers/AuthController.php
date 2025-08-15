<?php

namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\RESTful\ResourceController;

class AuthController extends ResourceController
{
    protected $format = 'json';

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    /**
     * Register a new user
     */
    public function register()
    {
        $rules = [
            'full_name' => 'required|min_length[2]|max_length[100]',
            'phone' => 'required|min_length[10]|max_length[15]',
            'pin' => 'required|min_length[4]|max_length[6]|numeric',
        ];

        if (!$this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $data = [
            'full_name' => $this->request->getPost('full_name'),
            'phone' => $this->request->getPost('phone'),
            'pin' => $this->request->getPost('pin'),
        ];

        // Check if phone already exists
        if ($this->userModel->phoneExists($data['phone'])) {
            return $this->failValidationError('Phone number already registered');
        }

        try {
            $userId = $this->userModel->insert($data);
            
            if ($userId) {
                $user = $this->userModel->find($userId);
                unset($user['pin']); // Don't return the hashed PIN
                
                return $this->respond([
                    'status' => 'success',
                    'message' => 'User registered successfully',
                    'data' => $user
                ], 201);
            } else {
                return $this->failServerError('Failed to register user');
            }
        } catch (\Exception $e) {
            return $this->failServerError('Registration failed: ' . $e->getMessage());
        }
    }

    /**
     * Login user
     */
    public function login()
    {
        $rules = [
            'phone' => 'required|min_length[10]|max_length[15]',
            'pin' => 'required|min_length[4]|max_length[6]|numeric',
        ];

        if (!$this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $phone = $this->request->getPost('phone');
        $pin = $this->request->getPost('pin');

        try {
            $user = $this->userModel->authenticate($phone, $pin);
            
            if ($user) {
                // Create session or token here if needed
                return $this->respond([
                    'status' => 'success',
                    'message' => 'Login successful',
                    'data' => $user
                ]);
            } else {
                return $this->failUnauthorized('Invalid phone number or PIN');
            }
        } catch (\Exception $e) {
            return $this->failServerError('Login failed: ' . $e->getMessage());
        }
    }

    /**
     * Get user profile
     */
    public function profile($userId = null)
    {
        // In a real app, you'd get userId from session/token
        if (!$userId) {
            return $this->failUnauthorized('User ID required');
        }

        try {
            $user = $this->userModel->find($userId);
            
            if ($user) {
                unset($user['pin']); // Don't return the hashed PIN
                return $this->respond([
                    'status' => 'success',
                    'data' => $user
                ]);
            } else {
                return $this->failNotFound('User not found');
            }
        } catch (\Exception $e) {
            return $this->failServerError('Failed to get profile: ' . $e->getMessage());
        }
    }

    /**
     * Update user profile
     */
    public function updateProfile()
    {
        $userId = $this->request->getPost('user_id');
        
        if (!$userId) {
            return $this->failUnauthorized('User ID required');
        }

        $rules = [
            'user_id' => 'required|integer|is_natural_no_zero',
            'full_name' => 'permit_empty|min_length[2]|max_length[100]',
            'pin' => 'permit_empty|min_length[4]|max_length[6]|numeric',
        ];

        if (!$this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $data = [];
        if ($this->request->getPost('full_name')) {
            $data['full_name'] = $this->request->getPost('full_name');
        }
        if ($this->request->getPost('pin')) {
            $data['pin'] = $this->request->getPost('pin');
        }

        if (empty($data)) {
            return $this->failValidationError('No data provided for update');
        }

        try {
            $updated = $this->userModel->update($userId, $data);
            
            if ($updated) {
                $user = $this->userModel->find($userId);
                unset($user['pin']); // Don't return the hashed PIN
                
                return $this->respond([
                    'status' => 'success',
                    'message' => 'Profile updated successfully',
                    'data' => $user
                ]);
            } else {
                return $this->failServerError('Failed to update profile');
            }
        } catch (\Exception $e) {
            return $this->failServerError('Update failed: ' . $e->getMessage());
        }
    }
} 