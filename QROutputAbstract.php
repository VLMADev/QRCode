<?php
/**
 * Class QROutputAbstract
 *
 * @created      09.12.2015
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2015 Smiley
 * @license      MIT
 */

namespace QR;

use QR\QRMatrix;
use QR\QRCodeException;
use QR\SettingsContainerInterface;

/**
 * common output abstract
 */
abstract class QROutputAbstract implements QROutputInterface{

    /**
     * @var \QR\QROptions
     */
    protected $options;

    /**
     * @var \QR\QRMatrix
     */
    protected $matrix;

    /**
     * @var int
     */
    protected $moduleCount;

    /**
     * @var string
     */
    protected $defaultMode;

    /**
     * @var array
     */
    protected $moduleValues;

    /**
     * @inheritDoc
     */
    public function __construct(SettingsContainerInterface $options, QRMatrix $matrix){
        $this->options     = $options;
        $this->matrix      = $matrix;
        $this->moduleCount = $this->matrix->size();
        $this->setModuleValues();
    }

    /**
     * Sets the initial module values
     *
     * @return void
     */
    protected function setModuleValues(){
        $this->moduleValues = array(
            // light
            QRMatrix::M_NULL            => $this->options->markupLight,
            QRMatrix::M_QUIETZONE       => $this->options->markupLight,
            // dark
            QRMatrix::M_DARKMODULE      => $this->options->markupDark,
            QRMatrix::M_DATA            => $this->options->markupDark,
            QRMatrix::M_FINDER          => $this->options->markupDark,
            QRMatrix::M_SEPARATOR       => $this->options->markupDark,
            QRMatrix::M_ALIGNMENT       => $this->options->markupDark,
            QRMatrix::M_TIMING          => $this->options->markupDark,
            QRMatrix::M_FORMAT          => $this->options->markupDark,
            QRMatrix::M_VERSION         => $this->options->markupDark,
            // special
            QRMatrix::M_LOGO            => $this->options->markupDark,
            QRMatrix::M_TEST            => $this->options->markupDark,
        );
    }

    /**
     * Generates the output, optionally dumps it to a file, and returns it
     *
     * @return mixed
     * @throws QRCodeException
     */
    public function dump(){
        $data = call_user_func(array($this, $this->options->outputType ?: $this->defaultMode));

        if($this->options->cachefile !== null){
            $this->saveToFile($data, $this->options->cachefile);
        }

        return $data;
    }

    /**
     * @param mixed  $data
     * @param string $file
     *
     * @return void
     * @throws \QR\QRCodeException
     */
    protected function saveToFile($data, $file){
        if(!is_writable(dirname($file))){
            throw new QRCodeException(sprintf('Cannot write data to cache file: %s', $file));
        }

        if(file_put_contents($file, $data) === false){
            throw new QRCodeException(sprintf('Cannot write data to cache file: %s (file_put_contents error)', $file));
        }
    }

}