<?php if (!empty($errors)): ?>
    <div class="alert alert-danger" role="alert">
        <p>Submitted data contains errors, changes have not been saved.<br>
            Please check the fields in red below.<br>
        <?foreach ($errors as $error) {
            print "- " . $error . "<br>";
        }?>
        </p>
    </div>
<?php endif ?>

<?/** @var Ssslim\Libraries\Lead $lead?> */?>
<form id = "editLead" action="<?=site_url("dashboard/edit_lead/".$lead->lead_id)?>" method="post" enctype="multipart/form-data">
    <div class="row">
        <div class="form-group col-md-3" >
            <label for="source">Source</label>
            <div class="inline alert alert-info" role="alert">
                <p><?=$lead->source ? $lead->source : "&nbsp;";?></p>
            </div>
        </div>
        <div class="form-group col-md-3" >
            <label for="source">AssetCode</label>
            <div class="inline alert alert-info" role="alert">
                <p><?=$lead->assetcode ? $lead->assetcode : "&nbsp;";?></p>
            </div>
        </div>
        <div class="form-group col-md-3" >
            <label for="source">OptIn</label>
            <div class="inline alert alert-info" role="alert">
                <p><?=$lead->checked ? $lead->checked : "&nbsp;";?></p>
            </div>
        </div>
        <div class="form-group col-md-3  <?=isset($errors['generatedTime'])?'has-error':''?> ">
            <label for="transdate">Generated time</label>
            <div class='input-group date datepicker' id="generatedTime">
                <input type='text' class="form-control" name="transdate" />
                <span class="input-group-addon">
                    <span class="glyphicon glyphicon-calendar"></span>
                </span>
            </div>
        </div>
    </div>

    <fieldset name="contact">
        <legend>Contact information</legend>
        <div class="row">
            <div class="form-group col-md-4  <?=isset($errors['$this->fname'])?'has-error':''?> ">
                <label for="fname">First Name</label>
                <input type="text" class="form-control" id="fname" name="fname" placeholder="First Name" value="<?= $lead->fname?>">
            </div>
            <div class="form-group col-md-4  <?=isset($errors['$this->lname'])?'has-error':''?> ">
                <label for="lname">Last Name</label>
                <input type="text" class="form-control" id="lname" name="lname" placeholder="Last Name" value="<?= $lead->lname?>">
            </div>
            <div class="form-group col-md-4  <?=isset($errors['email'])?'has-error':''?> ">
                <label for="email">Email</label>
                <input type="text" class="form-control" id="email" name="email" placeholder="Email" value="<?= $lead->email?>">
            </div>
        </div>
        <div class="row">
            <div class="form-group col-md-6  <?=isset($errors['$this->jobtitle'])?'has-error':''?> ">
                <label for="jobtitle">Job Title</label>
                <input type="text" class="form-control" id="jobtitle" name="jobtitle" placeholder="Job Title" value="<?= $lead->jobtitle?>">
            </div>
            <div class="form-group col-md-6  <?=isset($errors['company'])?'has-error':''?> ">
                <label for="company">Company</label>
                <input type="text" class="form-control" id="company" name="company" placeholder="Company" value="<?= $lead->company?>">
            </div>
        </div>

        <div class="row">
            <div class="form-group col-md-6  <?=isset($errors['State'])?'has-error':''?> ">
                <label for="state">State</label>
                <input type="text" class="form-control" id="state" name="state" placeholder="State" value="<?= $lead->state?>">
            </div>
            <div class="form-group col-md-6  <?=isset($errors['$this->country'])?'has-error':''?> ">
                <label for="country">Country</label>
                <input type="text" class="form-control" id="country" name="country" placeholder="Country" value="<?= $lead->country?>">
            </div>
        </div>

        <div class="row">
            <div class="form-group col-md-6  <?=isset($errors['q1'])?'has-error':''?> ">
                <label for="q1">What is the top business priority for technology within your organization?</label>
                <textarea class="form-control" id="q1" rows="3" name="q1"><?= $lead->q1?></textarea>
            </div>
            <div class="form-group col-md-6  <?=isset($errors['q2'])?'has-error':''?> ">
                <label for="q2">Is there a particular area about this priority that’s most important or are you just generally interested in this area?</label>
                <textarea class="form-control" id="q2" rows="3" name="q2"><?= $lead->q2?></textarea>
            </div>
        </div>

        <div class="row">
            <div class="form-group col-md-6  <?=isset($errors['q2_elaborate'])?'has-error':''?> ">
                <label for="q2_elaborate">Please elaborate</label>
                <textarea class="form-control" id="q2_elaborate" rows="3" name="q2_elaborate"><?= $lead->q2_elaborate?></textarea>
            </div>
            <div class="form-group col-md-6  <?=isset($errors['q3'])?'has-error':''?> ">
                <label for="q3">How would you characterize your organization as relates to adopting new technologies?</label>
                <textarea class="form-control" id="q3" rows="3" name="q3"><?= $lead->q3?></textarea>
            </div>
        </div>

        <div class="row">
            <div class="form-group col-md-6  <?=isset($errors['q4'])?'has-error':''?> ">
                <label for="q4">Where is your company on the journey of using data and AI to improve business?</label>
                <textarea class="form-control" id="q4" rows="3" name="q4"><?= $lead->q4?></textarea>
            </div>
            <div class="form-group col-md-6  <?=isset($errors['q5'])?'has-error':''?> ">
                <label for="q5">Where is your company on the journey to modernizing its cloud infrastructure?</label>
                <textarea class="form-control" id="q5" rows="3" name="q5"><?= $lead->q5?></textarea>
            </div>
        </div>

    </fieldset>

    <div class="row saveBlock">
        <div class="form-group col-md-6">
            <button type="submit" value="doSubmit" name="doSubmit"  class="btn btn-primary"><span class="glyphicon glyphicon-floppy-disk"></span> Save</button>
            <input type="hidden" name="sendQuoteOnLoad" value="0" />
        </div>
    </div>
</form>

<link href="<?= base_url() ?>css/admin/bootstrap-datetimepicker.css" rel="stylesheet">
<script src="//cdnjs.cloudflare.com/ajax/libs/moment.js/2.9.0/moment-with-locales.js"></script>
<script src="<?= base_url() ?>js/admin/bootstrap-datetimepicker.js"></script>
<script type="text/javascript">



    function setSendQuoteButtonState() {
        if ($('#txt').val() != "" &&  $("#value").val() != "" && $("#email").val() != "") {
            $('#sendEmailQuote').removeClass("disabled");
        }
        else  $('#sendEmailQuote').addClass("disabled");
    }

    function doSendEmail() {

        $('#sendEmailQuote').addClass("disabled");
        window.setTimeout(function() {$('#sendEmailQuote').removeClass("disabled")}, 5000);

        $.ajax({
            // dataType: "json",
            url : '<?=site_url("admin/send_email_quote")?>/<?=$lead->lead_id;?>',
            type: 'GET',
            success : function(r){
                if (r.s !== 0) {
                    alert ('Error sending email: ' + r.e);
                }
                else alert('Quote successfully sent');
            }
        });
    }

    $(function () {
        var sendOnLoad = <?=$sendQuoteOnLoad?>;
        $('#editLead input[name="sendQuoteOnLoad"]').val("0");
        $('#generatedTime').datetimepicker({format: 'YYYY-MM-DD HH:mm', defaultDate: '<?= $lead->transdate?>'});

        setSendQuoteButtonState();

        $('#txt, #value, #email').on('change', setSendQuoteButtonState);

        if (sendOnLoad) doSendEmail();

        $('#sendEmailQuote').click(function () {

            if ($(this).hasClass("disabled")) return;

            if ($('#attachment').val() == "" && $('#downloadAttachment').length == 0) {
                if (!confirm("No quote has been attached. Do you want to send anyway?")) return;
            }

            $('#editLead input[name="sendQuoteOnLoad"]').val("1");
            $('#editLead').submit();
            return false;
        });

        $('#markWon').click(function () {
            //$('#status option[value="won"]').prop("selected", true);
            $('#status').val("won");
            $('#editLead').submit();
        });


    });
</script>