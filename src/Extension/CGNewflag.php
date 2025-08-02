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
use Joomla\CMS\Plugin\PluginHelper;
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

        $path = PluginHelper::getLayoutPath('content', 'cgnewflag', 'default');
        // Render the layout
        ob_start();
        include $path;
        $html = ob_get_clean();
        $event->addResult($html);
    }
}
