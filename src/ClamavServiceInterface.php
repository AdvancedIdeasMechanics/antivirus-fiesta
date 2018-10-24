<?php
/**
 * Created by PhpStorm.
 * User: Matthew Pallotta
 * Date: 8/9/18
 * Time: 9:00 AM
 */
namespace Advancedideasmechanics\Antivirus;

interface ClamavServiceInterface {

    public function sendToScanner($file);

    public function hello();
}