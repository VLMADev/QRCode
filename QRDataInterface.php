<?php
/**
 * Interface QRDataInterface
 *
 * @created      01.12.2015
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2015 Smiley
 * @license      MIT
 */

namespace QR;

use QR\MaskPattern;

/**
 * Processes the binary data and maps it on a matrix which is then being returned
 */
interface QRDataInterface{

    /**
     * @param string $data
     *
     * @return \QR\QRDataInterface
     */
    public function setData($data);

    /**
     * @param \QR\MaskPattern $maskPattern
     *
     * @return \QR\QRMatrix
     */
    public function initMatrix($maskPattern);

    /**
     * @return int
     */
    public function getVersion();

}