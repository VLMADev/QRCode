<?php

namespace QR;
use QR\QRCodeException;
abstract class SettingsContainerAbstract implements SettingsContainerInterface{

    /**
     * SettingsContainerAbstract constructor.
     *
     * @param array|null $properties
     */
    public function __construct($properties = null){
        if(!empty($properties)){
            foreach($properties as $key => $value){
                $this->__set($key, $value);
            }
        }
    }

    /**
     * @param string $property
     *
     * @return mixed
     */
    public function __get($property){
        if(!property_exists($this, $property)){
            throw new QRCodeException('undefined property: '.$property);
        }

        return $this->{$property};
    }

    /**
     * @param string $property
     * @param mixed  $value
     *
     * @return void
     */
    public function __set($property, $value){
        if(!property_exists($this, $property)){
            throw new QRCodeException('undefined property: '.$property);
        }

        $this->{$property} = $value;
    }

    /**
     * @param string $property
     *
     * @return bool
     */
    public function __isset($property){
        return property_exists($this, $property);
    }

    /**
     * @return array
     */
    public function toArray(){
        return get_object_vars($this);
    }
}