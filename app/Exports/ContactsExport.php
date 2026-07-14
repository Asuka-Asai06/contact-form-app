<?php

namespace App\Exports;

use App\Models\Contact;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ContactsExport implements FromCollection, WithCustomCsvSettings, WithHeadings
{
    public function __construct(
        private array $filters
    ) {}

    public function getFilters(): array
    {
        return $this->filters;
    }

    public function collection()
    {
        return Contact::with([
            'category',
            'tags',
        ])
            ->filter($this->filters)
            ->latest()
            ->get()
            ->map(function ($contact) {
                return [
                    $contact->id,
                    $contact->first_name.$contact->last_name,
                    $contact->gender_label,
                    "=\"{$contact->email}\"",
                    "=\"{$contact->tel}\"",
                    "=\"{$contact->address}\"",
                    $contact->building,
                    $contact->category->content,
                    $contact->detail,
                    $contact->created_at,
                ];
            });
    }

    public function headings(): array
    {
        return [
            'ID',
            '氏名',
            '性別',
            'メール',
            '電話',
            '住所',
            '建物',
            'カテゴリ',
            '内容',
            '作成日時',
        ];
    }

    public function getCsvSettings(): array
    {
        return [
            'use_bom' => true,
        ];
    }
}
