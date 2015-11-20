<?php
/**
 *=============================================
 * REDAXO-Modul: do form!
 * Bereich: Eingabe 
 */
$doformversion="6.0";
 /**
 * ab Redaxo Version: 5
 * Werbeagentur KLXM Crossmedia  
 * www.klxm.de
 * Hinweise:
 * Formulargenerator für PHPMAILER
 * Required Addons: TinyMCE oder ckeditor, PHPMAiler
 * Ursprung: Formular-Generator Redaxo 3.2 Demo, do form! 2
 * Typ: Modifikation / Erweiterung  
 *=============================================
*/
// EINGABE EINSTELLUNGEN
// zur Vereinfachung der Eingabemaske
// Erweiterte Funktionen in der Moduleingabe freischalten 
// Es sind evtl. Anpassungen im ausgabe-Code erforderlich
 
$uploadon=true;  // UPLOADS AKTIVIEREN true oder false, beachte: Ausgabe $form_upload_folder
$sessionson=false;  // SESSIONS AKTIVIEREN true oder false
$bccon=true;  // BCC-Feld AKTIVIEREN true oder false
$sslon=true; // SSL-Unterstützung aktivieren
$weditor='rex_redactor'; // Welches WYSIWYG-addon soll verwendet werden? z.B.: redaktor ckeditor oder tinymce 
$editstyle='redactorEditor-full'; // Lege die CSS-Klasse für den WYSIWYG-Editor fest (z.B. ckeditor oder tinyMCEEditor) 

 
// Definition des Standard-Formulars 
$defaultdata="
text|Vorname|1|||name
text|Nachname|1|||name
text|Firma |
text|Straße|
text|PLZ|1|||plz
text|Ort|1|||
text|Telefon||||tel
text|Telefax||||tel
email|E-Mail|1|||sender
textarea|Ihre Nachricht: |1|
";
 
 
 
/**
 * Convert a shorthand byte value from a PHP configuration directive to an integer value
 * @param    string   $value
 * @return   int
 */
if (!function_exists('convertBytes')) {
function convertBytes( $value ) {
    if ( is_numeric( $value ) ) {
        return $value;
    } else {
      $value = trim ($value);
      $value_length = strlen( $value );
      $qty = substr( $value, 0, $value_length - 1 );
      $unit = strtolower( substr( $value, $value_length - 1 ) );
      switch ( $unit ) {
          case 'g':
              $qty *= 1024;
          case 'm':
              $qty *= 1024;
          case 'k':
              $qty *= 1024;
      }
      return $qty;
    }
}
}
?>
 
<style type="text/css">
<!--
.formgenheadline {
	color: #000000;
	display: block;
	padding-left: 10px;
	padding-top: 2px;
	padding-right: 2px;
	padding-bottom: 2px;
	font-weight: 300;
	border-top-width: 1px;
	border-right-width: 1px;
	border-bottom-width: 3px;
	border-left-width: 1px;
	border-top-style: none;
	border-right-style: none;
	border-bottom-style: none;
	border-left-style: none;
	font-style: normal;
	background-color: #FFFFFF;
	background-position: bottom;
	font-size: 1.5em;
}
.doform {
	background-color: #FFFFFF;
	padding-left: 1.2em;
	padding-bottom: 1.2em;
	font-family: Tahoma, Geneva, sans-serif;
}
.doleft {
	float: left;
	background-color: #FFF;
	margin-right: 1.2em;
	margin-top: 1.2em;
	padding: 0.5em;
	border: 1px solid #999;
}
.doform  .inp100 {
	background-color: #333333;
	border: 1px solid #CCC;
	width: 90%;
	color: rgba(255,255,255,1);
}
 
.formbg {
        background-color:#E0E2E8;
}
 
.formgenerror {
  color: #FFFFFF;
  background-color: #990000;
  border: 6px dashed #FFCC00;
  margin: 5px;
  padding: 5px;
}
.formgen_manual {
  color: #333333;
  font-size: 1.2em;
  background-color: #eeeeee;
}
.formgenconfig {
	background-color: #FFF;
	font-family: "Courier New", Courier, monospace;
	color: #006;
	font-size: 1.2em;
	width: 95%;
	margin-right: 2em;
	height: 250px;
	border: 1px solid #3C9ED0;
	overflow: auto;
}
.formgen_sample {
        background-color: #FFF;
        font-family: "Courier New", Courier, monospace;
        color: #333333;
        font-size: 1.2em;
        width: 95%;
        border: 1px solid #999;
}
.formgenalias {
	color: #CCCCCC;
	font-size: 0.9em;
}
#formgenblock {
  width: 540px;
  padding: 10px;
}
.infotext {
	color: #999999;
	font-style: italic;
	font-size: 0.9em;
}
.formgentitle {
        color: #6E97C1;
        background-color: #F1F1F1;
        display: block;
        padding-left: 10px;
        font-family: Geneva, Arial, Helvetica, sans-serif;
        padding-top: 2px;
        padding-right: 2px;
        padding-bottom: 2px;
        font-weight: bolder;
        border-top-width: 1px;
        border-right-width: 1px;
        border-bottom-width: 3px;
        border-left-width: 1px;
        border-top-style: solid;
        border-right-style: solid;
        border-bottom-style: solid;
        border-left-style: solid;
        border-top-color: #CCCCCC;
        border-right-color: #333333;
        border-bottom-color: #999;
        border-left-color: #666666;
        font-style: italic;
        font-size: 20px;
        margin-bottom: 6px;
}
.infotext2 {
        color: #37D749;
        font-weight: bold;
        font-family: Arial, Helvetica, sans-serif;
}
 
.myDivs .formgenheadline {
	background-color: #3C9ED0;
	color: #FFF;
}
.formnavi {
	font-family: Geneva, Arial, Helvetica, sans-serif;
	font-size: 1.2em;
	padding-top: 4px;
	padding-right: 10px;
	padding-bottom: 15px;
	padding-left: 10px;
}
.doleftdoc {
        float: left;
        width: 120px;
        background-color: #FFF;
        margin-right: 1.2em;
        margin-top: 1.2em;
        padding: 0.5em;
        border: 1px solid #999;
}
.doleftdoc2 {
        float: left;
        width: 420px;
        background-color: #FFF;
        margin-right: 1.2em;
        margin-top: 1.2em;
        padding: 0.5em;
        border: 1px solid #999;
}
 
 
-->
</style>
 
<script language="JavaScript" type="text/javascript"> 
<!-- 
function doIt(theValue) 
{ 
    var divs=document.getElementsByTagName("DIV"); 
    for (var i=0;i<divs.length;i++) 
    { 
        if (divs[i].className=="myDivs") 
        { 
        divs[i].style.display=(( theValue=="every" || divs[i].id==theValue)?"block":"none"); 
        }; 
    } 
} 
//--> 
</script>
 
 
<div class="col-md-12 formnavi"><a href="https://github.com/skerbis/do-form-5/wiki" target="_blank"><i class="fa fa-question-circle"></i> WIKI</a><a href="#anleitung" id="anzeige" onclick="javascript:document.getElementById('anleitung').style.display = 'block'" > <i class="fa fa-question-circle"></i> Beispiel-einblenden </a> do form! - Version: <?php echo $doformversion; ?>&nbsp;</div>
<br/><?php #$phpmcheck= OOAddon::isActivated('phpmailer'); 
$phpmcheck= rex_addon::get(phpmailer)->isAvailable();


if ($phpmcheck == 1)
{}
else { echo' <div class="formgenerror"> PHPMailer wurde nicht gefunden oder ist nicht aktiviert. <br/> Bitte installieren Sie das ADDON! </div>'; }
?>


 <div class="form-horizontal">
        <div class="form-group">
            <div class="col-md-12"><h3><i class="fa fa-list-alt"></i> Formularfelder</h3></div>
            <div class="col-md-12">typ|label|pflicht|default|value/s|validierung<br>
                 <textarea name="REX_INPUT_VALUE[3]" rows="10" class="form-control"><?php if ("REX_VALUE[3]" == ''){echo $defaultdata;} else {echo "REX_VALUE[3]";}?></textarea>
            
            </div>
        </div>


</div>


<div class="form-horizontal">
        <div class="form-group">
            <div class="col-md-12"><h3><i class="fa fa-envelope-o"></i> Setup:</h3></div>
           
            <div class="col-md-9">
              
              <div class="col-md-4">E-Mail an:</div>    
                  <div class="col-md-6"> <input type="email" name="REX_INPUT_VALUE[1]" value="REX_VALUE[1]" class="form-control"  /></div>
<div class="col-md-2">(%Mail%)</div>
   <?php if ($bccon==true) { ?>            
 <div class="col-md-4">BCC:</div>    
                  <div class="col-md-8"><input type="text" class="form-control" name="REX_INPUT_VALUE[11]" value="REX_VALUE[11]"  /></div>

 <?php } ?>              
                 
             <div class="col-md-4">Betreff:</div>    
                  <div class="col-md-8"><input type="text" class="form-control" name="REX_INPUT_VALUE[4]" value="REX_VALUE[4]"  /></div>
          
             
              <div class="col-md-4">Sende-Button:</div>    
                  <div class="col-md-8"><input type="text" class="form-control" name="REX_INPUT_VALUE[7]" value="REX_VALUE[7]"  /></div>
   
             
              <div class="col-md-4">HTML-E-Mail:</div>    
                  <div class="col-md-8"><select  class="form-control" name="REX_INPUT_VALUE[12]">
  <option value='ja' <?php if ("REX_VALUE[12]" == 'ja') echo 'selected'; ?>>ja</option>
  <option value='nein' <?php if ("REX_VALUE[12]" == 'nein') echo 'selected'; ?>>nein</option >
</select></div>


<?php if ($sslon==true) { ?>  
 <div class="col-md-4">SSL:</div>    
                  <div class="col-md-8"><select class="form-control"  name="REX_INPUT_VALUE[18]">
  <option value='nein' <?php if ("REX_VALUE[18]" == 'nein') echo 'selected'; ?>>nein</option>
  <option value='SSL' <?php if ("REX_VALUE[18]" == 'SSL') echo 'selected'; ?>>Ja</option >
</select></div>
           

 <?php } ?>   
 
<div class="col-md-4">Bestätigung:</div>    
                  <div class="col-md-8">


<select class="form-control" name="REX_INPUT_VALUE[10]" id="mySelect" onChange="doIt(this.value)">
      <option value='Nein' <?php if ("REX_VALUE[10]" == 'nein') echo 'selected'; ?>>Nein</option>
      <option value='ok' <?php if ("REX_VALUE[10]" == 'ok') echo 'selected'; ?>>Ja</option>
    </select>
    <i>Nur wenn Validierung sender definiert ist</i>
<br>
</div>




<?php if ($uploadon==true) { ?>
<div class="col-md-4">Uploads als Anhang:<br><br></div>    
                  <div class="col-md-8"> 


<select class="form-control" name="REX_INPUT_VALUE[15]">
<option value='Nein' <?php if ("REX_VALUE[15]" == 'nein') echo 'selected'; ?>>Nein</option>
      <option value='Ja' <?php if ("REX_VALUE[15]" == 'Ja') echo 'selected'; ?>>Ja</option>
      </select>
<div class="col-md-12">      
    <?php echo 'Uploadgr&#246;&#223;e, max: ' . convertBytes( ini_get( 'upload_max_filesize' ) ) / 1048576 . 'MB';?>
    </div>
    </div>
 


 <?php } ?>



 













 

 
  </div>
 </div>
 </div>
 

   
             









 
<?php if ($sessionson==true) { ?>
<div class="col-md-12 formgenheadline">Individuelle Sessionvariable (expert)</div>
  <div class="col-md-12 doform">
    <div class="col-md-6 "><strong>Bezeichner für Sessionvariable:</strong><br/>
      <input type="text" name="REX_INPUT_VALUE[16]" value="REX_VALUE[16]" class="form-control" />
      <br />
    <span class="infotext">z.B.: Warenkorb,  nur für Session-Variablen erlaubte Zeichen, erntspricht: $_SESSION[&quot;Warenkorb&quot;]</span></div>
    <div class="col-md-6">
      <p><em><strong>Info</strong> Die Variable wird nach dem Versenden zurückgesetzt</em></p>
      <p>Beispiel: Einsatz per <strong>svar|Warenkorb</strong></p>
    </div>
    <div style="clear:both">Es handelt sich hierbei um ein hidden field. Eine Ausgabe muss ggf. selbst erstellt werden.</div>
  </div>
   <?php } ?>

  <br />
<div id="ok" <?php if ("REX_VALUE[10]" == 'ok'){ echo 'style="display:block;"'; } else echo 'style="display:none;"'; ?> class="myDivs">
  <div class="formgenheadline">Best&#228;tigungs-E-Mail an den Absender</div>
  <div class="col-md-12 doform">
    <div class="doleft col-md-6">
    <strong>Betreff </strong>f&uuml;r die Best&auml;tigungs-E-Mail:<br />
      <input type="text" name="REX_INPUT_VALUE[17]" value="REX_VALUE[17]" class="form-control" />
 
    <strong><br>
    Absenderadresse </strong>f&uuml;r die Best&auml;tigungs-E-Mail:<br />
      <input type="email" name="REX_INPUT_VALUE[2]" value="REX_VALUE[2]" class="form-control" />
      <span class="formgenalias">(%Absender%)</span><br/>
<strong>Absender-Name:</strong><br />
      <input type="text" name="REX_INPUT_VALUE[8]" value="REX_VALUE[8]" class="form-control" />
    </div>
    <div class="doleft col-md-6"><strong>Original-Mail anh&auml;ngen?<br />
<select  class="form-control" name="REX_INPUT_VALUE[13]">
          <option value='nein' <?php if ("REX_VALUE[13]" == 'nein') echo 'selected'; ?>>nein</option >
          <option value='ja' <?php if ("REX_VALUE[13]" == 'ja') echo 'selected'; ?>>ja</option>
      </select>
        <br/>
        <br/>
Datei anh&#228;ngen: </strong>REX_MEDIA[id=1 widget=1]</div>
    <div style="clear:both"></div>
  </div>
  <div class="formgenheadline">E-Mail-Best&#228;tigungstext</div>
  <div class="col-md-12 doform"><textarea name="REX_INPUT_VALUE[5]" class="formgenconfig" style="width:100%;height:80px;">REX_VALUE[5]</textarea>
    <span class="formgen_sample1"><strong>Platzhalter für Bestätigungstext:</strong> <br />
    %Betreff%, %Datum% , %Zeit%, %Absender%, %Mail%, %Vorname%, %Nachname% </span>, <br />
    %Besuchermail% (wird durch sender gesetzt)<br/>
  </div>
</div>
  <br/>
  
  <h2><strong>Bestätigung auf Website</strong></h2>
 <?php 

$tinycheck= rex_addon::get($weditor)->isAvailable();


  if ($tinycheck == 1) { 
 ?>
 
   <textarea id="redactor_REX_SLICE_ID"name="REX_INPUT_VALUE[6]" class="<?php echo $editstyle;?> form-control" style="width:555px; height:250px;">REX_VALUE[6]</textarea>
   
<?php  }  else {
    echo' <div class="formgenerror"> Editor wurde nicht gefunden. <br/> Bitte installieren Sie ein geeignetes ADDON! <br/>z.B: TinyMCE, redactor oder CKEDITOR </div>';
  } ?>
   
   
<br/>
  <div align="right">Bearbeitung: <a href="http://www.klxm.de" target="_blank">Thomas Skerbis - KLXM Crossmedia GmbH</a></div>
 
 
<div id="anleitung" style="<?php echo (!isset ($anleitung) || !$anleitung) ? 'display: none' : 'display: block'; ?>"> 
  <div class="formgenheadline">Beispiel-Formular:</div>
  <div class="col-md-12 doform">
    <textarea name="demo" cols="80" rows="11" class="formgenconfig" style="width:95%;height:200px;">
fieldstart|Kontaktdaten
text|Name|1|||checkfield    
text|Vorname|1|||name
text|Firma
text|Straße
text|PLZ|1|||plz
text|Ort|1
text|Telefon||||tel
text|Telefax||||tel
fieldend|
fieldstart|Weitere Angaben
divstart|cssklasse
radio|Geschlecht|0|Mann;Frau|m;w|
password|Ihr Passwort|1|||alpha
email|E-Mail|1|||sender
url|Website||||url
divend|
select|Auswahl|1||Birne;Apfel;Kirsche
checkbox|AGB gelesen?
fieldend|
info|Geben Sie bitte nochmal Ihren Namen ein
text|Sicherheitscode|1|||check
textarea|Ihre Nachricht:|1|
upload|Upload JPG|0||jpg;jpeg;gif||0.5m
</textarea>
    <br/>
    <br/>
  </div>
  <h3>Kurzbeschreibung:</h3>
  do form! basiert auf den in Redaxo 3.2 mitgelieferten Formular-Generator.<br />
   Beim ersten Aufruf erstellt das Modul eine Konfiguration für ein Standard-Kontaktformular. <br/>
     Im Beispiel-Formular sehen Sie Möglichkeiten zur Konfiguration. <br/>
<a href="https://github.com/skerbis/do-form-5" target="_blank">Eine ausf&uuml;hrliche Anleitung.</a><br>
<a href="http://klxm.de/produkte/redaxo-formulargenerator/" target="_blank">Download der neusten Version </a><br />
     <br />
<strong>Empfehlung:</strong><br />
     Wir empfehlen im PHP-Mailer die Einstellung SMTP-AUTH zu verwenden. 
  <br />
  <br />
<br/>
<br />
  <br />
  <br />
  <div class="col-md-12 doform"><strong>Validierung</strong> von Textfeldern </span>
    
      <ul>
        <li>alpha (nur engl.Buchstaben) </li>
        <li>url (URL)</li>
        <li>date</li>
        <li>time</li>
        <li>IBAN</li>
        <li>BIC</li>
        <li>digit (nur Zahlen)</li>
        <li>plz (5 Zahlen)</li>
        <li>plz4 (4 Zahlen)</li>
        <li>tel</li>
        <li>name prüft Namen und z.B. übliche Firmenbezeichnungen</li>
        <li>mail (pr&uuml;ft eingegebene E-Mail-Adressen) </li>
        <li>sender (diese Adresse wird als Absendermail eingesetzt und gepr&uuml;ft)</li>
        <li>check - Prüfen der Spamschutzeingabe (captchapic oder checkfield) <br/>
          entspricht sonst der Validierung: name</li>
        <li>checkfield (legt ein Vergleichsfeld fest das als Spamschutzcode gilt)</li>
      </ul>
      <p>&nbsp;</p>
   
    <div style="clear:both"></div>
  </div>
  <br />
<br />
</div>

