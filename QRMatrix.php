<?php
/**
 * Class QRMatrix
 *
 * @created      15.11.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace QR;

use QR\EccLevel;
use QR\MaskPattern;
use QR\Version;
use QR\QRCodeException;

/**
 * Holds a numerical representation of the final QR Code matrix
 */
class QRMatrix{

    const M_NULL       = 0x00;
    const M_DARKMODULE = 0x02;
    const M_DATA       = 0x04;
    const M_FINDER     = 0x06;
    const M_SEPARATOR  = 0x08;
    const M_ALIGNMENT  = 0x0a;
    const M_TIMING     = 0x0c;
    const M_FORMAT     = 0x0e;
    const M_VERSION    = 0x10;
    const M_QUIETZONE  = 0x12;
    const M_LOGO       = 0x14;

    const M_TEST       = 0xff;

    protected $version;
    protected $eclevel;
    protected $maskPattern = MaskPattern::PATTERN_000;
    protected $moduleCount;
    protected $matrix;
    private $options;
    private $pattern;

    /**
     * QRMatrix constructor.
     *
     * @param int $version
     * @param int $eclevel
     *
     * @throws \QR\QRCodeException
     */
    public function __construct($version, $eclevel){
        $this->version     = new Version($version);
        $this->eclevel     = new EccLevel($eclevel);
        $this->moduleCount = $this->version->getDimension();
        $this->matrix      = array_fill(0, $this->moduleCount, array_fill(0, $this->moduleCount, $this::M_NULL));
    }
    public function get($x, $y){
        return $this->matrix[$y][$x];
    }
    public function set($x, $y, $value){
        $this->matrix[$y][$x] = $value;
        return $this;
    }
    public function check($x, $y){
        return $this->matrix[$y][$x] > 0;
    }
    public function getPenalty(){
        $penalty = 0;

        $penalty += $this->getPenaltyScore1();
        $penalty += $this->getPenaltyScore2();
        $penalty += $this->getPenaltyScore3();
        $penalty += $this->getPenaltyScore4();

        return $penalty;
    }
    /**
     * @return int
     */
    public function version(){
        return $this->version->getVersionNumber();
    }

    /**
     * @return int
     */
    public function eccLevel(){
        return $this->eclevel->getLevel();
    }

    /**
     * @return int
     */
    public function maskPattern(){
        return $this->maskPattern;
    }

    /**
     * @return int
     */
    public function size(){
        return $this->moduleCount;
    }


    /**
     * Sets the $M_TYPE value for the module at position [$x, $y]
     *
     * @param int   $x
     * @param int   $y
     * @param int   $value
     *
     * @return \QR\QRMatrix
     */

    public function setDarkModule(){
        $this->set(8, 4 * $this->version->getVersionNumber() + 9, $this::M_DARKMODULE);

        return $this;
    }

    /**
     * Draws the 7x7 finder patterns in the corners top left/right and bottom left
     *
     * @return \QR\QRMatrix
     */
    public function setFinderPattern(){

        $pos = array(
            array(0, 0), // top left
            array($this->moduleCount - 7, 0), // bottom left
            array(0, $this->moduleCount - 7), // top right
        );

        foreach($pos as $c){
            for($y = 0; $y < 7; $y++){
                for($x = 0; $x < 7; $x++){
                    if(($x === 0 || $x === 6 || $y === 0 || $y === 6) || ($x > 1 && $x < 5 && $y > 1 && $y < 5)){
                        $this->set($c[0] + $y, $c[1] + $x, $this::M_FINDER);
                    }
                }
            }
        }

        return $this;
    }

    /**
     * Draws the separator lines around the finder patterns
     *
     * @return \QR\QRMatrix
     */
    public function setSeparators(){

        $h = array(
            array(7, 0),
            array($this->moduleCount - 8, 0),
            array(7, $this->moduleCount - 8),
        );

        $v = array(
            array(7, 7),
            array($this->moduleCount - 1, 7),
            array(7, $this->moduleCount - 8),
        );

        for($c = 0; $c < 3; $c++){
            for($i = 0; $i < 8; $i++){
                $this->set($h[$c][0]     , $h[$c][1] + $i, $this::M_SEPARATOR);
                $this->set($v[$c][0] - $i, $v[$c][1]     , $this::M_SEPARATOR);
            }
        }

        return $this;
    }

    /**
     * Draws the 5x5 alignment patterns
     *
     * @return \QR\QRMatrix
     */
    public function setAlignmentPattern(){

        foreach($this->version->getAlignmentPattern() as $y){
            foreach($this->version->getAlignmentPattern() as $x){

                // skip existing patterns
                if($this->matrix[$y][$x] !== $this::M_NULL){
                    continue;
                }

                for($ry = -2; $ry <= 2; $ry++){
                    for($rx = -2; $rx <= 2; $rx++){
                        $v = ($ry === 0 && $rx === 0) || $ry === 2 || $ry === -2 || $rx === 2 || $rx === -2
                            ? $this::M_ALIGNMENT
                            : $this::M_NULL;

                        $this->set($x + $rx, $y + $ry, $v);
                    }
                }

            }
        }

        return $this;
    }

    /**
     * Draws the timing pattern (h/v checkered line between the finder patterns)
     *
     * @return \QR\QRMatrix
     */
    public function setTimingPattern(){

        foreach(range(8, $this->moduleCount - 8 - 1) as $i){

            if($this->matrix[6][$i] !== $this::M_NULL || $this->matrix[$i][6] !== $this::M_NULL){
                continue;
            }

            $v = $i % 2 === 0 ? $this::M_TIMING : $this::M_NULL;

            $this->set($i, 6, $v);
            $this->set(6, $i, $v);
        }

        return $this;
    }

    /**
     * Draws the version information, 2x 3x6 pixel
     *
     * @param bool $test
     *
     * @return \QR\QRMatrix
     */
    public function setVersionNumber($test = null){
        $test = $test !== null ? $test : false;

        if($this->version->getVersionNumber() < 7){
            return $this;
        }

        $bits = $this->version->getVersionPattern();

        for($i = 0; $i < 18; $i++){
            $a = (int)floor($i / 3);
            $b = $i % 3 + $this->moduleCount - 8 - 3;
            $v = !$test && (($bits >> $i) & 1) === 1 ? $this::M_VERSION : $this::M_NULL;

            $this->set($b, $a, $v);
            $this->set($a, $b, $v);
        }

        return $this;
    }

    /**
     * Draws the format info, 2x 15 pixel
     *
     * @param bool $test
     * @param int  $maskPattern
     *
     * @return \QR\QRMatrix
     */
    public function setFormatInfo($test = null, $maskPattern = null){
        $test = $test !== null ? $test : false;

        $maskPattern = $maskPattern !== null ? $maskPattern : $this->maskPattern;

        $bits = $this->eclevel->getFormatPattern($maskPattern);

        for($i = 0; $i < 15; $i++){
            $v = !$test && (($bits >> $i) & 1) === 1 ? $this::M_FORMAT : $this::M_NULL;

            if($i < 6){
                $this->set(8, $i, $v);
            }
            elseif($i < 8){
                $this->set(8, $i + 1, $v);
            }
            else{
                $this->set(8, $this->moduleCount - 15 + $i, $v);
            }

            if($i < 8){
                $this->set($this->moduleCount - $i - 1, 8, $v);
            }
            elseif($i < 9){
                $this->set(15 - $i, 8, $v);
            }
            else{
                $this->set(15 - $i - 1, 8, $v);
            }
        }

        $this->set(8, $this->moduleCount - 8, !$test ? $this::M_FORMAT : $this::M_NULL);

        return $this;
    }

    /**
     * Draws the "quiet zone" of $size around the matrix
     *
     * @param int $size
     *
     * @return \QR\QRMatrix
     * @throws \QR\QRCodeException
     */
    public function setQuietZone($size = null){

        if($this->matrix[$this->moduleCount - 1][$this->moduleCount - 1] === $this::M_NULL){
            throw new QRCodeException('use only after writing data');
        }

        $size = $size !== null ? max(0, min($size, floor($this->moduleCount / 2))) : 4;
        $t    = $this->moduleCount + 2 * $size;
        $q    = array_fill(0, $t, $this::M_QUIETZONE);
        $new  = array_fill(0, $t, $q);

        foreach($this->matrix as $y => $row){
            $new[$y + $size] = array_merge(array_fill(0, $size, $this::M_QUIETZONE), $row, array_fill(0, $size, $this::M_QUIETZONE));
        }

        $this->moduleCount = $t;
        $this->matrix      = $new;

        return $this;
    }

    /**
     * Maps the binary $data array from QRDataInterface::maskECC() on the matrix, using $maskPattern
     *
     * @param array $data
     * @param int   $maskPattern
     *
     * @return \QR\QRMatrix
     */
    public function mapData(array $data, $maskPattern){
        $this->maskPattern = $maskPattern;
        $byteCount         = count($data);
        $size              = $this->moduleCount - 1;

        for($i = $size, $y = $size, $inc = -1, $byteIndex = 0, $bitIndex  = 7; $i > 0; $i -= 2){

            if($i === 6){
                $i--;
            }

            while(true){
                for($c = 0; $c < 2; $c++){
                    $x = $i - $c;

                    if($this->matrix[$y][$x] === $this::M_NULL){
                        $v = false;

                        if($byteIndex < $byteCount){
                            $v = (($data[$byteIndex] >> $bitIndex) & 1) === 1;
                        }

                        if($this->getMask($x, $y, $maskPattern) === 0){
                            $v = !$v;
                        }

                        $this->matrix[$y][$x] = $v ? $this::M_DATA : $this::M_NULL;
                        $bitIndex--;

                        if($bitIndex === -1){
                            $byteIndex++;
                            $bitIndex = 7;
                        }

                    }
                }

                $y += $inc;

                if($y < 0 || $this->moduleCount <= $y){
                    $y   -=  $inc;
                    $inc  = -$inc;

                    break;
                }

            }
        }

        return $this;
    }

    /**
     * @param int $x
     * @param int $y
     * @param int $maskPattern
     *
     * @return int
     * @throws \QR\QRCodeException
     */
    protected function getMask($x, $y, $maskPattern){
        $a = $y + $x;
        $m = $y * $x;

        switch($maskPattern){
            case  0: $c = $a % 2; break;
            case  1: $c = $y % 2; break;
            case  2: $c = $x % 3; break;
            case  3: $c = $a % 3; break;
            case  4: $c = (floor($y / 2) + floor($x / 3)) % 2; break;
            case  5: $c = $m % 2 + $m % 3 === 0; break;
            case  6: $c = ($m % 2 + $m % 3) % 2; break;
            case  7: $c = ($m % 3 + $a % 2) % 2; break;
            default: throw new QRCodeException('invalid mask pattern');
        }

        return $c === 0;
    }
    protected function getPenaltyScore1(){
        $penalty = 0;

        for($y = 0; $y < $this->moduleCount; $y++){
            $rowValue = 0;
            $rowCount = 0;
            $colValue = 0;
            $colCount = 0;

            for($x = 0; $x < $this->moduleCount; $x++){
                $rowCurrent = $this->get($x, $y);
                $colCurrent = $this->get($y, $x);

                if($rowCurrent === $rowValue){
                    $rowCount++;
                }
                else{
                    if($rowCount >= 5){
                        $penalty += (3 + $rowCount - 5);
                    }
                    $rowValue = $rowCurrent;
                    $rowCount = 1;
                }

                if($colCurrent === $colValue){
                    $colCount++;
                }
                else{
                    if($colCount >= 5){
                        $penalty += (3 + $colCount - 5);
                    }
                    $colValue = $colCurrent;
                    $colCount = 1;
                }
            }

            if($rowCount >= 5){
                $penalty += (3 + $rowCount - 5);
            }

            if($colCount >= 5){
                $penalty += (3 + $colCount - 5);
            }
        }

        return $penalty;
    }

    /**
     * Calculates the penalty score for condition 2
     *
     * @return int
     */
    protected function getPenaltyScore2(){
        $penalty = 0;

        for($y = 0; $y < $this->moduleCount - 1; $y++){
            for($x = 0; $x < $this->moduleCount - 1; $x++){
                $value = $this->get($x, $y);

                if($value === $this->get($x + 1, $y) &&
                    $value === $this->get($x, $y + 1) &&
                    $value === $this->get($x + 1, $y + 1)){
                    $penalty += 3;
                }
            }
        }

        return $penalty;
    }

    /**
     * Calculates the penalty score for condition 3
     *
     * @return int
     */
    protected function getPenaltyScore3(){
        $penalty = 0;

        for($y = 0; $y < $this->moduleCount; $y++){
            for($x = 0; $x < $this->moduleCount; $x++){
                if($x + 6 < $this->moduleCount &&
                    $this->get($x, $y) &&
                    !$this->get($x + 1, $y) &&
                    $this->get($x + 2, $y) &&
                    $this->get($x + 3, $y) &&
                    $this->get($x + 4, $y) &&
                    !$this->get($x + 5, $y) &&
                    $this->get($x + 6, $y)){
                    $penalty += 40;
                }

                if($y + 6 < $this->moduleCount &&
                    $this->get($x, $y) &&
                    !$this->get($x, $y + 1) &&
                    $this->get($x, $y + 2) &&
                    $this->get($x, $y + 3) &&
                    $this->get($x, $y + 4) &&
                    !$this->get($x, $y + 5) &&
                    $this->get($x, $y + 6)){
                    $penalty += 40;
                }
            }
        }

        return $penalty;
    }

    /**
     * Calculates the penalty score for condition 4
     *
     * @return int
     */
    protected function getPenaltyScore4(){
        $darkCount = 0;

        for($y = 0; $y < $this->moduleCount; $y++){
            for($x = 0; $x < $this->moduleCount; $x++){
                if($this->get($x, $y)){
                    $darkCount++;
                }
            }
        }

        $ratio = abs(100 * $darkCount / $this->moduleCount / $this->moduleCount - 50) / 5;
        return $ratio * 10;
    }

    public function matrix(){
        $output = '';

        for($y = 0; $y < $this->moduleCount; $y++){
            for($x = 0; $x < $this->moduleCount; $x++){
                $output .= $this->get($x, $y) ? $this->options->textDark : $this->options->textLight;
            }

            $output .= $this->options->eol;
        }

        return $output;
    }

    public function getMaskPattern(){
        return $this->pattern;
    }
}