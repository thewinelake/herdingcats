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
        user: null,
        event : null,
        guestList: [],
        comments: []
    }

    var module = {};

    module.init = function (eid)
    {
        console.log('event.init('+eid+')');

        my.eid = eid;
        my.guests = [];
        my.comments = [];
        my.User  = '';

        this.loadFromServer();
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
               my.user = msg.user;

               HCATS.event.eventbuilder.render();

           }
        );
    }

    module.saveToServer = function() {
        // This is where we do an AJAX hit to the server to save the latest event settings
        // and get updates - there could be a "merge"?
        //
        // NB - this is very different to the way in which comments are submitted. Not sure if it's better!
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

        $('#guestRowInput').hide();

        // reset bound functions
        $('button').unbind();

        // attach handlers
        $('button.link').bind('click',buttonLink);

        // Guests
        $('button.addGuest').bind( 'click', function(e) { HCATS.event.eventbuilder.addGuestStep1(e); } );
        $("button.inviteGuest").bind( 'click', function(e) { HCATS.event.eventbuilder.addGuestStep2(e); });
        $('button.remove').bind('click',function(e) {HCATS.event.eventbuilder.removeGuest(e);});
        $('button.nudge').bind('click',function(e) {HCATS.event.eventbuilder.guestNudge(e);});

        // Comments
        $('button.addcomment').bind('click',function(e) {HCATS.event.eventbuilder.addComment(e);});
        $('button.broadcastComment').bind( 'click', function(e) { HCATS.event.eventbuilder.broadcastComment(e); });
        $('button.deleteComment').bind( 'click', function(e) { HCATS.event.eventbuilder.deleteComment(e); });

        // Generic
        $('button.deleteevent').bind('click',function(e) {HCATS.event.eventbuilder.sendEventCmd(e,'DeleteEvent');});
        $('button.cancelevent').bind('click',function(e) {HCATS.event.eventbuilder.sendEventCmd(e,'CancelEvent');});
        $('button.editEvent').bind('click',function(e) {HCATS.event.eventbuilder.editEvent();});



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
            var guestDetails = my.guestList[guestIdx];
            var $newRow = $('#guestRow').clone();
            $newRow.removeClass('template');
            $newRow.removeAttr('ID');
            $newRow.attr('ID','uid_'+guestDetails['uid']);


            $newRow.find('td[name=name]').text(guestDetails['name']);
            $newRow.find('td[name=email]').text(guestDetails['email']);
            $newRow.find('td[name=status]').text( my.guestList[guestIdx]['status']);

            $('#guestListContainer').append($newRow);
        }

        for (commentIdx in my.comments) {
            var comment = my.comments[commentIdx];
            var commentHtml = comment['commentHtml'];
            var commentAuthor = comment['commentAuthor'];

            var commentInfo = '['+comment['commentMid']+'] '+comment['commentAuthor']+' @ '+comment['commentGMT'];

            var $newRow = $('#comment').clone();
            $newRow.removeClass('template');
            $newRow.removeAttr('ID');
            $newRow.attr('ID', 'mid_'+comment['commentMid']);

            $newRow.find('.commentHtml').html(commentHtml);
            $newRow.find('.commentInfo').text(commentInfo);


            //$newRow.find('.commentActions').html(commentActions);

            $('#commentContainer').append($newRow);
        }


        this.initView();


    }

    module.editEvent = function() {

    }

    module.addGuestStep1 = function(e) {
        // alert("Add a guest");

        $._data( $("button.addGuest"), "events" );

        $('#guestRowInput').show();
        $('button.addGuest').hide();

    }

    module.addGuestStep2 = function (e) {
        this.addGuest();

        $('#guestRowInput').find("[name=guestName]").attr('value','');
        $('#guestRowInput').find("[name=guestEmail]").attr('value','');
    }

    module.inviteGuest = function(guestDetails) {
        // alert("inviting guest");
        my.guestList.push(guestDetails);
        this.saveToServer();
    };


    module.addGuest = function(e) {
        var guestDetails = {
            name: $('#guestRowInput').find("[name=guestName]")[0].value,
            email: $('#guestRowInput').find("[name=guestEmail]")[0].value
        };
        //my.guestList.push(guestDetails);
        //this.render();

        var params = {
            cmd:            'AddGuest',
            guestdetails:   JSON.stringify(guestDetails)
        };
        $.getJSON(
               "e_"+my.eid,
               params,
               function(msg){
                   console.dir(msg);
                   HCATS.event.eventbuilder.loadFromServer();
                   // Maybe this is where we should render?
               }
        );

    }

    module.removeGuest = function(e) {
        var guestEmail= $(e.target).closest('tr').children('td[name=email]').text();
        var guestUID = $(e.target).closest('tr').attr('ID');
        guestUID = guestUID.split('_')[1];

        console.log("Remove "+guestEmail);
//        for (guestIdx in my.guestList) {
//           if (my.guestList[guestIdx]['email']==guestEmail) {
//                var removeGuestUID = guestIdx;
//                my.guestList.splice(guestIdx,1);
//            }
//        }
//        HCATS.event.eventbuilder.render();

        var params = {
             cmd:           'RemoveGuest',
             guestuid:      guestUID
         };
         $.getJSON(
                "e_"+my.eid,
                params,
                function(msg){
                    console.dir(msg);
                    HCATS.event.eventbuilder.loadFromServer();

                    // Maybe this is where we should render? Slower but safer
                }
         );
    }

    module.guestNudge = function(e) {
        HCATS.event.eventbuilder.render();
    }

    module.addComment = function(e) {
        var comment = {};
        comment.commentText = $('.newComment').val();
        comment.commentAuthor = my.user.email;
        //my.comments.push(comment);
        //this.render();

        var params = {
            cmd:        'AddComment',
            comment:    JSON.stringify(comment)

        };
        $.getJSON(
               "e_"+my.eid,
               params,
               function(msg){
                   console.dir(msg);
                   HCATS.event.eventbuilder.loadFromServer();

               }
        );

    }

    module.broadcastComment = function(e) {
        this.sendCommentCmd(e,'BroadcastComment');
    }
    module.deleteComment = function(e) {
        this.sendCommentCmd(e,'DeleteComment');
    }

    module.sendCommentCmd = function(e,cmd) {
        var commentMID = $(e.target).closest('div.comment').attr('ID');
        commentMID = commentMID.split('_')[1];

        var params = {
            cmd:        cmd,
            commentmid:  commentMID

        };
        $.getJSON(
               "e_"+my.eid,
               params,
               function(msg){
                   console.dir(msg);
                   if (msg.refresh) {
                       HCATS.event.eventbuilder.loadFromServer();
                   }
               }
        );
    }

    module.sendEventCmd = function(e,cmd) {
        var params = {
            cmd:        cmd
        };
        $.getJSON(
               "e_"+my.eid,
               params,
               function(msg){
                   console.dir(msg);
                   // Then what do we do?
                   window.location.href='/';

               }
        );
    }

    return module;



}();