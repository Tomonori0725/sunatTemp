<?php declare(encoding = 'UTF-8');
if (!defined('FORM_FIELD_ARRAY_CLASS_INC')) : define('FORM_FIELD_ARRAY_CLASS_INC', 1);

class FormFieldArray extends FormField
{

  protected $type;

  public function __construct(iFormField $type, $flags = 0)
  {
    parent::__construct($flags);
    $this->type = $type;
  }

  public function pre_convert($value, array $file)
  {
    if (is_null($value)) {
      return ($this->flags & FormField::NOT_NULL) ? array() : NULL;
    }
    if (!is_array($value)) {
      return $value;
    }

    foreach ($value as $k => &$v) {
      $v = $this->type->pre_convert($v, array_key_exists($k, $file) ? $file[$k] : array());
    }
    return $value;
  }

  public function check_value($value, array &$errors)
  {
    if (is_array($value) && !empty($value)) {
      foreach ($value as $i => $v) {
        $e = array();
        $this->type->check_value($v, $e);
        if (count($e)) {
          $errors[$i] = $e;
        }
      }
    }
  }

  public function convert_value($value)
  {
    return $value;
  }

}

endif;  // FORM_FIELD_ARRAY_FIELDS_CLASS_INC
