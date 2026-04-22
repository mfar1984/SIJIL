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
            'IC Number',
            'Passport Number',
            'Gender',
            'Date of Birth',
            'Race',
            'Organization',
            'Job Title',
            'Address Line 1',
            'Address Line 2',
            'City',
            'State',
            'Postcode',
            'Country',
            'Event',
            'Event Organizer',
            'Event Date',
            'Event Location',
            'Status',
            'Registration Date',
            'Notes',
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
            $participant->formatted_phone ?? $participant->phone,
            $participant->identity_card,
            $participant->passport_no,
            $participant->gender ? ucfirst($participant->gender) : '',
            $participant->date_of_birth ? $participant->date_of_birth->format('d/m/Y') : '',
            $participant->race,
            $participant->organization,
            $participant->job_title,
            $participant->address1,
            $participant->address2,
            $participant->city,
            $participant->state,
            $participant->postcode,
            $participant->country,
            $participant->event ? $participant->event->name : '',
            $participant->event ? $participant->event->organizer : '',
            $participant->event ? $participant->event->start_date->format('d/m/Y') . ' - ' . $participant->event->end_date->format('d/m/Y') : '',
            $participant->event ? $participant->event->location : '',
            ucfirst($participant->status ?? 'registered'),
            $participant->registration_date ? $participant->registration_date->format('d/m/Y H:i') : ($participant->created_at ? $participant->created_at->format('d/m/Y H:i') : ''),
            $participant->notes,
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
