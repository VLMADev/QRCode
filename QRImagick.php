<?php
/**
 * Class QRImagick
 *
 * @created      04.07.2018
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2018 smiley
 * @license      MIT
 */

namespace QR;

use QR\QRMatrix;
use QR\QRCodeException;
use QR\Imagick;
use QR\ImagickDraw;
use QR\ImagickPixel;


/**
 * ImageMagick output module
 */
class QRImagick extends QROutputAbstract{

    /**
     * @var string
     */
    protected $defaultMode = QROutputInterface::IMAGICK;

    /**
     * @var \Imagick
     */
    protected $imagick;

    /**
     * @var \ImagickDraw
     */
    protected $imagickDraw;

    /**
     * @inheritdoc
     */
    public function __construct($options, QRMatrix $matrix){
        parent::__construct($options, $matrix);

        $this->imagick     = new Imagick;
        $this->imagickDraw = new ImagickDraw;

        $this->imagick->setResolution(72, 72);
        $this->imagick->setType(Imagick::IMGTYPE_TRUECOLORMATTE);
        $this->imagick->setColorspace(Imagick::COLORSPACE_SRGB);
    }

    /**
     * @inheritdoc
     */
    public function dump(){
        $this->imagick();

        if($this->options->returnResource){
            return $this->imagick;
        }

        $imageData = $this->imagick->getImageBlob();

        if($this->options->cachefile !== null){
            $this->saveToFile($imageData, $this->options->cachefile);
        }

        return $imageData;
    }

    /**
     * @return void
     */
    protected function imagick(){
        $this->imagick->newImage($this->length, $this->length, 'transparent', 'png');

        $this->imagickDraw->setStrokeWidth(0);

        foreach($this->matrix->matrix() as $y => $row){
            foreach($row as $x => $M_TYPE){
                $this->module($x, $y, $M_TYPE);
            }
        }

        $this->imagick->drawImage($this->imagickDraw);
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

        $this->imagickDraw->setFillColor(new ImagickPixel($this->moduleValues[$M_TYPE]));
        $this->imagickDraw->rectangle(
            $x * $this->scale,
            $y * $this->scale,
            ($x + 1) * $this->scale,
            ($y + 1) * $this->scale
        );
    }

}