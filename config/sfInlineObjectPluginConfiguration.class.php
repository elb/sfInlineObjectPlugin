<?php

/**
 * Plugin configuration for sfSympalInlineObjectPlugin
 * 
 * @package     sfInlineObjectPlugin
 * @subpackage  config
 * @author      Ryan Weaver <ryan@thatsquality.com>
 */
class sfInlineObjectPluginConfiguration extends sfPluginConfiguration
{
  protected $_parser;

  /**
   * Use this to optionally override the path to the InlineObject library
   */
  public static $inlineObjectPath;

  public function initialize()
  {
    sfOutputEscaper::markClassAsSafe('sfInlineObjectType');

    $inlineObjectPath = sfConfig::get('inline_object_dir', dirname(__FILE__).'/../lib/vendor/InlineObjectParser');

    $autoloader = $inlineObjectPath.'/lib/InlineObjectAutoloader.php';
    if (!file_exists($autoloader))
    {
      throw new sfException('InlineObject library autoloader not found at '.$autoloader);
    }

    require_once $autoloader;
    InlineObjectAutoloader::register();

    // Listener so we can bootstrap the plugin 
    $this->dispatcher->connect('context.load_factories', array($this, 'bootstrap'));
    
    // Listener so we can "extend" the actions class
    $action = new sfInlineObjectAction();
    $this->dispatcher->connect('component.method_not_found', array($action, 'listenComponentMethodNotFound'));
  }

  /**
   * Listens to the context.load_factories event and:
   * 
   *  * Adds InlineObject to the standard helpers
   */
  public function bootstrap(sfEvent $event)
  {
    $helpers = sfConfig::get('sf_standard_helpers', array());
    $helpers[] = 'InlineObject';
    
    sfConfig::set('sf_standard_helpers', $helpers);
  }

  /**
   * Returns the parser to be used to parse the inline objects
   * 
   * This allows us to effectively only have one parser instance without
   * implementing the singleton pattern
   * 
   * @param string $class The name of the class to use for the parser
   */
  public function getParser()
  {
    if ($this->_parser === null)
    {
      $this->_parser = sfInlineObjectParser::createInstance();
    }
    
    return $this->_parser;
  }
}