<?php
/**
 * Class QRMarkup
 *
 * @created      17.12.2016
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2016 Smiley
 * @license      MIT
 */

namespace QR;

use QR\QRMatrix;
use QR\SettingsContainerInterface;

/**
 * Converts the matrix into markup types: HTML, SVG, ...
 */
class QRMarkup extends QROutputAbstract{

    /**
     * @var string
     */
    protected $defaultMode = QROutputInterface::MARKUP_SVG;

    /**
     * @inheritDoc
     */
    public function __construct(SettingsContainerInterface $options, QRMatrix $matrix){
        parent::__construct($options, $matrix);

        $this->matrix->setQuietZone($this->options->quietzoneSize);
    }

    /**
     * @return string
     */
    protected function html(){
        $html = '';

        for($y = 0; $y < $this->moduleCount; $y++){
            $html .= '<div>';

            for($x = 0; $x < $this->moduleCount; $x++){
                $html .= '<span style="background: '.$this->getModuleValue($x, $y).';"></span>';
            }

            $html .= '</div>'.$this->options->eol;
        }

        $size = $this->moduleCount * $this->options->scale;

        return '<div class="qrcode" style="background-color: '.$this->options->markupLight.'; width: '.$size.'px; height: '.$size.'px;">'
            .$this->options->eol.$html.'</div>';
    }

    /**
     * @return string
     */
    protected function svg(){
        $scale  = $this->options->scale;
        $length = $this->moduleCount * $scale;

        $svg = '<svg xmlns="http://www.w3.org/2000/svg" version="1.1" width="'.$length.'px" height="'.$length.'px">'
            .$this->options->eol
            .'<defs>'.$this->options->svgDefs.'</defs>'
            .$this->options->eol;

        foreach($this->matrix->matrix() as $y => $row){
            foreach($row as $x => $M_TYPE){
                $fill = $this->getModuleValue($x, $y);

                if($fill){
                    $svg .= '<rect x="'.($x * $scale).'" y="'.($y * $scale).'" width="'.$scale.'" height="'.$scale.'" fill="'.$fill.'" />';
                }
            }
        }

        return $svg.$this->options->eol.'</svg>'.$this->options->eol;
    }

    /**
     * @param int $x
     * @param int $y
     *
     * @return string
     */
    protected function getModuleValue($x, $y){
        if($this->options->drawLightModules || $this->matrix->check($x, $y)){
            return $this->matrix->check($x, $y) ? $this->options->markupDark : $this->options->markupLight;
        }

        return '';
    }

}