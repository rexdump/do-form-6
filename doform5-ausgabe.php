<?php
/**==================================================
 * REDAXO-Modul: do form!  http://klxm.de/produkte/
 * Bereich: Ausgabe
 * Version: 5.1.1 rex5, Datum: 10.11.2015
 *==================================================*/
//   KONFIGURATION
$ftitel                      = 'REX_VALUE[4]'; // Überschrift / Betreff der E-Mail
$ssldomain                   = $_SERVER['HTTP_HOST'];
$style                       = 'class="formerror"'; // Label-Stildefinition für Fehler
$bstyle                      = 'formerror'; // Formfield-Fehler-Klasse
$formname                    = "doform" . "REX_SLICE_ID"; // Formular ID generiert aus SLICE ID
$formdatum                   = date("d.m.Y"); // Datum
$formzeit                    = date("H:i"); // Uhrzeit
$formreq                     = '&nbsp;<strong class="formreq">*</strong>'; // Markierung von Pflichtfeldern
$formbcc                     = "REX_VALUE[11]"; // BCC-Feld
$sendfullmail                = "REX_VALUE[13]"; //Original senden an Bestätigungsmail anhängen
$submitlabel                 = "REX_VALUE[7]"; // Bezeichnung des Sende-Buttons
$redaxofile                  = $REX['HTDOCS_PATH'] . "files/" . "REX_FILE[1]"; // Pfad zum Dateianhang bei Bestätigungs-E-Mail
$uploadpfad_mit_mailschicken = true; // Bei E-Mail-Anhängen
// FROMMODE: true entspricht der Absender der E-Mail dem Empfänger der Mail
// Bei false wird der Absender der im PHPMailer-Addon hinterlegt wurde übernommen
$frommode                    = true; // Standard=true
// Welche Felder sollen nicht in der E-Mail  übertragen werden?
$ignore                      = array(
    'captcha',
    'sicherheitscode',
    'ilink',
    'ilink2',
    'divstart',
    'divend',
    'fieldend',
    'info',
    'exlink'
);
//  Captcha
$captchaID                   = 000; // ID zum Captcha-Artikel der das Captcha-Template nutzt
$captchasource               = htmlspecialchars(rex_getUrl($captchaID));
// Alternative: Externe Einbindung eines Captchas
// $captchasource="/redaxo/captcha/captcha.php";
// Fehlermeldungen / Mehrsprachig
// Sprache 0 -- Hier Deutsch
if ($REX['CUR_CLANG'] == 0) {
    //### Achtung! Hinter <<< EOD darf kein Leerzeichen stehen.
    $fError   = <<<EOD
Leider konnten wir Ihre Anfrage nicht bearbeiten. <br /> Bitte überprüfen Sie Ihre Eingaben.
EOD;
    $ibaninfo = <<<EOD
<br/> Zu Ihrer Sicherheit wurde die IBAN anonymisiert. <br/>
EOD;
    $ibaninfo = <<<EOD
\n Zu Ihrer Sicherheit wurde die IBAN anonymisiert. \n
EOD;
    $frel     = "<br />Sie haben versucht die Seite neu zu laden. <br />Ihre Nachricht wurde bereits verschickt";
}
// Sprache 1 -- z.B. Englisch
if ($REX['CUR_CLANG'] == 1) {
    $fError = <<<EOD
Unfortunately we have been unable to process your request. <br/>
Please check the information you have provided.
EOD;
    $frel   = "<br />You have tried to reload this page. Your message has already been sent.";
}
// Sprache 2 -- z.B. Niederlande
if ($REX['CUR_CLANG'] == 2) {
    $fError = <<<EOD
We konden uw aanvraag helaas niet verwerken.<br/>
Controleer uw gegevens.
EOD;
    $frel   = "<br />You have tried to reload this page. Your message has been already sent.";
}
// E-Mail-HEADER
$doformhtml       = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="de" lang="de">
<head>
<meta http-equiv="Content-Type" content="text/html; charset="UTF-8" />
<title>NACHRICHTEN-ÜBERMITTLUNG</title>
<style type="text/css">
<!--
body {
        font-family: Arial, Helvetica, sans-serif;
        font-size: 1.3em;
        color: #003366;
        line-height: 1em;
        background-color: #F9F9F9;
}
h1 { color: #003366;
        background-color: #FFFFCC;
        display: block;
        clear: both;
        font-size: 1.2em;
        }
h2 { color: #003366;
        border-bottom-width: 1px;
        border-bottom-style: solid;
        border-bottom-color: #999999;
        display: block;
        clear: both;
        font-size: 0.9em;
        }
 
.dfheader {
        border-top-width: 6px;
        border-top-style: solid;
        border-top-color: #999999;
        color: #FFFFFF;
        background-color: #003366;;
        padding-top: 0px;
        padding-right: 2px;
        padding-bottom: 0px;
        padding-left: 2px;
        text-align: center;
        margin: 0px;
}
.slabel {
        width: 230px;
        display: block;
        float: left;
        margin-right: 5px;
        color: #666666;
        font-weight: normal;
}
br {
        clear: both;
        display: block;
}
-->
</style>
 
</head>
<body>
<div class="dfheader">
  ' . $ftitel . '
</div><br/>
';
// E-Mail-Footer
$doformhtmlfooter = '<hr size="1" /><br />
<br /></body></html>';
$nonhtmlfooter    = "\n----------------------------------\n
 ";
// Ende der allgemeinen Konfiguration

$sselect      = "";
$absendermail = "";
$linkclass    = "";
$cupload      = 0;
$fcounter     = 1;
$xcounter     = 1;
/* --------------------------- SSL-Schalter ------------ */
if ('REX_VALUE[18]' == "SSL") {
    // SSL - SCHALTER
    if ($REX['REDAXO'] != 1) {
        if ($_SERVER['SERVER_PORT'] != 443) {
            $datei = $_SERVER['REQUEST_URI'];
            // Domain anpassen
            $ziel  = $ssldomain . $datei;
            header("Location: $ziel");
            exit();
        }
    }
}

if (!function_exists('is_old_android')) {
    function is_old_android($version = '4.2.0')
    {
        
        if (strstr($_SERVER['HTTP_USER_AGENT'], 'Android')) {
            
            preg_match('/Android (\d+(?:\.\d+)+)[;)]/', $_SERVER['HTTP_USER_AGENT'], $matches);
            
            return version_compare($matches[1], $version, '<=');
            
        }
        
    }
}


/**
 * prueft ob die Mindestanzahl an Argumenten mit der Vorgabe uebereinstimmt
 * 
 * Achtung! Die Mindestanzahl an Elementen muss mit Array-Zaehlweise angegeben werden.
 * D.h., die Zahlung beginnt inkl. der Null.
 * 
 * @param int     $mustHave - Mindestanzahl an Elementen 
 * @param array   $elements - Elementa-Array
 * @param string  $formelement - Name des Elementes in dem der Check ausgefuehrt wird
 * @return string
 */

if (!function_exists('doform_checkElements')) {
    function doform_checkElements($mustHave, $elements, $formelement)
    {
        global $REX;
        // Diese Information ist nur im Backend zu sehen
        if ($REX['REDAXO']) {
            // $formelement darf nicht leer sein
            if ($formelement == '') {
                return 'Der Formelementename wurde nicht erkannt. Siehe Funktion "doform_checkElements"<br />';
            }
            // $mustHave muss mind. 2 sein
            if ((int) $mustHave < 2) {
                return $formelement . ': Die Vorgabezahl darf nicht kleiner als 2 sein!<br />';
            }
            // $elements muss ein Array sein
            if (!is_array($elements)) {
                return $formelement . ': Das ubergebene Element ist kein Array.<br />';
            }
            $anzahlElemente = count($elements);
            if ($mustHave > count($elements)) {
                $fehlermeldung = 'Es wurden nicht genuegend Argumente fuer das Formualarfeld "' . $formelement . '" angegeben.<br />';
                $fehlermeldung .= 'Angegeben wurden ' . $anzahlElemente . ' Argumente, benoetigt werden aber mind. ' . $mustHave . ' Argumente!<br />' . "\n";
                return $fehlermeldung;
            } else {
                return '';
            }
        } else {
            return '';
        }
    }
}
/**
 * Gibt eine Fehlermeldung vom Upload zurueck
 * 
 * @param $error_code
 * @see http://de.php.net/manual/en/features.file-upload.errors.php
 * @return string   Fehlermeldung
 */
if (!function_exists('file_upload_error_message')) {
    function file_upload_error_message($error_code)
    {
        switch ($error_code) {
            case UPLOAD_ERR_INI_SIZE: // Fehler Nr.: 1
                return 'The uploaded file exceeds the upload_max_filesize directive in php.ini';
            case UPLOAD_ERR_FORM_SIZE: // Fehler Nr.: 2
                return 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form';
            case UPLOAD_ERR_PARTIAL: // Fehler Nr.: 3
                return 'The uploaded file was only partially uploaded';
            case UPLOAD_ERR_NO_FILE: // Fehler Nr.: 4
                return 'No file was uploaded';
            case UPLOAD_ERR_NO_TMP_DIR: // Fehler Nr.: 6 (Introduced in PHP 4.3.10 and PHP 5.0.3.)
                return 'Missing a temporary folder';
            case UPLOAD_ERR_CANT_WRITE: // Fehler Nr.: 7 (Introduced in PHP 5.1.0.)
                return 'Failed to write file to disk';
            case UPLOAD_ERR_EXTENSION: // Fehler Nr.: 8 (Introduced in PHP 5.2.0.)
                return 'File upload stopped by extension';
            default:
                return 'Unknown upload error';
        }
    }
}



if (!function_exists('getValidIban')) {
    function getValidIban($iban)
    {
        // normalize
        $iban    = str_replace(array(
            ' ',
            '-',
            '.',
            ','
        ), '', strtoupper($iban));
        // define the pattern
        $pattern = '#(?P<value>((?=[0-9A-Z]{28}$)AL\d{10}[0-9A-Z]{16}$|^(?=[0-9A-Z]{24}$)AD\d{10}[0-9A-Z]{12}$|^(?=[0-9A-Z]{20}$)AT\d{18}$|^(?=[0-9A-Z]{22}$)BH\d{2}[A-Z]{4}[0-9A-Z]{14}$|^(?=[0-9A-Z]{16}$)BE\d{14}$|^(?=[0-9A-Z]{20}$)BA\d{18}$|^(?=[0-9A-Z]{22}$)BG\d{2}[A-Z]{4}\d{6}[0-9A-Z]{8}$|^(?=[0-9A-Z]{21}$)HR\d{19}$|^(?=[0-9A-Z]{28}$)CY\d{10}[0-9A-Z]{16}$|^(?=[0-9A-Z]{24}$)CZ\d{22}$|^(?=[0-9A-Z]{18}$)DK\d{16}$|^FO\d{16}$|^GL\d{16}$|^(?=[0-9A-Z]{28}$)DO\d{2}[0-9A-Z]{4}\d{20}$|^(?=[0-9A-Z]{20}$)EE\d{18}$|^(?=[0-9A-Z]{18}$)FI\d{16}$|^(?=[0-9A-Z]{27}$)FR\d{12}[0-9A-Z]{11}\d{2}$|^(?=[0-9A-Z]{22}$)GE\d{2}[A-Z]{2}\d{16}$|^(?=[0-9A-Z]{22}$)DE\d{20}$|^(?=[0-9A-Z]{23}$)GI\d{2}[A-Z]{4}[0-9A-Z]{15}$|^(?=[0-9A-Z]{27}$)GR\d{9}[0-9A-Z]{16}$|^(?=[0-9A-Z]{28}$)HU\d{26}$|^(?=[0-9A-Z]{26}$)IS\d{24}$|^(?=[0-9A-Z]{22}$)IE\d{2}[A-Z]{4}\d{14}$|^(?=[0-9A-Z]{23}$)IL\d{21}$|^(?=[0-9A-Z]{27}$)IT\d{2}[A-Z]\d{10}[0-9A-Z]{12}$|^(?=[0-9A-Z]{20}$)[A-Z]{2}\d{5}[0-9A-Z]{13}$|^(?=[0-9A-Z]{30}$)KW\d{2}[A-Z]{4}22!$|^(?=[0-9A-Z]{21}$)LV\d{2}[A-Z]{4}[0-9A-Z]{13}$|^(?=[0-9A-Z]{,28}$)LB\d{6}[0-9A-Z]{20}$|^(?=[0-9A-Z]{21}$)LI\d{7}[0-9A-Z]{12}$|^(?=[0-9A-Z]{20}$)LT\d{18}$|^(?=[0-9A-Z]{20}$)LU\d{5}[0-9A-Z]{13}$|^(?=[0-9A-Z]{19}$)MK\d{5}[0-9A-Z]{10}\d{2}$|^(?=[0-9A-Z]{31}$)MT\d{2}[A-Z]{4}\d{5}[0-9A-Z]{18}$|^(?=[0-9A-Z]{27}$)MR13\d{23}$|^(?=[0-9A-Z]{30}$)MU\d{2}[A-Z]{4}\d{19}[A-Z]{3}$|^(?=[0-9A-Z]{27}$)MC\d{12}[0-9A-Z]{11}\d{2}$|^(?=[0-9A-Z]{22}$)ME\d{20}$|^(?=[0-9A-Z]{18}$)NL\d{2}[A-Z]{4}\d{10}$|^(?=[0-9A-Z]{15}$)NO\d{13}$|^(?=[0-9A-Z]{28}$)PL\d{10}[0-9A-Z]{,16}n$|^(?=[0-9A-Z]{25}$)PT\d{23}$|^(?=[0-9A-Z]{24}$)RO\d{2}[A-Z]{4}[0-9A-Z]{16}$|^(?=[0-9A-Z]{27}$)SM\d{2}[A-Z]\d{10}[0-9A-Z]{12}$|^(?=[0-9A-Z]{,24}$)SA\d{4}[0-9A-Z]{18}$|^(?=[0-9A-Z]{22}$)RS\d{20}$|^(?=[0-9A-Z]{24}$)SK\d{22}$|^(?=[0-9A-Z]{19}$)SI\d{17}$|^(?=[0-9A-Z]{24}$)ES\d{22}$|^(?=[0-9A-Z]{24}$)SE\d{22}$|^(?=[0-9A-Z]{21}$)CH\d{7}[0-9A-Z]{12}$|^(?=[0-9A-Z]{24}$)TN59\d{20}$|^(?=[0-9A-Z]{26}$)TR\d{7}[0-9A-Z]{17}$|^(?=[0-9A-Z]{,23}$)AE\d{21}$|^(?=[0-9A-Z]{22}$)GB\d{2}[A-Z]{4}\d{14}))#';
        // check
        if (preg_match($pattern, $iban, $matches)) {
            return true;
        } else {
            return false;
        }
    }
}
if (!function_exists('convertBytes')) {
    function convertBytes($value)
    {
        if (is_numeric($value)) {
            return $value;
        } else {
            $value        = trim($value);
            $value_length = strlen($value);
            $qty          = substr($value, 0, $value_length - 1);
            $unit         = strtolower(substr($value, $value_length - 1));
            switch ($unit) {
                case 'g':
                    $qty *= 1024;
                case 'm':
                    $qty *= 1024;
                case 'k':
                    $qty *= 1024;
                case 'b':
                    $qty = $qty;
            }
            return $qty;
        }
    }
}
//### Achtung! Hinter <<< End darf kein Leerzeichen stehen.
$rex_form_data = <<<End
REX_VALUE[id=3]
End;
//### Achtung! Hinter <<< End darf kein Leerzeichen stehen.
$mailbody      = <<<End
End;
$responsemail  = <<<End
REX_HTML_VALUE[5]
End;
if (isset($_POST['eingabe'])) {
    $eingabe = $_POST['eingabe'];
}
$FORM          = rex_post('FORM', 'array');
$formoutput    = array();
$warning       = array();
$warning_set   = 0; // wird zu 1, wenn eine Fehler auftritt
$form_elements = array();
$form_elements = explode("\n", $rex_form_data);
//Abfrage Felder Vor- und Nachname, 14.05.2014, Benedikt Marcard, Marcard Media, www.marcard-media.de
for ($i = 0; $i < count($form_elements); $i++) {
    $element = explode("|", $form_elements[$i]);
    switch ($element[1]) {
        case ("Nachname"):
            $responsemail = str_replace("%Nachname%", $FORM[$formname]['el_' . $i], $responsemail);
            break;
        case ("Vorname"):
            $responsemail = str_replace("%Vorname%", $FORM[$formname]['el_' . $i], $responsemail);
            break;
        case ("Anrede"):
            if ($FORM[$formname]['el_' . $i] == 'Herr') {
                $responsemail = str_replace("%Anrede%", 'Sehr geehrter ' . $FORM[$formname]['el_' . $i] . '', $responsemail);
            }
            if ($FORM[$formname]['el_' . $i] == 'Frau') {
                $responsemail = str_replace("%Anrede%", 'Sehr geehrte ' . $FORM[$formname]['el_' . $i] . '', $responsemail);
            }
            break;
    }
}
$FORM          = rex_post('FORM', 'array');
$formoutput    = array();
$warning       = array();
$warning_set   = 0; // wird zu 1, wenn eine Fehler auftritt
$form_elements = array();
$form_elements = explode("\n", $rex_form_data);
$responsemail  = str_replace("%Datum%", $formdatum, $responsemail);
$responsemail  = str_replace("%Zeit%", $formzeit, $responsemail);
//Adresse die als Absenderadresse der Bestätigungs-E-Mail eingegeben wurde
$responsemail  = str_replace("%Absender%", "REX_VALUE[2]", $responsemail);
//Empfänderadresse die im Modul angegeben wurde
$responsemail  = str_replace("%Mail%", "REX_VALUE[1]", $responsemail);
$responsemail  = str_replace("%Betreff%", "REX_VALUE[4]", $responsemail);
$token         = md5(uniqid('token'));
$formcaptcha   = null;
$dfreload      = null;
$mailbodyhtml  = '';
$form_enctype  = '';
/**
 * Enthaelt die Dateiangaben der uebertragenen Datei und den Namen der Zieldatei
 * Form: array ( targetFile => tempFile )
 * @var array
 */
$upload_File   = array();
for ($i = 0; $i < count($form_elements); $i++) {
    // ueberspringe Leerzeilen
    if (trim($form_elements[$i]) == '') {
        continue;
    }
    $element   = explode("|", $form_elements[$i]);
    $AFE[$i]   = $element;
    $formfield = 0;
    if (!isset($FORM[$formname]['el_' . $i])) {
        $FORM[$formname]['el_' . $i] = '';
    }
    if (!isset($FORM[$formname][$formname . 'send'])) {
        $FORM[$formname][$formname . 'send'] = '';
    }
    if (!isset($warning["el_" . $i])) {
        $warning["el_" . $i] = NULL;
    }
    switch ($element[0]) {
        case "svar":
        case "session":
            $formoutput[] = '
          <input type="hidden" title="' . $element[1] . '" name="FORM[' . $formname . '][el_' . $i . ']" id="el_' . $i . '" value="' . $_SESSION["REX_VALUE[16]"] . '" />';
            break;
        //  Gestaltungselemente
        case "headline":
            $formoutput[] = '<div class="formheadline">' . $element[1] . '<input type="hidden" title="' . $element[1] . '" name="FORM[' . $formname . '][el_' . $i . ']" id="el_' . $i . '" value="' . $element[1] . '"/></div>';
            break;
        case "info":
            $formoutput[] = '<div class="formhinweis">' . $element[1] . '<input type="hidden" title="' . $element[1] . '" name="FORM[' . $formname . '][el_' . $i . ']" id="el_' . $i . '" value="' . $element[1] . '"/></div>';
            break;
        case "HTML":
            $formoutput[] = '<div class="formhtml">' . $element[1] . '</div>';
            break;
        case "exlink":
            $formoutput[] = '<div class="formlink"><a href="' . $element[1] . '" onclick="window.open(this.href); return false;">' . $element[2] . '</a></div>';
            break;
        case "ilink":
            if ($element[3] != "") {
                $linkclass = 'class="' . $element[3] . '" ';
            } else {
                $linkclass = "";
            }
            $formoutput[] = '<div class="formlink"><a ' . $linkclass . 'href="' . rex_getUrl($element[1]) . $element[2] . '">' . $element[4] . '</a></div>';
            break;
        case "trennelement":
            $formoutput[] = '<div class="formtrenn"><hr/></div>';
            break;
        case "fieldstart":
            $formoutput[] = '<fieldset class="fieldset"><legend>' . $element[1] . '</legend><input type="hidden" title="' . $element[1] . '" name="FORM[' . $formname . '][el_' . $i . ']" id="el_' . $i . '" value="' . $element[1] . '"/>';
            $formfield    = "on";
            break;
        case "fieldend":
            $formoutput[] = '</fieldset>';
            $formfield    = "on";
            break;
        case "divstart":
            $str   = $element[1];
            $first = $str[0];
            $id    = str_replace("#", '', $str);
            if (!isset($element[2]))
                $element[2] = ''; // Zeile eingefügt - ### MW ###
            if ($first == '#') {
                $formoutput[] = '<div id="' . $id . '">' . $element[2];
            } else {
                $formoutput[] = '<div class="' . $element[1] . '">' . $element[2];
            }
            $formfield = "on";
            break;
        case "divend":
            $formoutput[] = '</div>';
            $formfield    = "on";
            break;
        // Formular-Felder 
        case "checkbox":
            $req      = '';
            $cchecked = "";
            if (isset($element[2]) && $element[2] == 1) {
                $req = $formreq;
            }
            if (!isset($element[3]))
                $element[3] = ''; // Zeile eingefügt - ### MW ###
            if ((trim($FORM[$formname]["el_" . $i]) == "X") || ($FORM[$formname]["el_" . $i] == '' && !$FORM[$formname][$formname . "send"] && $element[3] == 1)) {
                $cchecked = ' checked="checked"';
                $hidden   = "";
            } else {
                $cchecked = '';
                //$hidden = '<div><input type="hidden" name="FORM['.$formname.'][el_'.$i.']" value="0" /></div>';
                $hidden   = "";
            }
            if (isset($element[2]) && $element[2] == 1 && $cchecked == "" && $FORM[$formname][$formname . "send"]) {
                $warning["el_" . $i]   = $style;
                $warnblock["el_" . $i] = $bstyle;
                $e                     = 1;
                $warning_set           = 1;
            }
            $formoutput[] = $hidden . '
              <div class="fieldblock ' . $warnblock["el_" . $i] . '"> <span class="checkspan"><label ' . $warning["el_" . $i] . ' for="el_' . $i . '" >' . $element[1] . $req . '</label>
                <input type="checkbox" title="' . $element[1] . '" class="formcheck" name="FORM[' . $formname . '][el_' . $i . ']" id="el_' . $i . '"value="X" ' . $cchecked . ' /></span><br/></div>';
            break;
        // Radio-Buttons von Markus Feustel 07.01.2008
        case "radio":
            $req = '';
            if (isset($element[2]) && $element[2] == 1) {
                $req = $formreq;
            }
            if ((trim($FORM[$formname]["el_" . $i]) == 1) || ($FORM[$formname]["el_" . $i] == '' && !$FORM[$formname][$formname . "send"] && $element[3] == 1)) {
                $checked = ' checked="checked"';
                $hidden  = '';
            } else {
                $checked = "";
                $hidden  = '<input type="hidden" name="FORM[' . $formname . '][el_' . $i . ']" value="0" />';
            }
            if (trim($FORM[$formname]["el_" . $i]) == '' && trim($element[5]) != '') {
                $FORM[$formname]["el_" . $i] = trim($element[5]);
            }
            if (isset($element[2]) && $element[2] == 1 && trim($FORM[$formname]["el_" . $i]) == "" && $FORM[$formname][$formname . "send"] == 1) {
                $warning["el_" . $i]   = $style;
                $warnblock["el_" . $i] = $bstyle;
                $warning_set           = 1;
                $e                     = 1;
            }
            $ro            = explode(';', trim($element[3]));
            $val           = explode(';', trim($element[4]));
            $formlabel[$i] = '<label ' . $warning["el_" . $i] . ' for="el_' . $i . '" >' . $element[1] . $req . '</label>';
            $fo            = $formlabel[$i] . '<div id="el_' . $i . '" >' . "\n";
            for ($xi = 0; $xi < count($ro); $xi++) {
                if ($val[$xi] == trim($FORM[$formname]["el_" . $i])) {
                    $checked = ' checked="checked"';
                } else {
                    $checked = '';
                }
                $fo .= '<br/><input type="radio" class="formradio" name="FORM[' . $formname . '][el_' . $i . ']" id="r' . $i . '_Rel_' . $xi . '" value="' . $val[$xi] . '" ' . $checked . ' />' . "\n";
                $fo .= '<label class="radiolabel" ' . $warning["el_" . $i] . 'for="r' . $i . '_Rel_' . $xi . '" >' . $ro[$xi] . '</label>' . "\n";
            }
            $fo .= '</div><br />' . "\n";
            $formoutput[$i] = '<div class="fieldblock radioblock' . $warnblock["el_" . $i] . '">' . $fo . '<br/></div>';
            break;
        //  Ende Radio-Buttons
        case "hidden":
        case "password":
        case "text":
        case "email":
        case "url":
        case "date":
        case "time":
        case "IBAN":
        case "BIC":
        case "subject":
            $req  = '';
            $freq = '';
            if (isset($element[2]) && $element[2] == 1) {
                $req  = $formreq;
                $freq = ' required';
            }
            // 14.08.2009: GET-VARIABLENABFRAGE von Tito übernommen, siehe http://forum.redaxo.de/ftopic11635-30.html
            if ($FORM[$formname]["el_" . $i] == '' && !$FORM[$formname][$formname . 'send'] && isset($element[3])) // " && isset($element[3])" eingefügt - ### MW ###
                {
                if (strchr($element[3], 'GET_')) {
                    $get        = explode('GET_', $element[3]);
                    $element[3] = rex_get($get[1]);
                }
                
                if ($element[3] == "session") {
                    $element[3] = $_SESSION["REX_VALUE[16]"];
                    unset($_SESSION["REX_VALUE[16]"]);
                }
                if ($element[3] == "today") {
                    $element[3] = $formdatum;
                }
                if ($element[3] == "now") {
                    $element[3] = $formzeit;
                }
                
                $FORM[$formname]["el_" . $i] = trim($element[3]);
            }
            if (isset($element[2]) && $element[2] == 1 && (trim($FORM[$formname]["el_" . $i]) == "" || trim($FORM[$formname]["el_" . $i]) == trim($element[3])) && $FORM[$formname][$formname . "send"] == 1) {
                $warning["el_" . $i]   = $style;
                $warnblock["el_" . $i] = $bstyle;
                $warning_set           = 1;
            }
            // ### Validierung falls Pflichtelement oder Inhalt da und Formular abgeschickt
            if (!isset($element[5]))
                $element[5] = ''; // Zeile eingefügt - ### MW ###
            if ((isset($element[2]) && $element[2] == 1) && (trim($FORM[$formname]["el_" . $i]) != "") && ($FORM[$formname][$formname . "send"] == 1) || (trim($element[5]) != "" && $FORM[$formname][$formname . "send"] == 1 && $element[2] != 1 && trim($FORM[$formname]["el_" . $i]) != "")) {
                // checken, ob und welches Validierungsmodell gewaehlt
                if (trim($element[5]) != '') {
                    // falls Validierung gefordert
                    $valid_ok = TRUE;
                    $inhalt   = trim($FORM[$formname]["el_" . $i]);
                    switch (trim($element[5])) {
                        case "mail":
                            if (!preg_match("#^.+@(.+\.)+([a-zA-Z]{2,6})$#", $inhalt))
                                $valid_ok = FALSE;
                            break;
                        case "sender":
                        case "sendercheck":
                        case "absendermail":
                            if (!preg_match("#^.+@(.+\.)+([a-zA-Z]{2,6})$#", $inhalt)) {
                                $valid_ok = FALSE;
                            } else {
                                $absendermail = $inhalt;
                                // Neu: 14.01.2014 - Sender als Checkfield
                                if ($element[5] == "sendercheck") {
                                    $_SESSION["formcheck"] = $inhalt;
                                }
                            }
                            break;
                        case "tel":
                        case "telefon":
                            if (preg_match("#^[ \(\)\+0-9\/-]{6,}+$#", $inhalt)) {
                                break;
                            } else {
                                $valid_ok = FALSE;
                            }
                            break;
                        case "plz":
                            if (preg_match("/^[0-9]{5}$/", $inhalt)) {
                                break;
                            } else {
                                $valid_ok = FALSE;
                            }
                            break;
                        case "plz4":
                            if (preg_match("/^[0-9]{4}$/", $inhalt)) {
                                break;
                            } else {
                                $valid_ok = FALSE;
                            }
                            break;
                        case "name":
                            if (preg_match("/^[^;,@%:_#+*'!\"§$\/()=?]+$/i", $inhalt)) {
                                break;
                            } else {
                                $valid_ok = FALSE;
                            }
                            break;
                        case "digit":
                            if (!ctype_digit($inhalt))
                                $valid_ok = FALSE;
                            break;
                        case "alpha":
                            if (!ctype_alpha($inhalt))
                                $valid_ok = FALSE;
                            break;
                        case "url":
                            $inhalt = trim($inhalt);
                            if (preg_match("#^(http|https|ftp)+(://www.)+([a-z0-9-_.]{2,}\.[a-z]{2,4})$#i", $inhalt)) {
                                break;
                            } else {
                                $valid_ok = FALSE;
                                break;
                            }
                            break;
                        case "iban":
                            if (($validiban = getValidIban($inhalt))) {
                                break;
                            } else {
                                $valid_ok = FALSE;
                                break;
                            }
                            break;
                        case "date":
                            if (strpos($inhalt, '.')) {
                                $values = explode('.', $inhalt);
                                $day    = $values[0];
                                $month  = $values[1];
                                $year   = $values[2];
                                if ($check = checkdate($month, $day, $year)) {
                                    break;
                                } else {
                                    $valid_ok = FALSE;
                                    break;
                                }
                            } else {
                                $valid_ok = FALSE;
                                break;
                            }
                            break;
                        case "time":
                            if (!(bool) preg_match('/^(?:2[0-3]||(([0-9]||0[0-9])||1[0-9])):[0-5][0-9]$/', trim($inhalt))) {
                                $valid_ok = FALSE;
                                break;
                            }
                            break;
                        case "bic":
                            if (preg_match("#^[a-zA-Z]{6}[a-zA-Z0-9]{2,5}$#", $inhalt)) {
                                break;
                            } else {
                                $valid_ok = FALSE;
                            }
                            break;
                        case "checkfield":
                            if (preg_match("/[\w\p{L}]/u", $inhalt)) {
                                $_SESSION["formcheck"] = $inhalt;
                                break;
                            } else {
                                $valid_ok = FALSE;
                            }
                            break;
                        // Captchaabfrage
                        case "check":
                        case "captcha":
                            if (isset($_SESSION['token'])) {
                                if ($_SESSION['token'] == $_POST['token']) {
                                    $formcaptcha = 'off';
                                    $valid_ok    = FALSE;
                                    $dfreload    = $frel;
                                    break;
                                }
                            }
                            if ($_SESSION["kcode"] == $inhalt) {
                                $valid_ok = TRUE;
                                break;
                            }
                            if ($_SESSION["formcheck"] == $inhalt) {
                                $valid_ok = TRUE;
                                break;
                            } else {
                                $formcaptcha = 'off';
                                $valid_ok    = FALSE;
                                break;
                            }
                    } // switch (trim($element[5]))
                    if (!$valid_ok) {
                        $warning["el_" . $i]   = $style;
                        $warnblock["el_" . $i] = $bstyle;
                        $warning_set           = 1;
                    }
                } // falls Validierung gefordert
            }
            $placeholder = '';
            // ### /Validierung
            if ($element[0] == "hidden") {
                $inptype = "hidden";
            }
            if ($element[0] == "BIC") {
                $placeholder = ' placeholder="Bitte BIC eingeben"';
                $inptype     = "text";
            }
            if ($element[0] == "IBAN") {
                $placeholder = ' placeholder="Bitte IBAN eingeben"';
                $inptype     = "text";
            }
            if ($element[0] == "date") {
                $placeholder = ' placeholder="tt.mm.jjjj"';
                if (is_old_android()) {
                    $inptype = "text";
                } else {
                    $inptype = "date";
                }
            }
            if ($element[0] == "time") {
                $placeholder = ' placeholder="hh:mm"';
                $inptype     = "time";
            }
            if ($element[0] == "text") {
                $inptype = "text";
            }
            if ($element[0] == "password") {
                $inptype = "password";
            }
            if ($element[0] == "email") {
                $placeholder = ' placeholder="name@domain.de"';
                $inptype     = "email";
            }
            if ($element[0] == "url") {
                $inptype = "url";
            }
            if ($formcaptcha == 'off') {
                if ($inptype == 'hidden') {
                    $formoutput[] = '
                <input type="' . $inptype . '" class="formtext ' . $element[0] . '" title="' . $element[1] . '" name="FORM[' . $formname . '][el_' . $i . ']" id="el_' . $i . '" value="" />';
                } else {
                    $formoutput[] = '
                   <div class="fieldblock ' . $warnblock["el_" . $i] . '"> <label ' . $warning["el_" . $i] . ' for="el_' . $i . '" >' . $element[1] . $req . '</label>
                    <input type="' . $inptype . '" class="formtext" title="' . $element[1] . '" name="FORM[' . $formname . '][el_' . $i . ']" id="el_' . $i . '" value="" ' . $freq . ' /><br /></div>
                    ';
                }
                $formcaptcha = 'on';
            } else {
                $formoutput[] = '
                 <div class="fieldblock ' . $warnblock["el_" . $i] . '">   <label ' . $warning["el_" . $i] . ' for="el_' . $i . '" >' . $element[1] . $req . '</label>
                    <input type="' . $inptype . '" ' . $placeholder . ' class="formtext f' . $element[0] . '" title="' . $element[1] . '" name="FORM[' . $formname . '][el_' . $i . ']" id="el_' . $i . '" value="' . htmlspecialchars(stripslashes($FORM[$formname]["el_" . $i])) . '" ' . $freq . ' /><br /> </div>
                    ';
            }
            break;
        case "textarea":
            $req                = '';
            $freq               = '';
            $fehlerImFormaufbau = doform_checkElements(2, $element, 'textarea');
            if (isset($element[2]) && $element[2] == 1) {
                $req  = $formreq;
                $freq = ' required';
            }
            if (isset($element[3]) && $FORM[$formname]["el_" . $i] == '' && !$FORM[$formname][$formname . "send"]) {
                $FORM[$formname]["el_" . $i] = $element[3];
            }
            if (isset($element[2]) && isset($element[3]) && $element[2] == 1 && (trim($FORM[$formname]["el_" . $i]) == "" || trim($FORM[$formname]["el_" . $i]) == trim($element[3])) && $FORM[$formname][$formname . "send"] == 1) {
                $warning["el_" . $i]   = $style;
                $warnblock["el_" . $i] = $bstyle;
                $warning_set           = 1;
            }
            $formoutput[] = $fehlerImFormaufbau . '
         <div class="fieldblock ' . $warnblock["el_" . $i] . '">  <label ' . $warning["el_" . $i] . ' for="el_' . $i . '" >' . $element[1] . $req . '</label>
           <textarea class="formtextfield" cols="40" rows="10" title="' . $element[1] . '" name="FORM[' . $formname . '][el_' . $i . ']" id="el_' . $i . '"' . $freq . ' >' . htmlspecialchars(stripslashes($FORM[$formname]["el_" . $i])) . '</textarea><br /></div>';
            break;
        case "select":
        case "subjectselect":
            $req                = '';
            $fehlerImFormaufbau = doform_checkElements(3, $element, 'select');
            if (isset($element[2]) && $element[2] == 1) {
                $req = $formreq;
            }
            $SEL = new select();
            $SEL->set_name("FORM[" . $formname . "][el_" . $i . "]");
            $SEL->set_id("el_" . $i);
            $SEL->set_size(1);
            $SEL->set_style(' class="formselect"');
            if ($FORM[$formname]["el_" . $i] == "" && !$FORM[$formname][$formname . "send"]) {
                $SEL->set_selected($element[3]);
            } else {
                $SEL->set_selected($FORM[$formname]["el_" . $i]);
            }
            foreach (explode(";", trim($element[4])) as $v) {
                $SEL->add_option($v, $v);
            }
            if (isset($element[2]) && $element[2] == 1 && trim($FORM[$formname]["el_" . $i]) == "" && $FORM[$formname][$formname . "send"] == 1) {
                $warning["el_" . $i]   = $style;
                $warnblock["el_" . $i] = $bstyle;
                $warning_set           = 1;
            }
            $formoutput[] = $fehlerImFormaufbau . '
             <div class="fieldblock ' . $warnblock["el_" . $i] . '"> <label ' . $warning["el_" . $i] . ' for="el_' . $i . '" >' . $element[1] . $req . '</label>
              ' . $SEL->out() . '<br /></div>';
            break;
        case "captchapic":
        case "spamschutz":
            //Session-Variable prüfen:
            if (!isset($_SESSION["kcode"])) {
                session_start();
                $_SESSION["kcode"] = ''; // "$_SESSION["kcode"];" durch "$_SESSION["kcode"] = '';" ersetzt - ### MW ###
            }
            if ($REX['REDAXO'] == 1) {
                $formoutput[] = 'im Backend wird das Captchabild nicht angezeigt';
            } else {
                $formoutput[] = '<div class="fieldblock ' . $warnblock["el_" . $i] . '"><img src="' . $captchasource . '" class="formcaptcha" alt="Security-Code" title="Security-Code" />' . $element[1] . '<br/><br/></div>';
            }
            break;
        case "dateselect":
        case "xdate":
            $req                                                         = ($element[2] == 1) ? $formreq : '';
            $AFE[$i . '_d']                                              = $element;
            $form_element_ids[md5(strtolower(trim($element[1])) . '_d')] = 'el_' . $i . '_d';
            $SEL                                                         = new select();
            $SEL->set_name("FORM[" . $formname . "][el_" . $i . "_d]");
            $SEL->set_id("el_" . $i . '_d');
            $SEL->set_size(1);
            $SEL->set_style(' class="date_day"');
            if ($FORM[$formname]["el_" . $i . '_d'] == "" && !$FORM[$formname][$formname . "send"]) {
                $SEL->set_selected($element[3]);
            } else {
                $SEL->set_selected($FORM[$formname]["el_" . $i . '_d']);
            }
            foreach (range(1, 31) as $v) {
                $v = sprintf('%02d', $v);
                $SEL->add_option($v, $v);
            }
            if ($element[2] == 1 && trim($FORM[$formname]["el_" . $i . '_d']) == "" && $FORM[$formname][$formname . "send"] == 1) {
                $warning["el_" . $i . '_d'] = $style;
                $warning_set                = 1;
            }
            if ($element[2] == 1 && trim($FORM[$formname]["el_" . $i . '_m']) == "" && $FORM[$formname][$formname . "send"] == 1) {
                $warning["el_" . $i . '_d'] = $style;
                $warning_set                = 1;
            }
            if ($element[2] == 1 && trim($FORM[$formname]["el_" . $i . '_y']) == "" && $FORM[$formname][$formname . "send"] == 1) {
                $warning["el_" . $i . '_d'] = $style;
                $warning_set                = 1;
            }
            $formoutput[]                                                = '
              <div class="fieldblock"><label ' . $warning["el_" . $i . '_d'] . ' for="el_' . $i . '_d" >' . $element[1] . $req . '</label>
              ' . $SEL->out();
            $AFE[$i . '_m']                                              = $element;
            $form_element_ids[md5(strtolower(trim($element[1])) . '_m')] = 'el_' . $i . '_m';
            $SEL                                                         = new select();
            $SEL->set_name("FORM[" . $formname . "][el_" . $i . "_m]");
            $SEL->set_id("el_" . $i . '_m');
            $SEL->set_size(1);
            $SEL->set_style(' class="date_month"');
            if ($FORM[$formname]["el_" . $i . '_m'] == "" && !$FORM[$formname][$formname . "send"]) {
                $SEL->set_selected($element[3]);
            } else {
                $SEL->set_selected($FORM[$formname]["el_" . $i . '_m']);
            }
            foreach (range(1, 12) as $v) {
                $v = sprintf('%02d', $v);
                $SEL->add_option($v, $v);
            }
            $formoutput[]                                                = '
              ' . $SEL->out();
            $AFE[$i . '_y']                                              = $element;
            $form_element_ids[md5(strtolower(trim($element[1])) . '_y')] = 'el_' . $i . '_y';
            $SEL                                                         = new select();
            $SEL->set_name("FORM[" . $formname . "][el_" . $i . "_y]");
            $SEL->set_id("el_" . $i . '_y');
            $SEL->set_size(1);
            $SEL->set_style(' class="date_year"');
            if ($FORM[$formname]["el_" . $i . '_y'] == "" && !$FORM[$formname][$formname . "send"]) {
                $SEL->set_selected($element[3]);
            } else {
                $SEL->set_selected($FORM[$formname]["el_" . $i . '_y']);
            }
            if ($element[0] == "date") {
                $year = intval(date('Y'));
                if ($element[4] == "") {
                    $iival = "5";
                } else {
                    $iival = $element[4];
                }
                for ($v = $year; $v < ($year + $iival); $v++) {
                    $SEL->add_option($v, $v);
                }
            } else {
                $year = $element[3];
                if ($year == "") {
                    $year = "1910";
                }
                $today = intval(date('Y'));
                for ($v = $year; $v < ($today); $v++) {
                    $SEL->add_option($v, $v);
                }
            }
            $formoutput[] = '
              ' . $SEL->out() . '<br /></div>';
            break;
        case "timeselect":
            $req                                                         = ($element[2] == 1) ? $formreq : '';
            // STUNDEN
            $AFE[$i . '_h']                                              = $element;
            $form_element_ids[md5(strtolower(trim($element[1])) . '_h')] = 'el_' . $i . '_h';
            $SEL                                                         = new select();
            $SEL->set_name("FORM[" . $formname . "][el_" . $i . "_h]");
            $SEL->set_id("el_" . $i . '_h');
            $SEL->set_size(1);
            $SEL->set_style(' class="time_hours"');
            if ($FORM[$formname]["el_" . $i . '_h'] == "" && !$FORM[$formname][$formname . "send"]) {
                $SEL->set_selected($element[3]);
            } else {
                $SEL->set_selected($FORM[$formname]["el_" . $i . '_h']);
            }
            $SEL->add_option('', '');
            foreach (range(0, 23) as $v) {
                $v = sprintf('%02d', $v);
                $SEL->add_option($v, $v);
            }
            if ($element[2] == 1 && trim($FORM[$formname]["el_" . $i . '_h']) == "" && $FORM[$formname][$formname . "send"] == 1) {
                $warning["el_" . $i . '_h'] = $style;
                $warning_set                = 1;
            }
            if ($element[2] == 1 && trim($FORM[$formname]["el_" . $i . '_n']) == "" && $FORM[$formname][$formname . "send"] == 1) {
                $warning["el_" . $i . '_h'] = $style;
                $warning_set                = 1;
            }
            $formoutput[]                                                = '
              <label ' . $warning["el_" . $i . '_h'] . ' for="el_' . $i . '_h" >' . $element[1] . $req . '</label>
              ' . $SEL->out();
            // MINUTEN
            $AFE[$i . '_n']                                              = $element;
            $form_element_ids[md5(strtolower(trim($element[1])) . '_n')] = 'el_' . $i . '_n';
            $SEL                                                         = new select();
            $SEL->set_name("FORM[" . $formname . "][el_" . $i . "_n]");
            $SEL->set_id("el_" . $i . '_n');
            $SEL->set_size(1);
            $SEL->set_style(' class="time_nutes"');
            if ($FORM[$formname]["el_" . $i . '_n'] == "" && !$FORM[$formname][$formname . "send"]) {
                $SEL->set_selected($element[3]);
            } else {
                $SEL->set_selected($FORM[$formname]["el_" . $i . '_n']);
            }
            $SEL->add_option('', '');
            foreach (range(0, 59, 15) as $v) {
                $v = sprintf('%02d', $v);
                $SEL->add_option($v, $v);
            }
            $formoutput[] = '
              ' . $SEL->out() . '<br />';
            break;
        // ENDE DATUMSABFRAGE
        // Upload
        case "upload":
            $fehlerImFormaufbau         = doform_checkElements(5, $element, 'Upload');
            $req                        = '';
            $error_message              = '';
            // wird true, wenn keine Datei uebergeben wurde
            $upload_keineDateivorhanden = false;
            if (isset($element[2]) && $element[2] == 1) {
                $req = $formreq;
            }
            if (isset($element[6]) && trim($element[6]) != '') {
                $upload_MaxSice = trim($element[6]);
            } else {
                $upload_MaxSice = 0;
            }
            if (!empty($_FILES)) {
                if ($_FILES['FORM']['error'][$formname]['el_' . $i] === UPLOAD_ERR_OK) {
                    // upload ok
                } elseif ($req == '' && $_FILES['FORM']['error'][$formname]['el_' . $i] === UPLOAD_ERR_NO_FILE) {
                    // upload ok aber keine Datei vorhanden
                    $upload_keineDateivorhanden = true;
                } else {
                    $error_message .= file_upload_error_message($_FILES['FORM']['error'][$formname]['el_' . $i]);
                    $warning["el_" . $i]   = $style;
                    $warnblock["el_" . $i] = $bstyle;
                    $warning_set           = 1;
                }
                // alexplus: http://forum.redaxo.de/ftopic11635-150.html          
                if (!$upload_keineDateivorhanden && $error_message == '') {
                    $targetPath     = "REX_VALUE[14]";
                    $tempFile       = $_FILES['FORM']['tmp_name'][$formname]['el_' . $i];
                    $preTarget      = time() . "_" . $_FILES['FORM']['name'][$formname]['el_' . $i];
                    // Leerzeichen ersetzen durch _
                    $targetFile     = str_replace(" ", "_", $preTarget);
                    $targetPathFile = str_replace('//', '/', $targetPath) . $targetFile;
                    // Multimail
                    $cupload++;
                    $domailfile[$cupload]           = $targetFile;
                    $upload_Extensions              = array();
                    $upload_Extensions_errormessage = '';
                    $zaehler_element                = count(explode(";", trim($element[4])));
                    $zaehler_element_z              = 0;
                    foreach (explode(";", trim($element[4])) as $v) {
                        if ($v != '') {
                            $upload_Extensions[] = $v;
                            $upload_Extensions_errormessage .= '.' . $v;
                        }
                        $zaehler_element_z++;
                        if ($zaehler_element_z < $zaehler_element) {
                            $upload_Extensions_errormessage .= ' | ';
                        }
                    }
                    $fileParts = pathinfo($_FILES['FORM']['name'][$formname]['el_' . $i]);
                    if (isset($fileParts['extension']) and $fileParts['extension'] != '' and in_array($fileParts['extension'], $upload_Extensions)) {
                        $upload_File[$targetPathFile] = $tempFile;
                        $FORM[$formname]['el_' . $i]  = ($uploadpfad_mit_mailschicken) ? $targetPathFile : $targetFile;
                    } else {
                        // Warnung ueber nicht erlaubte Datei ausgeben
                        $warning["el_" . $i]   = $style;
                        $warnblock["el_" . $i] = $bstyle;
                        $warning_set           = 1;
                        $error_message .= '<div class="forminfo">Die Datei kann nicht hochgeladen werden. Evtl. liegt es an einem falschen Dateityp. Erlaubt ist hier nur: ' . $upload_Extensions_errormessage . '</div>';
                    }
                    if ($_FILES['FORM']['size'][$formname]['el_' . $i] < convertBytes($upload_MaxSice)) {
                        // alles ok
                    } else {
                        // Warnung ueber zu grosse Datei ausgeben
                        $warning["el_" . $i]   = $style;
                        $warnblock["el_" . $i] = $bstyle;
                        $warning_set           = 1;
                        $error_message .= 'Die Datei "' . htmlspecialchars($targetFile) . '" ist zu gro&#223;!<br />';
                        $error_message .= 'Erlaubt sind maximal ' . convertBytes($upload_MaxSice) / 1048576 . ' MB';
                    }
                } // if (!$upload_keineDateivorhanden && $error_message == '')
            } // if (!empty($_FILES))
            if (isset($error_message) and $error_message != '') {
                $error_message = '<p>' . $error_message . '</p>';
            } else {
                $error_message = '';
            }
            $form_tmp = '';
            $form_tmp .= $fehlerImFormaufbau;
            $form_tmp .= $error_message;
            $form_tmp .= "\n" . '<div class="fieldblock ' . $warnblock["el_" . $i] . '"><label ' . $warning["el_" . $i] . ' for="FORM[' . $formname . '][el_' . $i . ']" >' . $element[1] . $req . '</label>' . "\n";
            $form_tmp .= '<input type="file" name="FORM[' . $formname . '][el_' . $i . ']" id="FORM[' . $formname . '][el_' . $i . ']" /><br/></div>' . "\n";
            $formoutput[] = $form_tmp;
            $form_enctype = 'enctype="multipart/form-data"';
            break;
    }
}
// BEGIN :: Uploadverarbeitung
$uploadpfad = "REX_VALUE[14]";
// pruefe Pfad auf Vorhandensein und Schreibrechte, Wenn Pfad nicht vorhanden, ignoriere die weitere Verarbeitung.
if (isset($uploadpfad) and $uploadpfad != '' and $REX['REDAXO']) {
    // ... dum die dum ... Pfadpruefung erfolgt hier ...beginnt der Uploadpfad nicht mit einem Slash, muss es sich um einen lokalen Ordner handeln der vom Backend aus erweitert werden muss
    if (substr($uploadpfad, 0, 1) != '/') {
        $uploadpfad_tmp = '../' . $uploadpfad;
    } else {
        $uploadpfad_tmp = $uploadpfad;
    }
    if (rex_is_writable($uploadpfad_tmp) !== true) {
        echo rex_warning('Der Uploadpfad "' . $uploadpfad_tmp . '" ist nicht beschreibbar.<br />
                      Pruefe die Schreibrechte oder lasse die Angaben zum Uploadordner leer, wenn kein Uploadfeld genutzt wird.');
    }
}
// =================AUSGABE-KOPF============================
$out = '
   
   <form class="formgen doajax" id="' . $formname . '" action="' . rex_getUrl(REX_ARTICLE_ID) . '" accept-charset="UTF-8" method="post" ' . $form_enctype . '>
      <div><input type="hidden" name="FORM[' . $formname . '][' . $formname . 'send]" value="1" /><input type="hidden" name="ctype" value="ctype" /></div>
      <input type="hidden" name="token" value="' . $token . '" />';
// =================Formular-generieren=====================
foreach ($formoutput as $v) {
    $out .= $v;
}
// =================AUSGABE-FUSS============================
$out .= '
 
 
      <div class="formblock">
         <input type="submit" name="FORM[' . $formname . '][' . $formname . 'submit]" value="' . $submitlabel . '" class="formsubmit" />
      </div>
      </form>
   ';
// =================SEND MAIL===============================
if (isset($FORM[$formname][$formname . 'send']) && $FORM[$formname][$formname . 'send'] == 1 && !$warning_set) {
    // BEGIN :: Uploadverarbeitung pruefe Pfad auf Vorhandensein und Schreibrechte
    // Wenn Pfad nicht vorhanden, ignoriere die weitere Verarbeitung.
    if (isset($uploadpfad) and $uploadpfad != '' and count($upload_File) > 0) {
        // ... dum die dum ... Pfadpruefung erfolgt hier ...
        foreach ($upload_File as $targetFile => $tempFile) {
            move_uploaded_file($tempFile, $targetFile);
        }
    } // if (isset ($uploadpfad) and $uploadpfad != '')
    // END :: Uploadverarbeitung
    $_SESSION['token'] = $_POST['token'];
    unset($_SESSION["kcode"]); //Captcha-Variable zurücksetzen
    // Selbsdefinierte Sessionvariable zurücksetzen 
    if ("REX_VALUE[16]" != "") {
        unset($_SESSION["REX_VALUE[16]"]);
    }
    // E-Mail
    $mail = new rex_mailer(); // Mailer initialisieren
    $mail->AddAddress("REX_VALUE[1]"); // Empfänger
    if ($frommode == true) {
        $mail->Sender   = "REX_VALUE[1]"; //Absenderadresse als Return-Path
        $mail->From     = "REX_VALUE[1]"; //Absenderadresse 
        $mail->FromName = "REX_VALUE[1]"; // Abdendername entspricht Empfängeradresse 
    }
    if ($absendermail != '') {
        $mail->AddReplyTo($absendermail); // Antwort an Absender per Reply-To -  Besucher
    }
    if ($formbcc != '') {
        $mail->AddBCC($formbcc);
    }
    // E-Mail-Content
    foreach ($FORM[$formname] as $k => $v) {
        $matches = array();
        if (preg_match('~el_[0-9]+_(d|m|y|h|n)~', $k, $matches)) {
            switch ($matches[1]) {
                case 'd': // TAG
                    $mailbodyhtml .= '<span class="slabel">' . $fcounter . '. ' . $AFE[preg_replace("#el_#", "", $k)][1] . ": </span>" . stripslashes($v);
                    $mailbody .= $xcounter . '. ' . $AFE[preg_replace("#el_#", "", $k)][1] . ":" . stripslashes($v);
                    $rmailbodyhtml .= $mailbodyhtml;
                    $rmailbody .= $mailbody;
                    $fcounter++;
                    $xcounter++;
                    break;
                case 'm': // MONAT
                    $mailbodyhtml .= '.' . stripslashes($v);
                    $mailbody .= '.' . stripslashes($v);
                    $rmailbodyhtml .= $mailbodyhtml;
                    $rmailbody .= $mailbody;
                    break;
                case 'y': // JAHR
                    $mailbodyhtml .= '.' . stripslashes($v) . '<br />';
                    $mailbody .= '.' . stripslashes($v) . "\n";
                    $rmailbodyhtml .= $mailbodyhtml;
                    $rmailbody .= $mailbody;
                    break;
                case 'h': // STUNDEN
                    $mailbodyhtml .= '<span class="slabel">' . $fcounter . '. ' . $AFE[preg_replace('#_.*#', '', preg_replace("#el_#", "", $k))][1] . ": </span>" . stripslashes($v);
                    $mailbody .= $xcounter . '. ' . $AFE[preg_replace("#el_#", "", $k)][1] . ":" . stripslashes($v);
                    $rmailbodyhtml .= $mailbodyhtml;
                    $rmailbody .= $mailbody;
                    break;
                case 'n': // MINUTEN
                    $mailbodyhtml .= ':' . stripslashes($v) . '<br />';
                    $mailbody .= ':' . stripslashes($v) . "\n";
                    $rmailbodyhtml .= $mailbodyhtml;
                    $rmailbody .= $mailbody;
                    $fcounter++;
                    $xcounter++;
                    break;
            }
        } else {
            // HTML-AUSGABE und Plaintext erstellen
            $key = preg_replace('#el_#', '', $k);
            if ($k != $formname . 'submit' && $k != $formname . 'send' && (!isset($AFE[$key][5]) || $AFE[$key][5] != 'captcha') && stripslashes($v) != '' && isset($AFE[$key][1]) && !in_array($AFE[$key][0], $ignore)) {
                $v  = strip_tags($v);
                $v  = stripslashes($v);
                $v2 = substr($v, 0, -5) . 'XXXXX';
                switch ($AFE[$key][0]) {
                    case "subjectselect":
                        $sselect = $v . ' - ';
                        break;
                    case "BIC":
                        $mailbodyhtml .= '<span class="slabel">' . $fcounter . '. ' . $AFE[$key][1] . ": </span>" . strtoupper($v) . '<br />';
                        $mailbody .= $xcounter . '. ' . $AFE[$key][1] . ": " . strtoupper($v) . "\n";
                        $rmailbodyhtml .= '<span class="slabel">' . $fcounter . '. ' . $AFE[$key][1] . ": </span>" . strtoupper($v) . '<br />';
                        $rmailbody .= $xcounter . '. ' . $AFE[$key][1] . ": " . strtoupper($v) . "\n";
                        $fcounter++;
                        $xcounter++;
                        break;
                    case "IBAN":
                        $rmailbodyhtml .= $ibaninfo . '<span class="slabel">' . $fcounter . '. ' . $AFE[$key][1] . ": </span>" . strtoupper($v2) . '<br />';
                        $rmailbody .= $ibaninfo2 . $xcounter . '. ' . $AFE[$key][1] . ": " . strtoupper($v2) . "\n";
                        $mailbodyhtml .= '<span class="slabel">' . $fcounter . '. ' . $AFE[$key][1] . ": </span>" . strtoupper($v) . '<br />';
                        $mailbody .= $xcounter . '. ' . $AFE[$key][1] . ": " . strtoupper($v) . "\n";
                        $fcounter++;
                        $xcounter++;
                        break;
                    case "fieldstart":
                        $mailbodyhtml .= '<h1>' . $v . '</h1>';
                        $mailbody .= "\n" . '****' . $v . "\n" . '---------------------------------------------------------' . "\n";
                        $rmailbodyhtml .= '<h1>' . $v . '</h1>';
                        $rmailbody .= "\n" . '****' . $v . "\n" . '---------------------------------------------------------' . "\n";
                        break;
                    case "headline":
                        $mailbodyhtml .= '<h2>' . $v . '</h2>';
                        $mailbody .= "\n" . '---' . $v . "\n" . '---------------------------------------------------------' . "\n";
                        $rmailbodyhtml .= '<h2>' . $v . '</h2>';
                        $rmailbody .= "\n" . '---' . $v . "\n" . '---------------------------------------------------------' . "\n";
                        break;
                    case "subject":
                        $mailbodyhtml .= '<span class="slabel">' . $fcounter . '. ' . $AFE[$key][1] . ": </span>" . stripslashes($v) . '<br />';
                        $mailbody .= $xcounter . '. ' . $AFE[$key][1] . ": " . stripslashes($v) . "\n";
                        $subject = "Anfrage zu: " . stripslashes($v);
                        $fcounter++;
                        $xcounter++;
                        break;
                    
                    
                    default:
                        $mailbodyhtml .= '<span class="slabel">' . $fcounter . '. ' . $AFE[$key][1] . ": </span>" . $v . '<br />';
                        $mailbody .= $xcounter . '. ' . $AFE[$key][1] . ": " . $v . "\n";
                        $rmailbodyhtml .= '<span class="slabel">' . $fcounter . '. ' . $AFE[$key][1] . ": </span>" . $v . '<br />';
                        $rmailbody .= $xcounter . '. ' . $AFE[$key][1] . ": " . $v . "\n";
                        $fcounter++;
                        $xcounter++;
                }
            }
        }
    }
    if ($subject != "") {
        $mail->Subject = $subject; // Betreff
    } else {
        $mail->Subject = $mail->Subject = $sselect . "REX_VALUE[4]"; // Betreff 
    }
    $mail->CharSet = 'UTF-8'; // Zeichensatz    
    // HTML-EMAIL JA /NEIN
    if ("REX_VALUE[12]" == 'ja') {
        $mail->IsHTML(true);
        $mail->Body    = $doformhtml . nl2br($mailbodyhtml) . $doformhtmlfooter;
        $mail->AltBody = $mailbody . $nonhtmlfooter;
    } else {
        $mail->Body = $mailbody . $nonhtmlfooter;
    }
    // Dateianhänge versenden
    if (is_array($domailfile) and "REX_VALUE[15]" == "Ja" and $cupload > "0") {
        foreach ($domailfile as $dfile) {
            $mail->AddAttachment("REX_VALUE[14]" . $dfile);
        }
    }
    if (!function_exists('doppelversand')) {
        function doppelversand()
        {
        }
        $mail->Send(); // Versenden an Empfänger
    }
    // =================MAIL-RESPONDER============================
    $responder = "REX_VALUE[10]";
    if (isset($FORM[$formname][$formname . 'send']) && $FORM[$formname][$formname . 'send'] == 1 && $responder == 'ok' && !$warning_set && isset($absendermail)) {
        $mail = new rex_mailer();
        $mail->AddAddress($absendermail);
        $mail->Sender   = "REX_VALUE[2]";
        $mail->From     = "REX_VALUE[2]";
        $mail->FromName = htmlspecialchars_decode("REX_VALUE[8]");
        $mail->Subject  = htmlspecialchars_decode("REX_VALUE[17]");
        $mail->CharSet  = 'UTF-8';
        //### Datei (z.B. AGB) versenden ####
        if ("REX_FILE[1]" != '') {
            $mail->AddAttachment($redaxofile);
        }
        if ($sendfullmail != 'ja') {
            $mail->Body = $responsemail . $nonhtmlfooter;
        } else {
            if ("REX_VALUE[12]" == 'ja') {
                $mail->IsHTML(true);
                $mail->Body    = $doformhtml . nl2br($responsemail) . '<hr/>' . nl2br($rmailbodyhtml) . $doformhtmlfooter;
                $mail->AltBody = $mailbody . $nonhtmlfooter;
            } else {
                $mail->Body = $responsemail . "\n-----------------------------------------------\n" . $rmailbody . $nonhtmlfooter;
            }
        }
        /*
        Doppelversand verhindern
        */
        if (!function_exists('doppelversand2')) {
            function doppelversand2()
            {
            }
            $mail->Send(); // Versenden an Absender
        }
    }
    // =================MAIL-RESPONDER-ENDE=========================
    unset($_SESSION["formcheck"]); //
?>
<div class="formthanks">REX_HTML_VALUE[6]</div>
 
<?php
    $noform = 1;
} else {
    $noform = 0;
}
if ($warning_set) {
    echo '<div class="kblock forminfo">';
    echo ($fError . $dfreload);
    echo '</div>';
    print $out;
} else {
    if ($noform != 1) {
        print $out;
    }
}
?>
