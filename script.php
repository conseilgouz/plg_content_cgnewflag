<?php
/**
* CG New Flag Plugin  - Joomla 4.x/5x Module
* copyright 		: Copyright (C) 2025 ConseilGouz. All rights reserved.
* license    		: https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
*/
// No direct access to this file
defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\Filesystem\Folder;
use Joomla\CMS\Version;
use Joomla\Database\DatabaseInterface;
use Joomla\Filesystem\File;

class plgcontentcgnewflagInstallerScript
{
    private $min_joomla_version      = '4.0.0';
    private $min_php_version         = '7.4';
    private $name                    = 'Plugin CG NewFlag';
    private $exttype                 = 'plugin';
    private $extname                 = 'cgnewflag';
    private $previous_version        = '';
    private $dir           = null;
    private $installerName = 'plgcontentcgnewflaginstaller';
    private $lang;
    public function __construct()
    {
        $this->dir = __DIR__;
        $this->lang = Factory::getApplication()->getLanguage();
        $this->lang->load($this->extname);
    }

    public function preflight($type, $parent)
    {
        if (! $this->passMinimumJoomlaVersion()) {
            $this->uninstallInstaller();
            return false;
        }

        if (! $this->passMinimumPHPVersion()) {
            $this->uninstallInstaller();
            return false;
        }
        // To prevent installer from running twice if installing multiple extensions
        if (file_exists($this->dir . '/' . $this->installerName . '.xml')) {
            return false;
        }
    }
    public function postflight($type, $parent)
    {
        if (($type == 'install') || ($type == 'update')) { // remove obsolete dir/files
            $this->postinstall_cleanup();
        }

        switch ($type) {
            case 'install': $message = Text::_('ISO_POSTFLIGHT_INSTALLED');
                break;
            case 'uninstall': $message = Text::_('ISO_POSTFLIGHT_UNINSTALLED');
                break;
            case 'update': $message = Text::_('ISO_POSTFLIGHT_UPDATED');
                break;
            case 'discover_install': $message = Text::_('ISO_POSTFLIGHT_DISC_INSTALLED');
                break;
        }
        return true;
    }
    private function postinstall_cleanup()
    {

        $obsloteFolders = ['language'];
        // Remove plugins' files which load outside of the component. If any is not fully updated your site won't crash.
        foreach ($obsloteFolders as $folder) {
            $f = JPATH_SITE . '/plugins/plg_content_'.$this->extname.'/' . $folder;

            if (!@file_exists($f) || !is_dir($f) || is_link($f)) {
                continue;
            }

            Folder::delete($f);
        }
        $obsoleteFiles = [
            sprintf("%s/language/en-GB/en-GB.plg_content_%s.ini", JPATH_ADMINISTRATOR, $this->extname),
            sprintf("%s/language/en-GB/en-GB.plg_content_%s.sys.ini", JPATH_ADMINISTRATOR, $this->extname),
            sprintf("%s/language/fr-FR/fr-FR.plg_content_%s.ini", JPATH_ADMINISTRATOR, $this->extname),
            sprintf("%s/language/fr-FR/fr-FR.plg_content_%s.sys.ini", JPATH_ADMINISTRATOR, $this->extname),
            JPATH_SITE . '/plugins/plg_content_'.$this->extname.'/cgchangelog.php'
        ];
        foreach ($obsoleteFiles as $file) {
            if (@is_file($file)) {
                File::delete($file);
            }
        }
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true);
        $query->select('manifest_cache,params');
        $query->from($db->quoteName('#__extensions'));
        $query->where('type = "plugin"');
        $query->where('element = "cgnewflag"');
        $db->setQuery($query);
        $res = $db->loadObject();
        if ($res) { // already installed
            $params = $res->params;
            if (substr($params, 0, 9) != '{"options') { // need to update parameters
                $css = strpos($params, ',"css');
                $new = '{"options":{"options0":'.substr($params, 0, $css).'}}'.substr($params, $css);
                $conditions = array(
                    $db->qn('type') . ' = ' . $db->q('plugin'),
                    $db->qn('element') . ' = ' . $db->quote($this->extname)
                    );
                $fields = array($db->qn('params') . ' = '.$db->q($new));
                $query = $db->getQuery(true);
                $query->update($db->quoteName('#__extensions'))->set($fields)->where($conditions);
                $db->setQuery($query);
                try {
                    $db->execute();
                } catch (RuntimeException $e) {
                    Log::add('unable to update '.$this->name, Log::ERROR, 'jerror');
                }
            }
        }
        $conditions = array(
            $db->qn('type') . ' = ' . $db->q('plugin'),
            $db->qn('element') . ' = ' . $db->quote($this->extname)
        );
        $fields = array($db->qn('enabled') . ' = 1');

        $query = $db->getQuery(true);
        $query->update($db->quoteName('#__extensions'))->set($fields)->where($conditions);
        $db->setQuery($query);
        try {
            $db->execute();
        } catch (RuntimeException $e) {
            Log::add('unable to enable '.$this->name, Log::ERROR, 'jerror');
        }
        // back to standard update site
        $query = $db->getQuery(true)
            ->delete('#__update_sites')
            ->where($db->quoteName('location') . ' like "%/cg_update.xml%"');
        $db->setQuery($query);
        $db->execute();
        // remove very old ones
        $query = $db->getQuery(true)
            ->delete('#__update_sites')
            ->where($db->quoteName('location') . ' like "%432473037d.url-de-test.ws/%"');
        $db->setQuery($query);
        $db->execute();

    }

    // Check if Joomla version passes minimum requirement
    private function passMinimumJoomlaVersion()
    {
        $j = new Version();
        $version = $j->getShortVersion();
        if (version_compare($version, $this->min_joomla_version, '<')) {
            Factory::getApplication()->enqueueMessage(
                'Incompatible Joomla version : found <strong>' . $version . '</strong>, Minimum : <strong>' . $this->min_joomla_version . '</strong>',
                'error'
            );

            return false;
        }

        return true;
    }

    // Check if PHP version passes minimum requirement
    private function passMinimumPHPVersion()
    {

        if (version_compare(PHP_VERSION, $this->min_php_version, '<')) {
            Factory::getApplication()->enqueueMessage(
                'Incompatible PHP version : found  <strong>' . PHP_VERSION . '</strong>, Minimum <strong>' . $this->min_php_version . '</strong>',
                'error'
            );
            return false;
        }

        return true;
    }
    private function uninstallInstaller()
    {
        if (! is_dir(JPATH_PLUGINS . '/system/' . $this->installerName)) {
            return;
        }
        $this->delete([
            JPATH_PLUGINS . '/system/' . $this->installerName . '/language',
            JPATH_PLUGINS . '/system/' . $this->installerName,
        ]);
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true)
            ->delete('#__extensions')
            ->where($db->quoteName('element') . ' = ' . $db->quote($this->installerName))
            ->where($db->quoteName('folder') . ' = ' . $db->quote('system'))
            ->where($db->quoteName('type') . ' = ' . $db->quote('plugin'));
        $db->setQuery($query);
        $db->execute();
        Factory::getApplication()->getCache()->clean('_system');
    }
    public function delete($files = [])
    {
        foreach ($files as $file) {
            if (is_dir($file)) {
                Folder::delete($file);
            }

            if (is_file($file)) {
                File::delete($file);
            }
        }
    }

}
