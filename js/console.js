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
        console.log('console.init()');
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


        // attach handlers
        $('button.link').bind('click',buttonLink);

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

                   $('#newEvent').hide();
                   $('.newEvent').show();


                   HCATS.console.wibble.loadFromServer();

                   // We need to render the event...
                   // HCATS.console.wibble.render();
               }
        );




        // and then maybe zoom in on it?
        // do we want to do the event view like a dialog or a whole new page?

    }

    module.render = function() {
        // This will fill the variable stuff into their containers - and attach actions
        var guestIdx;
        $('#myEventsContainer').empty();
        var $newRow = $('#eventHeader').clone();
        $newRow.removeClass('template');
        $newRow.removeAttr('ID');
        $('#myEventsContainer').append($newRow);

        if (my.myEventsList.length==0) {
            var $newRow = $('#noEvents').clone();
            $newRow.removeClass('template');
            $newRow.removeAttr('ID');
            $('#myEventsContainer').append($newRow)
        }


        for (var eventIdx in my.myEventsList) {
            var event = my.myEventsList[eventIdx]
            console.log('adding '+eventIdx+' '+event.title);
            var eid = event.eid;
            var eventURL = '/e_'+eid;

            var $newRow = $('#eventRow').clone();
            $newRow.removeClass('template');
            $newRow.removeAttr('ID');

            $newRow.find('td[name=title]').text(event.title);
            $newRow.find('td[name=date]').text(event.date);
            $newRow.find('td[name=hostname]').text(event.hostName);
            $newRow.find('td[name=status]').text(event.status);
            $newRow.find('button.zoom').attr('href',eventURL);
            $newRow.find('button.zoom').bind('click',buttonLink);

            $('#myEventsContainer').append($newRow);
        }
    }


    return module;



}();