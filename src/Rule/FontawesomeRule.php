<?php
/**
 * @package		CGNewFlag content plugin
 * @author		ConseilGouz
 * @copyright	Copyright (C) 2024 ConseilGouz. All rights reserved.
 * @license		GNU/GPL v2; see LICENSE.php
 **/

namespace ConseilGouz\Plugin\Content\CGNewflag\Rule;

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormRule;
use Joomla\Registry\Registry;
use Joomla\CMS\Language\Text;

class FontawesomeRule extends FormRule
{
    public function test(\SimpleXMLElement $element, $value, $group = null, ?Registry $input = null, ?Form $form = null)
    {
        // get showon to find field name
        $showon = (string)$element['showon'];
        $el = explode(':', $showon);
        $type = $el[0];
        $params = $input->get('params');
        if ($params->$type  == 'badge') { // badge : exit
            return true;
        }
        if (!$value) {
            Factory::getApplication()->enqueueMessage(Text::_('CG_NOTEMPTY'), 'error');
            return false;
        }
        if (substr($value, 0, 3) != 'fa-') {
            Factory::getApplication()->enqueueMessage(Text::_('CG_FA_START'), 'error');
            return false;
        }
        return true;

    }
}
