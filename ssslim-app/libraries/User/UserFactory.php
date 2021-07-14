<?php
/**
 * Created by IntelliJ IDEA.
 * User: kRs
 * Date: 12/07/2016
 * Time: 13.18
 */
namespace Ssslim\Libraries\User;

require_once "UserExceptions.php";

use Ssslim\Core\Libraries\Logger;
use Ssslim\Libraries\Cache\CacheFactory;
use Ssslim\Libraries\Notification\NotificationsManager;
use Ssslim\Libraries\Notification\Notification;
use Ssslim\Libraries\Token;

class UserFactory
{
    /** @var  \Ssslim\Libraries\DeviceData */
    public $deviceData = null; // this is meant to be assigned in AppCore constructor so we must check that it's not null before using it

    private $logger;
    private $db;
    private $cacheFactory;
    private $notificationsManager;
    private $token;

    private $myUser;


    const USER_LEVEL_ANONYMOUS=-2;
    const USER_LEVEL_BANNED=-1;
    const USER_LEVEL_INACTIVE=0;
    const USER_LEVEL_ACTIVE=1;
    const USER_LEVEL_CUSTOMER=2;
    const USER_LEVEL_SALESREP=3;
    const USER_LEVEL_ORGANIZER=4;
    const USER_LEVEL_ADMIN=5;
    const USER_LEVEL_SUPERADMIN=6;

    const ERR_USER_NOT_FOUND=0;
    const ERR_INVALID_STATE=1;

    const USER_FILTER_REQUESTS_NOTIFICATIONS_ENABLED = 1;
    private $getUsersAndFilters = [];

    public function __construct(Logger $logger, \DB $db, CacheFactory $cacheFactory, Token $token)
    {
        $this->logger = $logger;
        $this->db = $db;
        $this->cacheFactory = $cacheFactory;
        $this->token = $token;

        // initialize internal instance with anonymous user data
        $this->myUser = new User();
    }

    public function getHumanError($err){
        switch($err){
            case UserFactory::ERR_USER_NOT_FOUND:
                return 'Sorry, the specified user was not found.';
            case UserFactory::ERR_INVALID_STATE:
                return "Sorry, the specified user's state is incompatible with the required action. Please refresh the page and try again.";
        }
    }


    public function doLogin($email, $pass){
        $u=$this->fromLogin($email, $pass);

        if($u!=null){
          /*  if($u->active!=1){
                throw new UserInactiveException();
            }else {*/
           //     $this->token->setPayloadVar('user_id', $u->user_id);
          //  }
            $this->setMyUser($u);
            $this->logger->log("USER SUCCESSFULLY LOGGED IN, P: ".($this->deviceData ? $this->deviceData->getPlatform() : '-')." UID: $u->user_id EMAIL: '$u->email'", Logger::INFO, "devices_registrations.log", Logger::HTTP|Logger::DEF);

        }else{
            throw new UserNotFoundException();
        }
        return $u;
    }

    /**
     * TODO is this currently a possible weakness to dnd attacks?
     * @param $email
     * @param $pass
     * @return null|User
     */
    private function fromLogin($email, $pass){
        $qry="SELECT id_expert as user_id FROM experts WHERE pass=".$this->db->escape(sha1($pass))." AND email=".$this->db->escape($email);
        $e = $this->db->query($qry)->row();

        $u=null;
        if(!empty($e)) {
            $u=$this->fromUserId($e->user_id);
        }

        return $u;
    }
    public function fromCookie(){
        if(!isset($_COOKIE['authToken'])) {
            return null;
        } else {
            $tokenPayload = $this->token->getPayloadFromToken($_COOKIE['authToken']);
            return $this->loginFromToken($tokenPayload);
        }
    }


    public function setCookie(){   
        setcookie('authToken', $this->token->generateToken(), time() + (86400 * 30), "/"); // 86400 = 1 day
    }
    public function clearCookie(){
        setcookie('authToken', '', time() - 3600, "/");
    }

    public function fromUserId($user_id){
        $user_id=intval($user_id);

        $c = $this->cacheFactory->get("users/id/".$user_id);
        if (!$c->isValid()) {
            $qry="SELECT * FROM experts WHERE id_expert=".$this->db->escape($user_id);
            $e = $this->db->query($qry)->row();

            $u=null;
            if(!empty($e)) {
                $u = new User($e);
            }
            $c->userData=$u;

            if($c->userData==null) {
                $this->logger->logLine("Cache created - userData is NULL $user_id ", Logger::INFO, "auth_debug.log", Logger::HTTP | Logger::DEF | Logger::URL);
            }
            if($c->userData==false) {
                $this->logger->logLine("Cache created - userData is false $user_id ", Logger::INFO, "auth_debug.log", Logger::HTTP | Logger::DEF | Logger::URL);
            }

            if(isset($u->user_id)){
                $this->logger->logLine("Cache created - user id is set: ".$u->user_id,Logger::INFO,"auth_debug.log",Logger::HTTP|Logger::DEF|Logger::URL);
            }else{
                $this->logger->logLine("Cache created - user id is NOT set, was fetching for user $user_id ",Logger::INFO,"auth_debug.log",Logger::HTTP|Logger::DEF|Logger::URL);
            }


            $c->commit(24);
        }

        return $c->userData;
    }

    public function isLoggedIn(){
        return $this->myUser->active >= 1 && $this->myUser->user_id != 0;
    }

    public function getMyId()
    {
        return  $this->myUser->user_id;
    }

    public function getMyUser(){
        return $this->myUser;
    }
    public function setMyUser($u){
        $this->myUser=$u;
        $this->token->setPayloadVar('user_id', $u->user_id);
    }

    public function loginFromToken($tokenPayload){

        if(empty($tokenPayload->user_id) || intval($tokenPayload->user_id)<=0){
            return null;
        }else{
            $user_id=$tokenPayload->user_id;
        }

        $user=$this->fromUserId($user_id);

       /* $c = $this->cacheFactory->get("users/id/$user_id");
        if (!$c->isValid()) {
            $e = $this->db->query("SELECT * FROM users WHERE user_id=$user_id")->row();
            if(!empty($e)) {
                $c->userData = new User($e);
            }else{
                $c->userData=null;
            }
            $c->commit(24);
        }*/
        if($user!=null && isset($user->user_id)) {
            $this->setMyUser($user);
        }else{
            return null;
        }
        return $user;
    }

    public function saveUserPassword($pass, $user_id){
        $user_id=intval($user_id);
        if($user_id<=0){
            return false;
        }
        $pass=sha1($pass);
        $this->db->query("UPDATE experts SET pass=".$this->db->escape($pass)." WHERE user_id=$user_id");
        return($this->db->affected_rows()==1);
    }

    public function saveUser(User $u, $pass=false){
        if($u->user_id>0){
            $user_id=intval($u->user_id);

            //This is an update, not including password (specific function for that)
            //Check if account actually exists, return false otherwise
            $r=$this->db->query("SELECT user_id FROM experts WHERE user_id=".$this->db->escape($u->user_id))->row();
            if(!empty($r->user_id)){
                if($r->user_id!=$user_id){
                    return false;
                }

            }

            //Email is either available or equal to the old one, we can proceed with the update!
            $this->db->query(
                "UPDATE experts SET
                       email=".$this->db->escape($u->email)
                    .",mobile_number=".$this->db->escape($u->mobile_number)
                    .",first_name=".$this->db->escape($u->first_name)
                    .",last_name=".$this->db->escape($u->last_name)
                    .",organization=".$this->db->escape($u->organization)
                    .",phone_number=".$this->db->escape($u->phone_number)
                    .",title=".$this->db->escape($u->title)
                    .",active=".$this->db->escape($u->active)
                    .",country=".$this->db->escape($u->country)
                    .",notifications_requests_submission=".$this->db->escape($u->notifications_requests_submission)
                ." WHERE user_id=$user_id");
        }else{
            //This is an insert and must have a password
            $this->db->query(
                "INSERT IGNORE INTO experts (active, email, mobile_number, first_name, last_name, organization, phone_number, title, country, pass) VALUES ("
                .$this->db->escape($u->active).",".$this->db->escape($u->email).",".$this->db->escape($u->mobile_number).",".$this->db->escape($u->first_name).",".$this->db->escape($u->last_name).","
                .$this->db->escape($u->organization).",".$this->db->escape($u->phone_number).",".$this->db->escape($u->title).",".$this->db->escape($u->country).","
                .$this->db->escape(sha1($pass))
                .")");
            if($this->db->affected_rows()==0){
                return false;//Possibly email is present
            }
            $u->user_id=$this->db->insert_id();

            $this->logger->log("NEW USER REGISTERED, P: ".($this->deviceData ? $this->deviceData->getPlatform() : '-')." UID: $u->user_id EMAIL: '$u->email'", Logger::INFO, "devices_registrations.log", Logger::HTTP|Logger::DEF);
        }

        $this->cacheFactory->invalidateCacheFile("users/id/$u->user_id");
        $this->cacheFactory->invalidateCacheFile("contents/users/list");
       // $this->logger->debug($this->fromUserId($u->user_id));exit();
        return true;
    }


    public function deleteUser($user_id){
        $this->db->query("DELETE FROM experts WHERE user_id=$user_id");

        $this->cacheFactory->invalidateCacheFile("users/id/$user_id");
        $this->cacheFactory->invalidateCacheFile("contents/users/list");

        if($this->db->affected_rows()<1){
            return UserFactory::ERR_USER_NOT_FOUND;
        }

        return 0;
    }
    public function activateUser($user_id){
        $u=$this->db->query("SELECT active FROM experts WHERE user_id=$user_id")->row();
        if($u==null){
            return UserFactory::ERR_USER_NOT_FOUND;
        }

        if($u->active!=UserFactory::USER_LEVEL_BANNED &&$u->active!=UserFactory::USER_LEVEL_INACTIVE){
            return UserFactory::ERR_INVALID_STATE;
        }

        $this->db->query("UPDATE experts SET active=".UserFactory::USER_LEVEL_ACTIVE." WHERE user_id=$user_id");

        $this->cacheFactory->invalidateCacheFile("users/id/$user_id");
        $this->cacheFactory->invalidateCacheFile("contents/users/list");

        $data=new \stdClass();
        $data->notification_id=0;
        $data->recipients=$user_id;
        $data->notification_type=NotificationsManager::NOTIFICATION_TYPE_ACCESS_GRANTED;
        $data->title="Access granted";
        $data->text="Your access to Tetra Pak @ GFM16 mobile app has been granted!";
        $data->date_trigger=gmdate('Y-m-d H:i:s');

        $s = new Notification($data);
        $s=$this->notificationsManager->saveNotification($s);

        return 0;
    }
    public function banUser($user_id){
        $u=$this->db->query("SELECT active FROM experts WHERE user_id=$user_id")->row();
        if($u==null){
            return UserFactory::ERR_USER_NOT_FOUND;
        }

        if($u->active!=UserFactory::USER_LEVEL_ACTIVE && $u->active!=UserFactory::USER_LEVEL_INACTIVE){
            return UserFactory::ERR_INVALID_STATE;
        }

        $this->db->query("UPDATE experts SET active=".UserFactory::USER_LEVEL_BANNED." WHERE user_id=$user_id");

        $this->cacheFactory->invalidateCacheFile("users/id/$user_id");
        $this->cacheFactory->invalidateCacheFile("contents/users/list");

        $data=new \stdClass();
        $data->notification_id=0;
        $data->recipients=$user_id;
        $data->notification_type=NotificationsManager::NOTIFICATION_TYPE_ACCESS_DENIED;
        $data->title="Access denied";
        $data->text="Sorry, your access to Tetra Pak @ GFM16 mobile app has been denied at this time.";
        $data->date_trigger=gmdate('Y-m-d H:i:s');

        $s = new Notification($data);
        $s=$this->notificationsManager->saveNotification($s);


        return 0;
    }

    public function addUsersFilter($filterType, $filterValue = null, $type = "AND")
    {
        $f = new \stdClass();
        $f->type = $filterType;
        $f->value = $filterValue;

        $this->getUsersAndFilters[] = $f;
        return $this;
    }

    function getUsers(){
        $c = $this->cacheFactory->get("contents/users/list");

        $qWhere = " WHERE 1";

        foreach ($this->getUsersAndFilters as $f) {

            switch ($f->type) {
                case self::USER_FILTER_REQUESTS_NOTIFICATIONS_ENABLED:
                    $qWhere .= " AND experts.notifications_requests_submission = '" . $f->value . "'";
                    break;

                default:
                    throw new \Exception("UNKNOWN FILTER");
                    break;
            }
        }

        if (!$this->getUsersAndFilters && !$c->isValid()) {
            $l=$this->db->query("SELECT * FROM experts $qWhere ORDER BY user_id DESC")->result();
            $c->userData = $l;
            if (!$this->getUsersAndFilters) $c->commit(24);
        }

        $toReturn=array();
        foreach($c->userData as $u){
            $toReturn[]=new User($u);
        }

        return $toReturn;
    }

    /*    public function _generateTokenString($len = 128) {
            //No token - generate a new one
            $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
            $newToken = '';

            for ($i = 0; $i < $len; $i++) {
                $newToken .= $characters[mt_rand(0, strlen($characters) - 1)];
            }

            print $newToken;
        }*/
}

class User{
    public  $user_id=0;
    public  $active=UserFactory::USER_LEVEL_ANONYMOUS;
    public  $first_name = 'Anonymous';
    public  $last_name = '';
    public  $organization;
    public  $title;
    public  $phone_number;
    public  $mobile_number;
    public  $email;
    public  $country;
    public  $notifications_requests_submission=1;
    public  $subscribed_status=0;

    public function __construct($data = null)
    {
        if ($data) $this->setData($data);
    }

    public function setData($data)
    {
        if(isset($data->user_id)) {
            $this->user_id = $data->user_id;
        }
        if(isset($data->active)) {
            $this->active = $data->active;
        }
        if(isset($data->notifications_requests_submission)) {
            $this->notifications_requests_submission = $data->notifications_requests_submission;
        }

        if(isset($data->mobile_number)) {
            $this->mobile_number=$data->mobile_number;
        }

        $this->first_name=$data->first_name;
        $this->last_name=$data->last_name;
        $this->organization=$data->organization;
        $this->title=$data->title;
        $this->phone_number=$data->phone_number;

        $this->email=$data->email;
        $this->country=$data->country;

    }

    public function getHumanStatus(){
        return User::getHumanStatusDesc($this->active);
    }

    public static function getHumanStatusDesc($status){
        switch($status){
            case UserFactory::USER_LEVEL_BANNED: return 'Banned'; break;
            case UserFactory::USER_LEVEL_ANONYMOUS: return 'Anonymous'; break;
            case UserFactory::USER_LEVEL_INACTIVE: return 'Inactive'; break;
            case UserFactory::USER_LEVEL_ACTIVE: return 'Active'; break;
            case UserFactory::USER_LEVEL_CUSTOMER: return 'Customer'; break;
            case UserFactory::USER_LEVEL_SALESREP: return 'Sales representative'; break;
            case UserFactory::USER_LEVEL_ADMIN: return 'Administrator'; break;
            case UserFactory::USER_LEVEL_SUPERADMIN: return 'Super-admin'; break;
        }
    }

    public static function getHumanAdminStates(){
        return array(
//            UserFactory::USER_LEVEL_INACTIVE=> 'Inactive',
//            UserFactory::USER_LEVEL_ACTIVE=> 'Active',
            UserFactory::USER_LEVEL_ADMIN=>'Administrator',
//            UserFactory::USER_LEVEL_BANNED => 'Banned',
//            UserFactory::USER_LEVEL_CUSTOMER => 'Customer',
//            UserFactory::USER_LEVEL_SALESREP => 'Sales representative',
//            UserFactory::USER_LEVEL_SUPERADMIN => 'Super-admin',
        );
    }

}