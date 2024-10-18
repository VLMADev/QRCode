<?php

namespace QR;

interface SettingsContainerInterface {
    public function __construct($properties = null);
    public function __get($property);
    public function __set($property, $value);
    public function __isset($property);
    public function toArray();
}