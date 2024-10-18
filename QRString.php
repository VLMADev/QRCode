<?php
/**
 * Class QRString
 *
 * @created      05.12.2015
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2015 Smiley
 * @license      MIT
 */

namespace QR;

use QR\QRMatrix;

/**
 * Converts the matrix data into string types
 */
class QRString extends QROutputAbstract{

    /**
     * @var string
     */
    protected $defaultMode = QROutputInterface::STRING_TEXT;

    /**
     * @inheritdoc
     */
    protected function setModuleValues(){

        foreach($this->moduleValues as $M_TYPE => $value){
            $this->moduleValues[$M_TYPE] = $this->options->textDark;
        }

        $this->moduleValues[QRMatrix::M_NULL] = $this->options->textLight;
    }

    /**
     * @return string
     */
    protected function text(){
        $str = '';

        foreach($this->matrix->matrix() as $row){
            for($i = 0; $i < $this->moduleCount; $i += 2){
                if(isset($row[$i + 1])){
                    $str .= $this->getModuleValue($row[$i], $row[$i + 1]);
                }
                else{
                    $str .= $this->getModuleValue($row[$i], $row[$i]);
                }
            }

            $str .= $this->options->eol;
        }

        return $str;
    }

    /**
     * @return string
     */
    protected function json(){
        return json_encode($this->matrix->matrix());
    }

    /**
     * @param int $M_TYPE1
     * @param int $M_TYPE2
     *
     * @return string
     */
    protected function getModuleValue($M_TYPE1, $M_TYPE2){
        $map = array(
            2 => array(
                2 => $this->moduleValues[$M_TYPE1],
                0 => $this->moduleValues[$M_TYPE1],
            ),
            0 => array(
                2 => $this->moduleValues[$M_TYPE2],
                0 => $this->options->textLight,
            ),
        );

        return $map[$M_TYPE1 > 0 ? 2 : 0][$M_TYPE2 > 0 ? 2 : 0];
    }

}