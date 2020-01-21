<?php

/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */
 
function selectRead($socket, $sec = 0)
{
    $usec = $sec === null ? null : (($sec - floor($sec)) * 1000000);
    $r = array($socket);
    $ret = socket_select($r, $x, $x, $sec, $usec);
    if ($ret === false) {
        throw new Exception('Failed to select socket for reading');
    }
    return !!$ret;
}

function read($socket, $length, $type = PHP_BINARY_READ)
{
    $data = socket_read($socket, $length, $type);
    if ($data === false) {
        throw new Exception('Read');
    }
    return $data;
}

function formatResponse(string $data)
{
    return array_reduce(explode("\n", trim($data)), function ($carry, $item) {
        $res = explode(':', $item, 2);
        $carry[trim(reset($res))] = end($res);
        return $carry;
    }, []);
}

function ExtractIpAndPort(string $location)
{
    $address = explode('yeelight://', $location);
    return explode(':', end($address));
}

try {
    require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';
    include_file('core', 'authentification', 'php');

    if (!isConnect('admin')) {
        throw new Exception(__('401 - Accès non autorisé', __FILE__));
    }
    
    ajax::init();

    if (init('action') == 'discover') {
        $msg = "M-SEARCH * HTTP/1.1\r\nHOST: 239.255.255.250:1982\r\nMAN: \"ssdp:discover\"\r\nST: wifi_bulb\r\n";
        $len = strlen($msg);
        $socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        socket_sendto($socket, $msg, $len, 0, '239.255.255.250', 1982);
        socket_set_nonblock($socket);
        while (selectRead($socket, 1)) {
            $data = formatResponse(read($socket, 4096));
            log::add('yeelight','debug', 'Receive : '.json_encode($data));
            $id = trim($data['id']);
            $bulb = eqLogic::byLogicalId('bulb_'.$id, 'yeelight', $_multiple = false);
            if ( !is_object($bulb)) {
                $bulb = new yeelight();
                $bulb->setName('bulb_'.$id);
                $bulb->setLogicalId('bulb_'.$id);
                $bulb->setEqType_name('yeelight');
                $bulb->setCategory('light', '1');
            }
            list($ip, $port) = ExtractIpAndPort(trim($data['Location']));
            $bulb->setConfiguration("bulb_ip",$ip);
            $bulb->setConfiguration("bulb_port",$port);
            $bulb->save();
            log::add('yeelight','debug','Bulb '.$bulb->getName().' is created');
        }
        ajax::success();
    }

    throw new Exception(__('Aucune méthode correspondante à : ', __FILE__) . init('action'));
    /*     * *********Catch exeption*************** */
} catch (Exception $e) {
    ajax::error(displayException($e), $e->getCode());
}

