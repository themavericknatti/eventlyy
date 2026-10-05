<?php

namespace App\Models;

use Ace\Model;

class User extends Model
{
    public static function tableName(): string
    {
        return 'users';
    }

    public function primaryKey(): string
    {
        return 'id';
    }

    public function rules(): array
    {
        return [
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
        ];
    }

    public function save(): bool
    {
        if (isset($this->attributes['password'])) {
            if (!password_get_info($this->attributes['password'])['algo']) {
                $this->attributes['password'] = password_hash($this->attributes['password'], PASSWORD_BCRYPT);
            }
        }

        return parent::save();
    }
}
