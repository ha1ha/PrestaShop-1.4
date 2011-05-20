<?php 
/*
* 2007-2011 PrestaShop 
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2011 PrestaShop SA
*  @version  Release: $Revision: 1.4 $
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

/*
 * File called by ajax. It's like a controler, you have to send the
 * method name of the webservice and implement it.
 * Each Name method allow to instanciate an object containing
 * methods to manage correctly the data and name fields
 */

require_once(dirname(__FILE__).'../../../config/config.inc.php');
require_once(dirname(__FILE__).'../../../init.php');
require(dirname(__FILE__).'/classes/MRCreateTickets.php');
require(dirname(__FILE__).'/classes/MRGetTickets.php');
require(dirname(__FILE__).'/classes/MRManagement.php');

$method = Tools::getValue('method');
$params = array();
$result = array();

// Method name allow to instanciate his object to properly call the 
// implemented interface method and do his job
switch($method)
{
	case 'MRCreateTickets':
		$params['orderIdList'] = Tools::getValue('order_id_list');
		$params['totalOrder'] = Tools::getValue('numSelected');
		$params['weightList'] = Tools::getValue('weight_list');
		break;
	case 'MRGetTickets':
		$params['detailedExpeditionList'] = Tools::getValue('detailedExpeditionList');
		break;
	case 'DeleteHistory':
		$params['historyIdList'] = Tools::getValue('history_id_list');
		break;
	default:
}

// Try to instanciate the method object name and call the necessaries method
try 
{
	if (class_exists($method))
	{
		$obj = new $method($params);
		
		// Verify that the class implement correctly the interface
		// Else use a Management class to do some ajax stuff
		if (($obj instanceof IMondialRelayWSMethod))
		{
			$obj->init();
			$obj->send();
			$result = $obj->getResult();
		}
		unset($obj);
	}
	else if (($management = new MRManagement($params)) &&
				method_exists($management, $method))
			$result = $management->{$method}();
	else 
		throw new Exception('Method Class : '.$method.' can\'t be found');
	unset($management);
}
catch(Exception $e)
{
	echo Tools::jsonEncode(array('other' => array('error' => array($e->getMessage()))));
	exit(-1);
}
echo Tools::jsonEncode($result);
exit(0);
?>