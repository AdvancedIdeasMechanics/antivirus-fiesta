<?php
/**
 * Created by PhpStorm.
 * User: Matthew Pallotta
 * Date: 8/27/18
 * Time: 1:57 PM
 */

namespace Advancedideasmechanics\Antivirus\Adapter;

interface ClamavScanInterface {
    public function scan($fileHandle, $fileSize, $options);
}