<?php
/**
 * Description of dcWidgetFormTimepicker
 *
 * @authors ivan y emilia
 */
class dcWidgetFormDateTimePicker extends sfWidgetFormDateTime
{

  /**
   * Returns the date widget.
   *
   * @param  array $attributes  An array of attributes
   *
   * @return sfWidgetForm A Widget representing the date
   */
  protected function getDateWidget($attributes = array())
  {
    return new sfWidgetFormDatePicker($this->getOptionsFor('date'), $this->getAttributesFor('date', $attributes));

  }

  protected function getTimeWidget($attributes = array())
  {
    return new dcWidgetFormTimepicker();

  }

  public function getJavaScripts()
  {
    return array_merge(parent::getJavaScripts(), array("/dcReloadedFormExtraPlugin/js/alTimepicker/jquery.ui.timepicker.js"));

  }

  public function getStylesheets()
  {
    return array_merge(parent::getStylesheets(), array("/dcReloadedFormExtraPlugin/css/alTimepicker/jquery.ui.timepicker.css?v=0.2.5" => "screen", "/dcReloadedFormExtraPlugin/css/alTimepicker/reset-tables.css" => "screen"));

  }

}