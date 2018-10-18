<!doctype html>
<?php
$date = new DateTime('now +2 hour');
// Zeit angeben in +1 day oder +4 hour oder combiniere +1 day +4 hour
$tomorrow = $date->modify('+1 day +2 hour');
?>
<html>
<head>
    <meta charset="UTF-8">
    <title>ViaBiona: Natur in reinster Form</title>
    <script type="text/javascript">
        setTimeout(function () {
            location.reload(true);
        }, 30000);
    </script>
    <!-- add bookmarking script -->
    <script type="text/javascript">
        $(function() {
            $("#bookmarkme").click(function() {
                // Mozilla Firefox Bookmark
                if ('sidebar' in window && 'addPanel' in window.sidebar) {
                    window.sidebar.addPanel(location.href,document.title,"");
                } else if( /*@cc_on!@*/false) { // IE Favorite
                    window.external.AddFavorite(location.href,document.title);
                } else { // webkit - safari/chrome
                    alert('Press ' + (navigator.userAgent.toLowerCase().indexOf('mac') != - 1 ? 'Command/Cmd' : 'CTRL') + ' + D to bookmark this page.');
                }
            });
        });
    </script>
    <style>

        body {
            float: none;
            font-family: 'Helvetica', sans-serif;
            text-align: center;
            width: 50vw;
            min-width: 600px;
            margin: 0 auto;
            color: #232323;

        }

        h1 {
            font-size: 2rem;
        }
        .logo {
            float: none;
            width: 50vw;
            margin: 0 auto;
            margin-top: 1rem;
            display: inline-block;
            position: relative;
        }
        article {
            float: none;
            display: block;
            text-align: left;
            width: 50vw;
            min-width: 600px;
            margin: 0 auto;
            margin-top: 2rem;
            background: #efefef;
            border: 1px solid #565656;
            padding: 20px;
            border-radius: 5px;
        }

        a {
            color: #1d1c5b;
            text-decoration: none;
        }

        a:hover {
            color: #777;
            text-decoration: none;
        }

        .label {
           font-size: 1.2rem;
        }

        .message {
            font-size: 0.8rem;
            font-style: italic;
            text-align: center;
        }
        #bookmarkme {
            padding: 10px;
            background: #e09000;
            color: #ffffff;
            transition: all 0.5s;
            border-radius: 5px;

        }
        #bookmarkme:hover {
            background: #ffb100;
            color: #232323;
            transition: all 0.5s;
            border-radius: 5px;
        }
    </style>
    <link rel="shortcut icon" href="https://www.viabiona.com/media/logo/viabiona-icon.ico" type="image/x-icon">
</head>
<body>
<div class="logo"><a href="https://www.viabiona.com/" class="text-muted"><img class="img-responsive" src="media/logo/viabiona-logo.png" alt="Viabiona.com | Vitamine, Vitalstoffe, Mikronährstoffe"></a></div>
<article>
    <h2>Wartungsarbeiten</h2>
    <h3>Viabiona.com wird derzeit für Sie gewartet. Wir bitten um Ihr Verständnis.</h3>
   <br />
    <p class="label">Vermutlich wieder erreichbar am:<strong> <?php echo $tomorrow->format('d F Y H:i') ?></strong></p>
    <div>
        <p>Unsere Anschrift lautet:</p>
        <address>Viabiona Ltd.</address>
        <address>Postbus 320</address>
        <address>6460 AH Kerkrade</address>
        <address>Niederlande</address>
        <p>Vertretungsberechtigter: Mr. Christian Sickinger</p>

        <p><a href="tel:0080030030001">Tel: 00800 300 300 01</a>
        <p>Fax: 00800 300 300 02</p>

        <p>Oder schicken Sie eine E-Mail an: <a href="mailto:mail@viabiona.com">viabiona.com</a></p>
        <h4>Bitte vergessen Sie uns nicht! Setzen Sie ein lesezeichen im Ihrem browser</h4>
        <a id="bookmarkme" href="https://www.viabiona.com" rel="sidebar" title="Viabiona: Natur in reinster Form">Viabiona.com im Browser speichern</a>
        <br />
    </div>
    <div>
        <br />
        <p class="message">Diese seite wird in 30 sekunden herladen...</p>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>

    </div>
</article>
</body>
</html>
