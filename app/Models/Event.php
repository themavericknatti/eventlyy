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
        return [
            'name' => 'required',
            'description' => 'required',
            'category' => 'required',
            'date' => 'required|date',
            'location' => 'required',
            'organizer_id' => 'required|exists:users,id',
            'custom_url' => 'unique:events,custom_url'
        ];
    }
}