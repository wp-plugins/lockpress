<?php
// default templates

$lockpressTmplClosedGuest=
'<strong>This post is closed.</strong> Only <!--price--> <!--currency--> for <!--recurring--> <!--buylink-->
<br/>Click to <!--signinlink signin--> to see the content if you already bought it.';

$lockpressTmplClosedUser=
'<strong>This post is closed.</strong> Only <!--price--> <!--currency--> for <!--recurring--> <!--buylink-->';

$lockpressTmplClosedGuestR0=
'<strong>This post is closed.</strong> Only <!--price--> <!--currency--> <!--buylink-->
<br/>Click to <!--signinlink signin--> to see the content if you already bought it.';

$lockpressTmplClosedUserR0=
'<strong>This post is closed.</strong> Only <!--price--> <!--currency--> <!--buylink-->';

$lockpressTmplCancelPage=
'Canceled';

$lockpressTmplSuccessPageGuest=
'Thank you for your purchase. You now have access to the current item.  
Please use login <!--login--> and password <!--password--> next time you want to access your purchases.
<br/><a class="closelink">Click here to hide this message</a>';

$lockpressTmplSuccessPageUser=
'Thank you for your purchase. You now have access to the current item.
<br/><a class="closelink">Click here to hide this message</a>';

$lockpressTmplSuccessEmailThema=
'Welcome to '.get_option('siteurl');
$lockpressTmplSuccessEmail=
'Thank you for your purchase. You now have access to the item.  
Please use login <!--login--> and password <!--password--> next time you want to access your purchases.';

/// платежные сообщения
$lockpressMsg_NotVerified=
'Error! PayPal did not provide verification for your payment!';

$lockpressMsg_NotCorrected=
'Error! Payment data incorrect!';

$lockpressMsg_RepeatTxn=
'Error! This transaction has already been processed!';

$lockpressMsg_NotFoundPost=
'Error! Item was not found!';

$lockpressMsg_NotSell=
'Error! This item has no content!';

$lockpressMsg_NotAmount=
'Error! Payment amount mismatch!<br>Please contact <!--adminEmail-->';

$lockpressMsg_NotPaypalEmail=
'Sorry, but you must enter your PayPal email at the LockPress settings page.';

$lockpressMsg_NotSecureKey=
'Sorry, but you must enter your LockPress Secure Key at the LockPress settings page.';

?>