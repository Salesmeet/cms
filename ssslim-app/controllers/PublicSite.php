<?php

namespace Ssslim\Controllers;

use Ssslim\Libraries\Csv;
use Ssslim\Libraries\Form;
use Ssslim\Libraries\FormField;
use Ssslim\Libraries\AppCore;
use Ssslim\Libraries\Notification\NotificationsManager;
use Ssslim\Libraries\Pagination;
use Ssslim\Libraries\Response;
use Ssslim\Libraries\LeadsManager;
use Ssslim\Libraries\Lead;
use Ssslim\Libraries\Forms;
use Ssslim\Libraries\User\UserFactory;
use Ssslim\Libraries\User\User;
use Ssslim\Libraries\MailManager;
use Ssslim\Libraries\User\UserNotFoundException;
use Ssslim\Libraries\User\UserInactiveException;


class PublicSite
{

    private $leadsManager;
    private $userFactory;
    private $appCore;
    private $forms;
    private $loader;
    private $mailManager;
    private $pagination;

    private $leadAttachmentDir = "files/"; // TODO move to config file?

    private $_playbookIdData = [
        'p1' => ["url" => "https://drive.google.com/file/d/1WWWIKKaocmWNADFeoNpkRoQ_Hoy_Kute/preview", "file" => "190524_IBM_PLAYBOOK2 ADV CUTOMER JOURNEY.pdf"],
        'p2' => ["url" => "https://drive.google.com/file/d/18ZHmabGc6Zvs_mo8e1eyu4f68OYfHvsa/preview", "file" => "190524_IBM_PLAYBOOK2 ADVANCED Brand Building Focus.pdf"],
        'p3' => ["url" => "https://drive.google.com/file/d/1F8XikXSFTCuMpX-X-wsSuN27L9_5ydrs/preview", "file" => "190524_IBM_PLAYBOOK2 ADVANCED Supply Chain.pdf"],
        'p4' => ["url" => "https://drive.google.com/file/d/1RFU1-tnT3_MNV2Pmqhhn1THhi-dOyzPV/preview", "file" => "190524_IBM_PLAYBOOK2 ADVANCEDStore Operations Focus.pdf"],
        'p5' => ["url" => "https://drive.google.com/file/d/19ip3o6wLZhEE4kLo6yygd1t2b-djKClE/preview", "file" => "190524_IBM_PLAYBOOK2 BEG Brand Building Focus.pdf"],
        'p6' => ["url" => "https://drive.google.com/file/d/14YcCa18vZ569TGR5nH2VCFh66PxP0Ogp/preview", "file" => "190524_IBM_PLAYBOOK2 BEG Customer Experience Focus.pdf"],
        'p7' => ["url" => "https://drive.google.com/file/d/1VkDwOP-7zI6xc296wDbqdORDffYlkVTN/preview", "file" => "190524_IBM_PLAYBOOK2 BEG Store Operations Focus.pdf"],
        'p8' => ["url" => "https://drive.google.com/file/d/1p0GDk5YhfX32E3Zwj5i316ZdQoIFsKFL/preview", "file" => "190524_IBM_PLAYBOOK2 BEG Supply Chain Focus.pdf"]
    ];

    const assetCodeForGatedArticle = "ov71832";
    const assetCodeForPlaybooks = "ov71672";

    const gateLiftedCookieName = 'gt';
    const liftGateSecretKey = "jk4453_kfj79JHnm2zkQ";


    function __construct(AppCore $appCore, LeadsManager $leadsManager, UserFactory $userFactory, Forms $forms, \CI_Loader $loader, MailManager $mailManager, Pagination $pagination)
    {
        $this->appCore = $appCore;
        $this->leadsManager = $leadsManager;
        $this->userFactory = $userFactory;
        $this->forms = $forms;
        $this->loader = $loader;
        $this->mailManager = $mailManager;
        $this->pagination = $pagination;

    }

    private function _isGateLifted()
    {
        return isset($_COOKIE[self::gateLiftedCookieName]);
    }

    private function _getLeadIdFromGateCookie()
    {
        return isset($_COOKIE[self::gateLiftedCookieName]) ? $_COOKIE[self::gateLiftedCookieName] : '';
    }


    private function _liftGate($leadID = 0)
    {
        setcookie(self::gateLiftedCookieName, $leadID, time() + (86400 * 365), "/");
    }

    public function downloadTakeaways()
    {
        $r = $this->appCore->getRequest();
        if (!empty($r->liftgate) && $r->liftgate == self::liftGateSecretKey) {
            $this->_liftGate();
            $this->appCore->redirect('download-key-takeaways');
        }

        $validationErrors = [];
        $countries = [];
        $onlyMail = true;

        $isGateLifted = $this->_isGateLifted();

        if (!$isGateLifted) {
            if (isset($r->email)) { // form submitted

                if (empty($r->onlyMail)) { // full form submit

                    $onlyMail = false;

                    $form = new Form(
                        [
                            new FormField('email', true),
                            new FormField('fname', true),
                            new FormField('lname', true),
                            new FormField('company', true),
                            new FormField('jobtitle', true),
                        ]
                    );

                    $validationErrors = $this->forms->getValidationErrors($form, $r);

                    if (!empty($r->email) && !filter_var($r->email, FILTER_VALIDATE_EMAIL)) {
                        $validationErrors['email'] = "Invalid e-mail address";
                    }

                    if (count($validationErrors) == 0) { //VALIDATION PASSED

                        try {
                            $r->assetcode = self::assetCodeForGatedArticle;
                            $upsertedLeadId = $this->leadsManager->upsertLead($r);
                        } catch (\Exception $e) {
                            return;
                        }

                        // got here, leads has been upserted, gate can be lifted
                        $this->_liftGate($upsertedLeadId);
                        $isGateLifted = true;
                    }
                } else { //email only submit

                    $form = new Form(
                        [
                            new FormField('email', true),
                        ]
                    );

                    $validationErrors = $this->forms->getValidationErrors($form, $r);

                    if (!empty($r->email) && !filter_var($r->email, FILTER_VALIDATE_EMAIL)) {
                        $validationErrors['email'] = "Invalid e-mail address";
                    }

                    if (count($validationErrors) == 0) { //VALIDATION PASSED

                        $r = $this->leadsManager->getLeadFromEmail($r->email);
                        if ($r->lead_id == 0) {
                            // fatal error, how should we handle it?
                        }

                        $r->assetcode = $this->leadsManager->addAssetCode($r->assetcode, self::assetCodeForGatedArticle);
                        $this->leadsManager->saveLead($r);

                        // got here, leads has been upserted, gate can be lifted
                        $this->_liftGate($r->lead_id);
                        $isGateLifted = true;
                    }
                }
            }
            $countries = $this->leadsManager->getAvailableCountries();
        } else {
            if ( ($leadId = $this->_getLeadIdFromGateCookie()) != '0') {
                $lead = $this->leadsManager->getLead($leadId);
                if (!stristr($lead->assetcode, self::assetCodeForGatedArticle)) {
                    $lead->assetcode = $this->leadsManager->addAssetCode($lead->assetcode, self::assetCodeForGatedArticle);
                    $this->leadsManager->saveLead($lead);
                }
            }
        }

        $viewData['content'] = $this->loader->view("download_takeaways_v", ['isGateLifted' => $isGateLifted, 'validationErrors' => $validationErrors, 'onlyMail' => $onlyMail,  'countries' => $countries], true);
        $this->_render($viewData);
    }


    public function playbook($playbookId)
    {
        if (!$playbookId || !isset($this->_playbookIdData[$playbookId])) {
            show_404();
        }

        $r = $this->appCore->getRequest();

        if (!empty($r->liftgate) && $r->liftgate == self::liftGateSecretKey) {
            $leadId = 0;

            if (!empty($r->email)) {
                try {
                    $existingLead = $this->leadsManager->getLeadFromEmail($r->email);
                } catch (\Exception $e) {
                    return; // will never get here as email can't be empty
                }

                $leadId = $existingLead->lead_id;
                $existingLead->assetcode = $this->leadsManager->addAssetCode($existingLead->assetcode, self::assetCodeForPlaybooks);
                $this->leadsManager->saveLead($existingLead);
            }

            $this->_liftGate($leadId);
            $this->appCore->redirect("playbook/$playbookId");
        }

        $isGateLifted = $this->_isGateLifted();

        $viewData['content'] = $this->loader->view("playbook_" . $playbookId . "_v", ['isGateLifted' => $isGateLifted, 'id' => $playbookId, 'pdfUrl' => $this->_playbookIdData[$playbookId]['url']], true);
        $this->_render($viewData);
    }

    public function home()
    {
        $r = $this->appCore->getRequest();
        if (!empty($r->liftgate) && $r->liftgate == self::liftGateSecretKey) {
            $this->_liftGate();
            $this->appCore->redirect('');
        };

        if (ONLINE && !isset($r->postEvent)) $this->loader->view('home_old_chat_v', []);
        else {
            $userEmail = "(empty)";
            if ( $this->_isGateLifted() && ($leadId = $this->_getLeadIdFromGateCookie()) != '0') {
                $lead = $this->leadsManager->getLead($leadId);
                $userEmail = $lead ? $lead->email : "(empty)";
            }
            $viewData['content'] = $this->loader->view('home_v', ['email' => $userEmail], true);
            $this->_render($viewData);
        }
    }

    public function download($assetId = "")
    {
        if (!$assetId || !isset($this->_playbookIdData[$assetId])) {
            show_404();
        }

        // check permissions
        if (!$this->_isGateLifted()) {
            show_404();
        }

        $localFile = $this->_playbookIdData[$assetId]['file'];
        $filePathName = $this->leadAttachmentDir . $localFile;

        if (!file_exists($filePathName)) {
            die("Internal server error [FileError]");
        }

        $fileSize = filesize($filePathName);
        if (!$fileSize) {
            die("Internal server error [EmptyFileError]");
        }

        header('Content-Type: "application/octet-stream"');
        header('Content-Disposition: attachment; filename="' . $localFile . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header("Content-Transfer-Encoding: binary");
        header('Pragma: public');
        header("Content-Length: " . $fileSize);

        readfile($filePathName);
    }

    public function checkEmail()
    {
        $p = $this->appCore->getRequest();

        if (empty($p->email)) {
            print json_encode(['s' => 0]);
            return;
        }

        try {
            $l = $this->leadsManager->getLeadFromEmail($p->email);
        } catch (\Exception $e) {
            print json_encode(['s' => 0]);
            return;
        }
        print json_encode(['s' => $l->lead_id ? 1 : 0]);
    }

    public function clearCookie()
    {
        setcookie(self::gateLiftedCookieName, '', time() - 3600, "/");
        $this->appCore->redirect();
    }

    public function privacy()
    {
        $viewData['content'] = $this->loader->view("privacy_v", [], true);
        $this->_render($viewData);
    }

    private function _render($data = [])
    {
        // $data['config'] = json_encode($this->buildAppConfig());
        $this->loader->view('tpl_v', $data);
    }

}
