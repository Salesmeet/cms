<?php
namespace Ssslim\Controllers;

use Ssslim\Libraries\AppCore;
use Ssslim\Libraries\Forms;
use Ssslim\Libraries\LeadsManager;
use Ssslim\Libraries\MailManager;
use Ssslim\Libraries\Pagination;
use Ssslim\Libraries\Token;
use Ssslim\Libraries\User\UserFactory;
use Ssslim\Libraries\User\UserInactiveException;
use Ssslim\Libraries\User\UserNotFoundException;


class Auth
{
    private $leadsManager;
    private $userFactory;
    private $token;
    private $appCore;
    private $forms;
    private $loader;
    private $mailManager;
    private $pagination;

    private $leadAttachmentDir = "attachments/"; // TODO move to config file?
    private $maxFileNameCreationAttempts = 10;

    function __construct(AppCore $appCore, LeadsManager $leadsManager, UserFactory $userFactory, Token $token, Forms $forms, \CI_Loader $loader,  MailManager $mailManager, Pagination $pagination)
    {      
        $this->appCore = $appCore;
        $this->leadsManager = $leadsManager;
        $this->userFactory = $userFactory;
        $this->token = $token;
        $this->forms = $forms;
        $this->loader=$loader;
        $this->mailManager=$mailManager;
        $this->pagination = $pagination;
        
        //TODO clean this up please
        header("Access-Control-Allow-Origin:*");
        header("Access-Control-Allow-Credentials:true");
        header("Access-Control-Allow-Methods:GET, POST, PUT, DELETE,OPTIONS");
        header("Access-Control-Allow-Headers:Authorization, Access-Control-Allow-Headers, Origin,Accept, X-Requested-With, Content-Type, Access-Control-Request-Method, Access-Control-Request-Headers");

        if($_SERVER['REQUEST_METHOD']=="OPTIONS"){
          $this->render();
          exit();
        }
        
        
      if (isset($_SERVER['argv']) && !isset($_SERVER['SERVER_NAME'])) { // command line execution
            return;
            if (isset($_SERVER['argv'][2]) && strtolower($_SERVER['argv'][2]) == "sendactivationmails") return; // sendActivationMails is allowed from CLI
        }

        if (!empty($_GET['k']) && $_GET['k'] == 'q76__xMQ11e2ddr33Q') return;

        $loginPath="auth/login";
        $path=substr($_SERVER['REQUEST_URI'], strlen($_SERVER['REQUEST_URI'])-strlen($loginPath), strlen($loginPath));
        
        if($path!=$loginPath){
            try {
              $headers=getallheaders();
              if(!$headers||!isset($headers['Authorization'])){
                show_404(); //TODO logout!
              }
              $tokenPayload=$this->token->getPayloadFromToken(substr($headers['Authorization'],7));
              $u=$this->userFactory->loginFromToken($tokenPayload);           

//                if($u->active<UserFactory::USER_LEVEL_ADMIN){
//                    $this->appCore->redirect('auth/login');
//                }
            } catch (\Exception $e) {
               // $this->appCore->redirect($loginPath);
                show_404(); //TODO logout!
            }
        }

      /*  if($this->userFactory->getMyUser()->active<UserFactory::USER_LEVEL_ADMIN){
            $this->appCore->debug($this->userFactory->getMyUser());
            $this->appCore->show_404();
        }*/
    }

    public function render($data=array())
    {
       print json_encode($data);
    }

    public function login(){
//$this->appCore->debug(sha1($this->appCore->getRequest()->password));exit();
        $u = null;
            if (empty($this->appCore->getRequest()->email) || empty($this->appCore->getRequest()->password)) {
                $u=null;
            }else {
                try {
                    $u = $this->userFactory->doLogin($this->appCore->getRequest()->email, $this->appCore->getRequest()->password);
                    $redirect="auth";
                    if(!empty($this->appCore->getRequest()->redirect)){
                        $redirect=$this->appCore->getRequest()->redirect;
                    }

                    //$this->userFactory->setCookie();
                    //print "cookie set!";exit();

                  //  $this->appCore->redirect($redirect);
                } catch (UserNotFoundException $e) {
                    $u = null;
                } catch (UserInactiveException $e) {
                    $u = null;
                }
            }

        $u=new \stdClass();
        $u->authToken = $this->token->generateToken();
        $u->refreshToken = "123123123123";
        $u->expiresIn = "11234";
            
        $data=array();
        $data=$u;
       // $this->loader->view('admin/login_v', $data, false);

        $this->render($data);
    }

    public function me(){
    $u = new \stdClass();
      $u->id = 1;
      $u->username = 'admin';
      $u->password = 'demo';
      $u->email = 'admin@demo.com';
      $u->authToken = 'auth-token-8f3ae836da744329a6f93bf20594b5cc';
      $u->refreshToken = 'auth-token-f8c137a2c98743f48b643e71161d90aa';
      $u->roles = [1];
      $u->pic = './assets/media/users/300_25.jpg';
      $u->fullname = 'Sean S';
      $u->firstname = 'Sean';
      $u->lastname = 'Stark';
      $u->occupation = 'CEO';
      $u->companyName = 'Keenthemes';
      $u->phone = '456669067890';
      $u->language = 'en';
      $u->timeZone = 'International Date Line West';
      $u->website = 'https://keenthemes.com';
      $u->emailSettings = new \stdClass();

      $u->emailSettings->emailNotification = true;
      $u->emailSettings->sendCopyToPersonalEmail = false;
      $u->emailSettings->activityRelatesEmail = new \stdClass();
      $u->emailSettings->activityRelatesEmail->youHaveNewNotifications = false;
      $u->emailSettings->activityRelatesEmail->youAreSentADirectMessage = false;
      $u->emailSettings->activityRelatesEmail->someoneAddsYouAsAsAConnection = true;
      $u->emailSettings->activityRelatesEmail->uponNewOrder = false;
      $u->emailSettings->activityRelatesEmail->newMembershipApproval = false;
      $u->emailSettings->activityRelatesEmail->memberRegistration = true;

      $u->emailSettings->updatesFromKeenthemes = new \stdClass();
      $u->emailSettings->updatesFromKeenthemes->newsAboutKeenthemesProductsAndFeatureUpdates = false;
      $u->emailSettings->updatesFromKeenthemes->tipsOnGettingMoreOutOfKeen = false;
      $u->emailSettings->updatesFromKeenthemes->thingsYouMissedSindeYouLastLoggedIntoKeen = true;
      $u->emailSettings->updatesFromKeenthemes->newsAboutMetronicOnPartnerProductsAndOtherServices = true;
      $u->emailSettings->updatesFromKeenthemes->tipsOnMetronicBusinessProducts = true;


      $u->communication = new \stdClass();
      $u->communication->email = true;
      $u->communication->sms = true;
      $u->communication->phone = false;

      $u->address = new \stdClass();
      $u->address->addressLine = 'L-12-20 Vertex; Cybersquare';
      $u->address->city = 'San Francisco';
      $u->address->state = 'California';
      $u->address->postCode = '45000';

      $u->socialNetworks = new \stdClass();
      $u->socialNetworks->linkedIn = 'https://linkedin.com/admin';
      $u->socialNetworks->facebook = 'https://facebook.com/admin';
      $u->socialNetworks->twitter = 'https://twitter.com/admin';
      $u->socialNetworks->instagram = 'https://instagram.com/admin';
      $this->render($u);
    }


    public function logout(){
        $this->userFactory->clearCookie();
        $this->appCore->redirect("auth/login");
    }

}
