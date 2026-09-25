<?php

namespace App\Models;

use Ace\Model;

class Transaction extends Model
{
    public static function tableName(): string
    {
        return 'transactions';
    }

    public function primaryKey(): string
    {
        return 'id';
    }

    public function rules(): array
    {
        return [
            'reference' => 'required',
            'event_id' => 'required',
            'payer_name' => 'required',
            'payer_email' => 'required|email',
            'total_amount' => 'required|numeric'
        ];
    }

    /**
     * Find a transaction record by reference code.
     */
    public static function findByReference(string $reference): ?array
    {
        $db = \Ace\Application::$app->db->pdo;
        $stmt = $db->prepare("SELECT * FROM transactions WHERE reference = :reference LIMIT 1");
        $stmt->execute(['reference' => $reference]);
        return $stmt->fetch() ?: null;
    }
}
