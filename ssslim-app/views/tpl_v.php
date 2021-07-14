<!doctype html>
<html lang="en-US" class="no-js">
<head>
    <meta charset="UTF-8">
    <title>Your Clouds Can 2019 | IBM Futurism</title>
    <link rel="apple-touch-icon-precomposed" sizes="57x57" href="<?= base_url() ?>wp-content/themes/futurism/img/icons/apple-touch-icon-57x57.png">
    <link rel="apple-touch-icon-precomposed" sizes="114x114" href="<?= base_url() ?>wp-content/themes/futurism/img/icons/apple-touch-icon-114x114.png">
    <link rel="apple-touch-icon-precomposed" sizes="72x72" href="<?= base_url() ?>wp-content/themes/futurism/img/icons/apple-touch-icon-72x72.png">
    <link rel="apple-touch-icon-precomposed" sizes="144x144" href="<?= base_url() ?>wp-content/themes/futurism/img/icons/apple-touch-icon-144x144.png">
    <link rel="apple-touch-icon-precomposed" sizes="60x60" href="<?= base_url() ?>wp-content/themes/futurism/img/icons/apple-touch-icon-60x60.png">
    <link rel="apple-touch-icon-precomposed" sizes="120x120" href="<?= base_url() ?>wp-content/themes/futurism/img/icons/apple-touch-icon-120x120.png">
    <link rel="apple-touch-icon-precomposed" sizes="76x76" href="<?= base_url() ?>wp-content/themes/futurism/img/icons/apple-touch-icon-76x76.png">
    <link rel="apple-touch-icon-precomposed" sizes="152x152" href="<?= base_url() ?>wp-content/themes/futurism/img/icons/apple-touch-icon-152x152.png">
    <link rel="icon" type="image/png" href="<?= base_url() ?>wp-content/themes/futurism/img/icons/favicon-196x196.png" sizes="196x196">
    <link rel="icon" type="image/png" href="<?= base_url() ?>wp-content/themes/futurism/img/icons/favicon-96x96.png" sizes="96x96">
    <link rel="icon" type="image/png" href="<?= base_url() ?>wp-content/themes/futurism/img/icons/favicon-32x32.png" sizes="32x32">
    <link rel="icon" type="image/png" href="<?= base_url() ?>wp-content/themes/futurism/img/icons/favicon-16x16.png" sizes="16x16">
    <link rel="icon" type="image/png" href="<?= base_url() ?>wp-content/themes/futurism/img/icons/favicon-128.png" sizes="128x128">
    <meta name="application-name" content="&nbsp;">
    <meta name="msapplication-TileColor" content="#FFFFFF">
    <meta name="msapplication-TileImage" content="mstile-144x144.png">
    <meta name="msapplication-square70x70logo" content="mstile-70x70.png">
    <meta name="msapplication-square150x150logo" content="mstile-150x150.png">
    <meta name="msapplication-wide310x150logo" content="mstile-310x150.png">
    <meta name="msapplication-square310x310logo" content="mstile-310x310.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="This is a tagline">
    <meta name='robots' content='noindex,follow'>
<!--    <link rel='stylesheet' id='wp-block-library-css' href='--><?//= base_url() ?><!--wp-includes/css/dist/block-library/style.min.css' type='text/css' media='all'>-->
    <link rel='stylesheet' id='futurism-css-css' href='<?= base_url() ?>wp-content/themes/futurism/css/styles.css' type='text/css' media='all'>
    
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://www.yourcloudscan.com/">
    <meta property="og:title" content="Your Clouds Can 2019">
    <meta property="og:image" content="<?= site_url() ?>images/preview.jpg">
    <meta property="og:description" content="Experience the future of cloud-based technology for consumer companies. Sponsored by Futurism and IBM.">
    <meta property="og:locale" content="en_EN">
    
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    
    <link href="https://fonts.googleapis.com/css?family=Lato:400,400i,700&display=swap" rel="stylesheet">

</head>
<body class="home page-template page-template-template-home page-template-template-home-php page page-id-8">

<header>
    <div class="max">
        <div class="social">
            <a href="<?= base_url() ?>?postEvent=1" class="home"><span>Home</span></a>
            <a href="https://www.linkedin.com/shareArticle?mini=true&url=https://www.yourcloudscan.com" class="linkedin"><span>LinkedIn</span></a>
            <a href="https://twitter.com/intent/tweet?url=https://www.yourcloudscan.com" class="twitter"><span>Twitter</span></a>
            <a href="https://www.facebook.com/sharer.php?u=https://www.yourcloudscan.com" class="facebook"><span>Facebook</span></a>
            <a href="mailto:?subject=Your Clouds Can | IBM Futurism&body=Check out this site https://www.yourcloudscan.com." class="email"><span>Email</span></a>
        </div>
    </div>
</header>

<main>
    
    <?=$content;?>

</main>

<footer>
    <div class="max">
        <div id="copy">
            &copy; 2019 IBM
        </div>
        <div class="links">
            <a href="<?= site_url() ?>privacy">Privacy Policy</a> | <a href="http://www.ibm.com/legal/us/en/?lnk=flg-tous-usen" target="_blank">Terms of Use</a> | <a href="http://www.ibm.com/accessibility/us/en/?lnk=flg-acce-usen" target="_blank">Accessibility</a>
        </div>
    </div>
</footer>

<script>
    $(function() {
      window.getWindowOptions = function () {
        var width = 500,
            height = 350,
            left = (window.innerWidth / 2) - (width / 2),
            top = (window.innerHeight / 2) - (height / 2);

        return [
          'resizable,scrollbars,status',
          'height=' + height,
          'width=' + width,
          'left=' + left,
          'top=' + top,
        ].join();
      }

      $(".social a").not('.email, .home').on("click", function (e) {
        e.preventDefault();
        var win = window.open($(this).attr('href'), 'share', window.getWindowOptions());
        win.opener = null; // 2
      });
    });
</script>

<script src="<?=base_url()?>js/cookiechoices.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', function(event) {
    cookieChoices.showCookieConsentBar('<b>About Cookies on This Site</b><br><br>Your clouds can uses cookies which are necessary for the proper functioning of its websites. Subject to your preferences, Your clouds can  may also use cookies to improve your experience, to secure and remember log-in details, for session management, to collect statistics, to optimize site functionality and to deliver content tailored to your interests. We honor the preferences you select, both here and in specific applications where further cookie preferences will specifically be solicited.<br>To provide a smooth navigation, your cookie preferences will be shared across the following  Your clouds can web domains where the purpose and use of the cookies will remain the same: bluemix.net, bluewolf.com, ibm.com, ibmcloud.com, softlayer.com and securityintelligence.com.<br><br>Click “Agree and proceed with  Your clouds can standard Settings” to accept cookies and go directly to the site or click “View cookie settings” for a detailed description of the types of cookies and/or to customize your cookie selection.',
    'Agree and proceed with  Your clouds can standard Settings', 'Privacy Policy', '<?=site_url("privacy")?>');
  });
</script>

</body>
</html>
