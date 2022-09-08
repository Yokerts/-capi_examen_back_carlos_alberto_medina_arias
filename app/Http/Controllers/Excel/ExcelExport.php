<?php


namespace App\Http\Controllers\Excel;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ExcelExport implements FromCollection, WithHeadings
{

    private $header;
    private $body;

    /**
     * ExcelExport constructor.
     * @param $header
     * @param $body
     */
    public function __construct($header, $body)
    {
        $this->header = $header;
        $this->body = $body;
    }


    public function headings(): array
    {
        return $this->header;
    }

    public function collection()
    {
        return $this->body;
    }
}
