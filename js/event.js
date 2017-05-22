/**
 * Created with JetBrains PhpStorm.
 * User: alexlake
 * Date: 10/05/17
 * Time: 23:14
 * To change this template use File | Settings | File Templates.
 */
/*
$( document ).ready(function() {
    alert("Hello! I am an alert box!!");

});
*/

var HCATS = HCATS || {};
HCATS.event = HCATS.event || {};
HCATS.event.eventbuilder = function()
{
    var my = {
        eid : null,
        event : null,
        guestList: [],
        comments: []
    }

    var module = {};

    module.init = function (eid)
    {
        my.eid = eid;
        my.guests = [];
        my.comments = [];

        this.loadFromServer();
        this.initView();
    };

    module.inviteGuest = function(guestDetails) {
        // alert("inviting guest");
        my.guestList.push(guestDetails);
        this.saveToServer();
    };

    module.loadFromServer = function() {
        var params = {
                cmd:           'GetEventJSON'

        };
        $.getJSON(
           "e_"+my.eid,
           params,
           function(msg){
               my.event = msg;
               my.guestList = msg.guestList;
               my.comments = msg.comments;

               HCATS.event.eventbuilder.render();

           }
        );
    }

    module.saveToServer = function() {
        // This is where we do an AJAX hit to the server to save the latest event settings
        // and get updates - there could be a "merge"?
        var params = {
            cmd:        'UpdateEvent',
            guests:     my.guestList

        };
        $.getJSON(
               "e_"+my.eid,
               params,
               function(msg){
                       console.dir(msg);
               }
        );
    }

    module.initView = function() {
        // alert("eventView_init");

        // attach handlers
        $('.addGuest').bind( 'click', function() { HCATS.event.eventbuilder.addGuestStep1(); } );
        $('a.button.console').attr( 'href', '/');
        $('#guestRowInput').hide();
    }

    module.addGuestStep1 = function() {
        // alert("Add a guest");

        $('#guestRowInput').show();

        $(".inviteGuestButton").bind( 'click', function() { HCATS.event.eventbuilder.addGuestStep2(); });
        $('.addGuest').hide();

    }

    module.addGuestStep2 = function () {
        //alert("Add a guest step 2");
        var guestDetails = {
            name: $('#guestRowInput').find("[name=guestName]")[0].value,
            address: $('#guestRowInput').find("[name=guestAddress]")[0].value
        };

        this.inviteGuest(guestDetails);

        $('#guestRowInput').find("[name=guestName]").attr('value','');
        $('#guestRowInput').find("[name=guestAddress]").attr('value','');

        // Send an AJAX hit
        this.render();

    }

    module.render = function() {
        // This will fill the variable stuff into their containers - and attach actions
        var guestIdx;
        $('#guestListContainer').html('');
        $('#commentContainer').html('');

        var $newRow = $('#guestHeader').clone();
        $newRow.removeClass('template');
        $newRow.removeAttr('ID');
        $('#guestListContainer').append($newRow);

        for (guestIdx in my.guestList) {
            console.dir(my.guestList[guestIdx]);
            var name = my.guestList[guestIdx]['name'];
            var address = my.guestList[guestIdx]['address'];

            var $newRow = $('#guestRow').clone();
            $newRow.removeClass('template');
            $newRow.removeAttr('ID');

            $newRow.find('td[name=name]').text(name);
            $newRow.find('td[name=address]').text(address);
            $('#guestListContainer').append($newRow);
        }

        for (commentIdx in my.comments) {
            var commentText = my.comments[commentIdx]['commentText'];
            var commentAuthor = my.comments[commentIdx]['commentAuthor'];

            var $newRow = $('#comment').clone();
            $newRow.removeClass('template');
            $newRow.removeAttr('ID');

            $newRow.find('.commentText').text(commentText);
            $newRow.find('.commentAuthor').text(commentAuthor);
            $('#commentContainer').append($newRow);
        }

        $('a.remove').bind('click',function(e) {HCATS.event.eventbuilder.guestRemove(e);});
        $('a.nudge').bind('click',function(e) {HCATS.event.eventbuilder.guestNudge(e);});
        $('a.addcomment').bind('click',function(e) {HCATS.event.eventbuilder.addComment(e);});


    }

    module.guestRemove = function(e) {
        var guestAddress= $(e.target).closest('tr').children('td[name=address]').text();
        console.log("Remove "+guestAddress);
        for (guestIdx in my.guestList) {
            if (my.guestList[guestIdx]['address']==guestAddress) {
                my.guestList.splice(guestIdx,1);
            }
        }
        HCATS.event.eventbuilder.render();
    }

    module.guestNudge = function(e) {
        HCATS.event.eventbuilder.render();
    }

    module.addComment = function(e) {
        var comment = {};
        comment.commentText = $('.comment').val();
        comment.commentAuthor = 'Me';
        my.comments.push(comment);
        this.render();

        var params = {
            cmd:        'AddComment',
            comment:    JSON.stringify(comment)

        };
        $.getJSON(
               "e_"+my.eid,
               params,
               function(msg){
                       console.dir(msg);
               }
        );

    }

    return module;



}();