<?php
/**
 * Class QRGdImage
 *
 * @created      05.12.2015
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2015 Smiley
 * @license      MIT
 */

namespace QR;

use QR\QRMatrix;
use QR\QRCodeException;

/**
 * Converts the matrix into GD images
 */
class QRGdImage extends QROutputAbstract{

    /**
     * @var string
     */
    protected $defaultMode = QROutputInterface::GDIMAGE_PNG;

    /**
     * @var resource
     */
    protected $image;

    /**
     * @inheritdoc
     */
    public function dump(){
        $data = call_user_func(array($this, $this->options->outputType ?: $this->defaultMode));

        if($this->options->cachefile !== null){
            $this->saveToFile($data, $this->options->cachefile);
        }

        if($this->options->returnResource){
            return $this->image;
        }

        return $data;
    }

    /**
     * @return string
     */
    protected function png(){
        $this->prepareImage();

        ob_start();
        imagepng($this->image);

        return ob_get_clean();
    }

    /**
     * @return string
     */
    protected function gif(){
        $this->prepareImage();

        ob_start();
        imagegif($this->image);

        return ob_get_clean();
    }

    /**
     * @return string
     */
    protected function jpg(){
        $this->prepareImage();

        ob_start();
        imagejpeg($this->image, null, $this->options->jpegQuality);

        return ob_get_clean();
    }

    /**
     * @return void
     */
    protected function prepareImage(){
        $scale = $this->options->scale;
        $length = $this->moduleCount * $scale;

        $this->image = imagecreatetruecolor($length, $length);

        if($this->image === false){
            throw new QRCodeException('Unable to create GD image');
        }

        $background = $this->getColor($this->options->imageTransparencyBG);
        $foreground = $this->getColor($this->options->imageForegroundColor);

        if($this->options->imageTransparent && $this->options->outputType !== QROutputInterface::GDIMAGE_JPG){
            imagecolortransparent($this->image, $background);
        }

        imagefilledrectangle($this->image, 0, 0, $length, $length, $background);

        foreach($this->matrix->matrix() as $y => $row){
            foreach($row as $x => $M_TYPE){
                if($this->options->drawLightModules || $M_TYPE !== QRMatrix::M_NULL){
                    $color = $this->moduleValues[$M_TYPE] ? $foreground : $background;
                    imagefilledrectangle($this->image, $x * $scale, $y * $scale, ($x + 1) * $scale, ($y + 1) * $scale, $color);
                }
            }
        }

    }

    /**
     * @param string $color
     *
     * @return int
     */
    protected function getColor($color){
        $color = trim($color, '#');

        $r = 0;
        $g = 0;
        $b = 0;

        if(strlen($color) === 6){
            $r = hexdec(substr($color, 0, 2));
            $g = hexdec(substr($color, 2, 2));
            $b = hexdec(substr($color, 4, 2));
        }
        elseif(strlen($color) === 3){
            $r = hexdec(substr($color, 0, 1).substr($color, 0, 1));
            $g = hexdec(substr($color, 1, 1).substr($color, 1, 1));
            $b = hexdec(substr($color, 2, 1).substr($color, 2, 1));
        }

        return imagecolorallocate($this->image, $r, $g, $b);
    }

}