<?php

namespace App\Services;

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\SvgWriter;

class QrCodeService
{
    /**
     * Return the raw SVG markup for a QR code.
     */
    public function svg(string $content, int $size = 300): string
    {
        $writer = new SvgWriter;

        return $writer->write(
            new QrCode(data: $content, size: $size, margin: 10),
            options: [SvgWriter::WRITER_OPTION_EXCLUDE_XML_DECLARATION => true],
        )->getString();
    }

    /**
     * Return the QR code as a data URI suitable for <img src="...">.
     */
    public function dataUri(string $content, int $size = 300): string
    {
        $writer = new SvgWriter;

        return $writer->write(
            new QrCode(data: $content, size: $size, margin: 10),
        )->getDataUri();
    }
}