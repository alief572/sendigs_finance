
/*!++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
* GoToPage, Redirect ke halaman tertentu
*/
function GoToPage(url){
    window.location.href=url;
}


/*
* !++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
* Toast Notif by Kamran Ahmed copyright MIT license 2014
*/
function ToastNotif(msg)
{
    $.toast({
        text: msg, //+"<img src='"+ImgURL+"loading.gif'>",
        position: 'top-center',
        loader: false,
        hideAfter: 3000
    });
}

/*!----------------------------------------------------------------------------------------------------------------
* Fungsi cegah input selain number
*
*/
function check_int(evt) 
{
    var charCode = ( evt.which ) ? evt.which : event.keyCode;
    return ( charCode >= 45 && charCode <= 57 || charCode == 8 );
}

/*!----------------------------------------------------------------------------------------------------------------
* AjaxNotif, Munculkan notifikasi ajax
*/
function AjaxNotif(msg)
{
	$("#ajaxFailed").html("<div class='FailedMessage'>" + msg + "</div>");
	$("#ajaxFailed").fadeIn('fast').show().delay(3000).fadeOut('fast');
}

$(document).ready(function(){
	/*!-----------------------------------------------------------------------------------------------------------
	* Penanganan Ajax Error
	*/
	$.ajaxSetup({
		error: function(jqXHR, exception) {
			var msg = '';
			var TimoutLength = 0;

			if (jqXHR.status === 0) {
				msg = 'Not connected, Verify Network.';
				TimoutLength = 5000;
			} else if (jqXHR.status == 404) {
				msg = 'Requested page not found. [404]';
			} else if (jqXHR.status == 500) {
				msg = 'Internal Server Error [500].';
			} else if (exception === 'parsererror') {
				msg = 'Requested JSON parse failed.';
			} else if (exception === 'timeout') {
				msg = 'Request Timeout.';
			} else if (exception === 'abort') {
				msg = 'Ajax request aborted.';
			} else {
				msg = 'Uncaught Error.\n' + jqXHR.responseText;
			}

			setTimeout(function(){ 
		   		AjaxNotif(msg);
		    }, TimoutLength);
		},
		cache: false
	});
/*!
* End Document Ready
*/
});