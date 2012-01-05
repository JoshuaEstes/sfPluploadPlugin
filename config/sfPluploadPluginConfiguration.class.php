<?php

/**
 * sfPluploadPlugin configuration.
 *
 * @package     sfPluploadPlugin
 * @subpackage  config
 * @author      Your name here
 * @version     SVN: $Id: PluginConfiguration.class.php 17207 2009-04-10 15:36:26Z Kris.Wallsmith $
 */
class sfPluploadPluginConfiguration extends sfPluginConfiguration
{
    const VERSION = '1.0.0-DEV';

    /**
     * @see sfPluginConfiguration
     */
    public function initialize() {
        if (sfConfig::get('app_sfPluploadPlugin_register_routes', true)) {
            $this->dispatcher->connect('routing.load_configuration', array($this, 'listenToRoutingLoadConfigurationEvent'));
        }
    }

    public function listenToRoutingLoadConfigurationEvent(sfEvent $event) {
        $r = $event->getSubject();
        $r->prependRoute('sf_plupload_upload', new sfRoute('/sfPlupload/upload', array(
                    'module' => 'sfPlupload',
                    'action' => 'upload')));
    }

}
