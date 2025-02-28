<?php

/**
 * @package		CGNewFlag content plugin
 * @author		ConseilGouz
 * @copyright	Copyright (C) 2025 ConseilGouz. All rights reserved.
 * @license		GNU/GPL v2; see LICENSE.php
 **/

namespace ConseilGouz\Plugin\Content\CGNewflag\Extension;

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\Event\SubscriberInterface;

final class CGNewflag extends CMSPlugin implements SubscriberInterface
{
    public $myname = 'CGNewflag';
    protected $autoloadLanguage = true;

    public static function getSubscribedEvents(): array
    {
        return [
            'onContentPrepare'   => 'onPrepare',
        ];
    }
    public function onPrepare($event)
    {
        $context = $event[0];
        $contexts = explode(',', $this->params->get('contexts', 'com_content.article,com_content.category'));
        if (!in_array($context, $contexts)) {
            return true;
        }
        $article = $event[1];
        $date = $this->params->get('datefield', 'publish_up');
        if (isset($article->$date)) {
            $nbday = $this->params->get('length', 10);
            $tmp = date('Y-m-d H:i:s', mktime(date("H"), date("i"), 0, date("m"), date("d") - intval($nbday), date("Y")));
            if ($this->params->get('type', 'badge') == 'badge') {
                $new = ($tmp < $article->$date) ? ' <span class="cgnewflag_badge">'.Text::_('PLG_CONTENT_CGNEWFLAG_NEW').'</span> ' : '';
            } else {
                $new = ($tmp < $article->$date) ? ' <i class="cgnewflag_icon fa-solid '.$this->params->get('icon', 'fa-star').'" title="'.Text::_('PLG_CONTENT_CGNEWFLAG_NEW').'"></i> ' : '';
            }
            if ($new) {
                $article->text = $new.$article->text;
				$article->introtext = $new.$article->introtext;
                $plg	= 'media/plg_content_cgnewflag/';
                $document = Factory::getApplication()->getDocument();
                $wa = Factory::getApplication()->getDocument()->getWebAssetManager();
                $wa->registerAndUseStyle('cgnewflag', $plg.'/css/cgnewflag.css');
				if ($css = $this->params->get('css','')) {
					$customCSS = <<< CSS
					$css
					CSS;					
				    $wa->addInlineStyle($customCSS, ['name' => 'cgnewflag.asset']);
				}
                if ((bool)Factory::getConfig()->get('debug')) { // Mode debug
                    $document->addScript(''.URI::base(true).'/'.$plg.'/js/cgnewflag.js');
                } else {
                    $wa->registerAndUseScript('cgnewflag', $plg.'/js/cgnewflag.js');
                }
                $bg = $this->params->get('bg-type', 'pick') == 'pick' ? $this->params->get('bg-color', '#dc3545') : $this->params->get('bg-var', '--bg-alert')  ;
                $font =  $this->params->get('font-type', 'pick') == 'pick' ? $this->params->get('font-color', '#fff') : $this->params->get('font-var', '--bg-white')  ;
                $fontsize = $this->params->get('font-size', '1');
                $document->addScriptOptions(
                    'plg_content_cgnewflag',
                    array('bg' => $bg, 'font' => $font,'fontsize' => $fontsize)
                );
                return true;
            }
        }
        return true;
    }
}
