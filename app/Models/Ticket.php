<?php

namespace App\Models;

use Ace\Model;

class Ticket extends Model
{
    public static function tableName(): string
    {
        return 'tickets';
    }

    public function primaryKey(): string
    {
        return 'id';
    }

    public function rules(): array
    {
        return [
            'id' => 'required',
            'event_id' => 'required|exists:events,id',
            'ticket_type_id' => 'required|exists:ticket_types,id',
            'attendee_name' => 'required',
            'attendee_email' => 'required|email',
            'price' => 'required|numeric'
        ];
    }

    /**
     * Generate a unique ticket code.
     */
    public static function generateCode(): string
    {
        do {
            $code = 'TKT-' . strtoupper(bin2hex(random_bytes(3)));
        } while (static::findOne(['id' => $code]) !== null);
        
        return $code;
    }
}
