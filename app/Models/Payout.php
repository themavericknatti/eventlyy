<?php

namespace App\Models;

use Ace\Model;

class Payout extends Model
{
    public static function tableName(): string
    {
        return 'payouts';
    }

    public function primaryKey(): string
    {
        return 'id';
    }

    public function rules(): array
    {
        return [
            'organizer_id' => 'required|exists:users,id',
            'event_id' => 'required|exists:events,id',
            'amount' => 'required|numeric'
        ];
    }
}
