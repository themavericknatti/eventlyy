<?php

namespace App\Models;

use Ace\Model;

class HostProfile extends Model
{
    public static function tableName(): string
    {
        return 'host_profiles';
    }

    public function primaryKey(): string
    {
        return 'user_id';
    }

    public function rules(): array
    {
        return [
            'user_id' => 'required|exists:users,id'
        ];
    }
}
