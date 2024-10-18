<?php
/**
 * Class QRData
 *
 * @created      25.11.2015
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2015 Smiley
 * @license      MIT
 */

namespace QR;


use QR\QRCodeException;
use QR\EccLevel;
use QR\Mode;
use QR\Version;
use QR\SettingsContainerInterface;

/**
 * Processes the binary data and maps it on a matrix which is then being returned
 */
class QRData implements QRDataInterface{

    /**
     * @var \QR\SettingsContainerInterface|\QR\QROptions
     */
    protected $options;

    /**
     * @var \QR\QRDataInterface
     */
    protected $dataInterface;

    /**
     * @var \QR\Mode
     */
    protected $datamode;

    /**
     * @var int
     */
    protected $version;

    /**
     * @var int
     */
    protected $eclevel;

    /**
     * @var string
     */
    protected $data;

    /**
     * QRData constructor.
     *
     * @param \QR\SettingsContainerInterface $options
     * @param \QR\Mode                  $datamode
     */
    public function __construct(SettingsContainerInterface $options, Mode $datamode){
        $this->options  = $options;
        $this->datamode = $datamode;

        $this->eclevel = new EccLevel($this->options->eccLevel);
    }

    /**
     * @inheritdoc
     * @throws QRCodeException
     */
    public function setData($data){
        $this->data = $data;

        if(!$this->dataInterface){
            $this->dataInterface = $this->initDataInterface($data);
        }

        $this->dataInterface->setData($data);

        $this->version = $this->dataInterface->getVersion();

        return $this;
    }

    /**
     * @param string $data
     *
     * @return \QR\QRDataInterface
     * @throws \QR\QRCodeException
     */
    protected function initDataInterface($data){
        $interface = $this->options->dataInterface;

        if(!class_exists($interface)){
            throw new QRCodeException('invalid data interface');
        }

        return new $interface($this->options, $this->datamode);
    }

    /**
     * @inheritdoc
     */
    public function initMatrix($maskPattern){
        return $this->dataInterface->initMatrix($maskPattern);
    }

    /**
     * @return int
     */
    public function getVersion(){
        return $this->version;
    }

}