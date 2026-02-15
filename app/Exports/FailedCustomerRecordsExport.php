<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class FailedCustomerRecordsExport implements FromArray, WithHeadings, WithStyles, WithTitle
{
    protected $failedRecords;

    public function __construct($failedRecords)
    {
        $this->failedRecords = $failedRecords;
    }

    public function title(): string
    {
        return 'Failed Records';
    }

    public function headings(): array
    {
        return [
            'Row Number',
            'Name',
            'Phone1',
            'Phone2',
            'DOB',
            'Sex',
            'Region',
            'District',
            'Work',
            'Work Address',
            'ID Type',
            'ID Number',
            'Relation',
            'Description',
            'Error Reason'
        ];
    }

    public function array(): array
    {
        $data = [];
        
        foreach ($this->failedRecords as $record) {
            $rowData = $record['data'] ?? [];
            
            // Handle region - could be name or ID
            $region = $rowData['region'] ?? '';
            if (empty($region) && !empty($rowData['region_id'])) {
                $regionObj = \App\Models\Region::find($rowData['region_id']);
                $region = $regionObj ? $regionObj->name : $rowData['region_id'];
            }
            
            // Handle district - could be name or ID
            $district = $rowData['district'] ?? '';
            if (empty($district) && !empty($rowData['district_id'])) {
                $districtObj = \App\Models\District::find($rowData['district_id']);
                $district = $districtObj ? $districtObj->name : $rowData['district_id'];
            }
            
            $data[] = [
                $record['row_number'] ?? '',
                $rowData['name'] ?? '',
                $rowData['phone1'] ?? '',
                $rowData['phone2'] ?? '',
                $rowData['dob'] ?? '',
                $rowData['sex'] ?? '',
                $region,
                $district,
                $rowData['work'] ?? '',
                $rowData['workaddress'] ?? '',
                $rowData['idtype'] ?? '',
                $rowData['idnumber'] ?? '',
                $rowData['relation'] ?? '',
                $rowData['description'] ?? '',
                $record['error'] ?? 'Unknown error'
            ];
        }
        
        return $data;
    }

    public function styles(Worksheet $sheet)
    {
        // Style header row
        $sheet->getStyle('A1:O1')->getFont()->setBold(true);
        $sheet->getStyle('A1:O1')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFFF0000'); // Red background for failed records
        
        // Set column widths
        $sheet->getColumnDimension('A')->setWidth(12); // Row Number
        $sheet->getColumnDimension('B')->setWidth(25); // Name
        $sheet->getColumnDimension('C')->setWidth(15); // Phone1
        $sheet->getColumnDimension('D')->setWidth(15); // Phone2
        $sheet->getColumnDimension('E')->setWidth(15); // DOB
        $sheet->getColumnDimension('F')->setWidth(10); // Sex
        $sheet->getColumnDimension('G')->setWidth(20); // Region
        $sheet->getColumnDimension('H')->setWidth(20); // District
        $sheet->getColumnDimension('I')->setWidth(20); // Work
        $sheet->getColumnDimension('J')->setWidth(30); // Work Address
        $sheet->getColumnDimension('K')->setWidth(15); // ID Type
        $sheet->getColumnDimension('L')->setWidth(20); // ID Number
        $sheet->getColumnDimension('M')->setWidth(15); // Relation
        $sheet->getColumnDimension('N')->setWidth(30); // Description
        $sheet->getColumnDimension('O')->setWidth(50); // Error Reason

        return [];
    }
}
