<?php
/**
 * Created by PhpStorm.
 * User: Matthew Pallotta
 * Date: 8/27/18
 * Time: 1:52 PM
 */

namespace Advancedideasmechanics\Antivirus\Adapter;


interface ClamavSocketInterface
{
    public function openSocket($options);

    public function closeSocket($socket);

    public function checkSocket($options);

    public function send($socket, $chunk, $end);
}