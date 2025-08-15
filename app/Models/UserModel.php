<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = ['full_name', 'phone', 'pin'];

    // Dates
    protected $useTimestamps = false; // Disable automatic timestamps to avoid type error
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = false;
    protected $deletedField = false;

    // Validation
    protected $validationRules = [
        'full_name' => 'required|min_length[2]|max_length[100]',
        'phone' => 'required|min_length[10]|max_length[15]|is_unique[users.phone,id,{id}]',
        'pin' => 'required|min_length[4]|max_length[6]',
    ];

    protected $validationMessages = [
        'full_name' => [
            'required' => 'Full name is required',
            'min_length' => 'Full name must be at least 2 characters long',
            'max_length' => 'Full name cannot exceed 100 characters',
        ],
        'phone' => [
            'required' => 'Phone number is required',
            'min_length' => 'Phone number must be at least 10 digits',
            'max_length' => 'Phone number cannot exceed 15 digits',
            'is_unique' => 'Phone number already exists',
        ],
        'pin' => [
            'required' => 'PIN is required',
            'min_length' => 'PIN must be at least 4 digits',
            'max_length' => 'PIN cannot exceed 6 digits',
        ],
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    /**
     * Hash PIN and set created_at before saving
     */
    protected function hashPin($data)
    {
        if (isset($data['data']['pin'])) {
            $data['data']['pin'] = password_hash($data['data']['pin'], PASSWORD_DEFAULT);
        }
        // Manually set created_at to avoid type error
        $data['data']['created_at'] = date('Y-m-d H:i:s');
        return $data;
    }

    /**
     * Before insert callback
     */
    protected $beforeInsert = ['hashPin'];

    /**
     * Authenticate user with phone and PIN
     */
    public function authenticate($phone, $pin)
    {
        $user = $this->where('phone', $phone)->first();
        
        if ($user && password_verify($pin, $user['pin'])) {
            unset($user['pin']); // Don't return the hashed PIN
            return $user;
        }
        
        return false;
    }

    /**
     * Get user by phone number
     */
    public function getByPhone($phone)
    {
        $user = $this->where('phone', $phone)->first();
        if ($user) {
            unset($user['pin']); // Don't return the hashed PIN
        }
        return $user;
    }

    /**
     * Check if phone number exists
     */
    public function phoneExists($phone, $excludeId = null)
    {
        $builder = $this->where('phone', $phone);
        if ($excludeId) {
            $builder->where('id !=', $excludeId);
        }
        return $builder->countAllResults() > 0;
    }
} 