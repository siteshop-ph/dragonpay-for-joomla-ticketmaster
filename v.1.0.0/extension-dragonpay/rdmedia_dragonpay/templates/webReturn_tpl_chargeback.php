<?php



## Show transaction CHARGEBACK message to the client.

echo '<h3><font color="red">Payment Chargeback - Dragonpay Transaction Ref: '.$dragonpay_webReturn_refno.'</font></h3>';

echo '<br/>';

echo '<font color="red">Chargeback! Your payment for <b>order Ref: '.$dragonpay_webReturn_txnid.'</b> has a chargeback status.</font>';

echo '<br/><br/>';             

echo ' <h4><b>  >> Please, check your email inbox for Chargeback confirmation</b></h4>'; 




      


?>  
