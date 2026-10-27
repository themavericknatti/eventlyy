<?php

namespace App\Models;

use Ace\Model;

class Event extends Model
{
    public static function tableName(): string
    {
        return 'events';
    }

    public function primaryKey(): string
    {
        return 'id';
    }

    public function rules(): array
    {
        $rules = [
            'name' => 'required',
            'description' => 'required',
            'category' => 'required',
            'date' => 'required|date',
            'location' => 'required',
            'organizer_id' => 'required|exists:users,id',
        ];

        // For non-recurring events, enforce unique custom_url via model validator
        // (Recurring series share custom_url safely across their recurrence group)
        if (empty($this->is_recurring) || empty($this->recurrence_group_id)) {
            $rules['custom_url'] = 'unique:events,custom_url';
        }

        return $rules;
    }
}
