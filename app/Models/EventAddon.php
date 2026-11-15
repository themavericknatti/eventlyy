<?php

namespace App\Models;

use Ace\Model;

class EventAddon extends Model
{
    public static function tableName(): string
    {
        return 'event_addons';
    }

    public function primaryKey(): string
    {
        return 'id';
    }

    public function rules(): array
    {
        return [
            'event_id' => 'required',
            'name' => 'required',
            'price' => 'required'
        ];
    }
}
