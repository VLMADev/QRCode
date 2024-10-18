<?php
/**
 * Class QRCode
 *
 * @created      26.11.2015
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2015 Smiley
 * @license      MIT
 */

namespace QR;

use QR\QRMatrix;
use QR\MaskPattern;
use QR\Mode;
use QR\Version;
use QR\EccLevel;
use QR\QRDataInterface;
use QR\QROutputInterface;
use QR\SettingsContainerInterface;

/**
 * Generates QR Codes
 */
class QRCode{

    /**
     * @var \QR\QROptions
     */
    protected $options;

    /**
     * @var \QR\QRDataInterface
     */
    protected $dataInterface;

    /**
     * The current data matrix
     *
     * @var \QR\QRMatrix
     */
    protected $matrix;

    /**
     * QRCode constructor.
     *
     * @param \QR\SettingsContainerInterface|null $options
     */
    public function __construct(SettingsContainerInterface $options = null){
        $this->options = $options instanceof SettingsContainerInterface ? $options : new QROptions;
    }

    /**
     * Renders a QR Code for the given $data and QROptions
     *
     * @param string $data
     * @param \QR\SettingsContainerInterface|null $options
     *
     * @return string
     * @throws QRCodeException
     */
    public static function render($data, SettingsContainerInterface $options = null){
        return (new self($options))->output($data);
    }

    /**
     * Renders a QR Code for the given $data and QROptions
     *
     * @param string $data
     *
     * @return string
     * @throws QRCodeException
     */
    public function output($data){
        $this->initDataInterface($data);

        return $this->loadOutputInterface()->dump();
    }

    /**
     * Returns a QRMatrix object for the given $data and current QROptions
     *
     * @param string $data
     *
     * @return \QR\QRMatrix
     * @throws QRCodeException
     */
    public function getMatrix($data){
        $this->initDataInterface($data);

        return $this->matrix;
    }

    /**
     * initializes the data interface
     *
     * @param string $data
     *
     * @return void
     * @throws QRCodeException
     */
    protected function initDataInterface($data){
        $this->dataInterface = $this->loadDataInterface();

        $this->dataInterface->setData($data);

        $maskPattern = $this->options->maskPattern === MaskPattern::PATTERN_AUTO
            ? $this->getBestMaskPattern()
            : new MaskPattern($this->options->maskPattern);

        $this->matrix = $this->dataInterface->initMatrix($maskPattern);
    }

    /**
     * loads the data interface
     *
     * @return \QR\QRDataInterface
     */
    protected function loadDataInterface(){
        $interface = $this->options->dataInterface;

        if(!class_exists($interface)){
            throw new QRCodeException('invalid data interface');
        }

        return new $interface($this->options, new Mode($this->options->dataMode));
    }

    /**
     * loads the output interface
     *
     * @return \QR\QROutputInterface
     */
    protected function loadOutputInterface(){
        $interface = $this->options->outputInterface;

        if(!class_exists($interface)){
            throw new QRCodeException('invalid output interface');
        }

        return new $interface($this->options, $this->matrix);
    }

    /**
     * returns the best mask pattern for the current matrix
     *
     * @return \QR\MaskPattern
     * @throws QRCodeException
     */
    protected function getBestMaskPattern(){
        $penalties = array();

        for($pattern = 0; $pattern < 8; $pattern++){
            $penalties[$pattern] = $this->dataInterface->initMatrix(new MaskPattern($pattern))->getMaskPattern()->getPenalty();
        }

        return new MaskPattern(array_search(min($penalties), $penalties, true));
    }

}