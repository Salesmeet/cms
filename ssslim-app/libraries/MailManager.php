<?php

namespace Ssslim\Libraries;

use Ssslim\Core\Libraries\Logger;
use Ssslim\Libraries\User\UserFactory;

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

class MailManager
{
	var $logger;
	var $db;
	var $userFactory;

	var $userManagerMails= ['mauryr@tiscali.it', 'maurizio.ranaboldo@gmail.com','Rauf.Hameed@tetrapak.com','Tawfiq.Agoumi@tetrapak.com'];

	function __construct(Logger $logger,  \CI_Loader $loader, \DB $db, UserFactory $userFactory)
	{
		$this->logger=$logger;
        $this->loader=$loader;
		$this->db=$db;
		$this->userFactory=$userFactory;
	}

	function checkUserActivations(){
		$this->logger->setLogFile("mailer");
		$row=$this->db->query("SELECT count(*) as cnt FROM users where active='".UserFactory::USER_LEVEL_INACTIVE."'")->row();
		$count=$row->cnt;

		$this->logger->logLine("checkUserActivations: $count users found.");
		if($count==0){
			return;
		}

		$recipients=array();
		foreach($this->userManagerMails as $u){
			$us=new \stdClass();
			$us->address=$u;
			$recipients[]=$us;
		}

		$data=array();
		$data['count']=$count;
		$body= $this->loader->view("mails/user_activations_v", $data, true);

		$this->send($body, $recipients, "TP @ GFM 16 Management - user activations", 'notify_user_activation_requests');
	}

	function surveyMail(){
		$body= $this->loader->view("mails/survey_invitation_v", null, false);

	}


	function sendSurveyMail(){
		$this->logger->setLogFile("mailer");

		$rs=$this->db->query("SELECT * FROM users WHERE surveyMailSent=0 LIMIT 0,100")->result();
		foreach($rs as $r) {


			$recipients = array();
			//foreach($this->userManagerMails as $u){
			$us = new \stdClass();
			$us->address = $r->email;
			$recipients[] = $us;

			/*$us = new \stdClass();
			$us->address = 'maurizio.ranaboldo@gmail.com';
			$recipients[] = $us;*/
			//}

			$data = array();

			$body = $this->loader->view("mails/survey_invitation_v", $data, true);

			print "Sending survey mail to:".$r->email."... ";
			$httpCode=$this->send($body, $recipients, "Thank you for your participation at Gulfood Manufacturing 2016", 'survey_invitation');
			if($httpCode==200){
				$this->db->query('UPDATE users SET surveyMailSent=1 WHERE user_id='.$r->user_id);
				print "Done - $httpCode.<br>";
			}else{
				print "NOT Done - $httpCode.<br>";
			}
		}
	}


	function generatePasswords(){
		$rs=$this->db->query("SELECT * FROM users WHERE invite_sent=0 && invite_pw=''")->result();
		foreach($rs as $r){
			$invite_pw=$this->genPw($r->email);
			if(!empty($r->invite_pw)){
				$invite_pw=$r->invite_pw;
			}
			print $r->email; print "  ".$invite_pw."<br>";
			$this->userFactory->saveUserPassword($invite_pw, $r->user_id);
			$this->db->query('UPDATE users SET invite_pw='.$this->db->escape($invite_pw).' WHERE user_id='.$r->user_id);
		}
	}

	function genPw($email){
		$pw=substr($email,0,5);
		$pw=str_replace("@",'',$pw);
		$pw=str_replace(".",'',$pw);
		$pw=strtolower($pw);
		$pw.=".".substr(str_shuffle(MD5(microtime())), 0, 5);
		return $pw;
	}

	function sendUserInvites(){
		$this->logger->setLogFile("mailer");

		$rs=$this->db->query("SELECT * FROM users WHERE invite_sent=0 && invite_pw!='' LIMIT 0,100")->result();
		foreach($rs as $r) {


			$recipients = array();
			//foreach($this->userManagerMails as $u){
			$us = new \stdClass();
			$us->address = $r->email;
			$recipients[] = $us;

			/*$us = new \stdClass();
			$us->address = 'mauryr@tiscali.it';
			$recipients[] = $us;*/
			//}

			$data = array();
			$data['email']=$r->email;
			$data['password']=$r->invite_pw;
			$data['name']=ucwords($r->first_name). " ". ucwords($r->last_name);

			$body = $this->loader->view("mails/invitation_v", $data, true);

			print "Sending invite to:".$r->email."... ";
			$httpCode=$this->send($body, $recipients, "TP @ GFM 16 Invitation", 'user_invite');
			if($httpCode==200){
				$this->db->query('UPDATE users SET invite_sent=1 WHERE user_id='.$r->user_id);
				print "Done - $httpCode.<br>";
			}else{
				print "NOT Done - $httpCode.<br>";
			}
		}
	}


	function checkEventSubscriptionRequests(){
		$this->logger->setLogFile("mailer");


		$rs=$this->db->query("SELECT user_events.event_id, name, count(*) as cnt
								FROM user_events
								INNER JOIN events ON events.event_id=user_events.event_id
								where subscribed='".EventsManager::SUBSCRIBE_STATUS_REQUESTED."'
								GROUP BY user_events.event_id")->result();
		$count=sizeof($rs);

		$this->logger->logLine("checkEventSubscriptionRequests: $count events have subscribe requests.");
		if($count==0){
			return;
		}

		$data = array();
		$data['events']=array();
		foreach($rs as $row) {
			$data['events'][] = $row;
		}

		$recipients=array();
		foreach($this->userManagerMails as $u){
			$us=new \stdClass();
			$us->address=$u;
			$recipients[]=$us;
		}
		$body = $this->loader->view("mails/user_subscriptions_v", $data, true);
		$this->send($body, $recipients, "TP@GF2016 Management - user event registrations", 'notify_event_registration_requests');
	}



	function checkMessageStatus() {
		$message_id='00096c9b1058655bc3af';

		$data='{}';
		$ch = curl_init();
		//curl_setopt($ch, CURLOPT_URL, "https://api.sparkpost.com/api/v1/message-events?transmission_ids=174522994945379968");//message_ids=".$message_id);
		curl_setopt($ch, CURLOPT_URL, "https://api.sparkpost.com/api/v1/suppression-list/maurizio.ranaboldo@gmail.com");//message_ids=".$message_id);


		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json" , "Authorization: 3eb0e32b17ddf4f130c994161082f86c33b09fa9"));
		//curl_setopt($ch, CURLOPT_POSTFIELDS, $data);


		curl_setopt($ch, CURLOPT_POST, 0);


		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);


		curl_setopt($ch, CURLINFO_HEADER_OUT, true); // enable tracking

		$content=curl_exec($ch);
		$this->logger->debug(  curl_getinfo($ch, CURLINFO_HEADER_OUT )); // request headers);
		curl_close($ch);

		$this->logger->debug($content);
	}


	function fetchConfirmedOpens() {
		$message_id='00096c9b1058655bc3af';

		$data='{}';
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "https://api.sparkpost.com/api/v1/message-events?campaign_ids=employee%20invites&events=open&per_page=10000&from=2016-10-20T08:00");//message_ids=".$message_id);
		//curl_setopt($ch, CURLOPT_URL, "https://api.sparkpost.com/api/v1/suppression-list/maurizio.ranaboldo@gmail.com");//message_ids=".$message_id);


		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json" , "Authorization: 3eb0e32b17ddf4f130c994161082f86c33b09fa9"));
		//curl_setopt($ch, CURLOPT_POSTFIELDS, $data);


		curl_setopt($ch, CURLOPT_POST, 0);


		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);


		curl_setopt($ch, CURLINFO_HEADER_OUT, true); // enable tracking

		$content=curl_exec($ch);
		$this->logger->debug(  curl_getinfo($ch, CURLINFO_HEADER_OUT )); // request headers);
		curl_close($ch);

		$rs=json_decode($content);
		$res=array();
		foreach($rs->results as $r){
			$res[$r->rcpt_to]=$r->rcpt_to;
		}

		foreach($res as $r){
			print $r."<br>";
		}
		//print "<pre>".$content."</pre>";
	}



	function send($body, $recipients, $subject, $tag='default') {
		$data['body']=$body;
		$data['subject']=$subject;

       	$text= $this->loader->view("mails/mailtpl_v", $data, true);
		//print $text;exit();


        $text=str_replace("\"","\\\"",$text);

		$recipients=json_encode($recipients);
		/*
		   [{ "address": "maurizio.ranaboldo@gmail.com" },
                   { "address": "lorenzo.meriggi@gmail.com" }]
      			"subject": "Tetra Pak @ Gulfood 2016 App invitation",
		 */

		$data='{
    		"content": {
      			"from": "noreply@tetrapakevents.com",
      			"subject": "'.$subject.'",
      			"html": "'.$text.'"
    			},
    		"campaign_id": "'.$tag.'",
    		"description": "'.$tag.'",
    		"recipients":'.$recipients.
  		'}';
//$this->logger->debug($data);exit();
//		print $data;exit();

		$ch = curl_init();
       // curl_setopt($ch, CURLOPT_VERBOSE, 1);
		curl_setopt($ch, CURLOPT_URL, "https://api.sparkpost.com/api/v1/transmissions");
		//curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json" , "Authorization: 3eb0e32b17ddf4f130c994161082f86c33b09fa9"));
    //	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);


        curl_setopt($ch, CURLOPT_POST, 1);


		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	/*	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_ENCODING, "UTF-8");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_AUTOREFERER, true);
		curl_setopt($ch, CURLOPT_MAXREDIRS, 10);*/


        curl_setopt($ch, CURLINFO_HEADER_OUT, true); // enable tracking

		$content=curl_exec($ch);
		$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

	//	echo 'HTTP code: ' . $httpcode;
    //    $this->logger->debug(  curl_getinfo($ch, CURLINFO_HEADER_OUT )); // request headers);
		curl_close($ch);

		$this->logger->debug($content);

		return $httpcode;
	}

    /**
     * @param $templateId
     * @param $substitutionData
     * @param $recipients
     * @throws \Exception
     */
    function sendWithTemplate($templateId, $substitutionData, $recipients) {

		$data = ["content" => ["template_id" => $templateId/*, "use_draft_template" => true*/],
            "substitution_data" => $substitutionData,
            "recipients" => $recipients
        ];

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "https://api.sparkpost.com/api/v1/transmissions");
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json" , "Authorization: 136f60ae5e4571891dca28a824b5f3270c467f46"));
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true); // enable tracking

		$content = curl_exec($ch);
		$curlStatus = curl_errno($ch);
		$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		curl_close($ch);

		if ($content === false) throw new \Exception("Error sending quote [network error: $curlStatus ]");
		if (!$content) throw new \Exception("Error sending quote [empty result from mailer service]");

//        print $httpcode;
//        print $content;
//

        if ($httpcode != 200) throw new \Exception("Error sending quote [mailer service error]");

		$contentDecoded = @json_decode($content);
		if (!$contentDecoded) throw new \Exception("Error sending quote [invalid response from mailer service]");
        if (empty($contentDecoded->results->total_accepted_recipients)) throw new \Exception("[mailer service didn't accept recipient]");
	}

}

class Mail{
	var $email;
	var $text;
	var $subject;

}