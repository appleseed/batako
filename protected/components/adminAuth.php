<?php

/**
 * Class buat menghandle authentifikasi admin
 */
class adminAuth {

    public $auth_name = 'member';

    public function __construct() {
        if (!session_id()) {
            session_start();
        }
    }

    /**
     * setter buatmagic function dari php __set
     */
    public function __set($name, $value) {
        $_SESSION[$this->auth_name][$name] = $value;
    }

    /**
     * method buat ambil data magic function getter
     */
    public function __get($name) {
        if (isset($_SESSION[$this->auth_name][$name]))
            return $_SESSION[$this->auth_name][$name];
        else
            return false;
    }

    /**
     * fungsi buat mengecek authentifikasi dari user admin tersebut
     */
    public function authAdmin() {
        if (isset($_SESSION[$this->auth_name])) {
            return TRUE;
        } else {
            return array('error' => TRUE,
                'message' => 'Sesi telah habis');
        }
        //throw new CHttpException(500, 'Admin Tidak ada');
    }

    /**
     * public function check_password
     */
    public function checkPassword() {
        if (isset($_SESSION[$this->auth_name]['user_id'])) {
            $data_model = Yii::app()->db->createCommand()->from('user')->where('user_id=:admin_id', array(':admin_id' => $_SESSION[$this->auth_name]['user_id']))
                    ->queryRow();
            if (isset($data_model)) {
                //check password
                if ($_SESSION[$this->auth_name]['user_password'] == $data_model['user_password'] && $data_model['user_is_active'] == '1') {
                    return array('error' => FALSE);
                } else {
                    //CController::redirect('/site/login');//  throw new CHttpException(500, 'Admin Tidak aktiv');
                    return array('error' => TRUE,
                        'message' => 'Admin Tidak aktiv');
                }
            } else {
//                CController::redirect('/site/login');// throw new CHttpException(500, 'Admin Tidak ada');
                return array('error' => TRUE,
                    'message' => 'Admin Tidak ada');
            }
        } else {
            //CController::redirect('/site/login');
            //sthrow new CHttpException(500, 'Admin Tidak ada');
            return array('error' => TRUE,
                'message' => 'Admin Tidak ada');
        }
    }

    /**
     * fungsi buat wrapper authentifikasi
     */
    public function authenthicate() {
        $data_auth_check_password = $this->checkPassword();
        if ($this->authAdmin() && $this->checkPassword()) {
            return TRUE;
        }
        else
            return false;
    }

    public function login($username, $password) {
        $data_model = Yii::app()->db->createCommand()->from('user')->where('username=:admin_id', array(':admin_id' => $username))
                ->queryRow();
        if (isset($data_model)) {
            if (md5($password) != $data_model['user_password']) {
                return array('error' => true, 'message' => 'password salah');
            } else {
                //update last loginnya
                /*
                  $data_arr = array('user_id' => $data_model['user_id'],
                  'username' => $data_model['username'],
                  'user_password' => $data_model['user_password'],
                  'user_role_id' => $data_model['user_role_user_role_id'],
                  'user_is_administrator' => $data_model['user_is_administrator'],
                  'user_role' => dbHelper::getOne('user_role_name', 'user_role', 'user_role_id=\'' . $data_model['user_role_user_role_id'] . '\''));
                 */
                $data_arr = $data_model;
                $_SESSION[$this->auth_name] = $data_arr;
                return array('error' => false, 'message success');
            }
        }
        else
            return array('error' => true, 'message' => 'username ' . $username . ' tidak ada dalam database');
    }

    public function logout() {
        unset($_SESSION[$this->auth_name]);
    }

    /**
     * fungsi buat ambil data controller dan actionnya kemudian di samakan denga di dabase
     */
    public function auth_action_cont(CController $action) {
        $data_return = array();
        $action_name = $action->id . '.' . $action->action->id;
        //ambil con_action_id
        $con_action_id = dbHelper::getOne('con_action_id', 'con_action', 'con_action_data = \'' . $action_name . '\'');
        if ($con_action_id) {
            $cek = dbHelper::getOne('con_action_user_role_user_role_id', 'con_action_user_role', 'con_action_user_role_user_role_id = ' . $this->user_role_user_role_id . ' AND con_action_user_role_con_action_id = ' . $con_action_id);
            if (!$cek) {
                return array('error' => true, 'message' => $action_name . ' tidak berhak akses');
            }
            else
                return array('error' => false);
        } else {
            return array('error' => true, 'message' => $action_name . ' tidak terdapat di database');
        }
    }

    /**
     * fungsi buat cek akses berdasar url-nya
     * @param String $cont_action controller/action
     * @return Boolean 
     */
    public function checkAccess($cont_action) {
        if ($this->user_is_administrator == '1')
            return TRUE;
        $cont_action = trim(str_replace('/', '.', $cont_action), '.');
        $check = dbHelper::getOne('con_action_id', 'con_action LEFT JOIN con_action_user_role ON con_action_user_role_con_action_id = con_action_id', "con_action_data = '" . $cont_action . "' AND con_action_user_role_user_role_id = " . $this->user_role_id);
        if ($check)
            return TRUE;
        else
            return FALSE;
    }

}

?>
