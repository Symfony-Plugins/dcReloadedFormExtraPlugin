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
  
  public function executeDcWidgetFormAjaxDependenceChanged(sfWebRequest $request)
  {
    if ($request->isMethod('post'))
    {
      sfContext::getInstance()->getConfiguration()->loadHelpers(array('Asset','Tag','JavascriptBase','Url'));
      
      $id = $request->getParameter('id');
      $observed_value = $request->getParameter('observed_value');
      $this->widget = unserialize(base64_decode($request->getParameter('widget')));
      $this->getResponse()->setContent($this->widget->ajaxRender($observed_value));
    }
    
    return sfView::NONE;
  }

  public function executeDcWidgetFormJQueryDependenceChanged(sfWebRequest $request)
  {
    if ($request->isMethod('post'))
    {

      $widget_from_request = $request->getParameter('widget');
      $widget = dcWidgetFormJQueryDependence::decodeWidget($widget_from_request);
      $observed_values = $request->getParameter('observed_values');
      $this->getResponse()->setContent($widget->renderAfterUpdate($observed_values));
    }

    return sfView::NONE;
  }
  
  public function executePmWidgetFormPropelJQuerySearch(sfWebRequest $request)
  {
    $this->search = $request->getParameter("search");
    $this->js_var_name = $request->getParameter("js_var_name");
    
    $this->page = $request->getParameter("page");
    $this->previous_page = $this->page - 1;
    $this->next_page = $this->page + 1;
    
    $this->options = unserialize(base64_decode($request->getParameter("serialized_options")));
    
    $results = array();

    $class = constant($this->options['model'].'::PEER');

    $criteria = null === $this->options['criteria'] ? new Criteria() : clone $this->options['criteria'];
    
    $columns = $this->options['column']; // column is an array or a string
    
    if (is_array($columns))
    {
      for ($i = 0; $i < count($columns); $i++)
      { 
        $column = strtoupper($columns[$i]);
        if ($i == 0)
        {
          $criterion = $criteria->getNewCriterion(constant("$class::$column"), "%".$this->search."%", Criteria::LIKE);
        }
        else
        {
          $criterion->addOr($criteria->getNewCriterion(constant("$class::$column"), "%".$this->search."%", Criteria::LIKE));
        }
      }
      
      $criteria->add($criterion);
    }
    else
    {
      $column = strtoupper($columns);
      $criteria->add(constant("$class::$column"), "%".$this->search."%", Criteria::LIKE);
    }
    
    if ($order = $this->options['order_by'])
    {
      $method = sprintf('add%sOrderByColumn', 0 === strpos(strtoupper($order[1]), 'ASC') ? 'Ascending' : 'Descending');
      $criteria->$method(call_user_func(array($class, 'translateFieldName'), $order[0], BasePeer::TYPE_PHPNAME, BasePeer::TYPE_COLNAME));
    }
    
    $this->total_objects = call_user_func(array($class, 'doCount'), $criteria, $this->options['connection']);
    
    if (isset($this->options['limit']))
    {
      $this->limit = $this->options["limit"];
      $criteria->setLimit($this->limit);
      $criteria->setOffset($this->page * $this->limit);
    }
    
    $this->objects = call_user_func(array($class, $this->options['peer_method']), $criteria, $this->options['connection']);
    
    $this->methodKey = $this->options['key_method'];
    if (!method_exists($this->options['model'], $this->methodKey))
    {
      throw new RuntimeException(sprintf('Class "%s" must implement a "%s" method to be rendered in a "%s" widget', $this->options['model'], $this->methodKey, "pmWidgetFormPropelJQuerySearch"));
    }

    $this->methodValue = $this->options['method'];
    if (!method_exists($this->options['model'], $this->methodValue))
    {
      throw new RuntimeException(sprintf('Class "%s" must implement a "%s" method to be rendered in a "%s" widget', $this->options['model'], $this->methodValue, "pmWidgetFormPropelJQuerySearch"));
    }
    
    return $this->renderPartial("dc_ajax/pmWidgetFormPropelJQuerySearch");
  }
}