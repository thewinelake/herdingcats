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

// Main HCAT namespace
var HCATS = HCATS || {};




function buttonLink(e) {
    var url = $(e.target).closest('button').attr('href');
    if (url) {
        window.location.href=url;
    }
};