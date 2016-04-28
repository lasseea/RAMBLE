<?php
class User {
    private $_db,
        $_sessionName = null,
        $_cookieName = null,
        $_data = array(),
        $_isLoggedIn = false;

    public function __construct($user = null) {
        $this->_db = DB::getInstance();

        $this->_sessionName = Config::get('session/session_name');
        $this->_cookieName = Config::get('remember/cookie_name');

        // Check if a session exists and set user if so.
        if(Session::exists($this->_sessionName) && !$user) {
            $user = Session::get($this->_sessionName);

            if($this->find($user)) {
                $this->_isLoggedIn = true;
            } else {
                $this->logout();
            }
        } else {
            $this->find($user);
        }
    }

    public function exists() {
        return (!empty($this->_data)) ? true : false;
    }

    public function find($user = null) {
        // Check if user_id is specified and grab details
        if($user) {
            //checks if $user is numeric, to assign $field to either id(which is numeric) or username(which isn't numeric)
            $field = (is_numeric($user)) ? 'id' : 'username';
            //Assigns any user from the database with an username or id(dependent on if $user was numeric or not) = to $user, to the new variable $data
            $data = $this->_db->get('users', array($field, '=', $user));
            //Checks if any user/s was assigned to $data, and returns true if there is
            if($data->count()) {
                $this->_data = $data->first();
                return true;
            }
        }
        //otherwise return false
        return false;
    }

    //Function for creating a new user in the database with assigned values from the array variable $fields
    public function create($fields = array()) {
        //Creates a new user in the database, inserting the specified fields(from the array $fields), using the insert function from DB.php
        if(!$this->_db->insert('users', $fields)) {
            //In case it did not succeed, it will throw an exception
            throw new Exception('Der opstod et problem ved opretning af denne profil.');
        }
    }

    //Function for updating data in the
    public function update($fields = array(), $id = null) {
        if(!$id && $this->isLoggedIn()) {
            $id = $this->data()->id;
        }

        if(!$this->_db->update('users', $id, $fields)) {
            throw new Exception('Der opstod et problem ved denne opdatering.');
        }
    }


    public function login($username = null, $password = null, $remember = false) {

        if(!$username && !$password && $this->exists()) {
            Session::put($this->_sessionName, $this->data()->id);
        } else {
            $user = $this->find($username);

            if($user) {
                if($this->data()->password === Hash::make($password, $this->data()->salt)) {
                    Session::put($this->_sessionName, $this->data()->id);

                    if($remember) {
                        $hash = Hash::unique();
                        $hashCheck = $this->_db->get('users_session', array('user_id', '=', $this->data()->id));

                        if(!$hashCheck->count()) {
                            $this->_db->insert('users_session', array(
                                'user_id' => $this->data()->id,
                                'hash' => $hash
                            ));
                        } else {
                            $hash = $hashCheck->first()->hash;
                        }

                        Cookie::put($this->_cookieName, $hash, Config::get('remember/cookie_expiry'));
                    }

                    return true;
                }
            }
        }

        return false;
    }

    public function isLoggedIn() {
        return $this->_isLoggedIn;
    }

    public function data() {
        return $this->_data;
    }

    public function logout() {
        $this->_db->delete('users_session', array('user_id', '=', $this->data()->id));

        Cookie::delete($this->_cookieName);
        Session::delete($this->_sessionName);
    }
}