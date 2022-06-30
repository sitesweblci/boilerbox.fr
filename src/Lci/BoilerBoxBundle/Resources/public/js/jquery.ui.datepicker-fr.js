/* French initialisation for the jQuery UI date picker plugin. */
/* Written by Keith Wood (kbwood{at}iinet.com.au) and Stéphane Nahmani (sholby@sholby.net). */
		jQuery(function($)
		{
            $.datepicker.setDefaults({
                closeText: 'Fermer',
                currentText: 'Aujourd\'hui',
                changeMonth: true,
                changeYear: true,
                dayNames: ['Dimanche','Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi'],
                dayNamesShort: ['Dim','Lun','Mar','Mer','Jeu','Ven','Sam'],
                dayNamesMin: ['Di','Lu','Ma','Me','Je','Ve','Sa'],
                dateFormat: 'dd/mm/yy',
                firstDay: 1,
                gotoCurrent: true,
                isRTL: false,
                monthNames: ['Janvier','Fevrier','Mars','Avril','Mai','Juin', 'Juillet','Aout','Septembre','Octobre','Novembre','Decembre'],
                monthNamesShort: ['Jan','Fev','Mar','Avr','Mai','Juin', 'Jul','Aou','Sept','Oct','Nov','Dec'],
                minDate: new Date('2000/01/01'),
                maxDate: '+2Y',
                nextText: 'Suiv&#x3e;',
				numberOfMonths: 1,
				prevText: '&#x3c;Préc',
        		showMonthAfterYear: false,
        		showButtonPanel: true,
        		showWeek: false,
        		showAnim: "fadeIn",
        		//weekHeader: 'S',
        		yearSuffix: '',
				yearRange: '2000:c+1',
            });
			$.datepicker.setDefaults($.datepicker.regional['fr']);
		});
