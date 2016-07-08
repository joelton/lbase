<?php
namespace Lfalmeida\Lbase\Utils;

use Illuminate\Support\Facades\App;
use mikehaertl\wkhtmlto\Pdf;

/**
 * Class ReportsBase
 * @package Lfalmeida\Lbase\Utils
 */
class ReportsBase
{
    /**
     * @var array
     */
    protected $configOptions = [];

    /**
     * ReportsBase constructor.
     * @param array $options
     */
    public function __construct($options = [])
    {
        $this->configOptions = [
            'binary' => base_path('vendor/h4cc/wkhtmltopdf-amd64/bin/wkhtmltopdf-amd64'),
            'no-outline',
            'javascript-delay' => 3000,
            'header-right' => date('d/m/Y'),
            'footer-right' => 'pág. [page] de [toPage]',
        ];
    }

    /**
     * @param $url
     */
    public function getPdfReport($url)
    {
        $pdf = new Pdf($this->configOptions);
        $pdf->addPage($url);
        $pdf->saveAs(base_path('storage/app/' . time() . '.pdf'));
    }

    /**
     * @param $url
     * @return string
     */
    public function downloadPdfReport($url)
    {
        $pdf = new Pdf($this->configOptions);
        $pdf->addPage($url);
        $filename = '/tmp/relatorio-' . time() . '.pdf';
        $serverPath = public_path($filename);

        if ($pdf->saveAs($serverPath)) {
            return App::make('url')->to($filename);
        }

        return false;
    }
}