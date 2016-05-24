<?php

namespace Tinycar\App\Writer;

use SimpleExcel\SimpleExcel;
use Tinycar\App\Writer;

class Excel extends Writer
{
    private $writer;


    /**
     * @see Tinycar\App\Writer::init()
     */
    public function init()
    {
        $this->excel = new SimpleExcel('XML');
    }


    /**
     * Add new row data to export
     * @param $arg1 row argument #1
     * @param $arg2 row argument #2
     * ...
     */
    public function addRow()
    {
        $this->excel->writer->addRow(func_get_args());
    }


    /**
     * @see Tinycar\App\Writer::output()
     */
    public function output()
    {
        // Set custom body
        $this->setBody($this->excel->writer->saveString());

        // Add custom headers
        $this->addHeaders(array(
            'Content-Type'        => 'application/vnd.ms-excel; charset=UTF8',
            'Content-Disposition' => 'attachment; filename=ufn-'.time().'.xls',
            'Content-Length'      => $this->getBodyLength(),
        ));

        // Output writer contents
        parent::output();
    }
}
