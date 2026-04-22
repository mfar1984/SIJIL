<?php

namespace App\Exports;

use App\Models\Participant;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Database\Eloquent\Builder;

class ParticipantsExport implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $query;

    public function __construct(Builder $query)
    {
        $this->query = $query;
    }

    /**
     * @return Builder
     */
    public function query()
    {
        return $this->query->with('event');
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'No',
            'Name',
            'Email',
            'Phone',
            'IC / Passport',
            'Organization',
            'Event',
            'Status',
            'Country',
            'State',
            'City',
            'Postcode',
            'Address',
            'Registration Date',
        ];
    }

    /**
     * @param Participant $participant
     * @return array
     */
    public function map($participant): array
    {
        static $rowNumber = 0;
        $rowNumber++;

        return [
            $rowNumber,
            $participant->name,
            $participant->email,
            $participant->phone,
            $participant->identity_card ?: $participant->passport_no,
            $participant->organization,
            $participant->event ? $participant->event->name : 'N/A',
            ucfirst($participant->status ?? 'registered'),
            $participant->country,
            $participant->state,
            $participant->city,
            $participant->postcode,
            $participant->address,
            $participant->created_at ? $participant->created_at->format('d/m/Y H:i') : '',
        ];
    }

    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold header
            1 => [
                'font' => ['bold' => true, 'size' => 12],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '004aad']
                ],
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            ],
        ];
    }
}
