redaxo do form! 5 rex5
- early alpha -
=================

Formulargenerator für Redaxo CMS
--------------------------------

Es handelt sich hierbei um eine kostenlose und frei verwendbare Version, entsprechend der Lizenz Ihrer Redaxo-Installation. Eine Garantie oder Gewährleistung auf Funktionalität und Fehlerfreiheit wird nicht geleistet. Die KLXM Crossmedia haftet nicht für eventuell auftretenden Datenverlust. Bei Problemen, wenden Sie sich bitte an das Redaxo-Forum. 
Javascripte und CSS-Definitionen sind nicht Bestandteil der classic Version.

**Über eine Erwähnung im Impressum würden wir uns sehr freuen.** 

z.B. Redaxo-Modul: do form!, Werbeagentur KLXM Crossmedia GmbH, http://klxm.de

Homepage: http://klxm.de/produkte/redaxo-formulargenerator/

Imressum: http://klxm.de/impressum/

WIKI: https://github.com/skerbis/do-form-5/wiki

FAQ: https://github.com/skerbis/do-form-5/wiki/FAQ



**Screenshot**

![](<screenshot_.png>)


### Version 5.1.1 rex5
Umbau zu REX5-Modul ...
Sorry, das dauert noch

### Version 5.1.1 classic
Letzte Version für Redaxo 4.x


### Version 5.1 
subject| 
Textfeld als Betreff
und Session kann nun als Wert in alle Textfelder übernommen werden. 
Verwendung: subject|Produkt|0|session||  oder text|Produkt|0|session||

### Version 5.0.4 
subjectselect| 
Selectfield zur Übergabe eines Betreffs. 

### Version 5 – 5.0.2 

Diese Version sollte nicht als Ersatz für do form! 4 eingesetzt werden.

-   Einige Felder und auch die Ausgaben unterscheiden sich.  Personalisierung
    erweitert, entsprechend Modul Nr. 653, zusätzlich mit Anrede-Erkennung für
    die Bestätigungsmail

-   Das Date- und Time-Feld wurde durch HTML5-Date/Time-Feld ersetzt. Dadurch können die nativen
    Kalender-Widgets verwendet werden. Fallback per Jquery-Datepicker
    empfehlenswert. Durch ddie Parameter today und now, kann das aktuelle Datum oder Uhrzeit
    vorausgewählt werden.

-   Das alte date-Feld heißt jetzt dateselect. xdate wurde entfernt.  
    IBAN und BIC können jetzt eingesetzt und validiert werden. In der
    Bestätigungs-E-Mail wird die IBAN anonymisiert.

Beispiele der neuen Felder:

`IBAN|Ihre IBAN|1|DE||iban `

`BIC|BIC|1|||bic `

`date|Datum der Meldung|1|today||date `

`time|Uhrzeit|1|now||time `

(Wird kein default-value eingegeben, werden entsprechende Placeholder in
modernen Browsern dargestellt)

Neue CSS-Klassen erleichtern die Gestaltung der Textfelder.

z.B.:

`.formgen .formtext.femail {} `

`.formgen .formtext.fIBAN {} `

`.formgen .formtext.fpassword {} `



Jedes Formularfeld befindet sich in einem DIV mit der Klasse .formfield. Bei
Fehlern kann man diese mit einer weiteren Klasse ergänzen.  Standard: .formerror
