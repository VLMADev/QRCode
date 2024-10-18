<?php
/**
 * Class MaskPattern
 *
 * @created      19.11.2020
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2020 smiley
 * @license      MIT
 */

namespace QR;

use QR\QRCodeException;

/**
 * ISO/IEC 18004:2000 Section 8.8.1
 *
 * @see http://www.thonky.com/qr-code-tutorial/mask-patterns
 */
class MaskPattern{

    const PATTERN_000 = 0b000;
    const PATTERN_001 = 0b001;
    const PATTERN_010 = 0b010;
    const PATTERN_011 = 0b011;
    const PATTERN_100 = 0b100;
    const PATTERN_101 = 0b101;
    const PATTERN_110 = 0b110;
    const PATTERN_111 = 0b111;
    const PATTERN_AUTO = -1;

    protected $maskPattern;

    public function __construct($maskPattern){
        if(!$this->isValidMaskPattern($maskPattern)){
            throw new QRCodeException('invalid mask pattern');
        }

        $this->maskPattern = $maskPattern;
    }

    public function isValidMaskPattern($maskPattern){
        return in_array($maskPattern, array(
            self::PATTERN_000, self::PATTERN_001, self::PATTERN_010, self::PATTERN_011,
            self::PATTERN_100, self::PATTERN_101, self::PATTERN_110, self::PATTERN_111
        ), true);
    }

    public function getPattern(){
        return $this->maskPattern;
    }

    public function getMask($x, $y){
        switch($this->maskPattern){
            case self::PATTERN_000: return ($x + $y) % 2 === 0;
            case self::PATTERN_001: return $y % 2 === 0;
            case self::PATTERN_010: return $x % 3 === 0;
            case self::PATTERN_011: return ($x + $y) % 3 === 0;
            case self::PATTERN_100: return ((int)($y / 2) + (int)($x / 3)) % 2 === 0;
            case self::PATTERN_101: return (($x * $y) % 2) + (($x * $y) % 3) === 0;
            case self::PATTERN_110: return ((($x * $y) % 2) + (($x * $y) % 3)) % 2 === 0;
            case self::PATTERN_111: return ((($x + $y) % 2) + (($x * $y) % 3)) % 2 === 0;
        }

        throw new QRCodeException('invalid mask pattern');
    }
}