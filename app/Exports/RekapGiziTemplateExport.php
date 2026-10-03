<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Template import rekap gizi dalam format Excel (.xlsx).
 *
 * Header dibuat persis agar cocok dengan RekapGiziImport:
 * NO | DESA | STUNTING | GIZI KURANG | BB-KURANG | GIZI LEBIH | GIZI BAIK
 *
 * Periode tidak ada di template — memakai periode default dari modal import.
 */
class RekapGiziTemplateExport implements
    FromCollection,
    WithHeadings,
    WithStyles,
    ShouldAutoSize,
    WithTitle,
    WithEvents
{
    /** @var list<string> */
    protected array $desaNames;

    /**
     * @param  list<string>  $desaNames
     */
    public function __construct(array $desaNames = [])
    {
        $this->desaNames = array_values($desaNames);
    }

    public function headings(): array
    {
        return [
            'NO',
            'DESA',
            'STUNTING',
            'GIZI KURANG',
            'BB-KURANG',
            'GIZI LEBIH',
            'GIZI BAIK',
        ];
    }

    public function collection(): Collection
    {
        // Prefill satu baris per desa agar pengguna tinggal mengisi angka.
        if (! empty($this->desaNames)) {
            return collect($this->desaNames)->map(fn (string $nama, int $i) => [
                $i + 1,
                $nama,
                '',
                '',
                '',
                '',
                '',
            ]);
        }

        // Fallback bila daftar desa kosong: contoh 2 baris.
        return collect([
            [1, 'Lamedai', 18, '', '', '', ''],
            [2, 'Lalonggolosua', 9, 5, 12, 3, 50],
        ]);
    }

    public function title(): string
    {
        return 'Template';
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1B7A4D']],
                'alignment' => ['horizontal' => 'center', 'vertical' => 'center', 'wrapText' => true],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastRow = max(2, count($this->desaNames ?: [1, 2]) + 1);
                $lastCol = 'G';

                // Baris header lebih tinggi + border seluruh tabel.
                $sheet->getRowDimension(1)->setRowHeight(28);
                $sheet->getStyle("A1:{$lastCol}{$lastRow}")->applyFromArray([
                    'borders' => [
                        'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'B0BEC5']],
                    ],
                ]);

                // Freeze header + filter.
                $sheet->freezePane('A2');
                $sheet->setAutoFilter("A1:{$lastCol}{$lastRow}");

                // Validasi angka: bilangan bulat >= 0 untuk kolom C..G.
                foreach (range('C', 'G') as $col) {
                    for ($row = 2; $row <= $lastRow; $row++) {
                        $validation = $sheet->getCell("{$col}{$row}")->getDataValidation();
                        $validation->setType(DataValidation::TYPE_WHOLE);
                        $validation->setErrorStyle(DataValidation::STYLE_STOP);
                        $validation->setOperator(DataValidation::OPERATOR_GREATERTHANOREQUAL);
                        $validation->setFormula1('0');
                        $validation->setAllowBlank(true);
                        $validation->setShowInputMessage(true);
                        $validation->setShowErrorMessage(true);
                        $validation->setErrorTitle('Nilai tidak valid');
                        $validation->setError('Isi dengan bilangan bulat >= 0, atau kosongkan jika data belum ada.');
                        $validation->setPromptTitle('Petunjuk');
                        $validation->setPrompt('Isi bilangan bulat >= 0. Kosongkan bila data belum tersedia (akan tersimpan NULL).');
                    }
                }
            },
        ];
    }
}
