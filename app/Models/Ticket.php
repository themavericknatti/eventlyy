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

    /**
     * Get list of occupied seat numbers (integers) for a ticket type.
     */
    public static function getOccupiedSeatNumbers(int $ticketTypeId): array
    {
        $db = \Ace\Application::$app->db->pdo;
        $stmt = $db->prepare("
            SELECT assigned_seat 
            FROM tickets 
            WHERE ticket_type_id = :tt_id 
            AND status IN ('Paid', 'Comped', 'Layaway') 
            AND assigned_seat IS NOT NULL 
            AND assigned_seat != ''
        ");
        $stmt->execute(['tt_id' => $ticketTypeId]);
        $seatsRaw = $stmt->fetchAll(\PDO::FETCH_COLUMN) ?: [];

        $occupied = [];
        foreach ($seatsRaw as $raw) {
            if (preg_match('/(?:Seat[-:\s]*|\#)?(\d+)\s*$/i', trim($raw), $matches)) {
                $occupied[] = (int)$matches[1];
            }
        }
        return array_values(array_unique($occupied));
    }

    /**
     * Format a standardized seat string from section, row, prefix, and seat number.
     */
    public static function formatSeatString(array $ticketType, ?int $seatNumber = null): string
    {
        $sec = trim((string)($ticketType['section_name'] ?? ''));
        $row = trim((string)($ticketType['row_name'] ?? ''));
        $prefix = trim((string)($ticketType['seat_prefix'] ?? ''));

        $parts = [];
        if (!empty($sec)) $parts[] = 'Section ' . $sec;
        if (!empty($row)) $parts[] = 'Row ' . $row;
        if ($seatNumber !== null) {
            $parts[] = ($prefix ?: 'Seat ') . $seatNumber;
        }

        return implode(' • ', $parts);
    }

    /**
     * Parse the integer seat number from an assigned seat string.
     */
    public static function parseSeatNumber(?string $seatString): ?int
    {
        if (empty($seatString)) return null;
        if (preg_match('/(?:Seat[-:\s]*|\#)?(\d+)\s*$/i', trim($seatString), $matches)) {
            return (int)$matches[1];
        }
        return null;
    }
}
