<?php

namespace App\Models;

use Ace\Model;

class OrderAddon extends Model
{
    public static function tableName(): string
    {
        return 'order_addons';
    }

    public function primaryKey(): string
    {
        return 'id';
    }

    public function rules(): array
    {
        return [
            'event_id' => 'required',
            'addon_id' => 'required',
            'quantity' => 'required',
            'unit_price' => 'required'
        ];
    }
}
