<?php
namespace Ssslim\Libraries;
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use Ssslim\Core\Libraries\Logger;
use Ssslim\Libraries\Notification\NotificationsManager;
use Ssslim\Libraries\Notification\Notification;

class LeadsManager
{
    private $logger;
    private $db;
    private $cacheFactory;
    private $notificationsManager;

    /* getLeads stuff */
    private $getLeadsAndFilters = [];

    private $limit = 0;
    private $offset = 0;

    const LEADS_FILTER_START_DATE = 1;
    const LEADS_FILTER_END_DATE = 2;
    const LEADS_FILTER_STATUS = 3;
    const LEADS_FILTER_TYPE = 4;
    /* end of getLeads stuff */

    private $attachmentDownloadEndPoint = "get_quote/";

    public function __construct(Logger $logger, \DB $db, Cache\CacheFactory $cacheFactory)
    {
        $this->logger = $logger;
        $this->db = $db;
        $this->cacheFactory = $cacheFactory;
    }

    public static function getFromDbRow($data) {
        return new Lead($data);
    }

    public function addLeadsFilter($filterType, $filterValue = null, $type = "AND")
    {
        $f = new \stdClass();
        $f->type = $filterType;
        $f->value = $filterValue;

        $this->getLeadsAndFilters[] = $f;
        return $this;
    }

    public function setOffset($offset, $limit = null)
    {
        if ($offset !== null) $this->offset = $offset;
        if ($limit !== null) $this->limit = $limit;
        return $this;
    }

    function getLeads($returnCount = false){

        $qWhere = " WHERE 1";

        foreach ($this->getLeadsAndFilters as $f) {

            switch ($f->type) {
                case self::LEADS_FILTER_START_DATE:
                    $qWhere .= " AND leads.transdate >= '" . $f->value . "'";
                    break;


                case self::LEADS_FILTER_END_DATE:
                    $qWhere .= " AND leads.transdate <= '" . $f->value . "'";
                    break;


/*                case self::LEADS_FILTER_STATUS:
                    $qWhere .= " AND leads.status = '" . $f->value . "'";
                    break;


                case self::LEADS_FILTER_TYPE:
                    $qWhere .= " AND leads.type = '" . $f->value . "'";
                    break;*/

                default:
                    throw new \Exception("UNKNOWN FILTER");
                    break;
            }
        }

        $qLimit = ($this->limit) ? " LIMIT $this->offset , $this->limit" : '';

        if ($returnCount) return $this->db->query("SELECT COUNT(lead_id) AS n FROM leads $qWhere ")->row()->n;
        else return $this->db->query("SELECT ycc_dashboard.leads.* FROM leads 
                                        /*LEFT JOIN gdpr ON gdpr.q_user_id = leads.q_user_id*/  
                                        $qWhere 
                                        ORDER BY transdate DESC $qLimit")->result();

       /* $c = $this->cacheFactory->get("contents/leads/list");

        if (true || !$c->isValid()) {
            $l=$this->db->query("SELECT lead_id FROM leads ORDER BY generatedTime ASC")->result();
            $c->userData = $l;
            $c->commit(24);
        }

        return $c->userData;*/
    }

    /**
     * @param $lead_id
     * @return Lead
     */
    function getLead($lead_id){
        $c = $this->cacheFactory->get("contents/leads/".$lead_id);

        if (true || !$c->isValid()) {
            $e = $this->db->query("SELECT ycc_dashboard.leads.*/*, COALESCE(gdpr.GDPRAcceptedDate, '') AS GDPRAcceptedDate */FROM leads 
                                    /*LEFT JOIN gdpr ON gdpr.q_user_id = old_leads.q_user_id*/
                                    WHERE lead_id=$lead_id")->row();
            // $this->logger->debug($e);
            if(!empty($e)) {
                $c->userData = new Lead($e);
            }else{
                $c->userData=null;
            }
//            $c->commit(24);
        }

        return $c->userData;
    }

    /**
     * @param string $downloadToken
     * @return Lead
     * @throws \Exception
     */
    public function getLeadFromAttachmentToken($downloadToken = "")
    {
        if (!$downloadToken) throw new \Exception("downloadTokenNotProvided");

            $l = $this->db->query("SELECT * FROM old_leads WHERE attachmentDownloadToken = " . $this->db->escape($downloadToken))->row();
            if (!$l) throw new \Exception("quoteNotFound");

            return new Lead($l);
    }


    /**
     * @param string $email
     * @return Lead
     * @throws \Exception
     */
    public function getLeadFromEmail($email = "")
    {
        if (!$email) throw new \Exception("emailNotProvided");
        $l = $this->db->query("SELECT * FROM ycc_dashboard.leads WHERE email = " . $this->db->escape($email))->row();
        return new Lead($l ? $l : null);
    }

    public function deleteLead($lead_id){
        $this->db->query("DELETE FROM ycc_dashboard.leads WHERE lead_id=$lead_id");

        $this->cacheFactory->invalidateCacheFile("contents/leads/$lead_id");
//        $this->cacheFactory->invalidateCacheFile("contents/leads/list/".LeadsManager::DEFAULT_EVENTS_LABEL);
    }

    public function saveLead(Lead $e){
        if($e->lead_id>0){
            $lead_id=intval($e->lead_id);

            //This is an update, we can proceed!
            $this->db->query(
                "UPDATE ycc_dashboard.leads SET
                source=".$this->db->escape($e->source)
                .",fname=".$this->db->escape($e->fname)
                .",lname=".$this->db->escape($e->lname)
                .",jobtitle=".$this->db->escape($e->jobtitle)

                .",company=".$this->db->escape($e->company)
                .",state=".$this->db->escape($e->state)
                .",country=".$this->db->escape($e->country)
                .",email=".$this->db->escape($e->email)
                .",assetcode=".$this->db->escape($e->assetcode)
                .",checked=".$this->db->escape($e->checked)
                .",q1=".$this->db->escape($e->q1)
                .",q2=".$this->db->escape($e->q2)
                .",q2_elaborate=".$this->db->escape($e->q2_elaborate)
                .",q3=".$this->db->escape($e->q3)
                .",q4=".$this->db->escape($e->q4)
                .",q5=".$this->db->escape($e->q5)
                .($e->transdate ? ",transdate=".$this->db->escape($e->transdate) : "")
                ." WHERE lead_id=$lead_id");
        }else{
            //This is an insert
            $this->db->query(
                "INSERT IGNORE INTO ycc_dashboard.leads (source, transdate, fname, lname, jobtitle, company, state, country, email, assetcode, checked, q1, q2, q2_elaborate, q3, q4, q5) VALUES ("
                .$this->db->escape($e->source)
                ."," . ($e->transdate ? $this->db->escape($e->transdate) :  $this->db->escape(gmdate("Y-m-d H:i:s")))
                .",".$this->db->escape($e->fname)
                .",".$this->db->escape($e->lname)
                .",".$this->db->escape($e->jobtitle)
                .",".$this->db->escape($e->company)
                .",".$this->db->escape($e->state)
                .",".$this->db->escape($e->country)
                .",".$this->db->escape($e->email)
                .",".$this->db->escape($e->assetcode)
                .",".$this->db->escape($e->checked)
                .",".$this->db->escape($e->q1)
                .",".$this->db->escape($e->q2)
                .",".$this->db->escape($e->q2_elaborate)
                .",".$this->db->escape($e->q3)
                .",".$this->db->escape($e->q4)
                .",".$this->db->escape($e->q5)
                .")");
            if($this->db->affected_rows()==0){
                return false;
            }

            $e->lead_id=$this->db->insert_id();
        }

        $this->cacheFactory->invalidateCacheFile("contents/leads/$e->lead_id");

        return $e;
    }

    /**
     * @param $r
     * @return int
     * @throws \Exception
     */
    public function upsertLead($r) {
        
        $email = !empty($r->email) ? $r->email : "";

        if ($email) $existingLead = $this->db->query("SELECT * FROM ycc_dashboard.leads WHERE email = " . $this->db->escape($email))->row();
        // no email from querlo let's try with querlo_uid
        else throw new \Exception("Email not provided in upsertLead");

        if (!$existingLead) $existingLead = new Lead(null);
        $leadId = $existingLead->lead_id;

        $source = !empty($r->source) ? $r->source : $existingLead->source;
        $fname = !empty($r->fname) ? $r->fname : $existingLead->fname;
        $lname = !empty($r->lname) ? $r->lname : $existingLead->lname;
        $state = !empty($r->state) ? $r->state : $existingLead->state;
        $country = !empty($r->country) ? $r->country : $existingLead->country;
        $jobtitle = !empty($r->jobtitle) ? $r->jobtitle : $existingLead->jobtitle;
        $company = !empty($r->company) ? $r->company : $existingLead->company;
        $checked = !empty($r->check) ? "yes" : $existingLead->checked;
        $assetcode = !empty($r->assetcode) ?  $this->addAssetCode($existingLead->assetcode, $r->assetcode) : $existingLead->assetcode;


        if ($leadId) {
            $this->db->query("UPDATE ycc_dashboard.leads SET ".
                ($email ? " email = " . $this->db->escape($email) : "") .
                ($fname ? " , fname = " . $this->db->escape($fname) : "") .
                ($lname ? " , lname = " . $this->db->escape($lname) : "") .
                ($jobtitle ? ", jobtitle = " . $this->db->escape($jobtitle) : "") .
                ($company ? ", company = " . $this->db->escape($company) : "") .
                ($state ? ", state = " . $this->db->escape($state) : "") .
                ($country ? ", country = " . $this->db->escape($country) : "") .
                ($assetcode ? ", assetcode = " . $this->db->escape($assetcode) : "") .
                ($checked ? ", checked = " . $this->db->escape($checked) : "") .
                "WHERE lead_id = $leadId"
            );

            $this->logger->log(sprintf("(FORM) Updated lead, id: %d, querlo_uid: %d", $leadId, $existingLead->querlo_uid), Logger::INFO, "ycc_leads.log");
        }
        else {
            $this->db->query("INSERT INTO ycc_dashboard.leads (querlo_uid, source, transdate, fname, lname, jobtitle, company, state, country, email, assetcode, checked) 
                                    VALUES (" .
                "NULL" . ", " .
                $this->db->escape($source) . "," .
                "'" . gmdate('Y-m-d H:i:s') . "'," .
                $this->db->escape($fname) . "," .
                $this->db->escape($lname) . "," .
                $this->db->escape($jobtitle) . "," .
                $this->db->escape($company) . "," .
                $this->db->escape($state) . "," .
                $this->db->escape($country) . "," .
                $this->db->escape($email) . "," .
                $this->db->escape($assetcode) . "," .
                $this->db->escape($checked) .
                ")
                                ");

            $this->logger->log(sprintf("(FORM) New lead inserted: email: %s, lead id: %d", $email, $this->db->insert_id()), Logger::INFO, "ycc_leads.log");
            $leadId = $this->db->insert_id();
        }

        return $leadId;
    }

    public function addAssetCode($currentAssetCode, $newAssetCode)
    {
        if ($currentAssetCode == "") return $newAssetCode;

        $parts = explode(",", $currentAssetCode);

        if (!in_array($newAssetCode, $parts)) $parts[] = $newAssetCode;
        return implode(",", $parts);
    }

    public function getAvailableCountries()
    {
        $toReturn = [];
        $countries = $this->db->query('SELECT * FROM countries')->result();
        foreach ($countries as $country) {
            $toReturn[] = ['name' => $country->name, 'code' => $country->country_code];
        }
        return $toReturn;
    }

}


class Lead{

    var $lead_id=0;
    public $querlo_uid = null;

    public $source = "YourCloudsCan";
    public $transdate = "";
    public $fname = "";
    public $lname = "";
    public $jobtitle = "";
    public $company = "";
    public $state = "";
    public $country = "";
    public $email = "";
    public $assetcode = "";
    public $checked = "";

    public $q1 = "";
    public $q2_elaborate = "";
    public $q2 = "";
    public $q3 = "";
    public $q4 = "";
    public $q5 = "";

    private static $empty;

    public function __construct($data)
    {
        if($data==null){
            $this->transdate = gmdate('Y-m-d H:i:s');
            return;
        }

        $this->lead_id=$data->lead_id;
        $this->transdate=$data->transdate;

        if (isset($data->source)) $this->source=$data->source;
        if (isset($data->fname)) $this->fname=$data->fname;
        if (isset($data->lname)) $this->lname=$data->lname;
        if (isset($data->jobtitle)) $this->jobtitle=$data->jobtitle;
        if (isset($data->company)) $this->company=$data->company;
        if (isset($data->state)) $this->state=$data->state;
        if (isset($data->country)) $this->country=$data->country;
        if (isset($data->email)) $this->email=$data->email;
        if (isset($data->assetcode)) $this->assetcode=$data->assetcode;
        if (isset($data->checked)) $this->checked=$data->checked;

        if (isset($data->q1)) $this->q1=$data->q1;
        if (isset($data->q2)) $this->q2=$data->q2;
        if (isset($data->q2_elaborate)) $this->q2_elaborate=$data->q2_elaborate;
        if (isset($data->q3)) $this->q3=$data->q3;
        if (isset($data->q4)) $this->q4=$data->q4;
        if (isset($data->q5)) $this->q5=$data->q5;
    }

    public static function getEmpty(){
        if(Lead::$empty==null){
            Lead::$empty=new Lead(null);
        }
        return Lead::$empty;
    }

    public function getAttachmentDownloadUrl()
    {
        return $this->attachmentDownloadToken ? site_url('get_quote/' . $this->attachmentDownloadToken) : "";
    }

    /**
     * @return string
     */
    public function getGdprAcceptedDate()
    {
        return $this->gdprAcceptedDate != null ? $this->gdprAcceptedDate : "NOT ACCEPTED";
    }

}


