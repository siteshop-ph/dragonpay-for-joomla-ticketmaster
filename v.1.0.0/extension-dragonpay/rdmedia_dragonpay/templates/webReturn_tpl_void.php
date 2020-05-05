<?php



## Show transaction VOID message to the client.

echo '<h3><font color="red">Payment VOID - Dragonpay Transaction Ref: '.$dragonpay_webReturn_refno.'</font></h3>';

echo '<br/>';

echo '<font color="red">VOID! Your payment for <b>order Ref: '.$dragonpay_webReturn_txnid.'</b> has a VOID status.</font>';

echo '<br/><br/>';             

echo ' <h4><b>  >> Please, check your email inbox for VOID status confirmation</b></h4>'; 


      


?>   
