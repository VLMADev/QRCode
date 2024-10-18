<?php
/**
 * Interface QROutputInterface
 *
 * @created      02.12.2015
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2015 Smiley
 * @license      MIT
 */

namespace QR;

use QR\QRMatrix;
use QR\SettingsContainerInterface;

/**
 * Converts the data matrix into readable output
 */
interface QROutputInterface{

    const MARKUP_HTML = 'html';
    const MARKUP_SVG  = 'svg';
    const MARKUP_PNG  = 'png';
    const MARKUP_JPG  = 'jpg';
    const MARKUP_GIF  = 'gif';
    const MARKUP_TEXT = 'text';
    const MARKUP_JSON = 'json';

    const GDIMAGE_PNG = 'png';
    const GDIMAGE_JPG = 'jpg';
    const GDIMAGE_GIF = 'gif';

    const STRING_JSON = 'json';
    const STRING_TEXT = 'text';

    /**
     * @param \QR\SettingsContainerInterface $options
     * @param \QR\QRMatrix               $matrix
     */
    public function __construct(SettingsContainerInterface $options, QRMatrix $matrix);

    /**
     * Renders the QR Code
     *
     * @return mixed
     */
    public function dump();

}