<?php
class Validate {
    private $_passed = false,
        $_errors = array(),
        $_db = null;

    public function __construct() {
        $this->_db = DB::getInstance();
    }

    public function check($source, $items = array()) {
        foreach($items as $item => $rules) {
            foreach($rules as $rule => $rule_value) {
                $value = trim($source[$item]);
                if($rule === 'required' && $rule_value === true && empty($value)) {
                    $this->addError("{$item} Needs to be filled out.");
                }  else if (!empty($value)) {

                    switch($rule) {
                        case 'min':
                            if(strlen($value) < $rule_value) {
                                $this->addError("{$item} must contain at least {$rule_value} characters");
                            }
                            break;
                        case 'max':
                            if(strlen($value) > $rule_value) {
                                $this->addError("{$item} can not contain more than {$rule_value} characters");
                            }
                            break;
                        case 'matches':
                            if($value != $source[$rule_value]) {
                                $this->addError("Passwords doesn't match");
                            }
                            break;
                        case 'unique':
                            $check = $this->_db->get('users', array($item, '=', $value));
                            if($check->count()) {
                                $this->addError("{$item} is already taken");
                            }
                            break;
                        case 'uniqueemail':
                            $check = $this->_db->get('users', array('email', '=', $value));
                            if($check->count()) {
                                $this->addError("{$item} is already used for another profile");
                            }
                            break;
                        case 'uniqueURL':
                            $check = $this->_db->get('games', array('game_url', '=', $value));
                            if($check->count()) {
                                $this->addError("Game is already in the database. Add URL again and press \"Retrieve Game\" to edit ");
                            }
                            break;
                        case 'uniqueusername':
                            $check = $this->_db->get('users', array('username', '=', $value));
                            if($check->count()) {
                                $this->addError("{$item} is already taken");
                            }
                            break;
                        case 'maxvalue':
                            if($value > $rule_value) {
                                $this->addError("{$item} may not exceed {$rule_value}");
                            }
                            break;
                        case 'mailvalid':
                            if ($this->isValidEmail($value) != $rule_value) {
                                $this->addError("You need to enter a valid mail");
                            }
                            break;
                    }

                }

            }
        }

        if(empty($this->_errors)) {
            $this->_passed = true;
        }

        return $this;
    }

    protected function addError($error) {
        $this->_errors[] = $error;
    }

    public function passed() {
        return $this->_passed;
    }

    public function errors() {
        return $this->_errors;
    }

    protected function isValidEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL)
        && preg_match('/@.+\./', $email);
    }
}