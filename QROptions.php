<?php
/**
 * Class QROptions
 *
 * @created      08.12.2015
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2015 Smiley
 * @license      MIT
 */

namespace QR;


use QR\QRMatrix;
use QR\EccLevel;
use QR\MaskPattern;
use QR\Mode;
use QR\Version;
use QR\QROutputInterface;
use QR\SettingsContainerAbstract;

/**
 * The QRCode settings container
 */
class QROptions extends SettingsContainerAbstract{
    /**
     * @var mixed
     */
    public $dataInterface;

    /**
     * QR Code version number
     *
     * @var int
     */
    protected $version = Version::AUTO;

    /**
     * Minimum QR version
     *
     * @var int
     */
    protected $versionMin = 1;

    /**
     * Maximum QR version
     *
     * @var int
     */
    protected $versionMax = 40;

    /**
     * Error correct level
     *
     * @var int
     */
    public $eccLevel = EccLevel::L;

    /**
     * Mask Pattern to use
     *
     * @var int
     */
    public $maskPattern = MaskPattern::PATTERN_AUTO;

    /**
     * Add a "quiet zone" (margin) according to the QR code spec
     *
     * @var bool
     */
    protected $addQuietzone = true;

    /**
     * Size of the quiet zone
     *
     * @var int
     */
    protected $quietzoneSize = 4;

    /**
     * Use this data mode instead of auto detecting the data type
     *
     * @var int
     */
    public $dataMode = Mode::BYTE;

    /**
     * The output type
     *
     * @var string
     */
    public $outputType = QROutputInterface::MARKUP_SVG;

    /**
     * The output interface to use
     *
     * @var string
     */
    public $outputInterface = '\\QR\\QRMarkup';

    /**
     * /path/to/cache.file
     *
     * @var string
     */
    public $cachefile;

    /**
     * newline string [HTML, SVG, TEXT]
     *
     * @var string
     */
    protected $eol = PHP_EOL;

    /**
     * size of a QR code pixel [SVG, IMAGE_*]
     *
     * @var int
     */
    protected $scale = 5;

    /**
     * @see \QR\QROutputInterface::dump()
     *
     * @var bool
     */
    protected $returnResource = false;

    /**
     * string substitute for dark
     *
     * @var string
     */
    public $textDark = '🔴';

    /**
     * string substitute for light
     *
     * @var string
     */
    public $textLight = '⭕';

    /**
     * markup substitute for dark (CSS value)
     *
     * @var string
     */
    public $markupDark = '#000';

    /**
     * markup substitute for light (CSS value)
     *
     * @var string
     */
    public $markupLight = '#fff';

    /**
     * Return the options as an array
     *
     * @return array
     */
    public function toArray(){
        return array(
            'version' => $this->version,
            'versionMin' => $this->versionMin,
            'versionMax' => $this->versionMax,
            'eccLevel' => $this->eccLevel,
            'maskPattern' => $this->maskPattern,
            'addQuietzone' => $this->addQuietzone,
            'quietzoneSize' => $this->quietzoneSize,
            'dataMode' => $this->dataMode,
            'outputType' => $this->outputType,
            'outputInterface' => $this->outputInterface,
            'cachefile' => $this->cachefile,
            'eol' => $this->eol,
            'scale' => $this->scale,
            'returnResource' => $this->returnResource,
            'textDark' => $this->textDark,
            'textLight' => $this->textLight,
            'markupDark' => $this->markupDark,
            'markupLight' => $this->markupLight,
        );
    }

}