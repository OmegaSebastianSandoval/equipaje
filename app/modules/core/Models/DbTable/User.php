<?php

/**
*
*/

class Core_Model_DbTable_User extends Db_Table
{
	protected $_name = 'user';
    protected $_id = 'user_id';

     public function changePassword($id, $password)
    {
        $password = password_hash($password, PASSWORD_DEFAULT);
        $edit = "UPDATE ".$this->_name." SET user_password = '".$password."'  WHERE user_id = '".$id."'";
        $this->_conn->query($edit);
    }

    public function searchUser($id)
    {
        $res = $this->_conn->query('SELECT * FROM '.$this->_name.' WHERE user_id = "'.$id.'"')->fetchAsObject();
        return $res;
    }

	public function searchUserByUser($user)
    {
        $res = $this->_conn->query('SELECT * FROM '.$this->_name.' WHERE user_user = "'.$user.'"')->fetchAsObject();
        if(isset($res[0])){
            $res = $res[0];
        } else {
            $res = false;
        }
        return $res;
    }

    public function autenticateUser($user,$password){
        $resUser=$this->searchUserByUser($user);
        if ($resUser->user_id) {
            if(password_verify($password,$resUser->user_password)){
                return  true;
            }else{
                return false;
            }
        } else {
            return false;
        }
    }

    public function loginUser($user,$password)
    {
        $res = $this->_conn->query('SELECT * FROM '.$this->_name.' WHERE user_id = "'.$user.'"')->fetchAsObject();
        return $res[0];
    }

     public function editCode($id, $code)
    {
        $edit = "UPDATE ".$this->_name." SET user_code = '".$code."' WHERE user_id = '".$id."'";
        $this->_conn->query($edit);
    }

}