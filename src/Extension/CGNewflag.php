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
    protected $article_id;
    protected $article_title;
    protected $view;

    public static function getSubscribedEvents(): array
    {
        return [
            'onContentBeforeDisplay'	=> 'onBefore',
        ];
    }
    public function onBefore($event)
    {
        $context = $event->getContext();
        $contexts = explode(',', $this->params->get('contexts', 'com_content.article,com_content.category'));
        if (!in_array($context, $contexts)) {
            return true;
        }
        $input               = Factory::getApplication()->input;
        $this->view          = $input->getCmd('view');
        $this->article_id    = $event->getItem()->id;
        $this->article_title = $event->getItem()->title;
        $date = $this->params->get('datefield', 'publish_up');
        if (isset($event->getItem()->$date)) {
            $nbday = $this->params->get('length', 10);
            $tmp = date('Y-m-d H:i:s', mktime(date("H"), date("i"), 0, date("m"), date("d") - intval($nbday), date("Y")));
            if ($this->params->get('type', 'badge') == 'badge') {
                $new = ($tmp < $event->getItem()->$date) ? '<span class="cgnewflag_badge">'.Text::_('PLG_CONTENT_CGNEWFLAG_NEW').'</span>' : '';
            } else {
                $new = ($tmp < $event->getItem()->$date) ? '<i class="cgnewflag_icon fa-solid '.$this->params->get('icon', 'fa-star').'" title="'.Text::_('PLG_CONTENT_CGNEWFLAG_NEW').'"></i>' : '';
            }
            if ($new) {
                $app = Factory::getApplication();
                $plg	= 'media/plg_content_cgnewflag/';
                $document = $app->getDocument();
                $wa = $document->getWebAssetManager();
                $wa->registerAndUseStyle('cgnewflag', $plg.'/css/cgnewflag.css');
                if ($css = $this->params->get('css', '')) {
                    $customCSS = <<< CSS
					$css
					CSS;
                    $wa->addInlineStyle($customCSS, ['name' => 'cgnewflag.asset']);
                }
                if ((bool)$app->getConfig()->get('debug')) { // Mode debug
                    $document->addScript(''.URI::base(true).'/'.$plg.'/js/cgnewflag.js');
                } else {
                    $wa->registerAndUseScript('cgnewflag', $plg.'/js/cgnewflag.js');
                }
                $bg = $this->params->get('bg-type', 'pick') == 'pick' ? $this->params->get('bg-color', '#dc3545') : $this->params->get('bg-var', '--bg-alert')  ;
                $font =  $this->params->get('font-type', 'pick') == 'pick' ? $this->params->get('font-color', '#fff') : $this->params->get('font-var', '--bg-white')  ;
                $fontsize = $this->params->get('font-size', '1');
                $document->addScriptOptions(
                    'plg_content_cgnewflag',
                    array(  'bg' => $bg, 'font' => $font,'fontsize' => $fontsize,
                            'newstr' => $new,'posflg' => $this->params->get('posflg', 'before'))
                );
                if ($this->params->get('posflg', 'before') == 'header') {
                    $event->addResult($new);
                } elseif ($this->params->get('posflg', 'before') == 'after') { // done in js
                    $event->getItem()->title .= '<cgnewflag>';
                } elseif ($this->params->get('posflg', 'before') == 'before') { // done in js
                    $event->getItem()->title = '<cgnewflag>'.$event->getItem()->title;
                }
            }
        }
    }
}
