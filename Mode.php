<?php
/**
 * Class Mode
 *
 * @created      19.11.2020
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2020 smiley
 * @license      MIT
 */

namespace QR;

use QR\QRCodeException;

/**
 * ISO/IEC 18004:2000 Section 6.3
 * ISO/IEC 18004:2000 Section 8.4
 */
class Mode{

    const TERMINATOR    =  0; // 0000 0000
    const NUMBER        =  1; // 0001 0001
    const ALPHANUM      =  2; // 0010 0010
    const BYTE          =  4; // 0100 0100
    const KANJI         =  8; // 1000 1000
    const ECI           =  7; // 0111 0111
    const FNC1_FIRST    =  5; // 0101 0101
    const FNC1_SECOND   =  9; // 1001 1001
    const HANZI         = 13; // 1101 1101 - GBT18284-2000 Hanzi mode (ch. 4.4.1.4)
    const STRUCTURED_APPEND = 3; // 0011 0011

    /**
     * @var int
     */
    protected $mode;

    /**
     * Mode constructor.
     *
     * @param int $mode
     *
     * @throws \QR\QRCodeException
     */
    public function __construct($mode){
        if(!$this->isValidMode($mode)){
            throw new QRCodeException('invalid mode given');
        }

        $this->mode = $mode;
    }

    /**
     * @param int $mode
     *
     * @return bool
     */
    public function isValidMode($mode){
        return in_array($mode, array(
            self::TERMINATOR, self::NUMBER, self::ALPHANUM, self::BYTE, self::KANJI,
            self::ECI, self::FNC1_FIRST, self::FNC1_SECOND, self::HANZI, self::STRUCTURED_APPEND
        ), true);
    }

    /**
     * @return int
     */
    public function getMode(){
        return $this->mode;
    }

    /**
     * Returns the length bits for the version breakpoints 1-9, 10-26 and 27-40
     *
     * @param int $version
     *
     * @return int
     */
    public function getLengthBits($version){
        switch($this->mode){
            case self::NUMBER:     return $version < 10 ? 10 : ($version < 27 ? 12 : 14);
            case self::ALPHANUM:   return $version < 10 ? 9  : ($version < 27 ? 11 : 13);
            case self::BYTE:       return $version < 10 ? 8  : 16;
            case self::KANJI:      return $version < 10 ? 8  : ($version < 27 ? 10 : 12);
            case self::HANZI:      return $version < 10 ? 8  : ($version < 27 ? 10 : 12);
            default:               return 0;
        }
    }

}