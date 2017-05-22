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
HCATS.login = HCATS.login || {};
HCATS.login.js = function()
{
    var module = {};

    module.init = function ()
    {
        this.initView();
    };



    module.initView = function() {
        // attach handlers
        $('.login').bind( 'click', function() { $("#login").submit(); });
        $('.showregister').attr( 'href', '/showregister');


    }

    return module;



}();