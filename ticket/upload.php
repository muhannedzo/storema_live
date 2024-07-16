<?php

require_once '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['excelFile']['tmp_name'])) {
    $inputFileName = $_FILES['excelFile']['tmp_name'];

    $spreadsheet = IOFactory::load($inputFileName);
    $worksheet = $spreadsheet->getActiveSheet();

    $html = '<table border centpercen>';

    foreach ($worksheet->getRowIterator() as $row) {
        $html .= '<tr>';

        $cellIterator = $row->getCellIterator();
        $cellIterator->setIterateOnlyExistingCells(false);

        foreach ($cellIterator as $cell) {
            $html .= '<td>' . $cell->getValue() . '</td>';
        }

        $html .= '</tr>';
    }

    $html .= '</table>';

    echo $html;
}
?>