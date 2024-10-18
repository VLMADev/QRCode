<?php
/**
 * Class QRFpdf
 *
 * @created      03.06.2020
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2020 Smiley
 * @license      MIT
 */

namespace QR;

use QR\QRMatrix;
use QR\QRCodeException;
use FPDF;

/**
 * QRFpdf output module (requires fpdf)
 *
 * @see https://github.com/Setasign/FPDF
 * @see http://www.fpdf.org/
 */
class QRFpdf extends QROutputAbstract{

    /**
     * @var string
     */
    protected $defaultMode = QROutputInterface::FPDF;

    /**
     * @var \FPDF
     */
    protected $fpdf;

    /**
     * @inheritdoc
     */
    public function __construct($options, QRMatrix $matrix){
        parent::__construct($options, $matrix);

        $this->fpdf = new FPDF('P', $this->options->fpdfMeasureUnit, array($this->length, $this->length));
    }

    /**
     * @inheritdoc
     */
    public function dump(){
        $this->fpdf();

        if($this->options->returnResource){
            return $this->fpdf;
        }

        $pdfData = $this->fpdf->Output('S');

        if($this->options->cachefile !== null){
            $this->saveToFile($pdfData, $this->options->cachefile);
        }

        return $pdfData;
    }

    /**
     * @return void
     */
    protected function fpdf(){
        $prevDrawColor = $this->fpdf->DrawColor;
        $prevFillColor = $this->fpdf->FillColor;

        $this->fpdf->AddPage();
        $this->fpdf->SetAutoPageBreak(false);
        $this->fpdf->SetMargins(0, 0);

        $this->fpdf->SetDrawColor(...$this->getColor($this->options->fpdfDrawColor));
        $this->fpdf->SetFillColor(...$this->getColor($this->options->fpdfBackgroundColor));

        // fallback css color for the light modules if no image background is set
        $cssLightColor = $this->getCssColor($this->options->fpdfBackgroundColor);

        $this->fpdf->Rect(0, 0, $this->length, $this->length, 'DF');

        $this->fpdf->SetFillColor(...$this->getColor($this->options->fpdfColor));

        foreach($this->matrix->matrix() as $y => $row){
            foreach($row as $x => $M_TYPE){
                $this->module($x, $y, $M_TYPE);
            }
        }

        $this->fpdf->SetDrawColor($prevDrawColor);
        $this->fpdf->SetFillColor($prevFillColor);
    }

    /**
     * @param int $x
     * @param int $y
     * @param int $M_TYPE
     *
     * @return void
     */
    protected function module($x, $y, $M_TYPE){
        if(!$this->options->drawLightModules && !$this->matrix->check($x, $y)){
            return;
        }

        $this->fpdf->Rect(
            ($x * $this->scale),
            ($y * $this->scale),
            $this->scale,
            $this->scale,
            'F'
        );
    }

    /**
     * @param string $color
     *
     * @return int[]
     * @throws \QR\QRCodeException
     */
    protected function getColor($color){
        $color = trim($color, '#');

        if(strlen($color) === 6){
            return array(
                hexdec(substr($color, 0, 2)),
                hexdec(substr($color, 2, 2)),
                hexdec(substr($color, 4, 2)),
            );
        }

        if(strlen($color) === 3){
            $r = substr($color, 0, 1);
            $g = substr($color, 1, 1);
            $b = substr($color, 2, 1);

            return array(
                hexdec($r.$r),
                hexdec($g.$g),
                hexdec($b.$b),
            );
        }

        throw new QRCodeException('invalid color: '.$color);
    }

    /**
     * @param string $color
     *
     * @return string
     */
    protected function getCssColor($color){
        $color = trim($color, '#');
        $color = strlen($color) === 6 ? $color : substr($color, 0, 1).substr($color, 0, 1).substr($color, 1, 1).substr($color, 1, 1).substr($color, 2, 1).substr($color, 2, 1);

        return sprintf('#%s', $color);
    }

}