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
HCATS.register = HCATS.register || {};
HCATS.register.js = function()
{
    var module = {};

    module.init = function ()
    {
        this.initView();
    };



    module.initView = function() {
        // attach handlers
        $('.register').bind( 'click', function() { $("#register").submit(); });



    }

    return module;



}();