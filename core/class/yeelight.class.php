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

/* * ***************************Includes********************************* */
require_once __DIR__  . '/../../../../core/php/core.inc.php';

class yeelight extends eqLogic {
    /*     * *************************Attributs****************************** */



    /*     * ***********************Methode static*************************** */

    /*
     * Fonction exécutée automatiquement toutes les minutes par Jeedom
      public static function cron() {

      }
     */


    /*
     * Fonction exécutée automatiquement toutes les heures par Jeedom
      public static function cronHourly() {

      }
     */

    /*
     * Fonction exécutée automatiquement tous les jours par Jeedom
      public static function cronDaily() {

      }
     */
    public static function pull() {
		foreach (self::byType('yeelight') as $eqLogic) {
			$eqLogic->refresh();
		}
	}
    
    public static function sendCommand($ip, $port, $data) {
        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if (!$socket) {
            log::add('yeelight','debug', 'Erreur à la création : '.socket_strerror(socket_last_error($socket)));
        } else {
            log::add('yeelight','debug', 'Socket is created');
            $connected = socket_connect($socket, $ip, $port);
            if ($connected) {
                log::add('yeelight','debug', 'Socket is connected');
                $data = json_encode($data) . "\r\n";
                $ret = socket_send($socket, $data, strlen($data), 0);
                if ($ret === false) {
                    log::add('yeelight','debug', 'Erreur à l\'envoie : '.socket_strerror(socket_last_error($socket)));
                } else {
                    log::add('yeelight','debug', 'Socket is send');
                    $data = socket_read($socket, 4096, PHP_BINARY_READ);
                    if ($data === false) {
                        log::add('yeelight','debug', 'Erreur à la lecture : '.socket_strerror(socket_last_error($socket)));
                    } else {
                        log::add('yeelight','debug', 'Socket is read. Data : '.$data);
                    }
                    socket_close($socket);
                    if ($data) {
                        return json_decode($data, true);
                    } else {
                        return false;
                    }
                }
            } else {
                log::add('yeelight','debug', 'Erreur à la connexion : '.socket_strerror(socket_last_error($socket)));
            }
        }
        return false;
    }

    /*     * *********************Méthodes d'instance************************* */

    public function getProp($properties) {
        $data = [
            'id' => hexdec($this->getLogicalId()),
            'method' => 'get_prop',
            'params' => $properties,
        ];
        log::add('yeelight', 'debug', 'Send getProp command. Parameters : '.implode(', ', $properties));
        $response = yeelight::sendCommand($this->getConfiguration('bulb_ip',''), $this->getConfiguration('bulb_port',''), $data);
        return $response;
    }
    
    public function toggle() {
        $data = [
            'id' => hexdec($this->getLogicalId()),
            'method' => 'toggle',
            'params' => array(),
        ];
        log::add('yeelight', 'debug', 'Send toggle command');
        $response = yeelight::sendCommand($this->getConfiguration('bulb_ip',''), $this->getConfiguration('bulb_port',''), $data);
        if ($response['result'][0] == 'ok') {
            log::add('yeelight', 'debug', 'Command is successfully executed');
            $this->refresh();
        } else {
            log::add('yeelight', 'debug', 'Command is failed. Error : '.$response['error']);
        }
        return $response;
    }
    
    public function set_power_on() {
        $params = ["on", "smooth", 1500];
        $data = [
            'id' => hexdec($this->getLogicalId()),
            'method' => 'set_power',
            'params' => $params,
        ];
        log::add('yeelight', 'debug', 'Send set_power command');
        $response = yeelight::sendCommand($this->getConfiguration('bulb_ip',''), $this->getConfiguration('bulb_port',''), $data);
        if ($response['result'][0] == 'ok') {
            log::add('yeelight', 'debug', 'Command is successfully executed');
            $this->refresh();
        } else {
            log::add('yeelight', 'debug', 'Command is failed. Error : '.$response['error']);
        }
        return $response;
    }
    
    public function set_power_off() {
        $params = ["off", "smooth", 1500];
        $data = [
            'id' => hexdec($this->getLogicalId()),
            'method' => 'set_power',
            'params' => $params,
        ];
        log::add('yeelight', 'debug', 'Send set_power command');
        $response = yeelight::sendCommand($this->getConfiguration('bulb_ip',''), $this->getConfiguration('bulb_port',''), $data);
        if ($response['result'][0] == 'ok') {
            log::add('yeelight', 'debug', 'Command is successfully executed');
            $this->refresh();
        } else {
            log::add('yeelight', 'debug', 'Command is failed. Error : '.$response['error']);
        }
        return $response;
    }
    
    public function set_color($_options) {
        $powerValue = (((6500 - 1700)/100) * $_options['slider']) + 1700;
        $params = [$powerValue, "smooth", 1500];
        $data = [
            'id' => hexdec($this->getLogicalId()),
            'method' => 'set_ct_abx',
            'params' => $params,
        ];
        log::add('yeelight', 'debug', 'Send set_ct_abx command');
        $response = yeelight::sendCommand($this->getConfiguration('bulb_ip',''), $this->getConfiguration('bulb_port',''), $data);
        if ($response['result'][0] == 'ok') {
            log::add('yeelight', 'debug', 'Command is successfully executed');
        } else {
            log::add('yeelight', 'debug', 'Command is failed. Error : '.$response['error']);
        }
        return $response;
    }
    
    public function set_rgb($_options) {
        if ($_options['color'] == '#000000') {
            $params = ["off", "smooth", 1500];
            $data = [
                'id' => hexdec($this->getLogicalId()),
                'method' => 'set_power',
                'params' => $params,
            ];
            log::add('yeelight', 'debug', 'Send set_power command');
            $response = yeelight::sendCommand($this->getConfiguration('bulb_ip',''), $this->getConfiguration('bulb_port',''), $data);
        }
        else {
            $params = [hexdec($_options['color']), "smooth", 1500];
            $data = [
                'id' => hexdec($this->getLogicalId()),
                'method' => 'set_rgb',
                'params' => $params,
            ];
            log::add('yeelight', 'debug', 'Send set_rgb command');
            $response = yeelight::sendCommand($this->getConfiguration('bulb_ip',''), $this->getConfiguration('bulb_port',''), $data);
        }
        if ($response['result'][0] == 'ok') {
            log::add('yeelight', 'debug', 'Command is successfully executed');
        } else {
            log::add('yeelight', 'debug', 'Command is failed. Error : '.$response['error']['message']);
        }
        return $response;
    }
    
    public function set_bright($_options) {
        $params = [intval($_options['slider']), "smooth", 1500];
        $data = [
            'id' => hexdec($this->getLogicalId()),
            'method' => 'set_bright',
            'params' => $params,
        ];
        log::add('yeelight', 'debug', 'Send set_bright command');
        $response = yeelight::sendCommand($this->getConfiguration('bulb_ip',''), $this->getConfiguration('bulb_port',''), $data);
        if ($response['result'][0] == 'ok') {
            log::add('yeelight', 'debug', 'Command is successfully executed');
        } else {
            log::add('yeelight', 'debug', 'Command is failed. Error : '.$response['error']['message']);
        }
        return $response;
    }
    
    public function set_default() {
        $data = [
            'id' => hexdec($this->getLogicalId()),
            'method' => 'set_default',
            'params' => array(),
        ];
        log::add('yeelight', 'debug', 'Send set_default command');
        $response = yeelight::sendCommand($this->getConfiguration('bulb_ip',''), $this->getConfiguration('bulb_port',''), $data);
        if ($response['result'][0] == 'ok') {
            log::add('yeelight', 'debug', 'Command is successfully executed');
        } else {
            log::add('yeelight', 'debug', 'Command is failed. Error : '.$response['error']['message']);
        }
        return $response;
    }

    public function preInsert() {
        
    }

    public function postInsert() {
        
    }

    public function preSave() {
        
    }

    public function postSave() {
        
    }

    public function preUpdate() {
        
    }

    public function postUpdate() {
        if ( $this->getIsEnable() )
		{
            $refresh = $this->getCmd(null, 'refresh');
            if (!is_object($refresh)) {
                $refresh = new yeelightCmd();
            }
            $refresh->setName('Rafraichir');
            $refresh->setOrder(0);
            $refresh->setEqLogic_id($this->getId());
            $refresh->setLogicalId('refresh');
            $refresh->setType('action');
            $refresh->setSubType('other');
            $refresh->save();
            
            $cmd = $this->getCmd(null,'power');
            if (!is_object($cmd)) {
                $cmd = new yeelightCmd();
            }
            $cmd->setName('Etat ampoule');
            $cmd->setEqLogic_id($this->getId());
            $cmd->setLogicalId('power');
            $cmd->setOrder(1);
            $cmd->setType('info');
            $cmd->setSubType('binary');
            $cmd->setTemplate('dashboard', 'light');
            $cmd->setTemplate('mobile', 'light');
            $cmd->setDisplay('generic_type', 'LIGHT_STATE');
            $cmd->setDisplay('showNameOndashboard', '0');
            $cmd->setDisplay('showNameOnplan', '0');
            $cmd->setDisplay('showNameOnview', '0');
            $cmd->setDisplay('showNameOnmobile', '0');
            $cmd->setDisplay('forceReturnLineAfter', '1');
            $cmd->save();
            
            $cmd = $this->getCmd(null, 'color_mode');
			if ( ! is_object($cmd)) {
				$cmd = new yeelightCmd();
            }
            $cmd->setName('Mode');
            $cmd->setEqLogic_id($this->getId());
            $cmd->setLogicalId('color_mode');
            $cmd->setOrder(2);
            $cmd->setType('info');
            $cmd->setSubType('string');
            $cmd->setDisplay('generic_type', 'MODE_STATE');
            $cmd->save();
            
            $cmd = $this->getCmd(null, 'set_rgb');
            if ( ! is_object($cmd) ) {
                $cmd = new yeelightCmd();
            }
            $cmd->setName('Couleur');
            $cmd->setEqLogic_id($this->getId());
            $cmd->setLogicalId('set_rgb');
            $cmd->setOrder(3);
            $cmd->setType('action');
            $cmd->setSubType('color');
            $cmd->setIsVisible(0);
            $cmd->setDisplay('generic_type', 'LIGHT_SLIDER');
            $cmd->setDisplay('showNameOndashboard', '0');
            $cmd->setDisplay('showNameOnplan', '0');
            $cmd->setDisplay('showNameOnview', '0');
            $cmd->setDisplay('showNameOnmobile', '0');
            $cmd->setDisplay('forceReturnLineAfter', '1');
            $cmd->save();

            $cmd = $this->getCmd(null, 'set_power_on');
            if ( ! is_object($cmd) ) {
                $cmd = new yeelightCmd();
            }
            $cmd->setName('set_power_on');
            $cmd->setEqLogic_id($this->getId());
            $cmd->setLogicalId('set_power_on');
            $cmd->setOrder(4);
            $cmd->setType('action');
            $cmd->setSubType('other');
            $cmd->setDisplay('showNameOndashboard', '0');
            $cmd->setDisplay('showNameOnplan', '0');
            $cmd->setDisplay('showNameOnview', '0');
            $cmd->setDisplay('showNameOnmobile', '0');
            $cmd->setDisplay('icon', '<i class="icon jeedom-on"></i>');
            $cmd->save();
            
            $cmd = $this->getCmd(null, 'set_power_off');
            if ( ! is_object($cmd) ) {
                $cmd = new yeelightCmd();
            }
            $cmd->setName('set_power_off');
            $cmd->setEqLogic_id($this->getId());
            $cmd->setLogicalId('set_power_off');
            $cmd->setOrder(5);
            $cmd->setType('action');
            $cmd->setSubType('other');
            $cmd->setDisplay('showNameOndashboard', '0');
            $cmd->setDisplay('showNameOnplan', '0');
            $cmd->setDisplay('showNameOnview', '0');
            $cmd->setDisplay('showNameOnmobile', '0');
            $cmd->setDisplay('icon', '<i class="icon jeedom-off"></i>');
            $cmd->save();
            
            $cmd = $this->getCmd(null, 'set_default');
            if ( ! is_object($cmd) ) {
                $cmd = new yeelightCmd();
            }
            $cmd->setName('set_default');
            $cmd->setEqLogic_id($this->getId());
            $cmd->setLogicalId('set_default');
            $cmd->setOrder(6);
            $cmd->setType('action');
            $cmd->setSubType('other');
            $cmd->setDisplay('showNameOndashboard', '0');
            $cmd->setDisplay('showNameOnplan', '0');
            $cmd->setDisplay('showNameOnview', '0');
            $cmd->setDisplay('showNameOnmobile', '0');
            $cmd->setDisplay('icon', '<i class="icon jeedomapp-reload-manuel"></i>');
            $cmd->setDisplay('forceReturnLineAfter', '1');
            $cmd->save();
            
            $cmd = $this->getCmd(null, 'set_bright');
            if ( ! is_object($cmd) ) {
                $cmd = new yeelightCmd();
            }
            $cmd->setName('Luminosité');
            $cmd->setEqLogic_id($this->getId());
            $cmd->setLogicalId('set_bright');
            $cmd->setOrder(7);
            $cmd->setType('action');
            $cmd->setSubType('slider');
            $cmd->setIsVisible(0);
            $cmd->setDisplay('generic_type', 'LIGHT_SLIDER');
            $cmd->setDisplay('forceReturnLineAfter', '1');
            $cmd->save();

            $cmd = $this->getCmd(null, 'set_color');
            if ( ! is_object($cmd) ) {
                $cmd = new yeelightCmd();
            }
            $cmd->setName('Température des blancs');
            $cmd->setEqLogic_id($this->getId());
            $cmd->setLogicalId('set_color');
            $cmd->setOrder(8);
            $cmd->setType('action');
            $cmd->setSubType('slider');
            $cmd->setIsVisible(0);
            $cmd->setDisplay('generic_type', 'LIGHT_SLIDER');
            $cmd->setDisplay('forceReturnLineAfter', '1');
            $cmd->save();
            
            $cmd = $this->getCmd(null, 'updatetime');
			if ( ! is_object($cmd)) {
				$cmd = new yeelightCmd();
            }
            $cmd->setName('Dernier refresh');
            $cmd->setEqLogic_id($this->getId());
            $cmd->setLogicalId('updatetime');
            $cmd->setOrder(9);
            $cmd->setType('info');
            $cmd->setSubType('string');
            $cmd->save();
        }
    }

    public function preRemove() {
        
    }

    public function postRemove() {
        
    }
    
    public function refresh() {
        if ( $this->getIsEnable() ) {
            $eqpNetwork = eqLogic::byTypeAndSearhConfiguration('networks', $this->getConfiguration('bulb_ip'))[0];
            if (is_object($eqpNetwork)) {
                $statusCmd = $eqpNetwork->getCmd(null, 'ping');
                if (is_object($statusCmd) && $statusCmd->execCmd() == $statusCmd->formatValue(true)) {
                    $response = $this->getProp(array('power', 'color_mode'));
                    
                    $powerCmd = $this->getCmd(null, 'power');
                    if (is_object($powerCmd)) {
                        if ($powerCmd->formatValue($response['result'][0]) != $powerCmd->execCmd()) {
                            $powerCmd->setCollectDate('');
                            $powerCmd->event($response['result'][0]);
                        }
                    }
                    
                    $modeCmd = $this->getCmd(null, 'color_mode');
                    if (is_object($modeCmd)) {
                        $modeCmd->setCollectDate('');
                        switch ($response['result'][1])
                        {
                            case "1":
                                $modeCmd->event('Rgb');
                                break;
                            case "2":
                                $modeCmd->event('Température couleur');
                                break;
                            case "3":
                                $modeCmd->event('Hsv');
                                break;
                        }
                    }
                    
                    $colorCmd = $this->getCmd(null, 'set_color');
                    if (is_object($colorCmd)) {
                        if ($response['result'][0] == "on") {
                            $colorCmd->setIsVisible(1);
                        }
                        else {
                            $colorCmd->setIsVisible(0);
                        }
                        $colorCmd->save();
                    }
                    $rgbCmd = $this->getCmd(null, 'set_rgb');
                    if (is_object($rgbCmd)) {
                        if ($response['result'][0] == "on") {
                            $rgbCmd->setIsVisible(1);
                        }
                        else {
                            $rgbCmd->setIsVisible(0);
                        }
                        $rgbCmd->save();
                    }
                    $brightCmd = $this->getCmd(null, 'set_bright');
                    if (is_object($brightCmd)) {
                        if ($response['result'][0] == "on") {
                            $brightCmd->setIsVisible(1);
                        }
                        else {
                            $brightCmd->setIsVisible(0);
                        }
                        $brightCmd->save();
                    }
                    
                    $refresh = $this->getCmd(null, 'updatetime');
                    $refresh->event(date("d/m/Y H:i",(time())));
                    $mc = cache::byKey('yeelightWidgetmobile' . $this->getId());
                    $mc->remove();
                    $mc = cache::byKey('yeelightWidgetdashboard' . $this->getId());
                    $mc->remove();
                    $this->toHtml('mobile');
                    $this->toHtml('dashboard');
                    $this->refreshWidget();
                } else {
                    $powerCmd = $this->getCmd(null, 'power');
                    if (is_object($powerCmd)) {
                        $powerCmd->setCollectDate('');
                        $powerCmd->event(0);
                    }
                    $colorCmd = $this->getCmd(null, 'set_color');
                    if (is_object($colorCmd)) {
                        $colorCmd->setIsVisible(0);
                        $colorCmd->save();
                    }
                    $rgbCmd = $this->getCmd(null, 'set_rgb');
                    if (is_object($rgbCmd)) {
                        $rgbCmd->setIsVisible(0);
                        $rgbCmd->save();
                    }
                    $brightCmd = $this->getCmd(null, 'set_bright');
                    if (is_object($brightCmd)) {
                        $brightCmd->setIsVisible(0);
                        $brightCmd->save();
                    }
                }
            }
        }
    }

    /*
     * Non obligatoire mais permet de modifier l'affichage du widget si vous en avez besoin
      public function toHtml($_version = 'dashboard') {

      }
     */

    /*
     * Non obligatoire mais ca permet de déclencher une action après modification de variable de configuration
    public static function postConfig_<Variable>() {
    }
     */

    /*
     * Non obligatoire mais ca permet de déclencher une action avant modification de variable de configuration
    public static function preConfig_<Variable>() {
    }
     */

    /*     * **********************Getteur Setteur*************************** */
}

class yeelightCmd extends cmd {
    /*     * *************************Attributs****************************** */


    /*     * ***********************Methode static*************************** */


    /*     * *********************Methode d'instance************************* */

    /*
     * Non obligatoire permet de demander de ne pas supprimer les commandes même si elles ne sont pas dans la nouvelle configuration de l'équipement envoyé en JS
      public function dontRemoveCmd() {
      return true;
      }
     */

    public function execute($_options = array()) {
        $eqLogic = $this->getEqLogic();
        if (!is_object($eqLogic) || $eqLogic->getIsEnable() != 1) {
            throw new Exception(__('Equipement desactivé impossible d\éxecuter la commande : ' . $this->getHumanName(), __FILE__));
        }
		log::add('yeelight','debug','get '.$this->getLogicalId());
		switch ($this->getLogicalId()) {
            case "refresh":
                $eqLogic->refresh();
                $response['result'][0] = 'ok';
                break;
            case "set_power_on":
                $response = $eqLogic->set_power_on();
                break;
            case "set_power_off":
                $response = $eqLogic->set_power_off();
                break;
            case "set_color":
                $response = $eqLogic->set_color($_options);
                break;
            case "set_rgb":
                $response = $eqLogic->set_rgb($_options);
                break;
            case "set_bright":
                $response = $eqLogic->set_bright($_options);
                break;
            case "set_default":
                $response = $eqLogic->set_default();
                break;
		}
        if ($response['result'][0] == 'ok') {
            return true;
        } else {
            return false;
        }
    }

    /*     * **********************Getteur Setteur*************************** */
}


