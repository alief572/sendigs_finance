<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/
$route['default_controller'] = 'dashboard';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

$route['login'] = 'users/users/login';
$route['logout'] = 'users/users/logout';

// Redirect entry pages only; legacy action URLs remain the posting implementation.
$route['jurnal_invoicing'] = 'jurnal_all/legacy/INV';
$route['jurnal_invoicing/index'] = 'jurnal_all/legacy/INV';
$route['jurnal_penerimaan'] = 'jurnal_all/legacy/RCV';
$route['jurnal_penerimaan/index'] = 'jurnal_all/legacy/RCV';
$route['jurnal_payment'] = 'jurnal_all/legacy/PAY';
$route['jurnal_payment/index'] = 'jurnal_all/legacy/PAY';
$route['jurnal_expense'] = 'jurnal_all/legacy/EXP';
$route['jurnal_expense/index'] = 'jurnal_all/legacy/EXP';
$route['jurnal_payment_petty_cash'] = 'jurnal_all/legacy/PCS';
$route['jurnal_payment_petty_cash/index'] = 'jurnal_all/legacy/PCS';
$route['report_jurnal_invoicing'] = 'report_jurnal_all/legacy/INV';
$route['report_jurnal_invoicing/index'] = 'report_jurnal_all/legacy/INV';
$route['report_jurnal_penerimaan'] = 'report_jurnal_all/legacy/RCV';
$route['report_jurnal_penerimaan/index'] = 'report_jurnal_all/legacy/RCV';
$route['report_jurnal_payment'] = 'report_jurnal_all/legacy/PAY';
$route['report_jurnal_payment/index'] = 'report_jurnal_all/legacy/PAY';
$route['report_jurnal_expense'] = 'report_jurnal_all/legacy/EXP';
$route['report_jurnal_expense/index'] = 'report_jurnal_all/legacy/EXP';
