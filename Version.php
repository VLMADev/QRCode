<?php
/**
 * Class Version
 *
 * @created      19.11.2020
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2020 smiley
 * @license      MIT
 */

namespace QR;

use QR\QRCodeException;

/**
 * ISO/IEC 18004:2000 Section 8.10
 * ISO/IEC 18004:2000 Section 8.10.1 - Table 3 (pp. 24)
 */
class Version{

    const AUTO = -1;

    /**
     * @var int
     */
    protected $versionNumber;

    /**
     * @var int
     */
    protected $dimension;

    /**
     * @var array
     */
    protected $alignmentPattern;

    /**
     * @var int
     */
    protected $remainderBits;

    /**
     * @var array
     */
    protected static $versionList = array(
        // 1-9
        1 => array(21, array(), 0),
        2 => array(25, array(6, 18), 7),
        3 => array(29, array(6, 22), 7),
        4 => array(33, array(6, 26), 7),
        5 => array(37, array(6, 30), 7),
        6 => array(41, array(6, 34), 7),
        7 => array(45, array(6, 22, 38), 0),
        8 => array(49, array(6, 24, 42), 0),
        9 => array(53, array(6, 26, 46), 0),
        // 10-19
        10 => array(57, array(6, 28, 50), 0),
        11 => array(61, array(6, 30, 54), 0),
        12 => array(65, array(6, 32, 58), 0),
        13 => array(69, array(6, 34, 62), 0),
        14 => array(73, array(6, 26, 46, 66), 3),
        15 => array(77, array(6, 26, 48, 70), 3),
        16 => array(81, array(6, 26, 50, 74), 3),
        17 => array(85, array(6, 30, 54, 78), 3),
        18 => array(89, array(6, 30, 56, 82), 3),
        19 => array(93, array(6, 30, 58, 86), 3),
        // 20-29
        20 => array(97, array(6, 34, 62, 90), 3),
        21 => array(101, array(6, 28, 50, 72, 94), 4),
        22 => array(105, array(6, 26, 50, 74, 98), 4),
        23 => array(109, array(6, 30, 54, 78, 102), 4),
        24 => array(113, array(6, 28, 54, 80, 106), 4),
        25 => array(117, array(6, 32, 58, 84, 110), 4),
        26 => array(121, array(6, 30, 58, 86, 114), 4),
        27 => array(125, array(6, 34, 62, 90, 118), 4),
        28 => array(129, array(6, 26, 50, 74, 98, 122), 3),
        29 => array(133, array(6, 30, 54, 78, 102, 126), 3),
        // 30-40
        30 => array(137, array(6, 26, 52, 78, 104, 130), 3),
        31 => array(141, array(6, 30, 56, 82, 108, 134), 3),
        32 => array(145, array(6, 34, 60, 86, 112, 138), 3),
        33 => array(149, array(6, 30, 58, 86, 114, 142), 3),
        34 => array(153, array(6, 34, 62, 90, 118, 146), 3),
        35 => array(157, array(6, 30, 54, 78, 102, 126, 150), 0),
        36 => array(161, array(6, 24, 50, 76, 102, 128, 154), 0),
        37 => array(165, array(6, 28, 54, 80, 106, 132, 158), 0),
        38 => array(169, array(6, 32, 58, 84, 110, 136, 162), 0),
        39 => array(173, array(6, 26, 54, 82, 110, 138, 166), 0),
        40 => array(177, array(6, 30, 58, 86, 114, 142, 170), 0),
    );

    /**
     * Version constructor.
     *
     * @param int $versionNumber
     *
     * @throws \QR\QRCodeException
     */
    public function __construct($versionNumber){
        if(!$this->isValidVersion($versionNumber)){
            throw new QRCodeException('invalid version number');
        }

        $this->versionNumber    = $versionNumber;
        $this->dimension        = self::$versionList[$versionNumber][0];
        $this->alignmentPattern = self::$versionList[$versionNumber][1];
        $this->remainderBits    = self::$versionList[$versionNumber][2];
    }

    /**
     * @param int $versionNumber
     *
     * @return bool
     */
    public function isValidVersion($versionNumber){
        return $versionNumber >= 1 && $versionNumber <= 40;
    }

    /**
     * @return int
     */
    public function getVersionNumber(){
        return $this->versionNumber;
    }

    /**
     * @return int
     */
    public function getDimension(){
        return $this->dimension;
    }

    /**
     * @return array
     */
    public function getAlignmentPattern(){
        return $this->alignmentPattern;
    }

    /**
     * @return int
     */
    public function getRemainderBits(){
        return $this->remainderBits;
    }

    /**
     * @param int $length
     * @param int $eccLevel
     *
     * @return int
     * @throws QRCodeException
     */
    public static function getMinimumVersion($length, $eccLevel){
        for($version = 1; $version <= 40; $version++){
            if($length <= self::getMaxLengthForVersion($version, $eccLevel)){
                return $version;
            }
        }

        return -1;
    }

    /**
     * @param int $version
     * @param int $eccLevel
     *
     * @return int
     */
    public static function getMaxLengthForVersion($version, $eccLevel){
        if(!isset(self::$versionList[$version])){
            throw new QRCodeException('invalid version number');
        }

        $maxLength = 0;
        $dataLength = self::$versionList[$version][0] * self::$versionList[$version][0] - 64;

        switch($eccLevel){
            case EccLevel::L:
                $maxLength = (int)($dataLength * 0.07);
                break;
            case EccLevel::M:
                $maxLength = (int)($dataLength * 0.15);
                break;
            case EccLevel::Q:
                $maxLength = (int)($dataLength * 0.25);
                break;
            case EccLevel::H:
                $maxLength = (int)($dataLength * 0.30);
                break;
        }

        return $maxLength;
    }
    public function getVersionPattern(){
        if($this->versionNumber < 7){
            return 0;
        }

        $pattern = $this->versionNumber << 12;
        $polynomial = 0x1F25;

        for($i = 0; $i < 12; $i++){
            if($pattern & (1 << (11 - $i))){
                $pattern ^= $polynomial << (11 - $i);
            }
        }

        return $pattern;
    }
}