<?php

namespace App\Exports;

use App\Models\Region;
use App\Models\District;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\NamedRange;

class CustomerImportTemplateExport implements FromArray, WithHeadings, WithStyles, WithEvents, WithTitle
{
    public function title(): string
    {
        return 'Customer Data';
    }

    public function headings(): array
    {
        return [
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
            'Description'
        ];
    }

    public function array(): array
    {
        // Return sample data rows
        return [
            [
                'John Doe',
                '0712345678',
                '0755123456',
                '1990-01-15',
                'M',
                'Dar es Salaam',
                'Ilala',
                'Teacher',
                'ABC School, Dar es Salaam',
                'National ID',
                '12345678-12345-12345-12',
                'Spouse',
                'Sample customer'
            ],
            [
                'Jane Smith',
                '0723456789',
                '',
                '1985-05-20',
                'F',
                'Dar es Salaam',
                'Kinondoni',
                'Nurse',
                'City Hospital',
                'Voter Registration',
                '12345-123456-123',
                'Parent',
                'Another sample'
            ],
            [
                'Peter Johnson',
                '0734567890',
                '',
                '1988-03-10',
                'M',
                'Arusha',
                'Arusha City',
                'Driver',
                'Transport Company',
                'License',
                '987654321',
                '',
                'Sample with License'
            ]
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Style header row
        $sheet->getStyle('A1:M1')->getFont()->setBold(true);
        $sheet->getStyle('A1:M1')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFE0E0E0');
        
        // Set column widths
        $sheet->getColumnDimension('A')->setWidth(25);
        $sheet->getColumnDimension('B')->setWidth(15);
        $sheet->getColumnDimension('C')->setWidth(15);
        $sheet->getColumnDimension('D')->setWidth(20);
        $sheet->getColumnDimension('E')->setWidth(12);
        $sheet->getColumnDimension('F')->setWidth(20);
        $sheet->getColumnDimension('G')->setWidth(20);
        $sheet->getColumnDimension('H')->setWidth(20);
        $sheet->getColumnDimension('I')->setWidth(30);
        $sheet->getColumnDimension('J')->setWidth(15);
        $sheet->getColumnDimension('K')->setWidth(15);
        $sheet->getColumnDimension('L')->setWidth(15);
        $sheet->getColumnDimension('M')->setWidth(30);

        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow() + 100; // Allow up to 100 data rows

                // Get regions and districts
                $regions = Region::orderBy('name')->pluck('name')->toArray();
                $districtsByRegion = [];
                
                foreach ($regions as $regionName) {
                    $region = Region::where('name', $regionName)->first();
                    if ($region) {
                        $districts = District::where('region_id', $region->id)
                            ->orderBy('name')
                            ->pluck('name')
                            ->toArray();
                        $districtsByRegion[$regionName] = $districts;
                    }
                }

                // Create named ranges for regions
                $regionList = '"' . implode(',', $regions) . '"';
                $sheet->getParent()->addNamedRange(
                    new NamedRange('Regions', $sheet, '=$F$2:$F$' . (count($regions) + 1))
                );

                // Create helper sheet for districts with named ranges
                $helperSheet = $sheet->getParent()->createSheet();
                $helperSheet->setTitle('Districts');
                $helperSheet->setSheetState(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::SHEETSTATE_HIDDEN);
                
                // Create named ranges for each region's districts
                $col = 'A';
                foreach ($districtsByRegion as $regionName => $districts) {
                    if (empty($districts)) continue;
                    
                    $helperSheet->setCellValue($col . '1', $regionName);
                    foreach ($districts as $index => $district) {
                        $helperSheet->setCellValue($col . ($index + 2), $district);
                    }
                    $lastRow = count($districts) + 1;
                    
                    // Create named range for this region's districts
                    $rangeName = 'Districts_' . preg_replace('/[^A-Z0-9_]/i', '_', $regionName);
                    try {
                        $sheet->getParent()->addNamedRange(
                            new NamedRange($rangeName, $helperSheet, "='Districts'!\$" . $col . "\$2:\$" . $col . "\$" . $lastRow)
                        );
                    } catch (\Exception $e) {
                        // Named range might already exist, skip
                    }
                    
                    // Move to next column (handle overflow)
                    if ($col == 'Z') {
                        $col = 'AA';
                    } else {
                        $col++;
                    }
                }
                
                // Also create a simple list of all districts for fallback
                $allDistricts = [];
                foreach ($districtsByRegion as $districts) {
                    $allDistricts = array_merge($allDistricts, $districts);
                }
                $helperSheet->setCellValue('ZZ1', 'All Districts');
                foreach ($allDistricts as $index => $district) {
                    $helperSheet->setCellValue('ZZ' . ($index + 2), $district);
                }

                // Set data validation for Sex column (E)
                $sexValidation = $sheet->getCell('E2')->getDataValidation();
                $sexValidation->setType(DataValidation::TYPE_LIST);
                $sexValidation->setErrorStyle(DataValidation::STYLE_STOP);
                $sexValidation->setAllowBlank(false);
                $sexValidation->setShowInputMessage(true);
                $sexValidation->setShowErrorMessage(true);
                $sexValidation->setShowDropDown(true);
                $sexValidation->setErrorTitle('Invalid Sex');
                $sexValidation->setError('Sex must be either M or F');
                $sexValidation->setPromptTitle('Select Sex');
                $sexValidation->setPrompt('Please select M (Male) or F (Female)');
                $sexValidation->setFormula1('"M,F"');
                
                // Apply to all data rows
                for ($row = 2; $row <= $highestRow; $row++) {
                    $sheet->getCell("E{$row}")->setDataValidation(clone $sexValidation);
                }

                // Set data validation for Region column (F)
                $regionValidation = $sheet->getCell('F2')->getDataValidation();
                $regionValidation->setType(DataValidation::TYPE_LIST);
                $regionValidation->setErrorStyle(DataValidation::STYLE_STOP);
                $regionValidation->setAllowBlank(true);
                $regionValidation->setShowInputMessage(true);
                $regionValidation->setShowErrorMessage(true);
                $regionValidation->setShowDropDown(true);
                $regionValidation->setErrorTitle('Invalid Region');
                $regionValidation->setError('Please select a valid region from the list');
                $regionValidation->setPromptTitle('Select Region');
                $regionValidation->setPrompt('Please select a region from the dropdown');
                $regionValidation->setFormula1('"'.implode(',', $regions).'"');
                
                // Apply to all data rows
                for ($row = 2; $row <= $highestRow; $row++) {
                    $sheet->getCell("F{$row}")->setDataValidation(clone $regionValidation);
                }

                // Set data validation for District column (G) - Dynamic based on Region using INDIRECT
                // For each row, create a validation that depends on the region in column F
                for ($row = 2; $row <= $highestRow; $row++) {
                    $districtValidation = $sheet->getCell("G{$row}")->getDataValidation();
                    $districtValidation->setType(DataValidation::TYPE_LIST);
                    $districtValidation->setErrorStyle(DataValidation::STYLE_STOP);
                    $districtValidation->setAllowBlank(true);
                    $districtValidation->setShowInputMessage(true);
                    $districtValidation->setShowErrorMessage(true);
                    $districtValidation->setShowDropDown(true);
                    $districtValidation->setErrorTitle('Invalid District');
                    $districtValidation->setError('Please select a valid district for the selected region');
                    $districtValidation->setPromptTitle('Select District');
                    $districtValidation->setPrompt('District options depend on the selected region. Select Region first.');
                    
                    // Use INDIRECT with SUBSTITUTE to handle spaces and special characters
                    // Formula: INDIRECT("Districts_"&SUBSTITUTE(SUBSTITUTE(F{row}," ","_"),"/","_"))
                    $regionCellRef = "F{$row}";
                    // Clean the region name for named range lookup
                    $formula = 'INDIRECT("Districts_"&SUBSTITUTE(SUBSTITUTE(SUBSTITUTE('.$regionCellRef.'," ","_"),"/","_"),"-","_"),TRUE)';
                    $districtValidation->setFormula1($formula);
                }

                // Set data validation for ID Type column (J)
                $idTypes = ['National ID', 'License', 'Voter Registration', 'Other'];
                $idTypeValidation = $sheet->getCell('J2')->getDataValidation();
                $idTypeValidation->setType(DataValidation::TYPE_LIST);
                $idTypeValidation->setErrorStyle(DataValidation::STYLE_STOP);
                $idTypeValidation->setAllowBlank(true);
                $idTypeValidation->setShowInputMessage(true);
                $idTypeValidation->setShowErrorMessage(true);
                $idTypeValidation->setShowDropDown(true);
                $idTypeValidation->setErrorTitle('Invalid ID Type');
                $idTypeValidation->setError('Please select a valid ID type from the list');
                $idTypeValidation->setPromptTitle('Select ID Type');
                $idTypeValidation->setPrompt('Select ID Type: National ID, License, Voter Registration, or Other');
                $idTypeValidation->setFormula1('"'.implode(',', $idTypes).'"');
                
                // Apply to all data rows
                for ($row = 2; $row <= $highestRow; $row++) {
                    $sheet->getCell("J{$row}")->setDataValidation(clone $idTypeValidation);
                }

                // Add instruction note with ID formatting rules
                $sheet->mergeCells('A' . ($highestRow + 2) . ':M' . ($highestRow + 2));
                $instructionCell = $sheet->getCell('A' . ($highestRow + 2));
                $instructions = 'INSTRUCTIONS: 1) Sex must be M or F only. 2) Select Region first, then District will auto-filter. 3) Phone numbers starting with 0 will be auto-formatted to 255 prefix. 4) Date format: YYYY-MM-DD. 5) Customer must be at least 18 years old. 6) ID Type: Select from dropdown. 7) ID Number formatting: National ID = XXXXXXXX-XXXXX-XXXXX-XX (20 digits), License = XXXXXXXXX (9 digits, no dashes), Voter Registration = XXXXX-XXXXXX-XXX (14 digits), Other = free text.';
                $instructionCell->setValue($instructions);
                $instructionCell->getStyle()->getFont()->setBold(true)->setItalic(true)->getColor()->setARGB('FF0066CC');
                $sheet->getRowDimension($highestRow + 2)->setRowHeight(60);
                $sheet->getStyle('A' . ($highestRow + 2) . ':M' . ($highestRow + 2))->getAlignment()->setWrapText(true);

                // Add ID formatting reference in a separate row
                $sheet->mergeCells('A' . ($highestRow + 3) . ':M' . ($highestRow + 3));
                $formatCell = $sheet->getCell('A' . ($highestRow + 3));
                $formatCell->setValue('ID FORMATTING GUIDE: National ID (XXXXXXXX-XXXXX-XXXXX-XX) | License (XXXXXXXXX) | Voter Registration (XXXXX-XXXXXX-XXX) | Other (any format)');
                $formatCell->getStyle()->getFont()->setBold(true)->getColor()->setARGB('FF006600');
                $sheet->getRowDimension($highestRow + 3)->setRowHeight(25);
                $sheet->getStyle('A' . ($highestRow + 3) . ':M' . ($highestRow + 3))->getAlignment()->setWrapText(true);
            },
        ];
    }
}

