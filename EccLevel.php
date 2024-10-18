<?php
/**
 * Class EccLevel
 *
 * @created      19.11.2020
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2020 smiley
 * @license      MIT
 */

namespace QR;

use QR\QRCodeException;

/**
 * ISO/IEC 18004:2000 Section 8.9
 * ISO/IEC 18004:2000 Section 8.9.1
 *
 * @see https://github.com/zxing/zxing/blob/e9e2bd280bcaecd84e4d4d6b48bd4e3c0b7a4c2f/core/src/main/java/com/google/zxing/qrcode/decoder/ErrorCorrectionLevel.java
 */
class EccLevel{

    const L = 0b01; // 7%.
    const M = 0b00; // 15%.
    const Q = 0b11; // 25%.
    const H = 0b10; // 30%.

    /**
     * @var int
     */
    protected $eccLevel;

    /**
     * EccLevel constructor.
     *
     * @param int $eccLevel
     *
     * @throws \QR\QRCodeException
     */
    public function __construct($eccLevel){
        if(!$this->isValidEccLevel($eccLevel)){
            throw new QRCodeException('invalid ECC level');
        }

        $this->eccLevel = $eccLevel;
    }

    /**
     * @param int $eccLevel
     *
     * @return bool
     */
    public function isValidEccLevel($eccLevel){
        return in_array($eccLevel, array(self::L, self::M, self::Q, self::H), true);
    }

    /**
     * @return int
     */
    public function getLevel(){
        return $this->eccLevel;
    }

    /**
     * @return string
     */
    public function __toString(){
        return array(self::L => 'L', self::M => 'M', self::Q => 'Q', self::H => 'H')[$this->eccLevel];
    }
    public function getFormatPattern($maskPattern){
        $format = $this->eccLevel << 3 | $maskPattern;
        $polynomial = 0x537;
        $bits = $format << 10;

        while(decbin($bits) >= decbin($polynomial)){
            $bits ^= $polynomial << (strlen(decbin($bits)) - strlen(decbin($polynomial)));
        }

        return ($format << 10 | $bits) ^ 0x5412;
    }
}