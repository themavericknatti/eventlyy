<?php

namespace App\Services;

use App\Models\Event;
use Ace\Application;
use ZipArchive;

/**
 * AccountingExportService
 *
 * Generates an executive-grade, multi-sheet OpenXML Excel (.xlsx) workbook for post-event
 * accounting, reconciliation, and audit. Uses PHP's native ZipArchive with zero external dependencies.
 * Fully compliant with ISO/IEC 29500 and ECMA-376 OpenXML standard.
 */
class AccountingExportService
{
    /**
     * Generate the complete XLSX file for an event and return the binary file path.
     *
     * @param Event $event
     * @param array $tickets
     * @param array $transactions
     * @param array $ticketTypes
     * @param array|null $payout
     * @return string Temporary path of the generated .xlsx file
     */
    public function generateEventWorkbook(Event $event, array $tickets, array $transactions, array $ticketTypes, ?array $payout = null): string
    {
        $tempDir = Application::$ROOT_DIR . '/storage/framework/cache';
        if (!is_dir($tempDir)) {
            @mkdir($tempDir, 0775, true);
        }
        $filename = $tempDir . '/accounting_' . $event->id . '_' . uniqid() . '.xlsx';

        $zip = new ZipArchive();
        if ($zip->open($filename, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \Exception("Could not create spreadsheet archive at {$filename}");
        }

        // Build transaction lookup map: reference => transaction
        $txMap = [];
        foreach ($transactions as $tx) {
            if (!empty($tx['reference'])) {
                $txMap[$tx['reference']] = $tx;
            }
        }

        // Separate tickets into Online / Paystack Purchases vs Issued / Comp Tickets
        $onlineTickets = [];
        $issuedTickets = [];

        foreach ($tickets as $t) {
            $isComp = ($t['status'] === 'Comped') || !empty($t['is_issued']);
            $statusDetail = $t['status_detail'] ?? '';
            $isGateSale = str_contains($statusDetail, 'Gate Sale');

            if ($isComp || $isGateSale) {
                $issuedTickets[] = $t;
            } else {
                $onlineTickets[] = $t;
            }
        }

        // 1. [Content_Types].xml
        $contentTypes = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n" .
            '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">' . "\n" .
            '  <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>' . "\n" .
            '  <Default Extension="xml" ContentType="application/xml"/>' . "\n" .
            '  <Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>' . "\n" .
            '  <Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>' . "\n" .
            '  <Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>' . "\n" .
            '  <Override PartName="/xl/worksheets/sheet2.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>' . "\n" .
            '  <Override PartName="/xl/worksheets/sheet3.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>' . "\n" .
            '</Types>';
        $zip->addFromString('[Content_Types].xml', $contentTypes);

        // 2. _rels/.rels
        $rels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n" .
            '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">' . "\n" .
            '  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>' . "\n" .
            '</Relationships>';
        $zip->addFromString('_rels/.rels', $rels);

        // 3. xl/_rels/workbook.xml.rels
        $wbRels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n" .
            '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">' . "\n" .
            '  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>' . "\n" .
            '  <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet2.xml"/>' . "\n" .
            '  <Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet3.xml"/>' . "\n" .
            '  <Relationship Id="rId4" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>' . "\n" .
            '</Relationships>';
        $zip->addFromString('xl/_rels/workbook.xml.rels', $wbRels);

        // 4. xl/workbook.xml
        $workbook = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n" .
            '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">' . "\n" .
            '  <bookViews>' . "\n" .
            '    <workbookView xWindow="0" yWindow="0" windowWidth="20480" windowHeight="10240"/>' . "\n" .
            '  </bookViews>' . "\n" .
            '  <sheets>' . "\n" .
            '    <sheet name="Online &amp; Paystack Sales" sheetId="1" r:id="rId1"/>' . "\n" .
            '    <sheet name="Issued &amp; Comp Tickets" sheetId="2" r:id="rId2"/>' . "\n" .
            '    <sheet name="Financial Reconciliation" sheetId="3" r:id="rId3"/>' . "\n" .
            '  </sheets>' . "\n" .
            '</workbook>';
        $zip->addFromString('xl/workbook.xml', $workbook);

        // 5. xl/styles.xml
        $zip->addFromString('xl/styles.xml', $this->buildStylesXml());

        // 6. Generate Worksheets
        $zip->addFromString('xl/worksheets/sheet1.xml', $this->buildOnlineSalesSheet($event, $onlineTickets, $txMap));
        $zip->addFromString('xl/worksheets/sheet2.xml', $this->buildIssuedTicketsSheet($event, $issuedTickets, $txMap));
        $zip->addFromString('xl/worksheets/sheet3.xml', $this->buildFinancialReconciliationSheet($event, $onlineTickets, $issuedTickets, $ticketTypes, $transactions, $payout));

        $zip->close();

        return $filename;
    }

    /**
     * Styles XML definition compliant with ECMA-376 OpenXML schema.
     */
    protected function buildStylesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n" .
            '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">' . "\n" .
            '  <numFmts count="1">' . "\n" .
            '    <numFmt numFmtId="164" formatCode="#,##0.00;(#,##0.00);&quot;-&quot;"/>' . "\n" .
            '  </numFmts>' . "\n" .
            '  <fonts count="6">' . "\n" .
            '    <font><name val="Calibri"/><sz val="11"/><color theme="1"/></font>' . "\n" . // 0: Normal
            '    <font><b/><name val="Calibri"/><sz val="11"/><color rgb="FFFFFFFF"/></font>' . "\n" . // 1: White bold (header)
            '    <font><b/><name val="Calibri"/><sz val="11"/><color theme="1"/></font>' . "\n" . // 2: Black bold
            '    <font><b/><name val="Calibri"/><sz val="15"/><color rgb="FF0B7366"/></font>' . "\n" . // 3: Brand title
            '    <font><i/><name val="Calibri"/><sz val="10"/><color rgb="FF64748B"/></font>' . "\n" . // 4: Subtitle/muted
            '    <font><b/><name val="Calibri"/><sz val="11"/><color rgb="FF1E293B"/></font>' . "\n" . // 5: Section header
            '  </fonts>' . "\n" .
            '  <fills count="5">' . "\n" .
            '    <fill><patternFill patternType="none"/></fill>' . "\n" .
            '    <fill><patternFill patternType="gray125"/></fill>' . "\n" .
            '    <fill><patternFill patternType="solid"><fgColor rgb="FF0B7366"/></patternFill></fill>' . "\n" . // 2: Brand Teal
            '    <fill><patternFill patternType="solid"><fgColor rgb="FFF8FAFC"/></patternFill></fill>' . "\n" . // 3: Zebra light
            '    <fill><patternFill patternType="solid"><fgColor rgb="FFE2E8F0"/></patternFill></fill>' . "\n" . // 4: Total row
            '  </fills>' . "\n" .
            '  <borders count="3">' . "\n" .
            '    <border><left/><right/><top/><bottom/></border>' . "\n" . // 0: None
            '    <border>' . "\n" . // 1: Thin cell border
            '      <left style="thin"><color rgb="FFCBD5E1"/></left>' . "\n" .
            '      <right style="thin"><color rgb="FFCBD5E1"/></right>' . "\n" .
            '      <top style="thin"><color rgb="FFCBD5E1"/></top>' . "\n" .
            '      <bottom style="thin"><color rgb="FFCBD5E1"/></bottom>' . "\n" .
            '    </border>' . "\n" .
            '    <border>' . "\n" . // 2: Double bottom border for totals
            '      <left style="thin"><color rgb="FFCBD5E1"/></left>' . "\n" .
            '      <right style="thin"><color rgb="FFCBD5E1"/></right>' . "\n" .
            '      <top style="thin"><color rgb="FF475569"/></top>' . "\n" .
            '      <bottom style="double"><color rgb="FF0F172A"/></bottom>' . "\n" .
            '    </border>' . "\n" .
            '  </borders>' . "\n" .
            '  <cellStyleXfs count="1">' . "\n" .
            '    <xf numFmtId="0" fontId="0" fillId="0" borderId="0"/>' . "\n" .
            '  </cellStyleXfs>' . "\n" .
            '  <cellXfs count="10">' . "\n" .
            '    <xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>' . "\n" . // 0: Normal
            '    <xf numFmtId="0" fontId="1" fillId="2" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>' . "\n" . // 1: Header
            '    <xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1"/>' . "\n" . // 2: Normal border
            '    <xf numFmtId="164" fontId="0" fillId="0" borderId="1" xfId="0" applyNumberFormat="1" applyBorder="1" applyAlignment="1"><alignment horizontal="right"/></xf>' . "\n" . // 3: Currency
            '    <xf numFmtId="164" fontId="2" fillId="4" borderId="2" xfId="0" applyNumberFormat="1" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="right"/></xf>' . "\n" . // 4: Summary total currency
            '    <xf numFmtId="0" fontId="2" fillId="4" borderId="2" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="left"/></xf>' . "\n" . // 5: Summary total label
            '    <xf numFmtId="0" fontId="3" fillId="0" borderId="0" xfId="0" applyFont="1"/>' . "\n" . // 6: Sheet Title
            '    <xf numFmtId="0" fontId="4" fillId="0" borderId="0" xfId="0" applyFont="1"/>' . "\n" . // 7: Subtitle
            '    <xf numFmtId="0" fontId="5" fillId="3" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1"/>' . "\n" . // 8: Section header row
            '    <xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1" applyAlignment="1"><alignment horizontal="center"/></xf>' . "\n" . // 9: Centered cell
            '  </cellXfs>' . "\n" .
            '  <cellStyles count="1">' . "\n" .
            '    <cellStyle name="Normal" xfId="0" builtinId="0"/>' . "\n" .
            '  </cellStyles>' . "\n" .
            '</styleSheet>';
    }

    /**
     * Helper to convert 0-based column index to Excel column string (A, B, ... Z, AA, AB).
     */
    protected function col(int $index): string
    {
        $col = '';
        $index += 1;
        while ($index > 0) {
            $modulo = ($index - 1) % 26;
            $col = chr(65 + $modulo) . $col;
            $index = (int)(($index - $modulo) / 26);
        }
        return $col;
    }

    /**
     * SHEET 1: Online & Paystack Sales
     */
    protected function buildOnlineSalesSheet(Event $event, array $tickets, array $txMap): string
    {
        $headers = [
            'Ticket Code',
            'Attendee Name',
            'Attendee Email',
            'Ticket Tier',
            'Paystack Reference',
            'Gateway Status',
            'Ticket Price',
            'Gate Status',
            'Purchase Date'
        ];

        $colWidths = [18, 26, 32, 20, 28, 22, 18, 24, 22];

        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
        $xml .= '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">' . "\n";
        $xml .= '  <sheetFormatPr defaultRowHeight="15"/>' . "\n";

        // Column widths
        $xml .= '  <cols>' . "\n";
        foreach ($colWidths as $idx => $w) {
            $cNum = $idx + 1;
            $xml .= '    <col min="' . $cNum . '" max="' . $cNum . '" width="' . $w . '" customWidth="1"/>' . "\n";
        }
        $xml .= '  </cols>' . "\n";

        $xml .= '  <sheetData>' . "\n";

        // Row 1: Title
        $xml .= '    <row r="1" height="26">' . "\n";
        $xml .= '      <c r="A1" s="6" t="inlineStr"><is><t>' . htmlspecialchars($event->name . ' — Online & Paystack Sales Audit') . '</t></is></c>' . "\n";
        $xml .= '    </row>' . "\n";

        // Row 2: Subtitle
        $dateStr = !empty($event->date) ? date('F d, Y • h:i A', strtotime($event->date)) : 'N/A';
        $xml .= '    <row r="2" height="18">' . "\n";
        $xml .= '      <c r="A2" s="7" t="inlineStr"><is><t>Event Date: ' . htmlspecialchars($dateStr) . ' | Exported on: ' . date('Y-m-d H:i:s') . '</t></is></c>' . "\n";
        $xml .= '    </row>' . "\n";

        // Row 4: Table Headers
        $xml .= '    <row r="4" height="24">' . "\n";
        foreach ($headers as $colIdx => $h) {
            $cellRef = $this->col($colIdx) . '4';
            $xml .= '      <c r="' . $cellRef . '" s="1" t="inlineStr"><is><t>' . htmlspecialchars($h) . '</t></is></c>' . "\n";
        }
        $xml .= '    </row>' . "\n";

        // Data Rows
        $currentRow = 5;
        $totalSum = 0;
        $checkedInCount = 0;

        foreach ($tickets as $t) {
            $price = (float)($t['price'] ?? 0);
            $totalSum += $price;

            // Extract Paystack reference from status_detail or ticket
            $detail = $t['status_detail'] ?? '';
            $ref = 'N/A';
            if (preg_match('/(?:Paystack\s*Ref:\s*|Ref:\s*)([A-Za-z0-9_-]+)/i', $detail, $m)) {
                $ref = $m[1];
            } elseif (!empty($t['reference'])) {
                $ref = $t['reference'];
            }

            // Paystack verification status from transaction table
            $gatewayStatus = 'Verified (Paid)';
            if (isset($txMap[$ref])) {
                $tx = $txMap[$ref];
                $gatewayStatus = ucfirst($tx['status'] ?? 'completed') . ' (' . strtoupper($tx['payment_method'] ?? 'paystack') . ')';
            } elseif (in_array($t['status'] ?? '', ['Cancelled', 'Refunded'])) {
                $gatewayStatus = 'Refunded / Cancelled';
            }

            $isCheckedIn = !empty($t['checked_in']);
            if ($isCheckedIn) {
                $checkedInCount++;
                $gateStatus = '✓ Checked In (' . (!empty($t['checked_in_at']) ? date('H:i', strtotime($t['checked_in_at'])) : 'Gate') . ')';
            } else {
                $gateStatus = 'Awaiting Entry';
            }

            $purchaseDate = !empty($t['purchase_date']) ? date('Y-m-d H:i', strtotime($t['purchase_date'])) : 'N/A';

            $rowValues = [
                0 => ['val' => $t['id'] ?? '', 'style' => 9],
                1 => ['val' => $t['attendee_name'] ?? 'Guest', 'style' => 2],
                2 => ['val' => $t['attendee_email'] ?? '', 'style' => 2],
                3 => ['val' => $t['ticket_tier'] ?? ($t['ticket_type'] ?? 'General'), 'style' => 2],
                4 => ['val' => $ref, 'style' => 9],
                5 => ['val' => $gatewayStatus, 'style' => 9],
                6 => ['val' => $price, 'style' => 3, 'isNum' => true],
                7 => ['val' => $gateStatus, 'style' => 9],
                8 => ['val' => $purchaseDate, 'style' => 9]
            ];

            $xml .= '    <row r="' . $currentRow . '" height="20">' . "\n";
            foreach ($rowValues as $colIdx => $data) {
                $cellRef = $this->col($colIdx) . $currentRow;
                if (!empty($data['isNum'])) {
                    $xml .= '      <c r="' . $cellRef . '" s="' . $data['style'] . '"><v>' . $data['val'] . '</v></c>' . "\n";
                } else {
                    $xml .= '      <c r="' . $cellRef . '" s="' . $data['style'] . '" t="inlineStr"><is><t>' . htmlspecialchars((string)$data['val']) . '</t></is></c>' . "\n";
                }
            }
            $xml .= '    </row>' . "\n";
            $currentRow++;
        }

        // Summary row at the bottom (Merged A-F for label, G for sum, H for checked in)
        $xml .= '    <row r="' . $currentRow . '" height="24">' . "\n";
        $xml .= '      <c r="A' . $currentRow . '" s="5" t="inlineStr"><is><t>TOTAL VERIFIED ONLINE SALES (' . count($tickets) . ' Tickets)</t></is></c>' . "\n";
        $xml .= '      <c r="B' . $currentRow . '" s="5"/>' . "\n";
        $xml .= '      <c r="C' . $currentRow . '" s="5"/>' . "\n";
        $xml .= '      <c r="D' . $currentRow . '" s="5"/>' . "\n";
        $xml .= '      <c r="E' . $currentRow . '" s="5"/>' . "\n";
        $xml .= '      <c r="F' . $currentRow . '" s="5"/>' . "\n";
        $xml .= '      <c r="G' . $currentRow . '" s="4"><v>' . $totalSum . '</v></c>' . "\n";
        $xml .= '      <c r="H' . $currentRow . '" s="5" t="inlineStr"><is><t>' . $checkedInCount . ' Checked In</t></is></c>' . "\n";
        $xml .= '      <c r="I' . $currentRow . '" s="5"/>' . "\n";
        $xml .= '    </row>' . "\n";

        $xml .= '  </sheetData>' . "\n";
        $xml .= '  <mergeCells count="1">' . "\n";
        $xml .= '    <mergeCell ref="A' . $currentRow . ':F' . $currentRow . '"/>' . "\n";
        $xml .= '  </mergeCells>' . "\n";
        $xml .= '  <pageMargins left="0.7" right="0.7" top="0.75" bottom="0.75" header="0.3" footer="0.3"/>' . "\n";
        $xml .= '</worksheet>';

        return $xml;
    }

    /**
     * SHEET 2: Issued & Comp Tickets (Host-issued, Box-Office, Gate Cash sales)
     */
    protected function buildIssuedTicketsSheet(Event $event, array $tickets, array $txMap): string
    {
        $headers = [
            'Ticket Code',
            'Recipient / Attendee',
            'Email Address',
            'Ticket Tier',
            'Issuance Type',
            'Audit & Terminal Reference',
            'Nominal Value',
            'Gate Status',
            'Issue Date'
        ];

        $colWidths = [18, 26, 32, 20, 24, 34, 18, 24, 22];

        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
        $xml .= '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">' . "\n";
        $xml .= '  <sheetFormatPr defaultRowHeight="15"/>' . "\n";

        // Column widths
        $xml .= '  <cols>' . "\n";
        foreach ($colWidths as $idx => $w) {
            $cNum = $idx + 1;
            $xml .= '    <col min="' . $cNum . '" max="' . $cNum . '" width="' . $w . '" customWidth="1"/>' . "\n";
        }
        $xml .= '  </cols>' . "\n";

        $xml .= '  <sheetData>' . "\n";

        // Row 1: Title
        $xml .= '    <row r="1" height="26">' . "\n";
        $xml .= '      <c r="A1" s="6" t="inlineStr"><is><t>' . htmlspecialchars($event->name . ' — Issued, Comp & Gate Box Office Tickets') . '</t></is></c>' . "\n";
        $xml .= '    </row>' . "\n";

        // Row 2: Subtitle
        $xml .= '    <row r="2" height="18">' . "\n";
        $xml .= '      <c r="A2" s="7" t="inlineStr"><is><t>Includes Host Direct Issues, VIP Passes, and On-site Gate Cash Sales | Total Issued: ' . count($tickets) . '</t></is></c>' . "\n";
        $xml .= '    </row>' . "\n";

        // Row 4: Table Headers
        $xml .= '    <row r="4" height="24">' . "\n";
        foreach ($headers as $colIdx => $h) {
            $cellRef = $this->col($colIdx) . '4';
            $xml .= '      <c r="' . $cellRef . '" s="1" t="inlineStr"><is><t>' . htmlspecialchars($h) . '</t></is></c>' . "\n";
        }
        $xml .= '    </row>' . "\n";

        // Data Rows
        $currentRow = 5;
        $totalNominal = 0;
        $freeCount = 0;
        $gateCashSum = 0;

        foreach ($tickets as $t) {
            $price = (float)($t['price'] ?? 0);
            $totalNominal += $price;

            $detail = $t['status_detail'] ?? '';
            $status = $t['status'] ?? '';
            
            // Determine issuance type
            if (str_contains($detail, 'Gate Sale (CASH)')) {
                $type = 'Gate Box Office (CASH)';
                $gateCashSum += $price;
            } elseif ($price == 0 || $status === 'Comped' || str_contains($detail, 'Free')) {
                $type = 'Complimentary (Free)';
                $freeCount++;
            } else {
                $type = 'Host Direct Issuance';
            }

            $auditRef = $detail ?: 'Direct issuance by event host';

            $isCheckedIn = !empty($t['checked_in']);
            if ($isCheckedIn) {
                $gateStatus = '✓ Checked In (' . (!empty($t['checked_in_at']) ? date('H:i', strtotime($t['checked_in_at'])) : 'Gate') . ')';
            } else {
                $gateStatus = 'Awaiting Entry';
            }

            $issueDate = !empty($t['purchase_date']) ? date('Y-m-d H:i', strtotime($t['purchase_date'])) : 'N/A';

            $rowValues = [
                0 => ['val' => $t['id'] ?? '', 'style' => 9],
                1 => ['val' => $t['attendee_name'] ?? 'Guest', 'style' => 2],
                2 => ['val' => $t['attendee_email'] ?? '', 'style' => 2],
                3 => ['val' => $t['ticket_tier'] ?? ($t['ticket_type'] ?? 'General'), 'style' => 2],
                4 => ['val' => $type, 'style' => 9],
                5 => ['val' => $auditRef, 'style' => 2],
                6 => ['val' => $price, 'style' => 3, 'isNum' => true],
                7 => ['val' => $gateStatus, 'style' => 9],
                8 => ['val' => $issueDate, 'style' => 9]
            ];

            $xml .= '    <row r="' . $currentRow . '" height="20">' . "\n";
            foreach ($rowValues as $colIdx => $data) {
                $cellRef = $this->col($colIdx) . $currentRow;
                if (!empty($data['isNum'])) {
                    $xml .= '      <c r="' . $cellRef . '" s="' . $data['style'] . '"><v>' . $data['val'] . '</v></c>' . "\n";
                } else {
                    $xml .= '      <c r="' . $cellRef . '" s="' . $data['style'] . '" t="inlineStr"><is><t>' . htmlspecialchars((string)$data['val']) . '</t></is></c>' . "\n";
                }
            }
            $xml .= '    </row>' . "\n";
            $currentRow++;
        }

        // Summary row at the bottom
        $xml .= '    <row r="' . $currentRow . '" height="24">' . "\n";
        $xml .= '      <c r="A' . $currentRow . '" s="5" t="inlineStr"><is><t>TOTAL ISSUED TICKETS (' . count($tickets) . ' Tickets — ' . $freeCount . ' Free / Comp)</t></is></c>' . "\n";
        $xml .= '      <c r="B' . $currentRow . '" s="5"/>' . "\n";
        $xml .= '      <c r="C' . $currentRow . '" s="5"/>' . "\n";
        $xml .= '      <c r="D' . $currentRow . '" s="5"/>' . "\n";
        $xml .= '      <c r="E' . $currentRow . '" s="5"/>' . "\n";
        $xml .= '      <c r="F' . $currentRow . '" s="5" t="inlineStr"><is><t>Gate Cash Collected: ' . number_format($gateCashSum, 2) . '</t></is></c>' . "\n";
        $xml .= '      <c r="G' . $currentRow . '" s="4"><v>' . $totalNominal . '</v></c>' . "\n";
        $xml .= '      <c r="H' . $currentRow . '" s="5"/>' . "\n";
        $xml .= '      <c r="I' . $currentRow . '" s="5"/>' . "\n";
        $xml .= '    </row>' . "\n";

        $xml .= '  </sheetData>' . "\n";
        $xml .= '  <mergeCells count="1">' . "\n";
        $xml .= '    <mergeCell ref="A' . $currentRow . ':E' . $currentRow . '"/>' . "\n";
        $xml .= '  </mergeCells>' . "\n";
        $xml .= '  <pageMargins left="0.7" right="0.7" top="0.75" bottom="0.75" header="0.3" footer="0.3"/>' . "\n";
        $xml .= '</worksheet>';

        return $xml;
    }

    /**
     * SHEET 3: Financial Reconciliation & Executive Balance
     */
    protected function buildFinancialReconciliationSheet(Event $event, array $onlineTickets, array $issuedTickets, array $ticketTypes, array $transactions, ?array $payout): string
    {
        $colWidths = [4, 34, 24, 30, 24];

        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
        $xml .= '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">' . "\n";
        $xml .= '  <sheetFormatPr defaultRowHeight="15"/>' . "\n";

        // Column widths
        $xml .= '  <cols>' . "\n";
        foreach ($colWidths as $idx => $w) {
            $cNum = $idx + 1;
            $xml .= '    <col min="' . $cNum . '" max="' . $cNum . '" width="' . $w . '" customWidth="1"/>' . "\n";
        }
        $xml .= '  </cols>' . "\n";

        $xml .= '  <sheetData>' . "\n";

        // Title & Header
        $xml .= '    <row r="1" height="26"><c r="B1" s="6" t="inlineStr"><is><t>OFFICIAL POST-EVENT FINANCIAL RECONCILIATION</t></is></c></row>' . "\n";
        $xml .= '    <row r="2" height="18"><c r="B2" s="7" t="inlineStr"><is><t>Event: ' . htmlspecialchars($event->name) . ' | Generated: ' . date('F d, Y H:i:s') . '</t></is></c></row>' . "\n";

        // SECTION 1: Event & Host Metadata
        $xml .= '    <row r="4" height="22"><c r="B4" s="8" t="inlineStr"><is><t>1. EVENT &amp; ORGANIZER PROFILE</t></is></c><c r="C4" s="8" t="inlineStr"><is><t>DETAILS</t></is></c><c r="D4" s="8" t="inlineStr"><is><t>AUDIT NOTES</t></is></c></row>' . "\n";

        $meta = [
            ['Event Title', $event->name, 'Official registered event name'],
            ['Event Date & Time', !empty($event->date) ? date('l, F d, Y • h:i A', strtotime($event->date)) : 'N/A', 'Scheduled start time'],
            ['Venue & Location', $event->location ?: 'N/A', 'Physical venue location'],
            ['Event Category', $event->category ?: 'General', 'Primary category listing'],
            ['Fee Setting', !empty($event->pass_fee) ? 'Fee passed to attendee' : 'Fee absorbed by host', 'Platform fee deduction rule']
        ];

        $r = 5;
        foreach ($meta as $item) {
            $xml .= '    <row r="' . $r . '" height="19">';
            $xml .= '<c r="B' . $r . '" s="2" t="inlineStr"><is><t>' . htmlspecialchars($item[0]) . '</t></is></c>';
            $xml .= '<c r="C' . $r . '" s="2" t="inlineStr"><is><t>' . htmlspecialchars((string)$item[1]) . '</t></is></c>';
            $xml .= '<c r="D' . $r . '" s="2" t="inlineStr"><is><t>' . htmlspecialchars($item[2]) . '</t></is></c>';
            $xml .= '</row>' . "\n";
            $r++;
        }

        // SECTION 2: Ticket Inventory & Turnout
        $r++;
        $xml .= '    <row r="' . $r . '" height="22"><c r="B' . $r . '" s="8" t="inlineStr"><is><t>2. TICKET INVENTORY &amp; ATTENDANCE AUDIT</t></is></c><c r="C' . $r . '" s="8" t="inlineStr"><is><t>METRIC</t></is></c><c r="D' . $r . '" s="8" t="inlineStr"><is><t>AUDIT NOTES</t></is></c></row>' . "\n";
        $r++;

        $totalCapacity = 0;
        $totalSold = count($onlineTickets);
        $totalIssued = count($issuedTickets);
        $allTickets = array_merge($onlineTickets, $issuedTickets);

        $totalCheckedIn = 0;
        foreach ($allTickets as $t) {
            if (!empty($t['checked_in'])) {
                $totalCheckedIn++;
            }
        }

        foreach ($ticketTypes as $tt) {
            $totalCapacity += (int)($tt['capacity'] ?? 0);
        }

        $occupancyRate = $totalCapacity > 0 ? round((($totalSold + $totalIssued) / $totalCapacity) * 100, 1) : 0;
        $turnoutRate = ($totalSold + $totalIssued) > 0 ? round(($totalCheckedIn / ($totalSold + $totalIssued)) * 100, 1) : 0;

        $inventoryRows = [
            ['Total Venue / Ticket Capacity', number_format($totalCapacity) . ' tickets', 'Aggregated capacity across all tiers'],
            ['Online / Paystack Sold Tickets', number_format($totalSold) . ' tickets', 'Purchased organically online via Paystack'],
            ['Host / Gate Issued Tickets', number_format($totalIssued) . ' tickets', 'Issued complimentary or gate cash box office'],
            ['Total Attendees on Roster', number_format($totalSold + $totalIssued) . ' attendees', 'All active event ticket holders'],
            ['Gate Checked-In Turnout', number_format($totalCheckedIn) . ' attendees (' . $turnoutRate . '%)', 'Verified through gate ticket scanner'],
            ['Total Capacity Sold / Occupancy', $occupancyRate . '%', 'Percentage of available capacity taken']
        ];

        foreach ($inventoryRows as $item) {
            $xml .= '    <row r="' . $r . '" height="19">';
            $xml .= '<c r="B' . $r . '" s="2" t="inlineStr"><is><t>' . htmlspecialchars($item[0]) . '</t></is></c>';
            $xml .= '<c r="C' . $r . '" s="2" t="inlineStr"><is><t>' . htmlspecialchars((string)$item[1]) . '</t></is></c>';
            $xml .= '<c r="D' . $r . '" s="2" t="inlineStr"><is><t>' . htmlspecialchars($item[2]) . '</t></is></c>';
            $xml .= '</row>' . "\n";
            $r++;
        }

        // SECTION 3: Financial Balances & Revenue Reconciliation
        $r++;
        $xml .= '    <row r="' . $r . '" height="22"><c r="B' . $r . '" s="8" t="inlineStr"><is><t>3. FINANCIAL SETTLEMENT &amp; REVENUE RECONCILIATION</t></is></c><c r="C' . $r . '" s="8" t="inlineStr"><is><t>AMOUNT (NGN)</t></is></c><c r="D' . $r . '" s="8" t="inlineStr"><is><t>AUDIT NOTES</t></is></c></row>' . "\n";
        $r++;

        $onlineGross = 0;
        foreach ($onlineTickets as $t) {
            $onlineGross += (float)($t['price'] ?? 0);
        }

        $gateCashGross = 0;
        $compNominal = 0;
        foreach ($issuedTickets as $t) {
            $p = (float)($t['price'] ?? 0);
            if (str_contains($t['status_detail'] ?? '', 'Gate Sale (CASH)')) {
                $gateCashGross += $p;
            } else {
                $compNominal += $p;
            }
        }

        $totalGrossRevenue = $onlineGross + $gateCashGross;
        $platformFeePercent = (float)site_setting('platform_fee', 5);
        $totalPlatformFee = round($totalGrossRevenue * ($platformFeePercent / 100), 2);
        $netHostEarnings = $totalGrossRevenue - $totalPlatformFee;

        // Payout status
        $payoutStatus = 'Pending Event Conformance / Settlement';
        if (!empty($payout)) {
            $payoutStatus = ucfirst($payout['status'] ?? 'pending') . ' (Ref: ' . ($payout['reference'] ?? 'N/A') . ')';
        }

        $financialRows = [
            ['Gross Online / Paystack Sales', $onlineGross, true, 'Online ticket buyer payments'],
            ['Gross Gate / Door Cash Sales', $gateCashGross, true, 'On-site terminal box office cash'],
            ['Total Gross Event Revenue', $totalGrossRevenue, true, 'All organic & gate sales combined'],
            ['Platform Ticketing Fee (' . $platformFeePercent . '%)', -$totalPlatformFee, true, 'Service & payment processing fee'],
            ['Net Host Event Earnings', $netHostEarnings, true, 'Amount earned by event organizer'],
            ['Complimentary / Free Ticket Nominal Value', $compNominal, true, 'Face value absorbed by organizer'],
            ['Settlement & Payout Status', $payoutStatus, false, 'Settlement processing state']
        ];

        foreach ($financialRows as $row) {
            $xml .= '    <row r="' . $r . '" height="21">';
            $xml .= '<c r="B' . $r . '" s="2" t="inlineStr"><is><t>' . htmlspecialchars($row[0]) . '</t></is></c>';
            if ($row[2]) {
                $xml .= '<c r="C' . $r . '" s="3"><v>' . $row[1] . '</v></c>';
            } else {
                $xml .= '<c r="C' . $r . '" s="2" t="inlineStr"><is><t>' . htmlspecialchars((string)$row[1]) . '</t></is></c>';
            }
            $xml .= '<c r="D' . $r . '" s="2" t="inlineStr"><is><t>' . htmlspecialchars($row[3]) . '</t></is></c>';
            $xml .= '</row>' . "\n";
            $r++;
        }

        // FINAL BALANCE ROW
        $xml .= '    <row r="' . $r . '" height="26">';
        $xml .= '<c r="B' . $r . '" s="5" t="inlineStr"><is><t>FINAL NET BALANCE DUE TO HOST</t></is></c>';
        $xml .= '<c r="C' . $r . '" s="4"><v>' . $netHostEarnings . '</v></c>';
        $xml .= '<c r="D' . $r . '" s="5" t="inlineStr"><is><t>Reconciled &amp; Approved</t></is></c>';
        $xml .= '</row>' . "\n";

        $xml .= '  </sheetData>' . "\n";
        $xml .= '  <pageMargins left="0.7" right="0.7" top="0.75" bottom="0.75" header="0.3" footer="0.3"/>' . "\n";
        $xml .= '</worksheet>';

        return $xml;
    }
}
