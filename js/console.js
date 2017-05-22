/**
 * Created with JetBrains PhpStorm.
 * User: alexlake
 * Date: 10/05/17
 * Time: 23:14
 * To change this template use File | Settings | File Templates.
 */
/*

Maybe login could be added to this?

*/

var HCATS = HCATS || {};
HCATS.console = HCATS.console || {};
HCATS.console.wibble = function()
{
    var my = {
        myEventsList: [],
        otherEventsList: []
    }

    var module = {};

    module.init = function ()
    {
        this.loadFromServer();
        this.initView();
    };

    module.loadFromServer = function() {
        var params = {
                cmd:           'GetEvents'

        };
        $.getJSON(
           "console", // Not sure about this
           params,
           function(msg){
               my.console = msg;
               my.myEventsList = msg.myEventsList;
               HCATS.console.wibble.render();
           }
        );
    }

    module.initView = function() {
        // alert("eventView_init");
        $('a.button.console').attr( 'href', '/');


        // attach handlers
        $('.newEvent').bind( 'click', function() { HCATS.console.wibble.addEventStep1(); } );
        $('.createEvent').bind( 'click', function() { HCATS.console.wibble.addEventStep2(); } );

        $('#newEvent').hide();

        this.render();

    }

    module.addEventStep1 = function() {

        $('#newEvent').show(); // this is the form where the basic event params can be added
        $('.newEvent').hide();

    }

    module.addEventStep2 = function() {

        title=$('#newEvent').find("[name=title]")[0].value;
        description=$('#newEvent').find("[name=description]")[0].value;
        date=$('#newEvent').find("[name=date]")[0].value;


        var params = {
            cmd:         'CreateEvent',
            title:       title,
            description: description,
            date:        date

        };
        $.getJSON(
               "console",
               params,
               function(msg){
                       console.dir(msg);
               }
        );



        $('#newEvent').hide();

        // We need to render the event...

        this.render();

        // and then maybe zoom in on it?
        // do we want to do the event view like a dialog or a whole new page?

    }

    module.render = function() {
        // This will fill the variable stuff into their containers - and attach actions
        var guestIdx;
        $('#myEventContainer').html('');
        var $newRow = $('#myEventHeader').clone();
        $newRow.removeClass('template');
        $newRow.removeAttr('ID');
        $('#myEventContainer').append($newRow);

        for (eventIdx in my.myEventsList) {
            var event = my.myEventsList[eventIdx]
            console.dir(event);
            var eid = event.id;
            var title = event.title;
            var date = event.date;
            var eventURL = '/e_'+eid;

            var $newRow = $('#eventRow').clone();
            $newRow.removeClass('template');
            $newRow.removeAttr('ID');

            $newRow.find('td[name=title]').text(title);
            $newRow.find('td[name=date]').text(date);
            var before = $newRow.find('a.button.zoom').attr('href');
            $newRow.find('a.button.zoom').attr('href',eventURL);
            var after = $newRow.find('a.button.zoom').attr('href');

            $('#myEventsContainer').append($newRow);
        }
    }


    return module;



}();