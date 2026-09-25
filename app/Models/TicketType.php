<?php

namespace App\Models;

use Ace\Model;

class TicketType extends Model
{
    public static function tableName(): string
    {
        return 'ticket_types';
    }

    public function primaryKey(): string
    {
        return 'id';
    }

    public function rules(): array
    {
        return [
            'event_id' => 'required|exists:events,id',
            'name' => 'required',
            'price' => 'required|numeric',
            'capacity' => 'required|numeric'
        ];
    }
}
