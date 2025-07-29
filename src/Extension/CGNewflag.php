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
        $options = $this->params->get('options');
        $found = false;
        foreach ($options as $option) {
            $contexts = explode(',', $option->contexts);
            if (in_array($context, $contexts)) {
                $found = true;
                break;
            }
        }
        if (!$found) { // invalid context
            return;
        }
        $input               = Factory::getApplication()->input;
        $this->view          = $input->getCmd('view');
        $this->article_id    = $event->getItem()->id;
        $field = "";
        if ($context == 'com_tags.tag') {
            // tags : add core_ to key
            $field = "core_";
        }
        $title = $field.'title';
        foreach ($options as $option) {
            $this->article_title = $event->getItem()->$title;
            $date = $field.$option->datefield;
            if ($date == 'core_modified') { // tags
                $date .= '_time';
            }
            $bg = $option->bgtype == 'pick' ? $option->bgcolor : 'var('.$option->bgvar.')';
            $font =  $option->fonttype == 'pick' ? $option->fontcolor : 'var('.$option->fontvar.')'  ;
            $fontsize = $option->fontsize;
            if (isset($event->getItem()->$date)) {
                $nbday = $option->length;
                $tmp = date('Y-m-d H:i:s', mktime(date("H"), date("i"), 0, date("m"), date("d") - intval($nbday), date("Y")));
                if ($option->type == 'badge') {
                    $style = "style='background-color:".$bg.";color:".$font.";font-size:".$fontsize."em;'";
                    $new = ($tmp < $event->getItem()->$date) ? '<span class="cgnewflag_badge" '.$style.'>'.Text::_($option->badgetext).'</span>' : '';
                } else {
                    $style = "style='color:".$bg.";font-size:".$fontsize."em;'";
                    $new = ($tmp < $event->getItem()->$date) ? '<i class="cgnewflag_icon fa-solid '.$option->icon.'" '.$style.' title="'.Text::_($option->badgetext).'"></i>' : '';
                }
                if (!$new) {
                    continue;
                }
                $tag = $option->tag;
                if ($option->posflg == 'header') {
                    $event->addResult($new);
                } elseif ($option->posflg == 'after') { // done in js
                    $event->getItem()->$title .= '<'.$tag.'>';
                } elseif ($option->posflg == 'before') { // done in js
                    $event->getItem()->$title = '<'.$tag.'>'.$event->getItem()->$title;
                }
                break;
            }
        }
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
        $jsparams = [];
        foreach ($options as $option) {
            $tag = $option->tag;
            $bg = $option->bgtype == 'pick' ? $option->bgcolor : 'var('.$option->bgvar.')';
            $font =  $option->fonttype == 'pick' ? $option->fontcolor : 'var('.$option->fontvar.')'  ;
            $fontsize = $option->fontsize;
            if ($option->type == 'badge') {
                $style = "style='background-color:".$bg.";color:".$font.";font-size:".$fontsize."em;'";
                $new = '<span class="cgnewflag_badge" '.$style.'>'.Text::_($option->badgetext).'</span>';
            } else {
                $style = "style='color:".$bg.";font-size:".$fontsize."em;'";
                $new = '<i class="cgnewflag_icon fa-solid '.$option->icon.'" '.$style.' title="'.Text::_($option->badgetext).'"></i>';
            }

            $jsparams[] = array('newstr' => $new,'posflg' => $option->posflg,
                        'tag' => $tag);
        }
        $document->addScriptOptions(
            'plg_content_cgnewflag',
            array('params' => $jsparams)
        );

    }
}
