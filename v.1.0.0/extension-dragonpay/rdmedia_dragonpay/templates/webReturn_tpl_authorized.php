<?php



## Show transaction AUTHORIZED message to the client.

echo '<h3><font color="green">Payment Authorized - Dragonpay Transaction Ref: '.$dragonpay_webReturn_refno.'</font></h3>';

echo '<br/>';

echo '<font color="green">Authorized! Your payment for <b>order Ref: '.$dragonpay_webReturn_txnid.'</b> has an Authorized status.</font>';

echo '<br/><br/>';             

echo ' <h4><b>  >> Please, check your email inbox for Authorized status confirmation</b></h4>'; 
                  

      


?>    
