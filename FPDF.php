<?php

namespace QR;

class FPDF {
    public function __construct($orientation = 'P', $unit = 'mm', $size = 'A4') {}
    public function AddPage($orientation = '', $size = '', $rotation = 0) {}
    public function SetFont($family, $style = '', $size = 0) {}
    public function Cell($w, $h = 0, $txt = '', $border = 0, $ln = 0, $align = '', $fill = false, $link = '') {}
    public function SetFillColor($r, $g = null, $b = null) {}
    public function Rect($x, $y, $w, $h, $style = '') {}
    public function Output($dest = '', $name = '', $isUTF8 = false) {}
}