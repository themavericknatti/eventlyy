<?php

namespace App\Models;

use Ace\Model;

class EventReview extends Model
{
    public static function tableName(): string
    {
        return 'event_reviews';
    }

    public function primaryKey(): string
    {
        return 'id';
    }

    public function rules(): array
    {
        return [
            'event_id' => 'required',
            'organizer_id' => 'required',
            'attendee_name' => 'required',
            'attendee_email' => 'required|email',
            'rating' => 'required|numeric'
        ];
    }
}
