<?php

/**
 * @package		CGNewFlag content plugin
 * @author		ConseilGouz
 * @copyright	Copyright (C) 2025 ConseilGouz. All rights reserved.
 * @license		GNU/GPL v2; see LICENSE.php
 **/

defined('_JEXEC') or die;

// use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;

/**
 * Layout variables
 * -----------------
 * @var   string   $context  The context of the content being passed to the plugin
 * @var   object   &$row     The article object
 * @var   object   &$params  The article params
 * @var   integer  $page     The 'page' number
 * @var   array    $parts    The context segments
 * @var   string   $path     Path to this file
 */

    $input               = Factory::getApplication()->input;
    $this->view          = $input->getCmd('view');
    $this->article_id    = $event->getItem()->id;
    $field = "";
    $span = "span";
    if ($context == 'com_tags.tag') {
        // tags : add core_ to key
        $field = "core_";
    }
    $title = $field.'title';

    $jsparams = [];

    foreach ($options as $option) {
        $this->article_title = $event->getItem()->$title;
        $date = $field.$option->datefield;
        if ($date == 'core_modified') { // tags
            $date .= '_time';
        }
        $bg = $option->bgtype == 'pick' ? $option->bgcolor : 'var('.$option->bgvar.')';
        $font =  $option->fonttype == 'pick' ? $option->fontcolor : 'var('.$option->fontvar.')'  ;
        $fontsize = $option->fontsize;
        $tag = $option->tag;
        if (isset($event->getItem()->$date)) {
            $nbday = $option->length;
            $tmp = date('Y-m-d H:i:s', mktime(date("H"), date("i"), 0, date("m"), date("d") - intval($nbday), date("Y")));
            if ($option->type == 'badge') {
                $style = "style='background-color:".$bg.";color:".$font.";font-size:".$fontsize."em;'";
                $flag = '<'.$span.' class="cgnewflag_badge" '.$style.'>'.Text::_($option->badgetext).'</'.$span.'>';
                $new = ($tmp < $event->getItem()->$date) ? $flag : '';
            } else {
                $style = "style='color:".$bg.";font-size:".$fontsize."em;'";
                $flag = '<i class="cgnewflag_icon fa-solid '.$option->icon.'" '.$style.' title="'.Text::_($option->badgetext).'"></i>';
                $new = ($tmp < $event->getItem()->$date) ? $flag: '';
            }
            $jsparams[] = array('newstr' => $flag,'posflg' => $option->posflg,
                        'tag' => $tag);
            if (!$new) {
                continue;
            }
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
    if ($this->params->get('css', '')) {
        $wa->addInlineStyle($this->params->get('css'));
    }
    $wa->registerAndUseStyle('cgnewflag', $plg.'/css/cgnewflag.css');
    if ((bool)$app->getConfig()->get('debug')) { // Mode debug
        $document->addScript(''.URI::base(true).'/'.$plg.'/js/cgnewflag.js');
    } else {
        $wa->registerAndUseScript('cgnewflag', $plg.'/js/cgnewflag.js');
    }
    $document->addScriptOptions(
            'plg_content_cgnewflag',
            array('params' => $jsparams)
    );
