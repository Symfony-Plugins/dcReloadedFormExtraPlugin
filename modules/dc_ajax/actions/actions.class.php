<?php

/**
 * ajax actions.
 *
 * @package    testing
 * @subpackage ajax
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class dc_ajaxActions extends sfActions
{
  public function executeGetPropelChoices(sfWebRequest $request)
  {
    $model = $request->getParameter("model");
    $objects = call_user_func(array($model."Peer", "doSelect"), new Criteria());
    
    $choices = array();
    foreach ($objects as $object)
    {
      $choices[$object->getId()] = $object->__toString();
    }
    
    return $this->renderText(json_encode($choices));
  }

  public function executeGetDoctrineChoices(sfWebRequest $request)
  {
    $model = $request->getParameter("model");
    $objects = Doctrine_Core::getTable($model)->findAll();
    
    $choices = array();
    foreach ($objects as $object)
    {
      $choices[$object->getId()] = $object->__toString();
    }
    
    return $this->renderText(json_encode($choices));
  }
}