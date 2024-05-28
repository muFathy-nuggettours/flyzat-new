<?
namespace SimpleValidator;

class Validator {
    protected $errors = array();
    protected $namings = array();
    protected $customErrorsWithInputName = array();
    protected $customErrors = array();
    private function __construct($errors, $namings){
        $this->errors  = (array) $errors;
        $this->namings = (array) $namings;
    }
    public function isSuccess(){
        return (empty($this->errors) == true);
    }
    public function customErrors($errors_array){
        foreach ($errors_array as $key => $value){
            if (preg_match("#^(.+?)\.(.+?)$#", $key, $matches)){
                $this->customErrorsWithInputName[(string) $matches[1]][(string) $matches[2]] = $value;
            } else {
                $this->customErrors[(string) $key] = $value;
            }
        }
    }
    public function getErrorsList(){
        return "<ul><li>" . implode("</li><li>",$this->getErrors()) . "</li></ul>";
    }
    protected function getDefaultLang(){
        return "en";
    }
    protected function getErrorFilePath($lang){
        return null;
    }
    protected function getDefaultErrorTexts($lang = null){
		 $default_error_texts = array(
			'required' => ':attribute field is required',
			'integer' => ':attribute field must be an integer',
			'float' => ':attribute field must be a float',
			'numeric' => ':attribute field must be numeric',
			'email' => ':attribute is not a valid email',
			'alpha' => ':attribute field must be an alpha value',
			'alpha_numeric' => ':attribute field must be alphanumeric',
			'ip' => ':attribute must contain a valid IP',
			'url' => ':attribute must contain a valid URL',
			'max_length' => ':attribute can be maximum :params(0) character long',
			'min_length' => ':attribute must be minimum :params(0) character long',
			'exact_length' => ':attribute field must :params(0) character long',
			'equals' => ':attribute field should be same as :params(0)',
			'in_set' => ':attribute field should be one of :params(0)'
		);
        return $default_error_texts;
    }
    protected function getCustomErrorTexts($lang = null){
        $custom_error_texts = array();
        if (file_exists($this->getErrorFilePath($lang)))
            $custom_error_texts = include($this->getErrorFilePath($lang));
        return $custom_error_texts;
    }
    protected function handleNaming($input_name){
        if (isset($this->namings[(string) $input_name])){
            $named_input = $this->namings[(string) $input_name];
        } else {
            $named_input = $input_name;
        }
        return $named_input;
    }
    protected function handleParameterNaming($params){
        foreach ($params as $key => $param){
            if (preg_match("#^:([a-zA-Z0-9_]+)$#", $param, $param_type)){
                if (isset($this->namings[(string) $param_type[1]]))
                    $params[$key] = $this->namings[(string) $param_type[1]];
                else
                    $params[$key] = $param_type[1];
            }
        }
        return $params;
    }
    public function getErrors($lang = null){
        if ($lang == null){
            $lang = $this->getDefaultLang();
		}
        $error_results = array();
        $default_error_texts = $this->getDefaultErrorTexts($lang);
        $custom_error_texts = $this->getCustomErrorTexts($lang);
        foreach ($this->errors as $input_name => $results){
            foreach ($results as $rule => $result){
                $named_input = $this->handleNaming($input_name);
                $result['params'] = $this->handleParameterNaming($result['params']);
                if (isset($this->customErrorsWithInputName[(string) $input_name][(string) $rule])){
                    $error_message = $this->customErrorsWithInputName[(string) $input_name][(string) $rule];
                }
                else if (isset($this->customErrors[(string) $rule])){
                    $error_message = $this->customErrors[(string) $rule];
                }
                else if (isset($custom_error_texts[(string) $rule])){
                    $error_message = $custom_error_texts[(string) $rule];
                }
                else if (isset($default_error_texts[(string) $rule])){
                    $error_message = $default_error_texts[(string) $rule];
                } else {
                    throw new SimpleValidatorException(SimpleValidatorException::NO_ERROR_TEXT, $rule);
                }
                if (preg_match_all("#:params\((.+?)\)#", $error_message, $param_indexes))
                    foreach ($param_indexes[1] as $param_index){
                        $error_message = str_replace(":params(" . $param_index . ")", $result['params'][$param_index], $error_message);
                    }
                $error_results[] = str_replace(":attribute", $named_input, $error_message);
            }
        }
        return $error_results;
    }
    public function has($input_name, $rule_name = null){
        if ($rule_name != null)
            return isset($this->errors[$input_name][$rule_name]);
        return isset($this->errors[$input_name]);
    }
    final public function getResults(){
        return $this->errors;
    }
    private static function getParams($rule){
        if (preg_match("#^([a-zA-Z0-9_]+)\((.+?)\)$#", $rule, $matches)){
            return array(
                'rule' => $matches[1],
                'params' => explode(",", $matches[2])
            );
        }
        return array(
            'rule' => $rule,
            'params' => array()
        );
    }
    private static function getParamValues($params, $inputs){
        foreach ($params as $key => $param){
            if (preg_match("#^:([\[\]a-zA-Z0-9_]+)$#", $param, $param_type)){
                $params[$key] = @$inputs[(string) $param_type[1]];
            }
        }
        return $params;
    }
    public static function validate($inputs, $rules, $naming = null){
        $errors = null;
        foreach ($rules as $input => $input_rules){
            if (is_array($input_rules)){
                foreach ($input_rules as $rule => $closure){
                    if (!isset($inputs[(string) $input]))
                        $input_value = null;
                    else
                        $input_value = $inputs[(string) $input];
                    if (is_numeric($rule)){
                        $rule = $closure;
                    }
                    $rule_and_params = static::getParams($rule);
                    $params = $real_params = $rule_and_params['params'];
                    $rule = $rule_and_params['rule'];
                    $params = static::getParamValues($params, $inputs);
                    array_unshift($params, $input_value);
                    if (is_object($closure) && get_class($closure) == 'Closure'){
                        $refl_func = new \ReflectionFunction($closure);
                        $validation = $refl_func->invokeArgs($params);
                    } else if (@method_exists(get_called_class(), $rule)){
                        $refl = new \ReflectionMethod(get_called_class(), $rule);
                        if ($refl->isStatic()){
                            $refl->setAccessible(true);
                            $validation = $refl->invokeArgs(null, $params);
                        }
                    }
                    if ($validation == false){
                        $errors[(string) $input][(string) $rule]['result'] = false;
                        $errors[(string) $input][(string) $rule]['params'] = $real_params;
                    }
                }
            }
        }
        return new static($errors, $naming);
    }
    protected static function required($input = null){
        return (!is_null($input) && (trim($input) != ''));
    }
    protected static function numeric($input){
        return is_numeric($input);
    }
    protected static function email($input){
        return filter_var($input, FILTER_VALIDATE_EMAIL);
    }
    protected static function integer($input){
        return is_int($input) || ($input == (string) (int) $input);
    }
    protected static function float($input){
        return is_float($input) || ($input == (string) (float) $input);
    }
    protected static function alpha($input){
        return (preg_match("#^[a-zA-ZÀ-ÿ]+$#", $input) == 1);
    }
    protected static function alpha_numeric($input){
        return (preg_match("#^[a-zA-ZÀ-ÿ0-9]+$#", $input) == 1);
    }
    protected static function ip($input){
        return filter_var($input, FILTER_VALIDATE_IP);
    }
    protected static function url($input){
        return filter_var($input, FILTER_VALIDATE_URL);
    }
    protected static function max_length($input, $length){
        return (strlen($input) <= $length);
    }
    protected static function min_length($input, $length){
        return (strlen($input) >= $length);
    }
    protected static function exact_length($input, $length){
        return (strlen($input) == $length);
    }
    protected static function equals($input, $param){
        return ($input == $param);
    }
    protected static function in_set($input, $set){
		$set = explode("|",$set);
        return in_array($input,$set);
    }
    protected static function larger_than($input, $param){
        return (float) $input > (float) $param;
    }
    protected static function larger_than_or_equal($input, $param){
        return (float) $input >= (float) $param;
    }
    protected static function less_than($input, $param){
        return (float) $input < (float) $param;
    }
    protected static function less_than_or_equal($input, $param){
        return (float) $input <= (float) $param;
    }
}
?>