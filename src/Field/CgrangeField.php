<?php
/**
 * @package		CGNewFlag content plugin
 * @author		ConseilGouz
 * @copyright	Copyright (C) 2024 ConseilGouz. All rights reserved.
 * @license		GNU/GPL v2; see LICENSE.php
 **/
namespace ConseilGouz\Plugin\Content\CGNewflag\Field;

defined('JPATH_PLATFORM') or die;
use Joomla\CMS\Form\Field\RangeField;
use Joomla\CMS\Factory;

class CgrangeField extends RangeField
{
    public $type = 'Cgrange';

    /**
     * Name of the layout being used to render the field
     *
     * @var    string
     * @since  3.7
     */
    protected $layout = 'cgrange';

    /**
     * Unit
     *
     * @var    string
     */

    protected $unit = "";
    /* module's information */
    public $_ext = "plg";
    public $_type = "content";
    public $_name = "cgnewflag";

    protected function getLayoutPaths()
    {
        $paths = parent::getLayoutPaths();
        $paths[] = dirname(__DIR__).'/../layouts';
        return $paths;

    }

    /**
     * Method to get the field input markup.
     *
     * @return  string  The field input markup.
     *
     * @since   3.2
     */
    protected function getInput()
    {
        return $this->getRenderer($this->layout)->render($this->collectLayoutData());
    }
    /**
     * Method to get the data to be passed to the layout for rendering.
     * The data is cached in memory.
     *
     * @return  array
     *
     * @since 5.1.0
     */
    protected function collectLayoutData(): array
    {
        if ($this->layoutData) {
            return $this->layoutData;
        }

        $this->layoutData = $this->getLayoutData();
        return $this->layoutData;
    }
    protected function getLayoutData()
    {
        $data      = parent::getLayoutData();
        $extraData = ["unit" => $this->element['unit']
        ];
        return array_merge($data, $extraData);
    }
}
