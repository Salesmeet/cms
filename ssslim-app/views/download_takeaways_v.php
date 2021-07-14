<link rel="stylesheet" media="all" href="https://cdn.jsdelivr.net/gh/hankchizljaw/boilerform@master/dist/css/boilerform.min.css" />
<style>
    .o-heading {
        display: block;
        width: 100%;
        font-size: 20px;
        line-height: 1.2;
        font-weight: 400;
        border-bottom: 1px solid #ccc;
        padding: 0 0 5px 0;
        margin: 0 auto 20px auto;
    }
    
    .c-signup {
        min-width: 300px;
        max-width: 500px;
        margin: 30px auto;
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        grid-gap: 20px;
    }
    .c-signup__field > input[type] {
        width: 100%;
    }
    .c-signup__field--fill {
        grid-column: span 2;
    }
    .c-signup__button {
        grid-column: span 2;
        text-align: right;
    }
    form {
        padding: 20px;
        background: #fff;
        
        margin: 0 auto;
    }
    .error {
        display: none;
        background:#e74c3c;
        color: #fff;
        padding: 3px 5px;
        font-size: 14px
    }
    .force {
        display: block
    }
    .boilerform [class*="-field"] {
        font-family: IBMPlexSansCond, sans-serif;
    }
    .note {
        font-size: 14px
    }
    .boilerform .c-select-field__menu {
        width: 100%;
        padding-right: 0
    }
    .boilerform .c-select-field__decor {
        top: 65%
    }

    main {
        display: flex;
        justify-content: center;
        align-items: center;
    }
    
    @media only screen and (min-width: 768px) {
        
        form {
            max-width: 420px;
            border-radius: 2px;
        }
    }
</style>
<? if (!$isGateLifted): ?>
<section id="gateForm">
    <form id="signForm" class="boilerform" action="" method="post" novalidate>
        <h1 class="o-heading">Get the takeaways from Your clouds can 2019</h1>
        <div class="c-signup">
            
            <div class="c-signup__field c-signup__field--fill" id="emailWrp">
                <label for="email" class="c-label">Email address</label>
                <input type="email" name="email" id="email" class="c-input-field" required />
                <p class="error email<?= !empty($validationErrors['email']) ? ' force' : '' ?>"><?= !empty($validationErrors['email']) ? $validationErrors['email'] : 'Sorry, this field requires a valid e-mail address' ?></p>
            </div>
            
            <div class="c-signup__field">
                <label for="fname" class="c-label">First name</label>
                <input type="text" name="fname" id="fname" class="c-input-field" required />
                <p class="error fname<?= !empty($validationErrors['fname']) ? ' force' : '' ?>"><?= !empty($validationErrors['fname']) ? $validationErrors['fname'] : 'Sorry, this is a required field' ?></p>
            </div>
            <div class="c-signup__field">
                <label for="lname" class="c-label">Last name</label>
                <input type="text" name="lname" id="lname" class="c-input-field" required />
                <p class="error lname<?= !empty($validationErrors['lname']) ? ' force' : '' ?>"><?= !empty($validationErrors['lname']) ? $validationErrors['lname'] : 'Sorry, this is a required field' ?></p>
            </div>
            <div class="c-signup__field">
                <label for="company" class="c-label">Company</label>
                <input type="text" name="company" id="company" class="c-input-field" required />
                <p class="error company<?= !empty($validationErrors['company']) ?  'force' : '' ?>"><?= !empty($validationErrors['company']) ? $validationErrors['company'] : 'Sorry, this is a required field' ?></p>
            </div>
            <div class="c-signup__field">
                <label for="title" class="c-label">Job Title</label>
                <input type="text" name="jobtitle" id="jobtitle" class="c-input-field" required />
                <p class="error jobtitle<?= !empty($validationErrors['jobtitle']) ? ' force' : '' ?>"><?= !empty($validationErrors['jobtitle']) ? $validationErrors['jobtitle'] : 'Sorry, this is a required field' ?></p>
            </div>
            <div class="c-select-field">
               <label for="country" class="c-label">Country</label>
               <select style="margin-top:7px" name="country" id="country" class="c-select-field__menu">
                    <option value="">Select your country</option>
                    <? foreach($countries as $c): ?>
                        <option value="<?= $c['code'] ?>"><?= $c['name'] ?></option>
                    <? endforeach ?>
                </select>
                <span class="c-select-field__decor" aria-hidden="true" role="presentation">&dtrif;</span>
            </div>
            <div class="c-signup__field">
                <label for="state" class="c-label">State</label>
                <input type="text" name="state" id="state" class="c-input-field" />
            </div>
            <div class="c-check-field c-signup__field--fill">
                <input type="checkbox" name="check" id="check" class="c-check-field__input" />
                <label for="check" class="c-check-field__decor" aria-hidden="true" role="presentation"></label>
                <label for="check" class="c-check-field__label">IBM may use my contact data to keep me informed of products, services and offerings by email.</label>
            </div>
            <div class="c-check-field c-signup__field--fill">
                <p class="note">
                    You can withdraw your marketing consent from IBM at any time by sending an email to <a href="mailto:netsupp@us.ibm.com">netsupp@us.ibm.com</a>.<br /><br />
                    Also you may unsubscribe from receiving marketing emails by clicking the unsubscribe link in each such email.<br />
                    More information on IBM's processing can be found in the IBM <a href="https://www.ibm.com/privacy/us/en/" target="_blank">Privacy Statement</a>.<br />
                    By submitting this form, I acknowledge that I have read and understand the IBM Privacy Statement.
                </p>
            </div>
    
    
            <div class="c-signup__button">
                <button class="c-button" type="submit">Submit</button>
            </div>
        </div>

    </form>
</section>

<script>
    $(function() {
      var emailEl = $("#email"),
          formEl = document.querySelector("#signForm"),
          input = null,
          inputs = $('#signForm input'),
          step = 0;
      
      $("input, select, .note").not("#email").parent().hide();

      formEl.addEventListener("submit", function (e) {
        
        $(".error").hide();
        if ( !validateEmail(emailEl.val()) ) {
          showErr("email");
          e.preventDefault();
          return false;
        }
        if (step === 0) {
          e.preventDefault();
          
          $.post("<?= site_url() ?>check-email", { email: emailEl.val() }, function( data ) {
            if (data.s === 0) {
              $("input, select, .note").not("#email").parent().fadeIn();
              $("input[name=onlyMail]").remove();
              step = 1;
            } else {
              if (!input) {
                input = $("<input>").attr("type", "hidden").attr("name", "onlyMail").val(1);
                $(formEl).append(input);
                $(formEl).submit();
              }
            }
          }, "json");
        } else {
          //check for all required elements and set errors
          for(var i=0; i < inputs.length; i++){
            if(inputs[i].value === '' && inputs[i].hasAttribute('required')){
              showErr(inputs[i].id);
              e.preventDefault();
            }
          }
        }
        
      });

      function validateEmail(email) {
        var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        return re.test(String(email).toLowerCase());
      }
      
      function showErr(field) {
        $(".error." + field).show();
      }

      
    });
</script>

<? else: ?>
    
    <h1>Passed</h1>

<? endif ?>
