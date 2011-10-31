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

  public function executeDcWidgetFormActivator(sfWebRequest $request)
  {
    if ($request->isMethod('post'))
    {
      $widget_from_request = $request->getParameter('widget');
      $widget_array    = dcWidgetFormActivator::decodeWidget($widget_from_request);
      $observed_values = $request->getParameter('observed_values');
      $this->getResponse()->setContent(call_user_func($widget_array['render_after_method'], $observed_values, $widget_array));
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
      $criteria->$method(call_user_func(array($class, 'translateFieldName'), sfInflector::camelize($order[0]), BasePeer::TYPE_PHPNAME, BasePeer::TYPE_COLNAME));
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

  public function executeMtWidgetFormEmbedAdd(sfWebRequest $request)
  {
    $parentFormName           = mtWidgetFormEmbed::decode($request->getParameter('parent_form_name'));
    $childFormName            = mtWidgetFormEmbed::decode($request->getParameter('child_form_name'));
    $formCreationMethod       = mtWidgetFormEmbed::decode($request->getParameter('form_creation_method'));
    $formCreationMethodParams = mtWidgetFormEmbed::decode($request->getParameter('form_creation_method_params'));
    $childFormTitleMethod     = mtWidgetFormEmbed::decode($request->getParameter('title_method'));
    $this->widgetId           = mtWidgetFormEmbed::decode($request->getParameter('widget_id'));
    $this->formFormatter      = mtWidgetFormEmbed::decode($request->getParameter('form_formatter'));
    $this->afterDeleteJs      = mtWidgetFormEmbed::decode($request->getParameter('after_delete_js'));
    $this->rendererClass      = $request->getParameter('renderer_class');
    $this->images             = mtWidgetFormEmbed::decode($request->getParameter('images'));
    $this->childCount         = $request->getParameter('child_count');

    if (!empty($childFormTitleMethod))
    {
      if (is_string($childFormTitleMethod))
      {
        $this->title = $childFormTitleMethod;
      }
      elseif (is_array($childFormTitleMethod))
      {
        $this->title = call_user_func($childFormTitleMethod);
      }
    }

    $this->form = call_user_func($formCreationMethod, $formCreationMethodParams);
    $this->form->getWidgetSchema()->setNameFormat("$parentFormName"."[".$childFormName."_".$this->childCount."][%s]");
    $this->form->getWidgetSchema()->setFormFormatterName($this->formFormatter);
    $this->formTitle = $this->getFormTitle($this->form, $childFormTitleMethod);

    unset($this->form['_csrf_token']);
  }

  protected function getFormTitle($form, $childFormTitleMethod)
  {
    $method = $childFormTitleMethod;
    if (!empty($method))
    {
      if (method_exists($form, $method))
      {
        return $form->$method();
        return call_user_func(array($form, $method));
      }
      return $method;
    }
    return '';
  }
  
  public function executePmWidgetFormPropelInputByCode(sfWebRequest $request)
  {
    sfContext::getInstance()->getConfiguration()->loadHelpers(array('I18N'));
    
    $code = $request->getParameter('code');
    
    $widget_options = unserialize($request->getParameter('serialized_widget_options'));
    
    $model = $widget_options['model'];
    $column = $widget_options['column'];
    $criteria = $widget_options['criteria'];
    $method = $widget_options['method'];
    $peer_method = $widget_options['peer_method'];
    $object_not_found_text = $widget_options['object_not_found_text'];
    $object_not_found_text = __($object_not_found_text);
    
    if (is_null($criteria))
    {
      $criteria = new Criteria();
    }
    
    $criteria->add(constant($model.'Peer::'.strtoupper($column)), $code);
    
    $object = call_user_func(array($model.'Peer', $peer_method), $criteria);
    
    $text = !is_null($object) ? $object->$method() : $object_not_found_text;
    
    return $this->renderText("<span class=\"label ".(is_null($object) ? "not-" : "")."found\">$text</span>");
  }
  
  public function executePmWidgetFormPropelJQueryTokeninput(sfWebRequest $request)
  {
    sfContext::getInstance()->getConfiguration()->loadHelpers(array('I18N'));
    
    $q = $request->getParameter('q');
    
    $widget_options = unserialize(base64_decode($request->getParameter('serialized_widget_options')));
    
    $model = $widget_options['model'];
    $column = $widget_options['column'];
    $criteria = $widget_options['criteria'];
    $method = $widget_options['method'];
    $peer_method = $widget_options['peer_method'];
    $key_method = $widget_options['key_method'];
    
    if (is_null($criteria))
    {
      $criteria = new Criteria();
    }
    
    $criteria->add(constant($model.'Peer::'.strtoupper($column)), "%$q%", Criteria::LIKE);
    
    $objects = call_user_func(array($model.'Peer', $peer_method), $criteria);
    
    $results = array();
    foreach ($objects as $object)
    {
      $results[] = array(
        'id' => $object->$key_method(),
        'name' => strval($object)
      );
    }
    
    return $this->renderText(json_encode($results));
  }
}
