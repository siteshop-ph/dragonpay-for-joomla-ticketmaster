<?php




## Show transaction UNKNOW  ERROR message to the client.

echo '<h3><font color="red">Payment Unknow Error - Dragonpay Transaction Ref: '.$dragonpay_webReturn_refno.'</font></h3>';

echo '<br/>';

echo '<font color="red">Unknow Error! Your payment for <b>order Ref: '.$dragonpay_webReturn_txnid.'</b> is not complete.</font>';

echo '<br/><br/>';             

echo ' <h4><b>  >> Please, check your email inbox for Error confirmation</b></h4>'; 
                      






      


?>  
